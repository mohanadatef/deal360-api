<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class RoleDesc extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('role_descs', function (Blueprint $table) {
            $table->id("id",10)->autoIncrement()->primary()->unique()->unsigned();
            $table->index('role_id','role_id',10)->unsigned();
            $table->foreign('role_id')->references('id')->on('roles');
            $table->string('code',225)->unique();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        //
    }
}
