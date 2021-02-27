<?php

namespace App\Http\Resources\Wordpress;

use App\Models\Wordpress\CommentWordpress;
use App\Models\Wordpress\PostMetaWordpress;
use App\Models\Wordpress\PostWordpress;
use App\Models\Wordpress\UserWordpress;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\DB;

class PropertiesResource extends JsonResource
{
    public function toArray($request)
    {
        $images = [];
        $package_3d_image = "";
        $floor_plan_image = "";
        $image_to_attach_id = explode(",", $this->meta->image_to_attach);
        $images_id = DB::connection('wordpress')->table('postmeta')->wherein('post_id', $image_to_attach_id)->where('meta_key', '_wp_attached_file')->select('meta_value')->get();
        foreach ($images_id as $id) {
            array_push($images, 'https://deal360.ae/wp-content/uploads/' . $id->meta_value);
        }
        $post_authors = UserWordpress::find($this->post_author);
        $image_author = isset($post_authors->meta->custom_picture) ? $post_authors->meta->custom_picture : "";
        $name_author = $post_authors->meta->first_name . ' ' . $post_authors->meta->last_name;
        if (!$post_authors->meta->first_name) {
            $post_author = PostWordpress::find($post_authors->meta->user_agent_id);
            $name_author = isset($post_author->post_title) ? $post_author->post_title : "";
        }
        if ($this->meta->embed_virtual_tour) {
            $embed_virtual_tour = explode('"', $this->meta->embed_virtual_tour);
            $virtual_tour = $embed_virtual_tour[1];
        }
        $is_rate = CommentWordpress::where('comment_approved', '!=', 'trash')->where('comment_post_ID', $this->ID)->where('user_id', $request->user_id)->count();
        if (isset($this->meta->plan_image_attach)) {
            if (gettype(unserialize($this->meta->plan_image_attach)) != 'boolean') {
                $floor_plan_image = PostMetaWordpress::where('post_id', unserialize($this->meta->plan_image_attach)[0])->where('meta_key', '_wp_attached_file')->first();
            }
        }
        $stars = CommentWordpress::leftjoin('commentmeta', 'comments.comment_id', 'commentmeta.comment_id')
            ->where('commentmeta.meta_key', 'review_stars')
            ->where('comments.comment_post_ID', $this->ID)
            ->where('comments.comment_approved', 1)
            ->avg('commentmeta.meta_value');
        if (isset($this->meta->{'3d-package-id'})) {
            $package_3d_image = PostWordpress::find($this->meta->{'3d-package-id'});
        }
        return [
            'favorites' => $this->favorites ? '1' : '0',
            'post_title' => $this->post_title ? $this->post_title : '',
            'post_author' => "" . $this->post_author . "" ? "" . $this->post_author . "" : '',
            'name_author' => $name_author ? $name_author : '',
            'broker_name' => $name_author ? $name_author : '',
            'image_author' => $image_author ? $image_author : '',
            'broker_image' => $image_author ? $image_author : '',
            'ID' => "" . $this->ID . "",
            'post_content' => $this->post_content ? $this->post_content : '',
            'Description' => $this->post_content ? $this->post_content : '',
            'images' => $images ? $images : array(),
            'post_image' => $this->thumbnail ? 'https://deal360.ae/wp-content/uploads/' . $this->thumbnail->attachment->meta->_wp_attached_file : "",
            'property_address' => $this->meta->property_address ? $this->meta->property_address : "",
            'property_size' => $this->meta->property_size ? $this->meta->property_size : "",
            'property_rooms' => $this->meta->property_rooms ? $this->meta->property_rooms : "0",
            'property_bedrooms' => $this->meta->property_bedrooms ? $this->meta->property_bedrooms : "",
            'property_bathrooms' => $this->meta->property_bathrooms ? $this->meta->property_bathrooms : "",
            'property_price' => $this->meta->property_price ? $this->meta->property_price : "",
            'embed_video_type' => $this->meta->embed_video_id ? true : false,
            'rent-time' => $this->meta->{'rent-time'} ? $this->meta->{'rent-time'} : "",
            'area' => $this->meta->area ? $this->meta->area : "",
            'property_city' => isset($this->terms["property_city"]) ? array_values($this->terms["property_city"])[0] : (isset($this->terms["property_city_agent"]) ? array_values($this->terms["property_city_agent"])[0] : ""),
            'Property_Type' => isset($this->terms["property_action_category"]) ? array_values($this->terms["property_action_category"])[0] : '',
            'property_status' => isset($this->terms["property_status"]) ? array_values($this->terms["property_status"])[0] : '',
            'Property_Cat_Type' => isset($this->terms["Property_Cat_Type"]) ? array_values($this->terms["Property_Cat_Type"])[0] : (isset($this->terms["property_category"]) ? array_values($this->terms["property_category"])[0] : ''),
            'Amenaties' => isset($this->terms["property_features"]) ? array_values($this->terms["property_features"]) : array(),
            'property_latitude' => $this->meta->property_latitude ? $this->meta->property_latitude : "",
            'property_longitude' => $this->meta->property_longitude ? $this->meta->property_longitude : "",
            'property-garage' => $this->meta->{'property-garage'} ? $this->meta->{'property-garage'} : "",
            'property_lot_size' => $this->meta->property_lot_size ? $this->meta->property_lot_size : "0",
            'property-date' => $this->meta->{'property-date'} ? $this->meta->{'property-date'} : "",
            'stories-number' => $this->meta->{'stories-number'} ? $this->meta->{'stories-number'} : "",
            'property_country' => $this->meta->property_country ? $this->meta->property_country : "",
            'embed_video_id' => $this->meta->embed_video_id ? $this->meta->embed_video_id : "",
            'is_rate' => $is_rate > 0 ? 1 : 0,
            'stars' => !empty($stars) ? $stars : '0',
            'post_rate' => !empty($stars) ? $stars : '0',
            'post_link' => $this->post_name ? 'https://deal360.ae/estate_property/' . $this->post_name : '',
            'updated_on' => $this->post_modified ? $this->post_modified : '',
            'post_id' => "" . $this->ID . "",
            'agency_image' => $post_authors->meta->custom_picture ? $post_authors->meta->custom_picture : "",
            'broker_id' => "" . $this->post_author . "" ? "" . $this->post_author . "" : '',
            'broker_type' => $post_authors->meta->user_estate_role,
            'broker_phone' => $post_authors->meta->phone ? $post_authors->meta->phone : "",
            'broker_whatsapp' => isset($post_authors->meta->mobile) ? $post_authors->meta->mobile : (isset($post_authors->meta->phone) ? $post_authors->meta->phone : ""),
            'video_id' => $this->meta->embed_video_id ? $this->meta->embed_video_id : "",
            'video_type' => $this->meta->embed_video_type ? $this->meta->embed_video_type : "",
            'floor_plan_title' => $this->meta->plan_size ? unserialize($this->meta->plan_title)[0] : "",
            'floor_plan_image' => $floor_plan_image ? 'https://deal360.ae/wp-content/uploads/' . $floor_plan_image->meta_value : "",
            'floor_plan_size' => $this->meta->plan_size ? unserialize($this->meta->plan_size)[0] : "",
            'floor_plan_Bathrooms' => $this->meta->plan_bath ? unserialize($this->meta->plan_bath)[0] : "",
            'floor_plan_Description' => $this->meta->plan_description ? unserialize($this->meta->plan_description)[0] : "",
            '3d-package-id' => $this->meta->{'3d-package-id'} ? $this->meta->{'3d-package-id'} : "",
            '3d-package-images' => isset($package_3d_image->meta->_wp_attachment_metadata) ? unserialize($package_3d_image->meta->_wp_attachment_metadata)[0]['file'] : "",
            'virtual_tour' => isset($virtual_tour) ? $virtual_tour : "",
        ];
    }
}