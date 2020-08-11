<?php

namespace App\Serialization\Managers\Factories;

use App\Serialization\Exceptions\NoValidManagerException;
use App\Serialization\Managers\SerializationManagerInterface;

class ManagerFactory
{

    /**
     * @param string $format Format of the requested content type. json or xml.
     * @param mixed $serializer The class that performs the serialization.
     * @return SerializationManagerInterface
     */
    public function get($format, $serializer)
    {
        $class = sprintf('App\Serialization\Managers\%sManager', ucfirst($format));

        if (! class_exists($class)) {
            throw new NoValidManagerException(sprintf('There is no manager for the format "%s".', $format));
        }

        return new $class($serializer);
    }

}
