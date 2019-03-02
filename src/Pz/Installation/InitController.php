<?php

namespace Pz\Installation;

use Doctrine\DBAL\Connection;
use Pz\Service\CartService;
use Pz\Service\PageService;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class InitController extends Controller
{
    use TraitInit;
}