<div class="container-fluid">

<!-- ============================= -->
<!-- PAGE HEADER -->
<!-- ============================= -->

<div class="d-flex justify-content-between align-items-center mb-4">

    <div>

        <h2>SLA Settings</h2>

        <p class="text-muted mb-0">
       <!--     Manage SLA rules, working hours and public holidays. -->
        </p>

    </div>

</div>


<!-- ===================================================== -->
<!-- SLA RULES -->
<!-- ===================================================== -->

<div class="card shadow-sm border-0 mb-4">

    <div class="card-header bg-white">

        <h5 class="mb-0">
            <i class="bi bi-clock-history me-1"></i>
            SLA Rules
        </h5>

    </div>


    <div class="card-body">

        <p class="text-muted mb-4">
       <!--     Set the maximum time allowed to resolve incidents based
            on their priority. -->
        </p>


        <div class="table-responsive">

            <table class="table table-hover align-middle mb-0">

                <thead class="table-light">

                    <tr>

                        <th>
                            Priority
                        </th>

                        <th>
                            SLA Hours
                        </th>

                    </tr>

                </thead>


                <tbody id="slaSettingsTableBody">

                    <tr>

                        <td
                            colspan="2"
                            class="text-center py-4 text-muted"
                        >

                            Loading SLA settings...

                        </td>

                    </tr>

                </tbody>

            </table>

        </div>


        <div class="text-end mt-3">

            <button
                type="button"
                class="btn btn-primary"
                id="saveSLASettingsButton"
            >

                <i class="bi bi-check-lg me-1"></i>

                Save SLA Rules

            </button>

        </div>

    </div>

</div>


<!-- ===================================================== -->
<!-- WORKING HOURS -->
<!-- ===================================================== -->

<div class="card shadow-sm border-0 mb-4">

    <div class="card-header bg-white">

        <h5 class="mb-0">
            <i class="bi bi-calendar-week me-1"></i>
            Working Hours
        </h5>

    </div>


    <div class="card-body">

        <p class="text-muted mb-4">
            <!--    Set the campus working days and working hours used
            when calculating SLA deadlines. -->
        </p>


        <div class="table-responsive">

            <table class="table table-hover align-middle mb-0">

                <thead class="table-light">

                    <tr>

                        <th>
                            Day
                        </th>

                        <th>
                            Working Day
                        </th>

                        <th>
                            Start Time
                        </th>

                        <th>
                            End Time
                        </th>

                    </tr>

                </thead>


                <tbody id="workingHoursTableBody">

                    <tr>

                        <td
                            colspan="4"
                            class="text-center py-4 text-muted"
                        >

                            Loading working hours...

                        </td>

                    </tr>

                </tbody>

            </table>

        </div>


        <div class="text-end mt-3">

            <button
                type="button"
                class="btn btn-primary"
                id="saveWorkingHoursButton"
            >

                <i class="bi bi-check-lg me-1"></i>

                Save Working Hours

            </button>

        </div>

    </div>

</div>


<!-- ===================================================== -->
<!-- PUBLIC HOLIDAYS -->
<!-- ===================================================== -->

<div class="card shadow-sm border-0 mb-4">

    <div class="card-header bg-white">

        <div class="d-flex justify-content-between align-items-center">

            <h5 class="mb-0">

                <i class="bi bi-calendar-event me-1"></i>

                Public Holidays

            </h5>


            <button
                type="button"
                class="btn btn-primary"
                data-bs-toggle="modal"
                data-bs-target="#createHolidayModal"
            >

                <i class="bi bi-plus-lg me-1"></i>

                Add Holiday

            </button>

        </div>

    </div>


    <div class="card-body p-0">

        <div class="table-responsive">

            <table
                class="table table-hover align-middle mb-0"
                id="publicHolidaysTable"
            >

                <thead class="table-light">

                    <tr>

                        <th>ID</th>

                        <th>
                            Date
                        </th>

                        <th>
                            Holiday Name
                        </th>

                        <th>
                            Status
                        </th>

                        <th>
                            Created At
                        </th>

                        <th class="text-center">
                            Actions
                        </th>

                    </tr>

                </thead>


                <tbody id="publicHolidaysTableBody">

                    <tr>

                        <td
                            colspan="6"
                            class="text-center py-4 text-muted"
                        >

                            Loading public holidays...

                        </td>

                    </tr>

                </tbody>

            </table>

        </div>

    </div>

</div>


</div>

<!-- ===================================================== -->

<!-- CREATE PUBLIC HOLIDAY MODAL -->

<!-- ===================================================== -->

<div
    class="modal fade"
    id="createHolidayModal"
    tabindex="-1"
    aria-hidden="true"
>

```
<div class="modal-dialog">

    <div class="modal-content">

        <div class="modal-header">

            <h5 class="modal-title">
                Add Public Holiday
            </h5>

            <button
                type="button"
                class="btn-close"
                data-bs-dismiss="modal"
            ></button>

        </div>


        <form id="createHolidayForm">

            <div class="modal-body">

                <div class="mb-3">

                    <label
                        for="holiday_date"
                        class="form-label"
                    >
                        Holiday Date
                    </label>

                    <input
                        type="date"
                        class="form-control"
                        id="holiday_date"
                        name="holiday_date"
                        required
                    >

                </div>


                <div class="mb-3">

                    <label
                        for="holiday_name"
                        class="form-label"
                    >
                        Holiday Name
                    </label>

                    <input
                        type="text"
                        class="form-control"
                        id="holiday_name"
                        name="holiday_name"
                        maxlength="150"
                        placeholder="Example: Malaysia Day"
                        required
                    >

                </div>

            </div>


            <div class="modal-footer">

                <button
                    type="button"
                    class="btn btn-secondary"
                    data-bs-dismiss="modal"
                >
                    Cancel
                </button>


                <button
                    type="submit"
                    class="btn btn-primary"
                >

                    <i class="bi bi-plus-lg me-1"></i>

                    Add Holiday

                </button>

            </div>

        </form>

    </div>

</div>
```

</div>

<!-- ===================================================== -->

<!-- EDIT PUBLIC HOLIDAY MODAL -->

<!-- ===================================================== -->

<div
    class="modal fade"
    id="editHolidayModal"
    tabindex="-1"
    aria-hidden="true"
>

```
<div class="modal-dialog">

    <div class="modal-content">

        <div class="modal-header">

            <h5 class="modal-title">
                Edit Public Holiday
            </h5>

            <button
                type="button"
                class="btn-close"
                data-bs-dismiss="modal"
            ></button>

        </div>


        <form id="editHolidayForm">

            <div class="modal-body">

                <input
                    type="hidden"
                    id="edit_holiday_id"
                    name="holiday_id"
                >


                <div class="mb-3">

                    <label
                        for="edit_holiday_date"
                        class="form-label"
                    >
                        Holiday Date
                    </label>

                    <input
                        type="date"
                        class="form-control"
                        id="edit_holiday_date"
                        name="holiday_date"
                        required
                    >

                </div>


                <div class="mb-3">

                    <label
                        for="edit_holiday_name"
                        class="form-label"
                    >
                        Holiday Name
                    </label>

                    <input
                        type="text"
                        class="form-control"
                        id="edit_holiday_name"
                        name="holiday_name"
                        maxlength="150"
                        required
                    >

                </div>

            </div>


            <div class="modal-footer">

                <button
                    type="button"
                    class="btn btn-secondary"
                    data-bs-dismiss="modal"
                >
                    Cancel
                </button>


                <button
                    type="submit"
                    class="btn btn-primary"
                >

                    <i class="bi bi-check-lg me-1"></i>

                    Save Changes

                </button>

            </div>

        </form>

    </div>

</div>

</div>

<!-- ============================= -->

<!-- BASE URL -->

<!-- ============================= -->

<script>
    window.BASE_URL = "<?= BASE_URL ?>";
</script>
