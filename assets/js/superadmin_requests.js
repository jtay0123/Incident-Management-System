document.addEventListener('DOMContentLoaded', function () {
    loadRequests();
});

function loadRequests() {
    const tbody = document.getElementById('requestsTableBody');
    tbody.innerHTML = '<tr><td colspan="7" class="text-center py-4 text-muted">Loading requests...</td></tr>';

    fetch('../../app/controller/SuperAdminController.php?action=getRequests')
        .then(response => response.json())
        .then(data => {
            tbody.innerHTML = '';

            if (data.length === 0) {
                tbody.innerHTML = '<tr><td colspan="7" class="text-center py-4 text-muted">No pending requests found</td></tr>';
                return;
            }

            data.forEach(request => {
                const tr = document.createElement('tr');
                tr.innerHTML = `
                    <td class="ps-4 fw-bold">${request.staff_id}</td>
                    <td>
                        <div class="d-flex flex-column">
                            <span class="fw-bold">${request.staff_name}</span>
                            <small class="text-muted">${request.staff_specialization || ''}</small>
                        </div>
                    </td>
                    <td><span class="badge bg-${request.staff_role === 'doctor' ? 'info' : 'warning'} text-dark">${request.staff_role.toUpperCase()}</span></td>
                    <td>${request.staff_email}</td>
                    <td>${request.requester_name || request.registered_by}</td>
                    <td>${request.staff_dob}</td> <!-- Using DOB as placeholder for request date if no created_at -->
                    <td class="text-end pe-4">
                        <button class="btn btn-sm btn-success me-2" onclick="handleRequest('${request.staff_id}', 'approved')">
                            <i class="bi bi-check-lg"></i> Approve
                        </button>
                        <button class="btn btn-sm btn-danger" onclick="handleRequest('${request.staff_id}', 'rejected')">
                            <i class="bi bi-x-lg"></i> Reject
                        </button>
                    </td>
                `;
                tbody.appendChild(tr);
            });
        })
        .catch(error => {
            console.error('Error:', error);
            tbody.innerHTML = '<tr><td colspan="7" class="text-center py-4 text-danger">Error loading requests</td></tr>';
        });
}

function handleRequest(staffId, status) {
    if (!confirm(`Are you sure you want to ${status === 'approved' ? 'APPROVE' : 'REJECT'} this request?`)) {
        return;
    }

    fetch('../../app/controller/SuperAdminController.php?action=handleRequest', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json'
        },
        body: JSON.stringify({
            staffId: staffId,
            status: status
        })
    })
        .then(response => response.json())
        .then(result => {
            if (result.success) {
                // alert(`Request ${status === 'Active' ? 'approved' : 'rejected'} successfully`);
                loadRequests(); // Reload table
            } else {
                alert('Error: ' + (result.message || 'Operation failed'));
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('An error occurred');
        });
}
