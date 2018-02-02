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
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Component\HttpFoundation\Response;

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
            return $this->redirectToRoute('home');
        }
        
        //recordar el último usuario ingresado, que no lo vuelva a escribir
        $aux = $this->get('security.authentication_utils');
        $last_username = $aux->getLastUsername();

        return $this->render('@App/sign_in.html.twig', array(
                    'last_username' => $last_username
        ));
    }
    
    
    //----------------------------------------Controlador del Registro
    public function signUpAction(Request $request) {
        if ($this->getUser() instanceof User) {
            return $this->redirectToRoute('home');
        }
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
    
    public function homeAction(Request $request, ValidatorInterface $validator){
        $error = "";
        $success = false;
        // checar el folder acctual
        $user = $this->getUser();
        $url = $request->getUri();
        $url = parse_url($url, PHP_URL_PATH);
        $pos = strrpos($url, 'home');
        $url = substr($url, $pos , strlen($url));
        $path = explode("/",$url);
        $current_folder = $path[sizeof($path)-1];
        $parent_folder = "";
        // si esta en el folder root
        if($current_folder == 'home') {
            $current_folder = $user->getEmail();
           
        }else{
            // checar quien es el padre,  si es home que mande a /
            $parent_folder = "/".$path[sizeof($path)-2];
            if($parent_folder == '/home'){
                $parent_folder = '';
            }
        }
        
        //entidad del folder actual
        $em = $this->getDoctrine()->getManager();
        $current_folder = $em->getRepository('AppBundle:Folder')->findOneBy(array('name' => $current_folder, 'userId' => $user->getId()));
       
        // obtener todos los archivos y todos las carpetas
        $folders_obj = $current_folder->getChilds();
        $files_obj = $current_folder->getFiles();
        $files = array();
        foreach ($files_obj as $file){
            $files[$file->getFile()] = array('name' => $file->getName(), 
                                            'created' => $file->getCreatedAt(), 
                                            'file' => $file->getFile(),
                                            'updated' => $file->getUpdatedAt());
        }
        
        $folders = array();
        foreach ($folders_obj as $folder) {
            $path_folder = $folder->getPath();
            $path_folder = strstr($path_folder, '/');
            $path = substr($path_folder , 0 , strlen($path_folder)-1);
            $folders[$folder->getId()] = array('name' => $folder->getName(),
                                        'description' => $folder->getDescription(),
                                        'path' => $path,
                                        'created' => $folder->getCreatedAt());
        }
          
       //formulario para el modal de subir archivo
        $upfile = new Files();
        $form = $this->createForm(FilesType::class, $upfile);
        $em = $this->getDoctrine()->getManager();
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $data = $form["file"]->getData();
            if (isset($this->mimeType[$upfile->getFile()->getmimeType()])) {
                echo "Archivo subido";
                $file = $upfile->getFile();
                $ext = $data->getClientOriginalExtension();
                $file_name = time() . "." . $ext;
                $upfile->setExtension($ext);
                $upfile->setUser($user);
                $upfile->setName($data->getClientOriginalName());
                $upfile->setFile($file_name);
                $upfile->setFolder($current_folder);
                $upfile->setPath($current_folder->getPath());
                $em->persist($upfile);
                $em->flush();
                $file->move(
                        "Resources/Files", $file_name
                );
                header( "refresh:0" );
                $success = true;
                //return $this->redirectToRoute($request->get('_route'));
            }
        }
        
        //formulario para subir carpeta
        if ($request->getMethod() == 'POST') {
            if ($request->request->get('crear') == 'crear') {
                //folders unicos por carpeta
                $childs = $current_folder->getChilds();
                $create = true;
                foreach ($childs as $child){
                    if($child->getName()== $request->request->get('name')) {
                        $create = false;
                        break;
                    }
                           
                }
                if($create){
                    $folder = new Folder();
                    $folder->setName($request->request->get('name'));
                    $folder->setDescription($request->request->get('desc'));
                    $folder->setUser($user);
                    $folder->setParentFolder($current_folder);
                    $folder->setPath($current_folder->getPath() . $folder->getName() . "/");
                    $errors = $validator->validate($folder);
                    if(count($errors)>0){
                        $error = "El nombre de la carpeta no puede contener espacios.";
                        
                    }else{
                        $em->persist($folder);
                        $em->flush();
                        header( "refresh:0" );
                        $success = true;
                    }
                    
                    
                }else{
                    $error = $error."La carpeta ya existe";
                }
            }
        }
        
        return $this->render('@App/home.html.twig', array(
                    'folders'=>$folders,
                    'files' =>$files,
                     'folder' =>$current_folder,
                     'form'=> $form->createView(),
                     'error'=> $error,
                     'parent_folder' => $parent_folder,
                     'success' => $success
        ));
    }
    
}
