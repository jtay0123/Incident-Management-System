document.addEventListener('DOMContentLoaded', function () {

    // ==========================================
    // ELEMENTS
    // ==========================================
    const form = document.getElementById('reportIncidentForm');

    const titleInput = document.getElementById('incidentTitle');
    const descriptionInput = document.getElementById('incidentDescription');

    const locationSelect = document.getElementById('locationId');
    const qrCodeValue = document.getElementById('qrCodeValue');

    const categorySelect = document.getElementById('categoryId');
    const departmentSelect = document.getElementById('departmentId');

    const scanQrBtn = document.getElementById('scanQrBtn');
    const closeQrScannerBtn = document.getElementById('closeQrScannerBtn');

    const qrScannerContainer = document.getElementById('qrScannerContainer');
    const qrReader = document.getElementById('qrReader');
    const qrScanMessage = document.getElementById('qrScanMessage');

    const selectedLocationInfo = document.getElementById('selectedLocationInfo');

    const incidentImage = document.getElementById('incidentImage');
    const imagePreviewContainer = document.getElementById('imagePreviewContainer');
    const imagePreview = document.getElementById('imagePreview');
    const removeImageBtn = document.getElementById('removeImageBtn');

    const formMessage = document.getElementById('reportFormMessage');

    const resetBtn = document.getElementById('resetReportFormBtn');
    const submitBtn = document.getElementById('submitReportBtn');
    const submitSpinner = document.getElementById('submitReportSpinner');


    // ==========================================
    // CONTROLLER PATH
    // ==========================================
    const incidentController = '../controller/IncidentController.php';


    // ==========================================
    // QR SCANNER
    // ==========================================
    let html5QrCode = null;
    let scannerRunning = false;


    // ==========================================
    // LOAD LOCATIONS
    // ==========================================
    function loadLocations() {

        fetch(`${incidentController}?action=get_locations`)
            .then(response => response.json())
            .then(result => {

                if (!result.success) {
                    throw new Error(result.message || 'Failed to load locations.');
                }

                locationSelect.innerHTML = `
                    <option value="">Select location</option>
                `;

                result.locations.forEach(location => {

                    const option = document.createElement('option');

                    option.value = location.location_id;

                    option.textContent =
                        `${location.location_name} - ${location.building_name}`;

                    option.dataset.qrCode = location.qr_code_value;

                    locationSelect.appendChild(option);
                });

            })
            .catch(error => {

                console.error('Location loading error:', error);

                locationSelect.innerHTML = `
                    <option value="">
                        Unable to load locations
                    </option>
                `;
            });
    }


    // ==========================================
    // LOAD CATEGORIES
    // ==========================================
    function loadCategories() {

        fetch(`${incidentController}?action=get_categories`)
            .then(response => response.json())
            .then(result => {

                if (!result.success) {
                    throw new Error(result.message || 'Failed to load categories.');
                }

                categorySelect.innerHTML = `
                    <option value="">Select category</option>
                `;

                result.categories.forEach(category => {

                    const option = document.createElement('option');

                    option.value = category.category_id;

                    option.textContent = category.category_name;

                    option.dataset.departmentId = category.department_id;
                    option.dataset.departmentName = category.department_name;

                    categorySelect.appendChild(option);
                });

            })
            .catch(error => {

                console.error('Category loading error:', error);

                categorySelect.innerHTML = `
                    <option value="">
                        Unable to load categories
                    </option>
                `;
            });
    }


    // ==========================================
    // CATEGORY → DEPARTMENT
    // ==========================================
    categorySelect.addEventListener('change', function () {

        const selectedOption =
            categorySelect.options[categorySelect.selectedIndex];

        if (!selectedOption || !selectedOption.value) {

            departmentSelect.innerHTML = `
                <option value="">
                    Select a category first
                </option>
            `;

            return;
        }

        const departmentName =
            selectedOption.dataset.departmentName || '';

        departmentSelect.innerHTML = '';

        const option = document.createElement('option');

        option.value = selectedOption.dataset.departmentId || '';
        option.textContent = departmentName || 'Department assigned automatically';

        departmentSelect.appendChild(option);
    });


    // ==========================================
    // LOCATION MANUAL SELECTION
    // ==========================================
    locationSelect.addEventListener('change', function () {

        const selectedOption =
            locationSelect.options[locationSelect.selectedIndex];

        if (!selectedOption || !selectedOption.value) {

            qrCodeValue.value = '';

            selectedLocationInfo.textContent =
                'You can select a location manually or scan the location QR code.';

            return;
        }

        qrCodeValue.value =
            selectedOption.dataset.qrCode || '';

        selectedLocationInfo.textContent =
            `Selected location: ${selectedOption.textContent}`;
    });


    // ==========================================
    // QR SCANNER - OPEN
    // ==========================================
    scanQrBtn.addEventListener('click', function () {

        if (typeof Html5Qrcode === 'undefined') {

            qrScanMessage.textContent =
                'QR scanner library is not loaded.';

            qrScannerContainer.classList.remove('d-none');

            return;
        }

        qrScannerContainer.classList.remove('d-none');

        qrScanMessage.textContent =
            'Point the camera at the location QR code.';

        startQrScanner();
    });



    // ==========================================
    // START QR SCANNER - FULL CAMERA VIEW
    // ==========================================
    function startQrScanner() {

        if (scannerRunning) {
            return;
        }

        html5QrCode = new Html5Qrcode('qrReader');

        html5QrCode.start(
            { facingMode: 'environment' },
            {
                fps: 10
            },

            function (decodedText) {

                qrCodeValue.value = decodedText;

                qrScanMessage.textContent =
                    'QR code detected. Checking location...';

                resolveQrLocation(decodedText);
            },

            function () {
                // Ignore continuous scanner errors.
            }
        )
        .then(function () {

            scannerRunning = true;

        })
        .catch(function (error) {

            console.error('QR scanner error:', error);

            qrScanMessage.textContent =
                'Unable to access the camera. Please allow camera access or select a location manually.';
        });
    }



    // ==========================================
    // RESOLVE QR LOCATION
    // ==========================================
    function resolveQrLocation(qrValue) {

        fetch(
            `${incidentController}?action=get_location_by_qr&qr_code_value=${encodeURIComponent(qrValue)}`
        )
            .then(response => response.json())
            .then(result => {

                if (!result.success || !result.location) {

                    qrScanMessage.textContent =
                        'Invalid or inactive location QR code.';

                    qrCodeValue.value = '';

                    return;
                }

                const location = result.location;

                locationSelect.value = location.location_id;

                selectedLocationInfo.textContent =
                    `Selected location: ${location.location_name} - ${location.building_name}`;

                qrScanMessage.textContent =
                    'Location identified successfully.';

                stopQrScanner();

            })
            .catch(error => {

                console.error('QR location error:', error);

                qrScanMessage.textContent =
                    'Unable to identify this location.';
            });
    }


    // ==========================================
    // CLOSE QR SCANNER
    // ==========================================
    closeQrScannerBtn.addEventListener('click', function () {

        stopQrScanner();

    });


    // ==========================================
    // STOP QR SCANNER
    // ==========================================
    function stopQrScanner() {

        if (html5QrCode && scannerRunning) {

            html5QrCode.stop()
                .then(function () {

                    html5QrCode.clear();

                    scannerRunning = false;
                    html5QrCode = null;

                    qrScannerContainer.classList.add('d-none');

                })
                .catch(function (error) {

                    console.error('QR scanner stop error:', error);

                    scannerRunning = false;
                    html5QrCode = null;

                    qrScannerContainer.classList.add('d-none');
                });

        } else {

            qrScannerContainer.classList.add('d-none');
        }
    }


    // ==========================================
    // CATEGORY SUGGESTION
    // ==========================================
    let suggestionTimer = null;

    function suggestCategory() {

        const title = titleInput.value.trim();
        const description = descriptionInput.value.trim();

        if (!title && !description) {
            return;
        }

        const formData = new FormData();

        formData.append('title', title);
        formData.append('description', description);

        fetch(
            `${incidentController}?action=suggest_category`,
            {
                method: 'POST',
                body: formData
            }
        )
            .then(response => response.json())
            .then(result => {

                if (!result.success || !result.category) {
                    return;
                }

                const suggestedCategory =
                    result.category.category_id;

                const categoryOption =
                    [...categorySelect.options].find(
                        option => option.value == suggestedCategory
                    );

                if (!categoryOption) {
                    return;
                }

                categorySelect.value = suggestedCategory;

                categorySelect.dispatchEvent(
                    new Event('change')
                );

            })
            .catch(error => {

                console.error(
                    'Category suggestion error:',
                    error
                );
            });
    }


    // ==========================================
    // CATEGORY SUGGESTION DELAY
    // ==========================================
    function scheduleCategorySuggestion() {

        clearTimeout(suggestionTimer);

        suggestionTimer = setTimeout(function () {

            suggestCategory();

        }, 700);
    }


    titleInput.addEventListener(
        'input',
        scheduleCategorySuggestion
    );

    descriptionInput.addEventListener(
        'input',
        scheduleCategorySuggestion
    );


// ==========================================
// IMAGE PREVIEW - ALLOW MULTIPLE SELECTIONS
// ==========================================

let selectedImages = [];

incidentImage.addEventListener('change', function () {

    const newFiles = Array.from(incidentImage.files);

    if (newFiles.length === 0) {
        return;
    }

    const allowedTypes = [
        'image/jpeg',
        'image/png'
    ];

    // Validate new images
    for (const file of newFiles) {

        if (!allowedTypes.includes(file.type)) {

            showMessage(
                'danger',
                'Please upload JPG or PNG images only.'
            );

            incidentImage.value = '';
            return;
        }

        if (file.size > 5 * 1024 * 1024) {

            showMessage(
                'danger',
                'Each image must not exceed 5 MB.'
            );

            incidentImage.value = '';
            return;
        }
    }

    // Check maximum 5 images
    if (selectedImages.length + newFiles.length > 5) {

        showMessage(
            'danger',
            'You can upload a maximum of 5 images.'
        );

        incidentImage.value = '';
        return;
    }

    // Add new images without removing previous selections
    selectedImages = selectedImages.concat(newFiles);

    // Update input files using DataTransfer
    const dataTransfer = new DataTransfer();

    selectedImages.forEach(function (file) {
        dataTransfer.items.add(file);
    });

    incidentImage.files = dataTransfer.files;

    // Refresh preview
    renderImagePreview();

});


// ==========================================
// RENDER IMAGE PREVIEW
// ==========================================

function renderImagePreview() {

    imagePreview.innerHTML = '';

    if (selectedImages.length === 0) {

        imagePreviewContainer.classList.add('d-none');
        return;
    }

    selectedImages.forEach(function (file) {

        const reader = new FileReader();

        reader.onload = function (event) {

            const previewItem = document.createElement('div');

            previewItem.className = 'border rounded p-2';

            const img = document.createElement('img');

            img.src = event.target.result;
            img.style.width = '150px';
            img.style.height = '120px';
            img.style.objectFit = 'contain';

            const fileName = document.createElement('div');

            fileName.className = 'small text-muted mt-1';
            fileName.textContent = file.name;

            previewItem.appendChild(img);
            previewItem.appendChild(fileName);

            imagePreview.appendChild(previewItem);
        };

        reader.readAsDataURL(file);

    });

    imagePreviewContainer.classList.remove('d-none');
}


// ==========================================
// REMOVE ALL IMAGES
// ==========================================

removeImageBtn.addEventListener('click', function () {

    selectedImages = [];

    incidentImage.value = '';

    imagePreview.innerHTML = '';

    imagePreviewContainer.classList.add('d-none');

});

    // ==========================================
    // SHOW FORM MESSAGE
    // ==========================================
    function showMessage(type, message) {

        formMessage.className =
            `alert alert-${type}`;

        formMessage.textContent = message;
    }


    // ==========================================
    // SUBMIT FORM
    // ==========================================
    form.addEventListener('submit', function (event) {

        event.preventDefault();

        clearMessage();

        const title = titleInput.value.trim();
        const description = descriptionInput.value.trim();
        const categoryId = categorySelect.value;
        const locationId = locationSelect.value;

        if (!title) {

            showMessage(
                'danger',
                'Please enter an incident title.'
            );

            titleInput.focus();

            return;
        }

        if (!description) {

            showMessage(
                'danger',
                'Please enter a description.'
            );

            descriptionInput.focus();

            return;
        }

        if (!locationId) {

            showMessage(
                'danger',
                'Please select or scan a location.'
            );

            locationSelect.focus();

            return;
        }

        if (!categoryId) {

            showMessage(
                'danger',
                'Please select a category.'
            );

            categorySelect.focus();

            return;
        }


        // ------------------------------------------
        // DISABLE SUBMIT BUTTON
        // ------------------------------------------
        submitBtn.disabled = true;

        submitSpinner.classList.remove('d-none');


        // ------------------------------------------
        // CREATE FORM DATA
        // ------------------------------------------
        const formData = new FormData(form);

        // Backend determines department and priority.
        formData.delete('department_id');
        formData.delete('priority');


        // ------------------------------------------
        // SEND TO INCIDENT CONTROLLER
        // ------------------------------------------
            fetch(
                `${incidentController}?action=create_incident`,
                {
                    method: 'POST',
                    body: formData
                }
            )
            .then(async response => {
                const text = await response.text();

                console.log('RAW PHP RESPONSE:', text);

                try {
                    return JSON.parse(text);
                } catch (error) {
                    throw new Error(
                        'Server returned invalid JSON: ' + text
                    );
                }
            })
            .then(result => {

                if (!result.success) {

                    showMessage(
                        'danger',
                        result.message || 'Failed to submit incident.'
                    );

                    return;
                }


                // ----------------------------------
                // SUCCESS
                // ----------------------------------
                showMessage(
                    'success',
                    result.message || 'Incident reported successfully.'
                );


                // Reset form after successful submission.
                form.reset();

                qrCodeValue.value = '';

                departmentSelect.innerHTML = `
                    <option value="">
                        Select a category first
                    </option>
                `;

                selectedLocationInfo.textContent =
                    'You can select a location manually or scan the location QR code.';

                imagePreview.src = '';

                imagePreviewContainer.classList.add('d-none');

            })
            .catch(error => {

                console.error(
                    'Submit incident error:',
                    error
                );

                showMessage(
                    'danger',
                    'Unable to submit the incident. Please try again.'
                );

            })
            .finally(function () {

                submitBtn.disabled = false;

                submitSpinner.classList.add('d-none');
            });
    });


    // ==========================================
    // CLEAR MESSAGE
    // ==========================================
    function clearMessage() {

        formMessage.className =
            'alert d-none';

        formMessage.textContent = '';
    }


    // ==========================================
    // RESET FORM
    // ==========================================
    resetBtn.addEventListener('click', function () {

        setTimeout(function () {

            clearMessage();

            qrCodeValue.value = '';

            departmentSelect.innerHTML = `
                <option value="">
                    Select a category first
                </option>
            `;

            selectedLocationInfo.textContent =
                'You can select a location manually or scan the location QR code.';

            imagePreview.src = '';

            imagePreviewContainer.classList.add('d-none');

            stopQrScanner();

        }, 0);
    });


    // ==========================================
    // INITIAL LOAD
    // ==========================================
    loadLocations();
    loadCategories();

});