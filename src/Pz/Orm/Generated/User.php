<?php
//Last updated: 2018-09-04 20:00:08
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
    private $resetToken;
    
    /**
     * #pz datetime DEFAULT NULL
     */
    private $resetDate;
    
    /**
     * #pz text COLLATE utf8mb4_unicode_ci DEFAULT NULL
     */
    private $image;
    
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
    public static function getSerializedModel()
    {
        return "O:13:\"Pz\\Orm\\_Model\":17:{s:30:\"\0Pz\\Orm\\Generated\\_Model\0title\";s:5:\"Users\";s:34:\"\0Pz\\Orm\\Generated\\_Model\0className\";s:4:\"User\";s:34:\"\0Pz\\Orm\\Generated\\_Model\0namespace\";s:6:\"Pz\\Orm\";s:34:\"\0Pz\\Orm\\Generated\\_Model\0modelType\";i:1;s:33:\"\0Pz\\Orm\\Generated\\_Model\0dataType\";i:1;s:33:\"\0Pz\\Orm\\Generated\\_Model\0listType\";i:0;s:38:\"\0Pz\\Orm\\Generated\\_Model\0numberPerPage\";s:2:\"50\";s:38:\"\0Pz\\Orm\\Generated\\_Model\0defaultSortBy\";s:5:\"title\";s:37:\"\0Pz\\Orm\\Generated\\_Model\0defaultOrder\";i:1;s:36:\"\0Pz\\Orm\\Generated\\_Model\0columnsJson\";s:1329:\"[{\"id\":\"z1535180493499\",\"column\":\"title\",\"widget\":\"\\\\Symfony\\\\Component\\\\Form\\\\Extension\\\\Core\\\\Type\\\\TextType\",\"label\":\"Username:\",\"field\":\"title\",\"required\":1,\"sql\":\"\"},{\"id\":\"z1535183850245\",\"column\":\"password\",\"widget\":\"\\\\Symfony\\\\Component\\\\Form\\\\Extension\\\\Core\\\\Type\\\\HiddenType\",\"label\":\"Password:\",\"field\":\"password\",\"required\":0,\"sql\":\"\"},{\"id\":\"z1535183844503\",\"column\":\"extra1\",\"widget\":\"\\\\Symfony\\\\Component\\\\Form\\\\Extension\\\\Core\\\\Type\\\\PasswordType\",\"label\":\"Password:\",\"field\":\"passwordInput\",\"required\":0,\"sql\":\"\"},{\"id\":\"z1535183141014\",\"column\":\"name\",\"widget\":\"\\\\Symfony\\\\Component\\\\Form\\\\Extension\\\\Core\\\\Type\\\\TextType\",\"label\":\"Name:\",\"field\":\"name\",\"required\":1,\"sql\":\"\"},{\"id\":\"z1535183147451\",\"column\":\"email\",\"widget\":\"\\\\Symfony\\\\Component\\\\Form\\\\Extension\\\\Core\\\\Type\\\\EmailType\",\"label\":\"Email:\",\"field\":\"email\",\"required\":1,\"sql\":\"\"},{\"id\":\"z1535183830887\",\"column\":\"extra2\",\"widget\":\"\\\\Symfony\\\\Component\\\\Form\\\\Extension\\\\Core\\\\Type\\\\TextType\",\"label\":\"Reset token:\",\"field\":\"resetToken\",\"required\":0,\"sql\":\"\"},{\"id\":\"z1535183231304\",\"column\":\"date1\",\"widget\":\"\\\\Pz\\\\Form\\\\Type\\\\DateTimePicker\",\"label\":\"Reset date:\",\"field\":\"resetDate\",\"required\":0,\"sql\":\"\"},{\"id\":\"z1535183126968\",\"column\":\"image\",\"widget\":\"\\\\Pz\\\\Form\\\\Type\\\\AssetPicker\",\"label\":\"Photo:\",\"field\":\"image\",\"required\":0,\"sql\":\"\"}]\";s:19:\"\0Pz\\Axiom\\Walle\0pdo\";N;s:18:\"\0Pz\\Axiom\\Walle\0id\";s:1:\"1\";s:20:\"\0Pz\\Axiom\\Walle\0slug\";s:5:\"users\";s:20:\"\0Pz\\Axiom\\Walle\0rank\";s:1:\"0\";s:21:\"\0Pz\\Axiom\\Walle\0added\";s:19:\"2018-08-25 19:21:03\";s:24:\"\0Pz\\Axiom\\Walle\0modified\";s:19:\"2018-09-03 23:29:43\";s:22:\"\0Pz\\Axiom\\Walle\0active\";s:1:\"1\";}";
    }
}