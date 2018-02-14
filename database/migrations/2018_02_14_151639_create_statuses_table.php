<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateStatusesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('statuses', function (Blueprint $table) {
            $table->increments('id');
            //(content)储存微博的内容。
            $table->text('content');
             /** (integer)储存微博发布者的个人 id
               我们会借助 user_id 来查找指定用户发布过的所有微博，因此为了提高查询效率，这里我们需要为该字段加上索引
               */ 
            $table->integer('user_id')->index();
            /**
            (created_at)微博的创建时间添加索引
            */
            $table->index(['created_at']);
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
        Schema::dropIfExists('statuses');
    }
}
