<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateGrantsTable extends Migration
{
    public function up()
    {
        Schema::create('grants', function (Blueprint $table) {
            $table->id();
            $table->string('opportunity_title', 255);
            $table->string('opportunity_id', 20);
            $table->string('opportunity_number', 40);
            $table->char('opportunity_category', 1); // D, M, C, E, O
            $table->text('opportunity_category_explanation')->nullable(); // Changed to TEXT
            $table->string('funding_instrument_type', 2); // G, CA, O, PC
            $table->string('category_of_funding_activity', 3);
            $table->text('category_explanation')->nullable(); // Changed to TEXT
            $table->string('cfda_number', 6)->nullable();
            $table->string('eligible_applicants', 2);
            $table->text('additional_information_on_eligibility')->nullable(); // Changed to TEXT
            $table->string('agency_code', 255)->nullable();
            $table->string('agency_name', 255)->nullable();
            $table->date('post_date');
            $table->date('close_date')->nullable();
            $table->text('close_date_explanation')->nullable(); // Changed to TEXT
            $table->bigInteger('expected_number_of_awards')->nullable();
            $table->decimal('estimated_total_program_funding', 15, 2)->nullable();
            $table->decimal('award_ceiling', 15, 2)->nullable();
            $table->decimal('award_floor', 15, 2)->nullable();
            $table->date('last_updated_or_created_date');
            $table->date('estimated_synopsis_post_date')->nullable();
            $table->string('fiscal_year', 4)->nullable();
            $table->date('estimated_synopsis_close_date')->nullable();
            $table->string('estimated_synopsis_close_date_explanation', 255)->nullable();
            $table->date('estimated_award_date')->nullable();
            $table->date('estimated_project_start_date')->nullable();
            $table->date('archive_date')->nullable();
            $table->text('description')->nullable(); // Changed to TEXT
            $table->boolean('cost_sharing_requirement')->nullable();
            $table->string('additional_information_url', 250)->nullable();
            $table->text('grantor_contact_text')->nullable(); // Changed to TEXT
            $table->string('grantor_contact_email_description', 102)->nullable();
            $table->string('grantor_contact_email', 130)->nullable();
            $table->text('grantor_contact_name')->nullable(); // Changed to TEXT
            $table->string('grantor_contact_phone_number', 100)->nullable();
            $table->string('version', 20);
            $table->timestamps();  // Laravel's created_at and updated_at
        });
    }

    public function down()
    {
        Schema::dropIfExists('grants');
    }
}
