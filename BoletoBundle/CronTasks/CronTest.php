<?php

namespace BoletoBundle\CronTasks;

use Doctrine\ORM\EntityManager;
use Monolog\Logger;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Validator\Constraints\Date;
use Symfony\Component\Validator\Constraints\DateTime;
use AppBundle\Entity\User;
use BoletoBundle\Classes\BoletoPlan;
use BoletoBundle\Entity\PaymentNotification;


class CronTest extends ContainerAwareCommand
{
    protected function configure()
    {
        $this
            ->setName('CronTest:run')
            ->setDescription('Daily send promotional offers to invoice users');
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $em = $this->getContainer()->get('doctrine.orm.default_entity_manager');
        $boletoManager = $this->getContainer()->get('boleto.BoletoManager');

        //$user = new User();
        //$user->setId(1);

        $user = $em->getRepository('AppBundle:User')->findOneById(1);
        $user->setUsernameCanonical("Maria");   // Tereza
        $user->setEmailCanonical("majosto@yahoo.com.br");

        $boletoPlan = new BoletoPlan(BoletoPlan::BoletoPlan_1Month_full);


        $charge = $boletoManager->createBoleto($user, $boletoPlan, "073.742.239-42", null);
        //print "createBoleto: msg = " . $boletoManager->getErrorMsg();

        // ***********************************************************************************************

        //$boletoPaymentNotification = new PaymentNotification();
        //$boletoPaymentNotification->setPaymentToken('91BBCD05D73905F08C414F34C991543E');
        //$boletoPaymentNotification->setId(50);

        // conteudo do RAW BODY recebido na notificaçao
        $boletoNotification = "paymentToken=91BBCD05D73905F08C414F34C991543E&chargeReference=184&chargeCode=10100926";


        // Recebe a notificação do pagamento e registra o pagamento notificado
        $retorno = $boletoManager->receiveNotification($boletoNotification);
        print "receiveNotification: retorno = " . " msg = " . $boletoManager->getErrorMsg();

        // o recebimento do pagamento é executado logo após o registro da notificação
        //$retorno = $boletoManager->receivePayment($user, $boletoPaymentNotification);
        //print "receivePayment: retorno = " . " msg = " . $boletoManager->getErrorMsg();
    }
}