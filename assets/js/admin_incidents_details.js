
// =====================================================
// ADMIN INCIDENT DETAILS
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

    // Setup the Admin note form
    setupAdminNoteForm();


    async function loadIncidentDetails() {

        try {

            const response = await fetch(
                `${window.BASE_URL}/app/controller/IncidentController.php?action=get_incident&incident_id=${encodeURIComponent(incidentId)}`
            );

            const data = await response.json();

            console.log(
                "Admin incident detail API response:",
                data
            );

            if (!data.success) {

                showError(
                    data.message ||
                    "Failed to load incident details."
                );

                return;
            }

            const incident = data.incident;

            if (!incident) {

                showError("Incident not found.");

                return;
            }

            displayIncidentInformation(incident);
            displayAssignmentInformation(incident);
            displaySLAInformation(incident);
            displayEscalationInformation(incident);
            displayAdminActions(incident);

            await loadAttachments();
            await loadUpdates();

        } catch (error) {

            console.error(
                "Error loading incident details:",
                error
            );

            showError(
                "Unable to load incident details."
            );

        }
    }


    // =====================================================
    // ADMIN NOTE FORM
    // =====================================================

    function setupAdminNoteForm() {

        const form =
            document.getElementById("adminNoteForm");

        if (!form) {
            console.error("Admin note form was not found.");
            return;
        }

        form.addEventListener(
            "submit",
            async function (event) {

                // Prevent normal form submission/navigation
                event.preventDefault();

                const messageInput =
                    document.getElementById("adminNoteMessage");

                const feedback =
                    document.getElementById("adminNoteFeedback");

                const button =
                    document.getElementById("addAdminNoteButton");

                if (!messageInput || !feedback || !button) {
                    console.error(
                        "Admin note form elements are missing."
                    );
                    return;
                }

                const message = messageInput.value.trim();

                if (!message) {

                    feedback.textContent =
                        "Please enter a note.";

                    feedback.className =
                        "mt-2 small text-danger";

                    return;
                }

                const formData = new FormData();

                formData.append(
                    "incident_id",
                    incidentId
                );

                formData.append(
                    "message",
                    message
                );

                button.disabled = true;
                feedback.textContent = "Saving note...";
                feedback.className =
                    "mt-2 small text-muted";

                try {

                    const response = await fetch(
                        `${window.BASE_URL}/app/controller/IncidentController.php?action=add_admin_note`,
                        {
                            method: "POST",
                            body: formData
                        }
                    );

                    const data = await response.json();

                    if (!response.ok || !data.success) {

                        throw new Error(
                            data.message ||
                            "Failed to save note."
                        );
                    }

                    messageInput.value = "";

                    feedback.textContent =
                        data.message ||
                        "Note added successfully.";

                    feedback.className =
                        "mt-2 small text-success";

                    // Refresh only the update history
                    await loadUpdates();

                } catch (error) {

                    console.error(
                        "Error adding admin note:",
                        error
                    );

                    feedback.textContent =
                        error.message ||
                        "Unable to save note.";

                    feedback.className =
                        "mt-2 small text-danger";

                } finally {

                    button.disabled = false;

                }
            }
        );
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

                    <strong>Reporter</strong>

                    <div>
                        ${escapeHtml(
                            incident.reporter_name || "-"
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

    }


    // =====================================================
    // ASSIGNMENT INFORMATION
    // =====================================================

    function displayAssignmentInformation(
        incident
    ) {

        const container =
            document.getElementById(
                "assignmentInformation"
            );


        if (!container) {
            return;
        }


        container.innerHTML = `

            <div class="row g-3">

                <div class="col-md-6">

                    <strong>Department</strong>

                    <div>

                        ${escapeHtml(
                            incident.department_name || "-"
                        )}

                    </div>

                </div>


                <div class="col-md-6">

                    <strong>Assigned Staff</strong>

                    <div>

                        ${escapeHtml(
                            incident.assigned_staff_name ||
                            "Unassigned"
                        )}

                    </div>

                </div>


                <div class="col-md-6">

                    <strong>Assignment Status</strong>

                    <div>

                        ${
                            incident.assigned_staff_id
                            ?
                            `
                                <span class="badge bg-success">
                                    Assigned
                                </span>
                            `
                            :
                            `
                                <span class="badge bg-secondary">
                                    Unassigned
                                </span>
                            `
                        }

                    </div>

                </div>

            </div>

        `;

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

                                <strong>Uploaded By</strong>

                                <div>

                                    ${escapeHtml(
                                        attachment.uploaded_by_name ||
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
    // ESCALATION INFORMATION
    // =====================================================

    function displayEscalationInformation(
        incident
    ) {

        const container =
            document.getElementById(
                "escalationInformation"
            );


        if (!container) {
            return;
        }


        const status =
            incident.escalation_status ||
            "None";


        let html = `

            <div class="mb-3">

                <strong>Escalation Status</strong>

                <div class="mt-1">

                    ${getEscalationBadge(
                        status
                    )}

                </div>

            </div>

        `;


        if (
            incident.escalation_reason
        ) {

            html += `

                <div>

                    <strong>Escalation Reason</strong>

                    <div class="mt-1">

                        ${escapeHtml(
                            incident.escalation_reason
                        ).replace(
                            /\n/g,
                            "<br>"
                        )}

                    </div>

                </div>

            `;

        }


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
// ADMIN ACTIONS
// =====================================================

function displayAdminActions(incident) {

    const container =
        document.getElementById("adminActions");

    if (!container) {
        return;
    }

    let html = "";


    // =================================================
    // UPDATE PRIORITY
    // =================================================

    if (incident.status !== "Closed") {

        html += `
            <button
                type="button"
                class="btn btn-warning me-2 mb-2"
                id="updatePriorityBtn"
            >
                Update Priority
            </button>
        `;
    }


    // =================================================
    // REASSIGN
    // =================================================

    if (incident.status !== "Closed") {

        html += `
            <button
                type="button"
                class="btn btn-primary me-2 mb-2"
                id="reassignIncidentBtn"
            >
                Reassign Incident
            </button>
        `;
    }


    // =================================================
    // MARK ESCALATION AS REVIEWED
    // =================================================

    if (incident.escalation_status === "Raised") {

        html += `
            <button
                type="button"
                class="btn btn-success me-2 mb-2"
                id="reviewEscalationBtn"
            >
                Mark as Reviewed
            </button>
        `;
    }


    // =================================================
    // CLOSE
    // =================================================

    if (incident.status === "Resolved") {

        html += `
            <button
                type="button"
                class="btn btn-success me-2 mb-2"
                id="closeIncidentBtn"
            >
                Close Incident
            </button>
        `;
    }


    // =================================================
    // NO AVAILABLE ACTIONS
    // =================================================

    if (!html) {

        html = `
            <span class="text-muted">
                No administrative actions are available.
            </span>
        `;
    }


    container.innerHTML = html;


    // =================================================
    // UPDATE PRIORITY BUTTON
    // =================================================

    const updatePriorityButton =
        document.getElementById("updatePriorityBtn");

    if (updatePriorityButton) {

        updatePriorityButton.addEventListener(
            "click",
            function () {
                showUpdatePriorityForm(incident);
            }
        );
    }


    // =================================================
    // REASSIGN BUTTON
    // =================================================

    const reassignButton =
        document.getElementById("reassignIncidentBtn");

    if (reassignButton) {

        reassignButton.addEventListener(
            "click",
            function () {
                showReassignForm(incident);
            }
        );
    }


    // =================================================
    // REVIEW ESCALATION BUTTON
    // =================================================

    const reviewButton =
        document.getElementById("reviewEscalationBtn");

    if (reviewButton) {

        reviewButton.addEventListener(
            "click",
            function () {
                reviewIncidentEscalation(
                    incident.incident_id
                );
            }
        );
    }


    // =================================================
    // CLOSE BUTTON
    // =================================================

    const closeButton =
        document.getElementById("closeIncidentBtn");

    if (closeButton) {

        closeButton.addEventListener(
            "click",
            function () {
                closeIncident(incident.incident_id);
            }
        );
    }

}
// =====================================================
// REVIEW INCIDENT ESCALATION
// =====================================================

async function reviewIncidentEscalation(incidentId) {

    if (!confirm("Mark this escalation as reviewed?")) {
        return;
    }

    try {

        const formData = new FormData();
        formData.append("incident_id", incidentId);

        const response = await fetch(
            `${BASE_URL}/app/controller/AdminIncidentController.php?action=review_escalation`,
            {
                method: "POST",
                body: formData
            }
        );

        const data = await response.json();

        if (!data.success) {
            alert(data.message || "Unable to review escalation.");
            return;
        }

        alert(data.message);

        loadIncidentDetails();

    } catch (error) {

        console.error("Review escalation error:", error);
        alert("Unable to review escalation.");

    }
}



    

    
    // =====================================================
    // SHOW UPDATE PRIORITY FORM
    // =====================================================

    function showUpdatePriorityForm(
        incident
    ) {

        const container =
            document.getElementById(
                "adminActions"
            );


        if (!container) {
            return;
        }


        const allowedPriorities = [
            "Low",
            "Medium",
            "High",
            "Emergency"
        ];


        const currentPriority =
            allowedPriorities.includes(
                incident.priority
            )
                ? incident.priority
                : "Medium";


        container.innerHTML = `

            <div class="border rounded p-3">

                <div class="mb-3">

                    <strong>
                        Update Incident Priority
                    </strong>

                </div>


                <div class="mb-3">

                    <label
                        for="incidentPrioritySelect"
                        class="form-label"
                    >
                        Priority
                    </label>

                    <select
                        id="incidentPrioritySelect"
                        class="form-select"
                    >

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


                <div class="text-muted small mb-3">

                    Changing the priority will recalculate
                    the SLA deadline.

                </div>


                <button
                    type="button"
                    class="btn btn-warning me-2"
                    id="confirmUpdatePriorityBtn"
                >
                    Update Priority
                </button>


                <button
                    type="button"
                    class="btn btn-secondary"
                    id="cancelUpdatePriorityBtn"
                >
                    Cancel
                </button>


                <div
                    id="priorityUpdateMessage"
                    class="mt-3"
                ></div>

            </div>

        `;


        const prioritySelect =
            document.getElementById(
                "incidentPrioritySelect"
            );


        const confirmButton =
            document.getElementById(
                "confirmUpdatePriorityBtn"
            );


        const cancelButton =
            document.getElementById(
                "cancelUpdatePriorityBtn"
            );


        const messageContainer =
            document.getElementById(
                "priorityUpdateMessage"
            );


        // =================================================
        // SELECT CURRENT PRIORITY
        // =================================================

        prioritySelect.value =
            currentPriority;


        // =================================================
        // CANCEL
        // =================================================

        cancelButton.addEventListener(
            "click",
            function () {

                displayAdminActions(
                    incident
                );

            }
        );


        // =================================================
        // CONFIRM UPDATE PRIORITY
        // =================================================

        confirmButton.addEventListener(
            "click",
            async function () {

                const newPriority =
                    prioritySelect.value;


                if (
                    !allowedPriorities.includes(
                        newPriority
                    )
                ) {

                    messageContainer.innerHTML = `

                        <div class="alert alert-danger">

                            Please select a valid priority.

                        </div>

                    `;

                    return;
                }


                if (
                    newPriority === incident.priority
                ) {

                    messageContainer.innerHTML = `

                        <div class="alert alert-info">

                            The selected priority is already
                            assigned to this incident.

                        </div>

                    `;

                    return;
                }


                if (
                    !confirm(
                        `Change priority from ${incident.priority} to ${newPriority}? The SLA deadline will be recalculated.`
                    )
                ) {

                    return;

                }


                confirmButton.disabled =
                    true;


                cancelButton.disabled =
                    true;


                prioritySelect.disabled =
                    true;


                confirmButton.textContent =
                    "Updating...";


                messageContainer.innerHTML = "";


                try {

                    const formData =
                        new FormData();


                    formData.append(
                        "incident_id",
                        incident.incident_id
                    );


                    formData.append(
                        "priority",
                        newPriority
                    );


                    const response =
                        await fetch(
                            `${window.BASE_URL}/app/controller/AdminIncidentController.php?action=update_priority`,
                            {
                                method: "POST",
                                body: formData
                            }
                        );


                    const data =
                        await response.json();


                    if (!data.success) {

                        messageContainer.innerHTML = `

                            <div class="alert alert-danger">

                                ${escapeHtml(
                                    data.message ||
                                    "Failed to update incident priority."
                                )}

                            </div>

                        `;


                        confirmButton.disabled =
                            false;


                        cancelButton.disabled =
                            false;


                        prioritySelect.disabled =
                            false;


                        confirmButton.textContent =
                            "Update Priority";

                        return;
                    }


                    messageContainer.innerHTML = `

                        <div class="alert alert-success">

                            ${escapeHtml(
                                data.message ||
                                "Priority updated successfully."
                            )}

                        </div>

                    `;


                    // Reload incident details, SLA and history.

                    await loadIncidentDetails();


                } catch (error) {

                    console.error(
                        "Error updating incident priority:",
                        error
                    );


                    messageContainer.innerHTML = `

                        <div class="alert alert-danger">

                            Unable to update incident priority.
                            Please try again.

                        </div>

                    `;


                    confirmButton.disabled =
                        false;


                    cancelButton.disabled =
                        false;


                    prioritySelect.disabled =
                        false;


                    confirmButton.textContent =
                        "Update Priority";

                }

            }
        );

    }

    


    // =====================================================
    // SHOW REASSIGN FORM
    // =====================================================

    async function showReassignForm(
        incident
    ) {

        const container =
            document.getElementById(
                "adminActions"
            );


        if (!container) {
            return;
        }


        container.innerHTML = `

            <div class="border rounded p-3">

                <div class="mb-3">

                    <strong>
                        Reassign Incident
                    </strong>

                </div>


                <div class="mb-3">

                    <label
                        for="reassignDepartment"
                        class="form-label"
                    >
                        Department
                    </label>

                    <select
                        id="reassignDepartment"
                        class="form-select"
                    >

                        <option value="">
                            Loading departments...
                        </option>

                    </select>

                </div>


                <div class="mb-3">

                    <label
                        for="reassignStaff"
                        class="form-label"
                    >
                        Response Staff
                    </label>

                    <select
                        id="reassignStaff"
                        class="form-select"
                        disabled
                    >

                        <option value="">
                            Select department first
                        </option>

                    </select>

                </div>


                <button
                    type="button"
                    class="btn btn-primary me-2"
                    id="confirmReassignBtn"
                    disabled
                >
                    Reassign Incident
                </button>


                <button
                    type="button"
                    class="btn btn-secondary"
                    id="cancelReassignBtn"
                >
                    Cancel
                </button>


                <div
                    id="reassignMessage"
                    class="mt-3"
                ></div>

            </div>

        `;


        const departmentSelect =
            document.getElementById(
                "reassignDepartment"
            );


        const staffSelect =
            document.getElementById(
                "reassignStaff"
            );


        const confirmButton =
            document.getElementById(
                "confirmReassignBtn"
            );


        const cancelButton =
            document.getElementById(
                "cancelReassignBtn"
            );


        const messageContainer =
            document.getElementById(
                "reassignMessage"
            );

        // =================================================
        // REVIEW ESCALATION BUTTON
        // =================================================

        const reviewButton =
            document.getElementById("reviewEscalationBtn");

        if (reviewButton) {

            reviewButton.addEventListener(
                "click",
                function () {

                    reviewIncidentEscalation(
                        incident.incident_id
                    );

                }
            );

        }


        // =================================================
        // CANCEL
        // =================================================

        cancelButton.addEventListener(
            "click",
            function () {

                displayAdminActions(
                    incident
                );

            }
        );


        // =================================================
        // LOAD DEPARTMENTS
        // =================================================

        await loadDepartments();


        // =================================================
        // DEPARTMENT CHANGE
        // =================================================

        departmentSelect.addEventListener(
            "change",
            async function () {

                const departmentId =
                    this.value;


                staffSelect.innerHTML = `

                    <option value="">
                        Select response staff
                    </option>

                `;


                staffSelect.disabled =
                    true;


                confirmButton.disabled =
                    true;


                if (!departmentId) {
                    return;
                }


                await loadResponseStaff(
                    departmentId
                );

            }
        );


        // =================================================
        // STAFF CHANGE
        // =================================================

        staffSelect.addEventListener(
            "change",
            function () {

                const selectedStaffId =
                    this.value;


                const selectedDepartmentId =
                    departmentSelect.value;


                /*
                 * Do not allow the Admin to submit
                 * exactly the same assignment.
                 */

                if (
                    selectedStaffId &&
                    String(selectedStaffId) ===
                    String(incident.assigned_staff_id) &&
                    String(selectedDepartmentId) ===
                    String(incident.department_id)
                ) {

                    confirmButton.disabled =
                        true;

                    messageContainer.innerHTML = `

                        <div class="text-muted small">

                            This incident is already assigned
                            to this response staff.

                        </div>

                    `;

                    return;
                }


                messageContainer.innerHTML = "";


                confirmButton.disabled =
                    !selectedStaffId;

            }
        );


        // =================================================
        // CONFIRM REASSIGN
        // =================================================

        confirmButton.addEventListener(
            "click",
            async function () {

                const departmentId =
                    departmentSelect.value;


                const staffId =
                    staffSelect.value;


                if (
                    !departmentId ||
                    !staffId
                ) {

                    return;
                }


                confirmButton.disabled =
                    true;


                confirmButton.textContent =
                    "Reassigning...";


                messageContainer.innerHTML = "";


                try {

                    const formData =
                        new FormData();


                    formData.append(
                        "incident_id",
                        incident.incident_id
                    );


                    formData.append(
                        "department_id",
                        departmentId
                    );


                    formData.append(
                        "assigned_staff_id",
                        staffId
                    );


                    const response =
                        await fetch(
                            `${window.BASE_URL}/app/controller/AdminIncidentController.php?action=reassign_incident`,
                            {
                                method: "POST",
                                body: formData
                            }
                        );


                    const data =
                        await response.json();


                    if (!data.success) {

                        messageContainer.innerHTML = `

                            <div class="alert alert-danger">

                                ${escapeHtml(
                                    data.message ||
                                    "Failed to reassign incident."
                                )}

                            </div>

                        `;


                        confirmButton.disabled =
                            false;


                        confirmButton.textContent =
                            "Reassign Incident";

                        return;
                    }


                    messageContainer.innerHTML = `

                        <div class="alert alert-success">

                            ${escapeHtml(
                                data.message ||
                                "Incident reassigned successfully."
                            )}

                        </div>

                    `;


                    await loadIncidentDetails();


                } catch (error) {

                    console.error(
                        "Error reassigning incident:",
                        error
                    );


                    messageContainer.innerHTML = `

                        <div class="alert alert-danger">

                            Unable to reassign incident.

                        </div>

                    `;


                    confirmButton.disabled =
                        false;


                    confirmButton.textContent =
                        "Reassign Incident";

                }

            }
        );

        


        // =================================================
        // LOAD DEPARTMENTS
        // =================================================

        async function loadDepartments() {

            departmentSelect.innerHTML = `

                <option value="">
                    Loading departments...
                </option>

            `;


            departmentSelect.disabled =
                true;


            try {

                const response =
                    await fetch(
                        `${window.BASE_URL}/app/controller/AdminIncidentController.php?action=get_departments`
                    );


                console.log(
                    "Department response status:",
                    response.status
                );


                const responseText =
                    await response.text();


                console.log(
                    "Department response:",
                    responseText
                );


                let data;


                try {

                    data =
                        JSON.parse(
                            responseText
                        );

                } catch (jsonError) {

                    console.error(
                        "Department API did not return JSON:",
                        jsonError
                    );


                    departmentSelect.innerHTML = `

                        <option value="">
                            Invalid server response
                        </option>

                    `;

                    return;
                }


                if (
                    !data.success
                ) {

                    departmentSelect.innerHTML = `

                        <option value="">
                            ${escapeHtml(
                                data.message ||
                                "Unable to load departments."
                            )}
                        </option>

                    `;

                    return;
                }


                if (
                    !Array.isArray(
                        data.departments
                    )
                    ||
                    data.departments.length === 0
                ) {

                    departmentSelect.innerHTML = `

                        <option value="">
                            No active departments found
                        </option>

                    `;

                    return;
                }


                departmentSelect.innerHTML = `

                    <option value="">
                        Select department
                    </option>

                `;


                data.departments.forEach(
                    function (department) {

                        const option =
                            document.createElement(
                                "option"
                            );


                        option.value =
                            department.department_id;


                        option.textContent =
                            department.department_name;


                        if (
                            String(
                                department.department_id
                            ) ===
                            String(
                                incident.department_id
                            )
                        ) {

                            option.selected =
                                true;

                        }


                        departmentSelect.appendChild(
                            option
                        );

                    }
                );


                departmentSelect.disabled =
                    false;


                /*
                 * Load staff for the current
                 * department after departments
                 * have loaded successfully.
                 */

                if (
                    incident.department_id
                ) {

                    await loadResponseStaff(
                        incident.department_id
                    );

                }

            } catch (error) {

                console.error(
                    "Error loading departments:",
                    error
                );


                departmentSelect.innerHTML = `

                    <option value="">
                        Unable to connect to server
                    </option>

                `;

            }

        }


        // =================================================
        // LOAD RESPONSE STAFF
        // =================================================

        async function loadResponseStaff(
            departmentId
        ) {

            staffSelect.innerHTML = `

                <option value="">
                    Loading response staff...
                </option>

            `;


            staffSelect.disabled =
                true;


            confirmButton.disabled =
                true;


            try {

                const response =
                    await fetch(
                        `${window.BASE_URL}/app/controller/AdminIncidentController.php?action=get_response_staff&department_id=${encodeURIComponent(departmentId)}`
                    );


                console.log(
                    "Response staff response status:",
                    response.status
                );


                const responseText =
                    await response.text();


                console.log(
                    "Response staff response:",
                    responseText
                );


                let data;


                try {

                    data =
                        JSON.parse(
                            responseText
                        );

                } catch (jsonError) {

                    console.error(
                        "Response staff API did not return JSON:",
                        jsonError
                    );


                    staffSelect.innerHTML = `

                        <option value="">
                            Invalid server response
                        </option>

                    `;

                    return;
                }


                if (
                    !data.success
                ) {

                    staffSelect.innerHTML = `

                        <option value="">
                            ${escapeHtml(
                                data.message ||
                                "Unable to load response staff."
                            )}
                        </option>

                    `;

                    return;
                }


                if (
                    !Array.isArray(
                        data.staff
                    )
                    ||
                    data.staff.length === 0
                ) {

                    staffSelect.innerHTML = `

                        <option value="">
                            No available response staff
                        </option>

                    `;

                    return;
                }


                staffSelect.innerHTML = `

                    <option value="">
                        Select response staff
                    </option>

                `;


                data.staff.forEach(
                    function (staff) {

                        const option =
                            document.createElement(
                                "option"
                            );


                        option.value =
                            staff.user_id;


                        option.textContent =
                            `${staff.full_name} (${staff.user_type})`;


                        staffSelect.appendChild(
                            option
                        );

                    }
                );


                staffSelect.disabled =
                    false;


                /*
                 * Do not automatically select the
                 * existing staff.
                 *
                 * Admin must actively choose
                 * the new staff.
                 */

                confirmButton.disabled =
                    true;


            } catch (error) {

                console.error(
                    "Error loading response staff:",
                    error
                );


                staffSelect.innerHTML = `

                    <option value="">
                        Unable to connect to server
                    </option>

                `;

            }

        }

    }


    // =====================================================
    // CLOSE INCIDENT
    // =====================================================

    async function closeIncident(
        incidentId
    ) {

        if (
            !confirm(
                "Are you sure you want to close this incident?"
            )
        ) {

            return;
        }


        try {

            const formData =
                new FormData();


            formData.append(
                "incident_id",
                incidentId
            );


            const response =
                await fetch(
                    `${window.BASE_URL}/app/controller/AdminIncidentController.php?action=close_incident`,
                    {
                        method: "POST",
                        body: formData
                    }
                );


            const data =
                await response.json();


            if (!data.success) {

                alert(
                    data.message ||
                    "Failed to close incident."
                );

                return;
            }


            alert(
                data.message ||
                "Incident closed successfully."
            );


            await loadIncidentDetails();


        } catch (error) {

            console.error(
                "Error closing incident:",
                error
            );


            alert(
                "Unable to close incident."
            );

        }

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
    // ESCALATION BADGE
    // =====================================================

    function getEscalationBadge(
        status
    ) {

        if (
            !status ||
            status === "None"
        ) {

            return `

                <span class="badge bg-secondary">
                    None
                </span>

            `;

        }


        if (
            status === "Raised"
        ) {

            return `

                <span class="badge bg-danger">
                    Raised
                </span>

            `;

        }


        if (
            status === "Reviewed"
        ) {

            return `

                <span class="badge bg-success">
                    Reviewed
                </span>

            `;

        }


        return `

            <span class="badge bg-secondary">
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
            "assignmentInformation",
            "slaInformation",
            "attachmentsInformation",
            "escalationInformation",
            "updatesInformation",
            "adminActions"

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
