<?php

use App\Models\Post as Post;
use App\Models\Tasks;
use App\Models\User;
use Illuminate\Support\Facades\Auth;

if (!function_exists('getProperty')) {
    function getProperty($id='',$lang='',$num='',$ids=[],$status='',$user_id='',$tasks=false,$userRole='')
    {
        // Get a custom meta value (like 'link' or whatever) from a post (any type)
//        $post = Post::find(37703);
//        $posts = Post::published()->type('estate_property')->get()
        $data=array();
        $pages=array();
        $posts = Post::type('estate_property')
            ->leftJoin('icl_translations','icl_translations.element_id','posts.ID');
        if(isset($lang) and !empty($lang)){
            $posts->where('icl_translations.language_code',$lang);
        }
        if($status=='pending' and $userRole==1){
            if (!empty($ids) or !empty($user_id)){
                $posts->whereNotIn('posts.ID',$ids);
            }
        }else{
            if (!empty($ids) or !empty($user_id)){
                $posts->whereIn('posts.ID',$ids);
            }
        }
        if (!empty($status)){
            if($status=='publish'){
                $posts->published();
            }elseif($status=='pending'){
                $posts->where('post_status','pending');
            }else{
                $posts->where('post_status','!=','draft');
                $posts->where('post_status','!=','expired');
                $posts->where('post_status','!=','trash');
            }
        }else{
            $posts->where('post_status','!=','draft');
            $posts->where('post_status','!=','expired');
            $posts->where('post_status','!=','trash');
        }
        if (isset($id) and !empty($id)){
            $posts->where('posts.ID',$id);
        }
        $posts->orderBy('posts.ID','DESC');
        if(isset($num) and !empty($num)) {
            $posts=$posts->paginate($num);
            $pages['currentPage']=intval($posts->currentPage());
            $pages['lastPage']=intval($posts->lastPage());
            $pages['perPage']=intval($posts->perPage());
            $pages['total']=intval($posts->total());
        }
        else{
            $posts=$posts->get();
        }

        foreach ($posts as $post){
            //Define Var
            $category='';
            $type='';
            $city='';
            $property_status='';
            $embed_virtual_tour='';
            $property_features=array();
            $tasks_arr=array();
            $tasks_status='';
            $agent_name='';
            $agent_phone='';
            $agency_name='';
//            $agency_phone='';
//            $agent_id='';
//            $agency_id='';

            //Get Property Category
            if (isset($post->terms["property_category"])){
                foreach ($post->terms["property_category"] as $value){
                    $category=$value;
                }
            }

            //Get Property Action Category
            if (isset($post->terms["property_action_category"])){
                foreach ($post->terms["property_action_category"] as $value){
                    $type=$value;
                }
            }

            //Get Property City
            if (isset($post->terms["property_city"])){
                foreach ($post->terms["property_city"] as $value){
                    $city=$value;
                }
            }

            //Get Property Features
            if (isset($post->terms["property_features"])){
                foreach ($post->terms["property_features"] as $value){
                    $property_features[]=$value;
                }
            }

            //Get Embed Virtual Tour
            if(!empty($post->meta->embed_virtual_tour)){
                $embed_virtual_tour=$post->meta->embed_virtual_tour;
                $embed_virtual_tour=explode('"',$embed_virtual_tour);
                if (isset($embed_virtual_tour[1])){
                    $embed_virtual_tour=$embed_virtual_tour[1];
                }else{
                    $embed_virtual_tour='';
                }

            }

            //Get Property Status
            if (isset($post->terms["property_status"])){
                foreach ($post->terms["property_status"] as $value){
                    $property_status=$value;
                }
            }
            $agent=DB::connection('wordpress')
                ->table('users')
                ->leftjoin('usermeta','usermeta.user_id','users.id')
                ->select('users.user_nicename as name','users.id','usermeta.meta_value as phone')
                ->where('usermeta.meta_key','phone')
                ->where('users.id',$post->post_author)
                ->first();

            $agency=DB::connection('wordpress')
                ->table('usermeta')
                ->leftjoin('users','usermeta.user_id','users.id')
                ->select('users.user_nicename as name','users.id')
                ->where('usermeta.meta_key','current_agent_list')
                ->where('usermeta.meta_value','like','%:'.$post->post_author.';%')
                ->first();
            if ($agent){
                $agent_name=$agent->name;
                $agent_phone=$agent->phone;
                $agent_id=$agent->id;
            }

            if ($agency){
                $agency_name=$agency->name;
                $agency_id=$agency->id;
            }

            $post_image_id=DB::connection('wordpress')
                ->table('postmeta')
                ->select('meta_value')
                ->where('meta_key','_thumbnail_id')
                ->where('post_id',$post->ID)
                ->first();
            if ($post_image_id){
                $post_image=DB::connection('wordpress')
                    ->table('postmeta')
                    ->select('meta_value')
                    ->where('post_id',$post_image_id->meta_value)
                    ->where('meta_key','_wp_attached_file')
                    ->first();
                $post_img='https://deal360.ae/wp-content/uploads/'.$post_image->meta_value;
            }else{
                $post_img='';
            }
            if (isset($id) and !empty($id)){
                $img=array();
                $all = explode(",", $post->meta->image_to_attach);
                if (!empty($all)){
                    $post_images=DB::connection('wordpress')
                        ->table('postmeta')
                        ->select('meta_value')
                        ->wherein('post_id',$all)
                        ->where('meta_key','_wp_attached_file')
                        ->get();
                    foreach ($post_images as $item){
                        $img[]='https://deal360.ae/wp-content/uploads/'.$item->meta_value;
                    }
                }else{
                    $img=array();
                }

                if($post->post_status!='publish'){
                    if(!empty($user_id)){
                        $userAuth = User::find($user_id);
                        $userAuthId=$userAuth->id;
                    }else{
                        $userAuth = Auth::user();
                        $userAuthId=$userAuth->id;
                    }
                    $tasks_status=Tasks::where('wp_post_id',$post->ID)->where('users_id',$userAuthId)->select('current_status_id')->first();
                    if ($tasks_status){
                        $tasks_status=$tasks_status->current_status_id;
                    }else{
                        $getCount=Tasks::where('tasks.wp_post_id',$post->ID)->count();
                        if ($getCount>0){
                            $tasks_status='2';
                        }else{
                            $tasks_status='1';
                        }
                    }
                }else{
                    $tasks_status='4';
                }

                //Returned Data
                $data=array(
                    'language_code'=>$post->language_code,
                    'id'=>$post->ID,
                    'post_status'=>$post->post_status,
                    'images'=>$post_img,
                    'agent_name'=>$agent_name,
                    'agent_phone'=>$agent_phone,
                    'agency_name'=>$agency_name,
                    'tasks_status'=>$tasks_status,
                    'description'=>array(
                        'title'=>$post->post_title,
                        'price'=>$post->meta->property_price,
                        'status'=>$property_status,
                        'category'=>$category,
                        'type'=>$type,
                        'description'=>$post->post_content,
                    ),
                    'media'=>array(
                        'media_images'=>$img,
                        'embed_Video_id'=>$post->meta->embed_video_id,
                        'virtual_tour'=>$embed_virtual_tour,
                    ),
                    'location'=>array(
                        'address'=>$post->meta->property_address,
                        'city'=>$city,
                        'latitude'=>$post->meta->property_latitude,
                        'longitude'=>$post->meta->property_longitude,
                    ),
                    'details'=>array(
                        'size'=>$post->meta->property_size,
                        'lot_size'=>$post->meta->property_lot_size,
                        'rooms'=>$post->meta->property_rooms,
                        'bedrooms'=>$post->meta->property_bedrooms,
                        'bathrooms'=>$post->meta->property_bathrooms,
                        'garages'=>$post->meta->{'property-garage'},
                        'available_from'=>$post->post_modified,
                        'floors_no'=>$post->meta->{'stories-number'},
                        'rent_time'=>$post->meta->{'rent-time'},
                        'area'=>$post->meta->area,
                        'reference'=>$post->meta->reference,
                        'broker_orn'=>$post->meta->{'broker-orn'},
                        'trakheesi_permit'=>$post->meta->{'trakheesi-permit'},
                        'property_date'=>$post->meta->{'property-date'},
                    ),
                    'property_features'=>$property_features,
                );
            }
            else{
                if ($tasks){
                    $tasks_arr['DA']=Tasks::where('tasks.wp_post_id',$post->ID)->leftJoin('users','users.id','tasks.users_id')->where('users.role_id','2')->count();
                    $tasks_arr['AM']=Tasks::select('users.id','users.name','users.image','users.role_id')->where('tasks.wp_post_id',$post->ID)->leftJoin('users','users.id','tasks.users_id')->where('users.role_id','3')->first();
                    $tasks_arr['PH']=Tasks::where('tasks.wp_post_id',$post->ID)->leftJoin('users','users.id','tasks.users_id')->where('users.role_id','4')->count();
                    $tasks_arr['GD']=Tasks::where('tasks.wp_post_id',$post->ID)->leftJoin('users','users.id','tasks.users_id')->where('users.role_id','5')->count();
                    $tasks_arr['CM']=Tasks::where('tasks.wp_post_id',$post->ID)->leftJoin('users','users.id','tasks.users_id')->where('users.role_id','6')->count();
                }else{
                    $tasks_arr['AM']=Tasks::select('users.id','users.name','users.image','users.role_id')->where('tasks.wp_post_id',$post->ID)->leftJoin('users','users.id','tasks.users_id')->where('users.role_id','3')->first();
                }
                if($post->post_status!='publish'){
                    if(!empty($user_id)){
                        $userAuth = User::find($user_id);
                        $userAuthId=$userAuth->id;
                    }else{
                        $userAuth = Auth::user();
                        $userAuthId=$userAuth->id;
                    }
                    $tasks_status=Tasks::where('wp_post_id',$post->ID)->where('users_id',$userAuthId)->select('current_status_id')->first();
                    if ($tasks_status){
                        $tasks_status=$tasks_status->current_status_id;
                    }else{
                        $getCount=Tasks::where('tasks.wp_post_id',$post->ID)->count();
                        if ($getCount>0){
                            $tasks_status='2';
                        }else{
                            $tasks_status='1';
                        }
                    }
                }else{
                    $tasks_status='4';
                }
                if(!empty($user_id)){
                    $getExp=Tasks::where('wp_post_id',$post->ID)->where('users_id',$user_id)->first();
                    if ($getExp){
                        $tasks_exp=$getExp->expiry_date;
                    }else{
                        $tasks_exp='';
                    }
                }else{
                    $tasks_exp='';
                }


                //Returned Data
                $data[]=array(
                    'language_code'=>$post->language_code,
                    'id'=>$post->ID,
                    'post_status'=>$post->post_status,
                    'title'=>$post->post_title,
                    'description'=>$post->post_content,
                    'price'=>$post->meta->property_price,
                    'category'=>$category,
                    'type'=>$type,
                    'status'=>$property_status,
                    'images'=>$post_img,
                    'embed_Video_id'=>$post->meta->embed_video_id,
                    'virtual_tour'=>$embed_virtual_tour,
                    'address'=>$post->meta->property_address,
                    'city'=>$city,
                    'latitude'=>$post->meta->property_latitude,
                    'longitude'=>$post->meta->property_longitude,
                    'size'=>$post->meta->property_size,
                    'lot_size'=>$post->meta->property_lot_size,
                    'rooms'=>$post->meta->property_rooms,
                    'bedrooms'=>$post->meta->property_bedrooms,
                    'bathrooms'=>$post->meta->property_bathrooms,
                    'garages'=>$post->meta->{'property-garage'},
                    'available_from'=>$post->post_modified,
                    'floors_no'=>$post->meta->{'stories-number'},
                    'rent_time'=>$post->meta->{'rent-time'},
                    'area'=>$post->meta->area,
                    'trakheesi_permit'=>$post->meta->{'trakheesi-permit'},
                    'reference'=>$post->meta->reference,
                    'broker_orn'=>$post->meta->{'broker-orn'},
                    'property_features'=>$property_features,
                    'agent_name'=>$agent_name,
                    'agent_phone'=>$agent_phone,
                    'agency_name'=>$agency_name,
                    'tasks'=>$tasks_arr,
                    'tasks_status'=>$tasks_status,
                    'tasks_exp'=>$tasks_exp,
                );
            }
        }
        $response['data']=$data;
        $response['pages']=$pages;

        //Print Data
        return $response;

    }
}
