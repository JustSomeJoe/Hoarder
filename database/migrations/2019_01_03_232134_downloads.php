<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class Downloads extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('Downloads', function (Blueprint $table) {
            $table->increments('id');
            $table->smallInteger('done')->default(0)->index();
            $table->string('lock_hash', 8)->nullable()->index();
            $table->string('name')->index();
            $table->string('domain')->nullable()->index();
            $table->string('album')->nullable()->index();
            $table->integer('subreddit_id')->nullable()->index();
            $table->integer('author_id')->nullable()->index();
            $table->string('post')->nullable()->index();
            $table->string('type')->nullable();
            $table->integer('size')->nullable();
            $table->text('url')->nullable();
            $table->datetime('created_utc')->nullable();
            $table->unique('name');
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
