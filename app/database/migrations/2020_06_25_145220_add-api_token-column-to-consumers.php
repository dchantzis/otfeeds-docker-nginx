<?php

/** TODO: DELETE */

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddApiTokenColumnToConsumers extends Migration
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
           if (Schema::hasTable('consumers')) {
               Schema::table('consumers', function (Blueprint $table) {
                   if (!Schema::hasColumn('consumers', 'api_token')) {
                       $table->string('api_token', 80)->after('access_key')
                           ->unique()
                           ->nullable()
                           ->default(null);
                   }
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
            if (Schema::hasTable('consumers')) {
                Schema::table('consumers', function (Blueprint $table) {
                    if (Schema::hasColumn('consumers', 'api_token')) {
                        $table->dropColumn('api_token');
                    }
                });
            }
        });
    }
}
