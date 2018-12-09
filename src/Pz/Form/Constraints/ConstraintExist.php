<?php
namespace Pz\Form\Constraints;

use Symfony\Component\Validator\Constraint;

class ConstraintExist extends Constraint
{
	public $message = '"%string%" does not exist';

	public $pdo;
    public $fieldToCheck;
    public $className;
}