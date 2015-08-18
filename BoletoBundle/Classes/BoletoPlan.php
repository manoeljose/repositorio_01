<?php

namespace BoletoBundle\Classes;


class BoletoPlan
{
    const BoletoPlan_1Month_full = 1;
    const BoletoPlan_3Months_full = 2;
    const BoletoPlan_6Months_full = 3;
    const BoletoPlan_12Months_full = 4;

    private $id;              // integer - código do plano
    private $amount;          //float  - valor do plano
    private $description;
    private $maxOverdueDays;  // integer - Número máximo de dias que o boleto poderá ser pago após o vencimento
    private $notifyPayer;     // boolean - Define se o Boleto Fácil enviará emails de notificação de cobrança

    /** @var bool $valid */
    private  $valid = false;

    public function isValid()
    {
        return $this->valid;
    }

    /**
     * @return mixed
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return mixed
     */
    public function getAmount()
    {
        return $this->amount;
    }

    /**
     * @return mixed
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * @return mixed
     */
    public function getMaxOverdueDays()
    {
        if($this->maxOverdueDays<0)
            return 0;
        elseif($this->maxOverdueDays>90)
            return 90;
        else
            return $this->maxOverdueDays;
    }

    public function getNotifyPayer()
    {
        return $this->notifyPayer;
    }

    function __construct($BoletoPlan)
    {

        if($BoletoPlan === self::BoletoPlan_1Month_full)
        {
            $this->id = self::BoletoPlan_1Month_full;
            $this->amount = 2.30;       // alterar para 24.90;
            $this->description = "Aula_de_Musica_-_1Month_full";
            $this->maxOverdueDays = 0;                       // Número máximo de dias que o boleto poderá ser pago após o vencimento //Número inteiro maior ou igual a 0 e menor ou igual a 90
            $this->notifyPayer = false;                      // Boleto Fácil enviará emails de notificação de cobrança
            $this->valid = true;
        }
        else
            $this->valid = false;

    }//end of function

}//end of VindiSubscriptionPlan