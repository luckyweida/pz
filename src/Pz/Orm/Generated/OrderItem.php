<?php
//Last updated: 2018-11-26 22:05:34
namespace Pz\Orm\Generated;

use Pz\Axiom\Walle;

class OrderItem extends Walle
{
    /**
     * #pz text COLLATE utf8mb4_unicode_ci DEFAULT NULL
     */
    private $title;
    
    /**
     * #pz text COLLATE utf8mb4_unicode_ci DEFAULT NULL
     */
    private $orderId;
    
    /**
     * #pz text COLLATE utf8mb4_unicode_ci DEFAULT NULL
     */
    private $productId;
    
    /**
     * #pz text COLLATE utf8mb4_unicode_ci DEFAULT NULL
     */
    private $price;
    
    /**
     * #pz text COLLATE utf8mb4_unicode_ci DEFAULT NULL
     */
    private $quantity;
    
    /**
     * #pz text COLLATE utf8mb4_unicode_ci DEFAULT NULL
     */
    private $subtotal;
    
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
    public function getOrderId()
    {
        return $this->orderId;
    }
    
    /**
     * @param mixed orderId
     */
    public function setOrderId($orderId)
    {
        $this->orderId = $orderId;
    }
    
    /**
     * @return mixed
     */
    public function getProductId()
    {
        return $this->productId;
    }
    
    /**
     * @param mixed productId
     */
    public function setProductId($productId)
    {
        $this->productId = $productId;
    }
    
    /**
     * @return mixed
     */
    public function getPrice()
    {
        return $this->price;
    }
    
    /**
     * @param mixed price
     */
    public function setPrice($price)
    {
        $this->price = $price;
    }
    
    /**
     * @return mixed
     */
    public function getQuantity()
    {
        return $this->quantity;
    }
    
    /**
     * @param mixed quantity
     */
    public function setQuantity($quantity)
    {
        $this->quantity = $quantity;
    }
    
    /**
     * @return mixed
     */
    public function getSubtotal()
    {
        return $this->subtotal;
    }
    
    /**
     * @param mixed subtotal
     */
    public function setSubtotal($subtotal)
    {
        $this->subtotal = $subtotal;
    }
    
}