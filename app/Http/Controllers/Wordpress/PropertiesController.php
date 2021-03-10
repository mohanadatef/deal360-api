<?php

namespace App\Http\Controllers\Wordpress;

use App\Http\Resources\Wordpress\PropertiesResource;
use App\Traits\CoreData;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\DB;

class PropertiesController extends Controller
{
    use CoreData;

    public function index_type(Request $request)
    {
        if (!isset($request->lang)) {
            $request->lang = 'en';
        }
        if (isset($request->type)) {
            if ($request->lang == 'en') {
                if ($request->type == 1 || $request->type == 'Rent') {
                    //Rent
                    $id_type = '110';
                } elseif ($request->type == 2 || $request->type == 'Buy') {
                    //Buy
                    $id_type = '47';
                } elseif ($request->type == 3 || $request->type == 'Commercial Rent') {
                    //Commercial Rent
                    $id_type = '104';
                } elseif ($request->type == 4 || $request->type == 'Commercial Buy') {
                    //Commercial Buy
                    $id_type = '153';
                }
            } elseif ($request->lang == 'ar') {
                if ($request->type == 'Rent') {
                    //Rent
                    $id_type = '145';
                } elseif ($request->type == 'Buy') {
                    //Buy
                    $id_type = '142';
                } elseif ($request->type == 'Commercial Rent') {
                    //Commercial Rent
                    $id_type = '251';
                } elseif ($request->type == 'Commercial Buy') {
                    //Commercial Buy
                    $id_type = '250';
                }
            }
        } else {
            $id_type = '47';
        }
        $properties = $this->post->published()->Join('icl_translations', 'icl_translations.element_id', 'posts.ID')
            ->where('icl_translations.language_code', $request->lang)->Join('term_relationships', 'term_relationships.object_id', 'posts.ID')
            ->where('term_relationships.term_taxonomy_id', $id_type)->where('post_author', '!=', 0)->where('post_type', 'estate_property')->inRandomOrder()->paginate(10);
        foreach ($properties as $propertiess) {
            $propertiess->favorites = $this->option->where('option_name', 'favorites' . $request->user_id)->select('option_value')->first();
            if (isset($propertiess->favorites)) {
                $propertiess_favorites = unserialize($propertiess->favorites->option_value);
                if (in_array($propertiess->ID, $propertiess_favorites)) {
                    $propertiess->favorites = 1;
                } else {
                    $propertiess->favorites = 0;
                }
            } else {
                $propertiess->favorites = 0;
            }
        }
        return response()->json(PropertiesResource::collection($properties));
    }

    public function index_favorites(Request $request)
    {
        if (!isset($request->lang)) {
            $request->lang = 'en';
        }
        if (isset($request->user_id)) {
            $favoritess = $this->option->where('option_name', 'favorites' . $request->user_id)->select('option_value')->first();
            if (isset($favoritess)) {
                $user_favorites = unserialize($favoritess->option_value);
                $properties = $this->post->published()->Join('icl_translations', 'icl_translations.element_id', 'posts.ID')
                    ->where('icl_translations.language_code', $request->lang)->where('post_type', 'estate_property')->wherein('ID', $user_favorites)->get();
                foreach ($properties as $propertiess) {
                    $propertiess->favorites = 1;
                }
                return response()->json(PropertiesResource::collection($properties));
            }
        }
        return response()->json();
    }

    protected function show(Request $request)
    {
        $properties = $this->post->find($request->id);
        if ($properties) {
            $properties->favorites = $this->option->where('option_name', 'favorites' . $request->user_id)->select('option_value')->first();
            if (isset($properties->favorites)) {
                $properties_favorites = unserialize($properties->favorites->option_value);
                if (in_array($properties->ID, $properties_favorites)) {
                    $properties->favorites = 1;
                } else {
                    $properties->favorites = 0;
                }
            } else {
                $properties->favorites = 0;
            }
            return response()->json(new PropertiesResource($properties));
        }
        return response()->json(['status' => '0', 'data' => array(), 'message' => 'Please Enter A valid ID']);
    }

    public function store_favorites(Request $request)
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

    public function properties_type()
    {
        $type = $this->term->leftjoin('term_taxonomy', 'terms.term_id', 'term_taxonomy.term_id')
            ->where('term_taxonomy.taxonomy', 'property_category')->get();
        if ($type) {
            $i = 0;
            foreach ($type as $types) {
                if ($types->description == '') {
                    unset($type[$i]);
                }
                $option_type = $this->option->where('option_name', 'taxonomy_' . $types->term_taxonomy_id)->get();
                $types->image = '';
                $types->filter = "raw";
                if ($option_type) {
                    foreach ($option_type as $optiontype) {
                        $optiontype = unserialize($optiontype->option_value);
                        if (is_array($optiontype)) {
                            if (isset($optiontype['category_featured_image'])) {
                                $types->image = $optiontype['category_featured_image'];
                            }
                        }
                    }
                }
                $i++;
            }
            return response()->json(['status' => '1', 'data' => $type, 'message' => 'Successful'], 200);
        }
        return response()->json(['status' => '0', 'data' => array(), 'message' => 'Error'], 400);
    }

    public function search(Request $request)
    {
        if (!isset($request->lang)) {
            $request->lang = 'en';
        }
        if (isset($request->search_type)) {
            if ($request->lang == 'en') {
                if ($request->search_type == 1 || $request->search_type == 'Rent') {
                    //Rent
                    $id_type = '110';
                } elseif ($request->search_type == 2 || $request->search_type == 'Buy') {
                    //Buy
                    $id_type = '47';
                } elseif ($request->search_type == 3 || $request->search_type == 'Commercial Rent') {
                    //Commercial Rent
                    $id_type = '104';
                } elseif ($request->search_type == 4 || $request->search_type == 'Commercial Buy') {
                    //Commercial Buy
                    $id_type = '153';
                }
            } elseif ($request->lang == 'ar') {
                if ($request->search_type == 'Rent') {
                    //Rent
                    $id_type = '145';
                } elseif ($request->search_type == 'Buy') {
                    //Buy
                    $id_type = '142';
                } elseif ($request->search_type == 'Commercial Rent') {
                    //Commercial Rent
                    $id_type = '251';
                } elseif ($request->search_type == 'Commercial Buy') {
                    //Commercial Buy
                    $id_type = '250';
                }
            }
        }
        $properties = $this->post->published()
            ->Join('icl_translations', 'icl_translations.element_id', 'posts.ID')
            ->where('icl_translations.language_code', $request->lang)
            ->where('post_author', '!=', 0)->where('post_type', 'estate_property')->inRandomOrder()->paginate(10);
        $i = 0;
        foreach ($properties as $propertiess) {
            if ($request->property_status && isset($propertiess->terms["property_status"]) && array_values($propertiess->terms["property_status"])[0] != $request->property_status) {
                unset($propertiess[$i]);
            }
            if ($request->city && isset($propertiess->terms["property_city"]) && array_values($propertiess->terms["property_city"])[0] != $request->city) {
                unset($propertiess[$i]);
            }
            if ($request->{'stories-number'} && isset($propertiess->meta->{'stories-number'}) && $propertiess->meta->{'stories-number'} != $request->{'stories-number'}) {
                unset($propertiess[$i]);
            }
            if ($request->num_bed && isset($propertiess->meta->property_bathrooms) && $propertiess->meta->property_bathrooms != $request->num_bed) {
                unset($propertiess[$i]);
            }
            if ($request->rent_time && isset($propertiess->meta->{'rent-time'}) && $propertiess->meta->{'rent-time'} != $request->rent_time) {
                unset($propertiess[$i]);
            }
            if ($request->start_price && isset($propertiess->meta->property_price) && $propertiess->meta->property_price < $request->start_price && $request->end_price && $propertiess->meta->property_price > $request->end_price) {
                unset($propertiess[$i]);
            }
            if ($request->start_area && isset($propertiess->meta->property_size) && $propertiess->meta->property_size < $request->start_area) {
                unset($propertiess[$i]);
            }
            if ($request->end_area && isset($propertiess->meta->property_size) && $propertiess->meta->property_size > $request->end_area) {
                unset($propertiess[$i]);
            }
            if ($request->search_type) {
                $check = DB::connection('wordpress')->table('term_relationships')->where('object_id', $propertiess->ID)->where('term_taxonomy_id', $id_type)->count();
                if ($check > 0) {
                    unset($propertiess[$i]);
                }
            }
            if ($request->property_types) {
                $property_types = $this->term->leftjoin('term_taxonomy', 'terms.term_id', 'term_taxonomy.term_id')
                    ->where('term_taxonomy.taxonomy', 'property_category')->where('description', $request->property_types)->first();
                $check = DB::connection('wordpress')->table('term_relationships')->where('object_id', $propertiess->ID)->where('term_taxonomy_id', $property_types->term_id)->count();
                if ($check > 0) {
                    unset($propertiess[$i]);
                }
            }
            $propertiess->favorites = $this->option->where('option_name', 'favorites' . $request->user_id)->select('option_value')->first();
            if (isset($propertiess->favorites)) {
                $properties_favorites = unserialize($propertiess->favorites->option_value);
                if (in_array($propertiess->ID, $properties_favorites)) {
                    $propertiess->favorites = 1;
                } else {
                    $propertiess->favorites = 0;
                }
            } else {
                $propertiess->favorites = 0;
            }
        }
        return response()->json(['status' => '1', 'data' => PropertiesResource::collection($properties), 'message' => 'Successful'], 200);
    }
}