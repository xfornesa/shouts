<?php
/**
 * Author: Xavier
 */

namespace Prunatic\WebBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;

use Prunatic\WebBundle\Entity\Report;
use Prunatic\WebBundle\Entity\Vote;

/**
 * Shout
 *
 * @ORM\Table()
 * @ORM\Entity(repositoryClass="Prunatic\WebBundle\Entity\ShoutRepository")
 * @ORM\HasLifecycleCallbacks
 */
class Shout
{
    const MIN_REPORTS = 2;

    const STATUS_NEW = 0;
    const STATUS_APPROVED = 1;
    const STATUS_INAPPROPRIATE = 2;

    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @var string
     *
     * @ORM\Column(name="author", type="string", length=255, nullable=true)
     */
    private $author;

    /**
     * @var string
     *
     * @ORM\Column(name="email", type="string", length=255)
     */
    private $email;

    /**
     * @var string
     *
     * @ORM\Column(name="message", type="text", nullable=true)
     */
    private $message;

    /**
     * @var string
     *
     * @ORM\Column(name="image", type="string", length=255, nullable=true)
     */
    private $image;

    /**
     * @var float
     *
     * @ORM\Column(name="latitude", type="decimal", precision=11, scale=8, nullable=true)
     */
    private $latitude;

    /**
     * @var float
     *
     * @ORM\Column(name="longitude", type="decimal", precision=11, scale=8, nullable=true)
     */
    private $longitude;

    /**
     * @var ArrayCollection
     *
     * @ORM\OneToMany(targetEntity="Vote", mappedBy="shout", cascade={"persist", "remove"}, orphanRemoval=true)
     */
    private $votes;

    /**
     * @var ArrayCollection
     *
     * @ORM\OneToMany(targetEntity="Report", mappedBy="shout", cascade={"persist", "remove"}, orphanRemoval=true)
     */
    private $reports;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="created", type="datetime", nullable=true)
     */
    private $created;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->votes = new \Doctrine\Common\Collections\ArrayCollection();
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
     * Set author
     *
     * @param string $author
     * @return Shout
     */
    public function setAuthor($author)
    {
        $this->author = $author;
    
        return $this;
    }

    /**
     * Get author
     *
     * @return string 
     */
    public function getAuthor()
    {
        return $this->author;
    }

    /**
     * Set email
     *
     * @param string $email
     * @return Shout
     */
    public function setEmail($email)
    {
        $this->email = $email;
    
        return $this;
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
     * Set message
     *
     * @param string $message
     * @return Shout
     */
    public function setMessage($message)
    {
        $this->message = $message;
    
        return $this;
    }

    /**
     * Get message
     *
     * @return string 
     */
    public function getMessage()
    {
        return $this->message;
    }

    /**
     * Set image
     *
     * @param string $image
     * @return Shout
     */
    public function setImage($image)
    {
        $this->image = $image;
    
        return $this;
    }

    /**
     * Get image
     *
     * @return string 
     */
    public function getImage()
    {
        return $this->image;
    }

    /**
     * Set latitude
     *
     * @param float $latitude
     * @return Shout
     */
    public function setLatitude($latitude)
    {
        $this->latitude = $latitude;
    
        return $this;
    }

    /**
     * Get latitude
     *
     * @return float 
     */
    public function getLatitude()
    {
        return $this->latitude;
    }

    /**
     * Set longitude
     *
     * @param float $longitude
     * @return Shout
     */
    public function setLongitude($longitude)
    {
        $this->longitude = $longitude;
    
        return $this;
    }

    /**
     * Get longitude
     *
     * @return float 
     */
    public function getLongitude()
    {
        return $this->longitude;
    }

    /**
     * Add votes
     *
     * @param \Prunatic\WebBundle\Entity\Vote $votes
     * @return Shout
     */
    public function addVote(\Prunatic\WebBundle\Entity\Vote $votes)
    {
        $this->votes[] = $votes;
        $votes->setShout($this);
    
        return $this;
    }

    /**
     * Remove votes
     *
     * @param \Prunatic\WebBundle\Entity\Vote $votes
     */
    public function removeVote(\Prunatic\WebBundle\Entity\Vote $votes)
    {
        $this->votes->removeElement($votes);
    }

    /**
     * Get votes
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getVotes()
    {
        return $this->votes;
    }

    /**
     * Add reports
     *
     * @param \Prunatic\WebBundle\Entity\Report $reports
     * @return Shout
     */
    public function addReport(\Prunatic\WebBundle\Entity\Report $reports)
    {
        $this->reports[] = $reports;
        $reports->setShout($this);

        return $this;
    }

    /**
     * Remove reports
     *
     * @param \Prunatic\WebBundle\Entity\Report $reports
     */
    public function removeReport(\Prunatic\WebBundle\Entity\Report $reports)
    {
        $this->reports->removeElement($reports);
    }

    /**
     * Get reports
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getReports()
    {
        return $this->reports;
    }

    /**
     * Set created
     *
     * @param \DateTime $created
     * @return Shout
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
     * @ORM\PrePersist
     */
    public function doStuffOnPrePersist()
    {
        $this->created = new \DateTime();
    }

    /**
     * Report a shout as inappropriate
     *
     * @param integer|string $ip
     * @return Shout
     */
    public function reportInappropriate($ip)
    {
        $report = new Report();
        $report->setIp($ip);
        $this->addReport($report);

        $reports = $this->getReports();
        if (count($reports) >= self::MIN_REPORTS) {
            // TODO mark the shout as inappropriate to avoid showing again
            //$this->setStatus(self::STATUS_INAPPROPRIATE);
        }

        return $this;
    }
}