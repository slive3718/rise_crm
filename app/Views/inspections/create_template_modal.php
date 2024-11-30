

<!-- Modal -->
<div class="modal fade" id="InspectionTemplateCreateModal" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="exampleModalLabel">Create Inspection Template</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form action="" method="post" id="templateForm">
                    <div class="mb-4">
                        <label for="template_name" class="form-label">Template Name</label>
                        <input type="text" class="form-control" id="template_name" name="template_name" placeholder="Enter template name" required>
                        <input type="hidden" class="form-control" id="template_id" name="template_id" value="">
                    </div>
                    <div id="sections" class="sortable-list"></div>
                    <button type="button" class="btn btn-primary" id="addSectionBtn">Add Section</button>
                </form>
                <div class="mt-4">
                    <button type="submit" class="btn btn-success" form="templateForm">Save Template</button>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

