<?php

namespace AppBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;

class PartieControllerController extends Controller
{
    /**
     * @Route("/lister_parties_en_attente_ajax", name="lister_parties_en_attente_ajax")
     */
    public function listerPartiesEnAttenteAjax(){
        
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
        
        return $this->render("AppBundle:PartieController:lister_parties_en_attente_ajax.html.twig",
                array("parties"=>$partiesEnAttente));
    }
    
    /**
     * @Route("/lister_parties", name="lister_parties")
     */
    public function listerPartiesAction()
    {
        return $this->render('AppBundle:PartieController:lister_parties.html.twig', array(
            // ...
        ));
    }

}
