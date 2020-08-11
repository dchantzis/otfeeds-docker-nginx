<?php


namespace App\Http\Resources;

use App\Models\IPTrace;
use Illuminate\Http\Resources\Json\ResourceCollection;
use Illuminate\Http\Request;

class IpTraceCollection extends ResourceCollection
{
    /**
     * Transform the resource collection into an array.
     *
     * @param  Request  $request
     * @return array
     */
    public function toArray($request)
    {
        return [
            'data' => $this->collection->map(function ($entry) {
                if ($entry instanceof IPTrace) {
                    return new IpTraceResource($entry);
                }
                return $entry;
            }),
        ];
    }
}
