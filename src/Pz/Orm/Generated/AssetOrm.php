<?php
//Last updated: 2018-11-10 13:13:32
namespace Pz\Orm\Generated;

use Pz\Axiom\Walle;

class AssetOrm extends Walle
{
    /**
     * #pz text COLLATE utf8mb4_unicode_ci DEFAULT NULL
     */
    private $title;
    
    /**
     * #pz text COLLATE utf8mb4_unicode_ci DEFAULT NULL
     */
    private $modelName;
    
    /**
     * #pz text COLLATE utf8mb4_unicode_ci DEFAULT NULL
     */
    private $attributeName;
    
    /**
     * #pz text COLLATE utf8mb4_unicode_ci DEFAULT NULL
     */
    private $ormId;
    
    /**
     * #pz text COLLATE utf8mb4_unicode_ci DEFAULT NULL
     */
    private $myRank;
    
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
    public function getModelName()
    {
        return $this->modelName;
    }
    
    /**
     * @param mixed modelName
     */
    public function setModelName($modelName)
    {
        $this->modelName = $modelName;
    }
    
    /**
     * @return mixed
     */
    public function getAttributeName()
    {
        return $this->attributeName;
    }
    
    /**
     * @param mixed attributeName
     */
    public function setAttributeName($attributeName)
    {
        $this->attributeName = $attributeName;
    }
    
    /**
     * @return mixed
     */
    public function getOrmId()
    {
        return $this->ormId;
    }
    
    /**
     * @param mixed ormId
     */
    public function setOrmId($ormId)
    {
        $this->ormId = $ormId;
    }
    
    /**
     * @return mixed
     */
    public function getMyRank()
    {
        return $this->myRank;
    }
    
    /**
     * @param mixed myRank
     */
    public function setMyRank($myRank)
    {
        $this->myRank = $myRank;
    }
    
}