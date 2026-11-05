document.addEventListener("DOMContentLoaded", function () {

    const BASE_URL = window.BASE_URL || "";

    const locationTable =
        document.getElementById("locationsTableBody");

    const searchInput =
        document.getElementById("searchLocationInput");

    const statusFilter =
        document.getElementById("filter-location-status");


    /*
    |--------------------------------------------------------------------------
    | CREATE LOCATION FORM
    |--------------------------------------------------------------------------
    */

    const createLocationForm =
        document.getElementById("createLocationForm");


    /*
    |--------------------------------------------------------------------------
    | EDIT LOCATION FORM
    |--------------------------------------------------------------------------
    */

    const editLocationForm =
        document.getElementById("editLocationForm");

    const editLocationModal =
        document.getElementById("editLocationModal");

    const editLocationIdInput =
        document.getElementById("edit_location_id");

    const editLocationNameInput =
        document.getElementById("edit_location_name");

    const editBuildingNameInput =
        document.getElementById("edit_building_name");

    const editFloorNumberInput =
        document.getElementById("edit_floor_number");

    const editQrCodeValueInput =
        document.getElementById("edit_qr_code_value");


    /*
    |--------------------------------------------------------------------------
    | QR CODE
    |--------------------------------------------------------------------------
    */

    const qrCodeModal =
        document.getElementById("qrCodeModal");

    const qrCodeContainer =
        document.getElementById("qrCodeContainer");

    const qrLocationName =
        document.getElementById("qrLocationName");

    const qrLocationDetails =
        document.getElementById("qrLocationDetails");

    const qrCodeValue =
        document.getElementById("qrCodeValue");

    const downloadQrButton =
        document.getElementById("downloadQrButton");


    /*
    |--------------------------------------------------------------------------
    | LOCATION DATA
    |--------------------------------------------------------------------------
    */

    let locations = [];


    /*
    |--------------------------------------------------------------------------
    | LOAD LOCATIONS
    |--------------------------------------------------------------------------
    */

    function loadLocations() {

        if (!locationTable) {
            return;
        }


        locationTable.innerHTML = `
            <tr>
                <td colspan="8" class="text-center py-4 text-muted">
                    Loading locations...
                </td>
            </tr>
        `;


        fetch(
            BASE_URL +
            "/app/controller/LocationController.php?action=get_locations"
        )

        .then(response => response.json())

        .then(data => {

            if (!data.success) {

                locationTable.innerHTML = `
                    <tr>
                        <td
                            colspan="8"
                            class="text-center py-4 text-danger"
                        >
                            Unable to load locations.
                        </td>
                    </tr>
                `;

                return;
            }


            locations =
                data.locations || [];


            displayLocations();

        })

        .catch(error => {

            console.error(
                "Error loading locations:",
                error
            );


            locationTable.innerHTML = `
                <tr>
                    <td
                        colspan="8"
                        class="text-center py-4 text-danger"
                    >
                        Failed to load locations.
                    </td>
                </tr>
            `;

        });

    }


    /*
    |--------------------------------------------------------------------------
    | DISPLAY LOCATIONS
    |--------------------------------------------------------------------------
    */

    function displayLocations() {

        if (!locationTable) {
            return;
        }


        const searchValue =
            searchInput
                ? searchInput.value
                    .toLowerCase()
                    .trim()
                : "";


        const selectedStatus =
            statusFilter
                ? statusFilter.value
                : "";


        const filteredLocations =
            locations.filter(location => {

                const locationName =
                    (location.location_name || "")
                        .toLowerCase();


                const buildingName =
                    (location.building_name || "")
                        .toLowerCase();


                const floorNumber =
                    (location.floor_number || "")
                        .toLowerCase();


                const qrCodeValue =
                    (location.qr_code_value || "")
                        .toLowerCase();


                const matchesSearch =
                    locationName.includes(searchValue) ||
                    buildingName.includes(searchValue) ||
                    floorNumber.includes(searchValue) ||
                    qrCodeValue.includes(searchValue);


                const matchesStatus =
                    selectedStatus === "" ||
                    location.status === selectedStatus;


                return (
                    matchesSearch &&
                    matchesStatus
                );

            });


        if (filteredLocations.length === 0) {

            locationTable.innerHTML = `
                <tr>
                    <td
                        colspan="8"
                        class="text-center py-4 text-muted"
                    >
                        No locations found.
                    </td>
                </tr>
            `;

            return;

        }


        locationTable.innerHTML = "";


        filteredLocations.forEach(location => {

            const row =
                document.createElement("tr");


            row.innerHTML = `

                <td>
                    ${escapeHTML(location.location_id)}
                </td>


                <td>
                    <strong>
                        ${escapeHTML(location.location_name)}
                    </strong>
                </td>


                <td>
                    ${escapeHTML(location.building_name)}
                </td>


                <td>
                    ${escapeHTML(location.floor_number)}
                </td>


                <td>

                    <code>
                        ${escapeHTML(
                            location.qr_code_value
                        )}
                    </code>

                    <br>

                    <button
                        type="button"
                        class="btn btn-sm btn-outline-primary mt-1"
                        onclick="viewQrCode(${location.location_id})"
                        title="View QR Code"
                    >

                        <i class="bi bi-qr-code me-1"></i>

                        View QR

                    </button>

                </td>


                <td>
                    ${getStatusBadge(location.status)}
                </td>


                <td>
                    ${escapeHTML(
                        location.created_at || "-"
                    )}
                </td>


                <td>

                    <div class="d-flex gap-1">

                        <!-- EDIT -->

                        <button
                            type="button"
                            class="btn btn-sm btn-outline-primary edit-location-btn"
                            data-id="${location.location_id}"
                            title="Edit Location"
                        >

                            <i class="bi bi-pencil"></i>

                        </button>


                        <!-- STATUS -->

                        <select
                            class="form-select form-select-sm location-status-select"
                            data-id="${location.location_id}"
                            style="width: 120px;"
                        >

                            <option
                                value="Active"
                                ${location.status === "Active"
                                    ? "selected"
                                    : ""}
                            >
                                Active
                            </option>


                            <option
                                value="Inactive"
                                ${location.status === "Inactive"
                                    ? "selected"
                                    : ""}
                            >
                                Inactive
                            </option>

                        </select>


                        <!-- DELETE -->

                        <button
                            type="button"
                            class="btn btn-sm btn-outline-danger delete-location-btn"
                            data-id="${location.location_id}"
                            title="Delete Location"
                        >

                            <i class="bi bi-trash"></i>

                        </button>

                    </div>

                </td>

            `;


            locationTable.appendChild(row);

        });


        attachLocationActions();

    }


    /*
    |--------------------------------------------------------------------------
    | STATUS BADGE
    |--------------------------------------------------------------------------
    */

    function getStatusBadge(status) {

        let badgeClass =
            "bg-secondary";


        if (status === "Active") {

            badgeClass =
                "bg-success";

        }

        else if (status === "Inactive") {

            badgeClass =
                "bg-secondary";

        }


        return `
            <span class="badge ${badgeClass}">
                ${escapeHTML(status || "Unknown")}
            </span>
        `;

    }


    /*
    |--------------------------------------------------------------------------
    | SEARCH
    |--------------------------------------------------------------------------
    */

    if (searchInput) {

        searchInput.addEventListener(
            "input",
            function () {

                displayLocations();

            }
        );

    }


    /*
    |--------------------------------------------------------------------------
    | STATUS FILTER
    |--------------------------------------------------------------------------
    */

    if (statusFilter) {

        statusFilter.addEventListener(
            "change",
            function () {

                displayLocations();

            }
        );

    }


    /*
    |--------------------------------------------------------------------------
    | LOCATION ACTIONS
    |--------------------------------------------------------------------------
    */

    function attachLocationActions() {


        /*
        |----------------------------------------------------------------------
        | EDIT LOCATION
        |----------------------------------------------------------------------
        */

        document
            .querySelectorAll(".edit-location-btn")
            .forEach(button => {

                button.addEventListener(
                    "click",
                    function () {

                        const locationId =
                            this.dataset.id;


                        loadEditLocation(
                            locationId
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
            .querySelectorAll(".location-status-select")
            .forEach(select => {

                select.addEventListener(
                    "change",
                    function () {

                        const locationId =
                            this.dataset.id;


                        const newStatus =
                            this.value;


                        updateLocationStatus(
                            locationId,
                            newStatus
                        );

                    }
                );

            });


        /*
        |----------------------------------------------------------------------
        | DELETE LOCATION
        |----------------------------------------------------------------------
        */

        document
            .querySelectorAll(".delete-location-btn")
            .forEach(button => {

                button.addEventListener(
                    "click",
                    function () {

                        const locationId =
                            this.dataset.id;


                        const confirmed =
                            confirm(
                                "Are you sure you want to delete this location?"
                            );


                        if (!confirmed) {

                            return;

                        }


                        deleteLocation(
                            locationId
                        );

                    }
                );

            });

    }


    /*
    |--------------------------------------------------------------------------
    | VIEW QR CODE
    |--------------------------------------------------------------------------
    */

    window.viewQrCode = function (locationId) {

        const location =
            locations.find(
                item =>
                    Number(item.location_id) ===
                    Number(locationId)
            );


        if (!location) {

            alert(
                "Location not found."
            );

            return;

        }


        if (
            !qrCodeContainer ||
            !qrLocationName ||
            !qrLocationDetails ||
            !qrCodeValue
        ) {

            return;

        }


        /*
        |----------------------------------------------------------------------
        | CLEAR PREVIOUS QR CODE
        |----------------------------------------------------------------------
        */

        qrCodeContainer.innerHTML = "";


        /*
        |----------------------------------------------------------------------
        | DISPLAY LOCATION INFORMATION
        |----------------------------------------------------------------------
        */

        qrLocationName.textContent =
            location.location_name;


        qrLocationDetails.textContent =
            location.building_name +
            " - " +
            location.floor_number;


        qrCodeValue.textContent =
            location.qr_code_value;


        /*
        |----------------------------------------------------------------------
        | GENERATE QR CODE
        |----------------------------------------------------------------------
        */

        new QRCode(
            qrCodeContainer,
            {
                text: location.qr_code_value,
                width: 250,
                height: 250,
                correctLevel:
                    QRCode.CorrectLevel.H
            }
        );


        /*
        |----------------------------------------------------------------------
        | STORE CURRENT QR INFORMATION
        |----------------------------------------------------------------------
        */

        qrCodeContainer.dataset.qrValue =
            location.qr_code_value;


        qrCodeContainer.dataset.locationName =
            location.location_name;


        /*
        |----------------------------------------------------------------------
        | SHOW MODAL
        |----------------------------------------------------------------------
        */

        if (qrCodeModal) {

            const modal =
                bootstrap.Modal.getOrCreateInstance(
                    qrCodeModal
                );

            modal.show();

        }

    };


    /*
    |--------------------------------------------------------------------------
    | DOWNLOAD QR CODE
    |--------------------------------------------------------------------------
    */

    if (downloadQrButton) {

        downloadQrButton.addEventListener(
            "click",
            function () {

                if (!qrCodeContainer) {
                    return;
                }


                const canvas =
                    qrCodeContainer.querySelector(
                        "canvas"
                    );


                const image =
                    qrCodeContainer.querySelector(
                        "img"
                    );


                const qrValue =
                    qrCodeContainer.dataset.qrValue ||
                    "QR";


                const locationName =
                    qrCodeContainer.dataset.locationName ||
                    "Location";


                let downloadUrl = "";


                if (canvas) {

                    downloadUrl =
                        canvas.toDataURL(
                            "image/png"
                        );

                }

                else if (image) {

                    downloadUrl =
                        image.src;

                }

                else {

                    alert(
                        "QR code is not ready."
                    );

                    return;

                }


                const link =
                    document.createElement("a");


                link.href =
                    downloadUrl;


                link.download =
                    locationName
                        .replace(
                            /[^a-z0-9]/gi,
                            "_"
                        )
                        .toLowerCase()
                    +
                    "_" +
                    qrValue +
                    ".png";


                document.body.appendChild(
                    link
                );


                link.click();


                document.body.removeChild(
                    link
                );

            }
        );

    }


    /*
    |--------------------------------------------------------------------------
    | LOAD EDIT LOCATION
    |--------------------------------------------------------------------------
    */

    function loadEditLocation(locationId) {

        fetch(
            BASE_URL +
            "/app/controller/LocationController.php?action=get_location&location_id=" +
            encodeURIComponent(locationId)
        )

        .then(response => response.json())

        .then(data => {

            if (!data.success) {

                alert(
                    data.message ||
                    "Unable to load location information."
                );

                return;

            }


            const location =
                data.location;


            if (editLocationIdInput) {

                editLocationIdInput.value =
                    location.location_id;

            }


            if (editLocationNameInput) {

                editLocationNameInput.value =
                    location.location_name || "";

            }


            if (editBuildingNameInput) {

                editBuildingNameInput.value =
                    location.building_name || "";

            }


            if (editFloorNumberInput) {

                editFloorNumberInput.value =
                    location.floor_number || "";

            }


            if (editQrCodeValueInput) {

                editQrCodeValueInput.value =
                    location.qr_code_value || "";

            }


            if (editLocationModal) {

                const modal =
                    bootstrap.Modal.getOrCreateInstance(
                        editLocationModal
                    );

                modal.show();

            }

        })

        .catch(error => {

            console.error(
                "Error loading location:",
                error
            );


            alert(
                "Something went wrong while loading the location."
            );

        });

    }


    /*
    |--------------------------------------------------------------------------
    | UPDATE LOCATION STATUS
    |--------------------------------------------------------------------------
    */

    function updateLocationStatus(
        locationId,
        newStatus
    ) {

        const formData =
            new FormData();


        formData.append(
            "action",
            "updateStatus"
        );


        formData.append(
            "location_id",
            locationId
        );


        formData.append(
            "status",
            newStatus
        );


        fetch(
            BASE_URL +
            "/app/controller/LocationController.php",
            {
                method: "POST",
                body: formData
            }
        )

        .then(response => response.json())

        .then(data => {

            if (data.success) {

                loadLocations();

            }

            else {

                alert(
                    data.message ||
                    "Unable to update location status."
                );

            }

        })

        .catch(error => {

            console.error(
                "Error updating location status:",
                error
            );


            alert(
                "Something went wrong while updating location status."
            );

        });

    }


    /*
    |--------------------------------------------------------------------------
    | DELETE LOCATION
    |--------------------------------------------------------------------------
    */

    function deleteLocation(locationId) {

        const formData =
            new FormData();


        formData.append(
            "action",
            "delete"
        );


        formData.append(
            "location_id",
            locationId
        );


        fetch(
            BASE_URL +
            "/app/controller/LocationController.php",
            {
                method: "POST",
                body: formData
            }
        )

        .then(response => response.json())

        .then(data => {

            if (data.success) {

                loadLocations();

            }

            else {

                alert(
                    data.message ||
                    "Unable to delete location."
                );

            }

        })

        .catch(error => {

            console.error(
                "Error deleting location:",
                error
            );


            alert(
                "Something went wrong while deleting the location."
            );

        });

    }


    /*
    |--------------------------------------------------------------------------
    | CREATE LOCATION FORM
    |--------------------------------------------------------------------------
    */

    if (createLocationForm) {

        createLocationForm.addEventListener(
            "submit",
            function (event) {

                const locationName =
                    document
                        .getElementById("location_name")
                        .value
                        .trim();


                const buildingName =
                    document
                        .getElementById("building_name")
                        .value
                        .trim();


                const floorNumber =
                    document
                        .getElementById("floor_number")
                        .value
                        .trim();


                if (!locationName) {

                    event.preventDefault();

                    alert(
                        "Please enter the location name."
                    );

                    return;

                }


                if (!buildingName) {

                    event.preventDefault();

                    alert(
                        "Please enter the building name."
                    );

                    return;

                }


                if (!floorNumber) {

                    event.preventDefault();

                    alert(
                        "Please enter the floor."
                    );

                    return;

                }

            }
        );

    }


    /*
    |--------------------------------------------------------------------------
    | EDIT LOCATION FORM
    |--------------------------------------------------------------------------
    */

    if (editLocationForm) {

        editLocationForm.addEventListener(
            "submit",
            function (event) {

                event.preventDefault();


                const locationId =
                    editLocationIdInput.value.trim();


                const locationName =
                    editLocationNameInput.value.trim();


                const buildingName =
                    editBuildingNameInput.value.trim();


                const floorNumber =
                    editFloorNumberInput.value.trim();


                if (!locationId) {

                    alert(
                        "Invalid location ID."
                    );

                    return;

                }


                if (!locationName) {

                    alert(
                        "Please enter the location name."
                    );

                    return;

                }


                if (!buildingName) {

                    alert(
                        "Please enter the building name."
                    );

                    return;

                }


                if (!floorNumber) {

                    alert(
                        "Please enter the floor."
                    );

                    return;

                }


                const formData =
                    new FormData();


                formData.append(
                    "action",
                    "update_location"
                );


                formData.append(
                    "location_id",
                    locationId
                );


                formData.append(
                    "location_name",
                    locationName
                );


                formData.append(
                    "building_name",
                    buildingName
                );


                formData.append(
                    "floor_number",
                    floorNumber
                );


                fetch(
                    BASE_URL +
                    "/app/controller/LocationController.php",
                    {
                        method: "POST",
                        body: formData
                    }
                )

                .then(async response => {

                    const text =
                        await response.text();


                    console.log(
                        "UPDATE LOCATION RESPONSE:",
                        text
                    );


                    try {

                        return JSON.parse(text);

                    }

                    catch (error) {

                        console.error(
                            "INVALID JSON RESPONSE:",
                            text
                        );

                        throw error;

                    }

                })

                .then(data => {

                    if (data.success) {

                        if (editLocationModal) {

                            const modal =
                                bootstrap.Modal.getInstance(
                                    editLocationModal
                                );


                            if (modal) {

                                modal.hide();

                            }

                        }


                        loadLocations();


                        alert(
                            data.message ||
                            "Location updated successfully."
                        );

                    }

                    else {

                        alert(
                            data.message ||
                            "Unable to update location."
                        );

                    }

                })

                .catch(error => {

                    console.error(
                        "Error updating location:",
                        error
                    );


                    alert(
                        "Something went wrong while updating the location."
                    );

                });

            }
        );

    }


    /*
    |--------------------------------------------------------------------------
    | RESET CREATE LOCATION FORM
    |--------------------------------------------------------------------------
    */

    const createLocationModal =
        document.getElementById(
            "createLocationModal"
        );


    if (createLocationModal) {

        createLocationModal.addEventListener(
            "hidden.bs.modal",
            function () {

                if (createLocationForm) {

                    createLocationForm.reset();

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

    loadLocations();

});