<?php

declare(strict_types=1);

namespace App\Csv;

use AnzuSystems\CommonBundle\Traits\SerializerAwareTrait;
use App\Model\Csv\CsvFile;

final class CsvFactory
{
    use SerializerAwareTrait;

    /**
     * @param class-string $rowClassName
     */
    public function initCsv(
        string $filePath,
        string $rowClassName,
        bool $hasHeaders = false
    ): CsvFile {
        return new CsvFile(
            filePath: $filePath,
            serializer: $this->serializer,
            rowClassName: $rowClassName,
            hasHeaders: $hasHeaders
        );
    }
}
