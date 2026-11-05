
<div class="container-fluid">

    <!-- ============================= -->
    <!-- PAGE HEADER -->
    <!-- ============================= -->

    <div class="d-flex justify-content-between align-items-center mb-4">

        <div>

            <h2>Incident Details</h2>

            <p class="text-muted mb-0">
           <!--     View and manage the assigned incident. -->
            </p>

        </div>

        <div>

            <a
                href="dashboard.php?page=my_incidents"
                class="btn btn-outline-secondary"
            >

                <i class="bi bi-arrow-left me-1"></i>

                Back to My Incidents

            </a>

        </div>

    </div>


    <!-- ============================= -->
    <!-- INCIDENT INFORMATION -->
    <!-- ============================= -->

    <div class="card shadow-sm border-0 mb-4">

        <div class="card-header bg-white">

            <h5 class="mb-0">

                <i class="bi bi-clipboard-data me-1"></i>

                Incident Information

            </h5>

        </div>

        <div class="card-body">

            <div id="incidentDetails">

                <div class="text-muted">
                    Loading incident information...
                </div>

            </div>

        </div>

    </div>


    <!-- ============================= -->
    <!-- SLA INFORMATION -->
    <!-- ============================= -->

    <div class="card shadow-sm border-0 mb-4">

        <div class="card-header bg-white">

            <h5 class="mb-0">

                <i class="bi bi-clock-history me-1"></i>

                SLA Information

            </h5>

        </div>

        <div class="card-body">

            <div id="slaInformation">

                <div class="text-muted">
                    Loading SLA information...
                </div>

            </div>

        </div>

    </div>


    <!-- ============================= -->
    <!-- EVIDENCE -->
    <!-- ============================= -->

    <div class="card shadow-sm border-0 mb-4">

        <div class="card-header bg-white">

            <h5 class="mb-0">

                <i class="bi bi-paperclip me-1"></i>

                Evidence

            </h5>

        </div>

        <div class="card-body">

            <div id="evidenceSection">

                <div class="text-muted">
                    Loading evidence...
                </div>

            </div>

        </div>

    </div>


    <!-- ============================= -->
    <!-- STATUS UPDATE -->
    <!-- ============================= -->

    <div id="statusSection" class="mb-4"></div>


    <!-- ============================= -->
    <!-- COMPLETION PROOF -->
    <!-- ============================= -->

    <div id="completionSection" class="mb-4"></div>


    <!-- ============================= -->
    <!-- RAISE TO ADMIN -->
    <!-- ============================= -->

    <div id="escalationSection" class="mb-4"></div>


    <!-- ============================= -->
    <!-- INCIDENT UPDATE HISTORY -->
    <!-- ============================= -->

    <div class="card shadow-sm border-0 mb-4">

        <div class="card-header bg-white">

            <h5 class="mb-0">

                <i class="bi bi-clock-history me-1"></i>

                Incident Update History

            </h5>

        </div>

        <div class="card-body">

            <div id="updatesSection">

                <div class="text-muted">
                    Loading incident updates...
                </div>

            </div>

        </div>

    </div>

</div>


<!-- ============================= -->
<!-- BASE URL AND INCIDENT ID -->
<!-- ============================= -->

<script>

    const BASE_URL = "<?= BASE_URL ?>";

    const incidentId =
        new URLSearchParams(
            window.location.search
        ).get("id");

</script>
