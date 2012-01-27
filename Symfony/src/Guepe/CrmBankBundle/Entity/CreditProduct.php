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
	 * @ORM\Column(name="number",type="string", length=100)
	 */ 
	protected $Number;
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
	 * @ORM\Column(name="recurrent_prime_amount",type="decimal",nullable="true")
	 */
	protected $RecurrentPrimeAmount;
	/**
	 * @ORM\Column(name="amount",type="decimal",nullable=true)
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
	 * @var string $Name
	 */
	protected $Name;

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
	 * Set garantee
	 *
	 * @param string $garantee
	 */
	public function setGarantee($garantee) {
		$this->Garantee = $garantee;
	}

	/**
	 * Get garantee
	 *
	 * @return string 
	 */
	public function getGarantee() {
		return $this->Garantee;
	}

	/**
	 * Set Name
	 *
	 * @param string $name
	 */
	public function setName($name) {
		$this->Name = $name;
	}

	/**
	 * Get Name
	 *
	 * @return string 
	 */
	public function getName() {
		return $this->Name;
	}

	/**
	 * Set Type
	 *
	 * @param string $type
	 */
	public function setType($type) {
		$this->Type = $type;
	}

	/**
	 * Get Type
	 *
	 * @return string 
	 */
	public function getType() {
		return $this->Type;
	}

	/**
	 * Add Category
	 *
	 * @param Guepe\CrmBankBundle\Entity\Category $category
	 */
	public function addCategory(\Guepe\CrmBankBundle\Entity\Category $category) {
		$this->Category[] = $category;
	}

	/**
	 * Get Category
	 *
	 * @return Doctrine\Common\Collections\Collection 
	 */
	public function getCategory() {
		return $this->Category;
	}

	/**
	 * Add account
	 *
	 * @param Guepe\CrmBankBundle\Entity\Account $account
	 */
	public function addAccount(\Guepe\CrmBankBundle\Entity\Account $account) {
		$this->account[] = $account;
	}

	/**
	 * Get account
	 *
	 * @return Doctrine\Common\Collections\Collection 
	 */
	public function getAccount() {
		return $this->account;
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
	 * Set Notes
	 *
	 * @param text $notes
	 */
	public function setNotes($notes) {
		$this->Notes = $notes;
	}

	/**
	 * Get Notes
	 *
	 * @return text 
	 */
	public function getNotes() {
		return $this->Notes;
	}

	/**
	 * Set References
	 *
	 * @param string $references
	 */
	public function setReferences($references) {
		$this->References = $references;
	}

	/**
	 * Get References
	 *
	 * @return string 
	 */
	public function getReferences() {
		return $this->References;
	}

	/**
	 * Set Company
	 *
	 * @param text $company
	 */
	public function setCompany($company) {
		$this->Company = $company;
	}

	/**
	 * Get Company
	 *
	 * @return text 
	 */
	public function getCompany() {
		return $this->Company;
	}

	/**
	 * Set TauxInteret
	 *
	 * @param smallint $tauxInteret
	 */
	public function setTauxInteret($tauxInteret) {
		$this->TauxInteret = $tauxInteret;
	}

	/**
	 * Get TauxInteret
	 *
	 * @return smallint 
	 */
	public function getTauxInteret() {
		return $this->TauxInteret;
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
	 * Set GaranteeStart
	 *
	 * @param date $garanteeStart
	 */
	public function setGaranteeStart($garanteeStart) {
		$this->GaranteeStart = $garanteeStart;
	}

	/**
	 * Get GaranteeStart
	 *
	 * @return date 
	 */
	public function getGaranteeStart() {
		return $this->GaranteeStart;
	}

	/**
	 * Set Variability
	 *
	 * @param decimal $variability
	 */
	public function setVariability($variability) {
		$this->Variability = $variability;
	}

	/**
	 * Get Variability
	 *
	 * @return decimal 
	 */
	public function getVariability() {
		return $this->Variability;
	}
	/**
	 * @var text $Description
	 */
	protected $Description;

	/**
	 * Set Description
	 *
	 * @param text $description
	 */
	public function setDescription($description) {
		$this->Description = $description;
	}

	/**
	 * Get Description
	 *
	 * @return text 
	 */
	public function getDescription() {
		return $this->Description;
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