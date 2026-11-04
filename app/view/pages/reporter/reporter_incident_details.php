<div class="container-fluid">

    <!-- ============================= -->
    <!-- PAGE HEADER -->
    <!-- ============================= -->

    <div class="d-flex justify-content-between align-items-center mb-4">

        <div>

            <h2>Incident Details</h2>

            <p class="text-muted mb-0">
            <!--    View the details and progress of your incident report. -->
            </p>

        </div>


        <div>

            <a
                href="<?= BASE_URL ?>/app/view/dashboard.php?page=reporter_my_reports"
                class="btn btn-outline-secondary"
            >

                <i class="bi bi-arrow-left me-1"></i>

                Back to My Reports

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

            <div id="incidentInformation">

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
    <!-- ATTACHMENTS -->
    <!-- ============================= -->

    <div class="card shadow-sm border-0 mb-4">

        <div class="card-header bg-white">

            <h5 class="mb-0">

                <i class="bi bi-paperclip me-1"></i>

                Attachments

            </h5>

        </div>


        <div class="card-body">

            <div id="attachmentsInformation">

                <div class="text-muted">
                    Loading attachments...
                </div>

            </div>

        </div>

    </div>


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

            <div id="updatesInformation">

                <div class="text-muted">
                    Loading incident updates...
                </div>

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