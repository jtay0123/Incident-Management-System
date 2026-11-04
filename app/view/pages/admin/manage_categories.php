
<div class="container-fluid">

    <!-- ============================= -->
    <!-- PAGE HEADER -->
    <!-- ============================= -->

    <div class="d-flex justify-content-between align-items-center mb-4">

        <div>
            <h2>Manage Categories</h2>

            <p class="text-muted mb-0">
            <!--    Manage incident categories and their assigned departments. -->
            </p>
        </div>

        <button
            class="btn btn-primary"
            data-bs-toggle="modal"
            data-bs-target="#createCategoryModal"
        >
            <i class="bi bi-tags-fill me-1"></i>
            Add Category
        </button>

    </div>


    <!-- ============================= -->
    <!-- SEARCH AND FILTER -->
    <!-- ============================= -->

    <div class="card shadow-sm border-0 mb-4">

        <div class="card-body">

            <div class="row align-items-center">

                <div class="col-lg-6 col-md-7 mb-3 mb-lg-0">

                    <div class="input-group">

                        <span class="input-group-text bg-white">
                            <i class="bi bi-search text-muted"></i>
                        </span>

                        <input
                            type="text"
                            id="searchCategoryInput"
                            class="form-control"
                            placeholder="Search category or department"
                        >

                    </div>

                </div>

                <div class="col-lg-3 col-md-5">

                    <select
                        id="filter-category-status"
                        class="form-select"
                    >
                        <option value="">All Status</option>
                        <option value="Active">Active</option>
                        <option value="Inactive">Inactive</option>
                    </select>

                </div>

            </div>

        </div>

    </div>


    <!-- ============================= -->
    <!-- CATEGORY TABLE -->
    <!-- ============================= -->

    <div class="card shadow-sm border-0">

        <div class="card-header bg-white">
            <h5 class="mb-0">Incident Categories</h5>
        </div>

        <div class="card-body p-0">

            <div class="table-responsive">

                <table
                    class="table table-hover align-middle mb-0"
                    id="categoriesTable"
                >

                    <thead class="table-light">
                        <tr>
                            <th>ID</th>
                            <th>Category</th>
                            <th>Description</th>
                            <th>Department</th>
                            <th>Keywords</th>
                            <th>Default Priority</th>
                            <th>Status</th>
                            <th>Created At</th>
                            <th class="text-center">Actions</th>
                        </tr>
                    </thead>

                    <tbody id="categoriesTableBody">
                        <tr>
                            <td
                                colspan="9"
                                class="text-center py-4 text-muted"
                            >
                                Loading categories...
                            </td>
                        </tr>
                    </tbody>

                </table>

            </div>

        </div>

    </div>

</div>


<!-- ===================================================== -->
<!-- CREATE CATEGORY MODAL -->
<!-- ===================================================== -->

<div
    class="modal fade"
    id="createCategoryModal"
    tabindex="-1"
    aria-hidden="true"
>

    <div class="modal-dialog">

        <div class="modal-content">

            <div class="modal-header">

                <h5 class="modal-title">
                    Add Incident Category
                </h5>

                <button
                    type="button"
                    class="btn-close"
                    data-bs-dismiss="modal"
                ></button>

            </div>

            <form
                id="createCategoryForm"
                action="<?= BASE_URL ?>/app/controller/CategoryController.php?action=create_category"
                method="POST"
            >

                <div class="modal-body">

                    <!-- CATEGORY NAME -->
                    <div class="mb-3">

                        <label
                            for="category_name"
                            class="form-label"
                        >
                            Category Name
                        </label>

                        <input
                            type="text"
                            class="form-control"
                            id="category_name"
                            name="category_name"
                            maxlength="100"
                            placeholder="Example: Air Conditioning"
                            required
                        >

                    </div>


                    <!-- DESCRIPTION -->
                    <div class="mb-3">

                        <label
                            for="description"
                            class="form-label"
                        >
                            Description
                        </label>

                        <textarea
                            class="form-control"
                            id="description"
                            name="description"
                            rows="3"
                            placeholder="Describe the type of incidents under this category"
                        ></textarea>

                    </div>


                    <!-- DEPARTMENT -->
                    <div class="mb-3">

                        <label
                            for="department_id"
                            class="form-label"
                        >
                            Department
                        </label>

                        <select
                            class="form-select"
                            id="department_id"
                            name="department_id"
                            required
                        >
                            <option value="">Select Department</option>
                        </select>

                    </div>


                    <!-- DEFAULT PRIORITY -->
                    <div class="mb-3">

                        <label
                            for="default_priority"
                            class="form-label"
                        >
                            Default Priority
                        </label>

                        <select
                            class="form-select"
                            id="default_priority"
                            name="default_priority"
                            required
                        >
                            <option value="Low">Low</option>
                            <option value="Medium" selected>Medium</option>
                            <option value="High">High</option>
                            <option value="Emergency">Emergency</option>
                        </select>

                        <small class="text-muted">
                            Sets the initial priority for new incidents in this category.
                        </small>

                    </div>


                    <!-- KEYWORDS -->
                    <div class="mb-3">

                        <label
                            for="keywords"
                            class="form-label"
                        >
                            Keywords
                        </label>

                        <textarea
                            class="form-control"
                            id="keywords"
                            name="keywords"
                            rows="3"
                            placeholder="Example: aircon, air conditioner, not cold, warm, cooling"
                        ></textarea>

                        <small class="text-muted">
                            Separate keywords with commas. These keywords
                            are used for automatic incident categorisation.
                        </small>

                    </div>


                    <div class="alert alert-info">

                        <i class="bi bi-info-circle me-1"></i>

                        Keywords help the system identify the most suitable
                        category when a user submits an incident.

                    </div>

                </div>


                <div class="modal-footer">

                    <button
                        type="button"
                        class="btn btn-secondary"
                        data-bs-dismiss="modal"
                    >
                        Cancel
                    </button>

                    <button
                        type="submit"
                        class="btn btn-primary"
                    >
                        <i class="bi bi-plus-lg me-1"></i>
                        Add Category
                    </button>

                </div>

            </form>

        </div>

    </div>

</div>


<!-- ===================================================== -->
<!-- EDIT CATEGORY MODAL -->
<!-- ===================================================== -->

<div
    class="modal fade"
    id="editCategoryModal"
    tabindex="-1"
    aria-hidden="true"
>

    <div class="modal-dialog">

        <div class="modal-content">

            <div class="modal-header">

                <h5 class="modal-title">
                    Edit Category
                </h5>

                <button
                    type="button"
                    class="btn-close"
                    data-bs-dismiss="modal"
                ></button>

            </div>

            <form id="editCategoryForm">

                <div class="modal-body">

                    <!-- CATEGORY ID -->
                    <input
                        type="hidden"
                        id="edit_category_id"
                        name="category_id"
                    >


                    <!-- CATEGORY NAME -->
                    <div class="mb-3">

                        <label
                            for="edit_category_name"
                            class="form-label"
                        >
                            Category Name
                        </label>

                        <input
                            type="text"
                            class="form-control"
                            id="edit_category_name"
                            name="category_name"
                            maxlength="100"
                            required
                        >

                    </div>


                    <!-- DESCRIPTION -->
                    <div class="mb-3">

                        <label
                            for="edit_description"
                            class="form-label"
                        >
                            Description
                        </label>

                        <textarea
                            class="form-control"
                            id="edit_description"
                            name="description"
                            rows="3"
                        ></textarea>

                    </div>


                    <!-- DEPARTMENT -->
                    <div class="mb-3">

                        <label
                            for="edit_department_id"
                            class="form-label"
                        >
                            Department
                        </label>

                        <select
                            class="form-select"
                            id="edit_department_id"
                            name="department_id"
                            required
                        >
                            <option value="">Select Department</option>
                        </select>

                    </div>


                    <!-- DEFAULT PRIORITY -->
                    <div class="mb-3">

                        <label
                            for="edit_default_priority"
                            class="form-label"
                        >
                            Default Priority
                        </label>

                        <select
                            class="form-select"
                            id="edit_default_priority"
                            name="default_priority"
                            required
                        >
                            <option value="Low">Low</option>
                            <option value="Medium">Medium</option>
                            <option value="High">High</option>
                            <option value="Emergency">Emergency</option>
                        </select>

                        <small class="text-muted">
                            Changes the default priority for future incidents.
                            Existing incidents keep their current priority.
                        </small>

                    </div>


                    <!-- KEYWORDS -->
                    <div class="mb-3">

                        <label
                            for="edit_keywords"
                            class="form-label"
                        >
                            Keywords
                        </label>

                        <textarea
                            class="form-control"
                            id="edit_keywords"
                            name="keywords"
                            rows="3"
                            placeholder="Example: aircon, air conditioner, not cold, warm, cooling"
                        ></textarea>

                        <small class="text-muted">
                            Separate keywords with commas.
                        </small>

                    </div>

                </div>


                <div class="modal-footer">

                    <button
                        type="button"
                        class="btn btn-secondary"
                        data-bs-dismiss="modal"
                    >
                        Cancel
                    </button>

                    <button
                        type="submit"
                        class="btn btn-primary"
                    >
                        <i class="bi bi-check-lg me-1"></i>
                        Save Changes
                    </button>

                </div>

            </form>

        </div>

    </div>

</div>


<!-- ============================= -->
<!-- BASE URL -->
<!-- ============================= -->

<script>
    window.BASE_URL = "<?= BASE_URL ?>";
</script>
