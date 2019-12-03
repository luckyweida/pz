<?php
namespace Pz\Form\Constraints;

use Symfony\Component\Validator\Constraint;

class ConstraintUnique extends Constraint
{
	public $message = '"%string%" already exists';

	public $pdo;
    public $extraSql = null;
    public $fieldToCheck;
    public $className;
}