<?php

namespace BoletoBundle\Event;

use BoletoBundle\Entity\Charge;
use Symfony\Component\EventDispatcher\Event;


class BoletoPaidEvent extends Event
{
    /** @var  Charge */
    private $charge;

    /** This Event is fired when an invoice paid notification is received to inform to the listeners that an invoice was paid */
    const BOLETO_PAID = 'boleto.boleto_paid';


    public function getCharge()
    {
        return $this->charge;
    }

    function __construct(Charge $charge)
    {
        $this->charge = $charge;
    }

}//end of class BoletoEvents
