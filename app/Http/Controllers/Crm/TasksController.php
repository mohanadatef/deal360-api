<?php

namespace App\Http\Controllers\Crm;

use App\Http\Controllers\Controller;
use App\Models\Tasks;
use Illuminate\Http\Request;
use Validator;
use DB;

class TasksController extends Controller
{

    protected $tasks;

    public function __construct(Tasks $tasks)
    {
        $this->tasks = $tasks;
    }

    //chanage permater id to request & add role name to array mohanad
    public function getPropertiesTasks(Request $request){
        $tasks=$this->tasks->where('wp_post_id',$request->id)
            ->leftJoin('users','users.id','tasks.users_id')
            ->leftJoin('role_desc','users.role_id','role_desc.role_id')
            ->where('role_desc.language_id','1')
            ->select('users.name','users.image','users.role_id','tasks.*','role_desc.name as role_name')
            ->get();
        $data=array('status'=>1,'data'=>$tasks,'message'=>'Success.');
        $code=200;
        return response()->json($data,$code);
    }

    public function assignTasks(Request $request){
        $validator = Validator::make($request->all(), [
            'users_id' => 'required',
            'post_id' => 'required',
//            'expiry_date' => 'required',
        ]);
        if ($validator->fails()) {
            $data=array('status'=>0,'data'=>array(),'message'=>$validator->errors());
            $code=401;
            return response()->json($data,$code);
        }
        $tasksCount=$this->tasks->where('users_id',$request->users_id)->where('wp_post_id',$request->post_id)->count();
        if($tasksCount==0){
            if (empty($request->status_id)){
                $status_id=1;
            }else{
                $status_id=$request->status_id;
            }
            $task_id=$this->tasks->insertGetId([
                'users_id'=>$request->users_id,
                'wp_post_id'=>$request->post_id,
                'current_status_id'=>$status_id,
                'expiry_date'=>$request->expiry_date,
            ]);
            DB::table ('tasks_history')->insert([
                'task_id'=>$task_id,
                'status_id'=>1,
            ]);
            $data=array('status'=>1,'data'=>array(),'message'=>'Success.');
            $code=200;
        }else{
            $data=array('status'=>0,'data'=>array(),'message'=>'you have already assigned to this task.');
            $code=401;
        }
        return response()->json($data,$code);
    }

    public function changeTaskStatus(Request $request){
        $validator = Validator::make($request->all(), [
            'users_id' => 'required|integer',
            'post_id' => 'required|integer',
            'status_id' => 'required|integer|between:1,4',
        ]);
        if ($validator->fails()) {
            $data=array('status'=>0,'data'=>array(),'message'=>$validator->errors());
            $code=401;
            return response()->json($data,$code);
        }
        $this->tasks->where('wp_post_id',$request->post_id)->where('users_id',$request->users_id)
            ->update([
                'current_status_id'=>$request->status_id,
            ]);
//        DB::table ('tasks_history')->insert([
//            'task_id'=>$request->task_id,
//            'status_id'=>$request->status_id,
//        ]);
        $data=array('status'=>1,'data'=>array(),'message'=>'Success.');
        $code=200;
        return response()->json($data,$code);
    }

    public function deleteTask(Request $request){
        $validator = Validator::make($request->all(), [
            'users_id' => 'required|integer',
            'wp_post_id' => 'required|integer',
        ]);
        if ($validator->fails()) {
            $data=array('status'=>0,'data'=>array(),'message'=>$validator->errors());
            $code=401;
            return response()->json($data,$code);
        }
        $tasks = $this->tasks->where('users_id',$request->users_id)->where('wp_post_id',$request->wp_post_id)->delete();
        $data=array('status'=>1,'data'=>array(),'message'=>'Success.');
        $code=200;
        return response()->json($data,$code);
    }

}
