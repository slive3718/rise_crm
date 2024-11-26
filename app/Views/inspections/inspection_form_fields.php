
<div class="card">
    <div class="card-body">
        <label for="client_id"> Client </label>
        <select name="client_id" id="client_id" class="form-control" inspection_id="<?=$inspection['id'] ?? ""?>">
            <?php if(!empty($clients)): ?>
                <?php foreach ($clients as $client): ?>
                    <option value="<?=$client->id?>" <?= (isset($inspection) && $inspection['client_id'] == $client->id)? 'selected':'' ?>><?= $client->company_name ?></option>
                <?php endforeach ?>
            <?php endif ?>
        </select>

        <label for="payment_method"> Payment Method </label>
        <select name="payment_method" id="payment_method" class="form-control" inspection_id="<?=$inspection['id'] ?? ""?>">
            <?php if(!empty($payment_method)): ?>
                <option value="" >-- select --</option>
                <?php foreach ($payment_method as $method): ?>
                    <option value="<?=$method->id?>"  <?= (isset($inspection) && $inspection['payment_method_id'] == $method->id)? 'selected':'' ?>><?= $method->title ?></option>
                <?php endforeach ?>
            <?php endif ?>
        </select>

        <label for="conducted_location">Location</label>
        <input type="text" id="conducted_location" name="conducted_location" class="form-control" inspection_id="<?=$inspection['id'] ?? ""?>" value="<?= (isset($inspection) && $inspection['location'])? $inspection['location']:'' ?>">

        <label for="conducted_date">Conducted Date</label>
        <input type="date" id="conducted_date" name="conducted_date" class="form-control" inspection_id="<?=$inspection['id'] ?? ""?>" value="<?= (isset($inspection) && $inspection['inspection_date'])? date("Y-m-d", strtotime($inspection['inspection_date'])):'' ?>">

        <label for="prepared_by">Prepared By</label>
        <input type="text" id="prepared_by" name="conducted_date" class="form-control" inspection_id="<?=$inspection['id'] ?? "" ?>" value="<?= (isset($inspection) && $inspection['inspector_name'])? $inspection['inspector_name']:'' ?>">
    </div>
</div>
<form id="inspectionCreateForm">
    <div id="sections">
        <?php foreach ($sections as $section_index => $fields): ?>
            <div class="section-box card item mb-4" draggable="true" data-section-id="<?= $section_index ?>">
                <div class="card-header bg-secondary text-white fw-bolder d-flex justify-content-between">
                    <h4>Section <?= $section_index ?></h4>
                    <button type="button" class="btn btn-danger btn-sm delete-section" data-section="<?= $section_index ?>" title="delete">
                        <span class="icon-16" aria-hidden="true">x</span>
                    </button>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <label for="section_name_<?= $section_index ?>" class="form-label">Section Name</label>
                        <input type="text" class="form-control" id="section_name_<?= $section_index ?>" name="sections[<?= $section_index ?>][name]" placeholder="Enter section name" value="<?= $section_index ?>" required>
                    </div>
                    <div class="fields">
                        <?php foreach ($fields as $field): ?>
                            <div class="form-group">
                                <?php if ($field['field_type'] == 'radio'): ?>
                                    <div class="">
                                        <div class="mb-2">
                                            <h5 class="mb-0"><?= $field['field_label'] ?></h5>
                                        </div>
                                        <div class="row g-3">
                                            <?php foreach (json_decode($field['field_options'], true) as $option): ?>
                                                <div class="col-6">
                                                    <input type="radio" class="btn-check" name="<?= $field['id'] ?>"
                                                           id="<?= $field['id'] . '_' . $option['label'] ?>" value="<?= $option['label'] ?>" autocomplete="off"
                                                        <?= isset($field['value']) && $field['value'] == $option['label'] ? 'checked' : '' ?>
                                                           response_id="<?= $field['response_id'] ?? '' ?>">
                                                    <label class="btn btn-outline-primary w-100 text-start"
                                                           for="<?= $field['id'] . '_' . $option['label'] ?>"
                                                           style="color: black; background-color: <?= isset($option['flagged']) && $option['flagged'] === '1' ? 'rgb(198, 0, 34)' : ($option['color'] ?? '') ?>;">
                                                        <?= $option['label'] ?>
                                                    </label>
                                                </div>
                                            <?php endforeach; ?>
                                        </div>
                                    </div>
                                <?php elseif ($field['field_type'] == 'text'): ?>
                                    <label for="<?= $field['field_name'] ?>" class="form-label"><?= $field['field_label'] ?></label>
                                    <input type="text" class="form-control" id="<?= $field['field_name'] ?>" name="<?= $field['id'] ?>" placeholder="Enter <?= $field['field_label'] ?>" value="<?= $field['value'] ?? '' ?>" response_id="<?= $field['response_id'] ?? '' ?>">
                                <?php elseif ($field['field_type'] == 'textarea'): ?>
                                    <label for="<?= $field['field_name'] ?>" class="form-label"><?= $field['field_label'] ?></label>
                                    <textarea class="form-control" id="<?= $field['field_name'] ?>" name="<?= $field['id'] ?>" placeholder="Enter <?= $field['field_label'] ?>" response_id="<?= $field['response_id'] ?? '' ?>"><?= $field['value'] ?? '' ?></textarea>
                                <?php elseif ($field['field_type'] == 'select'): ?>
                                    <label for="<?= $field['field_name'] ?>" class="form-label"><?= $field['field_label'] ?></label>
                                    <select class="form-select" id="<?= $field['field_name'] ?>" name="<?= $field['id'] ?>" response_id="<?= $field['response_id'] ?? '' ?>">
                                        <?php foreach (json_decode($field['field_options'], true) as $option): ?>
                                            <option value="<?= $option['label'] ?>" <?= isset($field['value']) && $field['value'] == $option['label'] ? 'selected' : '' ?>>
                                                <?= $option['label'] ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                <?php endif; ?>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
<!--                <div class="card-footer">-->
<!--                    <button type="button" class="btn btn-light add-field" data-section="--><?php //= $section_index ?><!--">Add Field</button>-->
<!--                </div>-->
            </div>
        <?php endforeach; ?>
    </div>
    <button type="submit" class="btn btn-success">Submit Inspection</button>
</form>
