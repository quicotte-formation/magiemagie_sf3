<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Partie
 *
 * @ORM\Table(name="partie")
 * @ORM\Entity(repositoryClass="AppBundle\Repository\PartieRepository")
 */
class Partie
{
    const ETAT_EN_ATTENTE="EN_ATTENTE";
    const ETAT_DEMARREE="DEMARREE";
    const ETAT_TERMINEE="TERMINEE";

    /**
     * @var int
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @ORM\OneToMany(targetEntity="Joueur", mappedBy="partie")
     */
    private $joueurs;

    /**
     * @ORM\Column(type="string", length=16, nullable=false)
     */
    private $etat;
    
    /**
     * Renvoie l'ordre maxi de ts les joueurs de cette partie.
     * @return type
     */
    public function getOrdreMax(){
        
        $ordre=0;
        foreach ($this->joueurs as $joueur) {
            
            if( $joueur->getOrdre()>$ordre )
                $ordre = $joueur->getOrdre();
        }
        
        return $ordre;
    }
    
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
     * Constructor
     */
    public function __construct()
    {
        $this->joueurs = new \Doctrine\Common\Collections\ArrayCollection();
    }

    /**
     * Add joueur
     *
     * @param \AppBundle\Entity\Joueur $joueur
     *
     * @return Partie
     */
    public function addJoueur(\AppBundle\Entity\Joueur $joueur)
    {
        $this->joueurs[] = $joueur;

        return $this;
    }

    /**
     * Remove joueur
     *
     * @param \AppBundle\Entity\Joueur $joueur
     */
    public function removeJoueur(\AppBundle\Entity\Joueur $joueur)
    {
        $this->joueurs->removeElement($joueur);
    }

    /**
     * Get joueurs
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getJoueurs()
    {
        return $this->joueurs;
    }

    /**
     * Set etat
     *
     * @param string $etat
     *
     * @return Partie
     */
    public function setEtat($etat)
    {
        $this->etat = $etat;

        return $this;
    }

    /**
     * Get etat
     *
     * @return string
     */
    public function getEtat()
    {
        return $this->etat;
    }
}
