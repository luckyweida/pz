<?php
//Last updated: 2018-11-18 18:41:05
namespace Pz\Orm;

use Pz\Orm\OrmTrait\TraitCustomer;
use Symfony\Component\Security\Core\User\EquatableInterface;
use Symfony\Component\Security\Core\User\UserInterface;

class Customer extends \Pz\Orm\Generated\Customer implements UserInterface, EquatableInterface, \Serializable
{
    use TraitCustomer;
}