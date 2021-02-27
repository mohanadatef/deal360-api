<?php

namespace App\Http\Controllers\Wordpress;
use Illuminate\Routing\Controller;

class FaqsController extends Controller
{
   public function index()
    {
        $data[]=array('q'=>'What is Lorem Ipsum?','a'=>"is simply dummy text of the printing and typesetting industry. Lorem Ipsum has been the industry's standard dummy text ever since the 1500s");
        $data[]=array('q'=>'Why do we use it?','a'=>"It is a long established fact that a reader will be distracted by the readable content of a page when looking at its layout");
        $data[]=array('q'=>'Where does it come from?','a'=>"Contrary to popular belief, Lorem Ipsum is not simply random text. It has roots in a piece of classical Latin literature from 45 BC, making it over 2000 years old. Richard McClintock, a Latin professor at Hampden-Sydney College in Virginia, looked up one of the more obscure Latin words, consectetur");
        $data[]=array('q'=>'Where can I get some?','a'=>"There are many variations of passages of Lorem Ipsum available");
        return response()->json(['status' => 1, 'data' =>  $data, 'message' => 'successful']);
    }
}
