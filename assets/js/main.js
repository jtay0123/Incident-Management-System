// Get user role from PHP session (passed via data attribute or inline script)
const userRole = document.body.dataset.userRole || 'Super Admin';

function openTab(tabId, clickedTab) {
    //update url with new page
    window.location.href = `dashboard.php?page=${tabId}`;
}

document.addEventListener('DOMContentLoaded', function () {
    const urlParams = new URLSearchParams(window.location.search);
    const currentPage = urlParams.get('page');
    const tabs = document.getElementsByClassName('tab');

    // Default page based on role
    const roleDefaults = {
    'Super Admin': 'manage_accounts',
    'Admin': 'manage_users',
    'Response Staff': 'my_incidents',
    'Reporter': 'my_reports',
    
    };

    const activePage = currentPage || roleDefaults[userRole];

    for (let tab of tabs) {
        const tabPage = tab.getAttribute('data-page');
        if (tabPage === activePage) {
            tab.classList.add('active');
            break; // Exit loop once active tab is found
        } else {
            tab.classList.remove('active');
        }
    }

    // Update URL with default page if not present
    if (!currentPage) {
        window.history.replaceState({}, document.title, window.location.pathname + '?page=' + activePage);
    }
});

