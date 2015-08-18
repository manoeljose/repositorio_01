<?php

namespace BoletoBundle\Classes;


 use BoletoBundle\Entity\Charge;
 use Doctrine\ORM\EntityManager;
 use Monolog\Logger;
 use AppBundle\Entity\User;

 class BoletoManager {

     /** @var  $errorMsg */
     private $errorMsg;

     /** @var  $em EntityManager */
     private $em;


     /** @var $logger Logger */
     private $logger;

     /** @var  BoletoCharge */
     private $boletoCharge;

     /** @var  BoletoPayment */
     private $boletoPayment;


     function __construct(EntityManager $em, Logger $logger, BoletoCharge $boletoCharge, BoletoPayment $boletoPayment)
     {
         $this->em = $em;
         $this->logger = $logger;
         $this->boletoCharge = $boletoCharge;
         $this->boletoPayment = $boletoPayment;
         $this->errorMsg = null;
     }

     /**
      * @return mixed
      */
     public function getErrorMsg() {
         return $this->errorMsg;
     }

     /**
      * @param User $user
      * @param BoletoPlan $boletoPlan
      * @param $cpf
      * @param null $dueDate
      * @return bool
      */
     public function createBoleto(User $user, BoletoPlan $boletoPlan, $cpf, $dueDate = null)
     {
         return $this->boletoCharge->newBoleto($user, $boletoPlan, $cpf, $dueDate);
     }

     /**
      * @param $boletoNotification
      * @return bool
      */
     public function receiveNotification($post_data)
     {
         $paymentNotification = $this->boletoPayment->receiveNotifiation($post_data);
         $this->errorMsg = $this->boletoPayment->getErrorMsg();
         if (!$paymentNotification)
         {
             return false;
         }

         return $this->boletoPayment->receivePayment($paymentNotification);
     }


     /**
      * @param Charge $charge
      * @return bool
      */
     public function setChargeLiberado(Charge $charge)
     {
         return $this->boletoCharge->changeChargeStatus($charge, Charge::Status_Liberado);
     }


     /**
      * Search for the last registered cpf of the user (return the cpf) or return null if there's no cpf registered for this user yet
      *
      * @param User $user
      * @return null|string
      */
    public function getCpf(User $user)
    {
        //search for the the last registered cpf of the user
        $query = $this->em->getRepository('BoletoBundle:Charge')
            ->createQueryBuilder('c')
            ->where('c.User = :user')
            ->setParameter('user', $user)
            ->orderBy('c.id', 'DESC')
            ->getQuery()
            ->getResult();

        //test for result
        if(sizeof($query)>0 and array_key_exists(0, $query))
        {
            /** @var  $charge Charge */
            $charge = $query[0];
            return $charge->getCpf();

        }
        else //no cpf found for this user
            return null;

    }//end of function



 }//end of class