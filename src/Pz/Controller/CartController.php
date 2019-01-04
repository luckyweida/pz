<?php

namespace Pz\Controller;

use Doctrine\DBAL\Connection;
use Pz\Service\CartService;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class CartController extends Controller
{
    use TraitCart, TraitCartAjax, TraitCartAccount, TraitCartFacebookLogin, TraitCartGoogleLogin;

    /**
     * @var CartService
     */
    protected $cartService;

    /**
     * CartController constructor.
     * @param Connection $connection
     * @param CartController $cartService
     */
    public function __construct(Connection $connection, CartService $cartService)
    {
        $this->connection = $connection;
        $this->cartService = $cartService;
    }
}