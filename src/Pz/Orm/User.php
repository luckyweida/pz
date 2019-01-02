<?php
//Last updated: 2018-09-03 23:03:24
namespace Pz\Orm;

use Pz\Orm\OrmTrait\TraitUser;
use Symfony\Component\Security\Core\User\EquatableInterface;
use Symfony\Component\Security\Core\User\UserInterface;

class User extends \Pz\Orm\Generated\User implements UserInterface, EquatableInterface, \Serializable
{
    use TraitUser;
}