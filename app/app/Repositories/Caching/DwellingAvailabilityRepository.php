<?php

namespace App\Repositories\Caching;

use App\Repositories\DwellingAvailabilityRepository as BaseDwellingAvailabilityRepository;
use Illuminate\Support\Facades\Cache;

class DwellingAvailabilityRepository extends BaseDwellingAvailabilityRepository
{

    /**
     * @var BaseDwellingAvailabilityRepository
     */
    private $repository;

    /**
     * @var int Cache TTL in minutes.
     */
    private $ttl;

    /**
     * @param BaseDwellingAvailabilityRepository $repository
     * @param int $ttl Cache TTL in minutes.
     */
    public function __construct(BaseDwellingAvailabilityRepository $repository, $ttl = 240)
    {
        $this->repository = $repository;
        $this->ttl = $ttl;
    }

    /**
     * @param int $id
     * @param int $duration
     * @return mixed
     */
    public function findForDwelling($id, $duration = 365)
    {
        $key = sprintf('availability-%d-%d', $id, $duration);
        $repository = $this->repository;

        return Cache::remember($key, $this->ttl, function () use ($repository, $id, $duration) {
            return $repository->findForDwelling($id, $duration);
        });
    }

}
