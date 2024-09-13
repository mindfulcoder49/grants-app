<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateGrantVectorTable extends Migration
{
    public function up()
    {
        Schema::create('grant_vector', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('grant_id');
            $table->unsignedBigInteger('vector_id');
            $table->string('opportunity_id'); // Add opportunity_id field
            $table->timestamps();

            // Indexes for faster lookups
            $table->index('grant_id');
            $table->index('vector_id');
            $table->index('opportunity_id');
        });
    }

    public function down()
    {
        Schema::dropIfExists('grant_vector');
    }
}
