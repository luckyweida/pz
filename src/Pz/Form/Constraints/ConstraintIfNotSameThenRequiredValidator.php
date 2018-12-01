<?php

namespace Pz\Form\Constraints;

use Pz\Form\Builder\Cart;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

class ConstraintIfNotSameThenRequiredValidator extends ConstraintValidator
{
	public function validate($value, Constraint $constraint)
	{

        /** @var Cart $form */
	    $form = $constraint->form;

	    $reqeust = Request::createFromGlobals();
	    $data = $reqeust->request->get($form->getBlockPrefix());

		if ((!isset($data[$constraint->attrToCheckIfSame]) || $data[$constraint->attrToCheckIfSame] != 1) && !$value) {
//            var_dump('errpr');
			$this->context->addViolation(
				$constraint->message,
				array()
			);
		}
	}
}