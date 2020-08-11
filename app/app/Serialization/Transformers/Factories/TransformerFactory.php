<?php

namespace App\Serialization\Transformers\Factories;

use App\Serialization\Exceptions\NoValidTransformerException;
use Illuminate\Support\Collection;
use League\Fractal\TransformerAbstract;

class TransformerFactory
{

    /**
     * A mapping of transformers that can be used to transform data of a given format.
     *
     * @var array
     */
    private $transformers = array();

    /**
     * @param string $supports Full namespace of the supported class.
     * @param string $transformer Full namespace of the transformer class.
     * @return $this
     */
    public function registerTransformer($supports, $transformer)
    {
        $this->transformers[$supports] = $transformer;

        return $this;
    }

    /**
     * @param Collection $data Model to transform.
     * @return TransformerAbstract The matching transformer.
     *
     * @throws NoValidTransformerException
     */
    public function get($data)
    {
        $dataClass = get_class($data);

        // If the passed data is traversable, find out the class of the underlying objects.
        if ($data instanceof \Traversable && ! empty($data)) {
            $dataClass = get_class($data[0]);
        }

        if (! isset($this->transformers[$dataClass])) {
            throw new NoValidTransformerException(sprintf('No valid transformer configured for "%s".', $dataClass));
        }

        return $this->transformers[$dataClass];
    }

}
