<?php

namespace App\Services;

use App\Http\XmlResponse;
use App\Serialization\Transformers\Factories\TransformerFactory;
use App\Serialization\Serializers\Factories\SerializerFactory;
use App\Serialization\Managers\Factories\ManagerFactory;
use Illuminate\Http\Request;
use League\Fractal\Resource\Collection;
use League\Fractal\Resource\Item;

class SerializedResponseBuilder
{

    /**
     * @var TransformerFactory
     */
    private $transformers;
    /**
     * @var SerializerFactory
     */
    private $serializers;
    /**
     * @var ManagerFactory
     */
    private $managers;

    /**
     * @var Request
     */
    private $request;

    /**
     * @param TransformerFactory $transformers
     * @param SerializerFactory $serializers
     * @param ManagerFactory $managers
     * @param Request $request
     */
    public function __construct(TransformerFactory $transformers,
                                SerializerFactory $serializers,
                                ManagerFactory $managers,
                                Request $request)
    {
        $this->transformers = $transformers;
        $this->serializers = $serializers;
        $this->managers = $managers;
        $this->request = $request;
    }

    /**
     * @param $data
     *
     * @return XmlResponse|array|\Illuminate\Http\JsonResponse|\SimpleXMLElement
     */
    public function build($data)
    {
        $requestFormat = $this->request->route()->ext;

        $transformer = $this->transformers->get($data);
        $serializer = $this->serializers->get($requestFormat, $data);
        $manager = $this->managers->get($requestFormat, $serializer);

        if ($data instanceof \Traversable) {
            $resource = new Collection($data, $transformer);
        } else {
            $resource = new Item($data, $transformer);
        }

        return $data = $manager->serialize($resource);
    }

}
