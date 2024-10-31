<?php

namespace App\Models;

use CodeIgniter\Model;

class Inspections_response_model extends Model
{
    protected $table = 'inspection_responses';
    protected $primaryKey = 'id';
    protected $allowedFields = ['inspection_id', 'inspection_field_id', 'response', 'created_by', 'created_at'];
}
