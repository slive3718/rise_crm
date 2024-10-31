<form id="inspectionCreateForm">
    <?php foreach ($sections as $section_name => $fields): ?>
        <div class="form-section card">
            <div type="button" class="form-section-header" data-bs-toggle="collapse" data-bs-target="#section-<?= strtolower(str_replace(' ', '-', $section_name)) ?>" aria-expanded="false" aria-controls="section-<?= strtolower(str_replace(' ', '-', $section_name)) ?>">
                <?= $section_name ?>
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
                                <div class="row g-3">
                                    <?php foreach (json_decode($field['field_options'], true) as $option): ?>
                                        <div class="col-6">
                                            <input type="radio" class="btn-check" name="<?= $field['id'] ?>"
                                                   id="<?= $field['field_name'].'-'.$option['label'] ?>" value="<?= $option['label'] ?>" autocomplete="off"
                                                <?=  isset($field['value']) && $field['value'] == $option['label'] ? 'checked': '' ?>
                                                   response_id="<?=($field['response_id'] ?? '')?>"
                                            >
                                            <label class="btn btn-outline-primary w-100 text-start"
                                                   for="<?= $field['field_name'].'-'.$option['label'] ?>"
                                                   style="color: black; background-color: <?= $option['color'] ?>;">
                                                <?= $option['label'] ?>
                                            </label>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                                <div class="d-flex gap-4 mt-4">
                                    <a href="#" class="text-primary text-decoration-none">
                                        <i class="bi bi-pencil-square me-2"></i>Add note
                                    </a>
                                    <a href="#" class="text-primary text-decoration-none">
                                        <i class="bi bi-paperclip me-2"></i>Attach media
                                    </a>
                                    <a href="#" class="text-primary text-decoration-none">
                                        <i class="bi bi-lightning me-2"></i>Create action
                                    </a>
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
    <button type="submit" class="btn btn-success">Submit Inspection</button>
</form>
