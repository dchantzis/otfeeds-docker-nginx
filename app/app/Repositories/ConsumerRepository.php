<?php

namespace App\Repositories;

use App\Models\Consumer;
use App\Repositories\Contracts\ConsumerRepositoryInterface;
use Illuminate\Support\Facades\DB;

class ConsumerRepository implements ConsumerRepositoryInterface
{

    /**
     * @var Consumer
     */
    private $model;

    public function __construct(Consumer $model)
    {
        $this->model = $model;
    }

    /**
     * {@inheritdoc}
     */
    public function findByToken($token)
    {
        return $this->model
            ->where('access_key', '=', $token)
            ->first();
    }

    /**
     * Disable the given consumer for the given Owner or dwelling.
     *
     * @param  int     $consumerId
     * @param  int     $foreignId
     * @param  string  $foreignClass
     * @param  string  $accessKey
     */
    public function disable($consumerId, $foreignId, $foreignClass, $accessKey)
    {
        DB::insert('INSERT INTO ' . Consumer::$exclusionTable .
            ' (created_at, updated_at, consumer_id, foreign_class, foreign_id, access_key) values (?, ?, ?, ?, ?, ?)'
            , array(date("Y-m-d h:i:sa"), date("Y-m-d h:i:sa"), $consumerId, $foreignClass, $foreignId, $accessKey)
        );
    }

}
