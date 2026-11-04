<div class="container-fluid mt-4">

    <!-- ========================================== -->
    <!-- PAGE HEADER -->
    <!-- ========================================== -->

    <div class="d-flex justify-content-between align-items-center mb-4">

        <div>
            <h2 class="mb-1">My Leave</h2>

            <p class="text-muted mb-0">
                Manage your leave dates and availability.
            </p>
        </div>

        <button
            type="button"
            class="btn btn-primary"
            data-bs-toggle="modal"
            data-bs-target="#createLeaveModal"
        >
            Add Leave
        </button>

    </div>


    <!-- ========================================== -->
    <!-- LEAVE TABLE -->
    <!-- ========================================== -->

    <div class="card shadow-sm">

        <div class="card-body">

            <div class="table-responsive">

                <table
                    class="table table-hover align-middle"
                    id="leaveTable"
                >

                    <thead>

                        <tr>

                            <th>ID</th>

                            <th>Start Date</th>

                            <th>End Date</th>

                            <th>Reason</th>

                            <th>Created At</th>

                            <th>Actions</th>

                        </tr>

                    </thead>

                    <tbody id="leaveTableBody">

                        <tr>

                            <td
                                colspan="6"
                                class="text-center text-muted"
                            >
                                Loading leave records...
                            </td>

                        </tr>

                    </tbody>

                </table>

            </div>

        </div>

    </div>

</div>


<!-- ================================================== -->
<!-- CREATE LEAVE MODAL -->
<!-- ================================================== -->

<div
    class="modal fade"
    id="createLeaveModal"
    tabindex="-1"
    aria-labelledby="createLeaveModalLabel"
    aria-hidden="true"
>

    <div class="modal-dialog">

        <div class="modal-content">

            <div class="modal-header">

                <h5
                    class="modal-title"
                    id="createLeaveModalLabel"
                >
                    Add Leave
                </h5>

                <button
                    type="button"
                    class="btn-close"
                    data-bs-dismiss="modal"
                    aria-label="Close"
                ></button>

            </div>


            <form
                id="createLeaveForm"
                method="POST"
                action="<?= BASE_URL ?>/app/controller/LeaveController.php?action=create_leave"
            >

                <div class="modal-body">

                    <!-- START DATE -->

                    <div class="mb-3">

                        <label
                            for="start_date"
                            class="form-label"
                        >
                            Start Date
                        </label>

                        <input
                            type="date"
                            class="form-control"
                            id="start_date"
                            name="start_date"
                            required
                        >

                    </div>


                    <!-- END DATE -->

                    <div class="mb-3">

                        <label
                            for="end_date"
                            class="form-label"
                        >
                            End Date
                        </label>

                        <input
                            type="date"
                            class="form-control"
                            id="end_date"
                            name="end_date"
                            required
                        >

                    </div>


                    <!-- REASON -->

                    <div class="mb-3">

                        <label
                            for="reason"
                            class="form-label"
                        >
                            Reason
                        </label>

                        <textarea
                            class="form-control"
                            id="reason"
                            name="reason"
                            rows="3"
                            placeholder="Enter reason (optional)"
                        ></textarea>

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
                        Save Leave
                    </button>

                </div>

            </form>

        </div>

    </div>

</div>


<!-- ================================================== -->
<!-- EDIT LEAVE MODAL -->
<!-- ================================================== -->

<div
    class="modal fade"
    id="editLeaveModal"
    tabindex="-1"
    aria-labelledby="editLeaveModalLabel"
    aria-hidden="true"
>

    <div class="modal-dialog">

        <div class="modal-content">

            <div class="modal-header">

                <h5
                    class="modal-title"
                    id="editLeaveModalLabel"
                >
                    Edit Leave
                </h5>

                <button
                    type="button"
                    class="btn-close"
                    data-bs-dismiss="modal"
                    aria-label="Close"
                ></button>

            </div>


            <form id="editLeaveForm">

                <div class="modal-body">

                    <input
                        type="hidden"
                        id="edit_leave_id"
                        name="leave_id"
                    >


                    <!-- START DATE -->

                    <div class="mb-3">

                        <label
                            for="edit_start_date"
                            class="form-label"
                        >
                            Start Date
                        </label>

                        <input
                            type="date"
                            class="form-control"
                            id="edit_start_date"
                            name="start_date"
                            required
                        >

                    </div>


                    <!-- END DATE -->

                    <div class="mb-3">

                        <label
                            for="edit_end_date"
                            class="form-label"
                        >
                            End Date
                        </label>

                        <input
                            type="date"
                            class="form-control"
                            id="edit_end_date"
                            name="end_date"
                            required
                        >

                    </div>


                    <!-- REASON -->

                    <div class="mb-3">

                        <label
                            for="edit_reason"
                            class="form-label"
                        >
                            Reason
                        </label>

                        <textarea
                            class="form-control"
                            id="edit_reason"
                            name="reason"
                            rows="3"
                            placeholder="Enter reason (optional)"
                        ></textarea>

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
                        Update Leave
                    </button>

                </div>

            </form>

        </div>

    </div>

</div>


<script>

    const BASE_URL = "<?= BASE_URL ?>";

</script>
