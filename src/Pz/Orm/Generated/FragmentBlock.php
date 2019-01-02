<?php
//Last updated: 2019-01-02 17:20:52
namespace Pz\Orm\Generated;

use Pz\Axiom\Walle;

class FragmentBlock extends Walle
{
    /**
     * #pz text COLLATE utf8mb4_unicode_ci DEFAULT NULL
     */
    private $title;
    
    /**
     * #pz text COLLATE utf8mb4_unicode_ci DEFAULT NULL
     */
    private $twig;
    
    /**
     * #pz text COLLATE utf8mb4_unicode_ci DEFAULT NULL
     */
    private $tags;
    
    /**
     * #pz text COLLATE utf8mb4_unicode_ci DEFAULT NULL
     */
    private $items;
    
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
    public function getTwig()
    {
        return $this->twig;
    }
    
    /**
     * @param mixed twig
     */
    public function setTwig($twig)
    {
        $this->twig = $twig;
    }
    
    /**
     * @return mixed
     */
    public function getTags()
    {
        return $this->tags;
    }
    
    /**
     * @param mixed tags
     */
    public function setTags($tags)
    {
        $this->tags = $tags;
    }
    
    /**
     * @return mixed
     */
    public function getItems()
    {
        return $this->items;
    }
    
    /**
     * @param mixed items
     */
    public function setItems($items)
    {
        $this->items = $items;
    }
    
}