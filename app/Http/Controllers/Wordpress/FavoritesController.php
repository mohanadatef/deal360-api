<?php

namespace App\Http\Controllers\Wordpress;

use App\Http\Resources\Wordpress\PropertiesResource;
use App\Models\Wordpress\OptionWordpress;
use App\Models\Wordpress\PostWordpress;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;

class FavoritesController extends Controller
{
    protected $post, $option;

    public function __construct(PostWordpress $post, OptionWordpress $option)
    {
        $this->post = $post;
        $this->option = $option;
    }

    public function index(Request $request)
    {
        if (!isset($request->lang)) {
            $request->lang = 'en';
        }
        if (isset($request->user_id)) {
            $favoritess = $this->option->where('option_name', 'favorites' . $request->user_id)->select('option_value')->first();
            if (isset($favoritess)) {
                $user_favorites = unserialize($favoritess->option_value);
                $properties = $this->post->published()->Join('icl_translations', 'icl_translations.element_id', 'posts.ID')
                    ->where('icl_translations.language_code', $request->lang)->wherein('ID', $user_favorites)->get();
                foreach ($properties as $propertiess) {
                    $propertiess->favorites = 1;
                }
                return response()->json(PropertiesResource::collection($properties));
            }
        }
        return response()->json();
    }

    public function store(Request $request)
    {
        if (isset($request->user_id)) {
            $favoritess = $this->option->where('option_name', 'favorites' . $request->user_id)->first();
            if (!$favoritess) {
                $favorites = new $this->option;
                $favorites->option_name = 'favorites' . $request->user_id;
                $favorites->option_value = serialize(array($request->post_id));
                $favorites->autoload = 'yes';
                $favorites->save();
            } else {
                $user_favorites = unserialize($favoritess->option_value);
                if (!in_array($request->post_id, $user_favorites)) {
                    array_push($user_favorites, $request->post_id);
                } else {
                    unset($user_favorites[array_search($request->post_id, $user_favorites)]);
                }
                $favoritess->option_value = serialize($user_favorites);
                $favoritess->save();
            }
            $favoritess = $this->option->where('option_name', 'favorites' . $request->user_id)->select('option_value')->first();
            return response()->json(['status' => '1', 'data' => array_values(unserialize($favoritess->option_value)), 'message' => 'successful']);
        }
        return response()->json(['status' => '0', 'data' => array(), 'message' => 'make sure you have enter ( Post Id - User Id)']);
    }
}
