<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CityDesc extends Migration
{
    public function up()
    {
        Schema::create('city_descs', function (Blueprint $table) {
            $table->id();
            $table->string('title',225)->unique();
            $table->index('city_id')->unsigned();
            $table->foreign('city_id')->references('id')->on('cities');
            $table->index('language_id')->unsigned();
            $table->foreign('language_id')->references('id')->on('languages');
            $table->timestamps();
        });
    }

    public function down()
    {

    }
}
