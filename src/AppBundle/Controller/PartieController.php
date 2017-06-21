<?php

namespace AppBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;

class PartieController extends Controller
{
    /**
     * @Route("/ajax_passer_tour", name="ajax_passer_tour")
     * @param \Symfony\Component\HttpFoundation\Request $req
     */
    public function ajaxPasserTourAction(\Symfony\Component\HttpFoundation\Request $req){
        
        $service = $this->get("partieService");
        $service->passerTour( $req->getSession()->get("joueurConnecte")->getId() );
        
        return new \Symfony\Component\HttpFoundation\Response();
    }
    
    /**
     * @Route("/ajax_etat_partie", name="ajax_etat_partie")
     * @param \Symfony\Component\HttpFoundation\Request $req
     */
    public function ajaxEtatPartieAction(\Symfony\Component\HttpFoundation\Request $req){

        $idPartie = $req->getSession()->get("idPartieActuelle");
        $joueur = $req->getSession()->get("joueurConnecte");
        $em = $this->getDoctrine()->getManager();
        
        // Construit objet: {joueurs, mesCartes, monTour, etatPartie}
        $dto["joueurs"] = $this->getDoctrine()->getRepository("AppBundle:Joueur")->findDataByPartieIdOrderByJoueurOrdre($idPartie);
        $dto["mesCartes"] = $this->getDoctrine()->getRepository("AppBundle:Carte")->findDataByJoueurId($joueur->getId() );
        $dto["partie"] = $this->getDoctrine()->getRepository("AppBundle:Partie")->findDataById($idPartie);
        $dto["joueurConnecte"] = $this->getDoctrine()->getRepository("AppBundle:Joueur")->findOneDataByJoueurId($joueur->getId());
        
        return $this->json($dto);
    }
    
    /**
     * @Route("/zone_mes_cartes_ajax", name="zone_mes_cartes_ajax")
     */
    public function zoneMesCartesAjaxAction(\Symfony\Component\HttpFoundation\Request $req){
        
       // Récupère liste cartes du joueur
       $cartes = $this->getDoctrine()->getRepository("AppBundle:Joueur")->find( $req->getSession()->get("joueurConnecte")->getId() )->getCartes();
       
       return $this->render("AppBundle:Partie:_zone_mes_cartes_ajax.html.twig", array("cartes"=>$cartes) ); 
    }
    
    /**
     * @Route("/zone_joueurs_ajax", name="zone_joueurs_ajax")
     */
    public function zoneJoueursAjaxAction(\Symfony\Component\HttpFoundation\Request $req){
        
        $partieId=$req->getSession()->get("idPartieActuelle");
        
        $joueurs = $this->getDoctrine()->getRepository("AppBundle:Joueur")->findByPartieIdOrderByJoueurOrdre($partieId);
        
        return $this->render("AppBundle:Partie:_zone_joueurs_ajax.html.twig", array("joueurs"=>$joueurs) );
    }
    
    /**
     * @Route("/tableau_de_bord", name="tableau_de_bord")
     */
    public function tableauDeBordAction(){
        
        return $this->render("AppBundle:Partie:tableau_de_bord2.html.twig", array() );
    }
    
    /**
     * @Route("/rejoindre_partie/{idPartie}", name="rejoindre_partie")
     */
    public function rejoindrePartieAction(\Symfony\Component\HttpFoundation\Request $req, $idPartie){
        
        $partieService = $this->get("partieService");
        
        $joueurId = $req->getSession()->get("joueurConnecte")->getId();
        
        // Rejoint la partie
        $partieService->rejoindre( $joueurId, $idPartie );
        
        // Place l'id de la partie en session
        $req->getSession()->set("idPartieActuelle", $idPartie);
        
        // Redirige vers tableau de bord
        return $this->redirectToRoute("tableau_de_bord");
    }
    
    /**
     * @Route("/demarrer_partie/{idPartie}", name="demarrer_partie")
     */
    public function demarrerPartieAction(\Symfony\Component\HttpFoundation\Request $req, $idPartie){
        
        $partieService = $this->get("partieService");
        
        $joueurId = $req->getSession()->get("joueurConnecte")->getId();
        
        // Rejoint la partie
        $partieService->rejoindre( $joueurId, $idPartie );
        
        // Démarre la partie
        $partieService->demarrer( $idPartie );
        
        // Place l'id de la partie en session
        $req->getSession()->set("idPartieActuelle", $idPartie);
        
        // Redirige vers tableau de bord
        return $this->redirectToRoute("tableau_de_bord");
    }
    
    /**
     * @Route("/lister_parties_en_attente_ajax", name="lister_parties_en_attente_ajax")
     */
    public function listerPartiesEnAttenteAjaxAction(){
        
        $partiesEnAttente = $this->getDoctrine()->getRepository("AppBundle:Partie")->listerPartiesEnAttente();
        
        // Crée une partie si existe pas
        if( count($partiesEnAttente)<=0 ){
            
            $partie = new \AppBundle\Entity\Partie();
            $partie->setEtat(\AppBundle\Entity\Partie::ETAT_EN_ATTENTE);
            $this->getDoctrine()->getManager()->persist($partie);
            $this->getDoctrine()->getManager()->flush();
            
            // Recharge parties en attente
            $partiesEnAttente = $this->getDoctrine()->getRepository("AppBundle:Partie")->listerPartiesEnAttente();
        }
        
        return $this->render("AppBundle:Partie:_lister_parties_en_attente_ajax.html.twig",
                array("parties"=>$partiesEnAttente));
    }
    
    /**
     * @Route("/lister_parties", name="lister_parties")
     */
    public function listerPartiesAction()
    {
        return $this->render('AppBundle:Partie:lister_parties.html.twig', array(
            // ...
        ));
    }

}
