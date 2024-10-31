<?php


namespace App\Controllers;

use App\Controllers\Security_Controller;
use App\Models\Inspections_fields_model;
use App\Models\Inspections_model;
use App\Models\Inspections_templates_model;


class Inspections_templates extends Security_Controller
{

    function __construct()
    {
        parent::__construct();
    }

    /* load invoice list view */

    function index():string
    {
        $view_data["client_info"] = $this->Clients_model->get_one($this->login_user->client_id);

        $view_data['client_id'] = $this->login_user->client_id;
        $view_data['page_type'] = "full";
        $view_data['app_lang'] = "english";

        $templateModel = $this->Inspections_templates_model;
        $view_data['templates'] = $templateModel->where('is_deleted', 0)->findAll();

        return $this->template->rander("inspections/inspection_template", $view_data);
    }

    public function create() {
        try {
            // Load the models
            $templateModel = new Inspections_templates_model();
            $inspectionFieldsModel = new Inspections_fields_model();

            $templateModel->insert(
                [
                    'template_name' => $this->request->getPost('template_name'),
                    'created_by' => session()->get('user_id')
                ]
            );
            $template_id = $templateModel->getInsertID();

            // Get sections and fields data
            $sections = $this->request->getPost('sections');

            // Prepare the data to insert into the `sections` table
            $insertData = [];
            $sectionSortOrder = 1; // To manage sort order for sections

            foreach ($sections as $section) {
                // Insert the section first (without the fields)
                $insertData[] = [
                    'template_id' => $template_id,
                    'section_name' => $section['name'], // Section name
                    'field_label' => null, // No field label for the section row itself
                    'field_name' => null,  // No field name for the section row itself
                    'field_type' => null,  // No field type for the section row itself
                    'field_options' => null, // No field options for the section row itself
                    'is_required' => 0, // Default value for section (no field)
                    'sort_order' => $sectionSortOrder // Sort order for the section
                ];

                $fieldSortOrder = 1; // Reset field sort order within each section

                // Loop through fields within the section and insert them
                foreach ($section['fields'] as $field) {
//                print_r($field);exit;
                    // Initialize the field options and color
                    $fieldOptions = null;
                    $fieldOptionsArray = []; // For select and radio options

                    // If the field type is 'select' or 'radio', process the options
                    if (in_array($field['type'], ['select', 'radio'])) {
                        // Handle field options array
                        if (isset($field['options']) && is_array($field['options'])) {
                            foreach ($field['options'] as $optionIndex => $option) {
                                $fieldOptionsArray[] = [
                                    'label' => $option, // Each option label
                                    'color' => $field['colors'][$optionIndex] ?? '#000000' // Use color or default black
                                ];
                            }
                        }
                        // Convert the array of options to a JSON string
                        $fieldOptions = json_encode($fieldOptionsArray, JSON_UNESCAPED_SLASHES);
                    }

                    $insertData[] = [
                        'template_id' => $template_id,
                        'section_name' => $section['name'], // Same section name
                        'field_label' => $field['name'], // Field label
                        'field_name' => strtolower(str_replace(' ', '_', $field['name'])), // Field name (formatted)
                        'field_type' => $field['type'], // Field type (text, textarea, select, radio, etc.)
                        'field_options' => $fieldOptions, // Field options (if applicable, JSON-encoded for select and radio)
                        'is_required' => isset($field['required']) && $field['required'] ? 1 : 0, // Field required (if applicable)
                        'sort_order' => $sectionSortOrder . '.' . $fieldSortOrder // Dynamic sort order for fields within the section
                    ];

                    $fieldSortOrder++; // Increment field sort order
                }

                $sectionSortOrder++; // Increment section sort order for the next section
            }

            // Insert all the data into the table in a batch (single query)
            $inspectionFieldsModel->insertBatch($insertData);
//            return $template_id;
                return $this->response->setJSON([
                    'status' => 'success',
                    'message' => 'Template '. $template_id . 'created successfully!'
                ]);

        }catch (\Exception $e){
            return $this->response->setJSON([
                'status' => 'error',
                'message' => 'Something went wrong. Please check inspection create. error: '.$e->getMessage()
            ]);
        }
    }



    public function get_template_field(){
        $templateModel = new Inspections_templates_model();
        $view_data['templates'] = $templateModel->findAll();

        $formFieldModel = new Inspections_fields_model();

        // Fetch all fields, ordered by section and sort_order
        $fields = $formFieldModel->getFormFields($this->request->getPost('template_id'));

        $fields->where('template_id', intVal($this->request->getPost('template_id')));
        $fields = $fields->findAll();
        // Group fields by section
        $sections = [];
        foreach ($fields as $field) {
            $sections[$field['section_name']][] = $field;
        }

        $view_data['sections'] = $sections;
        return $this->response->setJSON($sections);
//        return $this->template->view("inspections/inspection_form_fields", $view_data);
    }

    public function get_create_template_modal(){
        $templateModel = new Inspections_templates_model();
        $view_data['templates'] = $templateModel->findAll();

        $formFieldModel = new Inspections_fields_model();

        // Fetch all fields, ordered by section and sort_order
        $fields = $formFieldModel->getFormFields($this->request->getPost('template_id'));

        $fields->where('template_id', intVal($this->request->getPost('template_id')));
        $fields = $fields->findAll();
        // Group fields by section
        $sections = [];
        foreach ($fields as $field) {
            $sections[$field['section_name']][] = $field;
        }

        $view_data['sections'] = $sections;
        return $this->template->view("inspections/create_template_modal", $view_data);
    }


    public function list() {
        $templateModel = new Inspections_Model();
        $data['templates'] = $templateModel->findAll();

        return view('template_list', $data);
    }

    public function delete(){
        $template_id = $this->request->getPost('template_id');
        if ($template_id) {
            try {
                $this->Inspections_templates_model->where('id', $template_id)->set('is_deleted', 1)->update();
            } catch (\Exception $e) {
                return $this->response->setJSON([
                    'status' => 'error',
                    'message' => 'Failed to delete template. Please try again later.'
                ]);
            }
            return $this->response->setJSON([
                'status' => 'success',
                'message' => 'Template deleted successfully.'
            ]);
        }
        return $this->response->setJSON([
            'status' => 'error',
            'message' => 'Template ID is required.'
        ]);
    }

}