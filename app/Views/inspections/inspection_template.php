
<style>
    /* Custom styles for the inspection form */

    body {
        background-color: #f7f9fc;
    }
    .btn-check:checked + .btn {
        opacity: 1; /* Fully opaque when checked */
    }

    .btn-check:not(:checked) + .btn {
        background-color:unset !important;
    }

    .container {
        max-width: 1000px;
        margin-top: 20px;
    }

    .form-section-header {
        background-color: #3c52ec;
        color: white;
        padding: 10px;
        font-size: 1.2rem;
        font-weight: bold;
        border-radius: 8px;
        margin-bottom: 10px;
        cursor: pointer;
        text-align: left;
    }

    .form-section-content {
        background-color: #fff;
        padding: 20px;
        border: 1px solid #ccc;
        border-radius: 8px;
        margin-bottom: 20px;
    }

    .form-group {
        margin-bottom: 1.5rem;
    }

    .form-label {
        font-weight: bold;
    }

    .progress {
        height: 25px;
        margin-bottom: 20px;
        border-radius: 12px;
    }

    .form-check {
        margin-right: 15px;
    }

    .form-check-input {
        margin-right: 10px;
    }

    .form-check-label {
        font-weight: normal;
    }

    button {
        margin-top: 10px;
    }

    .submit-buttons {
        display: flex;
        justify-content: space-between;
        margin-top: 30px;
    }

    button[type="submit"] {
        background-color: #3c52ec;
        color: white;
        border: none;
        border-radius: 8px;
        font-size: 1.1rem;
    }

    button[type="submit"]:hover {
        background-color: #3244d7;
    }

    button[type="button"] {
        background-color: #f3f4f6;
        color: #333;
        border: none;
        border-radius: 8px;
        font-size: 1.1rem;
    }

    button[type="button"]:hover {
        background-color: #e3e5e7;
    }

    /* Collapse icon position */
    .form-section-header::after {
        content: '\25bc'; /* down arrow */
        float: right;
        font-size: 1rem;
    }

    .form-section-header.collapsed::after {
        content: '\25b6'; /* right arrow */
    }

    .field-container{
        background-color: lightgray;
        padding:10px;
        border-radius:5px;
    }
    .field-container .delete-field{
        top: -5px;
        position: relative
    }


</style>
<div id="page-content" class="page-wrapper clearfix grid-button">

    <div class="card clearfix">
        <div class="page-title clearfix">
            <h1>Inspections</h1>
        </div>
        <div class="card-body">
            <div class="page-body">
                <a id="createInspectionTemplate" href="#" class="btn btn-success mb-4 mt-2"><span data-feather="plus" class="icon-16" title="Create template"></span>Create New Template</a>
                <table class="table dataTable" id="template_table">
                    <thead>
                    <tr>
                        <th>Name</th>
                        <th>Created By</th>
                        <th>Actions</th>
                        <th>Options</th>
                    </tr>
                    </thead>
                    <tbody>
                    <?php if (!empty($templates)) :?>
                    <?php foreach ($templates as $template): ?>
                        <tr data-template-id="<?=$template['id']?>">
                            <td><?= $template['template_name'] ?></td>
                            <td><?= $template['created_by'] ?></td>
                            <td>
                                <a  href="#" template_id="<?=$template['id']?>" class="btn btn-success useTemplateBtn">Start Inspection</a>
                            </td>
                            <td class="option w150 text-start">
                                <a href="#" template_id="<?=$template['id']?>" class="edit"><span data-feather="edit" class="icon-16" title="edit"></span></a>
                                <a href="#" template_id="<?=$template['id']?>" class="delete"><span data-feather="x" class="icon-16" title="delete"></span></a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                    <?php else : ?>
                    <tr>
                        <td colspan="3"> No Data</td>
                    </tr>
                    <?php endif ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
<?= view('inspections/create_template_modal') ?>
<?= view('inspections/custom_modal')?>
<script>
    $(function(){
        $("#createInspectionTemplate").on('click', function(){
            $('#InspectionTemplateCreateModal').modal('show')
            $('#InspectionTemplateCreateModal').find('#templateForm')[0].reset();
            $('#InspectionTemplateCreateModal').find('#sections').html('');
            $('#InspectionTemplateCreateModal').find('input[name="template_id"]').val('')
        })

        $('.form-section-header').on('click', function(e){
            e.preventDefault();
        })
    })

    $(function(){
        let templateTable = $('#template_table');
        templateTable.on('click', '.useTemplateBtn', function(e){
            e.preventDefault();
            let template_id = $(this).attr('template_id');
            let formFieldModal = $('#customInspectionModal')
            $.post("<?=get_uri('inspections_templates/get_template_field')?>",
                {
                    template_id : template_id
                },
                function(response){
                    formFieldModal.modal('show');
                    formFieldModal.attr('template_id', template_id)
                    $('#customInspectionModal .modal-body').html(response)
                    $('#customInspectionModal .modal-title').html("Inspection Form")
                }
            )
        })


        templateTable.on('click', '.delete', function(e){
            e.preventDefault();
            let template_id = $(this).attr('template_id');

            $.post("<?=get_uri('inspections_templates/delete')?>",
                {
                    template_id : template_id
                },
                function(response){
                    if(response.status === 'success') {
                        toastr.success(response.message)
                        $('#template_table').find(`tr[data-template-id="${template_id}"]`).fadeOut();
                    }
                    else
                        toastr.error(response.message)
                }
            )
        })

        // Event handler for the Edit button in the template table
        $('#template_table').on('click', '.edit', function(e) {
            e.preventDefault();
            let template_id = $(this).attr('template_id');
            let createTemplateModal = $('#InspectionTemplateCreateModal')
            $.post("<?=get_uri('inspections_templates/get_template_field_data')?>",
                {
                    template_id: template_id
                },
                function (response) {
                    console.log(response)
                    createTemplateModal.modal('show');


                    createTemplateModal.find('.modal-body').html('');
                    createTemplateModal.find('.modal-body').html(response);
                    createTemplateModal.find('input[name="template_id"]').val(template_id)


                    $('.delete-section').on('click', function(){
                        deleteSection(this.getAttribute('data-section'));
                    })

                    $(`.add-field`).on('click', function() {
                        addField(this.getAttribute('data-section'));
                    });

                    $('.delete-field').on('click', function(){
                        deleteField(this.getAttribute('data-section'),this.getAttribute('data-field') );
                    })

                    $('#addSectionBtn').on('click', function(){
                        addSection();
                    })

                    $('.add-option').on('click', function(){
                        addOption(this.getAttribute('data-section'),this.getAttribute('data-field'));
                    })

                    $('#templateForm').submit(function(e) {
                        e.preventDefault();
                        let formData = new FormData(this);
                        let template_id = $(this).find('input[name="template_id"]').val();
                        saveTemplate(formData, template_id);
                    });

                    initSortable();
                });
        })

    })

    $(function(){
        $('#customInspectionModal').on('submit', '#inspectionCreateForm', function(e) {
            e.preventDefault();

            let template_id = $('#customInspectionModal').attr('template_id');
            let formData = new FormData();

            // Append template_id as a single-entry array
            formData.append('template_id[]', template_id);

            // Create an object to hold the fields
            let fields = {};

            // Collect all form input, textarea, and select fields
            $(this).find('input, textarea, select').each(function() {
                let name = $(this).attr('name');
                let value = '';

                if ($(this).is(':radio')) {
                    // Get value of checked radio buttons only
                    value = $('input[name="' + name + '"]:checked').val() || '';
                } else {
                    // Get value for other input types
                    value = $(this).val() || '';
                }

                fields[name] = value;
            });

            // Append fields as a JSON string to FormData
            formData.append('fields', JSON.stringify(fields));
            let client_id = $('#client_id').val();
            let conducted_location = $('#conducted_location').val();
            let conducted_date = $('#conducted_date').val();
            let prepared_by = $('#prepared_by').val();

            formData.append('client_id', client_id)
            formData.append('conducted_location', conducted_location)
            formData.append('conducted_date', conducted_date)
            formData.append('inspector_name', prepared_by)

            $.ajax({
                url: '<?= get_uri("inspections/save") ?>',
                method: 'POST',
                data: formData,
                dataType: 'json',
                processData: false,
                contentType: false,
                success: function(data) {
                    toastr.success(data.message || 'Data saved successfully');
                    $(this).closest('.modal').modal('hide');
                    // Additional success handling if needed
                },
                error: function(jqXHR, textStatus, errorThrown) {
                    console.error("AJAX Error:", textStatus, errorThrown);
                    toastr.error('Error saving data: ' + (errorThrown || 'Unknown error'));
                    // Additional error handling if needed
                }
            });
        });
    })

    let sectionCounter = 0;
    let sectionCounters = {}; // Store field counters for each section

    // Add a new section
    document.getElementById('addSectionBtn').addEventListener('click', function() {
        addSection();
    });

    function addSection(){
        sectionCounter = $('.section-box').length
        sectionCounter++;
        sectionCounters[sectionCounter] = 0; // Initialize field counter for the new section

        const sectionHtml = `
            <div class="section-box card item mb-4" draggable="true" data-section-id="${sectionCounter}">
                <div class="card-header bg-secondary text-white fw-bolder d-flex justify-content-between">
                    <h4>Section ${sectionCounter}</h4>
                    <button type="button" class="btn btn-danger btn-sm delete-section" data-section="${sectionCounter}" title="delete"><span data-feather="x" class="icon-16" aria-hidden="true"></span></button>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <label for="section_name_${sectionCounter}" class="form-label">Section Name</label>
                        <input type="text" class="form-control" id="section_name_${sectionCounter}" name="sections[${sectionCounter}][name]" placeholder="Enter section name" required>
                    </div>
                    <div class="fields"></div>
                </div>
                <div class="card-footer">
                    <button type="button" class="btn btn-light add-field" data-section="${sectionCounter}">Add Field</button>
                </div>
            </div>`;

        document.getElementById('sections').insertAdjacentHTML('beforeend', sectionHtml);

        // Attach event for "Add Field" button in the new section
        document.querySelector(`.add-field[data-section="${sectionCounter}"]`).addEventListener('click', function() {
            addField(this.getAttribute('data-section'));
        });

        // Attach event for "Delete Section" button
        document.querySelector(`.delete-section[data-section="${sectionCounter}"]`).addEventListener('click', function() {
            deleteSection(this.getAttribute('data-section'));
        });

        initSortable(); // Initialize sortable functionality on new section
    }

    // Function to add a field to a specific section
    function addField(sectionId) {
        const fieldCounter = ++sectionCounters[sectionId]; // Increment field counter for this section

        const fieldHtml = `
            <div class="field-container mb-3" data-field-id="${fieldCounter}">
                <div class="d-flex justify-content-between align-items-center">
                    <label for="field_${sectionId}_${fieldCounter}" class="form-label">Field Name</label>
                    <button type="button" class="btn btn-danger btn-sm delete-field" data-section="${sectionId}" data-field="${fieldCounter}" title="delete"><span data-feather="x" class="icon-16" aria-hidden="true"></span></button>
                </div>
                <input type="text" class="form-control" id="field_${sectionId}_${fieldCounter}" name="sections[${sectionId}][fields][${fieldCounter}][name]" placeholder="Enter field name" required>
                <label for="field_type_${sectionId}_${fieldCounter}" class="form-label">Field Type</label>
                <select class="form-select" id="field_type_${sectionId}_${fieldCounter}" name="sections[${sectionId}][fields][${fieldCounter}][type]" required>
                    <option value="text">Text</option>
                    <option value="textarea">Textarea</option>
                    <option value="select">Select</option>
                    <option value="radio">Radio</option>
                    <option value="date">Date</option>
                </select>
                <div class="mb-3 field-options" style="display:none;">
                    <label for="field_options_${sectionId}_${fieldCounter}" class="form-label">Field Options</label>
                    <div class="options-container"></div>
                    <button type="button" class="btn btn-secondary btn-sm add-option" data-section="${sectionId}" data-field="${fieldCounter}" title="Add Option">+ Add Option</button>
                </div>
            </div>`;

        const fieldsContainer = document.querySelector(`.section-box[data-section-id="${sectionId}"] .fields`);
        fieldsContainer.insertAdjacentHTML('beforeend', fieldHtml);

        // Attach event to the new "Field Type" dropdown to show/hide options and color inputs
        const fieldTypeSelect = document.getElementById(`field_type_${sectionId}_${fieldCounter}`);
        fieldTypeSelect.addEventListener('change', function() {
            const optionsField = fieldTypeSelect.closest('.field-container').querySelector('.field-options');
            const colorField = optionsField.nextElementSibling;
            if (fieldTypeSelect.value === 'select' || fieldTypeSelect.value === 'radio') {
                optionsField.style.display = 'block';
                colorField.style.display = 'block';
            } else {
                optionsField.style.display = 'none';
                colorField.style.display = 'none';
            }
        });

        // Attach event for "Add Option" button to dynamically add options
        const addOptionBtn = document.querySelector(`.add-option[data-section="${sectionId}"][data-field="${fieldCounter}"]`);
        addOptionBtn.addEventListener('click', function() {
            addOption(sectionId, fieldCounter);
        });

        // Attach event for "Delete Field" button
        document.querySelector(`.delete-field[data-section="${sectionId}"][data-field="${fieldCounter}"]`).addEventListener('click', function() {
            deleteField(sectionId, fieldCounter);
        });
    }

    function addOption(sectionId, fieldCounter) {
        const optionsContainer = document.querySelector(`.section-box[data-section-id="${sectionId}"] .field-container[data-field-id="${fieldCounter}"] .options-container`);

        const optionCounter = optionsContainer.children.length + 1; // Keep track of the number of options
        const optionHtml = `
        <div class="d-flex align-items-center mb-2">
            <input type="text" class="form-control me-2" name="sections[${sectionId}][fields][${fieldCounter}][options][]" placeholder="Option ${optionCounter}" required>
            <input type="color" class="form-control form-control-color me-2" style="width:80px" name="sections[${sectionId}][fields][${fieldCounter}][colors][]" value="#7AB2D3" title="Choose color">
            <input type="checkbox" class="me-2" name="sections[${sectionId}][fields][${fieldCounter}][flagged][]" value="1" title="Mark flagged"><label class="me-3">Flagged</label>
            <input type="hidden" class="me-2" name="sections[${sectionId}][fields][${fieldCounter}][flagged][]" value="0" title="Mark flagged">
            <button type="button" class="btn btn-danger btn-sm delete-option" style="padding: 1px 9px" title="Delete Option">x</button>
        </div>`;

        optionsContainer.insertAdjacentHTML('beforeend', optionHtml);

        // Attach event for "Delete Option" button
        const deleteOptionBtn = optionsContainer.lastElementChild.querySelector('.delete-option');
        deleteOptionBtn.addEventListener('click', function() {
            deleteOption(this);
        });
    }

    // Function to delete an option
    function deleteOption(optionElement) {
        optionElement.closest('.d-flex').remove();
    }

    // Function to delete a section
    function deleteSection(sectionId) {
        const sectionElement = document.querySelector(`.section-box[data-section-id="${sectionId}"]`);
        if (sectionElement) {
            sectionElement.remove();
            delete sectionCounters[sectionId]; // Remove field counter for this section
        }
    }

    // Function to delete a field
    function deleteField(sectionId, fieldCounter) {
        const fieldElement = document.querySelector(`.section-box[data-section-id="${sectionId}"] .field-container[data-field-id="${fieldCounter}"]`);
        if (fieldElement) {
            fieldElement.remove();
        }
    }

    // Initialize sortable functionality
    function initSortable() {
        const sortableList = document.querySelector(".sortable-list");
        const items = sortableList.querySelectorAll(".item");

        items.forEach(item => {
            item.addEventListener("dragstart", () => {
                setTimeout(() => item.classList.add("dragging"), 0);
            });
            item.addEventListener("dragend", () => item.classList.remove("dragging"));
        });

        sortableList.addEventListener("dragover", (e) => {
            e.preventDefault();
            const draggingItem = sortableList.querySelector(".dragging");
            let siblings = [...sortableList.querySelectorAll(".item:not(.dragging)")];
            let nextSibling = siblings.find(sibling => e.clientY <= sibling.offsetTop + sibling.offsetHeight / 2);
            sortableList.insertBefore(draggingItem, nextSibling);
        });
    }


    // Submit form via AJAX
    $(function() {
        $('#templateForm').submit(function(e) {
            e.preventDefault();
            let formData = new FormData(this);
            let template_id = $(this).find('input[name="template_id"]').val();
            saveTemplate(formData, template_id);
        });
    });

    function saveTemplate(formData, template_id){
        let url = (template_id ? "<?= get_uri('inspections_templates/update') ?>" : "<?= get_uri('inspections_templates/create') ?>")

        $.ajax({
            url: url,
            method: 'POST',
            data: formData,
            contentType: false,
            processData: false,
            success: function(response) {
                if(response.status === 'success')
                    toastr.success(response.message)
            },
            error: function(response) {
            }
        });

        $(this).closest('.modal').modal('hide');
    }

    // Initialize the dragging functionality
    function initDraggable(containerSelector, itemSelector) {
        const container = document.querySelector(containerSelector);

        let draggedItem = null;

        container.addEventListener('dragstart', function (e) {
            if (e.target && e.target.matches(itemSelector)) {
                draggedItem = e.target;
                setTimeout(() => draggedItem.classList.add('dragging'), 0);
            }
        });

        container.addEventListener('dragend', function (e) {
            if (draggedItem) {
                setTimeout(() => draggedItem.classList.remove('dragging'), 0);
                draggedItem = null;
            }
        });

        container.addEventListener('dragover', function (e) {
            e.preventDefault();
            const afterElement = getDragAfterElement(container, e.clientY);
            const currentlyDragging = container.querySelector('.dragging');
            if (afterElement == null) {
                container.appendChild(currentlyDragging);
            } else {
                container.insertBefore(currentlyDragging, afterElement);
            }
        });

        // Helper function to get the element after which the dragged element should be placed
        function getDragAfterElement(container, y) {
            const draggableElements = [...container.querySelectorAll(`${itemSelector}:not(.dragging)`)];
            return draggableElements.reduce((closest, child) => {
                const box = child.getBoundingClientRect();
                const offset = y - (box.top + box.height / 2);
                if (offset < 0 && offset > closest.offset) {
                    return { offset: offset, element: child };
                } else {
                    return closest;
                }
            }, { offset: Number.NEGATIVE_INFINITY }).element;
        }
    }


</script>