<?php
/**
 * 01 - alteracao 11:51
 * 02 - alteracao 11:58
 * 03 - alteracao 00:06
 *
 * Created by PhpStorm.
 * User: Manoel
 * Date: 04-Jun-15
 * Time: 7:43 PM
 */
namespace BoletoBundle\Classes;

use Doctrine\ORM\EntityManager;
use Monolog\Logger;
use DateTime;
use Symfony\Component\Config\Definition\Exception\Exception;
use BoletoBundle\Entity\Charge;
use AppBundle\Entity\User;

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

    function __construct(EntityManager $em, Logger $logger, BoletoConnectionClient $boletoConnectionClient, $boletoAmb)
    {
        $this->em = $em;
        $this->logger = $logger;

        $this->boletoConnClient = $boletoConnectionClient;

        $this->boletoAmb = $boletoAmb;

        $this->errorMsg = null;
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
     * @return bool
     */
    public function newBoleto(User $user, BoletoPlan $boletoPlan) {

        // já existe boleto gerado para os próximos xx dias - return false

        //$url = "https://www.boletobancario.com/boletofacil/integration/api/v1/issue-charge?".
        //       "token="."1B544D5E53391EB76C70DA66689ED4CE235CBB1AC74F516FD6D22D2C8B9BC6E0"."&". // ver como recuperar token
        $dadosCharge =
               "issue-charge?".
               "token=1B544D5E53391EB76C70DA66689ED4CE235CBB1AC74F516FD6D22D2C8B9BC6E0"."&". // todo colocado o token aqui porque deu erro como baseUrl em função do "?"
               "description=".$boletoPlan->getDescription()."&".                            // descrição da cobrança
               "amount=".$boletoPlan->getAmount()."&".                                      // valor da cobrança
               "maxOverdueDays=".$boletoPlan->getMaxOverdueDays()."&".                      // numero de dias para pagamento em atraso
               "notifyPayer=true"."&".$boletoPlan->getNotifyPayer()."&".                    // haverá ou não notificação de cobrança
               "payerName=".$user->getUsernameCanonical()."&".
               "payerEmail=".$user->getEmailCanonical()."&".
               "payerCpfCnpj=".$user->getCpfCanonical()."&".
               "reference=".$user->getId()."&".                                             // referência da cobrança = id_user
               "test=".$this->getBoletoAmb();                                               // ambiente: true para teste; false para real

        $response = $this->boletoConnClient->post($dadosCharge, null);

        // check if the boleto was not registered successfully
        // todo melhorar a mensagem gerada na log em boletoConnection
        if(!is_array($response)) {
            $this->logger->error(__METHOD__ . " - When trying to insert new boleto into Boleto Facil, we received an unknown error. Check the logger info above to get more info about it");
            return false;
        }

        // boleto was registered successfully into Boleto Facil. Then the return datas will be registred into local database

        $charge = new Charge();

        // todo corrigir as variaveis a partir do retorno do Boleto Facil
        $charge->setCode($response['data']['charges']['0']['code']);
        $charge->setDueDate(DateTime::createFromFormat('d/m/Y', $response['data']['charges']['0']['dueDate']));
        $charge->setAmount($boletoPlan->getAmount());
        $charge->setLink($response['data']['charges']['0']['link']);
        $charge->setIdPlan($boletoPlan->getId());
        $charge->setUser($user);
        $charge->setCpf($user->getCpfCanonical());
        $charge->setChargeStatus(Charge::Status_Aguardando_Pagto);
        $charge->setUpdateDtTm(new DateTime());

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

        $this->errorMsg = "OK   - Boleto gerado com sucesso";
        return $charge;
    }
}