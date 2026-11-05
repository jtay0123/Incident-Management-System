
<div class="container-fluid">

    <!-- ============================= -->
    <!-- PAGE HEADER -->
    <!-- ============================= -->

    <div class="d-flex justify-content-between align-items-center mb-4">

        <div>

            <h2>Incident Details</h2>

            <p class="text-muted mb-0">
            <!--    View incident information, assignment, SLA and escalation details. -->
            </p>

        </div>

        <a
            href="dashboard.php?page=admin_incidents"
            class="btn btn-secondary"
        >

            <i class="bi bi-arrow-left me-1"></i>

            Back to Incidents

        </a>

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

                <div class="text-center py-4 text-muted">

                    Loading incident information...

                </div>

            </div>

        </div>

    </div>


    <!-- ============================= -->
    <!-- ASSIGNMENT INFORMATION -->
    <!-- ============================= -->

    <div class="card shadow-sm border-0 mb-4">

        <div class="card-header bg-white">

            <h5 class="mb-0">

                <i class="bi bi-person-check me-1"></i>

                Assignment Information

            </h5>

        </div>

        <div class="card-body">

            <div id="assignmentInformation">

                <div class="text-center py-4 text-muted">

                    Loading assignment information...

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

                <div class="text-center py-4 text-muted">

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

                Attachments

            </h5>

        </div>

        <div class="card-body">

            <div id="attachmentsInformation">

                <div class="text-center py-4 text-muted">

                    Loading attachments...

                </div>

            </div>

        </div>

    </div>


    <!-- ============================= -->
    <!-- ESCALATION -->
    <!-- ============================= -->

    <div class="card shadow-sm border-0 mb-4">

        <div class="card-header bg-white">

            <h5 class="mb-0">

                <i class="bi bi-exclamation-triangle me-1"></i>

                Escalation

            </h5>

        </div>

        <div class="card-body">

            <div id="escalationInformation">

                <div class="text-center py-4 text-muted">

                    Loading escalation information...

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

            <!-- ADMIN NOTE FORM -->
            <div class="border rounded p-3 mb-4 bg-light">

                <h6 class="mb-3">
                    <i class="bi bi-chat-left-text me-1"></i>
                    Add Note for Response Staff
                </h6>

                <form id="adminNoteForm">

                    <div class="mb-3">

                        <label for="adminNoteMessage" class="form-label">
                            Note
                        </label>

                        <textarea
                            id="adminNoteMessage"
                            name="message"
                            class="form-control"
                            rows="3"
                            maxlength="1000"
                            placeholder="Enter a note for the response staff..."
                            required
                        ></textarea>

                    </div>

                    <button
                        type="submit"
                        id="addAdminNoteButton"
                        class="btn btn-primary btn-sm"
                    >
                        <i class="bi bi-send me-1"></i>
                        Add Note
                    </button>

                    <div
                        id="adminNoteFeedback"
                        class="mt-2 small"
                        role="status"
                    ></div>

                </form>

            </div>

            <!-- EXISTING UPDATE HISTORY -->
            <div id="updatesInformation">

                <div class="text-center py-4 text-muted">
                    Loading incident updates...
                </div>

            </div>

        </div>

    </div>



    <!-- ============================= -->
    <!-- ADMIN ACTIONS -->
    <!-- ============================= -->

    <div class="card shadow-sm border-0 mb-4">

        <div class="card-header bg-white">

            <h5 class="mb-0">

                <i class="bi bi-gear me-1"></i>

                Admin Actions

            </h5>

        </div>

        <div class="card-body">

            <div
                id="adminActions"
                class="d-flex flex-wrap gap-2"
            >

                <span class="text-muted">
                    Loading available actions...
                </span>

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


<!-- ============================= -->
<!-- INCIDENT ID -->
<!-- ============================= -->

<script>

    const adminIncidentId =
        new URLSearchParams(
            window.location.search
        ).get("id");

</script>
