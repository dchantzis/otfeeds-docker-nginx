<?php


namespace App\Repositories\Caching;

use App\Repositories\DwellingRepository as BaseDwellingRepository;

class DwellingRepository extends BaseDwellingRepository
{

    /**
     * @var BaseDwellingRepository
     */
    private $repository;

    /**
     * @var int Cache TTL in minutes.
     */
    private $ttl;

    /**
     * @param BaseDwellingRepository $repository
     * @param int $ttl Cache TTL in minutes.
     */
    public function __construct(BaseDwellingRepository $repository, $ttl = 240)
    {
        $this->ttl = $ttl;
        $this->repository = $repository;

        parent::__construct($repository->model);
    }

    /**
     * {@inheritdoc}
     */
    public function find($id, $related = array())
    {
        $key = sprintf('detail-%d', $id);
        $repository = $this->repository;

        return \Cache::remember($key, $this->ttl, function () use ($repository, $id) {
            return $repository->find($id);
        });
    }

}
