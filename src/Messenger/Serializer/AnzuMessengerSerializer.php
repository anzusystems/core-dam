<?php

declare(strict_types=1);

namespace App\Messenger\Serializer;

use AnzuSystems\SerializerBundle\Exception\SerializerException;
use AnzuSystems\SerializerBundle\Serializer;
use Symfony\Component\Messenger\Envelope;
use Symfony\Component\Messenger\Exception\MessageDecodingFailedException;
use Symfony\Component\Messenger\Stamp\NonSendableStampInterface;
use Symfony\Component\Messenger\Stamp\SerializedMessageStamp;
use Symfony\Component\Messenger\Transport\Serialization\SerializerInterface;

final readonly class AnzuMessengerSerializer implements SerializerInterface
{
    private const array CONTENT_TYPE_HEADER = ['Content-Type' => 'application/json'];

    public function __construct(
        private Serializer $serializer,
    ) {
    }

    public function decode(array $encodedEnvelope): Envelope
    {
        throw new MessageDecodingFailedException('Anzu Serializer cannot encode stamps because of required serialization attributes, therefore we cannot decode.');
    }

    /**
     * @throws SerializerException
     */
    public function encode(Envelope $envelope): array
    {
        /** @var SerializedMessageStamp|null $serializedMessageStamp */
        $serializedMessageStamp = $envelope->last(SerializedMessageStamp::class);

        $envelope = $envelope->withoutStampsOfType(NonSendableStampInterface::class);

        $headers = ['type' => $envelope->getMessage()::class] + self::CONTENT_TYPE_HEADER;

        return [
            'body' => $serializedMessageStamp
                ? $serializedMessageStamp->getSerializedMessage()
                : $this->serializer->serialize($envelope->getMessage()),
            'headers' => $headers,
        ];
    }
}
