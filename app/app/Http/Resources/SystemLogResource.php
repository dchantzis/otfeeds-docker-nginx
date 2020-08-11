<?php


namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class SystemLogResource extends JsonResource
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
            'created_at' => $this->created_at->format('Y-m-d H:i:s'),
            'updated_at' => $this->updated_at->format('Y-m-d H:i:s'),
            'id' => $this->id,
            'ip_trace_id' => $this->ip_trace_id,
            'message' => $this->message,
            'level' => $this->level,
            'context' => json_decode($this->context) ?? '',
            'channel' => $this->channel,
        ];
    }

}
