<?php
//Last updated: 2019-01-28 21:38:29
namespace Pz\Orm\Generated;

use Pz\Axiom\Walle;

class ContentSnippet extends Walle
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
     * #pz text COLLATE utf8mb4_unicode_ci DEFAULT NULL
     */
    private $heading;
    
    /**
     * #pz text COLLATE utf8mb4_unicode_ci DEFAULT NULL
     */
    private $subheading;
    
    /**
     * #pz text COLLATE utf8mb4_unicode_ci DEFAULT NULL
     */
    private $image;
    
    /**
     * #pz text COLLATE utf8mb4_unicode_ci DEFAULT NULL
     */
    private $url;
    
    /**
     * #pz mediumtext COLLATE utf8mb4_unicode_ci DEFAULT NULL
     */
    private $content;
    
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
    
    /**
     * @return mixed
     */
    public function getHeading()
    {
        return $this->heading;
    }
    
    /**
     * @param mixed heading
     */
    public function setHeading($heading)
    {
        $this->heading = $heading;
    }
    
    /**
     * @return mixed
     */
    public function getSubheading()
    {
        return $this->subheading;
    }
    
    /**
     * @param mixed subheading
     */
    public function setSubheading($subheading)
    {
        $this->subheading = $subheading;
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
    public function getUrl()
    {
        return $this->url;
    }
    
    /**
     * @param mixed url
     */
    public function setUrl($url)
    {
        $this->url = $url;
    }
    
    /**
     * @return mixed
     */
    public function getContent()
    {
        return $this->content;
    }
    
    /**
     * @param mixed content
     */
    public function setContent($content)
    {
        $this->content = $content;
    }
    
}