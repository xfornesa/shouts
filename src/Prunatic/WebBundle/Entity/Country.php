<?php
/**
 * Author: Xavier
 */

namespace Prunatic\WebBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Symfony\Component\Validator\Constraints as Assert;
use Prunatic\WebBundle\Entity\Province;

/**
 * Report
 *
 * @ORM\Table()
 * @ORM\Entity(repositoryClass="Prunatic\WebBundle\Entity\CountryRepository")
 */
class Country
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
     * @var string
     *
     * @ORM\Column(name="name", type="string", length=255)
     * @Assert\NotBlank()
     * @Assert\Length(min="3", max="250")
     */
    private $name;

    /**
     * @var string
     *
     * @Gedmo\Slug(fields={"name"}, updatable=true, unique=true)
     * @ORM\Column(length=250, unique=true)
     * @Assert\NotBlank()
     */
    private $slug;

    /**
     * @var ArrayCollection
     *
     * @ORM\OneToMany(targetEntity="Province", mappedBy="country")
     */
    private $provinces;

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
     * @return Country
     */
    public function setName($name)
    {
        $this->name = $name;
    
        return $this;
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
     * Set slug
     *
     * @param string $slug
     * @return Country
     */
    public function setSlug($slug)
    {
        $this->slug = $slug;
    
        return $this;
    }

    /**
     * Get slug
     *
     * @return string 
     */
    public function getSlug()
    {
        return $this->slug;
    }
    /**
     * Constructor
     */
    public function __construct()
    {
        $this->provinces = new ArrayCollection();
    }
    
    /**
     * Add provinces
     *
     * @param Province $provinces
     * @return Country
     */
    public function addProvince(Province $provinces)
    {
        $this->provinces[] = $provinces;
    
        return $this;
    }

    /**
     * Remove provinces
     *
     * @param Province $provinces
     */
    public function removeProvince(Province $provinces)
    {
        $this->provinces->removeElement($provinces);
    }

    /**
     * Get provinces
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getProvinces()
    {
        return $this->provinces;
    }
}