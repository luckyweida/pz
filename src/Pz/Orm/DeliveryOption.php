<?php
//Last updated: 2018-12-15 11:38:58
namespace Pz\Orm;

use Pz\Orm\OrmTrait\TraitDeliveryOption;

class DeliveryOption extends \Pz\Orm\Generated\DeliveryOption implements \Serializable
{

    use TraitDeliveryOption;

    /**
     * @var float
     */
    protected $price;
}