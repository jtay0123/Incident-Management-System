document.addEventListener("DOMContentLoaded", function () {

    const BASE_URL = window.BASE_URL || "";

    const userTable = document.getElementById("userAccountsTable");
    const searchInput = document.getElementById("searchInput");
    const userTypeFilter = document.getElementById("filter-user-type");
    const statusFilter = document.getElementById("filter-status");

    const userTypeInput = document.getElementById("user_type");
    const roleInput = document.getElementById("role");
    const departmentInput = document.getElementById("department_id");

    const createUserForm = document.getElementById("createUserForm");
    const editUserForm = document.getElementById("editUserForm");
    const editUserModal = document.getElementById("editUserModal");

    const editUserIdInput = document.getElementById("edit_user_id");
    const editFullNameInput = document.getElementById("edit_full_name");
    const editEmailInput = document.getElementById("edit_email");
    const editUserTypeInput = document.getElementById("edit_user_type");
    const editRoleInput = document.getElementById("edit_role");
    const editDepartmentInput = document.getElementById("edit_department_id");

    /*
    |--------------------------------------------------------------------------
    | USER TYPE → ROLE
    |--------------------------------------------------------------------------
    | Department is NOT automatically assigned.
    | Admin selects the department manually.
    */

    const userTypeRoleMapping = {

        "Lecturer": "Reporter",

        "Maintenance": "Response Staff",

        "IT Support": "Response Staff",

        "Cleaning": "Response Staff",

        "Security": "Response Staff"

    };


    /*
    |--------------------------------------------------------------------------
    | AUTO SET ROLE
    |--------------------------------------------------------------------------
    */

    if (userTypeInput) {

        userTypeInput.addEventListener("change", function () {

            const selectedType = this.value;

            const role = userTypeRoleMapping[selectedType];


            if (roleInput) {

                if (role) {
                    roleInput.value = role;
                } else {
                    roleInput.value = "";
                }

            }

        });

    }

       /*
      |--------------------------------------------------------------------------
     | AUTO SET EDIT USER ROLE
     |--------------------------------------------------------------------------
    */

    if (editUserTypeInput) {

         editUserTypeInput.addEventListener(
               "change",
             function () {

                 const selectedType = this.value;

                 const role =
                    userTypeRoleMapping[selectedType];

                 if (editRoleInput) {

                     editRoleInput.value =
                         role || "";

                }

            }
        );

    }       

    /*
    |--------------------------------------------------------------------------
    | LOAD DEPARTMENTS
    |--------------------------------------------------------------------------
    */

    function loadDepartments() {

        if (!departmentInput && !editDepartmentInput) {
            return;
        }


        fetch(
            BASE_URL +
            "/app/controller/AdminController.php?action=get_departments"
        )

        .then(response => response.json())

        .then(data => {

            if (!data.success) {

                console.error(
                    "Unable to load departments:",
                    data.message
                );

                return;

            }


            /*
            |------------------------------------------------------------------
            | Create User Department
            |------------------------------------------------------------------
            */

            if (departmentInput) {

                departmentInput.innerHTML = `
                    <option value="" disabled selected>
                        Select Department
                    </option>
                `;

            }


            /*
            |------------------------------------------------------------------
            | Edit User Department
            |------------------------------------------------------------------
            */

            if (editDepartmentInput) {

                editDepartmentInput.innerHTML = `
                    <option value="" disabled selected>
                        Select Department
                    </option>
                `;

            }


            /*
            |------------------------------------------------------------------
            | Add Active Departments
            |------------------------------------------------------------------
            */

            data.departments.forEach(department => {

                /*
                |--------------------------------------------------------------
                | Create User
                |--------------------------------------------------------------
                */

                if (departmentInput) {

                    const option =
                        document.createElement("option");

                    option.value =
                        department.department_id;

                    option.textContent =
                        department.department_name;

                    departmentInput.appendChild(option);

                }


                /*
                |--------------------------------------------------------------
                | Edit User
                |--------------------------------------------------------------
                */

                if (editDepartmentInput) {

                    const option =
                        document.createElement("option");

                    option.value =
                        department.department_id;

                    option.textContent =
                        department.department_name;

                    editDepartmentInput.appendChild(option);

                }

            });

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
    | LOAD USERS
    |--------------------------------------------------------------------------
    */

    function loadUsers() {

        if (!userTable) {
            return;
        }


        userTable.innerHTML = `
            <tr>
                <td colspan="9" class="text-center">
                    Loading users...
                </td>
            </tr>
        `;


        fetch(
            BASE_URL +
            "/app/controller/AdminController.php?action=get_users"
        )

        .then(response => response.json())

        .then(data => {

            if (data.success) {

                displayUsers(data.users);

                updateStats(data.users);

            } else {

                userTable.innerHTML = `
                    <tr>
                        <td
                            colspan="9"
                            class="text-center text-danger"
                        >
                            Unable to load users.
                        </td>
                    </tr>
                `;

            }

        })

        .catch(error => {

            console.error(
                "Error loading users:",
                error
            );


            userTable.innerHTML = `
                <tr>
                    <td
                        colspan="9"
                        class="text-center text-danger"
                    >
                        Failed to load users.
                    </td>
                </tr>
            `;

        });

    }


    /*
    |--------------------------------------------------------------------------
    | DISPLAY USERS
    |--------------------------------------------------------------------------
    */

    function displayUsers(users) {

        if (!userTable) {
            return;
        }


        const searchValue =
            searchInput
                ? searchInput.value.toLowerCase().trim()
                : "";


        const selectedUserType =
            userTypeFilter
                ? userTypeFilter.value
                : "";


        const selectedStatus =
            statusFilter
                ? statusFilter.value
                : "";


        const filteredUsers = users.filter(user => {

            const name =
                (user.full_name || "").toLowerCase();


            const email =
                (user.email || "").toLowerCase();


            const matchesSearch =
                name.includes(searchValue) ||
                email.includes(searchValue);


            const matchesUserType =
                selectedUserType === "" ||
                user.user_type === selectedUserType;


            const matchesStatus =
                selectedStatus === "" ||
                user.account_status === selectedStatus;


            return (
                matchesSearch &&
                matchesUserType &&
                matchesStatus
            );

        });


        if (filteredUsers.length === 0) {

            userTable.innerHTML = `
                <tr>
                    <td
                        colspan="9"
                        class="text-center text-muted"
                    >
                        No users found.
                    </td>
                </tr>
            `;

            return;

        }


        userTable.innerHTML = "";


        filteredUsers.forEach(user => {

            const row = document.createElement("tr");


            row.innerHTML = `

                <td>
                    ${escapeHTML(user.user_id)}
                </td>

                <td>
                    ${escapeHTML(user.full_name)}
                </td>

                <td>
                    ${escapeHTML(user.email)}
                </td>

                <td>
                    ${escapeHTML(user.role)}
                </td>

                <td>
                    ${escapeHTML(user.user_type)}
                </td>

                <td>
                    ${escapeHTML(
                        user.department || "Not Assigned"
                    )}
                </td>

                <td>
                    ${getStatusBadge(user.account_status)}
                </td>

                <td>
                    ${escapeHTML(
                        user.created_at || "-"
                    )}
                </td>

                <td>

                    <div class="d-flex gap-1">

                        <button
                            type="button"
                            class="btn btn-sm btn-outline-primary edit-user-btn"
                            data-id="${user.user_id}"
                            data-bs-toggle="modal"
                            data-bs-target="#editUserModal"
                            title="Edit User"
                        >

                            <i class="bi bi-pencil"></i>

                        </button>


                        <select
                            class="form-select form-select-sm status-select"
                            data-id="${user.user_id}"
                            style="width: 130px;"
                        >
                            <option value="Active"
                                ${user.account_status === "Active" ? "selected" : ""}>
                                Active
                            </option>

                            <option value="Inactive"
                                ${user.account_status === "Inactive" ? "selected" : ""}>
                                Inactive
                            </option>

                            <option value="Suspended"
                                ${user.account_status === "Suspended" ? "selected" : ""}>
                                Suspended
                            </option>
                        </select>


                        <button
                            type="button"
                            class="btn btn-sm btn-outline-danger delete-user-btn"
                            data-id="${user.user_id}"
                            title="Delete User"
                        >

                            <i class="bi bi-trash"></i>

                        </button>

                    </div>

                </td>

            `;


            userTable.appendChild(row);

        });


        attachUserActions();

    }


    /*
    |--------------------------------------------------------------------------
    | STATUS BADGE
    |--------------------------------------------------------------------------
    */

    function getStatusBadge(status) {

        let badgeClass = "bg-secondary";


        if (status === "Active") {

            badgeClass = "bg-success";

        }

        else if (status === "Inactive") {

            badgeClass = "bg-secondary";

        }

        else if (status === "Suspended") {

            badgeClass = "bg-danger";

        }


        return `
            <span class="badge ${badgeClass}">
                ${escapeHTML(status || "Unknown")}
            </span>
        `;

    }


    /*
    |--------------------------------------------------------------------------
    | UPDATE STATISTICS
    |--------------------------------------------------------------------------
    */

    function updateStats(users) {

        const totalUsers =
            document.getElementById("totalUsers");


        const activeUsers =
            document.getElementById("activeUsers");


        const inactiveUsers =
            document.getElementById("inactiveUsers");


        const suspendedUsers =
            document.getElementById("suspendedUsers");


        const total = users.length;


        const active =
            users.filter(user =>
                user.account_status === "Active"
            ).length;


        const inactive =
            users.filter(user =>
                user.account_status === "Inactive"
            ).length;


        const suspended =
            users.filter(user =>
                user.account_status === "Suspended"
            ).length;


        if (totalUsers) {

            totalUsers.textContent = total;

        }


        if (activeUsers) {

            activeUsers.textContent = active;

        }


        if (inactiveUsers) {

            inactiveUsers.textContent = inactive;

        }


        if (suspendedUsers) {

            suspendedUsers.textContent = suspended;

        }

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

                loadUsers();

            }
        );

    }


    /*
    |--------------------------------------------------------------------------
    | FILTER USER TYPE
    |--------------------------------------------------------------------------
    */

    if (userTypeFilter) {

        userTypeFilter.addEventListener(
            "change",
            function () {

                loadUsers();

            }
        );

    }


    /*
    |--------------------------------------------------------------------------
    | FILTER STATUS
    |--------------------------------------------------------------------------
    */

    if (statusFilter) {

        statusFilter.addEventListener(
            "change",
            function () {

                loadUsers();

            }
        );

    }


    /*
    |--------------------------------------------------------------------------
    | USER ACTIONS
    |--------------------------------------------------------------------------
    */

    function attachUserActions() {

       
        /*
    |--------------------------------------------------------------------------
    | EDIT USER
    |--------------------------------------------------------------------------
    */

    document
    .querySelectorAll(".edit-user-btn")
    .forEach(button => {

        button.addEventListener(
            "click",
            function () {

                const userId = this.dataset.id;

                loadEditUser(userId);

            }
        );

    });


        /*
|--------------------------------------------------------------------------
| CHANGE STATUS
|--------------------------------------------------------------------------
*/

document
    .querySelectorAll(".status-select")
    .forEach(select => {

        select.addEventListener(
            "change",
            function () {

                const userId =
                    this.dataset.id;

                const newStatus =
                    this.value;

                updateUserStatus(
                    userId,
                    newStatus
                );

            }
        );

    });

        /*
        |--------------------------------------------------------------------------
        | DELETE USER
        |--------------------------------------------------------------------------
        */

        document
            .querySelectorAll(".delete-user-btn")
            .forEach(button => {

                button.addEventListener(
                    "click",
                    function () {

                        const userId =
                            this.dataset.id;


                        const confirmed =
                            confirm(
                                "Are you sure you want to delete this user?"
                            );


                        if (!confirmed) {

                            return;

                        }


                        deleteUser(userId);

                    }
                );

            });

    }


    /*
    |--------------------------------------------------------------------------
    | UPDATE USER STATUS
    |--------------------------------------------------------------------------
    */
/*
|--------------------------------------------------------------------------
| OPEN EDIT USER
|--------------------------------------------------------------------------
*/

function loadEditUser(userId) {

    fetch(
        BASE_URL +
        "/app/controller/AdminController.php?action=get_user&user_id=" +
        encodeURIComponent(userId)
    )

    .then(response => response.json())

    .then(data => {

        if (!data.success) {

            alert(
                data.message ||
                "Unable to load user information."
            );

            return;

        }

        const user = data.user;


        /*
        |------------------------------------------------------------------
        | Fill Edit Form
        |------------------------------------------------------------------
        */

        if (editUserIdInput) {
            editUserIdInput.value = user.user_id;
        }

        if (editFullNameInput) {
            editFullNameInput.value = user.full_name || "";
        }

        if (editEmailInput) {
            editEmailInput.value = user.email || "";
        }

        if (editUserTypeInput) {
            editUserTypeInput.value = user.user_type || "";
        }



        if (editRoleInput) {
            editRoleInput.value =
                userTypeRoleMapping[user.user_type] || "";
        }

        if (editDepartmentInput) {
            editDepartmentInput.value =
                user.department_id || "";
        }


        /*
        |------------------------------------------------------------------
        | Show Bootstrap Modal
        |------------------------------------------------------------------
        */

        if (editUserModal) {

            const modal =
                bootstrap.Modal.getOrCreateInstance(
                    editUserModal
                );

            modal.show();

        }

    })

    .catch(error => {

        console.error(
            "Error loading user:",
            error
        );

        alert(
            "Something went wrong while loading the user."
        );

    });

}

 function updateUserStatus(userId, newStatus) {

    const formData = new FormData();

    formData.append("action", "updateStatus");
    formData.append("user_id", userId);
    formData.append("status", newStatus);

    fetch(
        BASE_URL +
        "/app/controller/AdminController.php",
        {
            method: "POST",
            body: formData
        }
    )

    .then(response => response.json())

    .then(data => {

        if (data.success) {

            loadUsers();

        } else {

            alert(
                data.message ||
                "Unable to update user status."
            );

        }

    })

    .catch(error => {

        console.error(
            "Error updating user status:",
            error
        );

        alert(
            "Something went wrong while updating user status."
        );

    });

}


    /*
    |--------------------------------------------------------------------------
    | DELETE USER
    |--------------------------------------------------------------------------
    */

    function deleteUser(userId) {

        const formData =
            new FormData();


        formData.append(
            "action",
            "delete"
        );


        formData.append(
            "user_id",
            userId
        );


        fetch(
            BASE_URL +
            "/app/controller/AdminController.php",
            {
                method: "POST",
                body: formData
            }
        )

        .then(response =>
            response.json()
        )

        .then(data => {

            if (data.success) {

                loadUsers();

            }

            else {

                alert(
                    data.message ||
                    "Unable to delete user."
                );

            }

        })

        .catch(error => {

            console.error(
                "Error deleting user:",
                error
            );


            alert(
                "Something went wrong while deleting the user."
            );

        });

    }


    /*
    |--------------------------------------------------------------------------
    | CREATE USER FORM
    |--------------------------------------------------------------------------
    */

    if (createUserForm) {

        createUserForm.addEventListener(
            "submit",
            function (event) {


                /*
                |------------------------------------------------------------------
                | Validate User Type
                |------------------------------------------------------------------
                */

                if (
                    !userTypeInput ||
                    !roleInput ||
                    !departmentInput
                ) {

                    return;

                }


                /*
                |------------------------------------------------------------------
                | User Type must be selected
                |------------------------------------------------------------------
                */

                if (
                    userTypeInput.value === ""
                ) {

                    event.preventDefault();


                    alert(
                        "Please select a User Type."
                    );


                    return;

                }


                /*
                |------------------------------------------------------------------
                | Role must be automatically assigned
                |------------------------------------------------------------------
                */

                if (
                    roleInput.value === ""
                ) {

                    event.preventDefault();


                    alert(
                        "Please select a valid User Type."
                    );


                    return;

                }


                /*
                |------------------------------------------------------------------
                | Department must be selected manually
                |------------------------------------------------------------------
                */

                if (
                    departmentInput.value === ""
                ) {

                    event.preventDefault();


                    alert(
                        "Please select a Department."
                    );


                    return;

                }

            }
        );

    }

    /*
    |--------------------------------------------------------------------------
    | EDIT USER FORM
    |--------------------------------------------------------------------------
    */

    if (editUserForm) {

        editUserForm.addEventListener(
            "submit",
            function (event) {

                event.preventDefault();


                const userId =
                    editUserIdInput.value.trim();

                const fullName =
                    editFullNameInput.value.trim();

                const email =
                    editEmailInput.value.trim();

                const userType =
                    editUserTypeInput.value;

                const departmentId =
                    editDepartmentInput.value;

                const role =
                    userTypeRoleMapping[userType] || "";


                /*
                |------------------------------------------------------------------
                | Validation
                |------------------------------------------------------------------
                */

                if (!userId) {

                    alert("Invalid user ID.");

                    return;

                }


                if (!fullName) {

                    alert("Please enter the user's full name.");

                    return;

                }


                if (!email) {

                    alert("Please enter the user's email.");

                    return;

                }


                if (!userType || !role) {

                    alert("Please select a valid User Type.");

                    return;

                }


                if (!departmentId) {

                    alert("Please select a Department.");

                    return;

                }


                /*
                |------------------------------------------------------------------
                | Send Update
                |------------------------------------------------------------------
                */

                const formData =
                    new FormData();

                formData.append(
                    "action",
                    "update_user"
                );

                formData.append(
                    "user_id",
                    userId
                );

                formData.append(
                    "full_name",
                    fullName
                );

                formData.append(
                    "email",
                    email
                );

                formData.append(
                    "user_type",
                    userType
                );

                formData.append(
                    "role",
                    role
                );

                formData.append(
                    "department_id",
                    departmentId
                );


                fetch(
                    BASE_URL +
                    "/app/controller/AdminController.php",
                    {
                        method: "POST",
                        body: formData
                    }
                )

                .then(async response => {
                    const text = await response.text();

                    console.log("UPDATE RESPONSE:", text);

                    try {
                        return JSON.parse(text);
                    } catch (error) {
                        console.error("INVALID JSON RESPONSE:", text);
                        throw error;
                    }
                })

                .then(data => {

                    if (data.success) {

                        /*
                        |----------------------------------------------------------
                        | Close Modal
                        |----------------------------------------------------------
                        */

                        if (editUserModal) {

                            const modal =
                                bootstrap.Modal.getInstance(
                                    editUserModal
                                );

                            if (modal) {
                                modal.hide();
                            }

                        }


                        /*
                        |----------------------------------------------------------
                        | Reload User Table
                        |----------------------------------------------------------
                        */

                        loadUsers();

                        alert(
                            data.message ||
                            "User updated successfully."
                        );

                    }

                    else {

                        alert(
                            data.message ||
                            "Unable to update user."
                        );

                    }

                })

                .catch(error => {

                    console.error(
                        "Error updating user:",
                        error
                    );

                    alert(
                        "Something went wrong while updating the user."
                    );

                });

            }
        );

    }
    /*
    |--------------------------------------------------------------------------
    | RESET CREATE USER FORM WHEN MODAL CLOSES
    |--------------------------------------------------------------------------
    */

    const createUserModal =
        document.getElementById("createUserModal");


    if (createUserModal) {

        createUserModal.addEventListener(
            "hidden.bs.modal",
            function () {

                if (createUserForm) {

                    createUserForm.reset();

                }


                if (roleInput) {

                    roleInput.value = "";

                }


                if (departmentInput) {

                    departmentInput.value = "";

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

    loadUsers();

});