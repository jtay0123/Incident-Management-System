<div class="container-fluid">

    <!-- ============================= -->
    <!-- PAGE HEADER -->
    <!-- ============================= -->

    <div class="d-flex justify-content-between align-items-center mb-4">

        <div>

            <h2>My Reports</h2>

            <p class="text-muted mb-0">
         <!--View and track the campus incident reports you have submitted. -->
            </p>

        </div>

    </div>


    <!-- ============================= -->
    <!-- STATISTICS -->
    <!-- ============================= -->

    <div class="row mb-4">

        <!-- Total Reports -->

        <div class="col-lg-3 col-md-6 mb-3">

            <div class="stat-card">

                <div class="stat-icon">
                    <i class="bi bi-clipboard-data-fill"></i>
                </div>

                <p class="stat-label">
                    Total Reports
                </p>

                <div
                    class="stat-value"
                    id="totalReports"
                >
                    0
                </div>

            </div>

        </div>


        <!-- Unresolved Reports -->

        <div class="col-lg-3 col-md-6 mb-3">

            <div class="stat-card">

                <div class="stat-icon">
                    <i class="bi bi-hourglass-split"></i>
                </div>

                <p class="stat-label">
                    Unresolved Reports
                </p>

                <div
                    class="stat-value"
                    id="unresolvedReports"
                >
                    0
                </div>

            </div>

        </div>


        <!-- Resolved Reports -->

        <div class="col-lg-3 col-md-6 mb-3">

            <div class="stat-card">

                <div class="stat-icon">
                    <i class="bi bi-check-circle-fill"></i>
                </div>

                <p class="stat-label">
                    Resolved Reports
                </p>

                <div
                    class="stat-value"
                    id="resolvedReports"
                >
                    0
                </div>

            </div>

        </div>


        <!-- Closed Reports -->

        <div class="col-lg-3 col-md-6 mb-3">

            <div class="stat-card">

                <div class="stat-icon">
                    <i class="bi bi-folder-check"></i>
                </div>

                <p class="stat-label">
                    Closed Reports
                </p>

                <div
                    class="stat-value"
                    id="closedReports"
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
                            placeholder="Search report by title or ID"
                        >

                    </div>

                </div>


                <!-- Status Filter -->

                <div class="col-lg-2 col-md-3 mb-3 mb-lg-0">

                    <select
                        id="filter-status"
                        class="form-select"
                    >

                        <option value="">
                            All Status
                        </option>

                        <option value="Submitted">
                            Submitted
                        </option>

                        <option value="Assigned">
                            Assigned
                        </option>

                        <option value="In Progress">
                            In Progress
                        </option>

                        <option value="Resolved">
                            Resolved
                        </option>

                        <option value="Closed">
                            Closed
                        </option>

                    </select>

                </div>


                <!-- Priority Filter -->

                <div class="col-lg-2 col-md-3 mb-3 mb-lg-0">

                    <select
                        id="filter-priority"
                        class="form-select"
                    >

                        <option value="">
                            All Priority
                        </option>

                        <option value="Low">
                            Low
                        </option>

                        <option value="Medium">
                            Medium
                        </option>

                        <option value="High">
                            High
                        </option>

                        <option value="Emergency">
                            Emergency
                        </option>

                    </select>

                </div>


                <!-- Category Filter -->

                <div class="col-lg-3 col-md-3">

                    <select
                        id="filter-category"
                        class="form-select"
                    >

                        <option value="">
                            All Categories
                        </option>

                    </select>

                </div>

            </div>

        </div>

    </div>


    <!-- ============================= -->
    <!-- REPORT TABLE -->
    <!-- ============================= -->

    <div class="card shadow-sm border-0 mb-4">

        <div class="card-header bg-white">

            <h5 class="mb-0">

                <i class="bi bi-clipboard-data me-1"></i>

                My Incident Reports

            </h5>

        </div>


        <div class="card-body">



            <div class="table-responsive">

                <table
                    class="table table-hover align-middle mb-0"
                    id="reportsTable"
                >

                    <thead class="table-light">

                        <tr>

                            <th>ID</th>

                            <th>Title</th>

                            <th>Category</th>

                            <th>Priority</th>

                            <th>Status</th>

                            <th>Submitted</th>

                            <th class="text-center">
                                Actions
                            </th>

                        </tr>

                    </thead>


                    <tbody id="reportTableBody">

                        <tr>

                            <td
                                colspan="7"
                                class="text-center py-4 text-muted"
                            >

                                Loading your reports...

                            </td>

                        </tr>

                    </tbody>

                </table>

            </div>

        </div>

    </div>

</div>


<!-- ============================= -->
<!-- BASE URL -->
<!-- ============================= -->

<script>

    window.BASE_URL = "<?= BASE_URL ?>";

</script>