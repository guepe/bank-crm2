<?php

namespace Guepe\CrmBankBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Guepe\CrmBankBundle\Entity\TestAccount
 *
 * @ORM\Table()
 * @ORM\Entity(repositoryClass="Guepe\CrmBankBundle\Entity\TestAccountRepository")
 */
class TestAccount
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
     * Get id
     *
     * @return integer 
     */
    public function getId()
    {
        return $this->id;
    }
}