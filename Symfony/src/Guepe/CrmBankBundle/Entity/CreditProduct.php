<?php 

namespace Guepe\CrmBankBundle\Entity;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 */
class CreditProduct extends MetaProduct
{
	/**
     * @ORM\Column(type="string", length=100)
     */
    protected $garantee;
    
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

    public function __construct()
    {
        $this->Category = new \Doctrine\Common\Collections\ArrayCollection();
    $this->account = new \Doctrine\Common\Collections\ArrayCollection();
    }
    
    /**
     * Set garantee
     *
     * @param string $garantee
     */
    public function setGarantee($garantee)
    {
        $this->garantee = $garantee;
    }

    /**
     * Get garantee
     *
     * @return string 
     */
    public function getGarantee()
    {
        return $this->garantee;
    }

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
}