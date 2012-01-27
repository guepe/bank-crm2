<?php

namespace Guepe\CrmBankBundle\Entity;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 */
class FiscalProduct extends MetaProduct {
	/**
	 * @var integer $id
	 */
	protected $id;
	/**
	 * @ORM\Column(name="recurrent_prime_amount",type="decimal",nullable="true")
	 */
	protected $RecurrentPrimeAmount;
	/**
	 * @ORM\Column(name="capital_terme",type="decimal",nullable="true")
	 */
	protected $CapitalTerme;
	/**
	 * @ORM\Column(name="garantee",type="string", length=100,nullable=true)
	 */
	protected $Garantee;
	/**
	 * @ORM\Column(name="payment_date",type="string",length=100,nullable="true")
	 */
	protected $PaymentDate;
	/**
	 * @ORM\Column(name="payment_deadline",type="string",length=100,nullable="true")
	 */
	protected $PaymentDeadline;
	/**
	 * @ORM\Column(name="reserve",type="decimal",nullable="true")
	 */
	protected $Reserve;
	/**
	 * @ORM\Column(name="reserve_date",type="date",nullable="true")
	 */
	protected $ReserveDate;
	/**
	 * @ORM\Column(name="start_date",type="date",nullable=true)
	 */
	protected $StartDate;
	/**
	 * @ORM\Column(name="end_date",type="date",nullable=true)
	 */
	protected $EndDate;
	public function __construct() {
		$this->category = new \Doctrine\Common\Collections\ArrayCollection();
		$this->account = new \Doctrine\Common\Collections\ArrayCollection();
	}

	/**
	 * @var string $Type
	 */
	protected $Type;

	/**
	 * @var Guepe\CrmBankBundle\Entity\Category
	 */
	protected $Category;

	/**
	 * @var Guepe\CrmBankBundle\Entity\Account
	 */
	protected $account;



	/**
	 * @var text $Notes
	 */
	protected $Notes;

	/**
	 * @var string $References
	 */
	protected $References;

	/**
	 * @var text $Company
	 */
	protected $Company;

	/**
	 * @var smallint $TauxInteret
	 */
	protected $TauxInteret;
	/**
	 * @var string $Number
	 */
	protected $Number;

	/**
	 * @var text $Description
	 */
	protected $Description;



    /**
     * Set RecurrentPrimeAmount
     *
     * @param decimal $recurrentPrimeAmount
     */
    public function setRecurrentPrimeAmount($recurrentPrimeAmount)
    {
        $this->RecurrentPrimeAmount = $recurrentPrimeAmount;
    }

    /**
     * Get RecurrentPrimeAmount
     *
     * @return decimal 
     */
    public function getRecurrentPrimeAmount()
    {
        return $this->RecurrentPrimeAmount;
    }

    /**
     * Set CapitalTerme
     *
     * @param decimal $capitalTerme
     */
    public function setCapitalTerme($capitalTerme)
    {
        $this->CapitalTerme = $capitalTerme;
    }

    /**
     * Get CapitalTerme
     *
     * @return decimal 
     */
    public function getCapitalTerme()
    {
        return $this->CapitalTerme;
    }

    /**
     * Set Garantee
     *
     * @param string $garantee
     */
    public function setGarantee($garantee)
    {
        $this->Garantee = $garantee;
    }

    /**
     * Get Garantee
     *
     * @return string 
     */
    public function getGarantee()
    {
        return $this->Garantee;
    }

    /**
     * Set PaymentDate
     *
     * @param string $paymentDate
     */
    public function setPaymentDate($paymentDate)
    {
        $this->PaymentDate = $paymentDate;
    }

    /**
     * Get PaymentDate
     *
     * @return string 
     */
    public function getPaymentDate()
    {
        return $this->PaymentDate;
    }

    /**
     * Set PaymentDeadline
     *
     * @param string $paymentDeadline
     */
    public function setPaymentDeadline($paymentDeadline)
    {
        $this->PaymentDeadline = $paymentDeadline;
    }

    /**
     * Get PaymentDeadline
     *
     * @return string 
     */
    public function getPaymentDeadline()
    {
        return $this->PaymentDeadline;
    }

    /**
     * Set Reserve
     *
     * @param decimal $reserve
     */
    public function setReserve($reserve)
    {
        $this->Reserve = $reserve;
    }

    /**
     * Get Reserve
     *
     * @return decimal 
     */
    public function getReserve()
    {
        return $this->Reserve;
    }

    /**
     * Set ReserveDate
     *
     * @param date $reserveDate
     */
    public function setReserveDate($reserveDate)
    {
        $this->ReserveDate = $reserveDate;
    }

    /**
     * Get ReserveDate
     *
     * @return date 
     */
    public function getReserveDate()
    {
        return $this->ReserveDate;
    }

    /**
     * Set StartDate
     *
     * @param date $startDate
     */
    public function setStartDate($startDate)
    {
        $this->StartDate = $startDate;
    }

    /**
     * Get StartDate
     *
     * @return date 
     */
    public function getStartDate()
    {
        return $this->StartDate;
    }

    /**
     * Set EndDate
     *
     * @param date $endDate
     */
    public function setEndDate($endDate)
    {
        $this->EndDate = $endDate;
    }

    /**
     * Get EndDate
     *
     * @return date 
     */
    public function getEndDate()
    {
        return $this->EndDate;
    }
}