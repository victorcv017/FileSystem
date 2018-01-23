<?php
//https://symfony.com/doc/current/bundles/SonataAdminBundle/cookbook/recipe_file_uploads.html
namespace AppBundle\Controller;

use AppBundle\Entity\Files;
use AppBundle\Entity\Folder;
use AppBundle\Entity\User;
use AppBundle\Form\FilesType;
use AppBundle\Form\UserType;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;

class DefaultController extends Controller
{   
    private $mimeType = array(
        'text/plain'=>'txt',
        'application/vnd.openxmlformats-officedocument.wordprocessingml.document' => 'word',
        'application/vnd.openxmlformats-officedocument.presentationml.presentation' =>'ppt',
        'image/png'=>'png',
        'application/xml'=>'xml',
        'text/x-php'=>'php',
        'text/html'=>'html',
        'image/jpeg'=>'jpeg',
        'application/pdf'=>'pdf',
        'text/x-c'=>'java'
        
    );
    
    private function getCurrentFolder($url){
        
        $url = trim($url, '/');
    }
    
    public function signInAction(){
        if ($this->getUser() instanceof User) {
            
            return $this->redirectToRoute('home', array('email'=>$this->getUser()->getEmail()));
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
            
            $folder = new Folder();
            $folder->setUser($data);
            $folder->setName($data->getEmail());
            $folder->setDescription("root folder");
            $folder->setPath($data->getEmail()."/");
            
            $em = $this->getDoctrine()->getManager();
            $em->persist($data);
            $em->persist($folder);
            $em->flush();
            
            
            
            return $this->redirectToRoute('signIn');
        }
        return $this->render('@App/sign_up.html.twig', array(
                    'form' => $form->createView()
                        // ...
        ));
    }
    
    public function uploadFileAction(Request $request){
        $user = $this->getUser();
        $upfile = new Files();
        $form = $this->createForm(FilesType::class, $upfile);
        $em = $this->getDoctrine()->getManager();
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $data = $form["file"]->getData();
            var_dump($data);
            var_dump($upfile->getFile());
            if(isset($this->mimeType[$upfile->getFile()->getmimeType()])){
                echo "Archivo subido";
                $file = $upfile->getFile();
                $ext = $data->getClientOriginalExtension();
                $file_name = time() . "." . $ext;
                $upfile->setExtension($ext);
                $upfile->setUser($user);
                $upfile->setName($file_name);
                
               // $folder = 
                
                
                
                $em->persist($upfile);
                $em->flush();
                $file->move(
                        "Resources/Files", $file_name
                );
                  
                
            }

            
        }
        if($request->getMethod() == 'POST'){
            if($request->get('crear')!=null){
                $folder = new Folder();
                $folder->setName($request->get('name'));
                $folder->setUser($user);
                $em->getRepository("AppBundle:Folder");
                $query = $em->createQuery(
                                'SELECT COUNT(f.id)
                                FROM AppBundle:Folder f
                                WHERE f.userId = :user_id'
                        )->setParameter('user_id', $user->getId());
                $count = $query->getResult();
                $count = $count[0][1];
                //var_dump($count);
                if($count == 1){
                    $em = $this->getDoctrine()->getManager();
                    $parent_folder = $em->getRepository('AppBundle:Folder')->findOneBy(array('userId'=>$user->getId()));
                    
                    $folder->setPath($parent_folder->getPath().$folder->getName());
                    $folder->setParentFolder($parent_folder);
                    $em->persist($folder);
                    $em->flush();
                }
            }
        }

        return $this->render('@App/upload_file.html.twig', array(
                    'form' => $form->createView()
                        // ...
        ));
    }
    
    public function homeAction(Request $request){
        //var_dump($request->get('email'));
        $user = $this->getUser();
        /*
        if(!($request->get('email') == $user->getEmail())){
            return $this->redirectToRoute('signUp');
        }
        $em = $this->getDoctrine()->getManager();
        $folders = $em->getRepository('AppBundle:Folder')->findBy(array('userId' => $user->getId()));
        */
        $
        $url = $request->getUri();
        var_dump(trim($url, '/'));
        

        return $this->render('@App/home.html.twig', array(
                    //'folders'=>$folders
                        // ...
        ));
    }
    
    
    
}
