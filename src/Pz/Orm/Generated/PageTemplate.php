<?php
//Last updated: 2019-01-02 17:20:29
namespace Pz\Orm\Generated;

use Pz\Axiom\Walle;

class PageTemplate extends Walle
{
    /**
     * #pz text COLLATE utf8mb4_unicode_ci DEFAULT NULL
     */
    private $title;
    
    /**
     * #pz text COLLATE utf8mb4_unicode_ci DEFAULT NULL
     */
    private $filename;
    
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
    public function getFilename()
    {
        return $this->filename;
    }
    
    /**
     * @param mixed filename
     */
    public function setFilename($filename)
    {
        $this->filename = $filename;
    }
    
}