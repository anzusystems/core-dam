<?php

declare(strict_types=1);

namespace App\Model\Csv;

use AnzuSystems\SerializerBundle\Exception\SerializerException;
use AnzuSystems\SerializerBundle\Serializer;
use App\App;
use Generator;
use InvalidArgumentException;
use SplFileObject;

/**
 * @template T
 */
final class CsvFile
{
    private SplFileObject $csv;

    /**
     * @var array{string, int}
     */
    private array $headers;

    /**
     * @param class-string<T> $rowClassName
     */
    public function __construct(
        readonly string $filePath,
        private readonly Serializer $serializer,
        private readonly string $rowClassName,
        readonly bool $hasHeaders = false,
    ) {
        $this->csv = new SplFileObject($filePath);
        $this->csv->setFlags(SplFileObject::SKIP_EMPTY);

        if ($hasHeaders) {
            $this->headers = $this->readHeaders();
        }
    }

    public function getHeaders(): array
    {
        return $this->headers;
    }

    /**
     * @return Generator<int, T>
     *
     * @throws SerializerException
     */
    public function readObject(): Generator
    {
        foreach ($this->readCsv() as $row) {
            /** @var T $object */
            $object = $this->serializer->fromArray(
                $this->addHeadersToRow($row),
                $this->rowClassName
            );

            yield $object;
        }
    }

    public function readCsv(): Generator
    {
        if ($this->hasHeaders) {
            $this->csv->seek(1);
        }

        while (false === $this->csv->eof()) {
            $row = $this->csv->fgetcsv();
            if (false === $row) {
                continue;
            }
            yield $row;
        }
    }

    private function addHeadersToRow(array $row): array
    {
        $data = [];
        foreach ($this->headers as $key => $value) {
            if (isset($row[$value])) {
                $value = $row[$value];
                if (empty($value)) {
                    $value = null;
                }
                $data[$key] = $value;
            }
        }

        return $data;
    }

    /**
     * @return array{string, int}
     */
    private function readHeaders(): array
    {
        $currentPosition = $this->csv->key();

        $this->csv->seek(App::ZERO);
        $headers = $this->csv->fgetcsv();
        $this->csv->seek($currentPosition);

        if (is_array($headers)) {
            return array_flip($headers);
        }

        throw new InvalidArgumentException('CSV headers cannot be loaded');
    }
}
