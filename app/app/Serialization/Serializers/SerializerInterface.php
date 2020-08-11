<?php

namespace App\Serialization\Serializers;

use League\Fractal\Resource\ResourceInterface;

interface SerializerInterface
{

    /**
     * Serialize the model data an appropriate format.
     *
     * @param ResourceInterface $data
     * @return mixed
     */
    public function serialize(ResourceInterface $data);

}
