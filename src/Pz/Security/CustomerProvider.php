<?php

namespace Pz\Security;

use Doctrine\DBAL\Connection;
use Pz\Orm\Customer;
use Symfony\Component\Security\Core\Exception\UnsupportedUserException;
use Symfony\Component\Security\Core\Exception\UsernameNotFoundException;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\UserProviderInterface;

class CustomerProvider implements UserProviderInterface
{
    public function __construct(Connection $conn)
    {
        $this->conn = $conn;
    }

    public function loadUserByUsername($username)
    {
        return $this->fetchUser($username);
    }

    public function refreshUser(UserInterface $user)
    {
        if (!$user instanceof \Pz\Orm\Customer) {
            throw new UnsupportedUserException(
                sprintf('Instances of "%s" are not supported.', get_class($user))
            );
        }

        $username = $user->getUsername();

        return $this->fetchUser($username);
    }

    public function supportsClass($class)
    {
        return Customer::class === $class;
    }

    private function fetchUser($username)
    {

        $pdo = $this->conn->getWrappedConnection();
        /** @var Customer $user */
        $user = Customer::getByField($pdo, 'title', $username);

        if (!$user) {
            throw new UsernameNotFoundException(
                sprintf('Username "%s" does not exist.', $username)
            );
        }

        if ($user->getStatus() != 1) {
            throw new UsernameNotFoundException(
                sprintf('User "%s" is disabled.', $username)
            );
        }

        if ($user->getIsActivated() != 1) {
            throw new UsernameNotFoundException(
                sprintf('User "%s" is not activated.', $username)
            );
        }

        return $user;
    }
}