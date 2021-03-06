<?php
/**
 * Author: Xavier
 */

namespace Prunatic\WebBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use \InvalidArgumentException as InvalidArgumentException;
use Prunatic\WebBundle\Service\NotificationManager;
use \Swift_Mailer as Swift_Mailer;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface as UrlGeneratorInterface;
use Symfony\Component\Validator\Constraints as Assert;

use Prunatic\WebBundle\Entity\OperationNotPermittedException;
use Prunatic\WebBundle\Entity\Report;
use Prunatic\WebBundle\Entity\Vote;
use Prunatic\WebBundle\Entity\Point;
use Prunatic\WebBundle\Entity\City;
use Prunatic\WebBundle\Entity\Province;
use Prunatic\WebBundle\Entity\Country;

/**
 * Shout
 *
 * @ORM\Table(indexes={
 *     @ORM\Index(name="idx_shout_status", columns={"status"}),
 *     @ORM\Index(name="idx_shout_created", columns={"created"}),
 *     @ORM\Index(name="idx_shout_total_votes", columns={"total_votes"}),
 *     @ORM\Index(name="idx_shout_point", columns={"point"}),
 *     @ORM\Index(name="idx_shout_token", columns={"token"})
 * })
 * @ORM\Entity(repositoryClass="Prunatic\WebBundle\Entity\ShoutRepository")
 * @Gedmo\Uploadable(allowOverwrite=true, filenameGenerator="SHA1")
 */
class Shout
{
    const MIN_REPORTS = 2;

    const STATUS_NEW = 'new';
    const STATUS_APPROVED = 'approved';
    const STATUS_INAPPROPRIATE = 'inappropriate';

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
     * @ORM\Column(name="status", type="string", columnDefinition="ENUM('new', 'approved', 'inappropriate')")
     */
    private $status;

    /**
     * @var string
     *
     * @ORM\Column(name="author", type="string", length=255)
     * @Assert\NotBlank()
     * @Assert\Length(min="3", max="250")
     */
    private $author;

    /**
     * @var string
     *
     * @ORM\Column(name="email", type="string", length=255)
     * @Assert\NotBlank()
     * @Assert\Length(min="6", max="250")
     * @Assert\Email(checkMX=false, checkHost=false)
     */
    private $email;

    /**
     * @var string
     *
     * @ORM\Column(name="message", type="string", length=255)
     * @Assert\NotBlank()
     * @Assert\Length(min="3", max="250")
     *
     */
    private $message;

    /**
     * @var string
     *
     * @ORM\Column(name="image", type="string", length=255, nullable=true)
     * @Gedmo\UploadableFilePath
     */
    private $image;

    /**
     * @var Point
     *
     * @ORM\Column(name="point", type="point", nullable=true)
     */
    private $point;

    /**
     * @var ArrayCollection
     *
     * @ORM\OneToMany(targetEntity="Vote", mappedBy="shout", cascade={"persist", "remove"}, orphanRemoval=true)
     */
    private $votes;

    /**
     * @var integer
     *
     * @ORM\Column(name="total_votes", type="integer")
     */
    private $totalVotes;

    /**
     * @var ArrayCollection
     *
     * @ORM\OneToMany(targetEntity="Report", mappedBy="shout", cascade={"persist", "remove"}, orphanRemoval=true)
     */
    private $reports;

    /**
     * @var string
     *
     * @ORM\Column(name="token", type="string", length=255, unique=true, nullable=true)
     */
    private $token;

    /**
     * @var City
     *
     * @ORM\ManyToOne(targetEntity="City")
     * @ORM\JoinColumn(name="city_id", referencedColumnName="id", nullable=true)
     */
    private $city;

    /**
     * @var \DateTime
     *
     * @Gedmo\Timestampable(on="create")
     * @ORM\Column(name="created", type="datetime", nullable=true)
     */
    private $created;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->status = self::STATUS_NEW;
        $this->reports = new ArrayCollection();
        $this->votes = new ArrayCollection();
        $this->totalVotes = 0;
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
     * Set point
     *
     * @param point $point
     * @return Shout
     */
    public function setPoint($point)
    {
        $this->point = $point;

        return $this;
    }

    /**
     * Get point
     *
     * @return point
     */
    public function getPoint()
    {
        return $this->point;
    }

    /**
     * Add votes
     *
     * @param Vote $votes
     * @throws DuplicateException
     * @return Shout
     */
    public function addVote(Vote $votes)
    {
        // avoid more than one vote from the same IP
        $ip = $votes->getIp();
        if ($this->hasBeenVotedFromIp($ip)) {
            throw new DuplicateException(sprintf("Ja s'ha votat el crit des d'aquesta IP origen %s", $ip));
        }

        $this->votes[] = $votes;
        $this->totalVotes++;
        $votes->setShout($this);

        return $this;
    }

    /**
     * Return if the shout has been voted previously from a given IP
     *
     * @param string $ip
     * @return bool
     */
    public function hasBeenVotedFromIp($ip)
    {
        if (empty($this->votes))
            return false;
        $ip = ip2long($ip);
        /** @var Vote $vote */
        foreach($this->votes as $vote) {
            $voteIp = $vote->getIp();
            $voteIp = ip2long($voteIp);
            if ($ip === $voteIp) {
                return true;
            }
        }

        return false;
    }

    /**
     * Remove votes
     *
     * @param Vote $votes
     * @return Shout
     */
    public function removeVote(Vote $votes)
    {
        $this->votes->removeElement($votes);
        $this->totalVotes--;

        return $this;
    }

    /**
     * Get votes
     *
     * @return Collection
     */
    public function getVotes()
    {
        return $this->votes;
    }

    /**
     * Add reports
     *
     * @param Report $reports
     * @throws DuplicateException
     * @return Shout
     */
    public function addReport(Report $reports)
    {
        // avoid more than one report with the same IP
        $ip = $reports->getIp();
        if ($this->hasBeenReportedFromIp($ip)) {
            throw new DuplicateException(sprintf("Ja s'ha reportat el crit des d'aquesta IP origen %s", $ip));
        }
        $this->reports[] = $reports;
        $reports->setShout($this);

        return $this;
    }

    /**
     * Return if the shout has been reported previously from a given IP
     *
     * @param string $ip
     * @return bool
     */
    public function hasBeenReportedFromIp($ip)
    {
        if (empty($this->reports))
            return false;
        $ip = ip2long($ip);
        /** @var Report $report */
        foreach($this->reports as $report) {
            $reportIp = $report->getIp();
            $reportIp = ip2long($reportIp);
            if ($ip === $reportIp) {
                return true;
            }
        }

        return false;
    }

    /**
     * Remove reports
     *
     * @param Report $reports
     * @return Shout
     */
    public function removeReport(Report $reports)
    {
        $this->reports->removeElement($reports);

        return $this;
    }

    /**
     * Get reports
     *
     * @return Collection
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
            $this->setStatus(self::STATUS_INAPPROPRIATE);
        }

        return $this;
    }

    /**
     * Add a vote to the shout
     *
     * @param integer|string $ip
     * @return Shout
     */
    public function vote($ip)
    {
        $vote = new Vote();
        $vote->setIp($ip);
        $this->addVote($vote);

        return $this;
    }

    /**
     * Approve a shout making it visible
     *
     * @throws OperationNotPermittedException
     * @return $this
     */
    public function approve()
    {
        if (!$this->canBeApproved()) {
            $message = sprintf('No es pot aprovar el crit amb id %s i estat %s', $this->getId(), $this->getStatus());
            throw new OperationNotPermittedException($message);
        }

        $this->setStatus(self::STATUS_APPROVED);

        return $this;
    }

    /**
     * Can a shout be approved?
     *
     * @return bool
     */
    public function canBeApproved()
    {
        $availableStatusForApproving = array(self::STATUS_NEW);

        return in_array($this->status, $availableStatusForApproving);
    }

    /**
     * Set status
     *
     * @param string $status
     * @return Shout
     * @throws InvalidArgumentException
     */
    public function setStatus($status)
    {
        if (!in_array($status, array(self::STATUS_NEW, self::STATUS_APPROVED, self::STATUS_INAPPROPRIATE))) {
            throw new InvalidArgumentException(sprintf("L'estat del crit no és vàlid '%s'", $status));
        }
        $this->status = $status;
    
        return $this;
    }

    /**
     * Get status
     *
     * @return string 
     */
    public function getStatus()
    {
        return $this->status;
    }

    /**
     * Set token
     *
     * @param string $token
     * @return Shout
     */
    public function setToken($token)
    {
        $this->token = $token;
    
        return $this;
    }

    /**
     * Get token
     *
     * @return string 
     */
    public function getToken()
    {
        return $this->token;
    }

    /**
     * Generates a token
     *
     * @return string
     */
    private function generateToken()
    {
        return md5(uniqid(rand(), true));
    }

    /**
     * Request for a shout removal, request the author to confirm removal
     *
     * @param NotificationManager $notificationManager
     * @param UrlGeneratorInterface $router
     * @throws OperationNotPermittedException
     * @return Shout
     */
    public function requestRemoval(NotificationManager $notificationManager, UrlGeneratorInterface $router)
    {
        if (!$this->canBeRequestedForRemoval()) {
            $message = sprintf("No es pot demanar esborrar el crit amb id %s i estat %s", $this->getId(), $this->getStatus());
            throw new OperationNotPermittedException($message);
        }
        $token = $this->generateToken();
        $this->setToken($token);
        $confirmUrl = $router->generate('prunatic_shout_confirm_remove', array('token' => $this->getToken()), UrlGeneratorInterface::ABSOLUTE_URL);

        $notificationManager->sendShoutRemovalConfirmationEmail($this, $confirmUrl);

        return $this;
    }

    /**
     * If a shout could be requested for removal
     *
     * @return bool
     */
    public function canBeRequestedForRemoval()
    {
        $requestStatus = array(self::STATUS_NEW, self::STATUS_APPROVED, self::STATUS_INAPPROPRIATE);

        return in_array($this->getStatus(), $requestStatus);
    }

    /**
     * Return if a shout is visible
     *
     * @return bool
     */
    public function isVisible()
    {
        $visibleStatus = array(self::STATUS_APPROVED);
        return in_array($this->getStatus(), $visibleStatus);
    }

    /**
     * Set totalVotes
     *
     * @param integer $totalVotes
     * @return Shout
     */
    public function setTotalVotes($totalVotes)
    {
        $this->totalVotes = $totalVotes;
    
        return $this;
    }

    /**
     * Get totalVotes
     *
     * @return integer 
     */
    public function getTotalVotes()
    {
        return $this->totalVotes;
    }


    /**
     * Set city
     *
     * @param City $city
     * @return Shout
     */
    public function setCity(City $city = null)
    {
        $this->city = $city;
    
        return $this;
    }

    /**
     * Get city
     *
     * @return City
     */
    public function getCity()
    {
        return $this->city;
    }

    /**
     * Get province
     *
     * @return Province
     */
    public function getProvince()
    {
        return $this->getCity()->getProvince();
    }

    /**
     * Get country
     *
     * @return Country
     */
    public function getCountry()
    {
        return $this->getProvince()->getCountry();
    }
}