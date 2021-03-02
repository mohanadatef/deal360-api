<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class Area extends Migration
{
    public function up()
    {
        Schema::create('areas', function (Blueprint $table) {
            $table->id();
            $table->text('image')->nullable();
            $table->index('country_id')->unsigned();
            $table->foreign('country_id')->references('id')->on('counties');
            $table->index('city_id')->unsigned();
            $table->foreign('city_id')->references('id')->on('cities');
            $table->integer('status')->default(1);
            $table->timestamps();
        });
    }

    public function down()
    {

    }
}
