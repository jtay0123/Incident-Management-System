
document.addEventListener("DOMContentLoaded", function () {

    const BASE_URL = window.BASE_URL || "";

    const categoryTable =
        document.getElementById("categoriesTableBody");

    const searchInput =
        document.getElementById("searchCategoryInput");

    const statusFilter =
        document.getElementById("filter-category-status");


    /*
    |--------------------------------------------------------------------------
    | CREATE CATEGORY FORM
    |--------------------------------------------------------------------------
    */

    const createCategoryForm =
        document.getElementById("createCategoryForm");

    const createCategoryModal =
        document.getElementById("createCategoryModal");

    const createDepartmentSelect =
        document.getElementById("department_id");

    const createDefaultPrioritySelect =
        document.getElementById("default_priority");


    /*
    |--------------------------------------------------------------------------
    | EDIT CATEGORY FORM
    |--------------------------------------------------------------------------
    */

    const editCategoryForm =
        document.getElementById("editCategoryForm");

    const editCategoryModal =
        document.getElementById("editCategoryModal");

    const editCategoryIdInput =
        document.getElementById("edit_category_id");

    const editCategoryNameInput =
        document.getElementById("edit_category_name");

    const editDescriptionInput =
        document.getElementById("edit_description");

    const editDepartmentSelect =
        document.getElementById("edit_department_id");

    const editKeywordsInput =
        document.getElementById("edit_keywords");

    const editDefaultPrioritySelect =
        document.getElementById("edit_default_priority");


    /*
    |--------------------------------------------------------------------------
    | CATEGORY DATA
    |--------------------------------------------------------------------------
    */

    let categories = [];

    let departments = [];


    /*
    |--------------------------------------------------------------------------
    | LOAD CATEGORIES
    |--------------------------------------------------------------------------
    */

    function loadCategories() {

        if (!categoryTable) {
            return;
        }

        categoryTable.innerHTML = `
            <tr>
                <td colspan="9" class="text-center py-4 text-muted">
                    Loading categories...
                </td>
            </tr>
        `;

        fetch(
            BASE_URL +
            "/app/controller/CategoryController.php?action=get_categories"
        )

        .then(response => response.json())

        .then(data => {

            if (!data.success) {

                categoryTable.innerHTML = `
                    <tr>
                        <td
                            colspan="9"
                            class="text-center py-4 text-danger"
                        >
                            Unable to load categories.
                        </td>
                    </tr>
                `;

                return;
            }

            categories =
                data.categories || [];

            displayCategories();

        })

        .catch(error => {

            console.error(
                "Error loading categories:",
                error
            );

            categoryTable.innerHTML = `
                <tr>
                    <td
                        colspan="9"
                        class="text-center py-4 text-danger"
                    >
                        Failed to load categories.
                    </td>
                </tr>
            `;

        });

    }


    /*
    |--------------------------------------------------------------------------
    | LOAD DEPARTMENTS
    |--------------------------------------------------------------------------
    */

    function loadDepartments() {

        fetch(
            BASE_URL +
            "/app/controller/CategoryController.php?action=get_departments"
        )

        .then(response => response.json())

        .then(data => {

            if (!data.success) {

                console.error(
                    "Unable to load departments."
                );

                return;
            }

            departments =
                data.departments || [];

            populateDepartmentSelects();

        })

        .catch(error => {

            console.error(
                "Error loading departments:",
                error
            );

        });

    }


    /*
    |--------------------------------------------------------------------------
    | POPULATE DEPARTMENT SELECTS
    |--------------------------------------------------------------------------
    */

    function populateDepartmentSelects() {

        const selects = [
            createDepartmentSelect,
            editDepartmentSelect
        ];

        selects.forEach(select => {

            if (!select) {
                return;
            }

            select.innerHTML = `
                <option value="">
                    Select Department
                </option>
            `;

            departments.forEach(department => {

                const option =
                    document.createElement("option");

                option.value =
                    department.department_id;

                option.textContent =
                    department.department_name;

                select.appendChild(option);

            });

        });

    }


    /*
    |--------------------------------------------------------------------------
    | DISPLAY CATEGORIES
    |--------------------------------------------------------------------------
    */

    function displayCategories() {

        if (!categoryTable) {
            return;
        }

        const searchValue =
            searchInput
                ? searchInput.value
                    .toLowerCase()
                    .trim()
                : "";

        const selectedStatus =
            statusFilter
                ? statusFilter.value
                : "";

        const filteredCategories =
            categories.filter(category => {

                const categoryName =
                    (category.category_name || "")
                        .toLowerCase();

                const description =
                    (category.description || "")
                        .toLowerCase();

                const departmentName =
                    (category.department_name || "")
                        .toLowerCase();

                const keywords =
                    (category.keywords || "")
                        .toLowerCase();

                const matchesSearch =
                    categoryName.includes(searchValue) ||
                    description.includes(searchValue) ||
                    departmentName.includes(searchValue) ||
                    keywords.includes(searchValue);

                const matchesStatus =
                    selectedStatus === "" ||
                    category.status === selectedStatus;

                return (
                    matchesSearch &&
                    matchesStatus
                );

            });

        if (filteredCategories.length === 0) {

            categoryTable.innerHTML = `
                <tr>
                    <td
                        colspan="9"
                        class="text-center py-4 text-muted"
                    >
                        No categories found.
                    </td>
                </tr>
            `;

            return;
        }

        categoryTable.innerHTML = "";

        filteredCategories.forEach(category => {

            const row =
                document.createElement("tr");

            row.innerHTML = `

                <td>
                    ${escapeHTML(category.category_id)}
                </td>

                <td>
                    <strong>
                        ${escapeHTML(
                            category.category_name
                        )}
                    </strong>
                </td>

                <td>
                    ${escapeHTML(
                        category.description || "-"
                    )}
                </td>

                <td>
                    ${escapeHTML(
                        category.department_name || "-"
                    )}
                </td>

                <td>
                    ${formatKeywords(
                        category.keywords
                    )}
                </td>

                <td>
                    ${getPriorityBadge(
                        category.default_priority
                    )}
                </td>

                <td>
                    ${getStatusBadge(
                        category.status
                    )}
                </td>

                <td>
                    ${escapeHTML(
                        category.created_at || "-"
                    )}
                </td>

                <td>

                    <div class="d-flex gap-1">

                        <!-- EDIT -->

                        <button
                            type="button"
                            class="btn btn-sm btn-outline-primary edit-category-btn"
                            data-id="${escapeHTML(category.category_id)}"
                            title="Edit Category"
                        >

                            <i class="bi bi-pencil"></i>

                        </button>

                        <!-- STATUS -->

                        <select
                            class="form-select form-select-sm category-status-select"
                            data-id="${escapeHTML(category.category_id)}"
                            style="width: 120px;"
                        >

                            <option
                                value="Active"
                                ${category.status === "Active"
                                    ? "selected"
                                    : ""}
                            >
                                Active
                            </option>

                            <option
                                value="Inactive"
                                ${category.status === "Inactive"
                                    ? "selected"
                                    : ""}
                            >
                                Inactive
                            </option>

                        </select>

                        <!-- DELETE -->

                        <button
                            type="button"
                            class="btn btn-sm btn-outline-danger delete-category-btn"
                            data-id="${escapeHTML(category.category_id)}"
                            title="Delete Category"
                        >

                            <i class="bi bi-trash"></i>

                        </button>

                    </div>

                </td>

            `;

            categoryTable.appendChild(row);

        });

        attachCategoryActions();

    }


    /*
    |--------------------------------------------------------------------------
    | FORMAT KEYWORDS
    |--------------------------------------------------------------------------
    */

    function formatKeywords(keywords) {

        if (!keywords) {
            return "-";
        }

        const keywordList =
            String(keywords)
                .split(",")
                .map(keyword => keyword.trim())
                .filter(keyword => keyword !== "");

        if (keywordList.length === 0) {
            return "-";
        }

        return keywordList
            .map(keyword => {

                return `
                    <span class="badge bg-light text-dark border me-1 mb-1">
                        ${escapeHTML(keyword)}
                    </span>
                `;

            })
            .join("");

    }


    /*
    |--------------------------------------------------------------------------
    | PRIORITY BADGE
    |--------------------------------------------------------------------------
    */

    function getPriorityBadge(priority) {

        const validPriorities = [
            "Low",
            "Medium",
            "High",
            "Emergency"
        ];

        if (!validPriorities.includes(priority)) {
            priority = "Low";
        }

        let badgeClass = "bg-secondary";

        if (priority === "Low") {
            badgeClass = "bg-success";
        }

        else if (priority === "Medium") {
            badgeClass = "bg-primary";
        }

        else if (priority === "High") {
            badgeClass = "bg-warning text-dark";
        }

        else if (priority === "Emergency") {
            badgeClass = "bg-danger";
        }

        return `
            <span class="badge ${badgeClass}">
                ${escapeHTML(priority)}
            </span>
        `;

    }


    /*
    |--------------------------------------------------------------------------
    | STATUS BADGE
    |--------------------------------------------------------------------------
    */

    function getStatusBadge(status) {

        let badgeClass =
            "bg-secondary";

        if (status === "Active") {
            badgeClass =
                "bg-success";
        }

        else if (status === "Inactive") {
            badgeClass =
                "bg-secondary";
        }

        return `
            <span class="badge ${badgeClass}">
                ${escapeHTML(
                    status || "Unknown"
                )}
            </span>
        `;

    }


    /*
    |--------------------------------------------------------------------------
    | SEARCH
    |--------------------------------------------------------------------------
    */

    if (searchInput) {

        searchInput.addEventListener(
            "input",
            function () {

                displayCategories();

            }
        );

    }


    /*
    |--------------------------------------------------------------------------
    | STATUS FILTER
    |--------------------------------------------------------------------------
    */

    if (statusFilter) {

        statusFilter.addEventListener(
            "change",
            function () {

                displayCategories();

            }
        );

    }


    /*
    |--------------------------------------------------------------------------
    | CATEGORY ACTIONS
    |--------------------------------------------------------------------------
    */

    function attachCategoryActions() {

        /*
        |----------------------------------------------------------------------
        | EDIT CATEGORY
        |----------------------------------------------------------------------
        */

        document
            .querySelectorAll(".edit-category-btn")
            .forEach(button => {

                button.addEventListener(
                    "click",
                    function () {

                        const categoryId =
                            this.dataset.id;

                        loadEditCategory(
                            categoryId
                        );

                    }
                );

            });


        /*
        |----------------------------------------------------------------------
        | CHANGE STATUS
        |----------------------------------------------------------------------
        */

        document
            .querySelectorAll(".category-status-select")
            .forEach(select => {

                select.addEventListener(
                    "change",
                    function () {

                        const categoryId =
                            this.dataset.id;

                        const newStatus =
                            this.value;

                        updateCategoryStatus(
                            categoryId,
                            newStatus
                        );

                    }
                );

            });


        /*
        |----------------------------------------------------------------------
        | DELETE CATEGORY
        |----------------------------------------------------------------------
        */

        document
            .querySelectorAll(".delete-category-btn")
            .forEach(button => {

                button.addEventListener(
                    "click",
                    function () {

                        const categoryId =
                            this.dataset.id;

                        const confirmed =
                            confirm(
                                "Are you sure you want to delete this category?"
                            );

                        if (!confirmed) {
                            return;
                        }

                        deleteCategory(
                            categoryId
                        );

                    }
                );

            });

    }


    /*
    |--------------------------------------------------------------------------
    | LOAD EDIT CATEGORY
    |--------------------------------------------------------------------------
    */

    function loadEditCategory(categoryId) {

        fetch(
            BASE_URL +
            "/app/controller/CategoryController.php?action=get_category&category_id=" +
            encodeURIComponent(categoryId)
        )

        .then(response => response.json())

        .then(data => {

            if (!data.success) {

                alert(
                    data.message ||
                    "Unable to load category information."
                );

                return;
            }

            const category =
                data.category;

            if (editCategoryIdInput) {

                editCategoryIdInput.value =
                    category.category_id;

            }

            if (editCategoryNameInput) {

                editCategoryNameInput.value =
                    category.category_name || "";

            }

            if (editDescriptionInput) {

                editDescriptionInput.value =
                    category.description || "";

            }

            if (editDepartmentSelect) {

                editDepartmentSelect.value =
                    category.department_id || "";

            }

            if (editKeywordsInput) {

                editKeywordsInput.value =
                    category.keywords || "";

            }

            // Load the category's saved default priority.
            if (editDefaultPrioritySelect) {

                editDefaultPrioritySelect.value =
                    category.default_priority || "Low";

            }

            if (editCategoryModal) {

                const modal =
                    bootstrap.Modal.getOrCreateInstance(
                        editCategoryModal
                    );

                modal.show();

            }

        })

        .catch(error => {

            console.error(
                "Error loading category:",
                error
            );

            alert(
                "Something went wrong while loading the category."
            );

        });

    }


    /*
    |--------------------------------------------------------------------------
    | UPDATE CATEGORY STATUS
    |--------------------------------------------------------------------------
    */

    function updateCategoryStatus(
        categoryId,
        newStatus
    ) {

        const formData =
            new FormData();

        formData.append(
            "action",
            "updateStatus"
        );

        formData.append(
            "category_id",
            categoryId
        );

        formData.append(
            "status",
            newStatus
        );

        fetch(
            BASE_URL +
            "/app/controller/CategoryController.php",
            {
                method: "POST",
                body: formData
            }
        )

        .then(response => response.json())

        .then(data => {

            if (data.success) {

                loadCategories();

            }

            else {

                alert(
                    data.message ||
                    "Unable to update category status."
                );

            }

        })

        .catch(error => {

            console.error(
                "Error updating category status:",
                error
            );

            alert(
                "Something went wrong while updating category status."
            );

        });

    }


    /*
    |--------------------------------------------------------------------------
    | DELETE CATEGORY
    |--------------------------------------------------------------------------
    */

    function deleteCategory(categoryId) {

        const formData =
            new FormData();

        formData.append(
            "action",
            "delete"
        );

        formData.append(
            "category_id",
            categoryId
        );

        fetch(
            BASE_URL +
            "/app/controller/CategoryController.php",
            {
                method: "POST",
                body: formData
            }
        )

        .then(response => response.json())

        .then(data => {

            if (data.success) {

                loadCategories();

            }

            else {

                alert(
                    data.message ||
                    "Unable to delete category."
                );

            }

        })

        .catch(error => {

            console.error(
                "Error deleting category:",
                error
            );

            alert(
                "Something went wrong while deleting the category."
            );

        });

    }


    /*
    |--------------------------------------------------------------------------
    | CREATE CATEGORY FORM
    |--------------------------------------------------------------------------
    */

    if (createCategoryForm) {

        createCategoryForm.addEventListener(
            "submit",
            function (event) {

                const categoryName =
                    document
                        .getElementById("category_name")
                        .value
                        .trim();

                const departmentId =
                    document
                        .getElementById("department_id")
                        .value;

                const defaultPriority =
                    createDefaultPrioritySelect
                        ? createDefaultPrioritySelect.value
                        : "";

                if (!categoryName) {

                    event.preventDefault();

                    alert(
                        "Please enter the category name."
                    );

                    return;

                }

                if (!departmentId) {

                    event.preventDefault();

                    alert(
                        "Please select a department."
                    );

                    return;

                }

                if (
                    !defaultPriority ||
                    ![
                        "Low",
                        "Medium",
                        "High",
                        "Emergency"
                    ].includes(defaultPriority)
                ) {

                    event.preventDefault();

                    alert(
                        "Please select a valid default priority."
                    );

                    return;

                }

                // The normal form submission includes default_priority
                // because the select has name="default_priority".

            }
        );

    }


    /*
    |--------------------------------------------------------------------------
    | EDIT CATEGORY FORM
    |--------------------------------------------------------------------------
    */

    if (editCategoryForm) {

        editCategoryForm.addEventListener(
            "submit",
            function (event) {

                event.preventDefault();

                const categoryId =
                    editCategoryIdInput.value.trim();

                const categoryName =
                    editCategoryNameInput.value.trim();

                const description =
                    editDescriptionInput.value.trim();

                const departmentId =
                    editDepartmentSelect.value;

                const keywords =
                    editKeywordsInput.value.trim();

                const defaultPriority =
                    editDefaultPrioritySelect
                        ? editDefaultPrioritySelect.value
                        : "";

                if (!categoryId) {

                    alert(
                        "Invalid category ID."
                    );

                    return;

                }

                if (!categoryName) {

                    alert(
                        "Please enter the category name."
                    );

                    return;

                }

                if (!departmentId) {

                    alert(
                        "Please select a department."
                    );

                    return;

                }

                if (
                    !defaultPriority ||
                    ![
                        "Low",
                        "Medium",
                        "High",
                        "Emergency"
                    ].includes(defaultPriority)
                ) {

                    alert(
                        "Please select a valid default priority."
                    );

                    return;

                }

                const formData =
                    new FormData();

                formData.append(
                    "action",
                    "update_category"
                );

                formData.append(
                    "category_id",
                    categoryId
                );

                formData.append(
                    "category_name",
                    categoryName
                );

                formData.append(
                    "description",
                    description
                );

                formData.append(
                    "department_id",
                    departmentId
                );

                formData.append(
                    "keywords",
                    keywords
                );

                // Send the selected default priority to the controller.
                formData.append(
                    "default_priority",
                    defaultPriority
                );

                fetch(
                    BASE_URL +
                    "/app/controller/CategoryController.php",
                    {
                        method: "POST",
                        body: formData
                    }
                )

                .then(async response => {

                    const text =
                        await response.text();

                    console.log(
                        "UPDATE CATEGORY RESPONSE:",
                        text
                    );

                    try {

                        return JSON.parse(text);

                    }

                    catch (error) {

                        console.error(
                            "INVALID JSON RESPONSE:",
                            text
                        );

                        throw error;

                    }

                })

                .then(data => {

                    if (data.success) {

                        if (editCategoryModal) {

                            const modal =
                                bootstrap.Modal.getInstance(
                                    editCategoryModal
                                );

                            if (modal) {

                                modal.hide();

                            }

                        }

                        loadCategories();

                        alert(
                            data.message ||
                            "Category updated successfully."
                        );

                    }

                    else {

                        alert(
                            data.message ||
                            "Unable to update category."
                        );

                    }

                })

                .catch(error => {

                    console.error(
                        "Error updating category:",
                        error
                    );

                    alert(
                        "Something went wrong while updating the category."
                    );

                });

            }
        );

    }


    /*
    |--------------------------------------------------------------------------
    | RESET CREATE CATEGORY FORM
    |--------------------------------------------------------------------------
    */

    if (createCategoryModal) {

        createCategoryModal.addEventListener(
            "hidden.bs.modal",
            function () {

                if (createCategoryForm) {

                    createCategoryForm.reset();

                }

            }
        );

    }


    /*
    |--------------------------------------------------------------------------
    | ESCAPE HTML
    |--------------------------------------------------------------------------
    */

    function escapeHTML(value) {

        if (
            value === null ||
            value === undefined
        ) {

            return "";

        }

        return String(value)

            .replace(
                /&/g,
                "&amp;"
            )

            .replace(
                /</g,
                "&lt;"
            )

            .replace(
                />/g,
                "&gt;"
            )

            .replace(
                /"/g,
                "&quot;"
            )

            .replace(
                /'/g,
                "&#039;"
            );

    }


    /*
    |--------------------------------------------------------------------------
    | INITIAL LOAD
    |--------------------------------------------------------------------------
    */

    loadDepartments();

    loadCategories();

});
