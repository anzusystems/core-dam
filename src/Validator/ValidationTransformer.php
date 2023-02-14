<?php

declare(strict_types=1);

namespace App\Validator;

use AnzuSystems\CoreDamBundle\Model\Configuration\TextsWriter\TextsWriterConfiguration;
use Symfony\Component\Validator\ConstraintViolation;

final class ValidationTransformer
{
    /**
     * @param array<string, array<int, string>> $errors
     * @param array<string, TextsWriterConfiguration> $config
     * @psalm-param class-string $rootClass
     *
     * @return array<int, ConstraintViolation>
     */
    public function transformValidation(array $errors, array $config, string $rootClass, bool $reversedConfig = false): array
    {
        $transformedErrors = [];
        foreach ($config as $configuration) {
            $newPath = $this->getNewPath($configuration, $reversedConfig);
            $oldPath = $reversedConfig ? $configuration->getSourcePropertyPath() : $configuration->getDestinationPropertyPath();

            if (isset($errors[$oldPath])) {
                $transformedErrors = array_merge($transformedErrors, $this->transformErrorToViolations(
                    path: $newPath,
                    errors: $errors[$oldPath],
                    rootClass: $rootClass
                ));
            }
        }

        return $transformedErrors;
    }

    private function getNewPath(TextsWriterConfiguration $configuration, bool $reversedConfig = false): string
    {
        $newPath = $reversedConfig ? $configuration->getDestinationPropertyPath() : $configuration->getSourcePropertyPath();
        $newPath = str_replace('[', '.', $newPath);

        return str_replace(']', '', $newPath);
    }

    /**
     * @param array<int, string> $errors
     * @psalm-param class-string $rootClass
     */
    private function transformErrorToViolations(string $path, array $errors, string $rootClass): array
    {
        return array_map(
            fn (string $error) => new ConstraintViolation(
                message: $error,
                messageTemplate: $error,
                parameters: [],
                root: $rootClass,
                propertyPath: $path,
                invalidValue: $path
            ),
            $errors
        );
    }
}
