<?php

namespace App\Traits;

use App\Http\Resources\Wordpress\AgencyResource;
use App\Models\Wordpress\OptionWordpress;
use App\Models\Wordpress\PostWordpress;
use App\Models\Wordpress\TermWordpress;
use App\Models\Wordpress\UserWordpress;

trait CoreData
{
    protected $post;
    protected $user;
    protected $option;
    protected $term;

    public function __construct(
        PostWordpress $post,
        UserWordpress $user,
        TermWordpress $term,
        OptionWordpress $option)
    {
        $this->post = $post;
        $this->user = $user;
        $this->option = $option;
        $this->term = $term;
    }

    public function AgencyResource($data)
    {
        return ($data instanceof \Illuminate\Pagination\LengthAwarePaginator or $data instanceof \Illuminate\Database\Eloquent\Collection) ? AgencyResource::collection($data) : new AgencyResource($data);
    }

}