
<div class="container-fluid">

    <!-- ============================= -->
    <!-- PAGE HEADER -->
    <!-- ============================= -->

    <div class="d-flex justify-content-between align-items-center mb-4">

        <div>

            <h2>Manage Incidents</h2>

            <p class="text-muted mb-0">
           <!--    Monitor and manage campus incident reports. -->
            </p>

        </div>

    </div>


    <!-- ============================= -->
    <!-- STATISTICS -->
    <!-- ============================= -->

    <div class="row mb-4">

        <!-- Total Cases -->

        <div class="col-lg-3 col-md-6 mb-3">

            <div class="stat-card">

                <div class="stat-icon">
                    <i class="bi bi-clipboard-data-fill"></i>
                </div>

                <p class="stat-label">
                    Total Cases
                </p>

                <div
                    class="stat-value"
                    id="totalIncidents"
                >
                    0
                </div>

            </div>

        </div>


        <!-- Unsolved Cases -->

        <div class="col-lg-3 col-md-6 mb-3">

            <div class="stat-card">

                <div class="stat-icon">
                    <i class="bi bi-hourglass-split"></i>
                </div>

                <p class="stat-label">
                    Unsolved Cases
                </p>

                <div
                    class="stat-value"
                    id="unsolvedIncidents"
                >
                    0
                </div>

            </div>

        </div>


        <!-- Escalated Cases -->

        <div class="col-lg-3 col-md-6 mb-3">

            <div class="stat-card">

                <div class="stat-icon">
                    <i class="bi bi-exclamation-triangle-fill"></i>
                </div>

                <p class="stat-label">
                    Escalated Cases
                </p>

                <div
                    class="stat-value"
                    id="escalatedIncidents"
                >
                    0
                </div>

            </div>

        </div>


        <!-- Overdue Cases -->

        <div class="col-lg-3 col-md-6 mb-3">

            <div class="stat-card">

                <div class="stat-icon">
                    <i class="bi bi-clock-fill"></i>
                </div>

                <p class="stat-label">
                    Overdue Cases
                </p>

                <div
                    class="stat-value"
                    id="overdueIncidents"
                >
                    0
                </div>

            </div>

        </div>

    </div>
     
    
    <!-- ============================= -->
    <!-- OVERDUE INCIDENT ALERT -->
    <!-- ============================= -->

    <div id="overdueAlertSection"
        class="alert alert-danger mb-4"
        role="alert"
        style="display: none;">

        <div class="d-flex align-items-start gap-3">

            <i class="bi bi-exclamation-triangle-fill fs-4"></i>

            <div class="flex-grow-1">

                <h5 class="alert-heading mb-1">
                    Overdue Incident Alert
                </h5>

                <p class="mb-2" id="overdueAlertMessage">
                    Some incidents require follow-up.
                </p>

                <ul id="overdueAlertList" class="mb-2">
                </ul>

                <button type="button"
                        id="viewOverdueIncidents"
                        class="btn btn-danger btn-sm">
                    View Overdue Incidents
                </button>

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

                <div class="col-lg-4 col-md-6 mb-3 mb-lg-0">

                    <div class="input-group">

                        <span class="input-group-text bg-white">

                            <i class="bi bi-search text-muted"></i>

                        </span>

                        <input
                            type="text"
                            id="searchInput"
                            class="form-control"
                            placeholder="Search incident by title or ID"
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


                <!-- Department Filter -->

                <div class="col-lg-2 col-md-3 mb-3 mb-lg-0">

                    <select
                        id="filter-department"
                        class="form-select"
                    >

                        <option value="">
                            All Departments
                        </option>

                    </select>

                </div>


                <!-- SLA Filter -->

                <div class="col-lg-2 col-md-3">

                    <select
                        id="filter-sla"
                        class="form-select"
                    >

                        <option value="">
                            All SLA
                        </option>

                        <option value="On Track">
                            On Track
                        </option>

                        <option value="Overdue">
                            Overdue
                        </option>

                        <option value="Completed">
                            Completed
                        </option>

                    </select>

                </div>

            </div>

        </div>

    </div>


    <!-- ============================= -->
    <!-- INCIDENT TABLE -->
    <!-- ============================= -->

    <div class="card shadow-sm border-0 mb-4">

        <div class="card-header bg-white">

            <h5 class="mb-0">

                <i class="bi bi-clipboard-data me-1"></i>

                Incident Reports

            </h5>

        </div>

        <div class="card-body">

            <p class="text-muted mb-4">
            <!--    Monitor and manage reported campus incidents. -->
            </p>

            <div class="table-responsive">

                <table
                    class="table table-hover align-middle mb-0"
                    id="incidentsTable"
                >

                    <thead class="table-light">

                        <tr>

                            <th>ID</th>

                            <th>Title</th>

                            <th>Category</th>

                            <th>Department</th>

                            <th>Priority</th>

                            <th>Assigned Staff</th>

                            <th>Status</th>

                            <th>SLA</th>

                            <th>Escalation</th>

                            <th class="text-center">
                                Actions
                            </th>

                        </tr>

                    </thead>

                    <tbody id="incidentTableBody">

                        <tr>

                            <td
                                colspan="10"
                                class="text-center py-4 text-muted"
                            >

                                Loading incident reports...

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
