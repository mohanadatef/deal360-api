<?php


namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Corcel\Model\Post as Corcel;

class Taxonomy extends Corcel
{
    protected $connection = 'wordpress';

    public function customMethod()
    {
        //
    }

    use HasFactory;
}
