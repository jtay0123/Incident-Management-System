
document.addEventListener("DOMContentLoaded", function () {

    loadMyIncidents();

});


/* ================================================== */
/* GLOBAL DATA */
/* ================================================== */

let allIncidents = [];


/* ================================================== */
/* LOAD MY INCIDENTS */
/* ================================================== */

function loadMyIncidents() {

    const tableBody =
        document.getElementById("incidentTableBody");

    tableBody.innerHTML = `
        <tr>
            <td
                colspan="8"
                class="text-center text-muted"
            >
                Loading incidents...
            </td>
        </tr>
    `;


    fetch(
        `${BASE_URL}/app/controller/IncidentController.php?action=get_staff_incidents`
    )

        .then(response => response.json())

        .then(data => {

            if (!data.success) {

                tableBody.innerHTML = `
                    <tr>
                        <td
                            colspan="8"
                            class="text-center text-danger"
                        >
                            ${escapeHtml(
                                data.message ||
                                "Failed to load incidents."
                            )}
                        </td>
                    </tr>
                `;

                return;
            }


            /*
             * Get incidents from the JSON response.
             *
             * Supports:
             * data.data.incidents
             * or
             * data.incidents
             */

            allIncidents =
                data.data?.incidents ||
                data.incidents ||
                [];


            /* Update statistics */

            updateStatistics(
                allIncidents
            );


            /* Populate category filter */

            populateCategoryFilter(
                allIncidents
            );


            /* Display incidents */

            renderIncidents(
                allIncidents
            );


            /* Setup filters */

            setupFilters();

        })


        .catch(error => {

            console.error(
                "Error loading incidents:",
                error
            );


            tableBody.innerHTML = `
                <tr>
                    <td
                        colspan="8"
                        class="text-center text-danger"
                    >
                        Unable to load incidents.
                    </td>
                </tr>
            `;

        });

}


/* ================================================== */
/* UPDATE STATISTICS */
/* ================================================== */

function updateStatistics(incidents) {

    const totalElement =
        document.getElementById(
            "totalIncidents"
        );

    const unresolvedElement =
        document.getElementById(
            "unresolvedIncidents"
        );

    const resolvedElement =
        document.getElementById(
            "resolvedIncidents"
        );

    const overdueElement =
        document.getElementById(
            "overdueIncidents"
        );


    const total =
        incidents.length;


    /*
     * Unresolved:
     *
     * Assigned
     * In Progress
     */

    const unresolved =
        incidents.filter(function (incident) {

            return (
                incident.status === "Assigned" ||
                incident.status === "In Progress"
            );

        }).length;


    /*
     * Resolved:
     *
     * Resolved only.
     */

    const resolved =
        incidents.filter(function (incident) {

            return incident.status === "Resolved";

        }).length;


    /*
     * Overdue:
     *
     * Based on SLA status.
     */

    const overdue =
        incidents.filter(function (incident) {

            return incident.sla_status === "Overdue";

        }).length;


    if (totalElement) {

        totalElement.textContent =
            total;

    }


    if (unresolvedElement) {

        unresolvedElement.textContent =
            unresolved;

    }


    if (resolvedElement) {

        resolvedElement.textContent =
            resolved;

    }


    if (overdueElement) {

        overdueElement.textContent =
            overdue;

    }

}


/* ================================================== */
/* POPULATE CATEGORY FILTER */
/* ================================================== */

function populateCategoryFilter(
    incidents
) {

    const categoryFilter =
        document.getElementById(
            "filter-category"
        );


    if (!categoryFilter) {
        return;
    }


    const categories = [];


    incidents.forEach(function (incident) {

        const category =
            incident.category_name;


        if (
            category &&
            !categories.includes(category)
        ) {

            categories.push(category);

        }

    });


    categories.sort();


    categoryFilter.innerHTML = `
        <option value="">
            All Categories
        </option>
    `;


    categories.forEach(function (category) {

        const option =
            document.createElement(
                "option"
            );

        option.value =
            category;

        option.textContent =
            category;

        categoryFilter.appendChild(
            option
        );

    });

}


/* ================================================== */
/* SETUP FILTERS */
/* ================================================== */

function setupFilters() {

    const searchInput =
        document.getElementById(
            "searchInput"
        );

    const statusFilter =
        document.getElementById(
            "filter-status"
        );

    const priorityFilter =
        document.getElementById(
            "filter-priority"
        );

    const categoryFilter =
        document.getElementById(
            "filter-category"
        );


    if (searchInput) {

        searchInput.addEventListener(
            "input",
            applyFilters
        );

    }


    if (statusFilter) {

        statusFilter.addEventListener(
            "change",
            applyFilters
        );

    }


    if (priorityFilter) {

        priorityFilter.addEventListener(
            "change",
            applyFilters
        );

    }


    if (categoryFilter) {

        categoryFilter.addEventListener(
            "change",
            applyFilters
        );

    }

}


/* ================================================== */
/* APPLY FILTERS */
/* ================================================== */

function applyFilters() {

    const searchInput =
        document.getElementById(
            "searchInput"
        );

    const statusFilter =
        document.getElementById(
            "filter-status"
        );

    const priorityFilter =
        document.getElementById(
            "filter-priority"
        );

    const categoryFilter =
        document.getElementById(
            "filter-category"
        );


    const search =
        searchInput
            ? searchInput.value
                .trim()
                .toLowerCase()
            : "";


    const selectedStatus =
        statusFilter
            ? statusFilter.value
            : "";


    const selectedPriority =
        priorityFilter
            ? priorityFilter.value
            : "";


    const selectedCategory =
        categoryFilter
            ? categoryFilter.value
            : "";


    const filteredIncidents =
        allIncidents.filter(
            function (incident) {

                const incidentId =
                    String(
                        incident.incident_id ||
                        ""
                    ).toLowerCase();


                const title =
                    String(
                        incident.title ||
                        ""
                    ).toLowerCase();


                const category =
                    String(
                        incident.category_name ||
                        ""
                    );


                const matchesSearch =
                    search === "" ||
                    incidentId.includes(
                        search
                    ) ||
                    title.includes(
                        search
                    );


                const matchesStatus =
                    selectedStatus === "" ||
                    incident.status ===
                        selectedStatus;


                const matchesPriority =
                    selectedPriority === "" ||
                    incident.priority ===
                        selectedPriority;


                const matchesCategory =
                    selectedCategory === "" ||
                    category ===
                        selectedCategory;


                return (
                    matchesSearch &&
                    matchesStatus &&
                    matchesPriority &&
                    matchesCategory
                );

            }
        );


    renderIncidents(
        filteredIncidents
    );

}


/* ================================================== */
/* RENDER INCIDENTS */
/* ================================================== */

function renderIncidents(
    incidents
) {

    const tableBody =
        document.getElementById(
            "incidentTableBody"
        );


    if (!tableBody) {
        return;
    }


    if (incidents.length === 0) {

        tableBody.innerHTML = `
            <tr>
                <td
                    colspan="8"
                    class="text-center py-4 text-muted"
                >
                    No incidents assigned to you.
                </td>
            </tr>
        `;

        return;
    }


    tableBody.innerHTML = "";


    incidents.forEach(function (incident) {

        const row =
            document.createElement("tr");


        /* ====================================== */
        /* LOCATION */
        /* ====================================== */

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


        /* ====================================== */
        /* PRIORITY */
        /* ====================================== */

        const priorityBadge =
            getPriorityBadge(
                incident.priority
            );


        /* ====================================== */
        /* STATUS */
        /* ====================================== */

        const statusBadge =
            getStatusBadge(
                incident.status
            );


        /* ====================================== */
        /* DATE */
        /* ====================================== */

        const submittedAt =
            incident.submitted_at
                ? formatDateTime(
                    incident.submitted_at
                )
                : "-";


        /* ====================================== */
        /* ROW */
        /* ====================================== */

        row.innerHTML = `

            <td>
                ${escapeHtml(
                    incident.incident_id
                )}
            </td>


            <td>
                <strong>
                    ${escapeHtml(
                        incident.title
                    )}
                </strong>
            </td>


            <td>
                ${escapeHtml(
                    incident.category_name ||
                    "-"
                )}
            </td>


            <td>
                ${location || "-"}
            </td>


            <td>
                ${priorityBadge}
            </td>


            <td>
                ${statusBadge}
            </td>


            <td>
                ${submittedAt}
            </td>


            <td class="text-center">

                <button
                    type="button"
                    class="btn btn-sm btn-primary"
                    onclick="viewIncident(
                        ${Number(
                            incident.incident_id
                        )}
                    )"
                >   
                    <i class="bi bi-eye me-1"></i>
                    View
                </button>

            </td>

        `;


        tableBody.appendChild(
            row
        );

    });

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

function getStatusBadge(status) {

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
/* VIEW INCIDENT */
/* ================================================== */

function viewIncident(
    incidentId
) {

    window.location.href =
        `${BASE_URL}/app/view/dashboard.php?page=incident_details&id=${encodeURIComponent(incidentId)}`;

}


/* ================================================== */
/* FORMAT DATE TIME */
/* ================================================== */

function formatDateTime(
    dateTime
) {

    const date =
        new Date(
            String(
                dateTime
            ).replace(
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
