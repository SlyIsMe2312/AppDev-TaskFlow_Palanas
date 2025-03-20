<div class="modal fade" id="addTaskModal" data-bs-backdrop="static" tabindex="-1" >
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Add New Task</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="taskForm" autocomplete="off">
                    <div class="mb-3">
                        <label for="taskTitle" class="form-label">Task Title</label>
                        <input type="text" id="taskTitle" name="taskTitle" class="form-control" required>
                    </div>
                    <div class="d-flex justify-content-center">
                        <button type="submit" class="btn btn-success">
                            Add Task
                            <span class="spinner-border spinner-border-sm ms-2 d-none" role="status"></span>
                        </button>
                    </div>

                </form>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="addCategoryModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Add New Category</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="categoryForm" autocomplete="off">
                    <div class="mb-3">
                        <label for="categoryName" class="form-label">Category Name</label>
                        <input type="text" id="categoryName" name="categoryName" class="form-control" required>
                    </div>
                    <div class="d-flex justify-content-center">
                        <button type="submit" class="btn btn-success">
                            Add Category
                            <span class="spinner-border spinner-border-sm ms-2 d-none" role="status"></span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="addSubTaskModal" data-bs-backdrop="static" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Add New Subtask</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="subtaskForm" autocomplete="off">
                    <!-- Hidden Fields -->
                    <input type="hidden" id="parentTaskId" name="parentTaskId">
                    <input type="hidden" id="parentTaskTitle" name="parentTaskTitle">
                    <input type="hidden" id="aiGenerate" name="ai_generate" value="0">

                    <!-- AI Generate Toggle -->
                    <div class="form-check form-switch mb-3">
                        <input class="form-check-input" type="checkbox" id="aiToggle">
                        <label class="form-check-label" for="aiToggle">AI Generate Subtasks</label>
                    </div>

                    <!-- Subtask Title / Number of Subtasks -->
                    <div class="mb-3">
                        <label id="subtaskLabel" for="subtaskTitle" class="form-label">Subtask Title</label>
                        <input type="text" id="subtaskTitle" name="subtaskTitle" class="form-control" required>
                    </div>
                    <div class="d-flex justify-content-center">
                        <button type="submit" class="btn btn-success">
                            Add Subtask
                            <span class="spinner-border spinner-border-sm ms-2 d-none" role="status"></span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>