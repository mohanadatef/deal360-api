<?php

namespace App\Http\Controllers\Crm;

use Illuminate\Http\Request;
use App\Models\Post as Post;
use App\Http\Controllers\Controller;

class PostController extends Controller
{
    public function getAllPosts(){
        $posts = Post::published()->where('post_type','estate_property')->orderBy('ID','DESC')->get();
//        foreach ($posts as $post){
//            echo $post->meta->link;
//        }
        echo json_encode($posts);
    }
}
