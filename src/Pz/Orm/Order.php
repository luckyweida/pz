<?php
//Last updated: 2018-11-10 16:54:41
namespace Pz\Orm;

use Pz\Orm\OrmTrait\TraitOrder;

class Order extends \Pz\Orm\Generated\Order implements \Serializable
{
    use TraitOrder;

    /** @var OrderItem[] $pendingItems */
    private $pendingItems;

    /** @var OrderItem[] $orderItems */
    private $orderItems;

    /** @var DeliveryOption[] $deliveryOptions */
    private $deliveryOptions;

    /** @var string $orderItemClass */
    protected $orderItemClass;
}