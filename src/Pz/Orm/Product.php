<?php
//Last updated: 2018-11-10 13:15:51
namespace Pz\Orm;

class Product extends \Pz\Orm\Generated\Product
{
    /**
     * @return string
     */
    static public function getCmsOrmsTwig() {
        return 'pz/orms-product.twig';
    }

    public function objVariants()
    {
        return json_decode($this->getVariants());
    }

    public function save($doubleCheckExistence = false)
    {
        if ($this->getVariantProduct() != 1) {

            $variantProductIds = array();

            $objVariants = $this->objVariants();
            foreach ($objVariants as $objVariant) {
                foreach ($objVariant->blocks as &$block) {
                    $product= null;
                    if ($block->values->id) {
                        $product = Product::getByField($this->getPdo(), 'variantProductId', $block->values->id);
                    }
                    if (!$product) {
                        $block->values->id = uniqid();
                        $product = new Product($this->getPdo());
                        $product->setVariantProduct(1);
                        $product->setVariantProductId($block->values->id);
                        $product->setParentProductId($this->getUniqid());
                    }
                    $product->setTitle($block->values->title);
                    $product->setSku($block->values->sku);
                    $product->setBarcode($block->values->barcode);
                    $product->setPrice($block->values->price);
                    $product->setCompareAtPrice($block->values->compareAtPrice);
                    $product->setStockEnabled($block->values->stockEnabled);
                    $product->setStock($block->values->stock);
                    $product->save();

                    $variantProductIds[] = $block->values->id;
                }
            }
            $this->setVariants(json_encode($objVariants));

            /** @var Product[] $products */
            $products = Product::data($this->getPdo(), array(
                'whereSql' => 'm.parentProductId = ?',
                'params' => array($this->getUniqid()),
            ));
            foreach ($products as $product) {
                if (!in_array($product->getVariantProductId(), $variantProductIds)) {
                    $product->delete();
                }
            }
        }

        return parent::save($doubleCheckExistence);
    }
}