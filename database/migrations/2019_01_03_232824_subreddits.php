<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class Subreddits extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('subreddits', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name')->index();
            $table->timestamp('last_checked')->default('1984-07-23'); // Never gonna give you up
            $table->timestamp('last_post')->nullable();
            $table->integer('last_id')->default(0)->index();
            $table->integer('status')->default(200);
        });

        $default = [
            'gonewild'
        ];

        foreach($default as $temp) {
            DB::table('subreddits')->insert(
                array(
                    'name' => $temp
                )
            );
        }

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
