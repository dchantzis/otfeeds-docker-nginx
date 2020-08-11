<?php


namespace App\Auth;


use App\Exceptions\UnauthorisedException;
use App\Repositories\Contracts\ConsumerRepositoryInterface;
use Illuminate\Auth\GuardHelpers;
use Illuminate\Contracts\Auth\Guard;
use Illuminate\Contracts\Auth\UserProvider;
use Illuminate\Http\Request;

class AccessTokenGuard implements Guard
{
    use GuardHelpers;

    const AUTH_HEADER = 'X-Affiliate-Authentication';

    /**
     * @var ConsumerRepositoryInterface
     */
    private $consumers;

    /**
     * @var Request
     */
    private $request;

    public function __construct (UserProvider $provider, Request $request, $configuration) {

        $this->provider = $provider;

        $this->consumers = $provider->getConsumers();

        $this->request = $request;
    }

    /**
     * In the context of this solution, $this->user will contain an instance of the Container model
     *
     * @return \Illuminate\Contracts\Auth\Authenticatable|null
     * @throws UnauthorisedException
     */
    public function user () {

        if (!is_null($this->user)) {
            return $this->user;
        }

        $user = null;

        // retrieve via token
        $token = $this->getTokenForRequest();

        if (!empty($token)) {
            // the token was found, how you want to pass?
            $user = $this->provider->retrieveById($token);
        }

        return $this->user = $user;
    }

    /**
     * Get the token for the current request.
     *
     * @return array|string|null
     * @throws UnauthorisedException
     */
    public function getTokenForRequest () {
        return $this->token();
    }

    /**
     * Authenticate a request using the API authentication token.
     *
     * @return array|string|null
     * @throws UnauthorisedException
     */
    public function token()
    {
        if (!$this->request->route() || !in_array('auth:api', $this->request->route()->middleware())) {
            return null;
        }

        if (!$token = $this->request->header(self::AUTH_HEADER)) {
            throw new UnauthorisedException();
        }

        return $token;
    }

    /**
     * Validate a user's credentials.
     *
     * @param  array $credentials
     *
     * @return bool
     */
    public function validate (array $credentials = []) {
        return false;
    }

}
