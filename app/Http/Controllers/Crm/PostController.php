<?php

namespace App\Http\Controllers\Crm;

use App\Models\Wordpress\PostWordpress;
use App\Http\Controllers\Controller;

class PostController extends Controller
{
    protected $post;

    public function __construct(PostWordpress $post)
    {
        $this->post = $post;
    }

    public function getAllPosts(){
        $posts = $this->post->published()->where('post_type','estate_property')->orderBy('ID','DESC')->get();
//        foreach ($posts as $post){
//            echo $post->meta->link;
//        }
        echo json_encode($posts);
    }
}
