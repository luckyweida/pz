<?php

namespace Pz\Controller;

use Pz\Axiom\Mo;

class WebController extends Mo
{
    use TraitCart, TraitCartFacebookLogin, TraitCartGoogleLogin, TraitWeb;
}