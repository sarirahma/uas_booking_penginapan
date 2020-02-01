<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class Reservations extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('reservations', function (Blueprint $table) {
            $table->bigIncrements('reservation_id');
            
            $table->integer('hotel_id')->index('hotel_id_foreign');
            $table->string('hotel_name', 128);
            $table->integer('user_id')->index('user_id_foreign');
            $table->integer('room_id')->index('room_id_foreign');
            $table->enum('room_type', array('luxury', 'premium', 'standard'));
            $table->integer('night_stay');

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
        Schema::dropIfExists('reservations');
    }
}
