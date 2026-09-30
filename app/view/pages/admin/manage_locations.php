<div class="container-fluid">

    <!-- ============================= -->
    <!-- PAGE HEADER -->
    <!-- ============================= -->

    <div class="d-flex justify-content-between align-items-center mb-4">

        <div>

            <h2>Manage Locations</h2>

            <p class="text-muted mb-0">
                Manage campus locations and their QR codes.
            </p>

        </div>

        <button
            class="btn btn-primary"
            data-bs-toggle="modal"
            data-bs-target="#createLocationModal"
        >

            <i class="bi bi-geo-alt-fill me-1"></i>

            Add Location

        </button>

    </div>


    <!-- ============================= -->
    <!-- SEARCH AND FILTER -->
    <!-- ============================= -->

    <div class="card shadow-sm border-0 mb-4">

        <div class="card-body">

            <div class="row align-items-center">

                <div class="col-lg-6 col-md-7 mb-3 mb-lg-0">

                    <div class="input-group">

                        <span class="input-group-text bg-white">

                            <i class="bi bi-search text-muted"></i>

                        </span>

                        <input
                            type="text"
                            id="searchLocationInput"
                            class="form-control"
                            placeholder="Search location or building"
                        >

                    </div>

                </div>


                <div class="col-lg-3 col-md-5">

                    <select
                        id="filter-location-status"
                        class="form-select"
                    >

                        <option value="">
                            All Status
                        </option>

                        <option value="Active">
                            Active
                        </option>

                        <option value="Inactive">
                            Inactive
                        </option>

                    </select>

                </div>

            </div>

        </div>

    </div>


    <!-- ============================= -->
    <!-- LOCATION TABLE -->
    <!-- ============================= -->

    <div class="card shadow-sm border-0">

        <div class="card-header bg-white">

            <h5 class="mb-0">
                Campus Locations
            </h5>

        </div>

        <div class="card-body p-0">

            <div class="table-responsive">

                <table
                    class="table table-hover align-middle mb-0"
                    id="locationsTable"
                >

                    <thead class="table-light">

                        <tr>

                            <th>ID</th>

                            <th>Location</th>

                            <th>Building</th>

                            <th>Floor</th>

                            <th>QR Code</th>

                            <th>Status</th>

                            <th>Created At</th>

                            <th class="text-center">
                                Actions
                            </th>

                        </tr>

                    </thead>

                    <tbody id="locationsTableBody">

                        <tr>

                            <td
                                colspan="8"
                                class="text-center py-4 text-muted"
                            >

                                Loading locations...

                            </td>

                        </tr>

                    </tbody>

                </table>

            </div>

        </div>

    </div>

</div>


<!-- ===================================================== -->
<!-- CREATE LOCATION MODAL -->
<!-- ===================================================== -->

<div
    class="modal fade"
    id="createLocationModal"
    tabindex="-1"
    aria-hidden="true"
>

    <div class="modal-dialog">

        <div class="modal-content">

            <div class="modal-header">

                <h5 class="modal-title">
                    Add Campus Location
                </h5>

                <button
                    type="button"
                    class="btn-close"
                    data-bs-dismiss="modal"
                ></button>

            </div>


            <form
                id="createLocationForm"
                action="<?= BASE_URL ?>/app/controller/LocationController.php?action=create_location"
                method="POST"
            >

                <div class="modal-body">

                    <div class="mb-3">

                        <label
                            for="location_name"
                            class="form-label"
                        >
                            Location Name
                        </label>

                        <input
                            type="text"
                            class="form-control"
                            id="location_name"
                            name="location_name"
                            maxlength="100"
                            placeholder="Example: Computer Lab 1"
                            required
                        >

                    </div>


                    <div class="mb-3">

                        <label
                            for="building_name"
                            class="form-label"
                        >
                            Building
                        </label>

                        <input
                            type="text"
                            class="form-control"
                            id="building_name"
                            name="building_name"
                            maxlength="100"
                            placeholder="Example: Block A"
                            required
                        >

                    </div>


                    <div class="mb-3">

                        <label
                            for="floor_number"
                            class="form-label"
                        >
                            Floor
                        </label>

                        <input
                            type="text"
                            class="form-control"
                            id="floor_number"
                            name="floor_number"
                            maxlength="50"
                            placeholder="Example: LG, L1, L2"
                            required
                        >

                    </div>


                    <div class="alert alert-info">

                        <i class="bi bi-qr-code me-1"></i>

                        A unique QR code value will be generated
                        automatically for this location.

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

                        Add Location

                    </button>

                </div>

            </form>

        </div>

    </div>

</div>


<!-- ===================================================== -->
<!-- EDIT LOCATION MODAL -->
<!-- ===================================================== -->

<div
    class="modal fade"
    id="editLocationModal"
    tabindex="-1"
    aria-hidden="true"
>

    <div class="modal-dialog">

        <div class="modal-content">

            <div class="modal-header">

                <h5 class="modal-title">
                    Edit Location
                </h5>

                <button
                    type="button"
                    class="btn-close"
                    data-bs-dismiss="modal"
                ></button>

            </div>


            <form id="editLocationForm">

                <div class="modal-body">

                    <input
                        type="hidden"
                        id="edit_location_id"
                        name="location_id"
                    >


                    <div class="mb-3">

                        <label
                            for="edit_location_name"
                            class="form-label"
                        >
                            Location Name
                        </label>

                        <input
                            type="text"
                            class="form-control"
                            id="edit_location_name"
                            name="location_name"
                            maxlength="100"
                            required
                        >

                    </div>


                    <div class="mb-3">

                        <label
                            for="edit_building_name"
                            class="form-label"
                        >
                            Building
                        </label>

                        <input
                            type="text"
                            class="form-control"
                            id="edit_building_name"
                            name="building_name"
                            maxlength="100"
                            required
                        >

                    </div>


                    <div class="mb-3">

                        <label
                            for="edit_floor_number"
                            class="form-label"
                        >
                            Floor
                        </label>

                        <input
                            type="text"
                            class="form-control"
                            id="edit_floor_number"
                            name="floor_number"
                            maxlength="50"
                            placeholder="Example: LG, L1, L2"
                            required
                        >

                    </div>


                    <div class="mb-3">

                        <label
                            for="edit_qr_code_value"
                            class="form-label"
                        >
                            QR Code Value
                        </label>

                        <input
                            type="text"
                            class="form-control"
                            id="edit_qr_code_value"
                            readonly
                        >

                        <small class="text-muted">
                            The QR code value cannot be changed after
                            the location is created.
                        </small>

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


<!-- ===================================================== -->
<!-- VIEW QR CODE MODAL -->
<!-- ===================================================== -->

<div
    class="modal fade"
    id="qrCodeModal"
    tabindex="-1"
    aria-hidden="true"
>

    <div class="modal-dialog modal-dialog-centered">

        <div class="modal-content">

            <div class="modal-header">

                <h5 class="modal-title">
                    Location QR Code
                </h5>

                <button
                    type="button"
                    class="btn-close"
                    data-bs-dismiss="modal"
                ></button>

            </div>


            <div class="modal-body text-center">

                <h5 id="qrLocationName" class="mb-1">
                    Location
                </h5>

                <p
                    id="qrLocationDetails"
                    class="text-muted mb-3"
                >
                    Building - Floor
                </p>


                <!-- QR CODE WILL BE GENERATED HERE -->

                <div
                    id="qrCodeContainer"
                    class="d-flex justify-content-center mb-3"
                ></div>


                <div class="mb-3">

                    <code id="qrCodeValue">
                        LOC-XXXXXX
                    </code>

                </div>


                <p class="text-muted small mb-0">
                    Scan this QR code to identify this campus location.
                </p>

            </div>


            <div class="modal-footer">

                <button
                    type="button"
                    class="btn btn-secondary"
                    data-bs-dismiss="modal"
                >
                    Close
                </button>

                <button
                    type="button"
                    class="btn btn-primary"
                    id="downloadQrButton"
                >

                    <i class="bi bi-download me-1"></i>

                    Download QR Code

                </button>

            </div>

        </div>

    </div>

</div>


<!-- ============================= -->
<!-- BASE URL -->
<!-- ============================= -->

<script>
    window.BASE_URL = "<?= BASE_URL ?>";
</script>


<!-- ===================================================== -->
<!-- QR CODE LIBRARY -->
<!-- ===================================================== -->

<script src="https://cdnjs.cloudflare.com/ajax/libs/qrcodejs/1.0.0/qrcode.min.js"></script>


<!-- ===================================================== -->
<!-- MANAGE LOCATIONS JAVASCRIPT -->
<!-- ===================================================== -->

