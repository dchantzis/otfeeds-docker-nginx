<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ApiAuditLogResource extends JsonResource
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
            'type' => $this->type,
            'id' => $this->id,
            'created_at' => $this->created_at->format('Y-m-d H:i:s'),
            'updated_at' => $this->updated_at->format('Y-m-d H:i:s'),
            'ip_trace_id' => $this->ip_trace_id,
            'consumer_id' => $this->consumer_id,
            'content' => json_decode($this->content) ?? '',
            'meta' => json_decode($this->meta) ?? '',
        ];
    }

}
