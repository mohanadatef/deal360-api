<?php

namespace App\Http\Resources\Wordpress;

use App\Models\Wordpress\PostWordpress;
use Illuminate\Http\Resources\Json\JsonResource;

class AgentCrmResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request $request
     * @return array
     */
    public function toArray($request)
    {
        if (!isset($request->lang)) {
            $request->lang = 'en';
        }
        $properties=PostWordpress::published()->leftJoin('icl_translations', 'icl_translations.element_id', 'posts.ID')
            ->where('icl_translations.language_code', $request->lang)->where('post_author','!=',0)->where('post_author',$this->ID)->where('post_type','estate_property')->count();
        return [
            'ID' => $this->ID,
            'name' => $this->meta->first_name .' '.$this->meta->last_name,
            'count_listing' => $properties ? $properties : 0,
        ];
    }
}
