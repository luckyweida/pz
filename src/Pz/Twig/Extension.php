<?php

namespace Pz\Twig;

use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

class Extension extends AbstractExtension
{
    public function getFunctions(): array
    {
        return array(
            new TwigFunction('getenv', 'getenv'),
        );
    }
}