<?php

namespace App\Trait;

use App\Exception\ValidationException;

trait CanValidateEntity
{
    public function validate(object $entity): void
    {
        $violations = $this->validator->validate($entity);

        if (count($violations) > 0) {
            $errors = [];

            foreach ($violations as $violation) {
                $errors[$violation->getPropertyPath()][] = $violation->getMessage();
            }

            throw new ValidationException($errors);
        }
    }
}
