<?php

namespace App\Models;

use CodeIgniter\Model;

class Inspections_response_fields_model extends Model
{
    protected $table = 'inspection_responses_fields';

    // Fetch fields ordered by section and sort order

    protected $allowedFields = [
        'template_id',
        'section_name',
        'field_label',
        'field_name',
        'field_type',
        'field_options',
        'is_required',
        'sort_order',
    ];

    public function getFormFields($template_id)
    {
        return $this->orderBy('section_name', 'ASC')
            ->orderBy('sort_order', 'ASC')
            ->where('template_id', $template_id)
            ;
    }


}
