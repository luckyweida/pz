<?php
//Last updated: 2019-01-02 17:22:35
namespace Pz\Orm\OrmTrait;

use Pz\Orm\Country;

trait TraitCustomerAddress
{
    /**
     * @return string
     */
    public function __toString()
    {
        return
            $this->getFirstname() . ' ' . $this->getLastname() . ' (PH: ' . $this->getPhone() . '), ' .
            $this->getAddress() . ($this->getAddress2() ? ', ' . $this->getAddress2() : '') . ', ' .
            $this->getCity() . ' ' . $this->getPostcode() . ($this->getState() ? ', ' . $this->getState() : '') .
            ($this->objCountry() ? ', ' . $this->objCountry()->getTitle() : '');
    }

    /**
     *
     */
    public function delete() {
        if ($this->getPrimaryAddress() == 1) {
            $customerAddresses = static::active($this->getPdo(), array(
                'whereSql' => 'm.customerId = ? AND m.id != ?',
                'params' => array($this->getCustomerId(), $this->getId()),
            ));

            if (count($customerAddresses)) {
                $customerAddresses[0]->setPrimaryAddress(1);
                $customerAddresses[0]->save();
            }
        }
        parent::delete();
    }

    /**
     * @return Country|null
     */
    public function objCountry() {
        return Country::getByField($this->getPdo(), 'code', $this->getCountry());
    }
}