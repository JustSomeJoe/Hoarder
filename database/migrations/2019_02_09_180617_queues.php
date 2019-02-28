<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class Queues extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('Queues', function(Blueprint $table) {
            $table->increments('id');
            $table->string('name')->index();
            $table->integer('order')->default(0);
        });

        DB::table('Queues')->insert(
            array(
                'name' => 'gonewild'
            )
        );
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
