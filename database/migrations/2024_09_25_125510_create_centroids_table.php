<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCentroidsTable extends Migration
{
    public function up()
    {
        Schema::create('centroids', function (Blueprint $table) {
            $table->id();
            $table->json('vector');  // Storing the centroid vector (could use binary if needed)
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('centroids');
    }
}

