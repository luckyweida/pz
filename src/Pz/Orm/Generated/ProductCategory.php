<?php
//Last updated: 2018-11-10 18:50:44
namespace Pz\Orm\Generated;

use Pz\Axiom\Walle;

class ProductCategory extends Walle
{
    /**
     * #pz text COLLATE utf8mb4_unicode_ci DEFAULT NULL
     */
    private $title;
    
    /**
     * #pz text COLLATE utf8mb4_unicode_ci DEFAULT NULL
     */
    private $parentId;
    
    /**
     * #pz text COLLATE utf8mb4_unicode_ci DEFAULT NULL
     */
    private $closed;
    
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
    public function getParentId()
    {
        return $this->parentId;
    }
    
    /**
     * @param mixed parentId
     */
    public function setParentId($parentId)
    {
        $this->parentId = $parentId;
    }
    
    /**
     * @return mixed
     */
    public function getClosed()
    {
        return $this->closed;
    }
    
    /**
     * @param mixed closed
     */
    public function setClosed($closed)
    {
        $this->closed = $closed;
    }
    
}