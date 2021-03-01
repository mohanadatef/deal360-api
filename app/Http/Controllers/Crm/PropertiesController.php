<?php

namespace App\Http\Controllers\Crm;

use App\Http\Controllers\Controller;
use App\Models\Tasks;
use App\Models\User;
use Illuminate\Http\Request;
use App\Models\Post as Post;
use DB;
use Illuminate\Support\Facades\Auth;
use Validator;
class PropertiesController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $lang=$request->lang;
        $num=$request->num;
        $status=$request->status;
        $user_id=$request->user_id;
        $id=$request->id;
        $arr=array();
        $getData=array();
        $getTaskStatus=false;
        if (isset($request->user_id) and !empty($request->user_id)){
            $userId=$request->user_id;
            $user = User::find($userId);
            $userRole=$user->role_id;
        }else{
            $user = Auth::user();
            $userId=$user->id;
            $userRole=$user->role_id;
        }
        if($userRole==1){
            $getTaskStatus=true;
            if ($status=='inProgress' or $status=='expired'or $status=='pending') {
                if($status=='expired'){
                    $getData=Tasks::select('wp_post_id')->where('current_status_id',3)->groupBy('wp_post_id')->get();
                }elseif ($status=='inProgress'){
                    $getData=Tasks::select('wp_post_id')->where('current_status_id',2)->groupBy('wp_post_id')->get();
                }elseif ($status=='pending'){
                    $getData=Tasks::select('wp_post_id')->where('current_status_id',2)->orwhere('current_status_id',4)->groupBy('wp_post_id')->get();
                }
                foreach ($getData as $arrData){
                    $arr[]=$arrData->wp_post_id;
                }
                if ($status!='pending') {
                    if (count($arr) == 0) {
                        $arr[] = 0;
                    }
                }
            }
        }else{
            if ($status=='inProgress' or $status=='expired'or $status=='pending' or $status=='publish') {
                if($status=='expired'){
                    $getData=Tasks::select('wp_post_id')->where('users_id',$userId)->where('current_status_id',3)->groupBy('wp_post_id')->get();
                }elseif ($status=='inProgress'){
                    $getData=Tasks::select('wp_post_id')->where('users_id',$userId)->where('current_status_id',2)->groupBy('wp_post_id')->get();
                }elseif ($status=='pending'){
                    $getData=Tasks::select('wp_post_id')->where('users_id',$userId)->where('current_status_id',1)->groupBy('wp_post_id')->get();
                }elseif ($status=='publish'){
                    $getData=Tasks::select('wp_post_id')->where('users_id',$userId)->where('current_status_id',4)->groupBy('wp_post_id')->get();
                }
                foreach ($getData as $arrData){
                    $arr[]=$arrData->wp_post_id;
                }
                if (count($arr) == 0) {
                    $arr[] = 0;
                }
            }else{
                $getData=Tasks::select('wp_post_id')->where('users_id',$userId)->groupBy('wp_post_id')->get();
                foreach ($getData as $arrData){
                    $arr[]=$arrData->wp_post_id;
                }
                if (count($arr) == 0) {
                    $arr[] = 0;
                }
            }
        }


        $result=getProperty($id,$lang,$num,$arr,$status,$user_id,$getTaskStatus,$userRole);
        $data=$result['data'];
        $pages=$result['pages'];
        $response=array('status'=>1,'data'=>$data,'pages'=>$pages,'message'=>'success');
        echo json_encode($response);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'action' => 'required',
            'id' => 'required',
        ]);
        if ($validator->fails()) {
            $data=array('status'=>0,'data'=>array(),'message'=>$validator->errors());
            $code=401;
            return response()->json($data,$code);
        }
        $id=$request->id;
        $action=$request->action;
        if ($action=='description'){
            $post = Post::find($id);
            $post->post_title=$request->title;
            $post->saveMeta('property_price',$request->price);
            $post->post_content=$request->description;
            $post->save();

            $old_status=$request->old_status;
            $old_category=$request->old_category;
            $old_type=$request->old_type;
            $status=$request->status;
            $category=$request->category;
            $type=$request->type;

            if(!empty($old_status)){
                $getId=DB::connection('wordpress')->table('term_taxonomy')
                    ->select('terms.term_id as id')
                    ->leftJoin('terms','terms.term_id','term_taxonomy.term_id')
                    ->where('term_taxonomy.taxonomy','property_status')
                    ->where('terms.name',$old_status)
                    ->first();
                if ($getId){
                    $old=$getId->id;
                    $getId=DB::connection('wordpress')
                        ->table('term_relationships')
                        ->where('object_id',$id)
                        ->where('term_taxonomy_id',$old)
                        ->update(['term_taxonomy_id'=>$status]);
                }else{
                    DB::connection('wordpress')
                        ->table('term_relationships')
                        ->insert(['term_taxonomy_id'=>$status,'object_id'=>$id]);
                }
            }else{
                DB::connection('wordpress')
                    ->table('term_relationships')
                    ->insert(['term_taxonomy_id'=>$status,'object_id'=>$id]);
            }

            if(!empty($old_category)){
                $getId=DB::connection('wordpress')->table('term_taxonomy')
                    ->select('terms.term_id as id')
                    ->leftJoin('terms','terms.term_id','term_taxonomy.term_id')
                    ->where('term_taxonomy.taxonomy','property_category')
                    ->where('terms.name',$old_category)
                    ->first();
                if ($getId){
                    $old=$getId->id;
                    $getId=DB::connection('wordpress')
                        ->table('term_relationships')
                        ->where('object_id',$id)
                        ->where('term_taxonomy_id',$old)
                        ->update(['term_taxonomy_id'=>$category]);
                }else{
                    DB::connection('wordpress')
                        ->table('term_relationships')
                        ->insert(['term_taxonomy_id'=>$category,'object_id'=>$id]);
                }
            }else{
                DB::connection('wordpress')
                    ->table('term_relationships')
                    ->insert(['term_taxonomy_id'=>$category,'object_id'=>$id]);
            }

            if(!empty($old_type)){
                $getId=DB::connection('wordpress')->table('term_taxonomy')
                    ->select('terms.term_id as id')
                    ->leftJoin('terms','terms.term_id','term_taxonomy.term_id')
                    ->where('term_taxonomy.taxonomy','property_action_category')
                    ->where('terms.name',$old_type)
                    ->first();
                if ($getId){
                    $old=$getId->id;
                    $getId=DB::connection('wordpress')
                        ->table('term_relationships')
                        ->where('object_id',$id)
                        ->where('term_taxonomy_id',$old)
                        ->update(['term_taxonomy_id'=>$type]);
                }else{
                    DB::connection('wordpress')
                        ->table('term_relationships')
                        ->insert(['term_taxonomy_id'=>$type,'object_id'=>$id]);
                }
            }else{
                DB::connection('wordpress')
                    ->table('term_relationships')
                    ->insert(['term_taxonomy_id'=>$type,'object_id'=>$id]);
            }

            $data=array('status'=>1,'data'=>array(),'message'=>'Success.');
            $code=200;

        }

        elseif ($action=='media'){
            if(!empty($request->virtual_tour)){
                $virtual_tour='<iframe src="';
                $virtual_tour.=$request->virtual_tour;
                $virtual_tour.='" width="100%" height="100%" allowfullscreen="true"></iframe>';
            }else{
                $virtual_tour='';
            }
//            dd($virtual_tour);
            $post = Post::find($id);
            $post->saveMeta([
                'embed_video_id'=>$request->embed_Video_id,
                'embed_virtual_tour'=>$virtual_tour,
            ]);
            $post->save();
            $data=array('status'=>1,'data'=>array(),'message'=>'Success.');
            $code=200;
        }

        elseif ($action=='location'){
            $post = Post::find($id);
            $post->saveMeta([
                'property_address' => $request->address,
                'property_latitude' => $request->latitude,
                'property_longitude' => $request->longitude,
            ]);
            $post->save();
            $old_city=$request->old_city;
            $city=$request->city;

            if(!empty($old_city)){
                $getId=DB::connection('wordpress')->table('term_taxonomy')
                    ->select('terms.term_id as id')
                    ->leftJoin('terms','terms.term_id','term_taxonomy.term_id')
                    ->where('term_taxonomy.taxonomy','property_city')
                    ->where('terms.name',$old_city)
                    ->first();
                if ($getId){
                    $old=$getId->id;
                    $getId=DB::connection('wordpress')
                        ->table('term_relationships')
                        ->where('object_id',$id)
                        ->where('term_taxonomy_id',$old)
                        ->update(['term_taxonomy_id'=>$city]);
                }else{
                    DB::connection('wordpress')
                        ->table('term_relationships')
                        ->insert(['term_taxonomy_id'=>$city,'object_id'=>$id]);
                }
            }else{
                DB::connection('wordpress')
                    ->table('term_relationships')
                    ->insert(['term_taxonomy_id'=>$city,'object_id'=>$id]);
            }
            $data=array('status'=>1,'data'=>array(),'message'=>'Success.');
            $code=200;
        }

        elseif ($action=='details'){
            $post = Post::find($id);
            $post->saveMeta([
                'property_size'=>$request->size,
                'property_lot_size'=>$request->lot_size,
                'property_rooms'=>$request->rooms,
                'property_bedrooms'=>$request->bedrooms,
                'property_bathrooms'=>$request->bathrooms,
                'property-garage'=>$request->garages,
                'property-date'=>$request->available_from,
                'stories-number'=>$request->floors_no,
                'rent-time'=>$request->rent_time,
                'area'=>$request->area,
                'broker-orn'=>$request->broker_orn,
                'trakheesi-permit'=>$request->trakheesi_permit,
            ]);
            $post->save();
            $data=array('status'=>1,'data'=>array(),'message'=>'Success.');
            $code=200;
        }

        elseif ($action=='property_features'){
            $ids=array();
            $rows=array();
            $features=$request->features;
            $term_taxonomy=DB::connection('wordpress')->table('term_taxonomy')
                ->select('terms.term_id')
                ->leftJoin('terms','terms.term_id','term_taxonomy.term_id')
                ->where('term_taxonomy.taxonomy','property_features')
                ->get();
            foreach ($term_taxonomy as $item){
                $ids[]=$item->term_id;
            }
            if(!empty($features) and is_array($features)){
                DB::connection('wordpress')
                    ->table('term_relationships')
                    ->whereIn('term_taxonomy_id',$ids)
                    ->where('object_id',$id)
                    ->delete();
                foreach ($features as $feature){
                    $rows[]=array('object_id'=>$id,'term_taxonomy_id'=>$feature,'term_order'=>'0');
                }
                DB::connection('wordpress')->table('term_relationships')->insert($rows);
            }
            $data=array('status'=>1,'data'=>array(),'message'=>'Success.');
            $code=200;
        }

        else{
            $data=array('status'=>0,'data'=>array(),'message'=>'not found');
            $code=401;
        }
        return response()->json($data,$code);
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $post = Post::type('estate_property')
            ->leftJoin('icl_translations','icl_translations.element_id','posts.ID')
            ->find($id);
        if ($post){
            //Define Var
            $category='';
            $type='';
            $city='';
            $embed_virtual_tour='';
            $property_features=array();

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
                $embed_virtual_tour=$embed_virtual_tour[1];
            }

            //Returned Data
            $data=array(
                'language_code'=>$post->language_code,
                'id'=>$post->ID,
                'post_status'=>$post->post_status,
                'title'=>$post->post_title,
                'description'=>$post->post_content,
                'price'=>$post->meta->property_price,
                'category'=>$category,
                'type'=>$type,
                'status'=>$post->meta->property_status,
                'images'=>'https://deal360.ae/wp-content/uploads/'.$post->thumbnail->attachment->meta->_wp_attached_file,
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
                'reference'=>$post->meta->reference,
                'broker_orn'=>$post->meta->{'broker-orn'},
                'property_features'=>$property_features,
            );
            //Print Data
            $response=array('status'=>1,'data'=>$data,'message'=>'success');
            echo json_encode($response);
        }else{
            $response=array('status'=>0,'data'=>array(),'message'=>"This ID doesn't Match Any Records");
            echo json_encode($response);
        }
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
    }

    public function getData(Request $request){
        $types=array(
            'property_features',
            'property_category',
            'property_action_category',
            'property_city',
            'property_county_state',
            'property_status',
        );

        $data=array(
            'property_features'=>array(),
            'property_category'=>array(),
            'property_action_category'=>array(),
            'property_city'=>array(),
            'property_county_state'=>array(),
            'property_status'=>array(),
        );
        $term_taxonomy=DB::connection('wordpress')->table('term_taxonomy')
            ->select('terms.name','terms.term_id','term_taxonomy.taxonomy')
        ->leftJoin('terms','terms.term_id','term_taxonomy.term_id')
        ->whereIn('term_taxonomy.taxonomy',$types)
        ->get();
        foreach ($term_taxonomy as $value){
            $name=$value->name;
            $term_id=$value->term_id;
            $check=preg_match('/\p{Arabic}/u', $name);
            if ($check and $request->lang=='ar'){
                $data[$value->taxonomy][]=array('id'=>$term_id,'name'=>$name);
            }elseif(!$check and $request->lang=='en'){
                $data[$value->taxonomy][]=array('id'=>$term_id,'name'=>$name);
            }
        }
        if (!empty($data)){
            $data=array('status'=>1,'data'=>$data,'message'=>'Success.');
            $code=200;
        }else{
            $data=array('status'=>0,'data'=>$data,'message'=>'not found');
            $code=401;
        }
        return response()->json($data,$code);
    }

    public  function changePropertyStatus (Request $request){
        $validator = Validator::make($request->all(), [
            'id' => 'required',
            'status' => 'required',
        ]);
        if ($validator->fails()) {
            $data=array('status'=>0,'data'=>array(),'message'=>$validator->errors());
            $code=401;
            return response()->json($data,$code);
        }

        $id=$request->id;
        $post = Post::find($id);
        $post->post_status=$request->status;
        $post->save();
        $data=array('status'=>1,'data'=>array(),'message'=>'Success.');
        $code=200;
        return response()->json($data,$code);
    }
}
