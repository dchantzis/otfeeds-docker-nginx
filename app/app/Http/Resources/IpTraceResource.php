<?php


namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class IpTraceResource extends JsonResource
{

    /**
     * Transform the resource into an array.
     *
     * @param  Request  $request
     * @return array
     */
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'ip_address' => $this->ip_address,
            'consumer_id' => $this->consumer_id ?? '',
            'consumer_company_name' => ($this->consumer_id) ? $this->consumer->company_name : '',
            'request_method' => $this->request_method,
            'route' => $this->route,
            'request_parameters' => json_decode($this->request_parameters) ?? '',
            'request_header' => json_decode($this->request_header) ?? '',
            'host' => $this->host,
            'created_at' => $this->created_at->format('Y-m-d H:i:s')
        ];
    }

}
