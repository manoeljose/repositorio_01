<?php

namespace BoletoBundle\Entity;;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;

/**
 * Subscription
 *
 * @ORM\Table(name="boleto_payment")
 * @ORM\Entity
 */
class Payment
{
	/* domínio de $paymentStatus */
    const Status_Payment_OK = 1;           // recebido um pagamento ok
    const Status_Cobrana_inexistente = 2;  // recebido um pagamento para cobrança inexistente
    const Status_User_invalido = 3;        // recebido um pagamento cuja cobrança não foi emitida para este usuário
    const Status_Due_Date_invalido = 4;    // recebido um pagamento cuja cobranca não foi emitida para esta data de vencimento
    const Status_Amount_invalido = 5;      // recebido um pagamento cuja cobrança não foi emitida com este valor
    const Status_Valpago_invalido = 6;     // valor pago diferente do cobrado;
    const Status_DtPagto_invalido = 7;     // data de pagamento posterior da data de vencimento;

    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $id;

    /**
     * @var integer
     *
     * @ORM\Column(name="payment_id", type="integer", nullable=false)
     */
    private $paymentId;          // Identificador único do pagamento no Boleto Fácil feito para um charge.cod 

	 /**
     * @var float
     *
     * @ORM\Column(name="payment_amount", type="float", precision=2, scale=0, nullable=false)
     */

    private $paymentAmount;       // Valor pago


    /**
     * @var float
     *
     * @ORM\Column(name="payment_fee", type="float", precision=2, scale=0, nullable=false)
     */
    private $paymentFee;       // Taxa sobre o pagamento


    /**
     * @var float
     *
     * @ORM\Column(name="payment_net", type="float", precision=2, scale=0, nullable=true)
     */
    private $paymentNet;       // valor liquido

	 /**
     * @var \DateTime
     *
     * @ORM\Column(name="payment_date", type="datetime", nullable=false)
     */
    private $paymentDate;     // Data do registro do pagamento no banco 
	

	
	 /**
     * @var integer
     *
     * @ORM\Column(name="payment_status", type="smallint", nullable=false)
     */
    private $paymentStatus;
	
	 /**
     * @var \BoletoBundle\Entity\Charge
     *
     * @ORM\ManyToOne(targetEntity="BoletoBundle\Entity\Charge")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="charge_code", referencedColumnName="code")
     * })
     */
    private $charge;	

	 /**
     * @var integer
     *
     * @ORM\Column(name="charge_reference", type="integer", nullable=false)
     */
    private $chargeReference;       // referencia - equivale ao user_id	
	
	 /**
     * @var float
     *
     * @ORM\Column(name="charge_amount", type="float", precision=2, scale=0, nullable=false)
     */

    private $chargeAmount;       // Valor cobrado	
	
	 /**
     * @var \DateTime
     *
     * @ORM\Column(name="charge_duedate", type="datetime", nullable=false)
     */
    private $chargeDueDate;     // Data de vencimento da cobrança	

	 /**
     * @var \BoletoBundle\Entity\PaymentNotification
     *
     * @ORM\ManyToOne(targetEntity="BoletoBundle\Entity\PaymentNotification")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="notification_id", referencedColumnName="id")
     * })
     */
    private $paymentNotification;	

	
    /**
     * @var \DateTime
     *
     * @ORM\Column(name="update_dt_tm", type="datetime", nullable=false)
     */

    private $updateDtTm;    // Data de atualização/inserção dos dados



    /**
     * Get id
     *
     * @return integer
     */
    public function getId()
    {
        return $this->id;
    }


    /**
     * Set paymentId
     *
     * @param integer $paymentId
     * @return Payment
     */
    public function setPaymentId($paymentId)
    {
        $this->paymentId = $paymentId;

        return $this;
    }

    /**
     * Get paymentId
     *
     * @return integer 
     */
    public function getPaymentId()
    {
        return $this->paymentId;
    }

    /**
     * Set paymentAmount
     *
     * @param float $paymentAmount
     * @return Payment
     */
    public function setPaymentAmount($paymentAmount)
    {
        $this->paymentAmount = $paymentAmount;

        return $this;
    }

    /**
     * Get paymentAmount
     *
     * @return float 
     */
    public function getPaymentAmount()
    {
        return $this->paymentAmount;
    }

    /**
     * Set paymentDate
     *
     * @param \DateTime $paymentDate
     * @return Payment
     */
    public function setPaymentDate($paymentDate)
    {
        $this->paymentDate = $paymentDate;

        return $this;
    }

    /**
     * Get paymentDate
     *
     * @return \DateTime 
     */
    public function getPaymentDate()
    {
        return $this->paymentDate;
    }

    /**
     * Set paymentFee
     *
     * @param float $paymentFee
     * @return Payment
     */
    public function setPaymentFee($paymentFee)
    {
        $this->paymentFee = $paymentFee;

        $this->setPaymentNet();

        return $this;
    }

    /**
     * Get paymentFee
     *
     * @return float 
     */
    public function getPaymentFee()
    {
        return $this->paymentFee;
    }

    /**
     * Set chargeAmount
     *
     * @param float $chargeAmount
     * @return Payment
     */
    public function setChargeAmount($chargeAmount)
    {
        $this->chargeAmount = $chargeAmount;

        $this->setPaymentNet();

        return $this;
    }

    /**
     * Get chargeAmount
     *
     * @return float 
     */
    public function getChargeAmount()
    {
        return $this->chargeAmount;
    }

    /**
     * Set chargeDueDate
     *
     * @param \DateTime $chargeDueDate
     * @return Payment
     */
    public function setChargeDueDate($chargeDueDate)
    {
        $this->chargeDueDate = $chargeDueDate;

        return $this;
    }

    /**
     * Get chargeDueDate
     *
     * @return \DateTime 
     */
    public function getChargeDueDate()
    {
        return $this->chargeDueDate;
    }

    /**
     * Set updateDtTm
     *
     * @return Payment
     */
    public function setUpdateDtTm()
    {
        $this->updateDtTm = new \DateTime('now');

        return $this;
    }

    /**
     * Get updateDtTm
     *
     * @return \DateTime 
     */
    public function getUpdateDtTm()
    {
        return $this->updateDtTm;
    }

    /**
     * Set chargeReference
     *
     * @param \Integer $chargeReference
     * @return Payment
     */
    public function setChargeReference($chargeReference)
    {
        $this->chargeReference = $chargeReference;

        return $this;
    }

    /**
     * Get chargeReference
     *
     * @return \Integer
     */
    public function getChargeReference()
    {
        return $this->chargeReference;
    }

    /**
     * Set paymentNotification
     *
     * @param \BoletoBundle\Entity\PaymentNotification $paymentNotification
     * @return Payment
     */
    public function setPaymentNotification(\BoletoBundle\Entity\PaymentNotification $paymentNotification = null)
    {
        $this->paymentNotification = $paymentNotification;

        return $this;
    }

    /**
     * Get paymentNotification
     *
     * @return \BoletoBundle\Entity\PaymentNotification 
     */
    public function getPaymentNotification()
    {
        return $this->paymentNotification;
    }



    /**
     * Set charge
     *
     * @param \BoletoBundle\Entity\Charge $charge
     * @return Payment
     */
    public function setCharge(\BoletoBundle\Entity\Charge $charge = null)
    {
        $this->charge = $charge;

        return $this;
    }

    /**
     * Get charge
     *
     * @return \BoletoBundle\Entity\Charge 
     */
    public function getCharge()
    {
        return $this->charge;
    }

    /**
     * Set paymentStatus
     *
     * @param integer $paymentStatus
     * @return Payment
     */
    public function setPaymentStatus($paymentStatus)
    {
        $this->paymentStatus = $paymentStatus;

        return $this;
    }

    /**
     * Get paymentStatus
     *
     * @return integer 
     */
    public function getPaymentStatus()
    {
        return $this->paymentStatus;
    }

    /**
     * Set id
     *
     * @param integer $id
     * @return Payment
     */
    public function setId($id)
    {
        $this->id = $id;

        return $this;
    }

    /**
     * Set paymentNet
     *
     * @param float $paymentNet
     * @return Payment
     */
    public function setPaymentNet()
    {
        $this->paymentNet = $this->paymentAmount - $this->paymentFee;

        return $this;
    }

    /**
     * Get paymentNet
     *
     * @return float 
     */
    public function getPaymentNet()
    {
        return $this->paymentNet;
    }
}
