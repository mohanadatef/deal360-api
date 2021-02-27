<?php

namespace App\Http\Resources\Wordpress;

use Illuminate\Http\Resources\Json\JsonResource;

class CityResource extends JsonResource
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
            'name' => $this->name ?$this->name :"",
            'count' => $this->count ? ''.$this->count.'' : '0',
            'term_taxonomy_id' => ''.$this->term_taxonomy_id.'',
            'image' => isset($this->option['category_featured_image']) ? $this->option['category_featured_image'] : "",
        ];
    }
}
