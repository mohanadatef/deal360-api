<?php

namespace App\Http\Controllers\Wordpress;

use App\Models\Wordpress\PostWordpress;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;

class SaveSearchController extends Controller
{
    protected $post;

    public function __construct(PostWordpress $post)
    {
        $this->post = $post;
    }

    public function delete(Request $request)
    {
        $post = $this->post->find($request->post_id);
        if ($post) {
            foreach ($post->meta as $meta) {
                $meta->delete();
            }
            $post->delete();
            return response()->json(['status' => '1', 'data' => array(), 'message' => 'Successful'], 200);
        }
        return response()->json(['status' => '0', 'data' => array(), 'message' => 'Post ID does not exists or was deleted'], 400);
    }

    public function test_api()
    {
        $handle = curl_init();
        $url = "http://localhost:8080/deal360/wordpres0s/agency/all";
        curl_setopt($handle, CURLOPT_URL, $url);
        curl_setopt($handle, CURLOPT_RETURNTRANSFER, true);
        $output = json_decode(curl_exec($handle));
        curl_close($handle);
        return $output->data;
    }
}
