<?php
//Last updated: 2018-12-15 12:59:19
namespace Pz\Orm\Generated;

use Pz\Axiom\Walle;

class DeliveryOption extends Walle
{
    /**
     * #pz text COLLATE utf8mb4_unicode_ci DEFAULT NULL
     */
    private $title;
    
    /**
     * #pz text COLLATE utf8mb4_unicode_ci DEFAULT NULL
     */
    private $subtitle;
    
    /**
     * #pz mediumtext COLLATE utf8mb4_unicode_ci DEFAULT NULL
     */
    private $content;
    
    /**
     * #pz text COLLATE utf8mb4_unicode_ci DEFAULT NULL
     */
    private $infoHeading;
    
    /**
     * #pz text COLLATE utf8mb4_unicode_ci DEFAULT NULL
     */
    private $infoUrl;
    
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
    public function getSubtitle()
    {
        return $this->subtitle;
    }
    
    /**
     * @param mixed subtitle
     */
    public function setSubtitle($subtitle)
    {
        $this->subtitle = $subtitle;
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
    
    /**
     * @return mixed
     */
    public function getInfoHeading()
    {
        return $this->infoHeading;
    }
    
    /**
     * @param mixed infoHeading
     */
    public function setInfoHeading($infoHeading)
    {
        $this->infoHeading = $infoHeading;
    }
    
    /**
     * @return mixed
     */
    public function getInfoUrl()
    {
        return $this->infoUrl;
    }
    
    /**
     * @param mixed infoUrl
     */
    public function setInfoUrl($infoUrl)
    {
        $this->infoUrl = $infoUrl;
    }
    
}