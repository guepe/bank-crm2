<?php
namespace Guepe\CrmBankBundle\Entity;
use Doctrine\ORM\Mapping as ORM;
use Guepe\CrmBankBundle\Entity\Category;

/**
 * @ORM\Entity
 * @ORM\Table(name="category")
 */
class Category {
	/**
	 * @ORM\GeneratedValue
	 * @ORM\Id
	 * @ORM\Column(type="integer")
	 */
	private $id;

	/**
	 * @ORM\Column(type="string",length="255")
	 */ 
	private $name;
	/**
	 * @ORM\OneToMany(targetEntity="Guepe\CrmBankBundle\Entity\Category", mappedBy="parent")
	 */
	private $children;

	/**
	 * @ORM\ManyToOne(targetEntity="Guepe\CrmBankBundle\Entity\Category", inversedBy="children")
	 * @ORM\JoinColumn(name="parent_id", referencedColumnName="id")
	 */
	private $parent;

	public function __construct() {
		$this->children = new ArrayCollection();
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
	 * Add children
	 *
	 * @param Guepe\CrmBankBundle\Entity\Category $children
	 */
	public function addCategory(\Guepe\CrmBankBundle\Entity\Category $children) {
		$this->children[] = $children;
	}

	/**
	 * Get children
	 *
	 * @return Doctrine\Common\Collections\Collection 
	 */
	public function getChildren() {
		return $this->children;
	}

	/**
	 * Set parent
	 *
	 * @param Guepe\CrmBankBundle\Entity\Category $parent
	 */
	public function setParent(\Guepe\CrmBankBundle\Entity\Category $parent) {
		$this->parent = $parent;
	}

	/**
	 * Get parent
	 *
	 * @return Guepe\CrmBankBundle\Entity\Category 
	 */
	public function getParent() {
		return $this->parent;
	}
}