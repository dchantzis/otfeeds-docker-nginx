<?php


namespace App\Http\Controllers;

use Illuminate\Http\Request;

class PingController extends Controller
{

    /**
     * @param Request $request
     * @return mixed
     */
    public function __invoke(Request $request)
    {

        return response()->api('pong');
    }

    public function test(Request $request)
    {
        $a = 'cat';

        return $a;
    }

}
