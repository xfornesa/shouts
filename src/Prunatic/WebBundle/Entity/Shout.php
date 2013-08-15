<?php
/**
 * Author: Xavier
 */

namespace Prunatic\WebBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

use \InvalidArgumentException as InvalidArgumentException;
use Prunatic\WebBundle\Entity\OperationNotPermittedException;
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
    // TODO Move this field to configuration yml file
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
     * @var string
     *
     * @ORM\Column(name="token", type="string", length=255, nullable=true)
     */
    private $token;

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
        $this->votes = new ArrayCollection();
        $this->status = self::STATUS_NEW;
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
     * @param Vote $votes
     * @throws DuplicateException
     * @return Shout
     */
    public function addVote(Vote $votes)
    {
        // avoid more than one vote from the same IP
        $ip = $votes->getIp();
        if ($this->hasBeenVotedFromIp($ip)) {
            throw new DuplicateException(sprintf('There is a previous vote from the same IP.', $ip));
        }

        $this->votes[] = $votes;
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
            throw new DuplicateException(sprintf('There is a previous report from the same IP.', $ip));
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
            throw new InvalidArgumentException("Invalid status");
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
     * Sends an email to the author to confirm the shout removal
     *
     * @param \Swift_Mailer $mailer
     * @param string $url Absolute url to confirm shout removal
     * @return bool
     */
    public function sendRemovalConfirmationEmail(\Swift_Mailer $mailer, $url)
    {
        // TODO get data from configuration
        $subject = 'Confirmació per silenciar un crit';
        $from = 'send@example.com';
        $to = $this->getEmail();

        // TODO render body with a template
        $body = <<<EOF
            Hola, si us play fes clic aquí per confirmar que vols silenciar el teu crit:
            <a href="{$url}">{$url}</a>
EOF;

        $message = \Swift_Message::newInstance()
            ->setSubject($subject)
            ->setFrom($from)
            ->setTo($to)
            ->setBody($body);
        ;

        $i_recipients = $mailer->send($message);

        return $i_recipients > 0;
    }
}