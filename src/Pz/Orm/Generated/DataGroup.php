<?php
//Last updated: 2019-01-02 17:26:08
namespace Pz\Orm\Generated;

use Pz\Axiom\Walle;

class DataGroup extends Walle
{
    /**
     * #pz text COLLATE utf8mb4_unicode_ci DEFAULT NULL
     */
    private $title;
    
    /**
     * #pz text COLLATE utf8mb4_unicode_ci DEFAULT NULL
     */
    private $icon;
    
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
    public function getIcon()
    {
        return $this->icon;
    }
    
    /**
     * @param mixed icon
     */
    public function setIcon($icon)
    {
        $this->icon = $icon;
    }
    
}