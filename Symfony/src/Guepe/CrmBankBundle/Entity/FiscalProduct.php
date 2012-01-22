<?php 

namespace Guepe\CrmBankBundle\Entity;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 */
class  FiscalProduct extends MetaProduct
{
    /**
     * @var integer $id
     */
    protected $id;
    /**
 	 * @ORM\Column(name="revision_date",type="date",nullable="true")
	 */
    protected $RevisionDate;
   	 /**
 	 * @ORM\Column(name="unique_prime_amount",type="decimal",nullable="true")
	 */
    protected $UniquePrimeAmount;
 	/**
 	 * @ORM\Column(name="unique_prime_date",type="date",nullable="true")
	 */
    protected $UniquePrimeDate;
     /**
 	 * @ORM\Column(name="recurrent_prime_amount",type="decimal",nullable="true")
	 */
    protected $RecurrentPrimeAmount;
     /**
 	 * @ORM\Column(name="capital_terme",type="decimal",nullable="true")
	 */
    protected $CapitalTerme;
      /**
 	 * @ORM\Column(name="reserve",type="decimal",nullable="true")
	 */
    protected $Reserve;
 	/**
 	 * @ORM\Column(name="reserve_date",type="date",nullable="true")
	 */
    protected $ReserveDate;    

    public function __construct()
    {
        $this->category = new \Doctrine\Common\Collections\ArrayCollection();
    $this->account = new \Doctrine\Common\Collections\ArrayCollection();
    }
  
    

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


    /**
     * Set Name
     *
     * @param string $name
     */
    public function setName($name)
    {
        $this->Name = $name;
    }

    /**
     * Get Name
     *
     * @return string 
     */
    public function getName()
    {
        return $this->Name;
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
    /**
     * @var decimal $Amount
     */
    protected $Amount;

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

    /**
     * Set RevisionDate
     *
     * @param date $revisionDate
     */
    public function setRevisionDate($revisionDate)
    {
        $this->RevisionDate = $revisionDate;
    }

    /**
     * Get RevisionDate
     *
     * @return date 
     */
    public function getRevisionDate()
    {
        return $this->RevisionDate;
    }

    /**
     * Set UniquePrimeAmount
     *
     * @param decimal $uniquePrimeAmount
     */
    public function setUniquePrimeAmount($uniquePrimeAmount)
    {
        $this->UniquePrimeAmount = $uniquePrimeAmount;
    }

    /**
     * Get UniquePrimeAmount
     *
     * @return decimal 
     */
    public function getUniquePrimeAmount()
    {
        return $this->UniquePrimeAmount;
    }

    /**
     * Set UniquePrimeDate
     *
     * @param date $uniquePrimeDate
     */
    public function setUniquePrimeDate($uniquePrimeDate)
    {
        $this->UniquePrimeDate = $uniquePrimeDate;
    }

    /**
     * Get UniquePrimeDate
     *
     * @return date 
     */
    public function getUniquePrimeDate()
    {
        return $this->UniquePrimeDate;
    }

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
}