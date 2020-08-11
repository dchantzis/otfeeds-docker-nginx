<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use App\Models\IPTrace;

class CreateIpTraceTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        DB::transaction(function () {
           if (!Schema::hasTable('feeds_ip_trace')) {
               Schema::create('feeds_ip_trace', function (Blueprint $table) {
                  $table->bigIncrements('id');
                  $table->string('ip_address');
                  $table->integer('consumer_id')->unsigned()->nullable()->default(null);
                  $table->enum('request_method', [
                      IPTrace::REQUEST_METHOD_POST,
                      IPTrace::REQUEST_METHOD_GET,
                      IPTrace::REQUEST_METHOD_HEAD,
                      IPTrace::REQUEST_METHOD_PUT,
                      IPTrace::REQUEST_METHOD_DELETE,
                      IPTrace::REQUEST_METHOD_CONNECT,
                      IPTrace::REQUEST_METHOD_OPTIONS,
                      IPTrace::REQUEST_METHOD_TRACE,
                      IPTrace::REQUEST_METHOD_PATCH,
                  ]);
                  $table->string('route');
                  $table->text('request_parameters')->nullable()->default(null);
                  $table->string('host');

                   $table->foreign('consumer_id')->references('id')->on('consumers');

                  $table->timestamps();
               });
           }
        });
    }

    /**
     * Reverse the migrations.
     *
     *
     * @return void
     */
    public function down()
    {
        DB::transaction(function () {
            Schema::dropIfExists('feeds_ip_trace');
        });
    }
}
