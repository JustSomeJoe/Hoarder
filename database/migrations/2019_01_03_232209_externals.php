<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class Externals extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('externals', function (Blueprint $table) {
            $table->increments('id');
            $table->enum('done', ['New','Done','Working','Failed'])->default('New')->index();
            $table->string('notes')->nullable();
            $table->string('area');
            $table->string('url');
            $table->bigInteger('post_id');
            $table->timestamps();

            $table->unique(array('area', 'url'));

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
