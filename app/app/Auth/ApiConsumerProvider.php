<?php

namespace App\Auth;

use Illuminate\Contracts\Auth\Authenticatable as UserContract;
use Illuminate\Contracts\Auth\UserProvider;
use App\Repositories\Contracts\ConsumerRepositoryInterface;

class ApiConsumerProvider implements UserProvider
{

    private $consumers;

    public function __construct(ConsumerRepositoryInterface $repository)
    {
        $this->consumers = $repository;
    }

    public function getConsumers()
    {
        return $this->consumers;
    }

    public function retrieveById($identifier)
    {
        return $this->consumers->findByToken($identifier);
    }

    public function retrieveByToken($identifier, $token)
    {
        return;
    }

    public function retrieveByCredentials(array $credentials)
    {
        return $this->consumers->findByToken($credentials['id']);
    }

    /**
     * Validate a user against the given credentials.
     *
     * @param  UserContract  $user
     * @param  array  $credentials
     * @return bool
     */
    public function validateCredentials(UserContract $user, array $credentials)
    {
        return true;
    }

    public function updateRememberToken(UserContract $user, $token)
    {
    }

}
