<?php
//Last updated: 2018-11-18 19:09:26
namespace Pz\Orm;

class CustomerAddress extends \Pz\Orm\Generated\CustomerAddress
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