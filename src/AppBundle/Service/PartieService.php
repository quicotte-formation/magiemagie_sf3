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
     *
     * @var \Symfony\Bridge\Monolog\Logger
     */
    private $logger;

    private function volerCarte($idJoueurLanceurSort, $idJoueurVictime){
        
        // Récupère joueur source et cible
        $joueurLanceurSort = $this->em->getRepository("AppBundle:Joueur")->find($idJoueurLanceurSort);
        $joueurVictime = $this->em->getRepository("AppBundle:Joueur")->find($idJoueurVictime);
        
        // Si le joueur victime ne possède aucune carte, on sort de la fonction
        $nbCartesCible = count( $joueurVictime->getCartes() );
        if( $nbCartesCible<1  )
            return;
        
        // La victime possède au moins une carte
        
        // Vole une carte au hasard à la victime
        $n = rand(0, $nbCartesCible-1);
        $carte = $joueurVictime->getCartes()->get($n);
        $joueurVictime->removeCarte($carte);
        $carte->setJoueur($joueurLanceurSort);
    }
    
    public function lancerSort($idLanceurSort, $carteIds, $cibleIds){
        
        $carteRepository = $this->em->getRepository("AppBundle:Carte");
        $joueurRepository = $this->em->getRepository("AppBundle:Joueur");
        
        $this->em->beginTransaction();
        
        // Récupère lanceur
        $joueurLanceurSort = $this->em->find("AppBundle:Joueur", $idLanceurSort);
        
        // Récupère les cartes
        $cartes = [];
        foreach($carteIds as $carteId){
            $carte = $carteRepository->find($carteId);
            if( $carte->getJoueur()->getId()==$idLanceurSort )
                $cartes[] = $carte;
        }
        
        // Récupère les cibles
        $joueursCibles = $joueurRepository->findByJoueurIds($cibleIds);
        
        // Exception si le joueur ne possède pas ces cartes
        if( count($cartes)!=count($carteIds) )
            throw new \RuntimeException ("Ces cartes ne vous appartiennent pas ; (");
        
        // Lance le sort et exception si n'existe pas
        if( count($cartes)==2 && count($joueursCibles==0) 
                && $cartes[0]->getType()== Carte::TYPE_CORNE_DE_LICORNE
                && $cartes[1]->getType()== Carte::TYPE_BAVE_DE_CRAPAUD){
            
            // INVISIBILITE: prend 1 carte au hasard à chaque adversaire
            foreach ($joueurLanceurSort->getPartie()->getJoueurs() as $joueur){
                
                if( $joueur->getElimine()==false && $joueur->getId()!=$joueurLanceurSort->getId() ){
                    $this->volerCarte($idLanceurSort, $joueur->getId());
                }
            }
            
        } elseif( count($cartes)==2 && count($joueursCibles==1) 
                && $cartes[0]->getType()== Carte::TYPE_CORNE_DE_LICORNE
                && $cartes[1]->getType()== Carte::TYPE_SANG_DE_VIERGE){
            
            // FILTRE D'AMOUR: le joueur cible me donne la moitié de ses cartes
            
            $cartesJoueurCible = $joueursCibles[0]->getCartes();
            if( count($cartesJoueurCible)<1 ){
                throw new \RuntimeException("Votre petite victime n'a plus aucune carte!");
            }
            $nbCartesAVoler = ceil( count( $cartesJoueurCible )/2 );
            for( $i=0;$i<$nbCartesAVoler;$i++ ){
                
                $this->logger->warn( sprintf("*** %d %d", $idLanceurSort, $joueursCibles[0]->getId()) );
                
                $this->volerCarte($idLanceurSort, $joueursCibles[0]->getId());
            }
            
        } elseif( count($cartes)==2 && count($joueursCibles==1) 
                && $cartes[0]->getType()== Carte::TYPE_BAVE_DE_CRAPAUD
                && $cartes[1]->getType()== Carte::TYPE_LAPIS_LAZULI ){
            
            // HYPNOSE: échange 3 cartes contre 1 adverse
        } elseif( count($cartes)==2 && count($joueursCibles==0) 
                && $cartes[0]->getType()== Carte::TYPE_LAPIS_LAZULI
                && $cartes[1]->getType()== Carte::TYPE_AILES_DE_CHAUVE_SOURIS ){
            // DIVINATION: le joueur voit toutes les cartes des autres joueurs
        } elseif( count($cartes)==2 && count($joueursCibles==1) 
                && $cartes[0]->getType()== Carte::TYPE_SANG_DE_VIERGE
                && $cartes[1]->getType()== Carte::TYPE_BAVE_DE_CRAPAUD ){
            
            // SOMMEIL PROFOND: la cible passe 2 tours
        } else{
            
            $this->logger->warn("*** " . $cartes[0]->getType() . " " . $cartes[1]->getType() );
            throw new \RuntimeException( sprintf("Vous avez raté votre coup! Travaillez votre mémoire %s", json_encode($cartes[0]->getType() )));
        }
        
        // Supprime les cartes utilisées pour lancer le sort
        foreach ($cartes as $carte){
        
            $joueurLanceurSort->removeCarte( $carte );
            $carte->setJoueur( null );
        }
        
        // Passe au joueur suivant
        $this->determinerJoueurSuivant($joueurLanceurSort->getPartie()->getId());
        
        $this->em->flush();
        $this->em->commit();
    }
    
    public function passerTour($joueurId){
        
        $this->em->beginTransaction();
        
        // Récupère joueur
        $joueur = $this->em->find("AppBundle:Joueur", $joueurId);
        
        // Récupère 2 cartes ds la pioche
        for($i=0;$i<2;$i++){
            
            $carte = self::tirerCarte();
            $joueur->addCarte($carte);
            $carte->setJoueur($joueur);
            $this->em->persist($carte);
        }
        
        // Passe au joueur suivant
        $this->determinerJoueurSuivant($joueur->getPartie()->getId());
        
        $this->em->flush();
        $this->em->commit();
    }
    
    public function determinerJoueurSuivant($partieId){
        
        $this->em->beginTransaction();
        
        // Récupère la partie
        $partie = $this->em->find("AppBundle:Partie", $partieId);
        
        // Récupère les joueurs actifs suivants, dans l'ordre
        $joueursActifsSuivants
                = $this->em->createQuery(""
                . "SELECT   j "
                . "FROM     AppBundle:Joueur j "
                . "         JOIN j.partie p "
                . "WHERE    p.id=:partieId AND "
                . "         j.elimine=false AND "
                . "         j.ordre>p.ordre "
                . "ORDER BY j.ordre")->setParameter("partieId", $partieId)
                ->getResult();

        if( count($joueursActifsSuivants)>0)
            $partie->setOrdre( $joueursActifsSuivants[0]->getOrdre() );
        else{
            
            // Récupère les joueurs actifs précédents, dans l'ordre
            $joueursActifsPrécédents
                = $this->em->createQuery(""
                . "SELECT   j "
                . "FROM     AppBundle:Joueur j "
                . "         JOIN j.partie p "
                . "WHERE    p.id=:partieId AND "
                . "         j.elimine=false AND "
                . "         j.ordre<p.ordre "
                . "ORDER BY j.ordre")->setParameter("partieId", $partieId)
                ->getResult();
            if( count($joueursActifsSuivants)<0 )
                throw new \RuntimeException("La partie est terminée!");
            
            $partie->setOrdre( $joueursActifsPrécédents[0]->getOrdre() );
        }
        
        $this->em->flush();
        $this->em->commit();
    }
    
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
        
        // Détermine que le 1er joueur a la main
        $partie->setOrdre(1);

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

    public function __construct(\Doctrine\ORM\EntityManager $em, \Symfony\Bridge\Monolog\Logger $logger) {

        $this->em = $em;
        $this->logger = $logger;
    }

}
