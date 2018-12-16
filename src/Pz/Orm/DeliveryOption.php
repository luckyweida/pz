<?php
//Last updated: 2018-12-15 11:38:58
namespace Pz\Orm;

class DeliveryOption extends \Pz\Orm\Generated\DeliveryOption implements \Serializable
{
    /**
     * @var float
     */
    protected $price;

    /**
     * @param Order $orderContainer
     */
    public function calculatePrice(Order $orderContainer): void
    {


        $countryCode = $orderContainer->getCountryCode();
        if ($countryCode) {
            /** @var Country $country */
            $country = Country::getByField($this->getPdo(), 'code', $countryCode);
            if ($country) {
                $selectedBlock = null;
                $objContent = $this->objContent();
                foreach ($objContent as $section) {
                    foreach ($section->blocks as $block) {
                        if (in_array($country->getId(), $block->values->countries)) {
                            $selectedBlock = $block;
                        }
                    }
                }
                if (!$selectedBlock) {
                    $this->setPrice(0);
                } else {
                    if (!$selectedBlock->values->basePrice) {
                        $this->setPrice(0);
                    }

                    $totalWeight = $orderContainer->getTotalWeight();
                    if ($totalWeight <= $selectedBlock->values->baseWeight || !$selectedBlock->values->baseWeight) {
                        $this->setPrice($selectedBlock->values->basePrice);
                    }

                    if ($selectedBlock->values->baseWeight && $totalWeight > $selectedBlock->values->baseWeight) {
                        $units = ceil(($totalWeight - $selectedBlock->values->baseWeight) / $selectedBlock->values->extraWeight);
                        $deliveryFee = $selectedBlock->values->basePrice + ($units * $selectedBlock->values->extraPrice);
                        $this->setPrice($deliveryFee);
                    }
                }
            }

        } else {
            $this->setPrice(0);
        }


    }

    /**
     * @return float
     */
    public function getPrice(): float
    {
        return $this->price ?: 0;
    }

    /**
     * @param float $price
     */
    public function setPrice(float $price): void
    {
        $this->price = $price;
    }

    /**
     * @return mixed
     */
    public function objContent()
    {
        return json_decode($this->getContent());
    }

    /**
     * @return array
     */
    public function objCountryIds()
    {
        $result = array();
        $objContent = $this->objContent();
        foreach ($objContent as $section) {
            foreach ($section->blocks as $block) {
                $result = array_merge($result, $block->values->countries);
            }
        }
        return $result;
    }

    /**
     * @return array|Country[]
     */
    public function objCountries()
    {
        $countries = array();
        /** @var Country[] $result */
        $result = Country::active($this->getPdo());
        foreach ($result as $itm) {
            $countries[$itm->getId()] = $itm;
        }

        $result = array();
        $objCountryIds = $this->objCountryIds();
        foreach ($objCountryIds as $itm) {
            if (isset($countries[$itm])) {
                $result[] = $countries[$itm];
            }
        }
        return $result;
    }

    /**
     * @param Order $orderContainer
     * @return bool
     */
    public function hasDeliveryPrice(Order $orderContainer)
    {
        return $orderContainer->getCountryCode() ? true : false;
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
        $obj->price = $this->getPrice();
        return $obj;
    }
}