<?php

namespace App\Serialization\Managers;

use App\Serialization\Serializers\SerializerInterface;
use League\Fractal\Resource\ResourceInterface;

class JsonManager implements SerializationManagerInterface
{

    /**
     * @var SerializerInterface
     */
    private $serializer;

    public function __construct(SerializerInterface $serializer)
    {
        $this->serializer = $serializer;
    }

    /**
     * {@inheritdoc}
     */
    public function serialize(ResourceInterface $resource)
    {
        return $this->serializer->serialize($resource);
    }

}
