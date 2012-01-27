<?php

namespace Guepe\CrmBankBundle\Entity;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 * @ORM\InheritanceType("SINGLE_TABLE")
 * @ORM\DiscriminatorColumn(name="discr", type="string")
 * @ORM\DiscriminatorMap({"bankproduct" = "BankProduct","creditproduct"="CreditProduct","fiscalproduct"="FiscalProduct","savingsproduct"="SavingsProduct"})
 * @ORM\Table(name="metaproduct")
 */

class MetaProduct {
	/**
	 * @ORM\Id
	 * @ORM\Column(type="integer")
	 * @ORM\GeneratedValue(strategy="AUTO")
	 */
	protected $id;
	/**
	 * @ORM\Column(name="number",type="string", length=100)
	 */
	protected $Number;
	/**
	 * @ORM\ManyToMany(targetEntity="Category")
	 */ 
	protected $Category;
	/**
	 * @ORM\ManyToMany(targetEntity="Account", mappedBy="metaproduct")
	 */
	protected $account;
	/**
	 * @ORM\Column(name="type",type="string", length=100)
	 */
	protected $Type;

	/**
	 * @ORM\Column(name="notes",type="text",nullable=true)
	 */
	protected $Notes;
	/**
	 * @ORM\Column(name="description",type="text",nullable=true)
	 */
	protected $Description;
	/**
	 * @ORM\Column(name="reference",type="string", length=100,nullable=true)
	 */
	protected $References;
	/**
	 * @ORM\Column(name="company",type="text",nullable=true)
	 */
	protected $Company;
	/**
	 * @ORM\Column(name="taux_interet",type="smallint",nullable=true)
	 */
	protected $TauxInteret;


    public function __construct()
    {
        $this->Category = new \Doctrine\Common\Collections\ArrayCollection();
    $this->account = new \Doctrine\Common\Collections\ArrayCollection();
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
     * Set Number
     *
     * @param string $number
     */
    public function setNumber($number)
    {
        $this->Number = $number;
    }

    /**
     * Get Number
     *
     * @return string 
     */
    public function getNumber()
    {
        return $this->Number;
    }

    /**
     * Set Type
     *
     * @param string $type
     */
    public function setType($type)
    {
        $this->Type = $type;
    }

    /**
     * Get Type
     *
     * @return string 
     */
    public function getType()
    {
        return $this->Type;
    }

    /**
     * Set Notes
     *
     * @param text $notes
     */
    public function setNotes($notes)
    {
        $this->Notes = $notes;
    }

    /**
     * Get Notes
     *
     * @return text 
     */
    public function getNotes()
    {
        return $this->Notes;
    }

    /**
     * Set Description
     *
     * @param text $description
     */
    public function setDescription($description)
    {
        $this->Description = $description;
    }

    /**
     * Get Description
     *
     * @return text 
     */
    public function getDescription()
    {
        return $this->Description;
    }

    /**
     * Set References
     *
     * @param string $references
     */
    public function setReferences($references)
    {
        $this->References = $references;
    }

    /**
     * Get References
     *
     * @return string 
     */
    public function getReferences()
    {
        return $this->References;
    }

    /**
     * Set Company
     *
     * @param text $company
     */
    public function setCompany($company)
    {
        $this->Company = $company;
    }

    /**
     * Get Company
     *
     * @return text 
     */
    public function getCompany()
    {
        return $this->Company;
    }

    /**
     * Set TauxInteret
     *
     * @param smallint $tauxInteret
     */
    public function setTauxInteret($tauxInteret)
    {
        $this->TauxInteret = $tauxInteret;
    }

    /**
     * Get TauxInteret
     *
     * @return smallint 
     */
    public function getTauxInteret()
    {
        return $this->TauxInteret;
    }

    /**
     * Add Category
     *
     * @param Guepe\CrmBankBundle\Entity\Category $category
     */
    public function addCategory(\Guepe\CrmBankBundle\Entity\Category $category)
    {
        $this->Category[] = $category;
    }

    /**
     * Get Category
     *
     * @return Doctrine\Common\Collections\Collection 
     */
    public function getCategory()
    {
        return $this->Category;
    }

    /**
     * Add account
     *
     * @param Guepe\CrmBankBundle\Entity\Account $account
     */
    public function addAccount(\Guepe\CrmBankBundle\Entity\Account $account)
    {
        $this->account[] = $account;
    }

    /**
     * Get account
     *
     * @return Doctrine\Common\Collections\Collection 
     */
    public function getAccount()
    {
        return $this->account;
    }
}