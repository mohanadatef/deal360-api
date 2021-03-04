<?php

namespace App\Http\Controllers\Wordpress;

use App\Http\Resources\Wordpress\AgencyCrmResource;
use App\Http\Resources\Wordpress\AgencyResource;
use App\Http\Resources\Wordpress\AgentCrmResource;
use App\Http\Resources\Wordpress\AgentResource;
use App\Models\Wordpress\OptionWordpress;
use App\Models\Wordpress\PostWordpress;
use App\Models\Wordpress\UserWordpress;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;

class AgencyController extends Controller
{
    protected $post, $user, $option;

    public function __construct(PostWordpress $post, UserWordpress $user, OptionWordpress $option)
    {
        $this->post = $post;
        $this->user = $user;
        $this->option = $option;
    }

    public function index(Request $request)
    {
        if (!isset($request->lang)) {
            $request->lang = 'en';
        }
        $agency = $this->user->whereHas('meta', function (Builder $query) {
            $query->where('meta_key', 'current_agent_list');
        })->whereHas('meta', function (Builder $query) {
            $query->where('meta_key', 'user_estate_role')
                ->where('meta_value', '3');
        })->get();
        foreach ($agency as $myagency) {
            $agent_id = unserialize($myagency->meta->current_agent_list);
            $myagency->agent = $this->user->with('meta')->wherein('ID', $agent_id)->get();
            $myagency->post_meta = $this->post->find($myagency->meta->user_agent_id);
            array_push($agent_id, $myagency->ID);
            $myagency->properties = $this->post->published()->leftJoin('icl_translations', 'icl_translations.element_id', 'posts.ID')
                ->where('icl_translations.language_code', $request->lang)->where('post_type','estate_property')->where('post_author', '!=', 0)->wherein('post_author', $agent_id)->paginate(10);
            foreach ($myagency->properties as $properties) {
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
            }
        }
        return response()->json(['status' => 1, 'data' => AgencyResource::collection($agency), 'message' => 'Message_Logout']);
    }

    public function show(Request $request)
    {
        $agent_id = array();
        if (!isset($request->lang)) {
            $request->lang = 'en';
        }
        $agency = $this->user->find($request->id);
        if ($agency) {
            if (isset($agency->meta->current_agent_list)) {
                $agent_id = unserialize($agency->meta->current_agent_list);
            }
            $agency->agent = $this->user->with('meta')->wherein('ID', $agent_id)->get();
            $agency->post_meta = $this->post->find($agency->meta->user_agent_id);
            array_push($agent_id, $agency->ID);
            $agency->properties = $this->post->published()->Join('icl_translations', 'icl_translations.element_id', 'posts.ID')
                ->where('icl_translations.language_code', $request->lang)->where('post_type','estate_property')->where('post_author', '!=', 0)->wherein('post_author', $agent_id)->inRandomOrder()->first();
            if ($agency->properties) {
                $agency->properties->favorites = $this->option->where('option_name', 'favorites' . $request->user_id)->select('option_value')->first();
                if (isset($agency->properties->favorites)) {
                    $properties_favorites = unserialize($agency->properties->favorites->option_value);
                    if (in_array($agency->properties->ID, $properties_favorites)) {
                        $agency->properties->favorites = 1;
                    } else {
                        $agency->properties->favorites = 0;
                    }
                } else {
                    $agency->properties->favorites = 0;
                }
                $agency->properties->favorites = 0;
            }
            if($agency->meta->user_estate_role == 3)
            {
                return response()->json(['status' => 1, 'data' => new AgencyResource($agency), 'message' => 'Message_Done']);
            }
            elseif($agency->meta->user_estate_role == 2)
            {
                return response()->json(['status' => 1, 'data' => new AgentResource($agency), 'message' => 'Message_Done']);
            }
        }
        return response()->json(['status' => 0, 'data' => array(), 'message' => 'id not found']);
    }

    public function index_properties(Request $request)
    {
        $agent_id = array();
        if (!isset($request->lang)) {
            $request->lang = 'en';
        }
        $agency = $this->user->find($request->id);
        if ($agency) {
            if (isset($agency->meta->current_agent_list)) {
                $agent_id = unserialize($agency->meta->current_agent_list);
            }
            $agency->post_meta = $this->post->find($agency->meta->user_agent_id);
            array_push($agent_id, $agency->ID);
            $agency->properties = $this->post->published()->Join('icl_translations', 'icl_translations.element_id', 'posts.ID')
                ->where('icl_translations.language_code', $request->lang)->where('post_type','estate_property')->where('post_author', '!=', 0)->wherein('post_author', $agent_id)->paginate(10);
            foreach ($agency->properties as $properties) {
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
            }
            if($agency->meta->user_estate_role == 3)
            {
                return response()->json(['status' => 1, 'data' => new AgencyResource($agency), 'message' => 'Message_Done']);
            }
            elseif($agency->meta->user_estate_role == 2)
            {
                return response()->json(['status' => 1, 'data' => new AgentResource($agency), 'message' => 'Message_Done']);
            }
        }
        return response()->json(['status' => 0, 'data' => array(), 'message' => 'id not found']);
    }

    public function all_agency()
    {
        $agency = $this->user->whereHas('meta', function (Builder $query) {
            $query->where('meta_key', 'current_agent_list');
        })->whereHas('meta', function (Builder $query) {
            $query->where('meta_key', 'user_estate_role')
                ->where('meta_value', '3');
        })->get();
        foreach ($agency as $myagency) {
            $agent_id = unserialize($myagency->meta->current_agent_list);
            $myagency->post_meta = $this->post->find($myagency->meta->user_agent_id);
            array_push($agent_id, $myagency->ID);
            $myagency->properties_count = $this->post->leftJoin('icl_translations', 'icl_translations.element_id', 'posts.ID')
                ->where('icl_translations.language_code', 'en')->where('post_type','estate_property')->where('post_author', '!=', 0)->wherein('post_author', $agent_id)->count();
        }
        return response()->json(['status' => 1, 'data' => AgencyCrmResource::collection($agency), 'message' => 'Message_Logout']);
    }

    public function all_agent()
    {
        $agency = $this->user->whereHas('meta', function (Builder $query) {
            $query->where('meta_key', 'user_estate_role')
                ->where('meta_value', '2');
        })->get();
        return response()->json(['status' => 1, 'data' => AgentCrmResource::collection($agency), 'message' => 'Message_Logout']);
    }
}
