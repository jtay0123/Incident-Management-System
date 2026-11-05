
document.addEventListener("DOMContentLoaded", function () {

    const BASE_URL = window.BASE_URL || "";


    /*
    |--------------------------------------------------------------------------
    | SLA SETTINGS
    |--------------------------------------------------------------------------
    */

    const slaSettingsTableBody =
        document.getElementById("slaSettingsTableBody");

    const saveSLASettingsButton =
        document.getElementById("saveSLASettingsButton");


    /*
    |--------------------------------------------------------------------------
    | WORKING HOURS
    |--------------------------------------------------------------------------
    */

    const workingHoursTableBody =
        document.getElementById("workingHoursTableBody");

    const saveWorkingHoursButton =
        document.getElementById("saveWorkingHoursButton");


    /*
    |--------------------------------------------------------------------------
    | PUBLIC HOLIDAYS
    |--------------------------------------------------------------------------
    */

    const publicHolidaysTableBody =
        document.getElementById("publicHolidaysTableBody");

    const createHolidayForm =
        document.getElementById("createHolidayForm");

    const editHolidayForm =
        document.getElementById("editHolidayForm");

    const editHolidayModal =
        document.getElementById("editHolidayModal");

    const editHolidayIdInput =
        document.getElementById("edit_holiday_id");

    const editHolidayDateInput =
        document.getElementById("edit_holiday_date");

    const editHolidayNameInput =
        document.getElementById("edit_holiday_name");


    /*
    |--------------------------------------------------------------------------
    | DATA
    |--------------------------------------------------------------------------
    */

    let slaSettings = [];

    let workingHours = [];

    let publicHolidays = [];


    /*
    |--------------------------------------------------------------------------
    | LOAD SLA SETTINGS
    |--------------------------------------------------------------------------
    */

    function loadSLASettings() {

        if (!slaSettingsTableBody) {
            return;
        }


        slaSettingsTableBody.innerHTML = `
            <tr>
                <td
                    colspan="2"
                    class="text-center py-4 text-muted"
                >
                    Loading SLA settings...
                </td>
            </tr>
        `;


        fetch(
            BASE_URL +
            "/app/controller/SLAController.php?action=get_sla_settings"
        )

        .then(response => response.json())

        .then(data => {

            if (!data.success) {

                slaSettingsTableBody.innerHTML = `
                    <tr>
                        <td
                            colspan="2"
                            class="text-center py-4 text-danger"
                        >
                            Unable to load SLA settings.
                        </td>
                    </tr>
                `;

                return;

            }


            slaSettings =
                data.settings || [];


            displaySLASettings();

        })

        .catch(error => {

            console.error(
                "Error loading SLA settings:",
                error
            );


            slaSettingsTableBody.innerHTML = `
                <tr>
                    <td
                        colspan="2"
                        class="text-center py-4 text-danger"
                    >
                        Failed to load SLA settings.
                    </td>
                </tr>
            `;

        });

    }


    /*
    |--------------------------------------------------------------------------
    | DISPLAY SLA SETTINGS
    |--------------------------------------------------------------------------
    */

    function displaySLASettings() {

        if (!slaSettingsTableBody) {
            return;
        }


        if (slaSettings.length === 0) {

            slaSettingsTableBody.innerHTML = `
                <tr>
                    <td
                        colspan="2"
                        class="text-center py-4 text-muted"
                    >
                        No SLA settings found.
                    </td>
                </tr>
            `;

            return;

        }


        slaSettingsTableBody.innerHTML = "";


        slaSettings.forEach(setting => {

            const row =
                document.createElement("tr");


            row.innerHTML = `

                <td>

                    <strong>
                        ${escapeHTML(setting.priority)}
                    </strong>

                </td>


                <td>

                    <div
                        class="input-group"
                        style="max-width: 250px;"
                    >

                        <input
                            type="number"
                            class="form-control sla-hours-input"
                            data-priority="${escapeHTML(setting.priority)}"
                            value="${escapeHTML(setting.sla_hours)}"
                            min="1"
                            step="1"
                        >

                        <span class="input-group-text">
                            hours
                        </span>

                    </div>

                </td>

            `;


            slaSettingsTableBody.appendChild(row);

        });

    }


    /*
    |--------------------------------------------------------------------------
    | SAVE SLA SETTINGS
    |--------------------------------------------------------------------------
    */

    if (saveSLASettingsButton) {

        saveSLASettingsButton.addEventListener(
            "click",
            function () {

                const inputs =
                    document.querySelectorAll(
                        ".sla-hours-input"
                    );


                if (inputs.length === 0) {

                    alert(
                        "No SLA settings available."
                    );

                    return;

                }


                const formData =
                    new FormData();


                formData.append(
                    "action",
                    "update_sla_settings"
                );


                let valid = true;


                inputs.forEach(input => {

                    const priority =
                        input.dataset.priority;


                    const slaHours =
                        parseInt(
                            input.value,
                            10
                        );


                    if (
                        !priority ||
                        isNaN(slaHours) ||
                        slaHours <= 0
                    ) {

                        valid = false;

                        return;

                    }


                    formData.append(
                        `settings[${priority}]`,
                        slaHours
                    );

                });


                if (!valid) {

                    alert(
                        "Please enter a valid SLA hour value for every priority."
                    );

                    return;

                }


                saveSLASettingsButton.disabled = true;


                fetch(
                    BASE_URL +
                    "/app/controller/SLAController.php",
                    {
                        method: "POST",
                        body: formData
                    }
                )

                .then(response => response.json())

                .then(data => {

                    if (data.success) {

                        alert(
                            data.message ||
                            "SLA settings updated successfully."
                        );


                        loadSLASettings();

                    }

                    else {

                        alert(
                            data.message ||
                            "Unable to update SLA settings."
                        );

                    }

                })

                .catch(error => {

                    console.error(
                        "Error updating SLA settings:",
                        error
                    );


                    alert(
                        "Something went wrong while updating SLA settings."
                    );

                })

                .finally(() => {

                    saveSLASettingsButton.disabled = false;

                });

            }
        );

    }


    /*
    |--------------------------------------------------------------------------
    | LOAD WORKING HOURS
    |--------------------------------------------------------------------------
    */

    function loadWorkingHours() {

        if (!workingHoursTableBody) {
            return;
        }


        workingHoursTableBody.innerHTML = `
            <tr>
                <td
                    colspan="4"
                    class="text-center py-4 text-muted"
                >
                    Loading working hours...
                </td>
            </tr>
        `;


        fetch(
            BASE_URL +
            "/app/controller/SLAController.php?action=get_working_hours"
        )

        .then(response => response.json())

        .then(data => {

            if (!data.success) {

                workingHoursTableBody.innerHTML = `
                    <tr>
                        <td
                            colspan="4"
                            class="text-center py-4 text-danger"
                        >
                            Unable to load working hours.
                        </td>
                    </tr>
                `;

                return;

            }


            workingHours =
                data.working_hours || [];


            displayWorkingHours();

        })

        .catch(error => {

            console.error(
                "Error loading working hours:",
                error
            );


            workingHoursTableBody.innerHTML = `
                <tr>
                    <td
                        colspan="4"
                        class="text-center py-4 text-danger"
                    >
                        Failed to load working hours.
                    </td>
                </tr>
            `;

        });

    }


    /*
    |--------------------------------------------------------------------------
    | DISPLAY WORKING HOURS
    |--------------------------------------------------------------------------
    */

    function displayWorkingHours() {

        if (!workingHoursTableBody) {
            return;
        }


        if (workingHours.length === 0) {

            workingHoursTableBody.innerHTML = `
                <tr>
                    <td
                        colspan="4"
                        class="text-center py-4 text-muted"
                    >
                        No working hours found.
                    </td>
                </tr>
            `;

            return;

        }


        workingHoursTableBody.innerHTML = "";


        workingHours.forEach(day => {

            const isWorkingDay =
                Number(day.is_working_day) === 1;


            const row =
                document.createElement("tr");


            row.innerHTML = `

                <td>

                    <strong>
                        ${escapeHTML(day.day_of_week)}
                    </strong>

                </td>


                <td>

                    <div class="form-check form-switch">

                        <input
                            type="checkbox"
                            class="form-check-input working-day-checkbox"
                            data-day="${escapeHTML(day.day_of_week)}"
                            ${isWorkingDay ? "checked" : ""}
                        >

                        <label class="form-check-label">

                            ${
                                isWorkingDay
                                    ? "Working"
                                    : "Non-working"
                            }

                        </label>

                    </div>

                </td>


                <td>

                    <input
                        type="time"
                        class="form-control working-start-time"
                        data-day="${escapeHTML(day.day_of_week)}"
                        value="${
                            day.start_time
                                ? day.start_time.substring(0, 5)
                                : ""
                        }"
                        ${
                            isWorkingDay
                                ? ""
                                : "disabled"
                        }
                    >

                </td>


                <td>

                    <input
                        type="time"
                        class="form-control working-end-time"
                        data-day="${escapeHTML(day.day_of_week)}"
                        value="${
                            day.end_time
                                ? day.end_time.substring(0, 5)
                                : ""
                        }"
                        ${
                            isWorkingDay
                                ? ""
                                : "disabled"
                        }
                    >

                </td>

            `;


            workingHoursTableBody.appendChild(row);

        });


        attachWorkingHourEvents();

    }


    /*
    |--------------------------------------------------------------------------
    | WORKING DAY TOGGLE
    |--------------------------------------------------------------------------
    */

    function attachWorkingHourEvents() {

        document
            .querySelectorAll(".working-day-checkbox")
            .forEach(checkbox => {

                checkbox.addEventListener(
                    "change",
                    function () {

                        const day =
                            this.dataset.day;


                        const startInput =
                            document.querySelector(
                                `.working-start-time[data-day="${day}"]`
                            );


                        const endInput =
                            document.querySelector(
                                `.working-end-time[data-day="${day}"]`
                            );


                        const label =
                            this.parentElement
                                .querySelector(
                                    ".form-check-label"
                                );


                        if (this.checked) {

                            startInput.disabled =
                                false;

                            endInput.disabled =
                                false;

                            label.textContent =
                                "Working";

                        }

                        else {

                            startInput.disabled =
                                true;

                            endInput.disabled =
                                true;

                            label.textContent =
                                "Non-working";

                        }

                    }
                );

            });

    }


    /*
    |--------------------------------------------------------------------------
    | SAVE WORKING HOURS
    |--------------------------------------------------------------------------
    */

    if (saveWorkingHoursButton) {

        saveWorkingHoursButton.addEventListener(
            "click",
            function () {

                const rows =
                    document.querySelectorAll(
                        "#workingHoursTableBody tr"
                    );


                const formData =
                    new FormData();


                formData.append(
                    "action",
                    "update_working_hours"
                );


                let valid = true;


                rows.forEach(row => {

                    const checkbox =
                        row.querySelector(
                            ".working-day-checkbox"
                        );


                    if (!checkbox) {
                        return;
                    }


                    const day =
                        checkbox.dataset.day;


                    const isWorkingDay =
                        checkbox.checked
                            ? 1
                            : 0;


                    const startInput =
                        row.querySelector(
                            ".working-start-time"
                        );


                    const endInput =
                        row.querySelector(
                            ".working-end-time"
                        );


                    const startTime =
                        startInput.value;


                    const endTime =
                        endInput.value;


                    if (isWorkingDay === 1) {

                        if (
                            !startTime ||
                            !endTime
                        ) {

                            alert(
                                day +
                                " requires a start time and end time."
                            );

                            valid = false;

                            return;

                        }


                        if (startTime >= endTime) {

                            alert(
                                day +
                                " end time must be later than start time."
                            );

                            valid = false;

                            return;

                        }

                    }


                    formData.append(
                        `working_hours[${day}][is_working_day]`,
                        isWorkingDay
                    );


                    formData.append(
                        `working_hours[${day}][start_time]`,
                        startTime
                    );


                    formData.append(
                        `working_hours[${day}][end_time]`,
                        endTime
                    );

                });


                if (!valid) {
                    return;
                }


                saveWorkingHoursButton.disabled =
                    true;


                fetch(
                    BASE_URL +
                    "/app/controller/SLAController.php",
                    {
                        method: "POST",
                        body: formData
                    }
                )

                .then(response => response.json())

                .then(data => {

                    if (data.success) {

                        alert(
                            data.message ||
                            "Working hours updated successfully."
                        );


                        loadWorkingHours();

                    }

                    else {

                        alert(
                            data.message ||
                            "Unable to update working hours."
                        );

                    }

                })

                .catch(error => {

                    console.error(
                        "Error updating working hours:",
                        error
                    );


                    alert(
                        "Something went wrong while updating working hours."
                    );

                })

                .finally(() => {

                    saveWorkingHoursButton.disabled =
                        false;

                });

            }
        );

    }


    /*
    |--------------------------------------------------------------------------
    | LOAD PUBLIC HOLIDAYS
    |--------------------------------------------------------------------------
    */

    function loadPublicHolidays() {

        if (!publicHolidaysTableBody) {
            return;
        }


        publicHolidaysTableBody.innerHTML = `
            <tr>
                <td
                    colspan="6"
                    class="text-center py-4 text-muted"
                >
                    Loading public holidays...
                </td>
            </tr>
        `;


        fetch(
            BASE_URL +
            "/app/controller/SLAController.php?action=get_public_holidays"
        )

        .then(response => response.json())

        .then(data => {

            if (!data.success) {

                publicHolidaysTableBody.innerHTML = `
                    <tr>
                        <td
                            colspan="6"
                            class="text-center py-4 text-danger"
                        >
                            Unable to load public holidays.
                        </td>
                    </tr>
                `;

                return;

            }


            publicHolidays =
                data.holidays || [];


            displayPublicHolidays();

        })

        .catch(error => {

            console.error(
                "Error loading public holidays:",
                error
            );


            publicHolidaysTableBody.innerHTML = `
                <tr>
                    <td
                        colspan="6"
                        class="text-center py-4 text-danger"
                    >
                        Failed to load public holidays.
                    </td>
                </tr>
            `;

        });

    }


    /*
    |--------------------------------------------------------------------------
    | DISPLAY PUBLIC HOLIDAYS
    |--------------------------------------------------------------------------
    */

    function displayPublicHolidays() {

        if (!publicHolidaysTableBody) {
            return;
        }


        if (publicHolidays.length === 0) {

            publicHolidaysTableBody.innerHTML = `
                <tr>
                    <td
                        colspan="6"
                        class="text-center py-4 text-muted"
                    >
                        No public holidays found.
                    </td>
                </tr>
            `;

            return;

        }


        publicHolidaysTableBody.innerHTML = "";


        publicHolidays.forEach(holiday => {

            /*
            |------------------------------------------------------------------
            | PREPARE STATUS OPTIONS
            |------------------------------------------------------------------
            */

            const activeSelected =
                holiday.status === "Active"
                    ? "selected"
                    : "";


            const inactiveSelected =
                holiday.status === "Inactive"
                    ? "selected"
                    : "";


            const row =
                document.createElement("tr");


            row.innerHTML = `

                <td>
                    ${escapeHTML(holiday.holiday_id)}
                </td>


                <td>
                    ${escapeHTML(holiday.holiday_date)}
                </td>


                <td>

                    <strong>
                        ${escapeHTML(holiday.holiday_name)}
                    </strong>

                </td>


                <td>
                    ${getHolidayStatusBadge(holiday.status)}
                </td>


                <td>
                    ${escapeHTML(
                        holiday.created_at || "-"
                    )}
                </td>


                <td class="text-center">

                    <div
                        class="d-flex justify-content-center gap-1"
                    >

                        <!-- EDIT -->

                        <button
                            type="button"
                            class="btn btn-sm btn-outline-primary edit-holiday-btn"
                            data-id="${holiday.holiday_id}"
                            title="Edit Holiday"
                        >

                            <i class="bi bi-pencil"></i>

                        </button>


                        <!-- STATUS -->

                        <select
                            class="form-select form-select-sm holiday-status-select"
                            data-id="${holiday.holiday_id}"
                            style="width: 120px;"
                        >

                            <option
                                value="Active"
                                ${activeSelected}
                            >
                                Active
                            </option>


                            <option
                                value="Inactive"
                                ${inactiveSelected}
                            >
                                Inactive
                            </option>

                        </select>


                        <!-- DELETE -->

                        <button
                            type="button"
                            class="btn btn-sm btn-outline-danger delete-holiday-btn"
                            data-id="${holiday.holiday_id}"
                            title="Delete Holiday"
                        >

                            <i class="bi bi-trash"></i>

                        </button>

                    </div>

                </td>

            `;


            publicHolidaysTableBody.appendChild(row);

        });


        attachHolidayActions();

    }


    /*
    |--------------------------------------------------------------------------
    | HOLIDAY STATUS BADGE
    |--------------------------------------------------------------------------
    */

    function getHolidayStatusBadge(status) {

        let badgeClass =
            "bg-secondary";


        if (status === "Active") {

            badgeClass =
                "bg-success";

        }


        return `
            <span class="badge ${badgeClass}">
                ${escapeHTML(status || "Unknown")}
            </span>
        `;

    }


    /*
    |--------------------------------------------------------------------------
    | HOLIDAY ACTIONS
    |--------------------------------------------------------------------------
    */

    function attachHolidayActions() {


        /*
        |----------------------------------------------------------------------
        | EDIT HOLIDAY
        |----------------------------------------------------------------------
        */

        document
            .querySelectorAll(".edit-holiday-btn")
            .forEach(button => {

                button.addEventListener(
                    "click",
                    function () {

                        const holidayId =
                            this.dataset.id;


                        loadEditHoliday(
                            holidayId
                        );

                    }
                );

            });


        /*
        |----------------------------------------------------------------------
        | CHANGE STATUS
        |----------------------------------------------------------------------
        */

        document
            .querySelectorAll(".holiday-status-select")
            .forEach(select => {

                select.addEventListener(
                    "change",
                    function () {

                        const holidayId =
                            this.dataset.id;


                        const newStatus =
                            this.value;


                        updateHolidayStatus(
                            holidayId,
                            newStatus
                        );

                    }
                );

            });


        /*
        |----------------------------------------------------------------------
        | DELETE HOLIDAY
        |----------------------------------------------------------------------
        */

        document
            .querySelectorAll(".delete-holiday-btn")
            .forEach(button => {

                button.addEventListener(
                    "click",
                    function () {

                        const holidayId =
                            this.dataset.id;


                        const confirmed =
                            confirm(
                                "Are you sure you want to delete this public holiday?"
                            );


                        if (!confirmed) {

                            return;

                        }


                        deletePublicHoliday(
                            holidayId
                        );

                    }
                );

            });

    }


    /*
    |--------------------------------------------------------------------------
    | CREATE PUBLIC HOLIDAY
    |--------------------------------------------------------------------------
    */

    if (createHolidayForm) {

        createHolidayForm.addEventListener(
            "submit",
            function (event) {

                event.preventDefault();


                const holidayDate =
                    document
                        .getElementById("holiday_date")
                        .value
                        .trim();


                const holidayName =
                    document
                        .getElementById("holiday_name")
                        .value
                        .trim();


                if (!holidayDate) {

                    alert(
                        "Please select the holiday date."
                    );

                    return;

                }


                if (!holidayName) {

                    alert(
                        "Please enter the holiday name."
                    );

                    return;

                }


                const formData =
                    new FormData();


                formData.append(
                    "action",
                    "create_public_holiday"
                );


                formData.append(
                    "holiday_date",
                    holidayDate
                );


                formData.append(
                    "holiday_name",
                    holidayName
                );


                fetch(
                    BASE_URL +
                    "/app/controller/SLAController.php",
                    {
                        method: "POST",
                        body: formData
                    }
                )

                .then(response => response.json())

                .then(data => {

                    if (data.success) {

                        const modalElement =
                            document.getElementById(
                                "createHolidayModal"
                            );


                        if (modalElement) {

                            const modal =
                                bootstrap.Modal.getInstance(
                                    modalElement
                                );


                            if (modal) {

                                modal.hide();

                            }

                        }


                        createHolidayForm.reset();


                        loadPublicHolidays();


                        alert(
                            data.message ||
                            "Public holiday added successfully."
                        );

                    }

                    else {

                        alert(
                            data.message ||
                            "Unable to add public holiday."
                        );

                    }

                })

                .catch(error => {

                    console.error(
                        "Error creating public holiday:",
                        error
                    );


                    alert(
                        "Something went wrong while adding the public holiday."
                    );

                });

            }
        );

    }


    /*
    |--------------------------------------------------------------------------
    | LOAD EDIT HOLIDAY
    |--------------------------------------------------------------------------
    */

    function loadEditHoliday(holidayId) {

        const holiday =
            publicHolidays.find(
                item =>
                    Number(item.holiday_id) ===
                    Number(holidayId)
            );


        if (!holiday) {

            alert(
                "Public holiday not found."
            );

            return;

        }


        if (editHolidayIdInput) {

            editHolidayIdInput.value =
                holiday.holiday_id;

        }


        if (editHolidayDateInput) {

            editHolidayDateInput.value =
                holiday.holiday_date;

        }


        if (editHolidayNameInput) {

            editHolidayNameInput.value =
                holiday.holiday_name || "";

        }


        if (editHolidayModal) {

            const modal =
                bootstrap.Modal.getOrCreateInstance(
                    editHolidayModal
                );


            modal.show();

        }

    }


    /*
    |--------------------------------------------------------------------------
    | UPDATE PUBLIC HOLIDAY
    |--------------------------------------------------------------------------
    */

    if (editHolidayForm) {

        editHolidayForm.addEventListener(
            "submit",
            function (event) {

                event.preventDefault();


                const holidayId =
                    editHolidayIdInput.value.trim();


                const holidayDate =
                    editHolidayDateInput.value.trim();


                const holidayName =
                    editHolidayNameInput.value.trim();


                if (!holidayId) {

                    alert(
                        "Invalid public holiday ID."
                    );

                    return;

                }


                if (!holidayDate) {

                    alert(
                        "Please select the holiday date."
                    );

                    return;

                }


                if (!holidayName) {

                    alert(
                        "Please enter the holiday name."
                    );

                    return;

                }


                const formData =
                    new FormData();


                formData.append(
                    "action",
                    "update_public_holiday"
                );


                formData.append(
                    "holiday_id",
                    holidayId
                );


                formData.append(
                    "holiday_date",
                    holidayDate
                );


                formData.append(
                    "holiday_name",
                    holidayName
                );


                fetch(
                    BASE_URL +
                    "/app/controller/SLAController.php",
                    {
                        method: "POST",
                        body: formData
                    }
                )

                .then(response => response.json())

                .then(data => {

                    if (data.success) {

                        if (editHolidayModal) {

                            const modal =
                                bootstrap.Modal.getInstance(
                                    editHolidayModal
                                );


                            if (modal) {

                                modal.hide();

                            }

                        }


                        loadPublicHolidays();


                        alert(
                            data.message ||
                            "Public holiday updated successfully."
                        );

                    }

                    else {

                        alert(
                            data.message ||
                            "Unable to update public holiday."
                        );

                    }

                })

                .catch(error => {

                    console.error(
                        "Error updating public holiday:",
                        error
                    );


                    alert(
                        "Something went wrong while updating the public holiday."
                    );

                });

            }
        );

    }


    /*
    |--------------------------------------------------------------------------
    | UPDATE HOLIDAY STATUS
    |--------------------------------------------------------------------------
    */

    function updateHolidayStatus(
        holidayId,
        newStatus
    ) {

        const formData =
            new FormData();


        formData.append(
            "action",
            "update_holiday_status"
        );


        formData.append(
            "holiday_id",
            holidayId
        );


        formData.append(
            "status",
            newStatus
        );


        fetch(
            BASE_URL +
            "/app/controller/SLAController.php",
            {
                method: "POST",
                body: formData
            }
        )

        .then(response => response.json())

        .then(data => {

            if (data.success) {

                loadPublicHolidays();

            }

            else {

                alert(
                    data.message ||
                    "Unable to update public holiday status."
                );


                loadPublicHolidays();

            }

        })

        .catch(error => {

            console.error(
                "Error updating public holiday status:",
                error
            );


            alert(
                "Something went wrong while updating public holiday status."
            );


            loadPublicHolidays();

        });

    }


    /*
    |--------------------------------------------------------------------------
    | DELETE PUBLIC HOLIDAY
    |--------------------------------------------------------------------------
    */

    function deletePublicHoliday(holidayId) {

        const formData =
            new FormData();


        formData.append(
            "action",
            "delete_public_holiday"
        );


        formData.append(
            "holiday_id",
            holidayId
        );


        fetch(
            BASE_URL +
            "/app/controller/SLAController.php",
            {
                method: "POST",
                body: formData
            }
        )

        .then(response => response.json())

        .then(data => {

            if (data.success) {

                loadPublicHolidays();

            }

            else {

                alert(
                    data.message ||
                    "Unable to delete public holiday."
                );

            }

        })

        .catch(error => {

            console.error(
                "Error deleting public holiday:",
                error
            );

        });

    }


    /*
    |--------------------------------------------------------------------------
    | RESET CREATE HOLIDAY FORM
    |--------------------------------------------------------------------------
    */

    const createHolidayModal =
        document.getElementById(
            "createHolidayModal"
        );


    if (createHolidayModal) {

        createHolidayModal.addEventListener(
            "hidden.bs.modal",
            function () {

                if (createHolidayForm) {

                    createHolidayForm.reset();

                }

            }
        );

    }


    /*
    |--------------------------------------------------------------------------
    | ESCAPE HTML
    |--------------------------------------------------------------------------
    */

    function escapeHTML(value) {

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


    /*
    |--------------------------------------------------------------------------
    | INITIAL LOAD
    |--------------------------------------------------------------------------
    */

    loadSLASettings();

    loadWorkingHours();

    loadPublicHolidays();

});