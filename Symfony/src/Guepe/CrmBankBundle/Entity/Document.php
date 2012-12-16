<?php
// src/Guepe/CrmBankBundle/Entity/Document.php
namespace Guepe\CrmBankBundle\Entity;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity
 * @ORM\HasLifecycleCallbacks
 * @ORM\Table(name="document")
 */
class Document {
	/**
	 * @ORM\Id
	 * @ORM\Column(type="integer")
	 * @ORM\GeneratedValue(strategy="AUTO")
	 */
	public $id;
	/**
	 * @ORM\Column(type="string", length=30,nullable=true)
	 */
	protected $name;

	/**
	 * @Assert\File(maxSize="6000000")
	 */
	public $file;

	/**
	 * @ORM\Column(type="string", length=255, nullable=true)
	 */
	public $path;

    /**
     * @ORM\PrePersist()
     * @ORM\PreUpdate()
     */
    public function preUpload()
    {
        if (null !== $this->file) {
            // do whatever you want to generate a unique name
            $this->path = uniqid().'.'.$this->file->guessExtension();
        }
    }

    /**
     * @ORM\PostPersist()
     * @ORM\PostUpdate()
     */
    public function upload()
    {
        if (null === $this->file) {
            return;
        }

        // if there is an error when moving the file, an exception will
        // be automatically thrown by move(). This will properly prevent
        // the entity from being persisted to the database on error
        $this->file->move($this->getUploadRootDir(), $this->path);

        unset($this->file);
    }

    /**
     * @ORM\PostRemove()
     */
    public function removeUpload()
    {
        if ($file = $this->getAbsolutePath()) {
            unlink($file);
        }
    }	

	public function getAbsolutePath() {
		return null === $this->path ? null
				: $this->getUploadRootDir() . '/' . $this->path;
	}

	public function getWebPath() {
		return null === $this->path ? null
				: $this->getUploadDir() . '/' . $this->path;
	}

	protected function getUploadRootDir() {
		// the absolute directory path where uploaded documents should be saved
		return __DIR__ . '/../../../../web/' . $this->getUploadDir();
	}

	protected function getUploadDir() {
		// get rid of the __DIR__ so it doesn't screw when displaying uploaded doc/image in the view.
		return 'uploads/documents';
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
     * Set path
     *
     * @param string $path
     */
    public function setPath($path)
    {
        $this->path = $path;
    }

    /**
     * Get path
     *
     * @return string 
     */
    public function getPath()
    {
        return $this->path;
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
}