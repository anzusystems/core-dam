<?php

declare(strict_types=1);

namespace App\Fs1Migration;

use AnzuSystems\AuthBundle\Exception\UnsuccessfulAccessTokenRequestException;
use AnzuSystems\AuthBundle\Exception\UnsuccessfulUserInfoRequestException;
use AnzuSystems\AuthBundle\HttpClient\OAuth2HttpClient;
use AnzuSystems\CoreDamBundle\Command\Traits\OutputUtilTrait;
use AnzuSystems\CoreDamBundle\Helper\StringHelper;
use App\App;
use App\Entity\User;
use App\MediaApiMigrations\ConnectionDecorator;
use App\Model\Csv\Fs1CsvFile;
use DateTimeInterface;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Exception;
use Symfony\Component\String\ByteString;
use Symfony\Component\Uid\Uuid;

final class CategoryProvider
{
    private const CATEGORY_MAP = [
        118 => '1edd902c-ea71-6978-b0f1-2bf75087e621',
        117 => '1edd902c-f038-6816-90f0-2bf75087e621',
        119 => '1edd902c-eec7-6432-bb44-2bf75087e621',
        113 => '1edd902c-efc0-6c94-b3c9-2bf75087e621',
        114 => '1edd902c-ef93-6b4a-80bc-2bf75087e621',
    ];

    public function getCategory(Fs1CsvFile $file): string
    {
        if (isset(self::CATEGORY_MAP[$file->getRubricId()])) {
            return self::CATEGORY_MAP[$file->getRubricId()];
        }

        return '';
    }
}
