<?php

namespace App\Repositories\Contracts;

use App\Models\Consumer;

interface ConsumerRepositoryInterface
{

    /**
     * @param string $token API authentication token.
     * @return Consumer|false
     */
    public function findByToken($token);

}
