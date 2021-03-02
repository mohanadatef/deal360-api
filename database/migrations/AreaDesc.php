<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AreaDesc extends Migration
{
    public function up()
    {
        Schema::create('area_descs', function (Blueprint $table) {
            $table->id();
            $table->string('title',225)->unique();
            $table->index('area_id')->unsigned();
            $table->foreign('area_id')->references('id')->on('areas');
            $table->index('language_id')->unsigned();
            $table->foreign('language_id')->references('id')->on('languages');
            $table->timestamps();
        });
    }

    public function down()
    {

    }
}
