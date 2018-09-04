<?php
//Last updated: 2018-09-04 20:00:15
namespace Pz\Orm\Generated;

use Pz\Axiom\Walle;

class Asset extends Walle
{
    /**
     * #pz text COLLATE utf8mb4_unicode_ci DEFAULT NULL
     */
    private $title;
    
    /**
     * #pz text COLLATE utf8mb4_unicode_ci DEFAULT NULL
     */
    private $description;
    
    /**
     * #pz text COLLATE utf8mb4_unicode_ci DEFAULT NULL
     */
    private $isFolder;
    
    /**
     * #pz text COLLATE utf8mb4_unicode_ci DEFAULT NULL
     */
    private $fileName;
    
    /**
     * #pz text COLLATE utf8mb4_unicode_ci DEFAULT NULL
     */
    private $fileType;
    
    /**
     * #pz text COLLATE utf8mb4_unicode_ci DEFAULT NULL
     */
    private $fileSize;
    
    /**
     * #pz text COLLATE utf8mb4_unicode_ci DEFAULT NULL
     */
    private $fileLocation;
    
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
    public function getDescription()
    {
        return $this->description;
    }
    
    /**
     * @param mixed description
     */
    public function setDescription($description)
    {
        $this->description = $description;
    }
    
    /**
     * @return mixed
     */
    public function getIsFolder()
    {
        return $this->isFolder;
    }
    
    /**
     * @param mixed isFolder
     */
    public function setIsFolder($isFolder)
    {
        $this->isFolder = $isFolder;
    }
    
    /**
     * @return mixed
     */
    public function getFileName()
    {
        return $this->fileName;
    }
    
    /**
     * @param mixed fileName
     */
    public function setFileName($fileName)
    {
        $this->fileName = $fileName;
    }
    
    /**
     * @return mixed
     */
    public function getFileType()
    {
        return $this->fileType;
    }
    
    /**
     * @param mixed fileType
     */
    public function setFileType($fileType)
    {
        $this->fileType = $fileType;
    }
    
    /**
     * @return mixed
     */
    public function getFileSize()
    {
        return $this->fileSize;
    }
    
    /**
     * @param mixed fileSize
     */
    public function setFileSize($fileSize)
    {
        $this->fileSize = $fileSize;
    }
    
    /**
     * @return mixed
     */
    public function getFileLocation()
    {
        return $this->fileLocation;
    }
    
    /**
     * @param mixed fileLocation
     */
    public function setFileLocation($fileLocation)
    {
        $this->fileLocation = $fileLocation;
    }
    

    /**
     * @return mixed
     */
    public static function getSerializedModel()
    {
        return "O:13:\"Pz\\Orm\\_Model\":17:{s:30:\"\0Pz\\Orm\\Generated\\_Model\0title\";s:6:\"Assets\";s:34:\"\0Pz\\Orm\\Generated\\_Model\0className\";s:5:\"Asset\";s:34:\"\0Pz\\Orm\\Generated\\_Model\0namespace\";s:6:\"Pz\\Orm\";s:34:\"\0Pz\\Orm\\Generated\\_Model\0modelType\";i:1;s:33:\"\0Pz\\Orm\\Generated\\_Model\0dataType\";i:2;s:33:\"\0Pz\\Orm\\Generated\\_Model\0listType\";i:0;s:38:\"\0Pz\\Orm\\Generated\\_Model\0numberPerPage\";s:2:\"50\";s:38:\"\0Pz\\Orm\\Generated\\_Model\0defaultSortBy\";s:2:\"id\";s:37:\"\0Pz\\Orm\\Generated\\_Model\0defaultOrder\";i:1;s:36:\"\0Pz\\Orm\\Generated\\_Model\0columnsJson\";s:1242:\"[{\"id\":\"z1535960350583\",\"column\":\"title\",\"widget\":\"\\\\Symfony\\\\Component\\\\Form\\\\Extension\\\\Core\\\\Type\\\\TextType\",\"label\":\"Title:\",\"field\":\"title\",\"required\":1,\"sql\":\"\"},{\"id\":\"z1535960414591\",\"column\":\"description\",\"widget\":\"\\\\Symfony\\\\Component\\\\Form\\\\Extension\\\\Core\\\\Type\\\\TextAreaType\",\"label\":\"Description:\",\"field\":\"description\",\"required\":0,\"sql\":\"\"},{\"id\":\"z1535960420704\",\"column\":\"extra1\",\"widget\":\"\\\\Symfony\\\\Component\\\\Form\\\\Extension\\\\Core\\\\Type\\\\HiddenType\",\"label\":\"Is folder?\",\"field\":\"isFolder\",\"required\":0,\"sql\":\"\"},{\"id\":\"z1535960424545\",\"column\":\"extra2\",\"widget\":\"\\\\Symfony\\\\Component\\\\Form\\\\Extension\\\\Core\\\\Type\\\\TextType\",\"label\":\"File name:\",\"field\":\"fileName\",\"required\":0,\"sql\":\"\"},{\"id\":\"z1535960428311\",\"column\":\"extra4\",\"widget\":\"\\\\Symfony\\\\Component\\\\Form\\\\Extension\\\\Core\\\\Type\\\\TextType\",\"label\":\"File type:\",\"field\":\"fileType\",\"required\":0,\"sql\":\"\"},{\"id\":\"z1535960430473\",\"column\":\"extra5\",\"widget\":\"\\\\Symfony\\\\Component\\\\Form\\\\Extension\\\\Core\\\\Type\\\\TextType\",\"label\":\"File size:\",\"field\":\"fileSize\",\"required\":0,\"sql\":\"\"},{\"id\":\"z1535960433312\",\"column\":\"extra6\",\"widget\":\"\\\\Symfony\\\\Component\\\\Form\\\\Extension\\\\Core\\\\Type\\\\TextType\",\"label\":\"File location:\",\"field\":\"fileLocation\",\"required\":0,\"sql\":\"\"}]\";s:19:\"\0Pz\\Axiom\\Walle\0pdo\";N;s:18:\"\0Pz\\Axiom\\Walle\0id\";s:1:\"2\";s:20:\"\0Pz\\Axiom\\Walle\0slug\";s:6:\"assets\";s:20:\"\0Pz\\Axiom\\Walle\0rank\";s:1:\"0\";s:21:\"\0Pz\\Axiom\\Walle\0added\";s:19:\"2018-09-03 19:42:12\";s:24:\"\0Pz\\Axiom\\Walle\0modified\";s:19:\"2018-09-03 21:07:14\";s:22:\"\0Pz\\Axiom\\Walle\0active\";s:1:\"1\";}";
    }
}