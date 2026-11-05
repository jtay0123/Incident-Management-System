
document.addEventListener('DOMContentLoaded', () => {

    const totalAdmins = document.getElementById('totalAdmins');
    const activeAdmins = document.getElementById('activeAdmins');
    const inactiveAdmins = document.getElementById('inactiveAdmins');
    const suspendedAdmins = document.getElementById('suspendedAdmins');

    const tableBody = document.getElementById('adminAccountsTable');

    const searchInput = document.getElementById('searchInput');
    const statusFilter = document.getElementById('filter-status');

    let adminData = [];


    // =========================================================
    // LOAD ADMIN ACCOUNTS
    // =========================================================

    function loadAdmins() {

        fetch(window.BASE_URL + '/app/controller/SuperAdminController.php?action=getAdmins')

            .then(response => {

                if (!response.ok) {
                    throw new Error('Failed to load admin accounts.');
                }

                return response.json();
            })

            .then(data => {

                adminData = data;

                displayAdmins(adminData);

                loadStats();
            })

            .catch(error => {

    console.error('Error loading admin accounts:', error);

    tableBody.innerHTML =
        '<tr>' +
            '<td colspan="7" class="text-center py-4 text-danger">' +
                'Error loading admin accounts.' +
            '</td>' +
        '</tr>';
});
}

    // =========================================================
    // LOAD ADMIN STATISTICS
    // =========================================================

    function loadStats() {

        fetch(window.BASE_URL + '/app/controller/SuperAdminController.php?action=getStats')

            .then(response => {

                if (!response.ok) {
                    throw new Error('Failed to load statistics.');
                }

                return response.json();
            })

            .then(data => {

                totalAdmins.textContent = data.total ?? 0;
                activeAdmins.textContent = data.active ?? 0;
                inactiveAdmins.textContent = data.inactive ?? 0;
                suspendedAdmins.textContent = data.suspended ?? 0;
            })

            .catch(error => {

                console.error('Error loading statistics:', error);
            });
    }


    // =========================================================
    // DISPLAY ADMIN ACCOUNTS
    // =========================================================

    function displayAdmins(data) {

        tableBody.innerHTML = '';


        if (!data || data.length === 0) {

        tableBody.innerHTML =
            '<tr>' +
                '<td colspan="7" class="text-center py-4 text-muted">' +
                    'No admin accounts found.' +
                '</td>' +
            '</tr>';

            return;
        }


        data.forEach(admin => {

            const row = document.createElement('tr');

            let statusClass = 'bg-secondary';


            if (admin.account_status === 'Active') {

                statusClass = 'bg-success';

            } else if (admin.account_status === 'Inactive') {

                statusClass = 'bg-warning text-dark';

            } else if (admin.account_status === 'Suspended') {

                statusClass = 'bg-danger';
            }



row.innerHTML =
    '<td>' + admin.user_id + '</td>' +

    '<td>' + admin.full_name + '</td>' +

    '<td>' + admin.email + '</td>' +

    '<td>' + admin.user_type + '</td>' +

    '<td>' +
        '<span class="badge ' + statusClass + '">' +
            admin.account_status +
        '</span>' +
    '</td>' +

    '<td>' + admin.created_at + '</td>' +

    '<td class="text-center">' +

        '<select ' +
            'class="form-select form-select-sm status-select d-inline-block me-1" ' +
            'style="width: 120px;" ' +
            'data-id="' + admin.user_id + '">' +

            '<option value="Active" ' +
                (admin.account_status === 'Active' ? 'selected' : '') +
            '>Active</option>' +

            '<option value="Inactive" ' +
                (admin.account_status === 'Inactive' ? 'selected' : '') +
            '>Inactive</option>' +

            '<option value="Suspended" ' +
                (admin.account_status === 'Suspended' ? 'selected' : '') +
            '>Suspended</option>' +

        '</select>' +

        '<button ' +
            'type="button" ' +
            'class="btn btn-sm btn-outline-danger delete-admin-btn" ' +
            'data-id="' + admin.user_id + '"' +
        '>' +
            'Delete' +
        '</button>' +

    '</td>';



            tableBody.appendChild(row);
        });
    }


    // =========================================================
    // SEARCH + STATUS FILTER
    // =========================================================

    function filterAdmins() {

        const searchValue =
            searchInput.value.toLowerCase().trim();

        const selectedStatus =
            statusFilter.value;


        const filteredAdmins = adminData.filter(admin => {

            const fullName =
                (admin.full_name || '').toLowerCase();

            const email =
                (admin.email || '').toLowerCase();


            const matchesSearch =
                fullName.includes(searchValue) ||
                email.includes(searchValue);


            const matchesStatus =
                selectedStatus === '' ||
                admin.account_status === selectedStatus;


            return matchesSearch && matchesStatus;
        });


        displayAdmins(filteredAdmins);
    }


    // =========================================================
    // SEARCH EVENT
    // =========================================================

    if (searchInput) {

        searchInput.addEventListener(
            'input',
            filterAdmins
        );
    }


    // =========================================================
    // STATUS FILTER EVENT
    // =========================================================

    if (statusFilter) {

        statusFilter.addEventListener(
            'change',
            filterAdmins
        );
    }



    // =========================================================
    // UPDATE ADMIN STATUS
    // =========================================================

    document.addEventListener('change', event => {

        if (
            !event.target.classList.contains(
                'status-select'
            )
        ) {
            return;
        }


        const userId =
            event.target.dataset.id;

        const newStatus =
            event.target.value;


        fetch(
            window.BASE_URL +
            '/app/controller/SuperAdminController.php?action=updateStatus',
            {
                method: 'POST',

                headers: {
                    'Content-Type': 'application/json'
                },

                body: JSON.stringify({
                    user_id: userId,
                    status: newStatus
                })
            }
        )

        .then(response => response.json())

        .then(data => {

            if (!data.success) {

                throw new Error(
                    data.message ||
                    'Failed to update status.'
                );
            }


            // Reload the admin list and statistics
            loadAdmins();

        })

        .catch(error => {

            console.error(
                'Error updating admin status:',
                error
            );

            alert(
                error.message
            );
            

            // Restore the correct status from database
            loadAdmins();
        });

    });

    // =========================================================
    // DELETE ADMIN
    // =========================================================

    document.addEventListener('click', event => {

        if (
            !event.target.classList.contains(
                'delete-admin-btn'
            )
        ) {
            return;
        }


        const userId =
            event.target.dataset.id;


        const confirmDelete =
            confirm(
                'Are you sure you want to delete this Admin account?'
            );


        if (!confirmDelete) {
            return;
        }


        fetch(
  
          
            window.BASE_URL + '/app/controller/SuperAdminController.php?action=delete',
            {
                method: 'POST',

                headers: {
                    'Content-Type': 'application/json'
                },

                body: JSON.stringify({
                    user_id: userId
                })
            }
        )

        .then(response => response.json())

        .then(data => {

            if (!data.success) {

                throw new Error(
                    data.message ||
                    'Failed to delete admin.'
                );
            }


            loadAdmins();
        })

        .catch(error => {

            console.error(
                'Error deleting admin:',
                error
            );

            alert(
                'Failed to delete admin account.'
            );
        });
    });


    // =========================================================
    // INITIAL LOAD
    // =========================================================

    loadAdmins();

});

