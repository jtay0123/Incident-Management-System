<div class="container-fluid">

    <!-- ==========================================
         PAGE HEADER
    ========================================== -->
    <div class="mb-4">
        <h2 class="fw-bold mb-1">Report Incident</h2>
        <p class="text-muted mb-0">
        <!--    Submit a campus issue or incident for review and resolution.-->
        </p>
    </div>


    <!-- ==========================================
         REPORT INCIDENT FORM
    ========================================== -->
    <div class="card shadow-sm">

        <div class="card-header bg-white">
            <h5 class="mb-0">Incident Information</h5>
        </div>

        <div class="card-body">

            <form
                id="reportIncidentForm"
                method="POST"
                enctype="multipart/form-data"
            >

                <!-- ==========================================
                     INCIDENT TITLE
                ========================================== -->
                <div class="mb-3">

                    <label
                        for="incidentTitle"
                        class="form-label fw-semibold"
                    >
                        Incident Title
                        <span class="text-danger">*</span>
                    </label>

                    <input
                        type="text"
                        class="form-control"
                        id="incidentTitle"
                        name="title"
                        maxlength="200"
                        placeholder="Enter the incident title"
                        required
                    >

                </div>


                <!-- ==========================================
                     DESCRIPTION
                ========================================== -->
                <div class="mb-3">

                    <label
                        for="incidentDescription"
                        class="form-label fw-semibold"
                    >
                        Description
                        <span class="text-danger">*</span>
                    </label>

                    <textarea
                        class="form-control"
                        id="incidentDescription"
                        name="description"
                        rows="5"
                        maxlength="2000"
                        placeholder="Describe the incident or issue in detail"
                        required
                    ></textarea>

                    <div class="form-text">
                        Please provide enough information to help the
                        response staff understand the issue.
                    </div>

                </div>


                <!-- ==========================================
                     LOCATION
                ========================================== -->
                <div class="mb-3">

                    <label class="form-label fw-semibold">
                        Location
                        <span class="text-danger">*</span>
                    </label>

                    <div class="row g-2">

                        <div class="col-md-8">

                            <select
                                class="form-select"
                                id="locationId"
                                name="location_id"
                                required
                            >
                                <option value="">
                                    Select location
                                </option>
                            </select>

                        </div>

                        <div class="col-md-4">

                            <button
                                type="button"
                                class="btn btn-outline-primary w-100"
                                id="scanQrBtn"
                            >
                                <i class="bi bi-qr-code-scan"></i>
                                Scan QR Code
                            </button>

                        </div>

                    </div>

                    <input
                        type="hidden"
                        id="qrCodeValue"
                        name="qr_code_value"
                    >

                    <div
                        id="selectedLocationInfo"
                        class="form-text mt-2"
                    >
                        You can select a location manually or scan the
                        location QR code.
                    </div>

                </div>


                <!-- ==========================================
                     QR SCANNER AREA
                ========================================== -->
                <div
                    id="qrScannerContainer"
                    class="mb-3 d-none"
                >

                    <div class="border rounded p-3">

                        <div class="d-flex justify-content-between align-items-center mb-2">

                            <h6 class="mb-0">
                                Scan Location QR Code
                            </h6>

                            <button
                                type="button"
                                class="btn btn-sm btn-outline-secondary"
                                id="closeQrScannerBtn"
                            >
                                Close
                            </button>

                        </div>

                        <div
                            id="qrReader"
                            style="width: 100%;"
                        ></div>

                        <div
                            id="qrScanMessage"
                            class="small text-muted mt-2"
                        >
                            Point the camera at the location QR code.
                        </div>

                    </div>

                </div>


                <!-- ==========================================
                     CATEGORY
                ========================================== -->
                <div class="mb-3">

                    <label
                        for="categoryId"
                        class="form-label fw-semibold"
                    >
                        Category
                        <span class="text-danger">*</span>
                    </label>

                    <select
                        class="form-select"
                        id="categoryId"
                        name="category_id"
                        required
                    >
                        <option value="">
                            Select category
                        </option>
                    </select>

                    <div class="form-text">
                        The system may suggest a category based on
                        keywords in your incident title and description.
                    </div>

                </div>


                <!-- ==========================================
                     DEPARTMENT
                ========================================== -->
                <div class="mb-3">

                    <label
                        for="departmentId"
                        class="form-label fw-semibold"
                    >
                        Department
                    </label>

                    <select
                        class="form-select"
                        id="departmentId"
                        disabled
                    >
                        <option value="">
                            Select a category first
                        </option>
                    </select>

                    <div class="form-text">
                        The department is determined automatically based
                        on the selected category.
                    </div>

                </div>


<!-- ==========================================
     IMAGE UPLOAD
========================================== -->
<div class="mb-4">

    <label
        for="incidentImage"
        class="form-label fw-semibold"
    >
        Pictures
    </label>

<input
    type="file"
    class="form-control"
    id="incidentImage"
    name="incident_image[]"
    accept=".jpg,.jpeg,.png,image/jpeg,image/png"
    multiple
>

    <div class="form-text">
        Upload up to 5 pictures related to the incident.
        JPG or PNG only. Maximum 5 MB per image.
    </div>

    <!-- IMAGE PREVIEW -->
    <div
        id="imagePreviewContainer"
        class="mt-3 d-none"
    >

        <p class="small fw-semibold mb-2">
            Image Preview
        </p>

        <div
            id="imagePreview"
            class="d-flex flex-wrap gap-3"
        ></div>

        <button
            type="button"
            class="btn btn-sm btn-outline-danger mt-2"
            id="removeImageBtn"
        >
            Remove All Images
        </button>

    </div>

</div>

                <!-- ==========================================
                     FORM MESSAGE
                ========================================== -->
                <div
                    id="reportFormMessage"
                    class="alert d-none"
                    role="alert"
                ></div>


                <!-- ==========================================
                     SUBMIT / RESET
                ========================================== -->
                <div class="d-flex justify-content-end gap-2">

                    <button
                        type="reset"
                        class="btn btn-outline-secondary"
                        id="resetReportFormBtn"
                    >
                        Reset
                    </button>

                    <button
                        type="submit"
                        class="btn btn-primary"
                        id="submitReportBtn"
                    >
                        <span
                            id="submitReportSpinner"
                            class="spinner-border spinner-border-sm me-1 d-none"
                            role="status"
                            aria-hidden="true"
                        ></span>

                        Submit Report
                    </button>

                </div>

            </form>

        </div>

    </div>

</div>