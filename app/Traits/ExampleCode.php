<?php
/**
 * Created by PhpStorm.
 * User: pc5
 * Date: 10/03/2021
 * Time: 04:17 م
 */

namespace App\Traits;


use App\Http\Resources\Wordpress\AgencyResource;
use App\Models\Wordpress\OptionWordpress;
use App\Models\Wordpress\PostWordpress;
use App\Models\Wordpress\UserWordpress;

trait ExampleCode
{
    protected $post, $user, $option;

    public function __construct(PostWordpress $post, UserWordpress $user, OptionWordpress $option)
    {
        $this->post = $post;
        $this->user = $user;
        $this->option = $option;
    }

    public function AgencyResourceCollection($agency)
    {
       return AgencyResource::collection($agency);
    }

    public function AgencyResource($agency)
    {
        return new AgencyResource($agency);
    }
}