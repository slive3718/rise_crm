<?php
namespace App\Models;

use CodeIgniter\Model;

class Inspections_model extends Model
{
    protected $table = 'inspections';
    protected $primaryKey = 'id';
    protected $allowedFields = ['inspection_name', 'template_id', 'inspector_id', 'payment_method_id', 'inspection_date', 'inspector_name', 'location', 'client_id', 'status', 'result', 'created_at', 'is_deleted'];
}
