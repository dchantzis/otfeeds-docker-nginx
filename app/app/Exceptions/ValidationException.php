<?php


namespace App\Exceptions;


use Symfony\Component\HttpFoundation\Response;
use Illuminate\Validation\Validator;

class ValidationException extends \Exception
{

    /**
     * @var false|string
     */
    private $data;

    public function __construct($data = [], $statusCode = Response::HTTP_BAD_REQUEST)
    {
        $this->data = $this->formatErrors($data);

        parent::__construct();
    }

    private function formatErrors($data)
    {
        if (is_a($data, Validator::class)) {
            $data = $data->errors()->getMessages();
        }

        return json_encode($data);
    }

    public function getData()
    {
        if (!is_array($this->data) && ($dataJsonDecoded = json_decode($this->data, true))) {
            return $dataJsonDecoded;
        }

        return $this->data;
    }

}
