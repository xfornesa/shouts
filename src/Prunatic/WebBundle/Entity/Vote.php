<?php

namespace Prunatic\WebBundle\Entity;

use Prunatic\WebBundle\Entity\Shout;
use Doctrine\ORM\Mapping as ORM;

/**
 * Vote
 *
 * @ORM\Table()
 * @ORM\Entity
 * @ORM\HasLifecycleCallbacks
 */
class Vote
{
    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @var Shout
     *
     * @ORM\ManyToOne(targetEntity="Shout", inversedBy="votes")
     * @ORM\JoinColumn(name="shout_id", referencedColumnName="id")
     */
    private $shout;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="created", type="datetime", nullable=true)
     */
    private $created;

    /**
     * @var integer
     *
     * @ORM\Column(name="ip", type="integer", columnDefinition="INT UNSIGNED", nullable=true)
     */
    private $ip;


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
     * Set created
     *
     * @param \DateTime $created
     * @return Vote
     */
    public function setCreated($created)
    {
        $this->created = $created;
    
        return $this;
    }

    /**
     * Get created
     *
     * @return \DateTime 
     */
    public function getCreated()
    {
        return $this->created;
    }

    /**
     * Set ip
     *
     * @param integer $ip
     * @return Vote
     */
    public function setIp($ip)
    {
        $this->ip = $ip;
    
        return $this;
    }

    /**
     * Get ip
     *
     * @return integer 
     */
    public function getIp()
    {
        return $this->ip;
    }

    /**
     * Set shout
     *
     * @param \Prunatic\WebBundle\Entity\Shout $shout
     * @return Vote
     */
    public function setShout(\Prunatic\WebBundle\Entity\Shout $shout = null)
    {
        $this->shout = $shout;
    
        return $this;
    }

    /**
     * Get shout
     *
     * @return \Prunatic\WebBundle\Entity\Shout 
     */
    public function getShout()
    {
        return $this->shout;
    }

    /**
     * @ORM\PrePersist
     */
    public function doStuffOnPrePersist()
    {
        $this->created = new \DateTime();
    }
}