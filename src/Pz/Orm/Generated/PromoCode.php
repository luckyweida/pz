<?php
//Last updated: 2019-03-23 12:41:38
namespace Pz\Orm\Generated;

use Pz\Axiom\Walle;

class PromoCode extends Walle
{
    /**
     * #pz text COLLATE utf8mb4_unicode_ci DEFAULT NULL
     */
    private $title;
    
    /**
     * #pz text COLLATE utf8mb4_unicode_ci DEFAULT NULL
     */
    private $freeShipping;
    
    /**
     * #pz datetime DEFAULT NULL
     */
    private $startdate;
    
    /**
     * #pz datetime DEFAULT NULL
     */
    private $enddate;
    
    /**
     * #pz text COLLATE utf8mb4_unicode_ci DEFAULT NULL
     */
    private $perc;
    
    /**
     * #pz text COLLATE utf8mb4_unicode_ci DEFAULT NULL
     */
    private $value;
    
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
    public function getFreeShipping()
    {
        return $this->freeShipping;
    }
    
    /**
     * @param mixed freeShipping
     */
    public function setFreeShipping($freeShipping)
    {
        $this->freeShipping = $freeShipping;
    }
    
    /**
     * @return mixed
     */
    public function getStartdate()
    {
        return $this->startdate;
    }
    
    /**
     * @param mixed startdate
     */
    public function setStartdate($startdate)
    {
        $this->startdate = $startdate;
    }
    
    /**
     * @return mixed
     */
    public function getEnddate()
    {
        return $this->enddate;
    }
    
    /**
     * @param mixed enddate
     */
    public function setEnddate($enddate)
    {
        $this->enddate = $enddate;
    }
    
    /**
     * @return mixed
     */
    public function getPerc()
    {
        return $this->perc;
    }
    
    /**
     * @param mixed perc
     */
    public function setPerc($perc)
    {
        $this->perc = $perc;
    }
    
    /**
     * @return mixed
     */
    public function getValue()
    {
        return $this->value;
    }
    
    /**
     * @param mixed value
     */
    public function setValue($value)
    {
        $this->value = $value;
    }
    
}