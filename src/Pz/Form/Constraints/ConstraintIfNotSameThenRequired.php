<?php

namespace Pz\Form\Constraints;

use Symfony\Component\Validator\Constraint;

/**
 * @Annotation
 */
class ConstraintIfNotSameThenRequired extends Constraint
{
	public $message = 'This value should not be blank.';
	public $form;
    public $attrToCheckIfSame;
}