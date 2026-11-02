
// ==========================================
// MANAGE LEAVE
// ==========================================

document.addEventListener("DOMContentLoaded", function () {

    // ==========================================
    // LOAD LEAVE RECORDS
    // ==========================================

    function loadLeave() {

        fetch(
            BASE_URL +
            "/app/controller/LeaveController.php?action=get_my_leave"
        )

        .then(response => response.json())

        .then(data => {

            const tableBody =
                document.getElementById("leaveTableBody");

            if (!tableBody) {
                return;
            }

            if (
                !data.success ||
                !Array.isArray(data.leaves)
            ) {

                tableBody.innerHTML = `
                    <tr>
                        <td
                            colspan="6"
                            class="text-center text-danger"
                        >
                            Failed to load leave records.
                        </td>
                    </tr>
                `;

                return;
            }


            // ==========================================
            // NO LEAVE RECORDS
            // ==========================================

            if (data.leaves.length === 0) {

                tableBody.innerHTML = `
                    <tr>
                        <td
                            colspan="6"
                            class="text-center text-muted"
                        >
                            No leave records found.
                        </td>
                    </tr>
                `;

                return;
            }


            // ==========================================
            // DISPLAY LEAVE RECORDS
            // ==========================================

            tableBody.innerHTML = "";

            data.leaves.forEach(leave => {

                const row =
                    document.createElement("tr");

                row.innerHTML = `

                    <td>
                        ${escapeHTML(leave.leave_id)}
                    </td>

                    <td>
                        ${formatDate(leave.start_date)}
                    </td>

                    <td>
                        ${formatDate(leave.end_date)}
                    </td>

                    <td>
                        ${
                            leave.reason
                                ? escapeHTML(leave.reason)
                                : '<span class="text-muted">-</span>'
                        }
                    </td>

                    <td>
                        ${formatDateTime(leave.created_at)}
                    </td>

                    <td>

                        <button
                            type="button"
                            class="btn btn-sm btn-outline-primary me-1"
                            onclick="editLeave(${leave.leave_id})"
                        >
                            Edit
                        </button>

                        <button
                            type="button"
                            class="btn btn-sm btn-outline-danger"
                            onclick="deleteLeave(${leave.leave_id})"
                        >
                            Delete
                        </button>

                    </td>

                `;

                tableBody.appendChild(row);

            });

        })

        .catch(error => {

            console.error(
                "Error loading leave:",
                error
            );

            const tableBody =
                document.getElementById("leaveTableBody");

            if (tableBody) {

                tableBody.innerHTML = `
                    <tr>
                        <td
                            colspan="6"
                            class="text-center text-danger"
                        >
                            Failed to load leave records.
                        </td>
                    </tr>
                `;

            }

        });

    }


    // ==========================================
    // EDIT LEAVE
    // ==========================================

    window.editLeave = function (leaveId) {

        fetch(
            BASE_URL +
            "/app/controller/LeaveController.php?action=get_leave&leave_id="
            + encodeURIComponent(leaveId)
        )

        .then(response => response.json())

        .then(data => {

            if (!data.success) {

                alert(
                    data.message ||
                    "Failed to load leave record."
                );

                return;
            }


            const leave = data.leave;


            document.getElementById(
                "edit_leave_id"
            ).value = leave.leave_id;


            document.getElementById(
                "edit_start_date"
            ).value = leave.start_date;


            document.getElementById(
                "edit_end_date"
            ).value = leave.end_date;


            document.getElementById(
                "edit_reason"
            ).value = leave.reason || "";


            const modalElement =
                document.getElementById(
                    "editLeaveModal"
                );


            const modal =
                bootstrap.Modal.getOrCreateInstance(
                    modalElement
                );


            modal.show();

        })

        .catch(error => {

            console.error(
                "Error loading leave record:",
                error
            );

            alert(
                "Failed to load leave record."
            );

        });

    };


    // ==========================================
    // UPDATE LEAVE
    // ==========================================

    const editLeaveForm =
        document.getElementById(
            "editLeaveForm"
        );


    if (editLeaveForm) {

        editLeaveForm.addEventListener(
            "submit",
            function (event) {

                event.preventDefault();


                const formData =
                    new FormData(editLeaveForm);


                formData.append(
                    "action",
                    "update_leave"
                );


                fetch(
                    BASE_URL +
                    "/app/controller/LeaveController.php",
                    {
                        method: "POST",
                        body: formData
                    }
                )

                .then(response => response.json())

                .then(data => {

                    if (!data.success) {

                        alert(
                            data.message ||
                            "Failed to update leave."
                        );

                        return;
                    }


                    alert(
                        data.message ||
                        "Leave updated successfully."
                    );


                    const modalElement =
                        document.getElementById(
                            "editLeaveModal"
                        );


                    const modal =
                        bootstrap.Modal.getInstance(
                            modalElement
                        );


                    if (modal) {
                        modal.hide();
                    }


                    loadLeave();

                })

                .catch(error => {

                    console.error(
                        "Error updating leave:",
                        error
                    );

                    alert(
                        "Failed to update leave."
                    );

                });

            }
        );

    }


    // ==========================================
    // DELETE LEAVE
    // ==========================================

    window.deleteLeave = function (leaveId) {

        const confirmed =
            confirm(
                "Are you sure you want to delete this leave record?"
            );


        if (!confirmed) {
            return;
        }


        const formData =
            new FormData();


        formData.append(
            "action",
            "delete_leave"
        );


        formData.append(
            "leave_id",
            leaveId
        );


        fetch(
            BASE_URL +
            "/app/controller/LeaveController.php",
            {
                method: "POST",
                body: formData
            }
        )

        .then(response => response.json())

        .then(data => {

            if (!data.success) {

                alert(
                    data.message ||
                    "Failed to delete leave."
                );

                return;
            }


            alert(
                data.message ||
                "Leave deleted successfully."
            );


            loadLeave();

        })

        .catch(error => {

            console.error(
                "Error deleting leave:",
                error
            );

            alert(
                "Failed to delete leave."
            );

        });

    };


    // ==========================================
    // DATE FORMAT
    // ==========================================

    function formatDate(dateString) {

        if (!dateString) {
            return "-";
        }

        const date =
            new Date(
                dateString + "T00:00:00"
            );

        return date.toLocaleDateString(
            "en-GB",
            {
                day: "2-digit",
                month: "2-digit",
                year: "numeric"
            }
        );

    }


    // ==========================================
    // DATE TIME FORMAT
    // ==========================================

    function formatDateTime(dateTimeString) {

        if (!dateTimeString) {
            return "-";
        }

        const date =
            new Date(
                dateTimeString.replace(
                    " ",
                    "T"
                )
            );

        return date.toLocaleString(
            "en-GB",
            {
                day: "2-digit",
                month: "2-digit",
                year: "numeric",
                hour: "2-digit",
                minute: "2-digit"
            }
        );

    }


    // ==========================================
    // ESCAPE HTML
    // ==========================================

    function escapeHTML(value) {

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


    // ==========================================
    // CREATE LEAVE FORM VALIDATION
    // ==========================================

    const createLeaveForm =
        document.getElementById(
            "createLeaveForm"
        );


    if (createLeaveForm) {

        createLeaveForm.addEventListener(
            "submit",
            function (event) {

                const startDate =
                    document.getElementById(
                        "start_date"
                    ).value;

                const endDate =
                    document.getElementById(
                        "end_date"
                    ).value;


                if (
                    startDate &&
                    endDate &&
                    startDate > endDate
                ) {

                    event.preventDefault();

                    alert(
                        "End date cannot be earlier than start date."
                    );

                }

            }
        );

    }


    // ==========================================
    // INITIAL LOAD
    // ==========================================

    loadLeave();

});
