<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

class AlterFeedsSystemLogsAddColumnIpTraceId extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        DB::transaction(function () {
            if (Schema::hasTable('feeds_system_logs')) {
                Schema::table('feeds_system_logs', function (Blueprint $table) {
                    if (!Schema::hasColumn('feeds_system_logs', 'ip_trace_id')) {
                        $table->bigInteger('ip_trace_id')->unsigned()->nullable()->default(null)->after('id');
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
            if (Schema::hasTable('feeds_system_logs')) {
                Schema::table('feeds_system_logs', function (Blueprint $table) {
                    if (Schema::hasColumn('feeds_system_logs', 'ip_trace_id')) {
                        $table->dropColumn('ip_trace_id');
                    }
                });
            }
        });
    }
}
