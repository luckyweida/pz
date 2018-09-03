<?php

namespace Pz\Security\User;

use Doctrine\DBAL\Connection;
use Symfony\Component\Security\Core\Exception\UsernameNotFoundException;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\UserProviderInterface;
use Pz\Orm\User;

class UserProvider implements UserProviderInterface
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
        if (!$user instanceof \Pz\Orm\User) {
            throw new UnsupportedUserException(
                sprintf('Instances of "%s" are not supported.', get_class($user))
            );
        }

        $username = $user->getUsername();

        return $this->fetchUser($username);
    }

    public function supportsClass($class)
    {
        return User::class === $class;
    }

    private function fetchUser($username)
    {

        $pdo = $this->conn->getWrappedConnection();
        /** @var User $user */
        $user = User::getByField($pdo, 'title', $username);
        if ($user) {
            return $user;
        }

        throw new UsernameNotFoundException(
            sprintf('Username "%s" does not exist.', $username)
        );
    }
}