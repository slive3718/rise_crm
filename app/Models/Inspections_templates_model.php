<?php

namespace App\Models;

use CodeIgniter\Model;

class Inspections_templates_model extends Model
{
    protected $table = 'inspection_templates';
    protected $primaryKey = 'id';
    protected $allowedFields = ['template_name', 'fields', 'created_by', 'is_deleted'];
}
