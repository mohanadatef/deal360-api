<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CountryDesc extends Migration
{
    public function up()
    {
        Schema::create('country_descs', function (Blueprint $table) {
            $table->id();
            $table->string('title',225)->unique();
            $table->index('country_id')->unsigned();
            $table->foreign('country_id')->references('id')->on('countries');
            $table->index('language_id')->unsigned();
            $table->foreign('language_id')->references('id')->on('languages');
            $table->timestamps();
        });
    }

    public function down()
    {

    }
}
