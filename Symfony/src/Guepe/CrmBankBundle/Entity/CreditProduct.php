<?php

namespace Guepe\CrmBankBundle\Entity;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 */
class CreditProduct extends MetaProduct {
	/**
	 * @ORM\Column(name="garantee",type="text",nullable=true)
	 */
	protected $Garantee;
	/**
	 * @ORM\Column(name="purpose",type="text",nullable=true)
	 */ 
	protected $Purpose;
	/**
	 * @ORM\Column(name="variability",type="string",length=100,nullable=true)
	 */
	protected $Variability;
	/**
	 * @ORM\Column(name="start_date",type="date",nullable=true)
	 */
	protected $StartDate;
	/**
	 * @ORM\Column(name="end_date",type="date",nullable=true)
	 */
	protected $EndDate;
	/**
	 * @ORM\Column(name="duration",type="decimal",nullable="true")
	 */
	protected $Duration;
	/**
	 * @ORM\Column(name="recurrent_prime_amount",type="decimal",precision="10",scale="4",nullable="true")
	 */
	protected $RecurrentPrimeAmount;
	/**
	 * @ORM\Column(name="amount",type="decimal",precision="10",scale="4",nullable=true)
	 */
	protected $Amount;

	/**
	 * @ORM\Column(name="payment_date",type="string",length=100,nullable="true")
	 */
	protected $PaymentDate;
	/**
	 * @var integer $id
	 */
	protected $id;

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

	public function __construct() {
		$this->Category = new \Doctrine\Common\Collections\ArrayCollection();
		$this->account = new \Doctrine\Common\Collections\ArrayCollection();
	}

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
	 * @var text $Description
	 */
	protected $Description;
	/**
	 * @var string $Number
	 */
	protected $Number;

	/**
	 * Set Garantee
	 *
	 * @param text $garantee
	 */
	public function setGarantee($garantee) {
		$this->Garantee = $garantee;
	}

	/**
	 * Get Garantee
	 *
	 * @return text 
	 */
	public function getGarantee() {
		return $this->Garantee;
	}

	/**
	 * Set Number
	 *
	 * @param string $number
	 */
	public function setNumber($number) {
		$this->Number = $number;
	}

	/**
	 * Get Number
	 *
	 * @return string 
	 */
	public function getNumber() {
		return $this->Number;
	}

	/**
	 * Set Purpose
	 *
	 * @param text $purpose
	 */
	public function setPurpose($purpose) {
		$this->Purpose = $purpose;
	}

	/**
	 * Get Purpose
	 *
	 * @return text 
	 */
	public function getPurpose() {
		return $this->Purpose;
	}

	/**
	 * Set Variability
	 *
	 * @param string $variability
	 */
	public function setVariability($variability) {
		$this->Variability = $variability;
	}

	/**
	 * Get Variability
	 *
	 * @return string 
	 */
	public function getVariability() {
		return $this->Variability;
	}

	/**
	 * Set StartDate
	 *
	 * @param date $startDate
	 */
	public function setStartDate($startDate) {
		$this->StartDate = $startDate;
	}

	/**
	 * Get StartDate
	 *
	 * @return date 
	 */
	public function getStartDate() {
		return $this->StartDate;
	}

	/**
	 * Set EndDate
	 *
	 * @param date $endDate
	 */
	public function setEndDate($endDate) {
		$this->EndDate = $endDate;
	}

	/**
	 * Get EndDate
	 *
	 * @return date 
	 */
	public function getEndDate() {
		return $this->EndDate;
	}

	/**
	 * Set Duration
	 *
	 * @param decimal $duration
	 */
	public function setDuration($duration) {
		$this->Duration = $duration;
	}

	/**
	 * Get Duration
	 *
	 * @return decimal 
	 */
	public function getDuration() {
		return $this->Duration;
	}

	/**
	 * Set RecurrentPrimeAmount
	 *
	 * @param decimal $recurrentPrimeAmount
	 */
	public function setRecurrentPrimeAmount($recurrentPrimeAmount) {
		$this->RecurrentPrimeAmount = $recurrentPrimeAmount;
	}

	/**
	 * Get RecurrentPrimeAmount
	 *
	 * @return decimal 
	 */
	public function getRecurrentPrimeAmount() {
		return $this->RecurrentPrimeAmount;
	}

	/**
	 * Set Amount
	 *
	 * @param decimal $amount
	 */
	public function setAmount($amount) {
		$this->Amount = $amount;
	}

	/**
	 * Get Amount
	 *
	 * @return decimal 
	 */
	public function getAmount() {
		return $this->Amount;
	}

	/**
	 * Set PaymentDate
	 *
	 * @param string $paymentDate
	 */
	public function setPaymentDate($paymentDate) {
		$this->PaymentDate = $paymentDate;
	}

	/**
	 * Get PaymentDate
	 *
	 * @return string 
	 */
	public function getPaymentDate() {
		return $this->PaymentDate;
	}

}