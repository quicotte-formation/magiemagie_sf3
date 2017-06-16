<?php

namespace AppBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;

class JoueurController extends Controller
{
    /**
     * @Route("/inscription")
     */
    public function inscriptionAction(\Symfony\Component\HttpFoundation\Request $req)
    {
        // Form binding et validation
        $dto = new \AppBundle\Entity\Joueur();
        
        $form = $this->createForm( \AppBundle\Form\JoueurType::class, $dto );
        $form->handleRequest($req);
        if( $form->isSubmitted() && $form->isValid() ){
            
            // Ajoute l'utilisateur
            $em = $this->getDoctrine()->getManager();
            $em->persist($dto);
            $em->flush();
            
            // Place son id en session
            $req->getSession()->set("joueurConnecte", $dto);
            
            // Redirige vers liste des parties
            return $this->redirectToRoute("lister_parties");
        }
        
        return $this->render('AppBundle:Joueur:inscription.html.twig', array(
            "form"=>$form->createView()
        ));
    }

}
