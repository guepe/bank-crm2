<?php 

namespace Guepe\CrmBankBundle\Entity;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 * @ORM\Table(name="filelinked")
 */
class FileLinked 
{
	/**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

	/**
 	 * @ORM\Column(type="string", length=100)
	 */
    protected $type;

    /**
 	 * @ORM\Column(type="string", length=100)
     */
    protected $name;
	/**
	 *  @ORM\Column(type="object")
     */
	protected $filedata;

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
     * Set filedata
     *
     * @param object $filedata
     */
    public function setFiledata($filedata)
    {
        $this->filedata = $filedata;
    }

    /**
     * Get filedata
     *
     * @return object 
     */
    public function getFiledata()
    {
        return $this->filedata;
    }
}