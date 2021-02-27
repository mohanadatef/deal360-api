<?php

namespace App\Http\Resources\Wordpress;

use App\Models\Wordpress\PostWordpress;
use Illuminate\Http\Resources\Json\JsonResource;

class AgentResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request $request
     * @return array
     */
    public function toArray($request)
    {
        $properties=PostWordpress::published()->where('post_author','!=',0)->where('post_author',$this->ID)->count();
        return [
            'ID' => $this->ID,
            'full_name' => $this->meta->first_name .' '.$this->meta->last_name,
            'title' => $this->meta->title,
            'email' => $this->email,
            'agency_name' => $this->meta->agent_member,
            'phone' => $this->meta->phone,
            'mobile' => $this->meta->mobile,
            'whats_app'=>isset($this->meta->mobile) ? $this->meta->mobile : (isset($this->meta->phone) ? $this->meta->phone : ""),
            'description' => isset($this->post_meta->post_content) ? $this->post_meta->post_content : "",
            'picture' => $this->meta->custom_picture ? $this->meta->custom_picture : "",
            'count_listing' => $properties ? $properties : 0,
            'properties' => $this->properties ? $this->properties instanceof \Illuminate\Pagination\LengthAwarePaginator ? PropertiesResource::collection($this->properties) :array(new PropertiesResource($this->properties))  : array(),
        ];
    }
}
