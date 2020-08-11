<?php

namespace App\Http\Resources;

use App\Models\SystemLog;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\ResourceCollection;

class SystemLogCollection extends ResourceCollection
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
                if ($entry instanceof SystemLog) {
                    return new SystemLogResource($entry);
                }
                return $entry;
            })
        ];
    }

}
