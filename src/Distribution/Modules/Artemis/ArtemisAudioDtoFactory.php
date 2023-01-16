<?php

declare(strict_types=1);


namespace App\Distribution\Modules\Artemis;

use AnzuSystems\CoreDamBundle\Distribution\AbstractDistributionDtoFactory;
use AnzuSystems\CoreDamBundle\Entity\AssetFile;
use AnzuSystems\CoreDamBundle\Entity\CustomDistribution;
use App\Model\Dto\Artemis\ArtemisAudioMediaDto;
use App\Model\Dto\Artemis\ArtemisImageDto;
use App\Model\Dto\Artemis\ArtemisMediaAuthorDto;
use App\Model\Dto\Artemis\ArtemisMediaChannel;
use App\Model\Dto\Artemis\ArtemisMediaRubricDto;
use App\Model\Dto\Artemis\ArtemisMediaTagDto;

final class ArtemisAudioDtoFactory extends AbstractDistributionDtoFactory
{
    public function createMediaDto(AssetFile $assetFile, CustomDistribution $distribution): ArtemisAudioMediaDto
    {
        $mediaDto = new ArtemisAudioMediaDto();
        $mediaDto
            ->setImage((
                new ArtemisImageDto()
            )
                ->setTitle('Title')
                ->setUrl('https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcRspepjfyrVKhFNfHSjgVfgX_UP-w2vBXzqqg&usqp=CAU')
            )
            ->setTitle('Test media')
            ->setAnzuMediaId('123')
            ->setExternalId('123')
            ->setDirectSourceUrl('https://anchor.fm/s/40c6e0cc/podcast/play/63101018/https%3A%2F%2Fd3ctxlq1ktw2nl.cloudfront.net%2Fstaging%2F2023-0-5%2F76665c8c-7765-6587-57ec-42627834a9c2.mp3')
            ->setPremiumDirectSourceUrl('')
            ->setCreateArticle(true)
            ->setMediaChannel(
                (new ArtemisMediaChannel())->setAnzuId('nehehehe')
            )
            ->setRubric(
                (new ArtemisMediaRubricDto())->setId(6978)
            )
            ->setAuthors([
                (new ArtemisMediaAuthorDto())
                    ->setFullName( 'Ondrej Podstupka')
            ])
            ->setTags([
                (new ArtemisMediaTagDto())
                    ->setTitle('novy tagissssss')
            ])
        ;

        return $mediaDto;
    }
}