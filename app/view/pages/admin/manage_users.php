
<div class="container-fluid">

    <!-- ============================= -->
    <!-- PAGE HEADER -->
    <!-- ============================= -->

    <div class="d-flex justify-content-between align-items-center mb-4">

        <div>

            <h2>Manage Users</h2>

            <p class="text-muted mb-0"></p>

        </div>

        <button
            class="btn btn-primary"
            data-bs-toggle="modal"
            data-bs-target="#createUserModal"
        >

            <i class="bi bi-person-plus-fill me-1"></i>

            Add Staff / Lecturer

        </button>

    </div>


    <!-- ============================= -->
    <!-- STATISTICS -->
    <!-- ============================= -->

    <div class="row mb-4">

        <!-- Total Users -->

        <div class="col-lg-3 col-md-6 mb-3">

            <div class="stat-card">

                <div class="stat-icon">
                    <i class="bi bi-people-fill"></i>
                </div>

                <p class="stat-label">
                    Total Users
                </p>

                <div
                    class="stat-value"
                    id="totalUsers"
                >
                    0
                </div>

            </div>

        </div>


        <!-- Active Users -->

        <div class="col-lg-3 col-md-6 mb-3">

            <div class="stat-card">

                <div class="stat-icon">
                    <i class="bi bi-person-check-fill"></i>
                </div>

                <p class="stat-label">
                    Active Users
                </p>

                <div
                    class="stat-value"
                    id="activeUsers"
                >
                    0
                </div>

            </div>

        </div>


        <!-- Inactive Users -->

        <div class="col-lg-3 col-md-6 mb-3">

            <div class="stat-card">

                <div class="stat-icon">
                    <i class="bi bi-person-x-fill"></i>
                </div>

                <p class="stat-label">
                    Inactive Users
                </p>

                <div
                    class="stat-value"
                    id="inactiveUsers"
                >
                    0
                </div>

            </div>

        </div>


        <!-- Suspended Users -->

        <div class="col-lg-3 col-md-6 mb-3">

            <div class="stat-card">

                <div class="stat-icon">
                    <i class="bi bi-person-fill-exclamation"></i>
                </div>

                <p class="stat-label">
                    Suspended Users
                </p>

                <div
                    class="stat-value"
                    id="suspendedUsers"
                >
                    0
                </div>

            </div>

        </div>

    </div>


    <!-- ============================= -->
    <!-- SEARCH AND FILTER -->
    <!-- ============================= -->

    <div class="card shadow-sm border-0 mb-4">

        <div class="card-body">

            <div class="row align-items-center">

                <!-- Search -->

                <div class="col-lg-5 col-md-6 mb-3 mb-lg-0">

                    <div class="input-group">

                        <span class="input-group-text bg-white">

                            <i class="bi bi-search text-muted"></i>

                        </span>

                        <input
                            type="text"
                            id="searchInput"
                            class="form-control"
                            placeholder="Search user by name or email"
                        >

                    </div>

                </div>


                <!-- User Type Filter -->

                <div class="col-lg-3 col-md-3 mb-3 mb-lg-0">

                    <select
                        id="filter-user-type"
                        class="form-select"
                    >

                        <option value="">
                            All User Types
                        </option>

                        <option value="Student">
                            Student
                        </option>

                        <option value="Lecturer">
                            Lecturer
                        </option>

                        <option value="Maintenance">
                            Maintenance
                        </option>

                        <option value="IT Support">
                            IT Support
                        </option>

                        <option value="Cleaning">
                            Cleaning
                        </option>

                        <option value="Security">
                            Security
                        </option>

                    </select>

                </div>


                <!-- Status Filter -->

                <div class="col-lg-3 col-md-3">

                    <select
                        id="filter-status"
                        class="form-select"
                    >

                        <option value="">
                            All Status
                        </option>

                        <option value="Active">
                            Active
                        </option>

                        <option value="Inactive">
                            Inactive
                        </option>

                        <option value="Suspended">
                            Suspended
                        </option>

                    </select>

                </div>

            </div>

        </div>

    </div>


    <!-- ============================= -->
    <!-- USER ACCOUNT TABLE -->
    <!-- ============================= -->

    <div class="card shadow-sm border-0 mb-4">

        <div class="card-header bg-white">

            <h5 class="mb-0">
                <i class="bi bi-people me-1"></i>
                Users Accounts
            </h5>

        </div>

        <div class="card-body">

            <p class="text-muted mb-4">
                Manage staff and lecturer user accounts.
            </p>

            <div class="table-responsive">

                <table
                    class="table table-hover align-middle mb-0"
                    id="usersTable"
                >

                    <thead class="table-light">

                        <tr>

                            <th>ID</th>
                            <th>Full Name</th>
                            <th>Email</th>
                            <th>Role</th>
                            <th>User Type</th>
                            <th>Department</th>
                            <th>Status</th>
                            <th>Created At</th>

                            <th class="text-center">
                                Actions
                            </th>

                        </tr>

                    </thead>

                    <tbody id="userAccountsTable">

                        <tr>

                            <td
                                colspan="9"
                                class="text-center py-4 text-muted"
                            >
                                No user accounts found.
                            </td>

                        </tr>

                    </tbody>

                </table>

            </div>

        </div>

    </div>

<!-- ===================================================== -->
<!-- CREATE STAFF / LECTURER MODAL -->
<!-- ===================================================== -->

<div
    class="modal fade"
    id="createUserModal"
    tabindex="-1"
    aria-hidden="true"
>

    <div class="modal-dialog">

        <div class="modal-content">

            <!-- Modal Header -->

            <div class="modal-header">

                <h5 class="modal-title">
                    Create Staff / Lecturer Account
                </h5>

                <button
                    type="button"
                    class="btn-close"
                    data-bs-dismiss="modal"
                ></button>

            </div>


            <!-- Create User Form -->

            <form
                id="createUserForm"
                action="<?= BASE_URL ?>/app/controller/AdminController.php?action=create_user"
                method="POST"
            >

                <div class="modal-body">

                    <!-- Full Name -->

                    <div class="mb-3">

                        <label
                            for="full_name"
                            class="form-label"
                        >
                            Full Name
                        </label>

                        <input
                            type="text"
                            class="form-control"
                            id="full_name"
                            name="full_name"
                            maxlength="100"
                            required
                        >

                    </div>


                    <!-- Email -->

                    <div class="mb-3">

                        <label
                            for="email"
                            class="form-label"
                        >
                            Email
                        </label>

                        <input
                            type="email"
                            class="form-control"
                            id="email"
                            name="email"
                            placeholder="example@campus.edu.my"
                            required
                        >

                    </div>


                    <!-- User Type -->

                    <div class="mb-3">

                        <label
                            for="user_type"
                            class="form-label"
                        >
                            User Type
                        </label>

                        <select
                            class="form-select"
                            id="user_type"
                            name="user_type"
                            required
                        >

                            <option
                                value=""
                                disabled
                                selected
                            >
                                Select User Type
                            </option>

                            <option value="Lecturer">
                                Lecturer
                            </option>

                            <option value="Maintenance">
                                Maintenance
                            </option>

                            <option value="IT Support">
                                IT Support
                            </option>

                            <option value="Cleaning">
                                Cleaning
                            </option>

                            <option value="Security">
                                Security
                            </option>

                        </select>

                    </div>


                    <!-- Role -->

                    <div class="mb-3">

                        <label
                            for="role"
                            class="form-label"
                        >
                            Role
                        </label>

                        <input
                            type="text"
                            class="form-control"
                            id="role"
                            name="role"
                            placeholder="Automatically assigned"
                            readonly
                            required
                        >

                    </div>


                    <!-- Department -->

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

                            <option
                                value=""
                                disabled
                                selected
                            >
                                Select Department
                            </option>

                        </select>

                    </div>


                    <!-- Default Password Information -->

                    <div class="alert alert-info">

                        <i class="bi bi-info-circle me-1"></i>

                        The account will be created with a default
                        password. The user must change the password
                        after the first login.

                    </div>

                </div>


                <!-- Create Modal Footer -->

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
                        name="create_user"
                        class="btn btn-primary"
                    >

                        <i class="bi bi-person-plus-fill me-1"></i>

                        Create Account

                    </button>

                </div>

            </form>

        </div>

    </div>

</div>


<!-- ===================================================== -->
<!-- EDIT USER MODAL -->
<!-- ===================================================== -->

<div
    class="modal fade"
    id="editUserModal"
    tabindex="-1"
    aria-hidden="true"
>

    <div class="modal-dialog">

        <div class="modal-content">

            <!-- Modal Header -->

            <div class="modal-header">

                <h5 class="modal-title">
                    Edit User Account
                </h5>

                <button
                    type="button"
                    class="btn-close"
                    data-bs-dismiss="modal"
                ></button>

            </div>


            <!-- Edit User Form -->

            <form id="editUserForm">

                <div class="modal-body">

                    <!-- Hidden User ID -->

                    <input
                        type="hidden"
                        id="edit_user_id"
                        name="user_id"
                    >


                    <!-- Full Name -->

                    <div class="mb-3">

                        <label
                            for="edit_full_name"
                            class="form-label"
                        >
                            Full Name
                        </label>

                        <input
                            type="text"
                            class="form-control"
                            id="edit_full_name"
                            name="full_name"
                            maxlength="100"
                            required
                        >

                    </div>


                    <!-- Email -->

                    <div class="mb-3">

                        <label
                            for="edit_email"
                            class="form-label"
                        >
                            Email
                        </label>

                        <input
                            type="email"
                            class="form-control"
                            id="edit_email"
                            name="email"
                            maxlength="150"
                            required
                        >

                    </div>


                    <!-- User Type -->

                    <div class="mb-3">

                        <label
                            for="edit_user_type"
                            class="form-label"
                        >
                            User Type
                        </label>

                        <select
                            class="form-select"
                            id="edit_user_type"
                            name="user_type"
                            required
                        >

                            <option
                                value=""
                                disabled
                            >
                                Select User Type
                            </option>

                            <option value="Lecturer">
                                Lecturer
                            </option>

                            <option value="Maintenance">
                                Maintenance
                            </option>

                            <option value="IT Support">
                                IT Support
                            </option>

                            <option value="Cleaning">
                                Cleaning
                            </option>

                            <option value="Security">
                                Security
                            </option>

                        </select>

                    </div>


                    <!-- Role -->

                    <div class="mb-3">

                        <label
                            for="edit_role"
                            class="form-label"
                        >
                            Role
                        </label>

                        <input
                            type="text"
                            class="form-control"
                            id="edit_role"
                            name="role"
                            readonly
                        >

                    </div>


                    <!-- Department -->

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

                            <option
                                value=""
                                disabled
                            >
                                Select Department
                            </option>

                        </select>

                    </div>

                </div>


                <!-- Edit Modal Footer -->

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






