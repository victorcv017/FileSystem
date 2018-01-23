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
    
    //mime Types admitidos en un mapa
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
    
    //------------------------------------------Controlador de signIn
    public function signInAction(){
        //comprueba si hay una sesión de usuario iniciada
        if ($this->getUser() instanceof User) {
            return $this->redirectToRoute('home', array('email'=>$this->getUser()->getEmail()));
        }
        
        //recordar el último usuario ingresado, que no lo vuelva a escribir
        $aux = $this->get('security.authentication_utils');
        $last_username = $aux->getLastUsername();

        return $this->render('@App/sign_in.html.twig', array(
                    'last_username' => $last_username
        ));
    }
    
    //-----------------------------------------Controlador del Perfil
    public function profileAction(Request $request){
        $user = $this->getUser();
        
        return $this->render('@App/profile.html.twig', array(
                    
        ));
    }
    
    //----------------------------------------Controlador del Registro
    public function signUpAction(Request $request) {
        //instancia de la Entidad User y su formulario
        $user = new User();
        $form = $this->createForm(UserType::class, $user);

        //Cacha los datos ingresados
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $data = $form->getData();
            
            //encripta la contraseña
            $encoder = $this->get('security.password_encoder');
            $data->setPassword($encoder->encodePassword($data, $data->getPassword()));
            $data->setRole();
            
            // crea un nuevo folder para el nuevo usuario
            $folder = new Folder();
            $folder->setUser($data);
            $folder->setName($data->getEmail());
            $folder->setDescription("root folder");
            $folder->setPath($data->getEmail()."/");
           
            //guardar los cambios en la base
            $em = $this->getDoctrine()->getManager();
            $em->persist($data);
            $em->persist($folder);
            $em->flush();
            return $this->redirectToRoute('signIn');
        }
        
        return $this->render('@App/sign_up.html.twig', array(
                    'form' => $form->createView()
        ));
    }
    
    //----------------------------------Upload Controller
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
        $url = $request->getUri();
        $url = parse_url($url, PHP_URL_PATH);
        $pos = strrpos($url, 'home');
        $url = substr($url, $pos , strlen($url));
        $path = explode("/",$url);
        //var_dump($path);
        $current_folder = $path[sizeof($path)-1];
        
        if($current_folder == 'home') $current_folder = $user->getEmail();
        
        
        $em = $this->getDoctrine()->getManager();
        $current_folder = $em->getRepository('AppBundle:Folder')->findOneBy(array('name' => $current_folder, 'userId' => $user->getId()));
        
        if($current_folder == 'home') $current_folder = $user->getEmail();
        
        $folders_obj = $current_folder->getChilds();
        $files_obj = $current_folder->getFiles();
        $files = array();
        foreach ($files_obj as $file){
            $files[$file->getFile()] = array('name' => $file->getName().".".$file->getExtension(), 
                                            'created' => $file->getCreatedAt(), 
                                            'updated' => $file->getUpdatedAt());
        }
        $folders = array();
        foreach ($folders_obj as $folder) {
            $folders[$folder->getId()] = array('name' => $folder->getName(), 
                                        'created' => $folder->getCreatedAt());
        }
        //var_dump($files);
        //var_dump($folders);
        

        return $this->render('@App/home.html.twig', array(
                    'folders'=>$folders,
                     'files' =>$files
                
        ));
    }
    
}
