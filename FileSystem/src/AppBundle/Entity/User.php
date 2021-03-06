<?php

namespace AppBundle\Entity;


use Symfony\Component\Security\Core\User\UserInterface;
/**
 * User
 */
class User implements UserInterface
{
    /**
     * @var int
     */
    private $id;

    /**
     * @var string
     */
    private $name;

    /**
     * @var string
     */
    private $email;

    /**
     * @var string
     */
    private $password;


    /**
     * Get id
     *
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set name
     *
     * @param string $name
     *
     * @return User
     */
    public function setName($name)
    {
        $this->name = $name;

        return $this;
    }

    /**
     * Get name
     *
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Set email
     *
     * @param string $email
     *
     * @return User
     */
    public function setEmail($email)
    {
        $this->email = $email;

        return $this;
    }

    /**
     * Get email
     *
     * @return string
     */
    public function getEmail()
    {
        return $this->email;
    }

    /**
     * Set password
     *
     * @param string $password
     *
     * @return User
     */
    public function setPassword($password)
    {
        $this->password = $password;

        return $this;
    }

    /**
     * Get password
     *
     * @return string
     */
    public function getPassword()
    {
        return $this->password;
    }

    public function eraseCredentials() {
        
    }

    public function getRoles() {
        return array("ROLE_USER");
    }

    public function getSalt() {
        return $this->getName();
    }

    public function getUsername() {
        return $this->getEmail();
    }

    /**
     * @var string
     */
    private $surname;

    /**
     * @var string
     */
    private $role;

    /**
     * @var \Doctrine\Common\Collections\Collection
     */
    private $folders;

    /**
     * @var \Doctrine\Common\Collections\Collection
     */
    private $files;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->folders = new \Doctrine\Common\Collections\ArrayCollection();
        $this->files = new \Doctrine\Common\Collections\ArrayCollection();
    }

    /**
     * Set surname
     *
     * @param string $surname
     *
     * @return User
     */
    public function setSurname($surname)
    {
        $this->surname = $surname;

        return $this;
    }

    /**
     * Get surname
     *
     * @return string
     */
    public function getSurname()
    {
        return $this->surname;
    }

    /**
     * Set role
     *
     * @param string $role
     *
     * @return User
     */
    public function setRole()
    {
        $this->role = $this->getRoles()[0];

        return $this;
    }

    /**
     * Get role
     *
     * @return string
     */
    public function getRole()
    {
        return $this->role;
    }

    /**
     * Add folder
     *
     * @param \AppBundle\Entity\Folder $folder
     *
     * @return User
     */
    public function addFolder(\AppBundle\Entity\Folder $folder)
    {
        $this->folders[] = $folder;

        return $this;
    }

    /**
     * Remove folder
     *
     * @param \AppBundle\Entity\Folder $folder
     */
    public function removeFolder(\AppBundle\Entity\Folder $folder)
    {
        $this->folders->removeElement($folder);
    }

    /**
     * Get folders
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getFolders()
    {
        return $this->folders;
    }

    /**
     * Add file
     *
     * @param \AppBundle\Entity\File $file
     *
     * @return User
     */
    public function addFile(\AppBundle\Entity\File $file)
    {
        $this->files[] = $file;

        return $this;
    }

    /**
     * Remove file
     *
     * @param \AppBundle\Entity\File $file
     */
    public function removeFile(\AppBundle\Entity\File $file)
    {
        $this->files->removeElement($file);
    }

    /**
     * Get files
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getFiles()
    {
        return $this->files;
    }
}
