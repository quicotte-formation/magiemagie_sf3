<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Joueur
 *
 * @ORM\Table(name="joueur")
 * @ORM\Entity(repositoryClass="AppBundle\Repository\JoueurRepository")
 */
class Joueur
{
    /**
     * @var int
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=16, nullable=false)
     */
    private $nom;
    
    /**
     * @ORM\JoinColumn
     * @ORM\ManyToOne(targetEntity="Partie", inversedBy="joueurs")
     */
    private $partie;

    /**
     * @ORM\OneToMany(targetEntity="Carte", mappedBy="joueur")
     */
    private $cartes;
    
    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    private $ordre;
    
    /**
     * @ORM\Column(type="boolean", nullable=true)
     */
    private $elimine;
    
    /**
     * Get id
     *
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set partie
     *
     * @param \AppBundle\Entity\Partie $partie
     *
     * @return Joueur
     */
    public function setPartie(\AppBundle\Entity\Partie $partie = null)
    {
        $this->partie = $partie;

        return $this;
    }

    /**
     * Get partie
     *
     * @return \AppBundle\Entity\Partie
     */
    public function getPartie()
    {
        return $this->partie;
    }
    /**
     * Constructor
     */
    public function __construct()
    {
        $this->cartes = new \Doctrine\Common\Collections\ArrayCollection();
    }

    /**
     * Add carte
     *
     * @param \AppBundle\Entity\Carte $carte
     *
     * @return Joueur
     */
    public function addCarte(\AppBundle\Entity\Carte $carte)
    {
        $this->cartes[] = $carte;

        return $this;
    }

    /**
     * Remove carte
     *
     * @param \AppBundle\Entity\Carte $carte
     */
    public function removeCarte(\AppBundle\Entity\Carte $carte)
    {
        $this->cartes->removeElement($carte);
    }

    /**
     * Get cartes
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getCartes()
    {
        return $this->cartes;
    }

    /**
     * Set nom
     *
     * @param string $nom
     *
     * @return Joueur
     */
    public function setNom($nom)
    {
        $this->nom = $nom;

        return $this;
    }

    /**
     * Get nom
     *
     * @return string
     */
    public function getNom()
    {
        return $this->nom;
    }

    /**
     * Set ordre
     *
     * @param integer $ordre
     *
     * @return Joueur
     */
    public function setOrdre($ordre)
    {
        $this->ordre = $ordre;

        return $this;
    }

    /**
     * Get ordre
     *
     * @return integer
     */
    public function getOrdre()
    {
        return $this->ordre;
    }

    /**
     * Set elimine
     *
     * @param boolean $elimine
     *
     * @return Joueur
     */
    public function setElimine($elimine)
    {
        $this->elimine = $elimine;

        return $this;
    }

    /**
     * Get elimine
     *
     * @return boolean
     */
    public function getElimine()
    {
        return $this->elimine;
    }
}
