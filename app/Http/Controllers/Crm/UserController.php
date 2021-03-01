<?php

namespace App\Http\Controllers\Crm;

use App\Http\Controllers\Controller;
use App\Models\Tasks;
use App\Models\Wordpress\PostWordpress;
use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Validator;
use DB;
class UserController extends Controller
{
    public $successStatus = 200;
    /**
     * login api
     *
     * @return \Illuminate\Http\Response
     */
    protected $tasks,$user,$post;

    public function __construct(Tasks $tasks,User $user,PostWordpress $post)
    {
        $this->tasks = $tasks;
        $this->user = $user;
        $this->post = $post;
    }

    public function login(){
        if(Auth::attempt(['username' => request('username'), 'password' => request('password')])){
            $user = Auth::user();
            $token=  $user->createToken('CRM-Crm')-> accessToken;
            $user->token=$token;
            $user->role_name=DB::table('role_desc')->where('language_id',1)->select('name')->where('role_id',$user->role_id)->first()->name;
            if($user->role_id==1){
                $inProgress=$this->tasks->where('current_status_id',2)->count();
                $All_inProgress=$this->tasks->where('current_status_id',2)->distinct()->count('wp_post_id');
                $expired=$this->tasks->where('current_status_id',3)->distinct()->count('wp_post_id');
                $pending=$this->post->type('estate_property')->where('post_status','pending')->count();

                $user->inProgress=$inProgress;
                $user->expired=$expired;
                $user->pending=($pending-$expired-$All_inProgress);
                $user->completed=$this->post->type('estate_property')->published()->count();
            }else{
                $user->pending=$this->tasks->where('current_status_id',1)->where('users_id',$user->id)->count();
                $user->inProgress=$this->tasks->where('current_status_id',2)->where('users_id',$user->id)->count();
                $user->expired=$this->tasks->where('current_status_id',3)->where('users_id',$user->id)->count();
                $user->completed=$this->tasks->where('current_status_id',4)->where('users_id',$user->id)->count();
            }
            $data=array('status'=>1,'data'=>$user,'message'=>'Successful');
            $code=200;
        }
        else{
            $data=array('status'=>0,'data'=>array(),'message'=>'invalid UserName or password');
            $code=401;
        }
        return response()->json($data,$code);
    }
    /**
     * Register api
     *
     * @return \Illuminate\Http\Response
     */
    public function register(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'username' => 'required|unique:users',
            'email' => 'required|email|unique:users',
            'password' => 'required',
//            'c_password' => 'required|same:password',
            'role_id'=>'required|integer|between:2,6',
        ]);
        if ($validator->fails()) {
            $data=array('status'=>0,'data'=>array(),'message'=>$validator->errors());
            $code=401;
            return response()->json($data,$code);
        }
        $input = $request->all();
        $input['password'] = bcrypt($input['password']);
        $input['name']=$input['username'];
        $user = $this->user->create($input);
        $token =  $user->createToken('CRM-Crm')-> accessToken;
        $user->token=$token;
        $user->role_name=DB::table('role_desc')->where('language_id',1)->select('name')->where('role_id',$user->role_id)->first()->name;
        $data=array('status'=>1,'data'=>$user,'message'=>'Successful');
        $code=200;
        return response()->json($data,$code);
    }
    /**
     * details api
     *
     * @return \Illuminate\Http\Response
     */
    public function getUser(Request $request)
    {
        if (isset($request->user_id) and !empty($request->user_id)){
            $userId=$request->user_id;
            $user = $this->user->find($userId);
        }else{
            $user = Auth::user();
            $userId=$user->id;
        }
        $user->role_name=DB::table('role_desc')->where('language_id',1)->select('name')->where('role_id',$user->role_id)->first()->name;
        if($user->role_id==1){
            $inProgress=$this->tasks->where('current_status_id',2)->count();
            $All_inProgress=$this->tasks->where('current_status_id',2)->distinct()->count('wp_post_id');
            $expired=$this->tasks->where('current_status_id',3)->distinct()->count('wp_post_id');
            $pending=$this->post->type('estate_property')->where('post_status','pending')->count();

            $user->inProgress=$inProgress;
            $user->expired=$expired;
            $user->pending=($pending-$expired-$All_inProgress);
            $user->completed=$this->post->type('estate_property')->published()->count();
        }else{
            $user->pending=$this->tasks->where('current_status_id',1)->where('users_id',$userId)->count();
            $user->inProgress=$this->tasks->where('current_status_id',2)->where('users_id',$userId)->count();
            $user->expired=$this->tasks->where('current_status_id',3)->where('users_id',$userId)->count();
            $user->completed=$this->tasks->where('current_status_id',4)->where('users_id',$userId)->count();
        }

        $data=array('status'=>1,'data'=>$user,'message'=>'Successful');
        $code=200;
        return response()->json($data,$code);
    }

    //Update User Data
    public function updateUserData(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'username' => 'unique:users',
            'email' => 'email|unique:users',
            'fb' => 'url',
            'tw' => 'url',
            'insta' => 'url',
            'dob' => 'date',
        ]);
        if ($validator->fails()) {
            $data=array('status'=>0,'data'=>array(),'message'=>$validator->errors());
            $code=401;
            return response()->json($data,$code);
        }
        // Get current user
        if (isset($request->user_id) and !empty($request->user_id)){
            $userId=$request->user_id;
        }else{
            $userId = Auth::id();
        }
        $user = $this->user->findOrFail($userId);

        $data = $request->all();
        $user->update($data);
        //mohanad role_name
        $user->role_name=DB::table('role_desc')->where('language_id',1)->select('name')->first()->name;
        if($user->role_id==1){
            $inProgress=$this->tasks->where('current_status_id',2)->count();
            $All_inProgress=$this->tasks->where('current_status_id',2)->distinct()->count('wp_post_id');
            $expired=$this->tasks->where('current_status_id',3)->distinct()->count('wp_post_id');
            $pending=$this->post->type('estate_property')->where('post_status','pending')->count();

            $user->inProgress=$inProgress;
            $user->expired=$expired;
            $user->pending=($pending-$expired-$All_inProgress);
            $user->completed=$this->post->type('estate_property')->published()->count();
        }else{
            $user->pending=$this->tasks->where('current_status_id',1)->where('users_id',$user->id)->count();
            $user->inProgress=$this->tasks->where('current_status_id',2)->where('users_id',$user->id)->count();
            $user->expired=$this->tasks->where('current_status_id',3)->where('users_id',$user->id)->count();
            $user->completed=$this->tasks->where('current_status_id',4)->where('users_id',$user->id)->count();
        }
        // Redirect to route
        $data=array('status'=>1,'data'=>$user,'message'=>'Successful');
        $code=200;
        return response()->json($data,$code);
    }

    //Update User Password
    public function updateUserPassword(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'password' => 'required',
            'c_password' => 'same:password',
        ]);
        if ($validator->fails()) {
            $data=array('status'=>0,'data'=>array(),'message'=>$validator->errors());
            $code=401;
            return response()->json($data,$code);
        }
        // Get current user
        if (isset($request->user_id) and !empty($request->user_id)){
            $userId=$request->user_id;
        }else{
            $userId = Auth::id();
        }
        $user = $this->user->findOrFail($userId);

        $data = $request->all();
        $data['password'] = bcrypt($data['password']);
        $user->update($data);

        // Redirect to route
        $data=array('status'=>1,'data'=>$user,'message'=>'Successful');
        $code=200;
        return response()->json($data,$code);
    }

    //upload user image
    public function updateUserImage(Request $request){
        if (!empty($request->image)){
            $data = $request->all();
            $png_url = "user-".time().'-'.uniqid().".jpg";
            $path = "images/users/profile/" . $png_url;
            $img = $data['image'];
            $img = substr($img, strpos($img, ",")+1);
            $data = base64_decode($img);
            $success = file_put_contents($path, $data);
            if ($success){
                if (isset($request->user_id) and !empty($request->user_id)){
                    $userId=$request->user_id;
                }else{
                    $userId = Auth::id();
                }
                $user = $this->user->findOrFail($userId);
                $data = $request->all();
                $data['image'] = 'images/users/profile/'.$png_url;
                $user->update($data);
                $data=array('status'=>1,'data'=>$user,'message'=>'Successful');
                $code=200;
            }else{
                $data=array('status'=>0,'data'=>array(),'message'=>'Unable to save the file.');
                $code=401;
            }
        }else{
            $data=array('status'=>0,'data'=>array(),'message'=>'you must upload file.');
            $code=401;
        }

        return response()->json($data,$code);
    }
}
