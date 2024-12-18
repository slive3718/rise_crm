<?php

namespace App\Controllers;

use App\Controllers\Security_Controller;
use App\Models\Inspections_fields_model;
use App\Models\Inspections_model;
use App\Models\Inspections_templates_model;
use App\Models\Inspections_response_model;

class Inspections extends Security_Controller
{

    function __construct()
    {
        parent::__construct();
    }

    /* load invoice list view */

    function index()
    {
        $view_data["client_info"] = $this->Clients_model->get_one($this->login_user->client_id);
        $view_data['client_id'] = $this->login_user->client_id;
        $view_data['page_type'] = "full";
        $view_data['app_lang'] = "english";

         $inspections = $this->Inspections_model->where('is_deleted', 0)->get()->getResultArray();
        foreach ($inspections as &$inspection){
            $inspection['template'] =  $this->Inspections_templates_model->where(['is_deleted'=>0, 'id' => $inspection['template_id']])->first();
        }
        $view_data['inspections'] = $inspections;

        return $this->template->rander("inspections/index", $view_data);
    }

    public function save()
    {
        $responses = $this->request->getPost();
        if ($this->request->getMethod() === 'POST' || $this->request->getMethod() === 'get') {
            // Save the inspection
            $inspectionId = $this->Inspections_model->insert([
                'template_id' => $responses['template_id'],
                'inspection_name' => 'test',
                'inspection_date' => $responses['conducted_date'],
                'inspector_id' => session()->get('user_id'),
                'location' => $responses['conducted_location'],
                'client_id' => $responses['client_id'],
                'inspector_name' => $responses['inspector_name'],
                'payment_method_id' => $responses['payment_method'],
                'created_at' => date('Y-m-d H:i:s'),
            ]);

            if ($inspectionId) { // Ensure inspection was created
                // Save responses for each field
                if ($responses) {
                    $fields = json_decode($responses['fields']);
                    foreach ($fields as $fieldId => $response) {
                        $copiedFieldId = $this->copyFields($fieldId) ?? '';
                        $this->Inspections_response_model->insert([
                            'inspection_id' => $inspectionId,
                            'inspection_field_id' => $copiedFieldId,
                            'response' => $response,
                            'created_at' => date('Y-m-d H:i:s'),
                        ]);
                    }
                    return $this->response->setJSON([
                        'status' => 'success',
                        'message' => 'Inspection Saved!'
                    ]);
                }

            } else {
                return $this->response->setJSON([
                    'status' => 'failed',
                    'message' => 'Failed to create inspection'
                ]);
            }
        } else {
            return $this->response->setJSON([
                'status' => 'error',
                'message' => 'error'
            ]);
        }
    }

    function copyFields($field_id){
        $formFields = (new Inspections_fields_model());
        $formFieldsData = $formFields->where('id', $field_id)->first();

        if (!empty($formFieldsData)) {
            return ($this->Inspections_response_fields_model)->insert($formFieldsData);
        }

        return false;
    }
    public function update_inspection_response()
    {
        if ($this->request->getMethod() === 'POST') {
            $response_id = $this->request->getPost('inspection_response_id');
            if (!empty($response_id)) { // Ensure inspection was created
                // Save responses for each field
                $response = $this->request->getPost('response');
                if ($response) {
                    $this->Inspections_response_model->where('id', $response_id)->set([
                        'response' => $response,
                        'updated_at' => date('Y-m-d H:i:s'),
                    ])->update();
                }
                return $this->response->setJSON([
                    'status' => 'success',
                    'message' => 'Update success!'
                ]);
            } else {
                return $this->response->setJSON([
                    'status' => 'failed',
                    'message' => 'Failed to update response!'
                ]);
            }
        } else {
            return $this->response->setJSON([
                'status' => 'error',
                'message' => 'Invalid request.'
            ]);
        }
    }

    public function update_inspection()
    {
        $post = $this->request->getPost();
        if ($this->request->getMethod() === 'POST') {
            $inspection_id = $this->request->getPost('inspection_id');
            if (!empty($inspection_id)) { // Ensure inspection was created
                // Save responses for each field
                $this->Inspections_model->where('id', $inspection_id)->set([
                    'inspection_date' => $post['date'] ?? '',
                    'client_id' => $post['client_id'] ?? '',
                    'location' => $post['location'] ?? '',
                    'inspector_name' => $post['prepared_by'] ?? '',
                    'payment_method_id' => ($post['payment_method']) ? intVal($post['payment_method']) : 0,
                    'updated_at' => date('Y-m-d H:i:s'),
                ])->update();
                return $this->response->setJSON([
                    'status' => 'success',
                    'message' => 'Update success!'
                ]);
            } else {
                return $this->response->setJSON([
                    'status' => 'failed',
                    'message' => 'Failed to update inspection!'
                ]);
            }
        } else {
            return $this->response->setJSON([
                'status' => 'error',
                'message' => 'Invalid request.'
            ]);
        }
    }

    public function list() {
        if($this->request->getPost('inspection_id')) {
            $inspection = $this->Inspections_model->where('is_deleted', 0)->find($this->request->getPost('inspection_id'));
            $responseFormFieldModel = $this->Inspections_response_fields_model;

            if(!empty($inspection)) {
                // Fetch all fields, ordered by section and sort_order
                $fields = $responseFormFieldModel->getFormFields($inspection['template_id'])->findAll();
                $responses = ($this->Inspections_response_model)->where('inspection_id', $this->request->getPost('inspection_id'))->findAll();
                $sections = [];
                foreach ($fields as $field) {
                    if(in_array($field['id'], array_column($responses, 'inspection_field_id'))) {
                        // Initialize the field with a default value (empty string or null)
                        $field['value'] = '';
                        $field['response_id'] = '';
                        foreach ($responses as $response) {
                            if ($response['inspection_field_id'] == $field['id']) {
                                $field['value'] = $response['response'];
                                $field['response_id'] = $response['id'];
                                break;
                            }
                        }
                        $sections[$field['section_name']][] = $field;
                    }
                }

                $payment_method = $this->Payment_methods_model->get()->getResult();

                $view_data['sections'] = $sections;
                $view_data['fieldsData'] = $fields;
                $view_data['inspection'] = $inspection;
                $view_data['payment_method'] = $payment_method;
                $view_data['clients'] = $this->Clients_model->get()->getResult();
            }

        }else{
            return $this->response->setJSON([
                'status' => 'error',
                'message' => 'Missing Inspection ID'
            ]);
        }
        return view('inspections/inspection_form_fields', $view_data);
    }

    public function delete()
    {
        $inspection_id = $this->request->getPost('inspection_id');

        if ($inspection_id) {
            try {
                $this->Inspections_model->where('id', $inspection_id)->set('is_deleted', 1)->update();
            } catch (\Exception $e) {
                return $this->response->setJSON([
                    'status' => 'error',
                    'message' => 'Failed to delete inspection. Please try again later.'
                ]);
            }
            return $this->response->setJSON([
                'status' => 'success',
                'message' => 'Inspection deleted successfully.'
            ]);
        }
        return $this->response->setJSON([
            'status' => 'error',
            'message' => 'Inspection ID is required.'
        ]);
    }

    public function view_report($inspection_id) {
        $inspection = $this->Inspections_model->where('is_deleted', 0)->find($inspection_id);
        $formFieldModel = $this->Inspections_response_fields_model;
        $view_data["LOGO_URL"] = get_logo_url();

        if (!empty($inspection)) {
            // Fetch all fields, ordered by section and sort_order
            $fields = $formFieldModel->getFormFields($inspection['template_id'])->findAll();
            $responses = ($this->Inspections_response_model)->where('inspection_id', $inspection_id)->findAll();
            $sections = [];

            $flaggedCounts = [];
            $totalFlagged = 0;  // Initialize a variable for the overall total of flagged counts

            $fieldWithValue = 0;
            $fieldCount = 0;

            foreach ($fields as $field) {
                if(in_array($field['id'], array_column($responses, 'inspection_field_id'))) {
                    $fieldCount++;
                    // Initialize the field with default values
                    $field['value'] = '';
                    $field['response_id'] = '';
                    $field['flagged'] = false; // Default flagged status to false

                    // Search for a matching response for this field
                    foreach ($responses as $response) {
                        if ($response['inspection_field_id'] == $field['id']) {
                            $field['value'] = $response['response'];
                            $field['response_id'] = $response['id'];

                            // Check if the field has options and if the selected option is flagged
                            if (isset($field['field_options'])) {
                                $field_options = json_decode($field['field_options']);
                                foreach ($field_options as $option) {
                                    // Only set flagged if the selected option is flagged
                                    if ($field['value'] == $option->label && !empty($option->flagged)) {
                                        $field['flagged'] = true; // Mark the field as flagged if the selected option is flagged
                                        break;
                                    }

                                    if(!empty($field['value'])){
                                        $fieldWithValue++;
                                    }
                                }
                            }
                            break;
                        }
                    }

                    // Group fields by their section
                    $sections[$field['section_name']][] = $field;

                    // Increment the flagged count for the section if the field is flagged
                    if ($field['flagged']) {
                        if (!isset($flaggedCounts[$field['section_name']])) {
                            $flaggedCounts[$field['section_name']] = 0;
                        }
                        $flaggedCounts[$field['section_name']]++; // Increment flagged count for this section
                        $totalFlagged++; // Increment total flagged count
                    }
                }
            }

            $inspection['field_count'] = $fieldCount;
            $inspection['value_count'] = $fieldWithValue;
            $inspection['populated_percentage'] = round(($fieldWithValue/$fieldCount) * 100, 1);

            $inspection['paid_by'] = ($result = $this->Payment_methods_model->where('id', $inspection['payment_method_id'])->get()->getResult()) ? $result[0]:'';

            $inspection_client = $this->Clients_model->get_one($inspection['client_id']);
            $view_data['sections'] = $sections;
            $view_data['flaggedCounts'] = $flaggedCounts;
            $view_data['totalFlagged'] = $totalFlagged;
            $view_data['inspection'] = $inspection;
            $view_data['inspection_client'] = $inspection_client;
            $view_data['fieldsData'] = $fields;
            $view_data['inspection_id'] = $inspection_id;
        }
        return $this->template->rander('inspections/view_report', $view_data);
    }


    function prepare_inspection_pdf($inspection_id)
    {

        $inspection = $this->Inspections_model->where('is_deleted', 0)->find($inspection_id);
        $inspection_template = ($this->Inspections_templates_model)->find($inspection['template_id']);
        $formFieldModel = $this->Inspections_response_fields_model;
        $view_data["LOGO_URL"] = get_logo_url();



        if (!empty($inspection)) {
            // Fetch all fields, ordered by section and sort_order
            $fields = $formFieldModel->getFormFields($inspection['template_id'])->findAll();

            $responses = (new Inspections_response_model())->where('inspection_id', $inspection_id)->findAll();
            $sections = [];

            $flaggedCounts = [];
            $totalFlagged = 0;  // Initialize a variable for the overall total of flagged counts


            foreach ($fields as $field) {
                if(in_array($field['id'], array_column($responses, 'inspection_field_id'))) {
                    // Initialize the field with default values
                    $field['value'] = '';
                    $field['response_id'] = '';
                    $field['flagged'] = false; // Default flagged status to false

                    // Search for a matching response for this field
                    foreach ($responses as $response) {
                        if ($response['inspection_field_id'] == $field['id']) {
                            $field['value'] = $response['response'];
                            $field['response_id'] = $response['id'];

                            // Check if the field has options and if the selected option is flagged
                            if (isset($field['field_options'])) {
                                $field_options = json_decode($field['field_options']);
                                foreach ($field_options as $option) {
                                    // Only set flagged if the selected option is flagged
                                    if ($field['value'] == $option->label && !empty($option->flagged)) {
                                        $field['flagged'] = true; // Mark the field as flagged if the selected option is flagged
                                        break;
                                    }
                                }
                            }
                            break;
                        }
                    }

                    // Group fields by their section
                    $sections[$field['section_name']][] = $field;

                    // Increment the flagged count for the section if the field is flagged
                    if ($field['flagged']) {
                        if (!isset($flaggedCounts[$field['section_name']])) {
                            $flaggedCounts[$field['section_name']] = 0;
                        }
                        $flaggedCounts[$field['section_name']]++; // Increment flagged count for this section
                        $totalFlagged++; // Increment total flagged count
                    }
                }
            }

            $inspection['paid_by'] = ($result = $this->Payment_methods_model->where('id', $inspection['payment_method_id'])->get()->getResult()) ? $result[0]:'';


            $inspection_client = $this->Clients_model->get_one($inspection['client_id']);
            $view_data['sections'] = $sections;
            $view_data['flaggedCounts'] = $flaggedCounts;
            $view_data['totalFlagged'] = $totalFlagged;
            $view_data['inspection'] = $inspection;
            $view_data['inspection_client'] = $inspection_client;
            $view_data['fieldsData'] = $fields;
            $view_data['inspection_id'] = $inspection_id;
            $view_data['template'] = $inspection_template;
        }



        prepare_inspection_pdf($view_data);
    }

}