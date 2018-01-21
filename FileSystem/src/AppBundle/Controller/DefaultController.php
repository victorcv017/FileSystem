<?php

namespace AppBundle\Controller;

use AppBundle\Entity\User;
use AppBundle\Form\UserType;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;

class DefaultController extends Controller
{   
    public function signInAction(){
        var_dump($this->getUser());
        if ($this->getUser() instanceof User) {
            
            return $this->redirectToRoute('profile');
        }
        $aux = $this->get('security.authentication_utils');
        $last_username = $aux->getLastUsername();

        return $this->render('@App/sign_in.html.twig', array(
                    'last_username' => $last_username
        ));
    }
    
    public function profileAction(Request $request){
        $user = $this->getUser();
        
        return $this->render('@App/profile.html.twig', array(
                    
        ));
    }
    
    public function signUpAction(Request $request) {
        $user = new User();
        $form = $this->createForm(UserType::class, $user);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $data = $form->getData();
            $encoder = $this->get('security.password_encoder');
            $data->setPassword($encoder->encodePassword($data, $data->getPassword()));
            //var_dump($data->getCorreo());
            $em = $this->getDoctrine()->getManager();
            $em->persist($data);
            $em->flush();
            return $this->redirectToRoute('signIn');
        }
        
        return $this->render('@App/sign_up.html.twig', array(
                    'form' => $form->createView()
                        // ...
        ));
    }

}
