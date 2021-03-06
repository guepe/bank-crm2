<?php
// src/Guepe/CrmBankBundle/Entity/Account.php

namespace Guepe\CrmBankBundle\Entity;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 * @ORM\Table(name="account")
 */
class Account {
	/**
	 * @ORM\Id
	 * @ORM\Column(type="integer")
	 * @ORM\GeneratedValue(strategy="AUTO")
	 */
	protected $id;
	/**
	 * @ORM\Column(type="string", length=100)
	 */
	protected $name;
	/**
	 * @ORM\ManyToMany(targetEntity="Contact", inversedBy="accounts")
	 * @ORM\JoinTable(name="accounts_contacts")
	 */ 
	protected $contacts;
    /**
     * @ORM\ManyToMany(targetEntity="Document", inversedBy="accounts")
     * @ORM\JoinTable(name="accounts_document")
     */
    protected $document;
	/**
	 * @ORM\Column(type="string", length=100,nullable=true)
	 */
	protected $street_num;
	/**
	 * @ORM\Column(type="string", length=100,nullable=true)
	 */
	protected $city;
	/**
	 * @ORM\Column(type="string", length=100,nullable=true)
	 */
	protected $zip;
	/**
	 * @ORM\Column(type="string", length=100,nullable=true)
	 */
	protected $country;
	/**
	 * @ORM\Column(type="string", length=100,nullable=true)
	 */
	protected $company_statut;
	/**
	 * @ORM\Column(type="string",length=100,nullable=true)
	 */
	protected $other_bank;
	/**
	 * @ORM\Column(type="text",nullable=true)
	 */
	protected $notes;
	/**
	 * @ORM\Column(type="string",length=100,nullable=true)
	 */
	protected $type;
	/**
	 * @ORM\Column(type="date",nullable=true)
	 */
	protected $starting_date;

	/**
	 * @ORM\ManyToMany(targetEntity="BankProduct")
	 */
	protected $bankproduct;

	/**
	 * @ORM\ManyToMany(targetEntity="CreditProduct")
	 */
	protected $creditproduct;
	/**
	 * @ORM\ManyToMany(targetEntity="FiscalProduct")
	 */
	protected $fiscalproduct;

	/**
	 * @ORM\ManyToMany(targetEntity="SavingsProduct")
	 */
	protected $savingsproduct;

	public function __construct() {
		$this->contacts = new \Doctrine\Common\Collections\ArrayCollection();
		$this->bankproduct = new \Doctrine\Common\Collections\ArrayCollection();
		$this->creditproduct = new \Doctrine\Common\Collections\ArrayCollection();
		$this->fiscalproduct = new \Doctrine\Common\Collections\ArrayCollection();
		$this->savingsproduct = new \Doctrine\Common\Collections\ArrayCollection();

	}

	/**
	 * Get id
	 *
	 * @return integer 
	 */
	public function getId() {
		return $this->id;
	}

	/**
	 * Set name
	 *
	 * @param string $name
	 */
	public function setName($name) {
		$this->name = $name;
	}

	/**
	 * Get name
	 *
	 * @return string 
	 */
	public function getName() {
		return $this->name;
	}

	/**
	 * Set company_statut
	 *
	 * @param string $companyStatut
	 */
	public function setCompanyStatut($companyStatut) {
		$this->company_statut = $companyStatut;
	}

	/**
	 * Get company_statut
	 *
	 * @return string 
	 */
	public function getCompanyStatut() {
		return $this->company_statut;
	}

	/**
	 * Set other_bank
	 *
	 * @param string $otherBank
	 */
	public function setOtherBank($otherBank) {
		$this->other_bank = $otherBank;
	}

	/**
	 * Get other_bank
	 *
	 * @return string 
	 */
	public function getOtherBank() {
		return $this->other_bank;
	}

	/**
	 * Set notes
	 *
	 * @param text $notes
	 */
	public function setNotes($notes) {
		$this->notes = $notes;
	}

	/**
	 * Get notes
	 *
	 * @return text 
	 */
	public function getNotes() {
		return $this->notes;
	}

	/**
	 * Set type
	 *
	 * @param string $type
	 */
	public function setType($type) {
		$this->type = $type;
	}

	/**
	 * Get type
	 *
	 * @return string 
	 */
	public function getType() {
		return $this->type;
	}

	/**
	 * Set starting_date
	 *
	 * @param date $startingDate
	 */
	public function setStartingDate($startingDate) {
		$this->starting_date = $startingDate;
	}

	/**
	 * Get starting_date
	 *
	 * @return date 
	 */
	public function getStartingDate() {
		return $this->starting_date;
	}

	/**
	 * Add contacts
	 *
	 * @param Guepe\CrmBankBundle\Entity\Contact $contacts
	 */
	public function addContact(\Guepe\CrmBankBundle\Entity\Contact $contacts) {
		$this->contacts[] = $contacts;
	}

	/**
	 * Get contacts
	 *
	 * @return Doctrine\Common\Collections\Collection 
	 */
	public function getContacts() {
		return $this->contacts;
	}

	/**
	 * Add bankproduct
	 *
	 * @param Guepe\CrmBankBundle\Entity\BankProduct $bankproduct
	 */
	public function addBankProduct(
			\Guepe\CrmBankBundle\Entity\BankProduct $bankproduct) {
		$this->bankproduct[] = $bankproduct;
	}

	/**
	 * Get bankproduct
	 *
	 * @return Doctrine\Common\Collections\Collection 
	 */
	public function getBankproduct() {
		return $this->bankproduct;
	}

	/**
	 * Add creditproduct
	 *
	 * @param Guepe\CrmBankBundle\Entity\CreditProduct $creditproduct
	 */
	public function addCreditProduct(
			\Guepe\CrmBankBundle\Entity\CreditProduct $creditproduct) {
		$this->creditproduct[] = $creditproduct;
	}

	/**
	 * Get creditproduct
	 *
	 * @return Doctrine\Common\Collections\Collection 
	 */
	public function getCreditproduct() {
		return $this->creditproduct;
	}

	/**
	 * Set street_num
	 *
	 * @param string $streetNum
	 */
	public function setStreetNum($streetNum) {
		$this->street_num = $streetNum;
	}

	/**
	 * Get street_num
	 *
	 * @return string 
	 */
	public function getStreetNum() {
		return $this->street_num;
	}

	/**
	 * Set city
	 *
	 * @param string $city
	 */
	public function setCity($city) {
		$this->city = $city;
	}

	/**
	 * Get city
	 *
	 * @return string 
	 */
	public function getCity() {
		return $this->city;
	}

	/**
	 * Set zip
	 *
	 * @param string $zip
	 */
	public function setZip($zip) {
		$this->zip = $zip;
	}

	/**
	 * Get zip
	 *
	 * @return string 
	 */
	public function getZip() {
		return $this->zip;
	}

	/**
	 * Set country
	 *
	 * @param string $country
	 */
	public function setCountry($country) {
		$this->country = $country;
	}

	/**
	 * Get country
	 *
	 * @return string 
	 */
	public function getCountry() {
		return $this->country;
	}

	/**
	 * Add fiscalproduct
	 *
	 * @param Guepe\CrmBankBundle\Entity\FiscalProduct $fiscalproduct
	 */
	public function addFiscalProduct(
			\Guepe\CrmBankBundle\Entity\FiscalProduct $fiscalproduct) {
		$this->fiscalproduct[] = $fiscalproduct;
	}

	/**
	 * Get fiscalproduct
	 *
	 * @return Doctrine\Common\Collections\Collection 
	 */
	public function getFiscalproduct() {
		return $this->fiscalproduct;
	}

	/**
	 * Add savingsproduct
	 *
	 * @param Guepe\CrmBankBundle\Entity\SavingsProduct $savingsproduct
	 */
	public function addSavingsProduct(
			\Guepe\CrmBankBundle\Entity\SavingsProduct $savingsproduct) {
		$this->savingsproduct[] = $savingsproduct;
	}

	/**
	 * Get savingsproduct
	 *
	 * @return Doctrine\Common\Collections\Collection 
	 */
	public function getSavingsproduct() {
		return $this->savingsproduct;
	}

    /**
     * Set eIdDocument
     *
     * @param Guepe\CrmBankBundle\Entity\Documents $eIdDocument
     */
    public function setEIdDocument(\Guepe\CrmBankBundle\Entity\Documents $eIdDocument)
    {
        $this->eIdDocument = $eIdDocument;
    }

    /**
     * Get eIdDocument
     *
     * @return Guepe\CrmBankBundle\Entity\Documents 
     */
    public function getEIdDocument()
    {
        return $this->eIdDocument;
    }

    /**
     * Add document
     *
     * @param Guepe\CrmBankBundle\Entity\Document $document
     */
    public function addDocument(\Guepe\CrmBankBundle\Entity\Document $document)
    {
        $this->document[] = $document;
    }

    /**
     * Get document
     *
     * @return Guepe\CrmBankBundle\Entity\Documents 
     */
    public function getDocument()
    {
        return $this->document;
    }
}
