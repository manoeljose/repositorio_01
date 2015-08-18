<?php

namespace BoletoBundle\Entity;;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;

/**
 * Subscription
 *
 * @ORM\Table(name="boleto_charge",indexes={@ORM\Index(name="idx01", columns={"code"})})
 * @ORM\Entity
 * @UniqueEntity("code")
 */
class Charge
{
	/* domínio de $chargeStatus */
    const Status_Aguardando_Pagto = 0;              // Boleto emitido e pagamento ainda não efetuado
    const Status_Liberado = 1;        				// Boleto emitido e pagamento foi efetuado mas não chegou a confirmação pelo Boleto Fácil. Atualização manual
    const Status_Pago = 2;  						// Boleto emitido e pagamento já confirmado pelo Boleto Facil


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
     * @ORM\Column(name="code", type="integer", nullable=false)
     */
    private $code;          // Código único de identificação da cobrança no Boleto Fácil

    /**
     * @var \AppBundle\Entity\User
     *
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\User")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="id_user", referencedColumnName="id")
     * })
     */
    private $User;

    /**
     * @var \Date
     *
     * @ORM\Column(name="due_Date", type="date", nullable=false)
     */
    private $dueDate;     // Data de vencimento do boleto ou parcela

    /**
     * @var float
     *
     * @ORM\Column(name="amount", type="float", precision=2, scale=0, nullable=false)
     */

    private $amount;       // Valor do boleto ou da parcela no caso de cobrança parcelada

    /**
     * @var \String
     *
     * @ORM\Column(name="link", type="string", nullable=false)
     */

    private $link;         // Link para visualização/download do boleto ou carnet
	
	 /**
     * @var \Integer
     *
     * @ORM\Column(name="id_plan", type="smallint", nullable=false)
     */

    private $idPlan;       // código do plano

	 /**
     * @var \String
     *
     * @ORM\Column(name="cpf", type="string", length=11, nullable=true)
     */

    private $cpf;       // cpf do cliente
		
	 /**
     * @var \Integer
     *
     * @ORM\Column(name="charge_status", type="smallint", nullable=false)
     */

    private $chargeStatus;       // status da cobrança	
		
    /**
     * @var \DateTime
     *
     * @ORM\Column(name="update_dt_tm", type="datetime", nullable=true)
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
     * Set code
     *
     * @param integer $code
     * @return Charge
     */
    public function setCode($code)
    {
        $this->code = $code;

        return $this;
    }

    /**
     * Get code
     *
     * @return integer 
     */
    public function getCode()
    {
        return $this->code;
    }

    /**
     * Set dueDate
     *
     * @param \DateTime $dueDate
     * @return Charge
     */
    public function setDueDate($dueDate)
    {
        $this->dueDate = $dueDate;

        return $this;
    }

    /**
     * Get dueDate
     *
     * @return \DateTime 
     */
    public function getDueDate()
    {
        return $this->dueDate;
    }

    /**
     * Set amount
     *
     * @param float $amount
     * @return Charge
     */
    public function setAmount($amount)
    {
        $this->amount = $amount;

        return $this;
    }

    /**
     * Get amount
     *
     * @return float 
     */
    public function getAmount()
    {
        return $this->amount;
    }

    /**
     * Set link
     *
     * @param string $link
     * @return Charge
     */
    public function setLink($link)
    {
        $this->link = $link;

        return $this;
    }

    /**
     * Get link
     *
     * @return string 
     */
    public function getLink()
    {
        return $this->link;
    }

    /**
     * Set idPlan
     *
     * @param integer $idPlan
     * @return Charge
     */
	 
	public function setIdPlan($idPlan)
    {
        $this->idPlan = $idPlan;

        return $this;
    }

    /**
     * Get idPlan
     *
     * @return integer 
     */
    public function getIdPlan()
    {
        return $this->idPlan;
    }

    /**
     * Set updateDtTm
     *
     * @return Charge
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
     * Set User
     *
     * @param \AppBundle\Entity\User $user
     * @return Charge
     */
    public function setUser(\AppBundle\Entity\User $user = null)
    {
        $this->User = $user;

        return $this;
    }

    /**
     * Get User
     *
     * @return \AppBundle\Entity\User 
     */
    public function getUser()
    {
        return $this->User;
    }

    /**
     * Set cpf
     *
     * @param string $cpf
     * @return Charge
     */
    public function setCpf($cpf)
    {
        $this->cpf = $cpf;

        return $this;
    }

    /**
     * Get cpf
     *
     * @return string 
     */
    public function getCpf()
    {
        return $this->cpf;
    }

    /**
     * Set chargeStatus
     *
     * @param integer $chargeStatus
     * @return Charge
     */
    public function setChargeStatus($chargeStatus)
    {
        $this->chargeStatus = $chargeStatus;

        return $this;
    }

    /**
     * Get chargeStatus
     *
     * @return integer 
     */
    public function getChargeStatus()
    {
        return $this->chargeStatus;
    }

    /**
     * Get chargeStatusText
     *
     * @return integer
     */
    public function getChargeStatusText()
    {
        switch($this->chargeStatus)
        {
            case(self::Status_Aguardando_Pagto):
                return "Aguardando Pagamento";

            case(self::Status_Liberado):
                return "Liberado";

            case(self::Status_Pago):
                return "Pago";
        }
    }//end of function getChargeStatusText

    function __construct()
    {
        $this->chargeStatus = self::Status_Aguardando_Pagto;
        $this->updateDtTm = new \DateTime();
    }

    /**
     * Set id
     *
     * @param integer $id
     * @return Charge
     */
    public function setId($id)
    {
        $this->id = $id;

        return $this;
    }
}
