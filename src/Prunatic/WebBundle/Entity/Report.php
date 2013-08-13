<?php
/**
 * Author: Xavier
 */

namespace Prunatic\WebBundle\Entity;

use Prunatic\WebBundle\Entity\Shout;
use Doctrine\ORM\Mapping as ORM;

/**
 * Report
 *
 * @ORM\Table()
 * @ORM\Entity
 * @ORM\HasLifecycleCallbacks
 */
class Report
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
     * @ORM\ManyToOne(targetEntity="Shout", inversedBy="reports")
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
     * Constructor
     */
    public function __construct($ip = null, $created = null)
    {
        if (!is_null($ip))
            $this->setIp($ip);
        if (!is_null($created))
            $this->setCreated($created);
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
     * Set created
     *
     * @param \DateTime $created
     * @return Report
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
     * @param integer|string $ip
     * @return Report
     */
    public function setIp($ip)
    {
        if (!is_numeric($ip) && is_string($ip)) {
            $ip = ip2long($ip);
        }
        $this->ip = $ip;
    
        return $this;
    }

    /**
     * Get ip
     *
     * @return string
     */
    public function getIp()
    {
        if (!is_null($this->ip)) {
            $ip = long2ip($this->ip);
        }

        return $ip;
    }

    /**
     * Set shout
     *
     * @param Shout $shout
     * @return Report
     */
    public function setShout(Shout $shout = null)
    {
        $this->shout = $shout;
    
        return $this;
    }

    /**
     * Get shout
     *
     * @return Shout
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