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

class MetaProduct
{
	/**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

	/**
 	 * @ORM\Column(name="name",type="string", length=100)
	 */
    protected $Name;
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
 	 * @ORM\Column(name="amount",type="decimal",nullable=true)
	 */
    protected $Amount;
 	/**
 	 * @ORM\Column(name="start_date",type="date",nullable=true)
	 */
    protected $StartDate;
 	/**
 	 * @ORM\Column(name="end_date",type="date",nullable=true)
	 */
    protected $EndDate;
 	/**
 	 * @ORM\Column(name="notes",type="text",nullable=true)
	 */
    protected $Notes;
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
     * Set name
     *
     * @param string $name
     */
    public function setName($name)
    {
        $this->name = $name;
    }

    /**
     * Get name
     *
     * @return string 
     */
    public function getName()
    {
        return $this->name;
    }
    public function __construct()
    {
        $this->category = new \Doctrine\Common\Collections\ArrayCollection();
    }
    
    /**
     * Add category
     *
     * @param Guepe\CrmBankBundle\Entity\Category $category
     */
    public function addCategory(\Guepe\CrmBankBundle\Entity\Category $category)
    {
        $this->category[] = $category;
    }

    /**
     * Get category
     *
     * @return Doctrine\Common\Collections\Collection 
     */
    public function getCategory()
    {
        return $this->category;
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

    /**
     * Set type
     *
     * @param string $type
     */
    public function setType($type)
    {
        $this->type = $type;
    }

    /**
     * Get type
     *
     * @return string 
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * Set Amount
     *
     * @param decimal $amount
     */
    public function setAmount($amount)
    {
        $this->Amount = $amount;
    }

    /**
     * Get Amount
     *
     * @return decimal 
     */
    public function getAmount()
    {
        return $this->Amount;
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
}