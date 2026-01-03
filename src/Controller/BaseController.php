<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class BaseController extends AbstractController
{
    public function __construct(
        private readonly ValidatorInterface $validator
    ) {
    }

    /**
     * Validate given data and return meaningful array with error/s or continue further by returning null
     *
     * @param mixed $constraints
     * @return array|null
     */
    protected function validateConstraints(mixed $constraints): ?array
    {
        $errors = $this->validator->validate($constraints);

        if (count($errors) > 0) {
            $errorMessages = [];
            foreach ($errors as $error) {
                $errorMessages[$error->getPropertyPath()] = $error->getMessage();
            }

            return ['status' => false, 'errors' => $errorMessages];
        }

        return null;
    }
}
