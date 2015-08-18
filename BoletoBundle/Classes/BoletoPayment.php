<?php
/**
 * Created by PhpStorm.
 * User: Manoel
 * Date: 04-Jun-15
 * Time: 7:43 PM
 */
namespace BoletoBundle\Classes;

use Doctrine\ORM\EntityManager;
use Monolog\Logger;
use DateTime;
use BoletoBundle\Entity\Charge;
use BoletoBundle\Entity\Payment;
use BoletoBundle\Entity\PaymentNotification;


class BoletoPayment {

    /** @var  $errorMsg */
    private $errorMsg;

    /** @var  $em EntityManager */
    private $em;

    /** @var $logger Logger */
    private $logger;

    /** @var $boletoAmb */
    private $boletoAmb;          // define o ambiente para geração de boleto: 1(true) = teste; 0(false) = real

    /** @var $boletoConnClient BoletoConnectionClient  */
    private $boletoConnClient;

    /** @var  BoletoCharge */
    private $boletoCharge;



    function __construct(EntityManager $em, Logger $logger, BoletoConnectionClient $boletoConnectionClient, $boletoAmb, BoletoCharge $boletoCharge)
    {
        $this->em = $em;
        $this->logger = $logger;
        $this->boletoConnClient = $boletoConnectionClient;
        $this->boletoAmb = $boletoAmb;
        $this->errorMsg = null;
        $this->boletoCharge = $boletoCharge;
    }

    /**
     * @return mixed
     */
    public function getErrorMsg() {
        return $this->errorMsg;
    }

    /**
     * @return mixed
     */
    public function getBoletoAmb() {
        return $this->boletoAmb;
    }

    /**
     * @param $BoletoNotification
     * @return \BoletoBundle\Entity\PaymentNotification|bool
     */
    public function receiveNotifiation($BoletoNotification)
    {

        $arrayBoletoNotification = $this->validaNotificacaoRecebidaBoletoFacil($BoletoNotification);
        if (!$arrayBoletoNotification) {
            return false;
        }



        // insere a notificação na base local. Esta notificação será validada no momento da recuperação do pagamento notificado.
        $paymentNotification = new PaymentNotification();
        $paymentNotification->setChargeCode($arrayBoletoNotification['chargeCode']);
        $paymentNotification->setChargeReference($arrayBoletoNotification['chargeReference']);
        $paymentNotification->setPaymentToken($arrayBoletoNotification['paymentToken']);

        try
        {
            $this->em->persist($paymentNotification);
            $this->em->flush();

        } catch(\Exception $e)
        {
            $this->errorMsg = __METHOD__.":Erro na gravação de PaymentNotification com a mensagem:".$e->getMessage();
            $this->logger->error($this->getErrorMsg());

            return false;
        }

        return $paymentNotification;
    }

    /**
     * @param PaymentNotification $paymentNotification
     * @return bool
     */
    public function receivePayment(PaymentNotification $paymentNotification)
    {
        $this->logger->info(__METHOD__ . " - Receiving payment from PaymentNotification (".$paymentNotification->getId().")");

        $complementoUrl =
            "fetch-payment-details?".
            "paymentToken=".$paymentNotification->getPaymentToken();


        //from the notification token, request the full payment data
        $response = $this->boletoConnClient->get($complementoUrl);

        $this->logger->info(__METHOD__ . " - Received response: " . print_r($response, true));

        // check if return of Payment was not ok
        if(!is_array($response))
        {
            $this->errorMsg = __METHOD__ . " - When trying to get a Payment into Boleto Facil, we received an unknown error. Check the logger info above to get more info about it. Aditional URL:".$complementoUrl;
            $this->logger->error($this->getErrorMsg());
            return false;
        }

        // recupera a cobrança referente ao pagamento recebido
        $code_charge = $response['data']['payment']['charge']['code'];
        $charge = $this->em->getRepository('BoletoBundle:Charge')->findOneByCode($code_charge);


        //Validate the Charge data against the received payment data
        $statusPayment = $this->validaDadosCharge($charge, $response);

        if ($statusPayment != Payment::Status_Payment_OK)
        {
            $this->errorMsg = __METHOD__.":Dados de cobrança não estão de acordo com o registrado na emissão do boleto:".http_build_query($response);
            $this->logger->error($this->getErrorMsg());
        }

        // Grava dados de pagamento no DataBase local
        $payment = new Payment();

        $payment->setPaymentId($response['data']['payment']['id']);
        $payment->setPaymentAmount($response['data']['payment']['amount']);
        $payment->setPaymentDate(DateTime::createFromFormat('d/m/Y', $response['data']['payment']['date'])->setTime(0,0,0));
        $payment->setPaymentFee($response['data']['payment']['fee']);
        $payment->setPaymentStatus($statusPayment);
        $payment->setChargeReference($response['data']['payment']['charge']['reference']);
        $payment->setChargeAmount($response['data']['payment']['charge']['amount']);
        $payment->setChargeDueDate(DateTime::createFromFormat('d/m/Y', $response['data']['payment']['charge']['dueDate'])->setTime(0,0,0));
        $payment->setUpdateDtTm();
        $payment->setPaymentNotification($paymentNotification);

        if ($charge instanceof Charge)
            $payment->setCharge($charge);


        try
        {
            $this->em->persist($payment);
            $this->em->flush();
        }
        catch(\Exception $e)
        {
            $this->errorMsg = __METHOD__.":Erro na gravação de Payment com a mensagem:".$e->getMessage();
            $this->logger->error($this->getErrorMsg());
            return false;
        }



        //set payment notification status as processed
        $paymentNotification->setStatus(PaymentNotification::Status_processed);

        try
        {
            $this->em->persist($paymentNotification);
            $this->em->flush();
        }
        catch(\Exception $e)
        {
            $this->errorMsg = __METHOD__.":Erro na gravação de Payment Notification Status com a mensagem:".$e->getMessage();
            $this->logger->error($this->getErrorMsg());
            return false;
        }



        //Update the Charge Status
        if($charge instanceof Charge)
        {
            if($this->boletoCharge->changeChargeStatus($charge, Charge::Status_Pago) == false)
                return false;
            else
                return true;
        }
        else
            return true;

    }//end of function


    /**
     * @param Charge $charge
     * @param $response
     * @return int
     */
    public function validaDadosCharge(Charge $charge = null,$response)
    {
        if (!($charge instanceof Charge)) {
            return (Payment::Status_Cobrana_inexistente);
        }
        if ($response['data']['payment']['charge']['reference'] != $charge->getUser()->getId()) {
            return (Payment::Status_User_invalido);
        }
        if (DateTime::createFromFormat('d/m/Y', $response['data']['payment']['charge']['dueDate'])->setTime(0,0,0) != $charge->getDueDate()) {
            return (Payment::Status_Due_Date_invalido);
        }
        if ($response['data']['payment']['charge']['amount'] != $charge->getAmount()) {
            return (Payment::Status_Amount_invalido);
        }
        if ($response['data']['payment']['amount'] != $charge->getAmount()) {
            return (Payment::Status_Valpago_invalido);
        }
        if (DateTime::createFromFormat('d/m/Y', $response['data']['payment']['date'])->setTime(0,0,0) > $charge->getDueDate()) {
            return (Payment::Status_DtPagto_invalido);
        }
        return (Payment::Status_Payment_OK);
    }

    /**
     * @param $boletoNotification
     * Exemplo de notificação esperada como válida: "paymentToken=91BBCD05D73905F08C414F34C991543E&chargeReference=184&chargeCode=10100926"
     * @return array|bool
     */
    public function validaNotificacaoRecebidaBoletoFacil($boletoNotification)
    {

        $arrayBoletoNotification = array();
        $auxArray1 = explode ("&", $boletoNotification);

        foreach ($auxArray1 as $value)
        {
            $auxArray2 = explode ("=", $value);
            if (sizeof($auxArray2) == 2)
            {
                $arrayBoletoNotification[$auxArray2['0']] = $auxArray2['1'];
            }//end of if

        }//end of for each


        if (!array_key_exists("paymentToken", $arrayBoletoNotification) or !array_key_exists("chargeReference", $arrayBoletoNotification) or !array_key_exists("chargeCode", $arrayBoletoNotification))
        {
            $this->errorMsg = __METHOD__."Notificação de pagamento com conteudo inesperado:".$boletoNotification;
            $this->logger->error($this->getErrorMsg());
            return false;
        }//end of if

        return $arrayBoletoNotification;

    }//end of function validaNotificacaoRecebidaBoletoFacil

}//end of class