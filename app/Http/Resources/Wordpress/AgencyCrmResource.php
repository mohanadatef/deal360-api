<?php

namespace App\Http\Resources\Wordpress;

use Illuminate\Http\Resources\Json\JsonResource;

class AgencyCrmResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request $request
     * @return array
     */
    public function toArray($request)
    {
        return [
            'ID' => $this->ID,
            'name' => isset($this->post_meta->post_title) ? $this->post_meta->post_title : "",
            'properties_count' => isset($this->properties_count) ? $this->properties_count : 0,
        ];
    }
}
