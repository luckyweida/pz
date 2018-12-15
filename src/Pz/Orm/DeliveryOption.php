<?php
//Last updated: 2018-12-15 11:38:58
namespace Pz\Orm;

class DeliveryOption extends \Pz\Orm\Generated\DeliveryOption
{
    public function objCotent()
    {
        return json_decode($this->getContent());
    }

    public function objCountryIds()
    {
        $result = array();
        $objContent = $this->objCotent();
        foreach ($objContent as $section) {
            foreach ($section->blocks as $block) {
                $result = array_merge($result, $block->values->countries);
            }
        }
        return $result;
    }

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
}