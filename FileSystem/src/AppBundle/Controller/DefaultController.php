<?php
//https://symfony.com/doc/current/bundles/SonataAdminBundle/cookbook/recipe_file_uploads.html
namespace AppBundle\Controller;

use AppBundle\Entity\User;
use AppBundle\Entity\Files;
use AppBundle\Form\UserType;
use AppBundle\Form\FilesType;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Filesystem\Exception\IOExceptionInterface;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpFoundation\Request;


class DefaultController extends Controller
{   
    public function signInAction(){
        if ($this->getUser() instanceof User) {
            
            return $this->redirectToRoute('uploadFile');
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
            $data->setRole();
            $em = $this->getDoctrine()->getManager();
            $em->persist($data);
            $em->flush();
            $fs = new Filesystem();

            try {
                $fs->mkdir('../web/files/' . $data->getEmail());
                echo $data->getEmail();
            } catch (IOExceptionInterface $e) {
                //echo "An error occurred while creating your directory at " . $e->getPath();
            }
            return $this->redirectToRoute('signIn');
        }
        
        return $this->render('@App/sign_up.html.twig', array(
                    'form' => $form->createView()
                        // ...
        ));
    }
    
    public function uploadFileAction(Request $request){
        $user = $this->getUser();
        $file = new Files();
        $form = $this->createForm(FilesType::class, $file);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            
            $file = new Files();
            $file->setName($form->get('name')->getData());
            
            $data = $form["path"]->getData();
            $ext = $data->guessExtension();
            $ext = time().".".$ext;
            
            $file->setExtension($ext);
            $file->setUser($user);
            $file_name = $file->getName().".".time().".".$file->getExtension();
            
            $data->move("files/".$user->getEmail(),$file_name);
           
            
            
                  
            $em = $this->getDoctrine()->getManager();
            $em->persist($file);
            $em->flush();
                         
            
        }

        return $this->render('@App/upload_file.html.twig', array(
                    'form' => $form->createView()
                        // ...
        ));
    }

}
