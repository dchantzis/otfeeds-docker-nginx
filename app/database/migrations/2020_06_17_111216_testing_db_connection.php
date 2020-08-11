<?php

/** TODO: DELETE */

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class TestingDbConnection extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        return;
        DB::transaction(function () {
           if (!Schema::hasTable('db_test')) {
               Schema::create('db_test', function (Blueprint $table) {

                   $table->bigIncrements('id');
                   $table->timestamps();

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
        return;
        DB::transaction(function () {
            if (Schema::hasTable('db_test')) {
                Schema::dropIfExists('db_test');
            }
        });
    }
}
