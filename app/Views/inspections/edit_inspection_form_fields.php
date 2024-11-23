<form action="" method="post" id="templateForm">
    <div class="mb-4">
        <label for="template_name" class="form-label"><?=$templates['template_name'] ?? ''?></label>
        <input type="text" class="form-control" id="template_name" name="template_name" placeholder="Enter template name" value="<?=$templates['template_name'] ?? ''?>" required>
        <input type="hidden" class="form-control" id="template_id" name="template_id" value="">
    </div>
    <div id="sections" class="sortable-list">
        <?php
        $sectionCount = 1;
        foreach ($sections as $sectionIndex => $section): ?>

            <div class="section-box card item mb-4" draggable="true" data-section-id="<?= $sectionCount ?>">
                <div class="card-header bg-secondary text-white fw-bolder d-flex justify-content-between">
                    <h4>Section <?= $sectionCount ?></h4>
                    <button type="button" class="btn btn-danger btn-sm delete-section" data-section="<?= $sectionCount ?>" title="delete">
                        <span data-feather="x" class="icon-16" aria-hidden="true"></span>
                    </button>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <label for="section_name_<?= $sectionCount ?>" class="form-label">Section Name</label>
                        <input type="text" class="form-control" id="section_name_<?= $sectionCount ?>"
                               name="sections[<?= $sectionCount ?>][name]"
                               value="<?= htmlspecialchars($sectionIndex) ?>"
                               placeholder="Enter section name" required>
                    </div>
                    <div class="fields">
                        <?php
                        $fieldCount = 1;
                        foreach ($section as $fieldIndex => $field):
                            $fieldIndex ++;
                            $fieldType = $field['field_type'];
                            $fieldName = htmlspecialchars($field['field_name']);
                            $fieldOptions = $field['field_options'] ?? [];

                            if($fieldType):
                                ?>
                                <div class="field-container mb-3" data-field-id="<?= $fieldIndex ?>">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <label for="field_<?= $sectionCount ?>_<?= $fieldIndex ?>" class="form-label">Field Name</label>
                                        <button type="button" class="btn btn-danger btn-sm delete-field"
                                                data-section="<?= $sectionCount ?>"
                                                data-field="<?= $fieldIndex ?>"
                                                title="delete">
                                            <span data-feather="x" class="icon-16" aria-hidden="true"></span>
                                        </button>
                                    </div>
                                    <input type="text" class="form-control"
                                           id="field_<?= $sectionCount ?>_<?= $fieldIndex ?>"
                                           name="sections[<?= $sectionCount ?>][fields][<?= $fieldIndex ?>][name]"
                                           value="<?= $fieldName ?>"
                                           placeholder="Enter field name" required>

                                    <label for="field_type_<?= $sectionCount ?>_<?= $fieldIndex ?>" class="form-label">Field Type</label>
                                    <select class="form-select"
                                            id="field_type_<?= $sectionCount ?>_<?= $fieldIndex ?>"
                                            name="sections[<?= $sectionCount ?>][fields][<?= $fieldIndex ?>][type]" required>
                                        <option value="text" <?= $fieldType == 'text' ? 'selected' : '' ?>>Text</option>
                                        <option value="textarea" <?= $fieldType == 'textarea' ? 'selected' : '' ?>>Textarea</option>
                                        <option value="select" <?= $fieldType == 'select' ? 'selected' : '' ?>>Select</option>
                                        <option value="radio" <?= $fieldType == 'radio' ? 'selected' : '' ?>>Radio</option>
                                        <option value="date" <?= $fieldType == 'date' ? 'selected' : '' ?>>Date</option>
                                    </select>

                                    <div class="mb-3 field-options" style="display:<?= ($fieldType == 'select' || $fieldType == 'radio') ? 'block' : 'none' ?>;">
                                        <label for="field_options_<?= $sectionCount ?>_<?= $fieldIndex ?>" class="form-label">Field Options</label>
                                        <div class="options-container">
                                            <?php
                                            $fieldOptions = json_decode($field['field_options'], true);
                                            if(is_array($fieldOptions)){
                                                foreach ($fieldOptions as $optionIndex => $option):
                                                    $optionLabel = htmlspecialchars($option['label']);
                                                    $optionColor = $option['color'] ?? '#7AB2D3';
                                                    $flagged = isset($option['flagged']) && $option['flagged'] == '1' ? 'checked' : '';
                                                    ?>
                                                    <div class="d-flex align-items-center mb-2">
                                                        <input type="text" class="form-control me-2"
                                                               name="sections[<?= $sectionCount ?>][fields][<?= $fieldIndex ?>][options][]"
                                                               value="<?= $optionLabel ?>"
                                                               placeholder="Option <?= $optionIndex + 1 ?>" required>
                                                        <input type="color" class="form-control form-control-color me-2"
                                                               style="width:80px"
                                                               name="sections[<?= $sectionCount ?>][fields][<?= $fieldIndex ?>][colors][]"
                                                               value="<?= $optionColor ?>"
                                                               title="Choose color">
                                                        <input type="checkbox" class="me-2"
                                                               name="sections[<?= $sectionCount ?>][fields][<?= $fieldIndex ?>][flagged][]"
                                                               value="1" <?= $flagged ?> title="Mark flagged">
                                                        <input type="hidden" class="me-2"
                                                               name="sections[<?= $sectionCount ?>][fields][<?= $fieldIndex ?>][flagged][]"
                                                               value="0">
                                                        <label class="me-3">Flagged</label>
                                                        <button type="button" class="btn btn-danger btn-sm delete-option"
                                                                style="padding: 1px 9px"
                                                                title="Delete Option">x</button>
                                                    </div>
                                                <?php
                                                endforeach;
                                            }
                                            ?>
                                        </div>
                                        <button type="button" class="btn btn-secondary btn-sm add-option"
                                                data-section="<?= $sectionCount ?>"
                                                data-field="<?= $fieldIndex ?>"
                                                title="Add Option">+ Add Option</button>
                                    </div>
                                </div>

                            <?php endif; $fieldCount++; endforeach; ?>
                    </div>
                </div>
                <div class="card-footer">
                    <button type="button" class="btn btn-light add-field" data-section="<?= $sectionCount ?>">Add Field</button>
                </div>
            </div>

            <?php
            $sectionCount++;
        endforeach;
        ?>
    </div>
    <button type="button" class="btn btn-primary" id="addSectionBtn">Add Section</button>
</form>
<div class="mt-4">
    <button type="submit" class="btn btn-success" form="templateForm">Update Template</button>
</div>
