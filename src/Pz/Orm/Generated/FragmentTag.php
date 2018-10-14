<?php
//Last updated: 2018-10-14 21:12:52
namespace Pz\Orm\Generated;

use Pz\Axiom\Walle;

class FragmentTag extends Walle
{
    /**
     * #pz text COLLATE utf8mb4_unicode_ci DEFAULT NULL
     */
    private $title;
    
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
    
}