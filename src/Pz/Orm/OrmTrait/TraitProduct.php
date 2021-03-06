<?php
//Last updated: 2019-01-02 17:24:52
namespace Pz\Orm\OrmTrait;

use Pz\Orm\AssetOrm;

trait TraitProduct
{
    /**
     * TraitProduct constructor.
     * @param \PDO $pdo
     */
    public function __construct(\PDO $pdo)
    {
        $this->setProductType(1);
        parent::__construct($pdo);
    }

    public function getPrice($customer = null) {
        $price = parent::getPrice();
        if (!$customer || gettype($customer) == 'string') {
            return $price;
        } else {
            if ($this->getCompareAtPrice()) {
                return $this->getPrice();
            }
            $membership = $customer->objMembership();
            if ($membership) {
                return round(((100 - $membership->getDiscount()) / 100) * $price, 2);
            } else {
                return $price;
            }
        }
    }

    public function getDisplayPrice($customer = null) {
        $price = parent::getDisplayPrice();
        if (!$customer || gettype($customer) == 'string') {
            return $price;
        } else {
            if ($this->getCompareAtPrice()) {
                return $this->getPrice();
            }
            $membership = $customer->objMembership();
            if ($membership) {
                return round(((100 - $membership->getDiscount()) / 100) * $price, 2);
            } else {
                return $price;
            }
        }
    }

    /**
     * @return string
     */
    static public function getCmsOrmsTwig() {
        return 'pz/orms-product.twig';
    }

    /**
     * @return string
     */
    static public function getCmsOrmTwig() {
        return 'pz/orm-product.twig';
    }

    /**
     * @return mixed|null
     */
    public function objParentProduct()
    {
        return static::getById($this->getPdo(), $this->getParentProductId());
    }

    /**
     * @return mixed
     */
    public function nextProduct()
    {
        return static::active($this->getPdo(), array(
            'whereSql' => 'm.id > ?',
            'params' => array($this->getId()),
            'sort' => 'm.id',
            'order' => 'ASC',
            'limit' => 1,
            'oneOrNull' => 1,
        ));
    }

    /**
     * @return mixed
     */
    public function prevProduct()
    {
        return static::active($this->getPdo(), array(
            'whereSql' => 'm.id < ?',
            'params' => array($this->getId()),
            'sort' => 'm.id',
            'order' => 'DESC',
            'limit' => 1,
            'oneOrNull' => 1,
        ));
    }

    /**
     * @return string
     */
    public function getThumbnail() {
        /** @var AssetOrm $result */
        $result = $this->objGallery();
        return count($result) ? $result[0]->getTitle() : '';
    }

    /**
     * @return array|mixed
     */
    public function objDescription()
    {
        $description = $this->getDescription();
        return $description ? json_decode($description) : array();
    }

    /**
     * @return array|mixed
     */
    public function objVariants()
    {
        $variants = $this->getVariants();
        return $variants ? json_decode($variants) : array();
    }

    /**
     * @return array|null
     */
    public function objGallery() {
        return AssetOrm::active($this->getPdo(), array(
            'whereSql' => 'm.modelName = ? AND m.attributeName = ? AND m.ormId = ?',
            'params' => array('Product', 'form_gallery', $this->getUniqid()),
            'sort' => 'm.myRank',
//            'debug' => 1,
        ));
    }

    /**
     * @param bool $doubleCheckExistence
     * @return mixed
     */
    public function save($doubleCheckExistence = false)
    {
        if ($this->getProductType() == 1) {
            $this->setDisplayPrice($this->getPrice());
            $this->setDisplayCompareAtPrice($this->getCompareAtPrice());
            $this->setOnSpecial($this->getCompareAtPrice() ? 1 : 0);

        } elseif ($this->getProductType() == 2) {
            $displayPrice = 99999999;
            $compareAtPrice = 99999999;
            $onSpecial = 0;

            $objVariants = $this->objVariants();
            foreach ($objVariants as $objVariant) {
                foreach ($objVariant->blocks as &$block) {
                    if ($block->values->price < $displayPrice) {
                        $displayPrice = $block->values->price;
                        $compareAtPrice = $block->values->compareAtPrice;
                    }
                    if ($block->values->compareAtPrice) {
                        $onSpecial = 1;
                    }
                }
            }

            $this->setDisplayPrice($displayPrice);
            $this->setDisplayCompareAtPrice($compareAtPrice);
            $this->setOnSpecial($onSpecial);
        }

        if ($this->getVariantProduct() != 1) {

            $variantProductIds = array();

            $objVariants = $this->objVariants();
            foreach ($objVariants as $objVariant) {
                foreach ($objVariant->blocks as &$block) {
                    $product= null;

                    if ($block->values->id) {
                        $product = static::getByField($this->getPdo(), 'variantProductId', $block->values->id);
                    }
                    if (!$product) {
                        $block->values->id = uniqid();
                        $product = new self($this->getPdo());
                        $product->setVariantProduct(1);
                        $product->setVariantProductId($block->values->id);
                        $product->setParentProductId($this->getUniqid());
                    }

                    foreach ($block->values as $idx => $itm) {
                        if ($idx == 'id') {
                            continue;
                        }
                        $setMethod = 'set' . ucfirst($idx);
                        $product->$setMethod($itm);
                    }

                    $product->save();

                    $variantProductIds[] = $block->values->id;
                }
            }
            $this->setVariants(json_encode($objVariants));

            /** @var Product[] $products */
            $products = static::data($this->getPdo(), array(
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

    /**
     * @return mixed
     */
    public function delete()
    {
        $result = static::data($this->getPdo(), array(
            'whereSql' => 'm.parentProductId = ?',
            'params' => array($this->getUniqid()),
        ));
        foreach ($result as $itm) {
            $itm->delete();
        }
        return parent::delete();
    }

    /**
     * Specify data which should be serialized to JSON
     * @link http://php.net/manual/en/jsonserializable.jsonserialize.php
     * @return mixed data which can be serialized by <b>json_encode</b>,
     * which is a value of any type other than a resource.
     * @since 5.4.0
     */
    public function jsonSerialize()
    {
        $fields = array_keys(static::getFields());

        $obj = new \stdClass();
        foreach ($fields as $field) {
            $getMethod = "get" . ucfirst($field);
            $obj->{$field} = $this->$getMethod();
        }
        $obj->thumbnail = $this->getThumbnail();
        return $obj;
    }
}