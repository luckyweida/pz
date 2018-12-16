<?php
//Last updated: 2018-12-16 21:46:24
namespace Pz\Orm\Generated;

use Pz\Axiom\Walle;

class Product extends Walle
{
    /**
     * #pz text COLLATE utf8mb4_unicode_ci DEFAULT NULL
     */
    private $title;
    
    /**
     * #pz text COLLATE utf8mb4_unicode_ci DEFAULT NULL
     */
    private $category;
    
    /**
     * #pz text COLLATE utf8mb4_unicode_ci DEFAULT NULL
     */
    private $myRank;
    
    /**
     * #pz text COLLATE utf8mb4_unicode_ci DEFAULT NULL
     */
    private $productType;
    
    /**
     * #pz text COLLATE utf8mb4_unicode_ci DEFAULT NULL
     */
    private $sku;
    
    /**
     * #pz text COLLATE utf8mb4_unicode_ci DEFAULT NULL
     */
    private $barcode;
    
    /**
     * #pz text COLLATE utf8mb4_unicode_ci DEFAULT NULL
     */
    private $price;
    
    /**
     * #pz text COLLATE utf8mb4_unicode_ci DEFAULT NULL
     */
    private $compareAtPrice;
    
    /**
     * #pz text COLLATE utf8mb4_unicode_ci DEFAULT NULL
     */
    private $stockEnabled;
    
    /**
     * #pz text COLLATE utf8mb4_unicode_ci DEFAULT NULL
     */
    private $stock;
    
    /**
     * #pz text COLLATE utf8mb4_unicode_ci DEFAULT NULL
     */
    private $weight;
    
    /**
     * #pz text COLLATE utf8mb4_unicode_ci DEFAULT NULL
     */
    private $variants;
    
    /**
     * #pz text COLLATE utf8mb4_unicode_ci DEFAULT NULL
     */
    private $gallery;
    
    /**
     * #pz text COLLATE utf8mb4_unicode_ci DEFAULT NULL
     */
    private $description;
    
    /**
     * #pz text COLLATE utf8mb4_unicode_ci DEFAULT NULL
     */
    private $variantProduct;
    
    /**
     * #pz text COLLATE utf8mb4_unicode_ci DEFAULT NULL
     */
    private $variantProductId;
    
    /**
     * #pz text COLLATE utf8mb4_unicode_ci DEFAULT NULL
     */
    private $parentProductId;
    
    /**
     * #pz text COLLATE utf8mb4_unicode_ci DEFAULT NULL
     */
    private $onSpecial;
    
    /**
     * #pz text COLLATE utf8mb4_unicode_ci DEFAULT NULL
     */
    private $displayPrice;
    
    /**
     * #pz text COLLATE utf8mb4_unicode_ci DEFAULT NULL
     */
    private $displayCompareAtPrice;
    
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
    public function getMyRank()
    {
        return $this->myRank;
    }
    
    /**
     * @param mixed myRank
     */
    public function setMyRank($myRank)
    {
        $this->myRank = $myRank;
    }
    
    /**
     * @return mixed
     */
    public function getProductType()
    {
        return $this->productType;
    }
    
    /**
     * @param mixed productType
     */
    public function setProductType($productType)
    {
        $this->productType = $productType;
    }
    
    /**
     * @return mixed
     */
    public function getSku()
    {
        return $this->sku;
    }
    
    /**
     * @param mixed sku
     */
    public function setSku($sku)
    {
        $this->sku = $sku;
    }
    
    /**
     * @return mixed
     */
    public function getBarcode()
    {
        return $this->barcode;
    }
    
    /**
     * @param mixed barcode
     */
    public function setBarcode($barcode)
    {
        $this->barcode = $barcode;
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
    public function getCompareAtPrice()
    {
        return $this->compareAtPrice;
    }
    
    /**
     * @param mixed compareAtPrice
     */
    public function setCompareAtPrice($compareAtPrice)
    {
        $this->compareAtPrice = $compareAtPrice;
    }
    
    /**
     * @return mixed
     */
    public function getStockEnabled()
    {
        return $this->stockEnabled;
    }
    
    /**
     * @param mixed stockEnabled
     */
    public function setStockEnabled($stockEnabled)
    {
        $this->stockEnabled = $stockEnabled;
    }
    
    /**
     * @return mixed
     */
    public function getStock()
    {
        return $this->stock;
    }
    
    /**
     * @param mixed stock
     */
    public function setStock($stock)
    {
        $this->stock = $stock;
    }
    
    /**
     * @return mixed
     */
    public function getWeight()
    {
        return $this->weight;
    }
    
    /**
     * @param mixed weight
     */
    public function setWeight($weight)
    {
        $this->weight = $weight;
    }
    
    /**
     * @return mixed
     */
    public function getVariants()
    {
        return $this->variants;
    }
    
    /**
     * @param mixed variants
     */
    public function setVariants($variants)
    {
        $this->variants = $variants;
    }
    
    /**
     * @return mixed
     */
    public function getGallery()
    {
        return $this->gallery;
    }
    
    /**
     * @param mixed gallery
     */
    public function setGallery($gallery)
    {
        $this->gallery = $gallery;
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
    public function getVariantProduct()
    {
        return $this->variantProduct;
    }
    
    /**
     * @param mixed variantProduct
     */
    public function setVariantProduct($variantProduct)
    {
        $this->variantProduct = $variantProduct;
    }
    
    /**
     * @return mixed
     */
    public function getVariantProductId()
    {
        return $this->variantProductId;
    }
    
    /**
     * @param mixed variantProductId
     */
    public function setVariantProductId($variantProductId)
    {
        $this->variantProductId = $variantProductId;
    }
    
    /**
     * @return mixed
     */
    public function getParentProductId()
    {
        return $this->parentProductId;
    }
    
    /**
     * @param mixed parentProductId
     */
    public function setParentProductId($parentProductId)
    {
        $this->parentProductId = $parentProductId;
    }
    
    /**
     * @return mixed
     */
    public function getOnSpecial()
    {
        return $this->onSpecial;
    }
    
    /**
     * @param mixed onSpecial
     */
    public function setOnSpecial($onSpecial)
    {
        $this->onSpecial = $onSpecial;
    }
    
    /**
     * @return mixed
     */
    public function getDisplayPrice()
    {
        return $this->displayPrice;
    }
    
    /**
     * @param mixed displayPrice
     */
    public function setDisplayPrice($displayPrice)
    {
        $this->displayPrice = $displayPrice;
    }
    
    /**
     * @return mixed
     */
    public function getDisplayCompareAtPrice()
    {
        return $this->displayCompareAtPrice;
    }
    
    /**
     * @param mixed displayCompareAtPrice
     */
    public function setDisplayCompareAtPrice($displayCompareAtPrice)
    {
        $this->displayCompareAtPrice = $displayCompareAtPrice;
    }
    
}