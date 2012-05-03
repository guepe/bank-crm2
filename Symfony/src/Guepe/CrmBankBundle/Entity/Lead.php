<?php

namespace Guepe\CrmBankBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Guepe\CrmBankBundle\Entity\Lead
 *
 * @ORM\Table()
 * @ORM\Entity
 */
class Lead
{
    /**
     * @var integer $id
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @var string $name
     *
     * @ORM\Column(name="name", type="string", length=100)
     */
    private $name;
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
     * Set company_statut
     *
     * @param string $companyStatut
     */
    public function setCompanyStatut($companyStatut)
    {
        $this->company_statut = $companyStatut;
    }

    /**
     * Get company_statut
     *
     * @return string 
     */
    public function getCompanyStatut()
    {
        return $this->company_statut;
    }

    /**
     * Set other_bank
     *
     * @param string $otherBank
     */
    public function setOtherBank($otherBank)
    {
        $this->other_bank = $otherBank;
    }

    /**
     * Get other_bank
     *
     * @return string 
     */
    public function getOtherBank()
    {
        return $this->other_bank;
    }

    /**
     * Set notes
     *
     * @param text $notes
     */
    public function setNotes($notes)
    {
        $this->notes = $notes;
    }

    /**
     * Get notes
     *
     * @return text 
     */
    public function getNotes()
    {
        return $this->notes;
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
     * Set starting_date
     *
     * @param date $startingDate
     */
    public function setStartingDate($startingDate)
    {
        $this->starting_date = $startingDate;
    }

    /**
     * Get starting_date
     *
     * @return date 
     */
    public function getStartingDate()
    {
        return $this->starting_date;
    }
}