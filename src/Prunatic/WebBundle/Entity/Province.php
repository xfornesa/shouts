<?php
/**
 * Author: Xavier
 */

namespace Prunatic\WebBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Symfony\Component\Validator\Constraints as Assert;
use Prunatic\WebBundle\Entity\Country;
use Prunatic\WebBundle\Entity\City;

/**
 * Report
 *
 * @ORM\Table(indexes={
 *     @ORM\Index(name="idx_city_province", columns={"slug"}),
 * })
 * @ORM\Entity(repositoryClass="Prunatic\WebBundle\Entity\ProvinceRepository")
 */
class Province
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
     * @var Country
     *
     * @ORM\ManyToOne(targetEntity="Country", inversedBy="provinces")
     * @ORM\JoinColumn(name="country_id", referencedColumnName="id")
     **/
    private $country;

    /**
     * @var string
     *
     * @Gedmo\Slug(fields={"name"}, updatable=true, unique=false)
     * @ORM\Column(length=250, unique=false)
     * @Assert\NotBlank()
     */
    private $slug;

    /**
     * @var ArrayCollection
     *
     * @ORM\OneToMany(targetEntity="City", mappedBy="province")
     */
    private $cities;


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
     * @return Province
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
     * Set country
     *
     * @param Country $country
     * @return Province
     */
    public function setCountry(Country $country = null)
    {
        $this->country = $country;
    
        return $this;
    }

    /**
     * Get country
     *
     * @return Country
     */
    public function getCountry()
    {
        return $this->country;
    }

    /**
     * Set slug
     *
     * @param string $slug
     * @return Province
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
        $this->cities = new ArrayCollection();
    }
    
    /**
     * Add cities
     *
     * @param City $cities
     * @return Province
     */
    public function addCity(City $cities)
    {
        $this->cities[] = $cities;
    
        return $this;
    }

    /**
     * Remove cities
     *
     * @param City $cities
     */
    public function removeCity(City $cities)
    {
        $this->cities->removeElement($cities);
    }

    /**
     * Get cities
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getCities()
    {
        return $this->cities;
    }

    /**
     * Add cities
     *
     * @param \Prunatic\WebBundle\Entity\City $cities
     * @return Province
     */
    public function addCitie(\Prunatic\WebBundle\Entity\City $cities)
    {
        $this->cities[] = $cities;
    
        return $this;
    }

    /**
     * Remove cities
     *
     * @param \Prunatic\WebBundle\Entity\City $cities
     */
    public function removeCitie(\Prunatic\WebBundle\Entity\City $cities)
    {
        $this->cities->removeElement($cities);
    }
}