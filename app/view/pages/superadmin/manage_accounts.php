
<div class="container-fluid">

    <!-- Page Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">

        <div>
            <h2>Manage Admin Accounts</h2>

            <p class="text-muted mb-0">
            <!--    Create and manage administrator accounts.-->
            </p>
        </div>

        <button
            class="btn btn-primary"
            data-bs-toggle="modal"
            data-bs-target="#createAdminModal"
        >
            <i class="bi bi-person-plus-fill me-1"></i>
            Add Admin
        </button>

    </div>



<!-- ============================= -->
<!-- STATISTICS -->
<!-- ============================= -->

<div class="row mb-4">

    <!-- Total Admins -->
    <div class="col-lg-3 col-md-6 mb-3">

        <div class="stat-card">

            <div class="stat-icon">
                <i class="bi bi-people-fill"></i>
            </div>

            <p class="stat-label">
                Total Admins
            </p>

            <div
                class="stat-value"
                id="totalAdmins"
            >
                0
            </div>

        </div>

    </div>


    <!-- Active Admins -->
    <div class="col-lg-3 col-md-6 mb-3">

        <div class="stat-card">

            <div class="stat-icon">
                <i class="bi bi-person-check-fill"></i>
            </div>

            <p class="stat-label">
                Active Admins
            </p>

            <div
                class="stat-value"
                id="activeAdmins"
            >
                0
            </div>

        </div>

    </div>


    <!-- Inactive Admins -->
    <div class="col-lg-3 col-md-6 mb-3">

        <div class="stat-card">

            <div class="stat-icon">
                <i class="bi bi-person-x-fill"></i>
            </div>

            <p class="stat-label">
                Inactive Admins
            </p>

            <div
                class="stat-value"
                id="inactiveAdmins"
            >
                0
            </div>

        </div>

    </div>


    <!-- Suspended Admins -->
    <div class="col-lg-3 col-md-6 mb-3">

        <div class="stat-card">

            <div class="stat-icon">
                <i class="bi bi-person-fill-exclamation"></i>
            </div>

            <p class="stat-label">
                Suspended Admins
            </p>

            <div
                class="stat-value"
                id="suspendedAdmins"
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

                <div class="col-lg-6 col-md-8 mb-3 mb-lg-0">

                    <div class="input-group">

                        <span class="input-group-text bg-white">
                            <i class="bi bi-search text-muted"></i>
                        </span>

                        <input
                            type="text"
                            id="searchInput"
                            class="form-control"
                            placeholder="Search admin by name or email"
                        >

                    </div>

                </div>


                <!-- Status Filter -->

                <div class="col-lg-3 col-md-4">

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
    <!-- ADMIN ACCOUNT TABLE -->
    <!-- ============================= -->

    <div class="card shadow-sm border-0">

        <div class="card-header bg-white">

            <h5 class="mb-0">
                Admin Accounts
            </h5>

        </div>


        <div class="card-body p-0">

            <div class="table-responsive">

                <table
                    class="table table-hover align-middle mb-0"
                    id="adminsTable"
                >

                    <thead class="table-light">

                        <tr>

                            <th>ID</th>

                            <th>Full Name</th>

                            <th>Email</th>

                            <th>User Type</th>

                            <th>Status</th>

                            <th>Created At</th>

                            <th class="text-center">
                                Actions
                            </th>

                        </tr>

                    </thead>


                    <tbody id="adminAccountsTable">

                        <tr>

                            <td
                                colspan="7"
                                class="text-center py-4 text-muted"
                            >
                                No admin accounts found.
                            </td>

                        </tr>

                    </tbody>

                </table>

            </div>

        </div>

    </div>

</div>


<!-- ============================= -->
<!-- CREATE ADMIN MODAL -->
<!-- ============================= -->

<div
    class="modal fade"
    id="createAdminModal"
    tabindex="-1"
    aria-hidden="true"
>

    <div class="modal-dialog">

        <div class="modal-content">

            <!-- Modal Header -->

            <div class="modal-header">

                <h5 class="modal-title">
                    Create Admin Account
                </h5>

                <button
                    type="button"
                    class="btn-close"
                    data-bs-dismiss="modal"
                ></button>

            </div>


            <!-- ============================= -->
            <!-- CREATE ADMIN FORM -->
            <!-- ============================= -->

            <form
                id="createAdminForm"
                action="<?= BASE_URL ?>/app/controller/UserController.php"
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


                    <!-- Default Password Information -->

                    <div class="alert alert-info">

                        <i class="bi bi-info-circle me-1"></i>

                        The account will be created with a default
                        password. The Admin must change the password
                        after the first login.

                    </div>

                </div>


                <!-- ============================= -->
                <!-- MODAL FOOTER -->
                <!-- ============================= -->

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
                        name="create_admin"
                        class="btn btn-primary"
                    >

                        <i class="bi bi-person-plus-fill me-1"></i>

                        Create Admin

                    </button>

                </div>

            </form>

        </div>

    </div>

</div>


<script>
    window.BASE_URL = "<?= BASE_URL ?>";
</script>



