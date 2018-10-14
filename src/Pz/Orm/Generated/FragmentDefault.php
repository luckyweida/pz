<?php
//Last updated: 2018-10-14 21:11:20
namespace Pz\Orm\Generated;

use Pz\Axiom\Walle;

class FragmentDefault extends Walle
{
    /**
     * #pz text COLLATE utf8mb4_unicode_ci DEFAULT NULL
     */
    private $title;
    
    /**
     * #pz text COLLATE utf8mb4_unicode_ci DEFAULT NULL
     */
    private $attr;
    
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
    public function getAttr()
    {
        return $this->attr;
    }
    
    /**
     * @param mixed attr
     */
    public function setAttr($attr)
    {
        $this->attr = $attr;
    }
    
}