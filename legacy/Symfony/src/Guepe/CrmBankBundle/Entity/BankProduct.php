<?php

namespace Guepe\CrmBankBundle\Entity;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 */
class BankProduct extends MetaProduct {
	/**
	 * @var integer $id
	 */
	protected $id;
	/**
	 * @ORM\Column(name="amount",type="decimal",precision="10",scale="4",nullable=true)
	 */
	protected $Amount;

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
	 * @var date $StartDate
	 */
	protected $StartDate;

	/**
	 * @var date $EndDate
	 */
	protected $EndDate;

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
}