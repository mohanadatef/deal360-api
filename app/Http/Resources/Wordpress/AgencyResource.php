<?php

namespace App\Http\Resources\Wordpress;

use Illuminate\Http\Resources\Json\JsonResource;

class AgencyResource extends JsonResource
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
            'full_name' => isset($this->post_meta->post_title) ? $this->post_meta->post_title : "",
            'address' => isset($this->post_meta->meta->agency_address) ? $this->post_meta->meta->agency_address : "",
            'agency_phone' => isset($this->post_meta->meta->agency_phone) ? $this->post_meta->meta->agency_phone : ($this->meta->phone ? $this->meta->phone : ""),
            'agency_mobile' => isset($this->post_meta->meta->agency_mobile) ? $this->post_meta->meta->agency_mobile : ($this->meta->mobile ? $this->meta->mobile : ""),
            'whats_app'=>isset($this->post_meta->meta->agency_phone) ? $this->post_meta->meta->agency_phone : (isset($this->post_meta->meta->agency_mobile) ? $this->post_meta->meta->agency_mobile : ""),
            'agency_email' => isset($this->post_meta->meta->agency_email) ? $this->post_meta->meta->agency_email : ($this->email ? $this->email : ""),
            'agency_website' => isset($this->post_meta->meta->agency_website) ? $this->post_meta->meta->agency_website : ($this->post_meta->meta->agent_website ? $this->post_meta->meta->agent_website :""),
            'agency_opening_hours' => isset($this->post_meta->meta->agency_opening_hours) ? $this->post_meta->meta->agency_opening_hours : "",
            'description' => isset($this->post_meta->post_content) ? $this->post_meta->post_content : "",
            'image' => $this->meta->custom_picture ? $this->meta->custom_picture : "",
            'agent' => $this->agent ? AgentResource::collection($this->agent) : array(),
            'properties' => $this->properties ? $this->properties instanceof \Illuminate\Pagination\LengthAwarePaginator ? PropertiesResource::collection($this->properties) :array(new PropertiesResource($this->properties))  : array(),
        ];
    }
}
