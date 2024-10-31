<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Grant extends Model
{
    use HasFactory;

    protected $table = 'grants';

    // Define which attributes can be mass-assigned
    protected $fillable = [
        'opportunity_title',
        'opportunity_id',
        'opportunity_number',
        'opportunity_category',
        'opportunity_category_explanation',
        'funding_instrument_type',
        'category_of_funding_activity',
        'cfda_number',
        'eligible_applicants',
        'additional_information_on_eligibility',
        'agency_code',
        'agency_name',
        'post_date',
        'close_date',
        'last_updated_or_created_date',
        'award_ceiling',
        'award_floor',
        'estimated_total_program_funding',
        'expected_number_of_awards',
        'description',
        'cost_sharing_requirement',
        'additional_information_url',
        'grantor_contact_email',
        'grantor_contact_email_description',
        'grantor_contact_text',
        'version',
        'created_at',
        'updated_at'
    ];

    // Cast attributes to native types
    protected $casts = [
        'post_date' => 'date',
        'close_date' => 'date',
        'last_updated_or_created_date' => 'date',
        'award_ceiling' => 'float',
        'award_floor' => 'float',
        'estimated_total_program_funding' => 'float',
        'expected_number_of_awards' => 'integer',
        'cost_sharing_requirement' => 'boolean',
    ];

    //get search fields
    public static function getSearchFields(): array
    {
        //get all fillable
        $fillable = (new self())->getFillable();
        //remove created_at and updated_at
        $fillable = array_diff($fillable, ['created_at', 'updated_at']);
        //sort alphabetically
        sort($fillable);
        return $fillable;
        
    }
}
