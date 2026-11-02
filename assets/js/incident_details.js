
document.addEventListener(
    "DOMContentLoaded",
    function () {

        if (!incidentId) {

            showError(
                "Invalid incident ID."
            );

            return;
        }

        loadIncidentDetails();

    }
);


/* ================================================== */
/* LOAD INCIDENT */
/* ================================================== */

function loadIncidentDetails() {

    fetch(
        `${BASE_URL}/app/controller/IncidentController.php` +
        `?action=get_staff_incident_details` +
        `&incident_id=${encodeURIComponent(incidentId)}`
    )

        .then(async response => {

            const text = await response.text();

            console.log(
                "Incident API response:",
                text
            );

            let data;

            try {

                data = JSON.parse(text);

            } catch (error) {

                throw new Error(
                    "Invalid server response."
                );

            }

            if (!response.ok) {

                throw new Error(
                    data.message ||
                    "Unable to load incident."
                );

            }

            return data;

        })

        .then(data => {

            console.log(
                "Incident data:",
                data
            );

            if (!data.success) {

                showError(
                    data.message ||
                    "Unable to load incident."
                );

                displaySLAInformation(null, null);

                return;
            }


            /*
             * Controller returns:
             *
             * {
             *     success: true,
             *     incident: {...},
             *     sla: {...} or null,
             *     attachments: [...],
             *     updates: [...]
             * }
             */


            const incident = data.incident;

            const sla = data.sla || null;

            const attachments =
                data.attachments || [];

            const updates =
                data.updates || [];


            /*
             * Check incident information
             */

            if (!incident) {

                showError(
                    "Incident information was not returned."
                );

                displaySLAInformation(null, null);

                return;
            }


            /*
             * Store attachments globally so
             * displayStatusSection() can check
             * whether completion proof exists.
             */

            window.currentAttachments =
                attachments;


            /*
             * Display incident information
             */

            displayIncident(
                incident
            );


            /*
             * Display SLA information
             */

            displaySLAInformation(
                sla,
                incident
            );


            /*
             * Display attachments, status,
             * completion, escalation and updates
             */

            displayAttachments(
                attachments
            );

            displayStatusSection(
                incident
            );

            displayCompletionSection(
                incident
            );

            displayEscalationSection(
                incident
            );

            displayUpdates(
                updates
            );

        })

        .catch(error => {

            console.error(
                "Error loading incident:",
                error
            );

            showError(
                error.message ||
                "Unable to load incident."
            );

            displaySLAInformation(null, null);

        });

}


/* ================================================== */
/* DISPLAY SLA INFORMATION */
/* ================================================== */

function displaySLAInformation(sla, incident) {

    const container =
        document.getElementById("slaInformation");

    if (!container) {

        console.error(
            'SLA container not found: #slaInformation'
        );

        return;
    }


    /*
     * No SLA record returned
     */

    if (!sla) {

        container.innerHTML = `
            <div class="alert alert-warning mb-0">
                SLA information is not available for this incident.
            </div>
        `;

        return;
    }


    /*
     * Display SLA record
     */

    container.innerHTML = `
        <div class="row g-3">

            <div class="col-md-6">
                <strong>SLA Status</strong>
                <div class="mt-1">
                    ${getSlaBadge(sla.sla_status)}
                </div>
            </div>

            <div class="col-md-6">
                <strong>Priority</strong>
                <div class="mt-1">
                    ${getPriorityBadge(
                        sla.priority ||
                        (incident ? incident.priority : null)
                    )}
                </div>
            </div>

            <div class="col-md-6">
                <strong>Start Time</strong>
                <div class="mt-1">
                    ${formatDateTime(sla.start_time)}
                </div>
            </div>

            <div class="col-md-6">
                <strong>Due Time</strong>
                <div class="mt-1">
                    ${formatDateTime(sla.due_time)}
                </div>
            </div>

            <div class="col-md-6">
                <strong>Completed Time</strong>
                <div class="mt-1">
                    ${formatDateTime(sla.completed_time)}
                </div>
            </div>

        </div>
    `;

}


/* ================================================== */
/* SLA STATUS BADGE */
/* ================================================== */

function getSlaBadge(status) {

    let badgeClass = "bg-secondary";

    if (status === "On Track") {

        badgeClass = "bg-success";

    } else if (status === "Overdue") {

        badgeClass = "bg-danger";

    } else if (status === "Completed") {

        badgeClass = "bg-primary";

    }

    return `
        <span class="badge ${badgeClass}">
            ${escapeHtml(status || "Unknown")}
        </span>
    `;

}
/* ================================================== */
/* INCIDENT INFORMATION */
/* ================================================== */

function displayIncident(incident) {

    let location = "";

    if (incident.location_name) {

        location +=
            escapeHtml(
                incident.location_name
            );

    }

    if (incident.building_name) {

        location +=
            "<br><small class='text-muted'>" +
            escapeHtml(
                incident.building_name
            );

        if (incident.floor_number) {

            location +=
                " - Floor " +
                escapeHtml(
                    incident.floor_number
                );

        }

        location += "</small>";

    }


    document.getElementById(
        "incidentDetails"
    ).innerHTML = `

        <div class="card shadow-sm">

            <div class="card-header">

                <h5 class="mb-0">
                    Incident #${escapeHtml(
                        incident.incident_id
                    )}
                </h5>

            </div>


            <div class="card-body">

                <div class="row g-4">

                    <div class="col-md-8">

                        <h4>
                            ${escapeHtml(
                                incident.title
                            )}
                        </h4>

                        <p class="mb-0">
                            ${escapeHtml(
                                incident.description
                            )}
                        </p>

                    </div>


                    <div class="col-md-4">

                        <strong>
                            Priority
                        </strong>

                        <div class="mb-3">
                            ${getPriorityBadge(
                                incident.priority
                            )}
                        </div>


                        <strong>
                            Status
                        </strong>

                        <div class="mb-3">
                            ${getStatusBadge(
                                incident.status
                            )}
                        </div>

                    </div>


                    <div class="col-md-4">

                        <strong>
                            Category
                        </strong>

                        <p>
                            ${escapeHtml(
                                incident.category_name ||
                                "-"
                            )}
                        </p>

                    </div>


                    <div class="col-md-4">

                        <strong>
                            Department
                        </strong>

                        <p>
                            ${escapeHtml(
                                incident.department_name ||
                                "-"
                            )}
                        </p>

                    </div>


                    <div class="col-md-4">

                        <strong>
                            Location
                        </strong>

                        <p>
                            ${location || "-"}
                        </p>

                    </div>


                    <div class="col-md-4">

                        <strong>
                            Reporter
                        </strong>

                        <p>
                            ${escapeHtml(
                                incident.reporter_name ||
                                "-"
                            )}
                        </p>

                    </div>


                    <div class="col-md-4">

                        <strong>
                            Submitted At
                        </strong>

                        <p>
                            ${formatDateTime(
                                incident.submitted_at
                            )}
                        </p>

                    </div>

                </div>

            </div>

        </div>

    `;

}




/* ================================================== */
/* ATTACHMENTS */
/* ================================================== */

function displayAttachments(
    attachments
) {

    const evidence =
        attachments.filter(
            attachment =>
                attachment.attachment_type ===
                "Evidence"
        );


    const completion =
        attachments.filter(
            attachment =>
                attachment.attachment_type ===
                "Completion Proof"
        );


    /* ================================================== */
    /* EVIDENCE */
    /* ================================================== */

    let html = `

        <div class="card shadow-sm">

            <div class="card-header">

                <h5 class="mb-0">
                    Evidence
                </h5>

            </div>

            <div class="card-body">
    `;


    if (evidence.length === 0) {

        html += `
            <p class="text-muted mb-0">
                No evidence uploaded.
            </p>
        `;

    } else {

        evidence.forEach(
            function (attachment) {

                html += `
                    <div class="mb-3">

                        <a
                            href="${BASE_URL}/${escapeHtml(
                                attachment.file_path
                            )}"
                            target="_blank"
                            class="btn btn-outline-primary"
                        >
                            View
                            ${escapeHtml(
                                attachment.file_name
                            )}
                        </a>

                    </div>
                `;

            }
        );

    }


    html += `
            </div>
        </div>
    `;


    document.getElementById(
        "evidenceSection"
    ).innerHTML = html;


    /* ================================================== */
    /* EXISTING COMPLETION PROOF */
    /* ================================================== */

    let completionHtml = `

        <div class="card shadow-sm">

            <div class="card-header">

                <h5 class="mb-0">
                    Completion Proof
                </h5>

            </div>

            <div class="card-body">
    `;


    if (completion.length === 0) {

        completionHtml += `
            <p class="text-muted mb-0">
                No completion proof uploaded yet.
            </p>
        `;

    } else {

        completion.forEach(
            function (attachment) {

                completionHtml += `
                    <div class="mb-2">

                        <a
                            href="${BASE_URL}/${escapeHtml(
                                attachment.file_path
                            )}"
                            target="_blank"
                            class="btn btn-outline-success"
                        >
                            View
                            ${escapeHtml(
                                attachment.file_name
                            )}
                        </a>

                    </div>
                `;

            }
        );

    }


    completionHtml += `
            </div>
        </div>
    `;


    /*
     * Only display the existing completion
     * proof here.
     *
     * The upload form is handled separately
     * by displayCompletionSection().
     */

    document.getElementById(
        "completionSection"
    ).innerHTML =
        completionHtml;

}


/* ================================================== */
/* STATUS SECTION */
/* ================================================== */

function displayStatusSection(incident) {

    const section =
        document.getElementById(
            "statusSection"
        );


    if (!section) {
        return;
    }


    let html = `

        <div class="card shadow-sm">

            <div class="card-header">

                <h5 class="mb-0">
                    Update Incident
                </h5>

            </div>

            <div class="card-body">

    `;


    /* ================================================== */
    /* ASSIGNED */
    /* ================================================== */

    if (
        incident.status === "Assigned"
    ) {

        html += `

            <p class="text-muted">
                This incident has been assigned to you.
                Start work when you begin handling the incident.
            </p>


            <div class="mb-3">

                <label
                    for="statusMessage"
                    class="form-label"
                >
                    Update Message
                </label>


                <textarea
                    id="statusMessage"
                    class="form-control"
                    rows="3"
                    placeholder="Optional update message..."
                ></textarea>

            </div>


            <button
                type="button"
                class="btn btn-primary"
                onclick="updateStatus('In Progress')"
            >
                Start Work
            </button>

        `;


    /* ================================================== */
    /* IN PROGRESS */
    /* ================================================== */

    } else if (
        incident.status === "In Progress"
    ) {

        /*
         * Check whether completion proof
         * has already been uploaded.
         */

        const hasCompletionProof =
            Array.isArray(
                window.currentAttachments
            ) &&
            window.currentAttachments.some(
                function (attachment) {

                    return (
                        attachment.attachment_type ===
                        "Completion Proof"
                    );

                }
            );


        html += `

            <p class="text-muted">
                You are currently working on this incident.
                Upload completion proof before marking
                the incident as resolved.
            </p>


            <div class="mb-3">

                <label
                    for="statusMessage"
                    class="form-label"
                >
                    Update Message
                </label>


                <textarea
                    id="statusMessage"
                    class="form-control"
                    rows="3"
                    placeholder="Describe the work completed..."
                ></textarea>

            </div>

        `;


        if (hasCompletionProof) {

            html += `

                <div class="alert alert-success">

                    Completion proof has been uploaded.
                    You can now mark this incident as resolved.

                </div>


                <button
                    type="button"
                    class="btn btn-success"
                    onclick="updateStatus('Resolved')"
                >
                    Mark as Resolved
                </button>

            `;

        } else {

            html += `

                <div class="alert alert-warning">

                    Please upload completion proof
                    before marking this incident as resolved.

                </div>

            `;

        }


    /* ================================================== */
    /* RESOLVED */
    /* ================================================== */

    } else if (
        incident.status === "Resolved"
    ) {

        html += `

            <div class="alert alert-success mb-0">

                This incident has been resolved.

            </div>

        `;


    /* ================================================== */
    /* CLOSED */
    /* ================================================== */

    } else if (
        incident.status === "Closed"
    ) {

        html += `

            <div class="alert alert-secondary mb-0">

                This incident has been closed.

            </div>

        `;


    /* ================================================== */
    /* OTHER */
    /* ================================================== */

    } else {

        html += `

            <p class="text-muted mb-0">

                No status action is available
                for this incident.

            </p>

        `;

    }


    html += `

            </div>

        </div>

    `;


    section.innerHTML =
        html;

}


/* ================================================== */
/* COMPLETION SECTION */
/* ================================================== */

function displayCompletionSection(
    incident
) {

    let content = "";


    /*
     * Response Staff can upload completion
     * proof while the incident is In Progress.
     */

    if (
        incident.status === "In Progress"
    ) {

        content = `

            <div class="mt-4">

                <h6>
                    Upload Completion Proof
                </h6>


                <p class="text-muted">

                    Upload a picture or document
                    showing that the incident
                    has been resolved.

                </p>


                ${getCompletionUploadForm()}

            </div>

        `;

    }


    /*
     * Once resolved or closed,
     * do not show upload form again.
     */

    else if (
        incident.status === "Resolved" ||
        incident.status === "Closed"
    ) {

        content = `

            <div class="mt-4">

                <p class="text-muted mb-0">

                    This incident has been marked as
                    ${escapeHtml(
                        incident.status
                    )}.

                </p>

            </div>

        `;

    }


    /*
     * Assigned or other status.
     */

    else {

        content = `

            <div class="mt-4">

                <p class="text-muted mb-0">

                    Completion proof can be uploaded
                    when the incident is being worked on.

                </p>

            </div>

        `;

    }


    const section =
        document.getElementById(
            "completionSection"
        );


    if (section) {

        section.innerHTML +=
            content;

    }

}


/* ================================================== */
/* COMPLETION UPLOAD FORM */
/* ================================================== */

function getCompletionUploadForm() {

    return `

        <form
            id="completionUploadForm"
            enctype="multipart/form-data"
            onsubmit="uploadCompletionProof(event)"
        >

            <input
                type="file"
                class="form-control mb-3"
                id="completionFile"
                name="completion_file"
                accept="image/*,.doc,.docx,.pdf,video/*"
                required
            >


            <button
                type="submit"
                class="btn btn-success"
            >
                Upload Completion Proof
            </button>

        </form>

    `;

}


/* ================================================== */
/* UPDATE STATUS */
/* ================================================== */


function updateStatus(status) {

    const messageElement =
        document.getElementById(
            "statusMessage"
        );


    let message =
        messageElement
            ? messageElement.value.trim()
            : "";


    /*
     * When staff starts work, an update message
     * is optional.
     *
     * If no message is entered, use a default
     * message automatically.
     */

    if (
        status === "In Progress" &&
        message === ""
    ) {

        message =
            "Started work on the incident.";

    }


    /*
     * When resolving the incident, require
     * an update message.
     */

    if (
        status === "Resolved" &&
        message === ""
    ) {

        alert(
            "Please enter an update message describing the work completed."
        );

        return;
    }


    /*
     * Do not allow Resolved unless
     * completion proof exists.
     */

    if (
        status === "Resolved"
    ) {

        const hasCompletionProof =
            Array.isArray(
                window.currentAttachments
            ) &&
            window.currentAttachments.some(
                function (attachment) {

                    return (
                        attachment.attachment_type ===
                        "Completion Proof"
                    );

                }
            );


        if (!hasCompletionProof) {

            alert(
                "Please upload completion proof before marking this incident as resolved."
            );

            return;
        }

    }


    const formData =
        new FormData();


    formData.append(
        "incident_id",
        incidentId
    );


    formData.append(
        "status",
        status
    );


    formData.append(
        "message",
        message
    );


    fetch(
        `${BASE_URL}/app/controller/IncidentController.php?action=update_staff_incident_status`,
        {
            method: "POST",
            body: formData
        }
    )

        .then(async response => {

            const text =
                await response.text();

            console.log(
                "Status API response:",
                text
            );


            let data;

            try {

                data =
                    JSON.parse(text);

            } catch (error) {

                throw new Error(
                    "Invalid server response."
                );

            }

            return data;

        })

        .then(data => {

            if (!data.success) {

                alert(
                    data.message ||
                    "Unable to update incident."
                );

                return;
            }


            alert(
                data.message ||
                "Incident updated successfully."
            );


            loadIncidentDetails();

        })

        .catch(error => {

            console.error(
                "Status update error:",
                error
            );

            alert(
                error.message ||
                "Unable to update incident."
            );

        });

}



/* ================================================== */
/* UPLOAD COMPLETION PROOF */
/* ================================================== */

function uploadCompletionProof(
    event
) {

    event.preventDefault();


    const fileInput =
        document.getElementById(
            "completionFile"
        );


    if (
        !fileInput ||
        fileInput.files.length === 0
    ) {

        alert(
            "Please select a file."
        );

        return;
    }


    const formData =
        new FormData();


    formData.append(
        "incident_id",
        incidentId
    );


    formData.append(
        "completion_file",
        fileInput.files[0]
    );


    fetch(
        `${BASE_URL}/app/controller/IncidentController.php?action=upload_completion_proof`,
        {
            method: "POST",
            body: formData
        }
    )

        .then(response =>
            response.json()
        )

        .then(data => {

            if (!data.success) {

                alert(
                    data.message ||
                    "Unable to upload completion proof."
                );

                return;
            }


            alert(
                data.message ||
                "Completion proof uploaded successfully."
            );


            loadIncidentDetails();

        })

        .catch(error => {

            console.error(
                "Upload error:",
                error
            );

            alert(
                "Unable to upload completion proof."
            );

        });

}


/* ================================================== */
/* ESCALATION */
/* ================================================== */

function displayEscalationSection(
    incident
) {

    let content = "";


    if (
        incident.escalation_status ===
        "Raised"
    ) {

        content = `

            <div class="alert alert-warning mb-0">

                <strong>
                    Raised to Admin
                </strong>

                <hr>

                <p class="mb-0">

                    ${escapeHtml(
                        incident.escalation_reason ||
                        "-"
                    )}

                </p>

            </div>

        `;

    } else {

        content = `

            <button
                type="button"
                class="btn btn-warning"
                onclick="showEscalationForm()"
            >
                Raise to Admin
            </button>


            <div
                id="escalationForm"
                class="mt-3"
                style="display:none;"
            >

                <textarea
                    class="form-control mb-3"
                    id="escalationReason"
                    rows="4"
                    placeholder="Explain why you cannot complete this incident..."
                ></textarea>


                <button
                    type="button"
                    class="btn btn-danger"
                    onclick="raiseToAdmin()"
                >
                    Confirm Raise to Admin
                </button>

            </div>

        `;

    }


    document.getElementById(
        "escalationSection"
    ).innerHTML = `

        <div class="card shadow-sm">

            <div class="card-header">

                <h5 class="mb-0">
                    Escalation
                </h5>

            </div>

            <div class="card-body">

                ${content}

            </div>

        </div>

    `;

}


/* ================================================== */
/* SHOW ESCALATION FORM */
/* ================================================== */

function showEscalationForm() {

    const form =
        document.getElementById(
            "escalationForm"
        );


    if (form) {

        form.style.display =
            "block";

    }

}


/* ================================================== */
/* RAISE TO ADMIN */
/* ================================================== */

function raiseToAdmin() {

    const reasonElement =
        document.getElementById(
            "escalationReason"
        );


    const reason =
        reasonElement
            ? reasonElement.value.trim()
            : "";


    if (reason === "") {

        alert(
            "Please provide a reason."
        );

        return;
    }


    const formData =
        new FormData();


    formData.append(
        "incident_id",
        incidentId
    );


    formData.append(
        "reason",
        reason
    );


    fetch(
        `${BASE_URL}/app/controller/IncidentController.php?action=raise_incident_to_admin`,
        {
            method: "POST",
            body: formData
        }
    )

        .then(response =>
            response.json()
        )

        .then(data => {

            if (!data.success) {

                alert(
                    data.message ||
                    "Unable to raise incident."
                );

                return;
            }


            alert(
                data.message ||
                "Incident raised to Admin."
            );


            loadIncidentDetails();

        })

        .catch(error => {

            console.error(
                "Escalation error:",
                error
            );

            alert(
                "Unable to raise incident."
            );

        });

}



/* ================================================== */
/* INCIDENT UPDATES */
/* ================================================== */

function displayUpdates(
    updates
) {

    let html = `

        <div class="card shadow-sm">

            <div class="card-header">

                <h5 class="mb-0">
                    Incident Updates
                </h5>

            </div>

            <div class="card-body">

    `;


    if (updates.length === 0) {

        html += `

            <p class="text-muted mb-0">
                No updates yet.
            </p>

        `;

    } else {

        updates.forEach(
            function (update) {

                html += `

                    <div class="border-bottom pb-3 mb-3">

                        <div>

                            <strong>
                                ${escapeHtml(
                                    update.full_name
                                )}
                            </strong>

                            <span>
                                ${escapeHtml(
                                    update.status || "-"
                                )}
                            </span>

                        </div>


                        <p class="mb-1 mt-2">

                            ${escapeHtml(
                                update.update_message
                            )}

                        </p>


                        <small class="text-muted">

                            ${formatDateTime(
                                update.created_at
                            )}

                        </small>

                    </div>

                `;

            }
        );

    }


    html += `

            </div>

        </div>

    `;


    document.getElementById(
        "updatesSection"
    ).innerHTML = html;

}



/* ================================================== */
/* PRIORITY BADGE */
/* ================================================== */


function getPriorityBadge(priority) {

    let badgeClass = "bg-secondary";

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
            ${escapeHtml(priority || "-")}
        </span>
    `;
}



/* ================================================== */
/* STATUS BADGE */
/* ================================================== */

function getStatusBadge(
    status
) {

    switch (status) {

        case "Assigned":

            return `
                <span class="badge bg-primary">
                    Assigned
                </span>
            `;

        case "In Progress":

            return `
                <span class="badge bg-warning text-dark">
                    In Progress
                </span>
            `;

        case "Resolved":

            return `
                <span class="badge bg-success">
                    Resolved
                </span>
            `;

        case "Closed":

            return `
                <span class="badge bg-secondary">
                    Closed
                </span>
            `;

        default:

            return `
                <span class="badge bg-secondary">
                    ${escapeHtml(
                        status || "-"
                    )}
                </span>
            `;

    }

}


/* ================================================== */
/* FORMAT DATE TIME */
/* ================================================== */

function formatDateTime(
    dateTime
) {

    if (!dateTime) {
        return "-";
    }


    const date =
        new Date(
            dateTime.replace(
                " ",
                "T"
            )
        );


    if (
        isNaN(
            date.getTime()
        )
    ) {

        return escapeHtml(
            dateTime
        );

    }


    return date.toLocaleString(
        "en-MY",
        {
            day: "2-digit",
            month: "2-digit",
            year: "numeric",
            hour: "2-digit",
            minute: "2-digit"
        }
    );

}


/* ================================================== */
/* ERROR */
/* ================================================== */

function showError(
    message
) {

    document.getElementById(
        "incidentDetails"
    ).innerHTML = `

        <div class="alert alert-danger">

            ${escapeHtml(
                message
            )}

        </div>

    `;

}


/* ================================================== */
/* ESCAPE HTML */
/* ================================================== */

function escapeHtml(
    value
) {

    const div =
        document.createElement(
            "div"
        );


    div.textContent =
        value ?? "";


    return div.innerHTML;

}
