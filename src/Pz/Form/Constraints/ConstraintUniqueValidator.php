<?php
namespace Pz\Form\Constraints;

use Pz\Service\DbService;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Web\ORMs\Customer;

class ConstraintUniqueValidator extends ConstraintValidator
{
	public function validate($value, Constraint $constraint)
	{
        if ($value) {
            $pdo = $constraint->pdo;
            $fieldToCheck = $constraint->fieldToCheck;
            $extraSql = $constraint->extraSql;
            $className = $constraint->className;
            $fullClassName = DbService::fullClassName($className);

            $orm = $fullClassName::data($pdo, array(
                'oneOrNull' => 1,
                'whereSql' => "m.$fieldToCheck = ? AND m.status = 1" . ($extraSql ? ' AND ' . $extraSql : ''),
                'params' => array($value),
            ));
            if ($orm) {
                $this->context->addViolation(
                    $constraint->message,
                    array('%string%' => $value)
                );
            }
        }
	}
}