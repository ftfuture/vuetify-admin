<?php

namespace App\Http\Resources;

use ftfuture\LaravelAdmin\Http\Resources\BaseResource;

class Monster extends BaseResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        return parent::toArray($request);
    }
}
