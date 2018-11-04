<?php
//Last updated: 2018-11-04 13:02:30
namespace Pz\Orm\Generated;

use Pz\Axiom\Walle;

class User extends Walle
{
    /**
     * #pz text COLLATE utf8mb4_unicode_ci DEFAULT NULL
     */
    private $title;
    
    /**
     * #pz text COLLATE utf8mb4_unicode_ci DEFAULT NULL
     */
    private $password;
    
    /**
     * #pz text COLLATE utf8mb4_unicode_ci DEFAULT NULL
     */
    private $passwordInput;
    
    /**
     * #pz text COLLATE utf8mb4_unicode_ci DEFAULT NULL
     */
    private $name;
    
    /**
     * #pz text COLLATE utf8mb4_unicode_ci DEFAULT NULL
     */
    private $email;
    
    /**
     * #pz text COLLATE utf8mb4_unicode_ci DEFAULT NULL
     */
    private $image;
    
    /**
     * #pz text COLLATE utf8mb4_unicode_ci DEFAULT NULL
     */
    private $gallery;
    
    /**
     * #pz text COLLATE utf8mb4_unicode_ci DEFAULT NULL
     */
    private $resetToken;
    
    /**
     * #pz datetime DEFAULT NULL
     */
    private $resetDate;
    
    /**
     * #pz text COLLATE utf8mb4_unicode_ci DEFAULT NULL
     */
    private $test;
    
    /**
     * #pz text COLLATE utf8mb4_unicode_ci DEFAULT NULL
     */
    private $extra6;
    
    /**
     * #pz text COLLATE utf8mb4_unicode_ci DEFAULT NULL
     */
    private $extra4;
    
    /**
     * @return mixed
     */
    public function getTitle()
    {
        return $this->title;
    }
    
    /**
     * @param mixed title
     */
    public function setTitle($title)
    {
        $this->title = $title;
    }
    
    /**
     * @return mixed
     */
    public function getPassword()
    {
        return $this->password;
    }
    
    /**
     * @param mixed password
     */
    public function setPassword($password)
    {
        $this->password = $password;
    }
    
    /**
     * @return mixed
     */
    public function getPasswordInput()
    {
        return $this->passwordInput;
    }
    
    /**
     * @param mixed passwordInput
     */
    public function setPasswordInput($passwordInput)
    {
        $this->passwordInput = $passwordInput;
    }
    
    /**
     * @return mixed
     */
    public function getName()
    {
        return $this->name;
    }
    
    /**
     * @param mixed name
     */
    public function setName($name)
    {
        $this->name = $name;
    }
    
    /**
     * @return mixed
     */
    public function getEmail()
    {
        return $this->email;
    }
    
    /**
     * @param mixed email
     */
    public function setEmail($email)
    {
        $this->email = $email;
    }
    
    /**
     * @return mixed
     */
    public function getImage()
    {
        return $this->image;
    }
    
    /**
     * @param mixed image
     */
    public function setImage($image)
    {
        $this->image = $image;
    }
    
    /**
     * @return mixed
     */
    public function getGallery()
    {
        return $this->gallery;
    }
    
    /**
     * @param mixed gallery
     */
    public function setGallery($gallery)
    {
        $this->gallery = $gallery;
    }
    
    /**
     * @return mixed
     */
    public function getResetToken()
    {
        return $this->resetToken;
    }
    
    /**
     * @param mixed resetToken
     */
    public function setResetToken($resetToken)
    {
        $this->resetToken = $resetToken;
    }
    
    /**
     * @return mixed
     */
    public function getResetDate()
    {
        return $this->resetDate;
    }
    
    /**
     * @param mixed resetDate
     */
    public function setResetDate($resetDate)
    {
        $this->resetDate = $resetDate;
    }
    
    /**
     * @return mixed
     */
    public function getTest()
    {
        return $this->test;
    }
    
    /**
     * @param mixed test
     */
    public function setTest($test)
    {
        $this->test = $test;
    }
    
    /**
     * @return mixed
     */
    public function getExtra6()
    {
        return $this->extra6;
    }
    
    /**
     * @param mixed extra6
     */
    public function setExtra6($extra6)
    {
        $this->extra6 = $extra6;
    }
    
    /**
     * @return mixed
     */
    public function getExtra4()
    {
        return $this->extra4;
    }
    
    /**
     * @param mixed extra4
     */
    public function setExtra4($extra4)
    {
        $this->extra4 = $extra4;
    }
    
}