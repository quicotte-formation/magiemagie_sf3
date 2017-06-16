<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Carte
 *
 * @ORM\Table(name="carte")
 * @ORM\Entity(repositoryClass="AppBundle\Repository\CarteRepository")
 */
class Carte
{
    const TYPE_CORNE_DE_LICORNE="CORNE_DE_LICORNE";
    const TYPE_BAVE_DE_CRAPAUD="BAVE_DE_CRAPAUD";
    const TYPE_SANG_DE_VIERGE="SANG_DE_VIERGE";
    const TYPE_LAPIS_LAZULI="LAPIS_LAZULI";
    const TYPE_AILES_DE_CHAUVE_SOURIS="AILES_DE_CHAUVE_SOURIS";
    
    /**
     * @var int
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @ORM\JoinColumn(name="joueur_id")
     * @ORM\ManyToOne(targetEntity="Joueur", inversedBy="cartes")
     */
    private $joueur;
    
    /**
     * @ORM\Column(type="string", length=32, nullable=false)
     */
    private $type;

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
     * Set type
     *
     * @param string $type
     *
     * @return Carte
     */
    public function setType($type)
    {
        $this->type = $type;

        return $this;
    }

    /**
     * Get type
     *
     * @return string
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * Set joueur
     *
     * @param \AppBundle\Entity\Joueur $joueur
     *
     * @return Carte
     */
    public function setJoueur(\AppBundle\Entity\Joueur $joueur = null)
    {
        $this->joueur = $joueur;

        return $this;
    }

    /**
     * Get joueur
     *
     * @return \AppBundle\Entity\Joueur
     */
    public function getJoueur()
    {
        return $this->joueur;
    }
}
