<?php
/**
 * Created by PhpStorm.
 * User: Manoel
 * Date: 04-Jun-15
 * Time: 7:43 PM
 */
namespace BoletoBundle\Classes;

use BoletoBundle\Event\BoletoEvents;
use BoletoBundle\Event\BoletoPaidEvent;
use Doctrine\ORM\EntityManager;
use Monolog\Logger;
use DateTime;
use Symfony\Component\Config\Definition\Exception\Exception;
use BoletoBundle\Entity\Charge;
use AppBundle\Entity\User;
use Symfony\Component\EventDispatcher\ContainerAwareEventDispatcher;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

class BoletoCharge {

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

    /** @var ContainerAwareEventDispatcher  */
    private $eventDispatcher;

    /** @var  Integer */
    private $due_date_days;

    function __construct(EntityManager $em, Logger $logger, BoletoConnectionClient $boletoConnectionClient, $boletoAmb, $due_date_days, EventDispatcherInterface $eventDispatcher)
    {
        $this->em = $em;
        $this->logger = $logger;
        $this->boletoConnClient = $boletoConnectionClient;
        $this->boletoAmb = $boletoAmb;
        $this->errorMsg = null;
        $this->eventDispatcher = $eventDispatcher;
        $this->due_date_days = $due_date_days;
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
     * @param User $user
     * @param BoletoPlan $boletoPlan
     * @param $user_cpf
     * @param DateTime $dueDate
     * @return bool
     */
    public function newBoleto(User $user, BoletoPlan $boletoPlan, $user_cpf, DateTime $dueDate = null)
    {
        $this->logger->info(__METHOD__ . " - Creating new boleto for user (".$user->getId().") with plan (".$boletoPlan->getId().") and cpf (".$user_cpf.")");

        //sanitize cpf string
        $user_cpf = str_replace(array(".", "-", " "), array("", "", ""), $user_cpf);


        //sanitize user name (limited to 60 chars)
        $user_name = $user->getUsernameCanonical();

        if(strlen($user_name)>60)
            $user_name = substr($user_name, 0, 60);


        //sanitize user email (limited to 80 chars)
        $user_email = $user->getEmailCanonical();

        if(strlen($user_email)>80)
            $user_email = substr($user_email, 0, 80);


        //due data
        if(is_null($dueDate))
        {
            //if there's no due date set, put the standard due date days from now
            $dueDate = new \DateTime();
            $dueDate->add(new \DateInterval("P".$this->due_date_days."D"));
        }


        //notifyPayer
        if($boletoPlan->getNotifyPayer()==true OR $boletoPlan->getNotifyPayer()=="true")
            $notifyPlayer = "true";
        else
            $notifyPlayer = "false";


        //check if boletoPlan is valid
        if($boletoPlan->isValid() == false)
        {
            $this->logger->error(__METHOD__ . " - boleto plan is invalid ");
            return false;
        }

        //create the url query
        $dadosCharge =
               "issue-charge?".
               "token=".$this->boletoConnClient->getToken()."&".
               "description=".$boletoPlan->getDescription()."&".                            // descrição da cobrança
               "amount=".$boletoPlan->getAmount()."&".                                      // valor da cobrança
               "maxOverdueDays=".$boletoPlan->getMaxOverdueDays()."&".                      // numero de dias para pagamento em atraso
               "dueDate=".$dueDate->format("d/m/Y")."&".
               "notifyPayer=".$notifyPlayer."&".                                            // haverá ou não notificação de cobrança
               "payerName=".$user_name."&".
               "payerEmail=".$user_email."&".
               "payerCpfCnpj=".$user_cpf."&".
               "reference=".$user->getId()."&".                                             // referência da cobrança = id_user
               "test=".$this->getBoletoAmb();                                               // ambiente: true para teste; false para real

        //Execute the request
        $response = $this->boletoConnClient->post($dadosCharge, null);

        $this->logger->info(__METHOD__ . " - Received response: " . print_r($response, true));

        // check if the boleto was not registered successfully
        // todo melhorar a mensagem gerada na log em boletoConnection
        if(!is_array($response) OR !array_key_exists('data', $response) OR !array_key_exists('charges', $response['data']) OR !array_key_exists('0', $response['data']['charges']))
        {
            $this->logger->error(__METHOD__ . " - When trying to insert new boleto into Boleto Facil, we received an unknown error. Check the logger info above to get more info about it");
            return false;
        }

        // boleto was registered successfully into Boleto Facil. Then the return data will be registered into local database

        $charge = new Charge();

        // todo corrigir as variaveis a partir do retorno do Boleto Facil
        $charge->setCode($response['data']['charges']['0']['code']);
        $charge->setDueDate(DateTime::createFromFormat('d/m/Y', $response['data']['charges']['0']['dueDate']));
        $charge->setAmount($boletoPlan->getAmount());
        $charge->setLink($response['data']['charges']['0']['link']);
        $charge->setIdPlan($boletoPlan->getId());
        $charge->setUser($user);
        $charge->setCpf($user_cpf);


        try
        {
            $this->em->persist($charge);
            $this->em->flush();

        } catch(\Exception $e)
        {
            $this->errorMsg = __METHOD__.":Erro na gravação de Charge com a mensagem:".$e->getMessage();
            $this->logger->error($this->getErrorMsg());

            return false;
        }

        $this->logger->info(__METHOD__ . " - invoice created (".$charge->getId().")");

        return $charge;

    }//end of function new boleto


    /**
     * Change status of a charge
     *
     * @param Charge $charge
     * @param $status
     * @return bool
     */
    public function changeChargeStatus(Charge $charge, $status)
    {
        $this->logger->info(__METHOD__. " - Updating charge (".$charge->getId().") to status ".$status);

        $charge->setChargeStatus($status);

        try
        {
            $this->em->persist($charge);
            $this->em->flush();

        } catch(\Exception $e)
        {
            $this->errorMsg = __METHOD__.":Erro na gravação de Charge com a mensagem:".$e->getMessage();
            $this->logger->error($this->getErrorMsg());

            return false;
        }

        $this->logger->info(__METHOD__. " - Updating ok, firing boleto paid event ");

        //create the event with the invoice
        $event = new BoletoPaidEvent($charge);

        //dispatch the event
        $this->eventDispatcher->dispatch(BoletoPaidEvent::BOLETO_PAID, $event);

        return true;

    }//end of function chagenChargeStatus

}//end of class