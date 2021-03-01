<?php

namespace App\Http\Controllers\Crm;

use App\Http\Controllers\Controller;
use App\Models\Tasks;
use Illuminate\Http\Request;
use App\Models\User as User;
use Illuminate\Support\Facades\Auth;

class TeamsController extends Controller
{
    public function getAllTeams(Request $request){
        $allData=array();
        $user = Auth::user();
        if (isset($user->id) and $user->role_id==1){
            $teams=User::Where('users.role_id',$request->role_id)
                ->leftjoin('role_desc','role_desc.role_id','users.role_id')
                ->where('role_desc.language_id',1)
                ->where('users.id','!=',$user->id)
                ->select('users.*','role_desc.name as role_name')
                ->get();
            foreach ($teams as $team){
                $team->prop_count=Tasks::where('users_id',$team->id)->count();
                $allData[]=$team;
            }
            $response=array('status'=>1,'data'=>$teams,'message'=>"Success");
        }elseif(isset($user->id) and $user->role_id==2){
            if ($request->role_id==5 or $request->role_id==4){
                $teams=User::Where('users.role_id',$request->role_id)
                    ->leftjoin('role_desc','role_desc.role_id','users.role_id')
                    ->where('role_desc.language_id',1)
                    ->where('users.id','!=',$user->id)
                    ->select('users.*','role_desc.name as role_name')
                    ->get();
                foreach ($teams as $team){
                    $team->prop_count=Tasks::where('users_id',$team->id)->count();
                    $allData[]=$team;
                }
                $response=array('status'=>1,'data'=>$allData,'message'=>"Success");
            }else{
                $response=array('status'=>0,'data'=>array(),'message'=>"You Don't Have Permission");
            }
        }else{
            $response=array('status'=>0,'data'=>array(),'message'=>"You Don't Have Permission");
        }
        echo json_encode($response);
    }
}
