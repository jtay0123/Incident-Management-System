document.addEventListener("DOMContentLoaded", () => {

    let allReports = [];


    // ================================
    // DOM ELEMENTS
    // ================================

    const searchInput = document.getElementById("searchInput");
    const statusFilter = document.getElementById("filter-status");
    const priorityFilter = document.getElementById("filter-priority");
    const categoryFilter = document.getElementById("filter-category");
    const reportTableBody = document.getElementById("reportTableBody");


    // ================================
    // LOAD REPORTS
    // ================================

    async function loadReports() {

        reportTableBody.innerHTML = `
            <tr>
                <td colspan="7" class="text-center py-4 text-muted">
                    Loading your reports...
                </td>
            </tr>
        `;

        try {

            const response = await fetch(
                `${window.BASE_URL}/app/controller/IncidentController.php?action=get_my_incidents`
            );

            const data = await response.json();

            console.log("Reporter my reports API response:", data);

            if (!data.success) {

                reportTableBody.innerHTML = `
                    <tr>
                        <td colspan="7" class="text-center py-4 text-danger">
                            ${escapeHtml(data.message || "Failed to load reports.")}
                        </td>
                    </tr>
                `;

                return;
            }


            allReports = Array.isArray(data.incidents)
                ? data.incidents
                : [];

            // Show incidents waiting for Reporter approval.
            updateResolutionApprovalAlert(allReports);

            updateStatistics();

            populateCategoryFilter();

            renderReports();

        } catch (error) {

            console.error("Error loading reporter reports:", error);

            reportTableBody.innerHTML = `
                <tr>
                    <td colspan="7" class="text-center py-4 text-danger">
                        Failed to load your reports.
                    </td>
                </tr>
            `;

        }

    }


    // ================================
    // UPDATE STATISTICS
    // ================================

    function updateStatistics() {

        const total = allReports.length;


        const unresolved = allReports.filter(report =>
            report.status !== "Resolved" &&
            report.status !== "Closed"
        ).length;


        const resolved = allReports.filter(report =>
            report.status === "Resolved"
        ).length;


        const closed = allReports.filter(report =>
            report.status === "Closed"
        ).length;


        document.getElementById("totalReports").textContent = total;

        document.getElementById("unresolvedReports").textContent = unresolved;

        document.getElementById("resolvedReports").textContent = resolved;

        document.getElementById("closedReports").textContent = closed;

    }


    // ================================
    // CATEGORY FILTER
    // ================================

    function populateCategoryFilter() {

        const categories = [
            ...new Set(
                allReports
                    .map(report => report.category_name)
                    .filter(category => category)
            )
        ];


        categories.sort();


        categoryFilter.innerHTML = `
            <option value="">
                All Categories
            </option>
        `;


        categories.forEach(category => {

            const option = document.createElement("option");

            option.value = category;

            option.textContent = category;

            categoryFilter.appendChild(option);

        });

    }


    // ================================
    // FILTER REPORTS
    // ================================

    function getFilteredReports() {

        const searchValue =
            searchInput.value.trim().toLowerCase();

        const selectedStatus =
            statusFilter.value;

        const selectedPriority =
            priorityFilter.value;

        const selectedCategory =
            categoryFilter.value;


        return allReports.filter(report => {

            const incidentId =
                String(report.incident_id || "").toLowerCase();

            const title =
                String(report.title || "").toLowerCase();

            const category =
                String(report.category_name || "").toLowerCase();


            const matchesSearch =
                searchValue === "" ||
                incidentId.includes(searchValue) ||
                title.includes(searchValue) ||
                category.includes(searchValue);


            const matchesStatus =
                selectedStatus === "" ||
                report.status === selectedStatus;


            const matchesPriority =
                selectedPriority === "" ||
                report.priority === selectedPriority;


            const matchesCategory =
                selectedCategory === "" ||
                report.category_name === selectedCategory;


            return (
                matchesSearch &&
                matchesStatus &&
                matchesPriority &&
                matchesCategory
            );

        });

    }


    // ================================
    // RENDER REPORT TABLE
    // ================================

    function renderReports() {

        const filteredReports = getFilteredReports();


        if (filteredReports.length === 0) {

            reportTableBody.innerHTML = `
                <tr>
                    <td
                        colspan="7"
                        class="text-center py-5 text-muted"
                    >

                        <i class="bi bi-clipboard-x fs-3 d-block mb-2"></i>

                        No reports found.

                    </td>
                </tr>
            `;

            return;
        }


        reportTableBody.innerHTML = "";


        filteredReports.forEach(report => {

            const row = document.createElement("tr");


            row.innerHTML = `

                <td>
                    <strong>#${escapeHtml(report.incident_id)}</strong>
                </td>


                <td>

                    <div class="fw-semibold">
                        ${escapeHtml(report.title || "-")}
                    </div>

                </td>


                <td>
                    ${escapeHtml(report.category_name || "-")}
                </td>


                <td>
                    ${getPriorityBadge(report.priority)}
                </td>


                <td>
                    ${getStatusBadge(report.status)}
                </td>


                <td>
                    ${formatDate(report.submitted_at)}
                </td>


                <td class="text-center">

                    <button
                        type="button"
                        class="btn btn-sm btn-primary"
                        onclick="viewReport(${Number(report.incident_id)})"
                    >

                        <i class="bi bi-eye me-1"></i>

                        View

                    </button>

                </td>

            `;


            reportTableBody.appendChild(row);

        });

    }


        // =====================================================
    // PENDING RESOLUTION APPROVAL ALERT
    // =====================================================

    function updateResolutionApprovalAlert(incidents) {

        console.log(
            "Resolution approval alert function called:",
            incidents.length
        );

        const alertSection = document.getElementById(
            "resolutionApprovalAlertSection"
        );

        const alertMessage = document.getElementById(
            "resolutionApprovalAlertMessage"
        );

        const alertList = document.getElementById(
            "resolutionApprovalAlertList"
        );

        if (!alertSection || !alertMessage || !alertList) {
            return;
        }

        // Only include incidents waiting for Reporter approval.
        const pendingCases = incidents.filter(function (incident) {

            return (
                String(incident.status ?? "").trim() === "Resolved" &&
                Number(incident.reporter_approved) === 0
            );

        });

        console.table(incidents.map(incident => ({
            id: incident.incident_id,
            status: incident.status,
            reporter_approved: incident.reporter_approved
        })));

        // Clear previous alerts.
        alertList.replaceChildren();

        if (pendingCases.length === 0) {

            alertSection.style.display = "none";
            return;

        }

        // Display notification.
        alertSection.style.display = "block";

        alertMessage.textContent =
            pendingCases.length +
            " incident(s) have been resolved and require your confirmation.";

        pendingCases.forEach(function (incident) {

        const item = document.createElement("li");

        const link = document.createElement("a");

        link.href =
            `${window.BASE_URL}/app/view/dashboard.php?page=reporter_incident_details&id=${encodeURIComponent(incident.incident_id)}`;

        link.textContent =
            `#${incident.incident_id} - ${incident.title || "Untitled incident"}`;

        link.className = "alert-link text-decoration-underline";

        item.appendChild(link);

        alertList.appendChild(item);

    });

    }


    // ================================
    // PRIORITY BADGE
    // ================================

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


    // ================================
    // STATUS BADGE
    // ================================

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


    // ================================
    // FORMAT DATE
    // ================================

    function formatDate(dateString) {

        if (!dateString) {
            return "-";
        }


        const date = new Date(
            dateString.replace(" ", "T")
        );


        if (isNaN(date.getTime())) {
            return escapeHtml(dateString);
        }


        return date.toLocaleString("en-MY", {
            day: "2-digit",
            month: "short",
            year: "numeric",
            hour: "2-digit",
            minute: "2-digit",
            hour12: true
        });

    }


    // ================================
    // VIEW REPORT
    // ================================

    window.viewReport = function (incidentId) {

        if (!incidentId) {
            return;
        }


        window.location.href =
            `${window.BASE_URL}/app/view/dashboard.php?page=reporter_incident_details&id=${encodeURIComponent(incidentId)}`;

    };


    // ================================
    // ESCAPE HTML
    // ================================

    function escapeHtml(value) {

        const div = document.createElement("div");

        div.textContent = value ?? "";

        return div.innerHTML;

    }


    // ================================
    // FILTER EVENTS
    // ================================

    searchInput.addEventListener(
        "input",
        renderReports
    );


    statusFilter.addEventListener(
        "change",
        renderReports
    );


    priorityFilter.addEventListener(
        "change",
        renderReports
    );


    categoryFilter.addEventListener(
        "change",
        renderReports
    );


    // ================================
    // INITIAL LOAD
    // ================================

    loadReports();

});