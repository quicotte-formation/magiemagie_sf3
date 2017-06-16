<?php

namespace AppBundle\Service;

use \AppBundle\Entity\Carte as Carte;

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of PartieService
 *
 * @author tom
 */
class PartieService {

    /**
     * @var \Doctrine\ORM\EntityManager 
     */
    private $em;

    /**
     * Crée une nouvelle carte, initialisée avec l'un des types de cartes existant.
     * @return \AppBundle\Service\AppBundle\Entity\Carte
     */
    private static function tirerCarte() {

        $carte = new \AppBundle\Entity\Carte();

        $tab = array($carte::TYPE_AILES_DE_CHAUVE_SOURIS,
            $carte::TYPE_BAVE_DE_CRAPAUD,
            $carte::TYPE_CORNE_DE_LICORNE,
            $carte::TYPE_LAPIS_LAZULI,
            $carte::TYPE_SANG_DE_VIERGE);
        $n = rand(0, count($tab) - 1);

        $carte->setType($tab[$n]);

        return $carte;
    }

    public function demarrer($partieId) {

        $this->em->beginTransaction();

        // Change état partie
        $partie = $this->em->find("AppBundle:Partie", $partieId);
        $partie->setEtat(\AppBundle\Entity\Partie::ETAT_DEMARREE);

        // Attribue 7 cartes à chaque joueur
        foreach ($partie->getJoueurs() as $joueur) {

            for ($i = 0; $i < 7; $i++) {
                $carte = self::tirerCarte();
                $carte->setJoueur($joueur);
                $joueur->addCarte($carte);
                $this->em->persist($carte);
            }
        }

        $this->em->flush();
        $this->em->commit();
    }

    public function rejoindre($joueurId, $partieId) {

        $this->em->beginTransaction();

        $joueur = $this->em->find("AppBundle:Joueur", $joueurId);
        $partie = $this->em->find("AppBundle:Partie", $partieId);
        $joueur->setPartie($partie);
        $partie->addJoueur($joueur);
        $joueur->setOrdre($partie->getOrdreMax() + 1);
        $joueur->setElimine(false);

        $this->em->flush();
        $this->em->commit();
    }

    public function __construct(\Doctrine\ORM\EntityManager $em) {

        $this->em = $em;
    }

}
