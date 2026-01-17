<?php

declare(strict_types=1);

namespace App\Controller\Traits;

use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Contracts\Service\Attribute\Required;

trait ValidatesRequestTrait
{
    protected ?ValidatorInterface $validator = null;

    /**
     * Inject required setter
     *
     * @param ValidatorInterface $validator
     * @return void
     */
    #[Required]
    public function setValidator(ValidatorInterface $validator): void
    {
        $this->validator = $validator;
    }

    /**
     * Validate Constraints arguments and either return an array with validation error or null
     *
     * @param mixed $constraints - Constraints to validate
     * @return array|null
     */
    protected function validateConstraints(mixed $constraints): ?array
    {
        if ($this->validator === null) {
            throw new \LogicException(
                sprintf(
                    'Validator was not injected. Make sure the controller is a service and uses #[Required] injection (%s).',
                    static::class
                )
            );
        }

        $errors = $this->validator->validate($constraints);

        if (count($errors) === 0) {
            return null;
        }

        $messages = [];
        $invalidFields = [];
        foreach ($errors as $error) {
            $messages[$error->getPropertyPath()] = $error->getMessage();
            $invalidFields[] = $error->getPropertyPath();
        }

        return ['status' => false, 'errors' => $messages, 'invalid_fields' => $invalidFields];
    }
}
