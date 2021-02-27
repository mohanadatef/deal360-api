<?php

namespace App\Http\Resources\Wordpress;

use Illuminate\Http\Resources\Json\JsonResource;

class UserResource extends JsonResource
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
            'user_id'=>$this->ID,
            'user_login'=>$this->user_login,
            'user_email'=>$this->user_email,
            "first_name"=> isset($this->meta->first_name) ? $this->meta->first_name : "",
            "last_name"=> isset($this->meta->last_name)? $this->meta->last_name : "",
            "phone"=> isset($this->meta->phone)? $this->meta->phone : "",
            "facebook"=> isset($this->meta->facebook)? $this->meta->facebook : "",
            "twitter"=> isset($this->meta->twitter)? $this->meta->twitter : "",
            "linkedin"=> isset($this->meta->linkedin)? $this->meta->linkedin : "",
            "pinterest"=> isset($this->meta->pinterest)? $this->meta->pinterest : "",
            "instagram"=> isset($this->meta->instagram)? $this->meta->instagram : "",
            "website"=> isset($this->meta->website)? $this->meta->website : "",
            'custom_picture'=>isset($this->meta->custom_picture) ? $this->meta->custom_picture : "",
        ];
    }
}
