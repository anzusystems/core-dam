<?php

declare(strict_types=1);

namespace App\Domain\Distribution;

use AnzuSystems\CommonBundle\Exception\ValidationException;
use AnzuSystems\CommonBundle\Traits\ValidatorAwareTrait;
use AnzuSystems\CoreDamBundle\Distribution\DistributionAdapterInterface;
use AnzuSystems\CoreDamBundle\Entity\AssetFile;
use AnzuSystems\CoreDamBundle\Entity\Distribution;
use AnzuSystems\CoreDamBundle\Model\Dto\CustomDistribution\CustomDistributionAdmDto;
use App\Validator\ValidationTransformer;
use Symfony\Component\Validator\ConstraintViolationList;
use Symfony\Contracts\Service\Attribute\Required;

abstract class AbstractDistributionAdapter implements DistributionAdapterInterface
{
    use ValidatorAwareTrait;

    protected ValidationTransformer $validationTransformer;

    #[Required]
    public function setValidationTransformer(ValidationTransformer $validationTransformer): void
    {
        $this->validationTransformer = $validationTransformer;
    }

    protected function setBaseDistributionFields(
        AssetFile $assetFile,
        CustomDistributionAdmDto $distributionDto,
        Distribution $distribution
    ): void {
        $distribution->setDistributionService($distributionDto->getDistributionService());
        $distribution->setAssetId((string) $assetFile->getAsset()->getId());
        $distribution->setAssetFileId((string) $assetFile->getId());
        $distribution->setBlockedBy($distributionDto->getBlockedBy());
    }

    /**
     * @throws ValidationException
     */
    protected function validate(
        Distribution $distribution,
        CustomDistributionAdmDto $distributionDto,
        array $config,
    ): void {
        try {
            $this->validator->validate($distribution);
        } catch (ValidationException $exception) {
            throw new ValidationException(
                (new ConstraintViolationList(
                    $this->validationTransformer->transformValidation(
                        errors: $exception->getFormattedErrors(),
                        config: $config,
                        rootClass: $distributionDto::class,
                        reversedConfig: true
                    )
                ))
            );
        }
    }
}
