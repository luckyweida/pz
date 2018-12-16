<?php
//Last updated: 2018-11-10 16:54:41
namespace Pz\Orm;

use Web\Service\CartService;

class Order extends \Pz\Orm\Generated\Order implements \Serializable
{
    /** @var OrderItem[] $pendingItems */
    private $pendingItems;

    /** @var OrderItem[] $orderItems */
    private $orderItems;

    /** @var DeliveryOption[] $deliveryOptions */
    private $deliveryOptions;

    public function __construct(\PDO $pdo)
    {
        $this->setBillingSame(1);
        $this->setDeliveryStatus(CartService::DELIVERY_HIDDEN());
        $this->setPayStatus(CartService::STATUS_UNPAID());

        $this->pendingItems = array();
        $this->orderItems = array();
        $this->deliveryOptions = array();

        parent::__construct($pdo);
    }

    /**
     * @return array|Country[]
     */
    public function getAvailableCountries()
    {
        /** @var Country[] $result */
        $result = array();
        /** @var DeliveryOption[] $result */
        $deliveryOptions = DeliveryOption::active($pdo);
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
     * @return OrderItem[]
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
     * @return OrderItem[]
     */
    public function getOrderItems()
    {
        $this->orderItems = OrderItem::active($this->getPdo(), array(
            'whereSql' => 'm.orderId = ?',
            'params' => array($this->getUniqid()),
        ));
        return $this->orderItems;
    }

    /**
     * @param array $pendingItems
     */
    public function setOrderItems(array $orderItems): void
    {
        $this->orderItems = $orderItems;
    }

    /**
     * @return DeliveryOption[]
     */
    public function getDeliveryOptions()
    {
        /** @var Country[] $result */
        $result = array();
        /** @var DeliveryOption[] $result */
        $deliveryOptions = DeliveryOption::active($pdo);
//        var_dump($this->getCou)
        foreach ($deliveryOptions as $itm) {
            $result = array_merge($result, $itm->objCountries());
        }
        return $result;
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
        return $obj;
    }
}