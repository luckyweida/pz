<?php
namespace Pz\Form\Constraints;

use Pz\Service\Db;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Web\ORMs\Customer;

class ConstraintExistValidator extends ConstraintValidator
{
	public function validate($value, Constraint $constraint)
	{
        if ($value) {
            $pdo = $constraint->pdo;
            $fieldToCheck = $constraint->fieldToCheck;
            $className = $constraint->className;
            $fullClassName = Db::fullClassName($className);

            $orm = $fullClassName::data($pdo, array(
                'oneOrNull' => 1,
                'whereSql' => "m.$fieldToCheck = ? AND m.status = 1" . ($constraint->extraQuery ? ' AND ' . $constraint->extraQuery : ''),
                'params' => array($value),
            ));
            if (!$orm) {
                $this->context->addViolation(
                    $constraint->message,
                    array('%string%' => $value)
                );
            }
        }
	}
}