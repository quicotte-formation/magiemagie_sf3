<?php

namespace AppBundle\Service;

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
    
    public function demarrer($partieId){
        
        $this->em->beginTransaction();
        
        $partie = $this->em->find("AppBundle:Partie", $partieId);
        $partie->setEtat( \AppBundle\Entity\Partie::ETAT_DEMARREE );
        
        $this->em->flush();
        $this->em->commit();
    }
    
    public function rejoindre($joueurId, $partieId){
        
        $this->em->beginTransaction();
        
        $joueur = $this->em->find("AppBundle:Joueur", $joueurId);
        $partie = $this->em->find("AppBundle:Partie", $partieId);
        $joueur->setPartie($partie);
        $partie->addJoueur($joueur);
        $joueur->setOrdre( $partie->getOrdreMax() + 1 );
        $joueur->setElimine(false);
        
        $this->em->flush();
        $this->em->commit();
    }
    
    public function __construct(\Doctrine\ORM\EntityManager $em) {
        
        $this->em=$em;
    }
}
