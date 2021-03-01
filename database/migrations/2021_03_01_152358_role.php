<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class Role extends Migration
{
    public function up()
    {
        Schema::create('roles', function (Blueprint $table) {
            $table->id()->autoIncrement()->primary()->unique();
            $table->string('code',225)->unique();
            $table->timestamps();
        });
    }

    public function down()
    {

    }
}
