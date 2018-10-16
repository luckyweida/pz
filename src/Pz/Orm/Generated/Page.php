<?php
//Last updated: 2018-10-16 21:40:59
namespace Pz\Orm\Generated;

use Pz\Axiom\Walle;

class Page extends Walle
{
    /**
     * #pz text COLLATE utf8mb4_unicode_ci DEFAULT NULL
     */
    private $title;
    
    /**
     * #pz text COLLATE utf8mb4_unicode_ci DEFAULT NULL
     */
    private $type;
    
    /**
     * #pz text COLLATE utf8mb4_unicode_ci DEFAULT NULL
     */
    private $redirectTo;
    
    /**
     * #pz text COLLATE utf8mb4_unicode_ci DEFAULT NULL
     */
    private $template;
    
    /**
     * #pz text COLLATE utf8mb4_unicode_ci DEFAULT NULL
     */
    private $category;
    
    /**
     * #pz text COLLATE utf8mb4_unicode_ci DEFAULT NULL
     */
    private $url;
    
    /**
     * #pz mediumtext COLLATE utf8mb4_unicode_ci DEFAULT NULL
     */
    private $content;
    
    /**
     * #pz text COLLATE utf8mb4_unicode_ci DEFAULT NULL
     */
    private $pageTitle;
    
    /**
     * #pz text COLLATE utf8mb4_unicode_ci DEFAULT NULL
     */
    private $description;
    
    /**
     * #pz text COLLATE utf8mb4_unicode_ci DEFAULT NULL
     */
    private $allowExtra;
    
    /**
     * #pz text COLLATE utf8mb4_unicode_ci DEFAULT NULL
     */
    private $maxParams;
    
    /**
     * #pz text COLLATE utf8mb4_unicode_ci DEFAULT NULL
     */
    private $categoryRank;
    
    /**
     * #pz text COLLATE utf8mb4_unicode_ci DEFAULT NULL
     */
    private $categoryParent;
    
    /**
     * #pz text COLLATE utf8mb4_unicode_ci DEFAULT NULL
     */
    private $categoryClosed;
    
    /**
     * #pz text COLLATE utf8mb4_unicode_ci DEFAULT NULL
     */
    private $pinterest;
    
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
    public function getType()
    {
        return $this->type;
    }
    
    /**
     * @param mixed type
     */
    public function setType($type)
    {
        $this->type = $type;
    }
    
    /**
     * @return mixed
     */
    public function getRedirectTo()
    {
        return $this->redirectTo;
    }
    
    /**
     * @param mixed redirectTo
     */
    public function setRedirectTo($redirectTo)
    {
        $this->redirectTo = $redirectTo;
    }
    
    /**
     * @return mixed
     */
    public function getTemplate()
    {
        return $this->template;
    }
    
    /**
     * @param mixed template
     */
    public function setTemplate($template)
    {
        $this->template = $template;
    }
    
    /**
     * @return mixed
     */
    public function getCategory()
    {
        return $this->category;
    }
    
    /**
     * @param mixed category
     */
    public function setCategory($category)
    {
        $this->category = $category;
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
    
    /**
     * @return mixed
     */
    public function getPageTitle()
    {
        return $this->pageTitle;
    }
    
    /**
     * @param mixed pageTitle
     */
    public function setPageTitle($pageTitle)
    {
        $this->pageTitle = $pageTitle;
    }
    
    /**
     * @return mixed
     */
    public function getDescription()
    {
        return $this->description;
    }
    
    /**
     * @param mixed description
     */
    public function setDescription($description)
    {
        $this->description = $description;
    }
    
    /**
     * @return mixed
     */
    public function getAllowExtra()
    {
        return $this->allowExtra;
    }
    
    /**
     * @param mixed allowExtra
     */
    public function setAllowExtra($allowExtra)
    {
        $this->allowExtra = $allowExtra;
    }
    
    /**
     * @return mixed
     */
    public function getMaxParams()
    {
        return $this->maxParams;
    }
    
    /**
     * @param mixed maxParams
     */
    public function setMaxParams($maxParams)
    {
        $this->maxParams = $maxParams;
    }
    
    /**
     * @return mixed
     */
    public function getCategoryRank()
    {
        return $this->categoryRank;
    }
    
    /**
     * @param mixed categoryRank
     */
    public function setCategoryRank($categoryRank)
    {
        $this->categoryRank = $categoryRank;
    }
    
    /**
     * @return mixed
     */
    public function getCategoryParent()
    {
        return $this->categoryParent;
    }
    
    /**
     * @param mixed categoryParent
     */
    public function setCategoryParent($categoryParent)
    {
        $this->categoryParent = $categoryParent;
    }
    
    /**
     * @return mixed
     */
    public function getCategoryClosed()
    {
        return $this->categoryClosed;
    }
    
    /**
     * @param mixed categoryClosed
     */
    public function setCategoryClosed($categoryClosed)
    {
        $this->categoryClosed = $categoryClosed;
    }
    
    /**
     * @return mixed
     */
    public function getPinterest()
    {
        return $this->pinterest;
    }
    
    /**
     * @param mixed pinterest
     */
    public function setPinterest($pinterest)
    {
        $this->pinterest = $pinterest;
    }
    
}