
/* ===================================================== */
// ADMIN INCIDENT MANAGEMENT
/* ===================================================== */

document.addEventListener("DOMContentLoaded", function () {

    let incidents = [];


    // =====================================================
    // LOAD INCIDENTS
    // =====================================================

    loadIncidents();


    async function loadIncidents() {

        try {

            const response = await fetch(
                `${window.BASE_URL}/app/controller/IncidentController.php?action=get_all_incidents`
            );

            const data = await response.json();

            console.log("Incident API response:", data);

            if (!data.success) {

                showTableMessage(
                    data.message || "Failed to load incident reports."
                );

                return;
            }

            incidents = data.incidents || [];

            updateStatistics(incidents);

            // Update overdue notification separately.
            updateOverdueAlert(incidents);

            populateDepartmentFilter(incidents);

            applyFilters();

        } catch (error) {

            console.error("Error loading incidents:", error);

            showTableMessage(
                "Unable to load incident reports."
            );
        }
    }


    // =====================================================
    // UPDATE STATISTICS
    // =====================================================

    function updateStatistics(data) {

        const total = data.length;

        const unsolved = data.filter(function (incident) {

            return incident.status !== "Resolved"
                && incident.status !== "Closed";

        }).length;


        const escalated = data.filter(function (incident) {

            return incident.escalation_status === "Raised";

        }).length;


        // Keep the original overdue statistic.
        // Count every incident whose SLA status is Overdue.
        const overdue = data.filter(function (incident) {

            return incident.sla_status === "Overdue";

        }).length;


        document.getElementById("totalIncidents").textContent =
            total;

        document.getElementById("unsolvedIncidents").textContent =
            unsolved;

        document.getElementById("escalatedIncidents").textContent =
            escalated;

        document.getElementById("overdueIncidents").textContent =
            overdue;
    }


    // =====================================================
    // POPULATE DEPARTMENT FILTER
    // =====================================================

    function populateDepartmentFilter(data) {

        const select =
            document.getElementById("filter-department");

        if (!select) {
            return;
        }


        const departments = [];

        // Keep the default option and avoid adding duplicates.
        data.forEach(function (incident) {

            const department =
                incident.department_name;

            if (
                department &&
                !departments.includes(department)
            ) {

                departments.push(department);

            }

        });


        departments.sort();


        departments.forEach(function (department) {

            // Avoid adding the same department option again.
            const existingOption = Array.from(
                select.options
            ).some(function (option) {

                return option.value === department;

            });

            if (existingOption) {
                return;
            }

            const option =
                document.createElement("option");

            option.value = department;

            option.textContent = department;

            select.appendChild(option);

        });

    }


    // =====================================================
    // FILTER EVENTS
    // =====================================================

    document
        .getElementById("searchInput")
        .addEventListener("input", applyFilters);


    document
        .getElementById("filter-status")
        .addEventListener("change", applyFilters);


    document
        .getElementById("filter-priority")
        .addEventListener("change", applyFilters);


    document
        .getElementById("filter-department")
        .addEventListener("change", applyFilters);


    document
        .getElementById("filter-sla")
        .addEventListener("change", applyFilters);


    // =====================================================
    // APPLY FILTERS
    // =====================================================

    function applyFilters() {

        const searchValue =
            document
                .getElementById("searchInput")
                .value
                .trim()
                .toLowerCase();


        const statusValue =
            document
                .getElementById("filter-status")
                .value;


        const priorityValue =
            document
                .getElementById("filter-priority")
                .value;


        const departmentValue =
            document
                .getElementById("filter-department")
                .value;


        const slaValue =
            document
                .getElementById("filter-sla")
                .value;


        const filteredIncidents =
            incidents.filter(function (incident) {


                // -----------------------------------------
                // SEARCH
                // -----------------------------------------

                const incidentId =
                    String(
                        incident.incident_id ?? ""
                    ).toLowerCase();


                const title =
                    String(
                        incident.title ?? ""
                    ).toLowerCase();


                const category =
                    String(
                        incident.category_name ?? ""
                    ).toLowerCase();


                const matchesSearch =
                    searchValue === ""
                    ||
                    incidentId.includes(searchValue)
                    ||
                    title.includes(searchValue)
                    ||
                    category.includes(searchValue);


                // -----------------------------------------
                // STATUS
                // -----------------------------------------

                const matchesStatus =
                    statusValue === ""
                    ||
                    incident.status === statusValue;


                // -----------------------------------------
                // PRIORITY
                // -----------------------------------------

                const matchesPriority =
                    priorityValue === ""
                    ||
                    incident.priority === priorityValue;


                // -----------------------------------------
                // DEPARTMENT
                // -----------------------------------------

                const matchesDepartment =
                    departmentValue === ""
                    ||
                    incident.department_name === departmentValue;


                // -----------------------------------------
                // SLA
                // -----------------------------------------

                const matchesSla =
                    slaValue === ""
                    ||
                    incident.sla_status === slaValue;


                return (
                    matchesSearch
                    &&
                    matchesStatus
                    &&
                    matchesPriority
                    &&
                    matchesDepartment
                    &&
                    matchesSla
                );

            });


        renderIncidentTable(filteredIncidents);

    }


    // =====================================================
    // RENDER INCIDENT TABLE
    // =====================================================

    function renderIncidentTable(data) {

        const tbody =
            document.getElementById(
                "incidentTableBody"
            );


        if (!tbody) {
            return;
        }


        if (data.length === 0) {

            showTableMessage(
                "No incident reports found."
            );

            return;
        }


        tbody.innerHTML = "";


        data.forEach(function (incident) {

            const row =
                document.createElement("tr");


            row.innerHTML = `

                <td>
                    #${escapeHtml(
                        incident.incident_id
                    )}
                </td>


                <td>

                    <div class="fw-semibold">

                        ${escapeHtml(
                            incident.title
                        )}

                    </div>

                </td>


                <td>
                    ${escapeHtml(
                        incident.category_name || "-"
                    )}
                </td>


                <td>
                    ${escapeHtml(
                        incident.department_name || "-"
                    )}
                </td>


                <td>
                    ${getPriorityBadge(
                        incident.priority
                    )}
                </td>


                <td>
                    ${escapeHtml(
                        incident.assigned_staff_name || "Unassigned"
                    )}
                </td>


                <td>
                    ${getStatusBadge(
                        incident.status
                    )}
                </td>


                <td>
                    ${getSlaBadge(
                        incident.sla_status
                    )}
                </td>


                <td>
                    ${getEscalationBadge(
                        incident.escalation_status
                    )}
                </td>


                <td class="text-center">

                    <a
                        href="${window.BASE_URL}/app/view/dashboard.php?page=admin_incident_details&id=${encodeURIComponent(
                            incident.incident_id
                        )}"
                        class="btn btn-sm btn-primary"
                    >

                        <i class="bi bi-eye me-1"></i>

                        View

                    </a>

                </td>

            `;


            tbody.appendChild(row);

        });

    }


    // =====================================================
    // PRIORITY BADGE
    // =====================================================

    function getPriorityBadge(priority) {

        if (!priority) {

            return `
                <span class="badge bg-secondary">
                    -
                </span>
            `;

        }


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
                ${escapeHtml(priority)}
            </span>
        `;

    }


    // =====================================================
    // STATUS BADGE
    // =====================================================

    function getStatusBadge(status) {

        if (!status) {

            return `
                <span class="badge bg-secondary">
                    -
                </span>
            `;

        }


        let badgeClass = "bg-secondary";


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

    function getSlaBadge(slaStatus) {

        if (!slaStatus) {

            return `
                <span class="badge bg-secondary">
                    -
                </span>
            `;

        }


        let badgeClass = "bg-secondary";


        switch (slaStatus) {

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
                ${escapeHtml(slaStatus)}
            </span>
        `;

    }


    // =====================================================
    // OVERDUE INCIDENT ALERT
    // =====================================================

    function updateOverdueAlert(incidents) {

        console.log("Overdue alert function called:", incidents.length);

        const alertSection = document.getElementById(
            "overdueAlertSection"
        );

        const alertMessage = document.getElementById(
            "overdueAlertMessage"
        );

        const alertList = document.getElementById(
            "overdueAlertList"
        );

        if (!alertSection || !alertMessage || !alertList) {
            return;
        }


        // Only include overdue incidents that are not resolved
        // or closed. The overdue statistic remains unchanged.
        const overdueCases = incidents.filter(function (incident) {

            const sla = String(
                incident.sla_status ?? ""
            ).trim().toLowerCase();

            const status = String(
                incident.status ?? ""
            ).trim().toLowerCase();

            const isResolved =
                status === "resolved" ||
                status === "closed";

            return sla === "overdue" && !isResolved;

        });


        // Clear the previous alert list.
        alertList.replaceChildren();


        // Hide the notification when no unresolved overdue cases remain.
        if (overdueCases.length === 0) {

            alertSection.style.display = "none";
            return;

        }


        // Display the notification.
        alertSection.style.display = "block";

        alertMessage.textContent =
            overdueCases.length +
            " unresolved incident(s) have exceeded their SLA deadline and require follow-up.";


        overdueCases.forEach(function (incident) {

            const item = document.createElement("li");

            const incidentId =
                incident.incident_id ?? "N/A";

            const incidentTitle =
                incident.title ?? "Untitled incident";


            // Display incident details as text, not HTML.
            item.textContent =
                String(incidentId) +
                " - " +
                String(incidentTitle);

            alertList.appendChild(item);

        });

    }


    // =====================================================
    // VIEW OVERDUE INCIDENTS BUTTON
    // =====================================================

    document
        .getElementById("viewOverdueIncidents")
        ?.addEventListener("click", function () {

            const slaFilter = document.getElementById(
                "filter-sla"
            );

            if (slaFilter) {

                slaFilter.value = "Overdue";

                slaFilter.dispatchEvent(
                    new Event("change", {
                        bubbles: true
                    })
                );

            }

        });


    // =====================================================
    // ESCALATION BADGE
    // =====================================================

    function getEscalationBadge(escalationStatus) {

        if (
            !escalationStatus ||
            escalationStatus === "None"
        ) {

            return `
                <span class="badge bg-secondary">
                    None
                </span>
            `;

        }


        if (escalationStatus === "Raised") {

            return `
                <span class="badge bg-danger">
                    Raised
                </span>
            `;

        }


        if (escalationStatus === "Reviewed") {

            return `
                <span class="badge bg-success">
                    Reviewed
                </span>
            `;

        }


        return `
            <span class="badge bg-secondary">
                ${escapeHtml(
                    escalationStatus
                )}
            </span>
        `;

    }


    // =====================================================
    // EMPTY / ERROR TABLE MESSAGE
    // =====================================================

    function showTableMessage(message) {

        const tbody =
            document.getElementById(
                "incidentTableBody"
            );


        if (!tbody) {
            return;
        }


        tbody.innerHTML = `

            <tr>

                <td
                    colspan="10"
                    class="text-center py-4 text-muted"
                >

                    ${escapeHtml(message)}

                </td>

            </tr>

        `;

    }


    // =====================================================
    // HTML ESCAPE
    // =====================================================

    function escapeHtml(value) {

        if (value === null || value === undefined) {

            return "";

        }


        return String(value)
            .replace(/&/g, "&amp;")
            .replace(/</g, "&lt;")
            .replace(/>/g, "&gt;")
            .replace(/"/g, "&quot;")
            .replace(/'/g, "&#039;");

    }

});
