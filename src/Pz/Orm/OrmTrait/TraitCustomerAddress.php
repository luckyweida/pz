<?php
//Last updated: 2019-01-02 17:22:35
namespace Pz\Orm\OrmTrait;

use Pz\Orm\Country;

trait TraitCustomerAddress
{
    public function __toString()
    {
        return
            $this->getFirstname() . ' ' . $this->getLastname() . ' (PH: ' . $this->getPhone() . '), ' .
            $this->getAddress() . ($this->getAddress2() ? ', ' . $this->getAddress2() : '') . ', ' .
            $this->getCity() . ' ' . $this->getPostcode() . ', ' . ($this->getState() ? ', ' . $this->getState() : '') .
            $this->objCountry()->getTitle();
    }

    /**
     * @return Country|null
     */
    public function objCountry() {
        return Country::getByField($this->getPdo(), 'code', $this->getCountry());
    }
}