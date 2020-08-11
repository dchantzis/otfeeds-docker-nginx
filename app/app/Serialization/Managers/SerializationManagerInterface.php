<?php

namespace App\Serialization\Managers;

use League\Fractal\Resource\ResourceInterface;

interface SerializationManagerInterface
{

    /**
     * Serializes the underlying data structure into a format that can be used to respond to the client.
     *
     * @param ResourceInterface $resource
     * @return array|\SimpleXMLElement
     */
    public function serialize(ResourceInterface $resource);

}
