<?php

namespace App\Serialization\Serializers\Factories;

use App\Serialization\Exceptions\NoValidSerializerException;
use App\Serialization\Serializers\SerializerInterface;

class SerializerFactory
{

    private $serializers = array();

    /**
     * Register a mapping between a format and the underlying model that can be serialized, and the actual serializer.
     *
     * @param string $format json or xml.
     * @param string $supports Full namespace for the supported class.
     * @param SerializerInterface $serializer The serializer class.
     * @return $this
     */
    public function registerSerializer($format, $supports, SerializerInterface $serializer)
    {
        $this->serializers[$format][$supports] = $serializer;

        return $this;
    }

    /**
     * Register a fallback serializer that will be used if there are no specific serializer for the format and model.
     *
     * @param string $format json or xml.
     * @param SerializerInterface $serializer The serializer class.
     * @return $this
     */
    public function registerFallbackSerializer($format, SerializerInterface $serializer)
    {
        $this->serializers[$format]['default'] = $serializer;

        return $this;
    }

    /**
     * Get a serializer to serialize the passed data to the given format.
     *
     * @param string $format json or xml.
     * @param mixed $data
     * @throws NoValidSerializerException
     * @return SerializerInterface
     */
    public function get($format, $data)
    {
        $dataClass = get_class($data);

        // If the passed data is traversable, find out the class of the underlying objects.
        if ($data instanceof \Traversable) {
            $dataClass = get_class($data[0]);
        }

        if (isset($this->serializers[$format][$dataClass])) {
            return $this->serializers[$format][$dataClass];
        }

        if (isset($this->serializers[$format]['default'])) {
            return $this->serializers[$format]['default'];
        }

        throw new NoValidSerializerException(
            sprintf('No valid serializer configured for "%s" with a format of "%s".', $dataClass, $format)
        );
    }

}
