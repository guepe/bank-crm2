<?php 
// src/Guepe/CrmBankBundle/Entity/Contact.php

namespace Guepe\CrmBankBundle\Entity;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;


/**
 * @ORM\Entity
 * @ORM\Table(name="contact")
 */
class Contact
{
	/**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;
	/**
 	 * @ORM\Column(type="string", length=100,nullable=true)
	 */
    protected $titre;
	/**
 	 * @ORM\Column(type="string", length=100,nullable=true)
	 */
    protected $firstname;
	/**
	 * @ORM\ManyToMany(targetEntity="Account", mappedBy="contacts")
     */    
    protected $accounts;
	/**
	 * @ORM\Column(type="string", length=100)
	 * @Assert\NotBlank()
	 */
    protected $lastname;
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
	 * @ORM\Column(type="string", length=150,nullable=true)
     */
// TODO: check the email format  
  
    protected $email;
    /**
	 * @ORM\Column(type="string", length=100,nullable=true)
     */
    protected $phone;
        /**
	 * @ORM\Column(type="string", length=100,nullable=true)
     */
    protected $phone2;
    /**
	 * @ORM\Column(type="string", length=16,nullable=true)
     */
    protected $gsm;
    /**
	 * @ORM\Column(type="string", length=16,nullable=true)
     */
    protected $birthplace;
    /**
	 * @ORM\Column(type="date",nullable=true)
     */
    protected $birthdate;
    /**
	 * @ORM\Column(type="string", length=100,nullable=true)
     */
    protected $eid;
    /**
	 * @ORM\Column(type="string", length=100,nullable=true)
     */
    protected $niss;
    /**
	 * @ORM\Column(type="integer",nullable=true)
     */
    protected $marital_status;
    /**
	 * @ORM\Column(type="integer",nullable=true)
     */
    protected $income_amount;
    /**
	 * @ORM\Column(type="string", length=100,nullable=true)
     */
    protected $income_recurence;
    /**
	 * @ORM\Column(type="string",nullable=true)
     */
    protected $income_date;
// TODO:   * lien avec �poux, soci�t�... si un leads ou un compte client existe d�j�
// TODO:   * si pas d'encodage existant pr�voir un champs pour indiquer le nom et pr�nom
	/**
	 * @ORM\Column(type="integer",nullable=true)
     */
    protected $charged_people;    
    

    //TODO:   * Enfants � charge (nombre, nom pr�nom et dates de naissance)
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
     * Set firstname
     *
     * @param string $firstname
     */
    public function setFirstname($firstname)
    {
        $this->firstname = $firstname;
    }

    /**
     * Get firstname
     *
     * @return string 
     */
    public function getFirstname()
    {
        return $this->firstname;
    }

    /**
     * Set lastname
     *
     * @param string $lastname
     */
    public function setLastname($lastname)
    {
        $this->lastname = $lastname;
    }

    /**
     * Get lastname
     *
     * @return string 
     */
    public function getLastname()
    {
        return $this->lastname;
    }

    /**
     * Set street_num
     *
     * @param string $streetNum
     */
    public function setStreetNum($streetNum)
    {
        $this->street_num = $streetNum;
    }

    /**
     * Get street_num
     *
     * @return string 
     */
    public function getStreetNum()
    {
        return $this->street_num;
    }

    /**
     * Set email
     *
     * @param string $email
     */
    public function setEmail($email)
    {
        $this->email = $email;
    }

    /**
     * Get email
     *
     * @return string 
     */
    public function getEmail()
    {
        return $this->email;
    }

    /**
     * Set phone
     *
     * @param string $phone
     */
    public function setPhone($phone)
    {
        $this->phone = $phone;
    }

    /**
     * Get phone
     *
     * @return string 
     */
    public function getPhone()
    {
        return $this->phone;
    }

    /**
     * Set gsm
     *
     * @param string $gsm
     */
    public function setGsm($gsm)
    {
        $this->gsm = $gsm;
    }

    /**
     * Get gsm
     *
     * @return string 
     */
    public function getGsm()
    {
        return $this->gsm;
    }

    /**
     * Set birthdate
     *
     * @param string $birthdate
     */
    public function setBirthdate($birthdate)
    {
        $this->birthdate = $birthdate;
    }

    /**
     * Get birthdate
     *
     * @return string 
     */
    public function getBirthdate()
    {
        return $this->birthdate;
    }

    /**
     * Set birthplace
     *
     * @param date $birthplace
     */
    public function setBirthplace($birthplace)
    {
        $this->birthplace = $birthplace;
    }

    /**
     * Get birthplace
     *
     * @return date 
     */
    public function getBirthplace()
    {
        return $this->birthplace;
    }

    /**
     * Set eid
     *
     * @param string $eid
     */
    public function setEid($eid)
    {
        $this->eid = $eid;
    }

    /**
     * Get eid
     *
     * @return string 
     */
    public function getEid()
    {
        return $this->eid;
    }

    /**
     * Set niss
     *
     * @param integer $niss
     */
    public function setNiss($niss)
    {
        $this->niss = $niss;
    }

    /**
     * Get niss
     *
     * @return integer 
     */
    public function getNiss()
    {
        return $this->niss;
    }

    /**
     * Set marital_status
     *
     * @param integer $maritalStatus
     */
    public function setMaritalStatus($maritalStatus)
    {
        $this->marital_status = $maritalStatus;
    }

    /**
     * Get marital_status
     *
     * @return integer 
     */
    public function getMaritalStatus()
    {
        return $this->marital_status;
    }

    /**
     * Set income_amount
     *
     * @param integer $incomeAmount
     */
    public function setIncomeAmount($incomeAmount)
    {
        $this->income_amount = $incomeAmount;
    }

    /**
     * Get income_amount
     *
     * @return integer 
     */
    public function getIncomeAmount()
    {
        return $this->income_amount;
    }

    /**
     * Set income_recurence
     *
     * @param string $incomeRecurence
     */
    public function setIncomeRecurence($incomeRecurence)
    {
        $this->income_recurence = $incomeRecurence;
    }

    /**
     * Get income_recurence
     *
     * @return string 
     */
    public function getIncomeRecurence()
    {
        return $this->income_recurence;
    }

    /**
     * Set income_date
     *
     * @param string $incomeDate
     */
    public function setIncomeDate($incomeDate)
    {
        $this->income_date = $incomeDate;
    }

    /**
     * Get income_date
     *
     * @return string 
     */
    public function getIncomeDate()
    {
        return $this->income_date;
    }

    /**
     * Set charged_people
     *
     * @param integer $chargedPeople
     */
    public function setChargedPeople($chargedPeople)
    {
        $this->charged_people = $chargedPeople;
    }

    /**
     * Get charged_people
     *
     * @return integer 
     */
    public function getChargedPeople()
    {
        return $this->charged_people;
    }
    public function __construct()
    {
        $this->files = new \Doctrine\Common\Collections\ArrayCollection();
        $this->accounts = new \Doctrine\Common\Collections\ArrayCollection();
        
    }
    
    /**
     * Add files
     *
     * @param Guepe\CrmBankBundle\Entity\FileLinked $files
     */
    public function addFileLinked(\Guepe\CrmBankBundle\Entity\FileLinked $files)
    {
        $this->files[] = $files;
    }

    /**
     * Get files
     *
     * @return Doctrine\Common\Collections\Collection 
     */
    public function getFiles()
    {
        return $this->files;
    }

    /**
     * Set city
     *
     * @param string $city
     */
    public function setCity($city)
    {
        $this->city = $city;
    }

    /**
     * Get city
     *
     * @return string 
     */
    public function getCity()
    {
        return $this->city;
    }

    /**
     * Set zip
     *
     * @param string $zip
     */
    public function setZip($zip)
    {
        $this->zip = $zip;
    }

    /**
     * Get zip
     *
     * @return string 
     */
    public function getZip()
    {
        return $this->zip;
    }

    /**
     * Set country
     *
     * @param string $country
     */
    public function setCountry($country)
    {
        $this->country = $country;
    }

    /**
     * Get country
     *
     * @return string 
     */
    public function getCountry()
    {
        return $this->country;
    }

    /**
     * Add accounts
     *
     * @param Guepe\CrmBankBundle\Entity\Account $accounts
     */
    public function addAccount(\Guepe\CrmBankBundle\Entity\Account $accounts)
    {
        $this->accounts[] = $accounts;
    }

    /**
     * Get accounts
     *
     * @return Doctrine\Common\Collections\Collection 
     */
    public function getAccounts()
    {
        return $this->accounts;
    }

    /**
     * Set titre
     *
     * @param string $titre
     */
    public function setTitre($titre)
    {
        $this->titre = $titre;
    }

    /**
     * Get titre
     *
     * @return string 
     */
    public function getTitre()
    {
        return $this->titre;
    }

    /**
     * Set phone2
     *
     * @param string $phone2
     */
    public function setPhone2($phone2)
    {
        $this->phone2 = $phone2;
    }

    /**
     * Get phone2
     *
     * @return string 
     */
    public function getPhone2()
    {
        return $this->phone2;
    }
}