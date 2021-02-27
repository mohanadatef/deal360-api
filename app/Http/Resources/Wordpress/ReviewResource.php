<?php

namespace App\Http\Resources\Wordpress;

use Illuminate\Http\Resources\Json\JsonResource;

class ReviewResource extends JsonResource
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
            'user_id' => $this->user->ID,
            'user_name' => $this->user->user_login,
            'user_image' => isset($this->user->meta->custom_picture) ? $this->user->meta->custom_picture : "https://deal360.ae/wp-content/themes/wpresidence/img/default_user.png",
            'comment' => $this->comment_content ? $this->comment_content : "",
            'title' => $this->meta->review_title ? $this->meta->review_title : "",
            'stars' => $this->meta->review_stars ? $this->meta->review_stars : "0",
        ];
    }
}
