
<style>
    .table-header {
        background-color: #f1f3f5;
    }
    .table tbody tr td {
        vertical-align: middle;
    }
    .progress {
        height: 20px;
    }
    .actions-dropdown {
        cursor: pointer;
    }

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
        color: white;
        border: none;
        padding: 12px 25px;
        border-radius: 8px;
        font-size: 1.1rem;
    }


    button[type="button"] {
        background-color: #f3f4f6;
        color: #333;
        border: none;
        padding: 12px 25px;
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

</style>
<div id="page-content" class="page-wrapper clearfix grid-button">

    <div class="card clearfix">
        <div class="page-title clearfix">
            <h1>Inspections</h1>
        </div>
        <div class="card-body">
            <div class="page-body">
                <table class="table table-bordered table-hover dataTable" id="inspections_table">
                    <thead>
                    <tr class="table-header">
                        <th scope="col">
                            <input class="form-check-input" type="checkbox">
                        </th>
                        <th scope="col">Inspection</th>
                        <th scope="col">Actions</th>
                        <th scope="col">Template Name</th>
                        <th scope="col">Result</th>
                        <th scope="col">Status</th>
                        <th scope="col"></th>
                    </tr>
                    </thead>
                    <tbody>
                    <?php foreach ($inspections as $item): ?>
                        <tr data-inspection-id="<?=trim($item['id'])?>">
                            <td><input class="form-check-input" type="checkbox"></td>
                            <td>
                                <a href="#" class=" btn btn-link view" inspection_id="<?=$item['id']?>">
                                <?= $item['created_at'] ?> <br><small><?= $item['inspection_name']?></small>
                                </a>
                            </td>
                            <td class="option w150">
                                <a href="#" class="edit" inspection_id="<?=$item['id']?>"><span data-feather="edit" class="icon-16" title="Edit"></span></a>
                                <a href="#"  class="delete" inspection_id="<?=$item['id']?>"><span data-feather="x" class="icon-16" title="delete"></span></a>
                            </td>
                            <td><?= $item['template'] ? $item['template']['template_name'] : "Missing Template"?></td>
                            <td><?= $item['result']?></td>
                            <td><?= $item['status']?></td>
                            <td><button class="btn btn-link viewReport" inspection_id="<?=$item['id']?>"> View Report</button></td>
                            <td><button class="btn btn-link viewPdf" inspection_id="<?=$item['id']?>"> View Pdf</button></td>
                        </tr>
                    <?php endforeach; ?>


                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!--Note-->
<?= view('inspections/custom_modal')?>
<script>
    $(function(){
        let isUpdating = false;  // Flag to track update status

        let inspectionTable =  $('#inspections_table');
        inspectionTable.on('click', '.view', function() {
            let inspection_id = $(this).attr('inspection_id');
            $('#customInspectionModal .modal-body').html("");
            getInspection(inspection_id).then(function(response) {
                $('#customInspectionModal #inspectionCreateForm button[type="submit"]').hide();
                $('#customInspectionModal').modal('show');
                $('#customInspectionModal .modal-body').html(response);
                $('#customInspectionModal #inspectionCreateForm button[type="submit"]').hide();
            });
        });

        let debounceTimer; // Declare a timer variable outside to keep track of debounce

        inspectionTable.on('click', '.edit', function() {
            let inspection_id = $(this).attr('inspection_id');
            $('#customInspectionModal .modal-body').html("");
            getInspection(inspection_id).then(function(response) {
                $('#customInspectionModal').modal('show');
                $('#customInspectionModal .modal-body').html(response);
                // Unbind any previous event handlers to prevent multiple triggers
                $('#customInspectionModal #inspectionCreateForm :input').off('input change').on('input change', function() {
                    let $this = $(this);
                    clearTimeout(debounceTimer);
                    debounceTimer = setTimeout(() => {
                        updateResponse($this); // Call update function after delay
                    }, 300); // Adjust delay as needed (300ms in this example)
                });

                $('#customInspectionModal #inspectionCreateForm button[type="submit"]').hide();
            });
        });

        inspectionTable.on('click', '.viewReport', function(){
            let inspection_id = $(this).attr('inspection_id')
            window.location.href="<?= get_uri('inspections/view_report/') ?>"+inspection_id
        })

        inspectionTable.on('click', '.viewPdf', function(){
            let inspection_id = $(this).attr('inspection_id')
            window.location.href="<?= get_uri('inspections/prepare_inspection_pdf/') ?>"+inspection_id
        })

        inspectionTable.on('click', '.delete', function(){
            let inspection_id = $(this).attr('inspection_id')
            deleteInspection(inspection_id)
        })

        $("#customInspectionModal").on('change input', '#conducted_date, #client_id, #prepared_by, #conducted_location, #payment_method', function() {
            let $this = $(this);
            clearTimeout(debounceTimer);
            debounceTimer = setTimeout(() => {
                updateInspection($this);
            }, 300); // Adjust delay as needed (300ms in this example)

        });
    })

    function updateInspection($this){
        let inspection_id = $this.attr('inspection_id')
        let client_id = $("#client_id").val();
        let location = $("#conducted_location").val();
        let date = $("#conducted_date").val();
        let prepared_by = $("#prepared_by").val();
        let payment_method = $("#payment_method").val();
        $.ajax({
            url: "<?= get_uri('inspections/update_inspection') ?>",
            type: "POST", // Specify request type
            data: {
                inspection_id: inspection_id,
                client_id: client_id,
                location: location,
                date: date,
                prepared_by: prepared_by,
                payment_method: payment_method
            },
            success: function(response) {
                if (response.status === 'success')
                    toastr.success(response.message);
                else
                    toastr.error(response.message);
            },
            error: function(xhr, status, error) {
                console.error("AJAX Error:", status, error);
                alert("An error occurred while updating the inspection details. Please try again.");
            }
        });
    }

    async function getInspection(inspection_id){
        return $.ajax({
            url: "<?= get_uri('inspections/list') ?>",
            type: "POST", // Specify request type
            data: {
                inspection_id: inspection_id
            },
            success: function(response) {
                return response
            },
            error: function(xhr, status, error) {
                console.error("AJAX Error:", status, error);
                alert("An error occurred while loading the inspection details. Please try again.");
            }
        });
    }

    function updateResponse($this) {
        let inspection_response_id = $this.attr('response_id');

        $.ajax({
            url: "<?= get_uri('inspections/update_inspection_response') ?>",
            type: "POST", // Specify request type
            data: {
                inspection_response_id: inspection_response_id,
                response: $this.val()
            },
            success: function(response) {
                if (response.status === 'success')
                    toastr.success(response.message);
                else
                    toastr.error(response.message);
            },
            error: function(xhr, status, error) {
                console.error("AJAX Error:", status, error);
                alert("An error occurred while updating the inspection details. Please try again.");
            }
        });
    }

    function deleteInspection(inspection_id){
        console.log('here')
        console.log(inspection_id)
        $.ajax({
            url: "<?= get_uri('inspections/delete') ?>",
            type: "POST", // Specify request type
            data: {
                "inspection_id": inspection_id
            },
            datatype: 'json',
            success: function(response) {

                if(response.status === 'success') {
                    console.log(`data-inspection-id="${inspection_id}"`)
                    $('#inspections_table').find(`tr[data-inspection-id="${inspection_id}"]`).fadeOut();
                    toastr.success(response.message)
                }
                else
                    toastr.error(response.message)
            },
            error: function(xhr, status, error) {
                console.error("AJAX Error:", status, error);
                alert("An error occurred while updating the inspection details. Please try again.");
            }
        });
    }
</script>
