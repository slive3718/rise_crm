 <style>
        *{
            font-family:"Open Sans", "Helvetica Neue", Helvetica, Arial, sans-serif !important;
        }
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
            border-radius:  5px 5px 0 0;
            background-color: #b3c1e0 ;
            color: black;
            padding: 10px;
            font-size: 1.2rem;
            font-weight: bold;
            /*border-radius: 8px;*/
            margin-bottom: 10px;
            cursor: pointer;
            text-align: left;
        }

        .form-section-content {
            background-color:  #b3c1e0;
            padding: 0 20px 0  20px;
            /*border: 1px solid #ccc;*/
            border-radius: 20px;
            margin-bottom: 20px;
        }

        .form-section-content .card{
            border-radius:15px
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
        <div class="card clearfix " >
            <div class="page-title clearfix">
                <h1>Inspections</h1>
            </div>
            <div class="card-body">
                <div class="page-body">
                    <div class="container my-4">

                        <div class="card bg-none">
                            <div class="card-header d-flex justify-content-between">
                                <h5>Overview</h5>
                                <span class="badge bg-success">Complete</span>
                            </div>
                            <div class="card-body">
                                <div class="text-center mb-3">
                                    <img src="<?=get_uri('assets/images/logo.png')?>" alt="Company Logo" class="img-fluid" style="max-height: 100px;">
                                    <h6 class="mt-2"><?=$inspection['inspection_name']?></h6>
                                    <p><?=date("Y-m-d", strtotime($inspection['created_at']))?></p>
                                </div>
                                <div class="row">
                                    <div class="col-md-4">
                                        <p><strong>Inspection score:</strong> 15 / 16 (93.75%)</p>
                                    </div>
                                    <div class="col-md-4">
                                        <p><strong>Flagged items:</strong> 2</p>
                                    </div>
                                    <div class="col-md-4">
                                        <p><strong>Created actions:</strong> 0</p>
                                    </div>
                                </div>
                                <hr>
                                <p><strong>Customer:</strong> <?=$inspection_client->company_name?></p>
                                <p><strong>Email:</strong> <?=$inspection_client->email?></p>
                                <p><strong>Location:</strong> <?=$inspection['location']?></p>
                                <p><strong>Conducted on:</strong> <?= date("Y-m-d", strtotime($inspection['inspection_date']))?></p>
                                <p><strong>Prepared By:</strong> <?=$inspection['inspector_name']?></p>
                                <p><strong>Paid By:</strong> N/A</p>
                            </div>
                        </div>

                        <!-- Flagged Items Card -->
                        <!-- Flagged Items Card -->
                        <div class="card" style="background-color: #b3c1e0;">
                            <div class="form-section">
                                <div class="form-section-header px-4 pt-3" data-bs-toggle="collapse" data-bs-target="#section-flagged-items" aria-expanded="false" aria-controls="section-flagged-items">
                                    Flagged Items
                                    <span data-feather="bell" class="icon-16 ms-3 text-danger" title="Flagged" ></span>
                                    <span class="text-danger" id="flagged_items_count"><?=$totalFlagged ??''?></span>
                                </div>
                                <div id="section-flagged-items" class="form-section-content collapse">
                                    <?php foreach ($sections as $section_name => $fields): ?>
                                        <?php if(!empty ($flaggedCounts[$section_name])): ?>
                                        <div class="form-section">
                                        <div  class="form-section-header px-4 pt-3" data-bs-toggle="collapse" data-bs-target="#section-<?= strtolower(str_replace(' ', '-', $section_name)) ?>" aria-expanded="false" aria-controls="section-<?= strtolower(str_replace(' ', '-', $section_name)) ?>">
                                            <?= $section_name ?>
                                            <span data-feather="bell" class="icon-16 ms-3 text-danger" title="Flagged" ></span>
                                            <span class="text-danger" id="flagged_items_count"><?= $flaggedCounts[$section_name] ?? 0 ?> </span>
                                        </div>
                                        <div id="section-<?= strtolower(str_replace(' ', '-', $section_name)) ?>" class="form-section-content collapse">
                                            <?php foreach ($fields as $field): ?>
                                                <?php if($field['flagged'] == '1'): ?>
                                            <div class="form-group">
                                                <?php if ($field['field_type'] == 'radio'): ?>
                                                <!-- Radio buttons styled with colors -->
                                                <div class="card border-0 p-3">
                                                    <div class="mb-2">
                                                        <h5 class="mb-0"><?= $field['field_label'] ?></h5>
                                                    </div>
                                                    <div class="row">
                                                        <?php  foreach (json_decode($field['field_options'], true) as $option): ?>
                                                            <div class="col-12">
                                                                <?php if( $field['value'] == $option['label'] ): ?>
                                                                    <badge class="badge py-1 px-4 text-start" data-flagged = "<?= (isset($option['flags']) && $option['flags'] === '1') ? 1: 0 ?>"
                                                                           for="<?= $field['field_name'].'-'.$option['label'] ?>"
                                                                           style="border-radius:20px;font-size: 16px ;color: white; background-color: <?= (isset($option['flags']) && $option['flags'] === '1') ? 'rgb(198,0,34)' : $option['color'] ?>;">
                                                                        <?= $option['label'] ?>
                                                                    </badge>
                                                                <?php endif; ?>
                                                            </div>
                                                        <?php endforeach; ?>
                                                    </div>
                                                </div>
                                                <?php endif ?>
                                            </div>
                                                <?php endif ?>
                                            <?php endforeach; ?>
                                        </div>
                                    </div>
                                        <?php endif ?>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                        </div>
                        <!-- Overview Card -->
                        <div class="card" style="background-color: #b3c1e0 ;" >
                        <div id="inspectionCreateForm">
                            <?php foreach ($sections as $section_name => $fields): ?>
                                <div class="form-section">
                                    <div  class="form-section-header px-4 pt-3" data-bs-toggle="collapse" data-bs-target="#section-<?= strtolower(str_replace(' ', '-', $section_name)) ?>" aria-expanded="false" aria-controls="section-<?= strtolower(str_replace(' ', '-', $section_name)) ?>">
                                        <?= $section_name ?>
                                        <span class="flagCount"></span>
                                    </div>
                                    <div id="section-<?= strtolower(str_replace(' ', '-', $section_name)) ?>" class="form-section-content collapse">
                                        <?php foreach ($fields as $field): ?>
                                            <div class="form-group">
                                                <?php if ($field['field_type'] == 'radio'): ?>
                                                    <!-- Radio buttons styled with colors -->
                                                    <div class="card border-0 p-3">
                                                        <div class="mb-2">
                                                            <h5 class="mb-0"><?= $field['field_label'] ?></h5>
                                                        </div>
                                                        <div class="row">
                                                            <?php  foreach (json_decode($field['field_options'], true) as $option): ?>
                                                                <div class="col-12">
                                                                    <?php if( $field['value'] == $option['label'] ): ?>
                                                                    <badge class="badge py-1 px-4 text-start" data-flagged = "<?= (isset($option['flags']) && $option['flags'] === '1') ? 1: 0 ?>"
                                                                           for="<?= $field['field_name'].'-'.$option['label'] ?>"
                                                                           style="border-radius:20px;font-size: 16px ;color: white; background-color: <?= (isset($option['flags']) && $option['flags'] === '1') ? 'rgb(198,0,34)' : $option['color'] ?>;">
                                                                        <?= $option['label'] ?>
                                                                    </badge>
                                                                    <?php endif; ?>
                                                                </div>
                                                            <?php endforeach; ?>
                                                        </div>
                                                    </div>

                                                <?php elseif ($field['field_type'] == 'text'): ?>
                                                    <!-- Text input -->
                                                    <label for="<?= $field['field_name'] ?>" class="form-label"><?= $field['field_label'] ?></label>
                                                    <input type="text" class="form-control" id="<?= $field['field_name'] ?>" name="<?= $field['id'] ?>" placeholder="Enter <?= $field['field_label'] ?>" value="<?= isset($field['value']) ? $field['value'] : '' ?>" response_id="<?=($field['response_id'] ?? '')?>">

                                                <?php elseif ($field['field_type'] == 'textarea'): ?>
                                                    <!-- Textarea input -->
                                                    <label for="<?= $field['field_name'] ?>" class="form-label"><?= $field['field_label'] ?></label>
                                                    <textarea class="form-control" id="<?= $field['field_name'] ?>" name="<?= $field['id'] ?>" placeholder="Enter <?= $field['field_label'] ?>" response_id="<?=($field['response_id'] ?? '')?>"><?= isset($field['value']) ? $field['value'] : '' ?></textarea>

                                                <?php elseif ($field['field_type'] == 'select'): ?>
                                                    <!-- Select Dropdown styled with options -->
                                                    <label for="<?= $field['field_name'] ?>" class="form-label"><?= $field['field_label'] ?></label>
                                                    <select class="form-select" id="<?= $field['field_name'] ?>" name="<?= $field['id'] ?>" response_id="<?=($field['response_id'] ?? '')?>">
                                                        <?php if(!empty(json_decode($field['field_options'], true))) : ?>
                                                            <?php foreach (json_decode($field['field_options'], true) as $option): ?>
                                                                <option value="<?= $option['label'] ?>" <?= isset($field['value']) && $option['label'] == $field['value'] ? 'selected' : '' ?>>
                                                                    <?= $option['label'] ?>
                                                                </option>
                                                            <?php endforeach; ?>
                                                        <?php endif; ?>
                                                    </select>
                                                <?php endif; ?>
                                            </div>
                                        <?php endforeach; ?>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>