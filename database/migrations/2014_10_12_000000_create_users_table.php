<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

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
            $table->id();
            $table->string('staff_id')->unique();
            $table->string('email')->unique();
            $table->string('first_name');
            $table->string('last_name')->nullable();
            $table->string('phone')->unique();
            $table->timestamp('email_verified_at')->nullable();
            $table->timestamp('phone_verified_at')->nullable();
            $table->string('password');
            $table->smallInteger('role_id')->default(3);
            $table->string('api_token')->nullable();
            $table->tinyInteger('status')->default(1)->comment('0=deactive, 1=active, 2=deleted');
            $table->string('image')->nullable();       
            $table->string('profile_image')->nullable();         
            $table->smallInteger('notification_enabled')->default(1);
            $table->rememberToken();
            $table->timestamps();

            // $table->string('cover_image')->nullable();
            // $table->string('logo')->nullable();
            // $table->string('latitude')->nullable();
            // $table->string('longitude')->nullable();
            // $table->integer('age')->nullable();
            // $table->string('gst_no')->nullable();
            // $table->string('whatsapp')->nullable()->unique();
            // $table->string('address')->nullable();
            // $table->string('state')->nullable();
            // $table->string('city')->nullable();
            // $table->string('pincode')->nullable();
            // $table->string('aadhar_number')->nullable()->unique();
            // $table->string('pan_number')->nullable()->unique();
            // $table->string('aadhar_image')->nullable();
            // $table->string('pan_image')->nullable();
          
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
