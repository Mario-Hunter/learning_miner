<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
class CreateUsersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {   
        Schema::create('users', function (Blueprint $table) {
            $table->increments('id');
            $table->string('first_name');
            $table->string('last_name');
            $table->dateTime('dob')->default('1990-01-01 00:00:00');
            $table->string('gender')->nullable();
            $table->string('email')->unique();
            $table->string('password')->nullable();
            $table->string('avatar')->default('default.jpg');
            $table->float('user_score')->default(0);
            $table->boolean('confirmed')->default(0);
            $table->string('confirmation_code')->nullable();
            $table->rememberToken();
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
        Schema::dropIfExists('users');
    }
}
