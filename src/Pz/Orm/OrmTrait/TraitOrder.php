<?php
//Last updated: 2019-01-02 17:26:45
namespace Pz\Orm\OrmTrait;

use Pz\Orm\Country;
use Pz\Orm\DeliveryOption;
use Pz\Orm\PromoCode;
use Pz\Service\CartService;

trait TraitOrder
{
    /** @var OrderItem[] $pendingItems */
    private $pendingItems;

    /** @var OrderItem[] $orderItems */
    private $orderItems;

    /** @var DeliveryOption[] $deliveryOptions */
    private $deliveryOptions;

    /**
     * TraitOrder constructor.
     * @param \PDO $pdo
     */
    public function __construct(\PDO $pdo)
    {
        $this->setBillingSame(1);
        $this->setDeliveryOptionStatus(CartService::DELIVERY_HIDDEN);
        $this->setPayStatus(CartService::STATUS_UNPAID);

        $this->pendingItems = array();
        $this->orderItems = array();
        $this->deliveryOptions = array();

        parent::__construct($pdo);
    }

    /**
     * @return int|mixed
     */
    public function getTotalWeight() {
        $totalWeight = 0;
        $pendingItems = $this->getPendingItems();
        foreach ($pendingItems as $pendingItem) {
            $totalWeight += $pendingItem->getWeight() * $pendingItem->getQuantity();
        }
        return $totalWeight;
    }

    /**
     * @return array|Country[]
     */
    public function getAvailableCountries()
    {
        /** @var Country[] $result */
        $result = array();
        /** @var DeliveryOption[] $result */
        $deliveryOptions = DeliveryOption::active($this->getPdo());
        foreach ($deliveryOptions as $itm) {
            $result = array_merge($result, $itm->objCountries());
        }
        return $result;
    }

    /**
     * @param $pendingItem
     */
    public function addPendingItem($pendingItem)
    {
        $this->pendingItems[] = $pendingItem;
    }

    /**
     * @return array
     */
    public function getPendingItems()
    {
        return $this->pendingItems;
    }

    /**
     * @param array $pendingItems
     */
    public function setPendingItems(array $pendingItems): void
    {
        $this->pendingItems = $pendingItems;
    }

    /**
     * @return array
     */
    public function getOrderItems()
    {
        if ($this->getOrderItemClass()) {
            return $this->getOrderItemClass()::active($this->getPdo(), array(
                'whereSql' => 'm.orderId = ?',
                'params' => array($this->getUniqid()),
            ));
        } else {
            return array();
        }
    }

    /**
     * @param array $pendingItems
     */
    public function setOrderItems(array $orderItems): void
    {
        $this->orderItems = $orderItems;
    }

    /**
     * @return mixed
     */
    public function getCountryCode()
    {
        return $this->getBillingSame() ? $this->getBillingCountry() : $this->getShippingCountry();
    }

    /**
     * @return DeliveryOption[]
     */
    public function getDeliveryOptions()
    {

        /** @var DeliveryOption[] $result */
        $result = DeliveryOption::active($this->getPdo());
        /** @var DeliveryOption[] $deliveryOptions */
        $deliveryOptions = $result;

        $countryCode = $this->getCountryCode();
        if ($countryCode) {
            /** @var Country $country */
            $country = Country::getByField($this->getPdo(), 'code', $countryCode);
            if ($country) {
                $deliveryOptions = array();
                foreach ($result as $deliveryOption) {
                    $deliveryOption->calculatePrice($this);
                    $valid = false;
                    $objContent = $deliveryOption->objContent();
                    foreach ($objContent as $section) {
                        foreach ($section->blocks as $block) {
                            if (in_array($country->getId(), $block->values->countries)) {
                                $valid = true;
                            }
                        }
                    }
                    if ($valid) {
                        $deliveryOptions[] = $deliveryOption;
                    }
                }

            }
        }
        $this->deliveryOptions = $deliveryOptions;
        return $this->deliveryOptions;
    }

    /**
     * @param array $deliveryOptions
     */
    public function setDeliveryOptions(array $deliveryOptions): void
    {
        $this->deliveryOptions = $deliveryOptions;
    }

    /**
     * @return bool
     */
    public function update() {
        $result = 0;

        $pendingItems = $this->getPendingItems();
        foreach ($pendingItems as $pendingItem) {
            $result += $pendingItem->getSubtotal();
        }
        $subtotal = $result;

        $discount = 0;
        $freeDelivery = 0;

        /** @var PromoCode $promoCode */
        $promoCode = PromoCode::getByField($this->getPdo(), 'title', $this->getPromoCode());
        if ($promoCode) {
            $valid = true;
            if ($promoCode->getStartdate() && strtotime($promoCode->getStartdate()) >= time()) {
                $valid = false;
            }
            if ($promoCode->getEnddate() && strtotime($promoCode->getEnddate()) <= time()) {
                $valid = false;
            }

            if ($valid) {
                if ($promoCode->getFreeShipping() == 1) {
                    $freeDelivery = 1;
                } else {
                    if ($promoCode->getPerc() == 1) {
                        $discount = round(($promoCode->getValue() / 100) * $subtotal, 2);
                    } else {
                        $discount = $promoCode->getValue();
                    }
                }

            }
        }

        $afterDiscount = $subtotal - $discount;
        $gst = round(($afterDiscount * 3) / 23, 2);


        $deliveryFee = 0;

        $countryCode = $this->getCountryCode();
        $deliveryOptions = $this->getDeliveryOptions();
        if ($countryCode) {
            /** @var DeliveryOption $firstDeliveryOption */
            $selectedDeliveryOption = $deliveryOptions[0];
            $deliveryOptionId = $this->getDeliveryOptionId();

            foreach ($deliveryOptions as $deliveryOption) {
                if ($deliveryOption->getId() == $deliveryOptionId) {
                    $selectedDeliveryOption = $deliveryOption;
                }
            }

            $deliveryFee = $freeDelivery ? 0 : $selectedDeliveryOption->getPrice();

            $this->setDeliveryOptionDescription($selectedDeliveryOption->getTitle());
            $this->setDeliveryOptionId($selectedDeliveryOption->getId());
            $this->setDeliveryOptionStatus(CartService::DELIVERY_VISIBLE);
        } else {
            $this->setDeliveryOptionDescription('');
            $this->setDeliveryOptionId(null);
            $this->setDeliveryOptionStatus(CartService::DELIVERY_HIDDEN);
        }

        $total = $subtotal - $discount + max($deliveryFee, 0);

        $this->setDeliveryFee($deliveryFee);
        $this->setDiscount($discount);
        $this->setAfterDiscount($afterDiscount);
        $this->setSubtotal($subtotal);
        $this->setGst($gst);
        $this->setTotal($total);

        return true;
    }

    /**
     * String representation of object
     * @link http://php.net/manual/en/serializable.serialize.php
     * @return string the string representation of the object or null
     * @since 5.1.0
     */
    public function serialize()
    {

        $fields = array_keys(static::getFields());

        $obj = new \stdClass();
        foreach ($fields as $field) {
            $getMethod = "get" . ucfirst($field);
            $obj->{$field} = $this->$getMethod();
        }
        $obj->pendingItems = $this->getPendingItems();
        $obj->deliveryOptions = $this->getDeliveryOptions();
        return serialize($obj);
    }

    /**
     * Constructs the object
     * @link http://php.net/manual/en/serializable.unserialize.php
     * @param string $serialized <p>
     * The string representation of the object.
     * </p>
     * @return void
     * @since 5.1.0
     */
    public function unserialize($serialized)
    {
        $obj = unserialize($serialized);
        foreach ($obj as $idx => $itm) {
            $setMethod = "set" . ucfirst($idx);
            $this->$setMethod($itm);
        }
        $this->setPendingItems($obj->pendingItems);
        $this->setDeliveryOptions($obj->deliveryOptions);

        $conn = \Doctrine\DBAL\DriverManager::getConnection(array(
            'url' => getenv('DATABASE_URL'),
        ), new \Doctrine\DBAL\Configuration());
        $this->setPdo($conn->getWrappedConnection());
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
        $obj->pendingItems = $this->getPendingItems();
        $obj->deliveryOptions = $this->getDeliveryOptions();
        return $obj;
    }
}