<?php
//Last updated: 2018-09-04 20:00:21
namespace Pz\Orm\Generated;

use Pz\Axiom\Walle;

class AssetSize extends Walle
{
    /**
     * #pz text COLLATE utf8mb4_unicode_ci DEFAULT NULL
     */
    private $title;
    
    /**
     * #pz text COLLATE utf8mb4_unicode_ci DEFAULT NULL
     */
    private $width;
    
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
    public function getWidth()
    {
        return $this->width;
    }
    
    /**
     * @param mixed width
     */
    public function setWidth($width)
    {
        $this->width = $width;
    }
    

    /**
     * @return mixed
     */
    public static function getSerializedModel()
    {
        return "O:13:\"Pz\\Orm\\_Model\":17:{s:30:\"\0Pz\\Orm\\Generated\\_Model\0title\";s:11:\"Asset Sizes\";s:34:\"\0Pz\\Orm\\Generated\\_Model\0className\";s:9:\"AssetSize\";s:34:\"\0Pz\\Orm\\Generated\\_Model\0namespace\";s:6:\"Pz\\Orm\";s:34:\"\0Pz\\Orm\\Generated\\_Model\0modelType\";i:1;s:33:\"\0Pz\\Orm\\Generated\\_Model\0dataType\";i:1;s:33:\"\0Pz\\Orm\\Generated\\_Model\0listType\";i:0;s:38:\"\0Pz\\Orm\\Generated\\_Model\0numberPerPage\";s:2:\"50\";s:38:\"\0Pz\\Orm\\Generated\\_Model\0defaultSortBy\";s:2:\"id\";s:37:\"\0Pz\\Orm\\Generated\\_Model\0defaultOrder\";i:1;s:36:\"\0Pz\\Orm\\Generated\\_Model\0columnsJson\";s:338:\"[{\"id\":\"z1535960623556\",\"column\":\"title\",\"widget\":\"\\\\Symfony\\\\Component\\\\Form\\\\Extension\\\\Core\\\\Type\\\\TextType\",\"label\":\"Title:\",\"field\":\"title\",\"required\":1,\"sql\":\"\"},{\"id\":\"z1535962076537\",\"column\":\"latitude\",\"widget\":\"\\\\Symfony\\\\Component\\\\Form\\\\Extension\\\\Core\\\\Type\\\\TextType\",\"label\":\"Width:\",\"field\":\"width\",\"required\":1,\"sql\":\"\"}]\";s:19:\"\0Pz\\Axiom\\Walle\0pdo\";N;s:18:\"\0Pz\\Axiom\\Walle\0id\";s:1:\"3\";s:20:\"\0Pz\\Axiom\\Walle\0slug\";s:11:\"asset-sizes\";s:20:\"\0Pz\\Axiom\\Walle\0rank\";s:1:\"0\";s:21:\"\0Pz\\Axiom\\Walle\0added\";s:19:\"2018-09-03 19:44:02\";s:24:\"\0Pz\\Axiom\\Walle\0modified\";s:19:\"2018-09-04 19:52:23\";s:22:\"\0Pz\\Axiom\\Walle\0active\";s:1:\"1\";}";
    }
}