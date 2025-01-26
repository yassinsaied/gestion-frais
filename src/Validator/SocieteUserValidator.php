<?php

namespace App\Validator;

use App\Entity\User;
use App\Entity\Societe;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Security\Core\Security;

class SocieteUserValidator extends ConstraintValidator
{
    private $security;

    public function __construct(Security $security)
    {
        $this->security = $security;
    }

    public function validate($value, Constraint $constraint)
    {
        if (!$value instanceof Societe) {
            return;
        }

        $user = $this->security->getUser();

        if (!$user instanceof User) {
            $this->context->buildViolation($constraint->message)
                ->addViolation();
            return;
        }

        if (!$user->getSocietes()->contains($value)) {
            $this->context->buildViolation($constraint->message)
                ->addViolation();
        }
    }
}
