<?php
// database/migrations/xxxx_xx_xx_create_vectors_table.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateVectorTable extends Migration
{
    public function up()
    {
        Schema::create('vectors', function (Blueprint $table) {
            $table->id();  // Auto increment primary key
            $table->json('vector');  // Store the raw vector as JSON
            $table->json('normalized_vector');  // Store the normalized vector
            $table->double('magnitude');  // Store the magnitude of the vector
            $table->binary('binary_code');  // Store binary code representation
            $table->timestamps();  // Created at, Updated at
        });
    }

    public function down()
    {
        Schema::dropIfExists('vectors');
    }
}

