<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddCentroidIdToImageVectorTable extends Migration
{
    public function up()
    {
        Schema::table('grant_vector', function (Blueprint $table) {
            $table->foreignId('centroid_id')->nullable()->constrained('centroids');
        });
    }

    public function down()
    {
        Schema::table('grant_vector', function (Blueprint $table) {
            $table->dropForeign(['centroid_id']);
            $table->dropColumn('centroid_id');
        });
    }
}
