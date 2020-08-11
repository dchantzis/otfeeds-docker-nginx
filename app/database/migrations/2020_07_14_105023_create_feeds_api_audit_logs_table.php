<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use App\Models\ApiAuditLog;

class CreateFeedsApiAuditLogsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        DB::transaction(function () {
           if (!Schema::hasTable('feeds_api_audit_logs')) {
               Schema::create('feeds_api_audit_logs', function (Blueprint $table) {
                   $table->bigIncrements('id');

                   $table->bigInteger('ip_trace_id')->unsigned()->nullable()->default(null);
                   $table->integer('consumer_id')->unsigned()->nullable()->default(null);

                   $table->mediumText('content')->nullable()->default(null);
                   $table->enum('type', [
                       ApiAuditLog::REQUEST,
                       ApiAuditLog::RESPONSE,
                   ])->nullable()->default(null);
                   $table->text('meta')->nullable()->default(null);
                   $table->timestamps();

                   $table->foreign('consumer_id')->references('id')->on('consumers');
                   $table->foreign('ip_trace_id')->references('id')->on('feeds_ip_trace');
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
            Schema::dropIfExists('feeds_api_audit_logs');
        });
    }
}
