<?php

namespace App\Http\Controllers\Wordpress;
use App\Models\Wordpress\PostWordpress;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;

class PackageController extends Controller
{

    protected $post;

    public function __construct(PostWordpress $post)
    {
        $this->post = $post;
    }

    public function index(Request $request)
    {
        $lang='en';
        if (isset($request->lang)) {
            $lang=$request->lang;
        }
        $data=array();
        $packages = $this->post->published()->Join('icl_translations','icl_translations.element_id','posts.ID')
            ->where('icl_translations.language_code',$lang)->where('post_type','membership_package')->get();
        foreach ($packages as $value) {
                $pack_visible_user_role = unserialize($value->meta->pack_visible_user_role);
                if (is_array($pack_visible_user_role) && in_array($request->role, $pack_visible_user_role) && $value->meta->pack_visible == 'yes') {
                    $data[] = array(
                        'ID' => $value->ID,
                        'post_title' => $value->post_title,
                        'pack_listings' => $value->meta->pack_listings,
                        'pack_featured_listings' => $value->meta->pack_featured_listings,
                        'pack_image_included' => $value->meta->pack_image_included,
                        'pack_price' => $value->meta->pack_price,
                        'biling_period' => $value->meta->biling_period);
                }
        }
        return response()->json(['status' => 1, 'data' => $data, 'message' => 'Message_Done']);
    }
}
