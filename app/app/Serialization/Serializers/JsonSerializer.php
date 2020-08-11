<?php


namespace App\Serialization\Serializers;

use League\Fractal\Manager;
use League\Fractal\Resource\ResourceInterface;
use League\Fractal\Serializer\SerializerAbstract;

class JsonSerializer implements SerializerInterface
{

    /**
     * @var Manager
     */
    private $manager;

    /**
     * @param Manager $manager
     * @param SerializerAbstract $serializer
     */
    public function __construct(Manager $manager, SerializerAbstract $serializer)
    {
        $this->manager = $manager;

        $this->manager->setSerializer($serializer);
    }

    /**
     * {@inheritdoc}
     */
    public function serialize(ResourceInterface $data)
    {
        return $this->manager->createData($data)->toArray();
    }

}
