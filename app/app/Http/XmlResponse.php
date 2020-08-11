<?php


namespace App\Http;

use Symfony\Component\HttpFoundation\Response;

class XmlResponse extends Response
{

    /**
     * XmlResponse constructor.
     * @param $content
     * @param int $httpStatus
     * @param array $headers
     */
    public function __construct($content, $httpStatus = Response::HTTP_OK, array $headers = [])
    {
        $content = $content->asXml();

        parent::__construct($content, $httpStatus, $headers);

        $this->setContentType();
    }

    /**
     * Set the correct Content-Type header, if it is has not been set already.
     */
    private function setContentType()
    {
        if (! $this->headers->has('Content-Type')) {
            $this->headers->set('Content-Type', 'text/xml');
        }
    }

}
