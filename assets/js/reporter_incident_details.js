// =====================================================
// REPORTER INCIDENT DETAILS
// =====================================================

document.addEventListener("DOMContentLoaded", function () {

    // =====================================================
    // GET INCIDENT ID
    // =====================================================

    const incidentId =
        new URLSearchParams(
            window.location.search
        ).get("id");


    if (!incidentId) {

        showError(
            "Invalid incident ID."
        );

        return;
    }


    // =====================================================
    // LOAD INCIDENT
    // =====================================================

    loadIncidentDetails();


    async function loadIncidentDetails() {

        try {

            const response = await fetch(
                `${window.BASE_URL}/app/controller/IncidentController.php?action=get_incident&incident_id=${encodeURIComponent(incidentId)}`
            );


            const data =
                await response.json();


            console.log(
                "Reporter incident detail API response:",
                data
            );


            if (!data.success) {

                showError(
                    data.message ||
                    "Failed to load incident details."
                );

                return;
            }


            const incident =
                data.incident;


            if (!incident) {

                showError(
                    "Incident not found."
                );

                return;
            }


            // =================================================
            // DISPLAY INFORMATION
            // =================================================

            displayIncidentInformation(
                incident
            );


            displaySLAInformation(
                incident
            );


            // =================================================
            // LOAD ADDITIONAL INFORMATION
            // =================================================

            await loadAttachments();

            await loadUpdates();


        } catch (error) {

            console.error(
                "Error loading reporter incident details:",
                error
            );


            showError(
                "Unable to load incident details."
            );

        }

    }


    // =====================================================
    // INCIDENT INFORMATION
    // =====================================================

    function displayIncidentInformation(
        incident
    ) {

        const container =
            document.getElementById(
                "incidentInformation"
            );


        if (!container) {
            return;
        }


        container.innerHTML = `

            <div class="row g-3">

                <div class="col-md-6">

                    <strong>Incident ID</strong>

                    <div>
                        #${escapeHtml(
                            incident.incident_id
                        )}
                    </div>

                </div>


                <div class="col-md-6">

                    <strong>Title</strong>

                    <div>
                        ${escapeHtml(
                            incident.title || "-"
                        )}
                    </div>

                </div>


                <div class="col-md-6">

                    <strong>Category</strong>

                    <div>
                        ${escapeHtml(
                            incident.category_name || "-"
                        )}
                    </div>

                </div>


                <div class="col-md-6">

                    <strong>Department</strong>

                    <div>
                        ${escapeHtml(
                            incident.department_name || "-"
                        )}
                    </div>

                </div>


                <div class="col-md-6">

                    <strong>Priority</strong>

                    <div>
                        ${getPriorityBadge(
                            incident.priority
                        )}
                    </div>

                </div>


                <div class="col-md-6">

                    <strong>Status</strong>

                    <div>
                        ${getStatusBadge(
                            incident.status
                        )}
                    </div>

                </div>


                <div class="col-md-6">

                    <strong>Submitted At</strong>

                    <div>
                        ${formatDate(
                            incident.submitted_at
                        )}
                    </div>

                </div>


                <div class="col-md-6">

                    <strong>Location</strong>

                    <div>
                        ${escapeHtml(
                            incident.location_name || "-"
                        )}
                    </div>

                </div>


                <div class="col-md-6">

                    <strong>Building</strong>

                    <div>
                        ${escapeHtml(
                            incident.building_name || "-"
                        )}
                    </div>

                </div>


                <div class="col-md-6">

                    <strong>Floor</strong>

                    <div>
                        ${escapeHtml(
                            incident.floor_number || "-"
                        )}
                    </div>

                </div>


                <div class="col-12">

                    <strong>Description</strong>

                    <div class="mt-2">

                        ${escapeHtml(
                            incident.description || "-"
                        ).replace(
                            /\n/g,
                            "<br>"
                        )}

                    </div>

                </div>

            </div>

        `;
        renderReporterApproval(incident);

    }

    // =====================================================
// REPORTER RESOLUTION APPROVAL
// =====================================================

function renderReporterApproval(incident) {

    const container = document.getElementById(
        "incidentInformation"
    );

    if (!container) {
        return;
    }

    // Show approval buttons only for unresolved approval.
    if (
        incident.status !== "Resolved" ||
        Number(incident.reporter_approved) !== 0
    ) {
        return;
    }

    container.innerHTML += `

        <div class="mt-4 p-3 border rounded">

            <h6 class="fw-bold">
                Resolution Review
            </h6>

            <p class="text-muted">
                Response Staff has marked this incident as resolved.
                Please confirm whether the issue has been fixed.
            </p>

            <button
                type="button"
                class="btn btn-success me-2"
                id="confirmResolvedBtn"
            >
                Confirm Resolved
            </button>

            <button
                type="button"
                class="btn btn-outline-danger"
                id="notFixedBtn"
            >
                Not Fixed
            </button>

        </div>

    `;

    document.getElementById("confirmResolvedBtn")
        .addEventListener("click", function () {

            submitReporterDecision("approve");

        });

    document.getElementById("notFixedBtn")
        .addEventListener("click", function () {

            submitReporterDecision("reject");

        });

}


    // =====================================================
    // SUBMIT REPORTER DECISION
    // =====================================================

    async function submitReporterDecision(decision) {

        let message = "";

        if (decision === "approve") {

            if (!confirm(
                "Are you sure the incident has been fixed?"
            )) {
                return;
            }

        } else {

            message = prompt(
                "Please explain why the incident is not fixed:"
            );

            if (message === null) {
                return;
            }

            message = message.trim();

            if (!message) {

                alert(
                    "Please provide a reason why the incident is not fixed."
                );

                return;
            }

        }

        const approveButton = document.getElementById(
            "confirmResolvedBtn"
        );

        const rejectButton = document.getElementById(
            "notFixedBtn"
        );

        if (approveButton) approveButton.disabled = true;
        if (rejectButton) rejectButton.disabled = true;

        try {

            const formData = new FormData();

            formData.append("incident_id", incidentId);
            formData.append("decision", decision);
            formData.append("message", message);

            const response = await fetch(
                `${window.BASE_URL}/app/controller/IncidentController.php?action=reporter_approve_incident`,
                {
                    method: "POST",
                    body: formData
                }
            );

            const responseText = await response.text();

            console.log("HTTP Status:", response.status);
            console.log("Server Response:", responseText);

            let data;

            try {
                data = JSON.parse(responseText);
            } catch (error) {
                console.error("Invalid JSON response:", error);
                throw new Error("PHP did not return valid JSON. Check the console.");
            }

            if (!data.success) {

                alert(
                    data.message || "Unable to submit your decision."
                );

                if (approveButton) approveButton.disabled = false;
                if (rejectButton) rejectButton.disabled = false;

                return;
            }

            alert(data.message);

            // Refresh incident information and update history.
            await loadIncidentDetails();

        } catch (error) {

            console.error(
                "Error submitting Reporter decision:",
                error
            );

            alert(
                "Unable to submit your decision. Please try again."
            );

            if (approveButton) approveButton.disabled = false;
            if (rejectButton) rejectButton.disabled = false;

        }

    }


    // =====================================================
    // SLA INFORMATION
    // =====================================================

    function displaySLAInformation(
        incident
    ) {

        const container =
            document.getElementById(
                "slaInformation"
            );


        if (!container) {
            return;
        }


        const sla =
            incident.sla ||
            null;


        if (!sla) {

            container.innerHTML = `

                <div class="text-muted">
                    SLA information is not available.
                </div>

            `;

            return;
        }


        container.innerHTML = `

            <div class="row g-3">

                <div class="col-md-6">

                    <strong>SLA Status</strong>

                    <div>
                        ${getSlaBadge(
                            sla.sla_status
                        )}
                    </div>

                </div>


                <div class="col-md-6">

                    <strong>Priority</strong>

                    <div>
                        ${getPriorityBadge(
                            sla.priority ||
                            incident.priority
                        )}
                    </div>

                </div>


                <div class="col-md-6">

                    <strong>Start Time</strong>

                    <div>
                        ${formatDate(
                            sla.start_time
                        )}
                    </div>

                </div>


                <div class="col-md-6">

                    <strong>Due Time</strong>

                    <div>
                        ${formatDate(
                            sla.due_time
                        )}
                    </div>

                </div>


                <div class="col-md-6">

                    <strong>Completed Time</strong>

                    <div>
                        ${formatDate(
                            sla.completed_time
                        )}
                    </div>

                </div>

            </div>

        `;

    }


    // =====================================================
    // ATTACHMENTS
    // =====================================================

    async function loadAttachments() {

        const container =
            document.getElementById(
                "attachmentsInformation"
            );


        if (!container) {
            return;
        }


        container.innerHTML = `

            <div class="text-muted">
                Loading attachments...
            </div>

        `;


        try {

            const response = await fetch(
                `${window.BASE_URL}/app/controller/IncidentController.php?action=get_incident_attachments&incident_id=${encodeURIComponent(incidentId)}`
            );


            const data =
                await response.json();


            if (
                data.success &&
                Array.isArray(data.attachments)
            ) {

                displayAttachments(
                    data.attachments
                );

                return;
            }


            container.innerHTML = `

                <div class="text-muted">
                    No attachments uploaded.
                </div>

            `;


        } catch (error) {

            console.error(
                "Error loading attachments:",
                error
            );


            container.innerHTML = `

                <div class="text-muted">
                    Unable to load attachments.
                </div>

            `;

        }

    }


    function displayAttachments(
        attachments
    ) {

        const container =
            document.getElementById(
                "attachmentsInformation"
            );


        if (!container) {
            return;
        }


        if (!attachments.length) {

            container.innerHTML = `

                <div class="text-muted">
                    No attachments uploaded.
                </div>

            `;

            return;
        }


        let html = "";


        attachments.forEach(
            function (attachment) {

                const fileName =
                    attachment.file_name || "-";


                const filePath =
                    attachment.file_path || "";


                html += `

                    <div class="border rounded p-3 mb-3">

                        <div class="row g-3">

                            <div class="col-md-6">

                                <strong>File</strong>

                                <div>

                                    ${
                                        filePath
                                        ?
                                        `
                                            <a
                                                href="${window.BASE_URL}/${escapeAttributePath(filePath)}"
                                                target="_blank"
                                                rel="noopener noreferrer"
                                            >
                                                ${escapeHtml(
                                                    fileName
                                                )}
                                            </a>
                                        `
                                        :
                                        escapeHtml(
                                            fileName
                                        )
                                    }

                                </div>

                            </div>


                            <div class="col-md-6">

                                <strong>Type</strong>

                                <div>

                                    ${escapeHtml(
                                        attachment.attachment_type ||
                                        "-"
                                    )}

                                </div>

                            </div>


                            <div class="col-md-6">

                                <strong>Uploaded At</strong>

                                <div>

                                    ${formatDate(
                                        attachment.uploaded_at
                                    )}

                                </div>

                            </div>

                        </div>

                    </div>

                `;

            }
        );


        container.innerHTML =
            html;

    }


    // =====================================================
    // INCIDENT UPDATE HISTORY
    // =====================================================

    async function loadUpdates() {

        const container =
            document.getElementById(
                "updatesInformation"
            );


        if (!container) {
            return;
        }


        container.innerHTML = `

            <div class="text-muted">
                Loading incident updates...
            </div>

        `;


        try {

            const response = await fetch(
                `${window.BASE_URL}/app/controller/IncidentController.php?action=get_incident_updates&incident_id=${encodeURIComponent(incidentId)}`
            );


            const data =
                await response.json();


            if (
                data.success &&
                Array.isArray(data.updates)
            ) {

                displayUpdates(
                    data.updates
                );

                return;
            }


            container.innerHTML = `

                <div class="text-muted">
                    No incident updates available.
                </div>

            `;


        } catch (error) {

            console.error(
                "Error loading updates:",
                error
            );


            container.innerHTML = `

                <div class="text-muted">
                    Unable to load incident updates.
                </div>

            `;

        }

    }


    function displayUpdates(
        updates
    ) {

        const container =
            document.getElementById(
                "updatesInformation"
            );


        if (!container) {
            return;
        }


        if (!updates.length) {

            container.innerHTML = `

                <div class="text-muted">
                    No incident updates available.
                </div>

            `;

            return;
        }


        let html = "";


        updates.forEach(
            function (update) {

                html += `

                    <div class="border-start border-3 ps-3 mb-3">

                        <div class="fw-semibold">

                            ${escapeHtml(
                                update.status || "-"
                            )}

                        </div>


                        <div class="text-muted small">

                            ${escapeHtml(
                                update.full_name || "-"
                            )}

                            &middot;

                            ${formatDate(
                                update.created_at
                            )}

                        </div>


                        <div class="mt-2">

                            ${escapeHtml(
                                update.update_message || "-"
                            ).replace(
                                /\n/g,
                                "<br>"
                            )}

                        </div>

                    </div>

                `;

            }
        );


        container.innerHTML =
            html;

    }


    // =====================================================
    // PRIORITY BADGE
    // =====================================================

    function getPriorityBadge(
        priority
    ) {

        if (!priority) {

            return `

                <span class="badge bg-secondary">
                    -
                </span>

            `;

        }


        let badgeClass =
            "bg-secondary";


        switch (priority) {

            case "Low":
                badgeClass = "bg-success";
                break;

            case "Medium":
                badgeClass = "bg-primary";
                break;

            case "High":
                badgeClass = "bg-warning text-dark";
                break;

            case "Emergency":
                badgeClass = "bg-danger";
                break;

        }


        return `

            <span class="badge ${badgeClass}">
                ${escapeHtml(priority)}
            </span>

        `;

    }


    // =====================================================
    // STATUS BADGE
    // =====================================================

    function getStatusBadge(
        status
    ) {

        if (!status) {

            return `

                <span class="badge bg-secondary">
                    -
                </span>

            `;

        }


        let badgeClass =
            "bg-secondary";


        switch (status) {

            case "Submitted":
                badgeClass = "bg-secondary";
                break;

            case "Assigned":
                badgeClass = "bg-primary";
                break;

            case "In Progress":
                badgeClass = "bg-warning text-dark";
                break;

            case "Resolved":
                badgeClass = "bg-success";
                break;

            case "Closed":
                badgeClass = "bg-dark";
                break;

        }


        return `

            <span class="badge ${badgeClass}">
                ${escapeHtml(status)}
            </span>

        `;

    }


    // =====================================================
    // SLA BADGE
    // =====================================================

    function getSlaBadge(
        status
    ) {

        if (!status) {

            return `

                <span class="badge bg-secondary">
                    -
                </span>

            `;

        }


        let badgeClass =
            "bg-secondary";


        switch (status) {

            case "On Track":
                badgeClass = "bg-success";
                break;

            case "Overdue":
                badgeClass = "bg-danger";
                break;

            case "Completed":
                badgeClass = "bg-primary";
                break;

        }


        return `

            <span class="badge ${badgeClass}">
                ${escapeHtml(status)}
            </span>

        `;

    }


    // =====================================================
    // DATE FORMAT
    // =====================================================

    function formatDate(
        value
    ) {

        if (!value) {
            return "-";
        }


        const date =
            new Date(
                String(value).replace(
                    " ",
                    "T"
                )
            );


        if (
            Number.isNaN(
                date.getTime()
            )
        ) {

            return escapeHtml(
                value
            );

        }


        return date.toLocaleString(
            "en-MY",
            {
                day: "2-digit",
                month: "short",
                year: "numeric",
                hour: "2-digit",
                minute: "2-digit"
            }
        );

    }


    // =====================================================
    // HTML ESCAPE
    // =====================================================

    function escapeHtml(
        value
    ) {

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


    // =====================================================
    // FILE PATH ESCAPE
    // =====================================================

    function escapeAttributePath(
        value
    ) {

        if (!value) {
            return "";
        }


        return String(value)
            .replace(
                /\\/g,
                "/"
            )
            .split("/")
            .map(
                function (part) {

                    return encodeURIComponent(
                        part
                    );

                }
            )
            .join("/");

    }


    // =====================================================
    // ERROR DISPLAY
    // =====================================================

    function showError(
        message
    ) {

        const sections = [

            "incidentInformation",
            "slaInformation",
            "attachmentsInformation",
            "updatesInformation"

        ];


        sections.forEach(
            function (id) {

                const element =
                    document.getElementById(
                        id
                    );


                if (element) {

                    element.innerHTML = `

                        <div class="alert alert-danger">

                            ${escapeHtml(
                                message
                            )}

                        </div>

                    `;

                }

            }
        );

    }

});