<?php

namespace BoletoBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;

/**
 * Subscription
 *
 * @ORM\Table(name="boleto_PaymentNotification")
 * @ORM\Entity
 */
class PaymentNotification
{

    const Status_received = 0;
    const Status_processed = 1;

    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="integer", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;          // Número único para uma notificação de pagamento

	 /**
     * @var integer
     *
     * @ORM\Column(name="charge_code", type="integer", nullable=false)
     */
    private $chargeCode;          // Código único da cobrança

    /**
     * @var \String
     *
     * @ORM\Column(name="charge_Reference", type="string", nullable=false)
     */
    private $chargeReference;     // código de referência usado na geração da cobrança = id_user

    /**
     * @var string
     *
     * @ORM\Column(name="payment_Token", type="string", nullable=false)
     */

    private $paymentToken;       // Token que identifica o pagamento no BoletoFácil


    /**
     * @var integer
     *
     * @ORM\Column(name="status", type="smallint", nullable=false)
     */

    private $status;       // Token que identifica o pagamento no BoletoFácil

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="update_dt_tm", type="datetime", nullable=false)
     */

    private $updateDtTm;    // Data de atualização/inserção dos dados

    /**
     * Set id
     *
     * @param integer $id
     * @return PaymentNotification
     */
    public function setId($id)
    {
        $this->id = $id;

        return $this;
    }

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
     * Set chargeReference
     *
     * @param string $chargeReference
     * @return PaymentNotification
     */
    public function setChargeReference($chargeReference)
    {
        $this->chargeReference = $chargeReference;

        return $this;
    }

    /**
     * Get chargeReference
     *
     * @return string 
     */
    public function getChargeReference()
    {
        return $this->chargeReference;
    }

    /**
     * Set paymentToken
     *
     * @param string $paymentToken
     * @return PaymentNotification
     */
    public function setPaymentToken($paymentToken)
    {
        $this->paymentToken = $paymentToken;

        return $this;
    }

    /**
     * Get paymentToken
     *
     * @return string 
     */
    public function getPaymentToken()
    {
        return $this->paymentToken;
    }

    /**
     * Set updateDtTm
     *
     * @return PaymentNotification
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
     * Set chargeCode
     *
     * @param integer $chargeCode
     * @return PaymentNotification
     */
    public function setChargeCode($chargeCode)
    {
        $this->chargeCode = $chargeCode;

        return $this;
    }

    /**
     * Get chargeCode
     *
     * @return integer 
     */
    public function getChargeCode()
    {
        return $this->chargeCode;
    }


    function __construct()
    {
        $this->status = self::Status_received;
        $this->setUpdateDtTm();
    }

    /**
     * Set status
     *
     * @param integer $status
     * @return PaymentNotification
     */
    public function setStatus($status)
    {
        $this->status = $status;

        return $this;
    }

    /**
     * Get status
     *
     * @return integer 
     */
    public function getStatus()
    {
        return $this->status;
    }
}
