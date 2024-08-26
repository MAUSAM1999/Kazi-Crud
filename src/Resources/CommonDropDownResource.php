<?php

namespace YajTech\Crud\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class CommonDropDownResource extends JsonResource
{
    public function toArray($request)
    {
        return parent::toArray($request);
    }
}
