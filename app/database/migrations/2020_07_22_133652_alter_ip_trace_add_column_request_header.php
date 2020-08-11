<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

class AlterIpTraceAddColumnRequestHeader extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        DB::transaction(function () {
            if (Schema::hasTable('feeds_ip_trace')) {
                Schema::table('feeds_ip_trace', function (Blueprint $table) {
                    if (!Schema::hasColumn('feeds_ip_trace', 'request_header')) {
                        $table->text('request_header')->nullable()->default(null)->after('request_parameters');
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
        DB::transaction(function () {
            if (Schema::hasTable('feeds_ip_trace')) {
                Schema::table('feeds_ip_trace', function (Blueprint $table) {
                    if (Schema::hasColumn('feeds_ip_trace', 'request_header')) {
                        $table->dropColumn('request_header');
                    }
                });
            }
        });
    }
}
