<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

class CreateFeedsSystemLogsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        DB::transaction(function () {
            if (!Schema::hasTable('feeds_system_logs')) {
                Schema::create('feeds_system_logs', function (Blueprint $table) {
                    $table->bigIncrements('id');
                    $table->text('message');
                    $table->string('level');
                    $table->string('level_name');
                    $table->mediumText('context');
                    $table->string('channel');
                    $table->timestamps();

                    $table->index('created_at');
                });
            }
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        DB::transaction(function () {
            Schema::dropIfExists('feeds_system_logs');
        });
    }
}
