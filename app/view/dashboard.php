<?php

session_start();

require_once '../config/config.php';


// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location:' . BASE_URL . '/app/view/login_page.php');
    exit();
}


// Get user's role
$userRole = $_SESSION['user_role'] ?? '';


// Database role → folder name
$roleFolders = [
    'Super Admin'    => 'superadmin',
    'Admin'          => 'admin',
    'Response Staff' => 'response_staff',
    'Reporter'       => 'reporter',
    'Security'       => 'security'
];


// Check whether the role is valid
if (!isset($roleFolders[$userRole])) {
    session_destroy();

    header('Location:' . BASE_URL . '/app/view/login_page.php');
    exit();
}

$roleFolder = $roleFolders[$userRole];


// Default page for each role
$defaultPages = [
    'Super Admin'    => 'manage_accounts',
    'Admin'          => 'manage_incidents',
    'Response Staff' => 'my_incidents',
    'Reporter'       => 'my_reports',
    'Security'       => 'sensitive_incidents'
];


// Pages that each role is allowed to access
$validPages = [
    'Super Admin' => [
        'manage_accounts',
        
    ],

    'Admin' => [
        'manage_locations',
        'manage_users',
        'manage_incidents',
        'manage_sla_settings'
    ],

    'Response Staff' => [
        'my_incidents',
        'clock_in_out'
    ],

    'Reporter' => [
        'my_reports',
        'report_incident'
    ],

    'Security' => [
        'sensitive_incidents'
    ]
];


// Get requested page
$page = $_GET['page'] ?? $defaultPages[$userRole];


// Check whether user is allowed to access the page
if (!in_array($page, $validPages[$userRole])) {
    $page = $defaultPages[$userRole];
}


// Load the page using the folder name
$contentFile = __DIR__ . "/pages/{$roleFolder}/{$page}.php";


// Check whether page file exists
if (!file_exists($contentFile)) {
    $contentFile = null;
}

?>

<!DOCTYPE html>
<html>

<head>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css"
        rel="stylesheet"
        integrity="sha384-EVSTQN3/azprG1Anm3QDgpJLIm9Nao0Yz1ztcQTwFspd3yD65VohhpuuCOmLASjC"
        crossorigin="anonymous">

    <link rel="stylesheet"
        href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.3.0/font/bootstrap-icons.css">

    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;700&display=swap"
        rel="stylesheet">

    <link rel="stylesheet"
        href="<?php echo BASE_URL; ?>/assets/css/dashboard.css">

    <link rel="stylesheet"
        href="<?php echo BASE_URL; ?>/assets/css/manage_user.css">

    <link rel="stylesheet"
        href="<?php echo BASE_URL; ?>/assets/css/manage_accounts.css">


    <style>

        .nav-container {
            background: linear-gradient(
                135deg,
                #1a1a2e 0%,
                #16213e 50%,
                #0f3460 100%
            ) !important;

            padding: 12px 30px;

            display: flex;
            justify-content: space-between;
            align-items: center;

            box-shadow: 0 4px 20px rgba(0,0,0,0.3);
        }


        .logo-section {
            display: flex;
            align-items: center;
            gap: 15px;
        }


        .logo-section img {
            width: 40px;
            height: 40px;

            border-radius: 50%;

            border: 2px solid #3498db;

            padding: 3px;

            object-fit: cover;
        }


        .logo-section .brand-text {
            color: white;

            font-size: 18px;

            font-weight: 700;
        }


        .logo-section .brand-text span {
            color: #3498db;
        }


        .tab-container {
            display: flex;

            gap: 5px;

            flex: 1;

            justify-content: center;
        }


        .tab {
            padding: 8px 18px;

            color: #ffffff;

            text-decoration: none;

            border-radius: 6px;

            transition: all 0.3s ease;

            cursor: pointer;

            font-size: 14px;
        }


        .tab:hover {
            color: white;

            background: rgba(255,255,255,0.1);
        }


        .tab.active {
            color: white;

            background: #3498db;
        }


        .profile-container .dropdown-toggle {
            color: white !important;

            text-decoration: none;
        }


        .profile-container {
            background: transparent !important;
        }


        .profile-container .dropdown-toggle span {
            color: white;
        }


        .dropdown-menu .dropdown-item {
            color: #ff0000 !important;
        }


        .dropdown-menu .dropdown-item:hover {
            background: #d4e9ff !important;

            color: #ff0000 !important;
        }


        @media (max-width: 768px) {

            .nav-container {
                flex-wrap: wrap;

                padding: 10px 15px;
            }


            .logo-section .brand-text {
                font-size: 14px;
            }


            .tab-container {
                order: 3;

                flex-wrap: wrap;

                width: 100%;

                gap: 3px;
            }


            .tab {
                padding: 5px 10px;

                font-size: 11px;
            }

        }

    </style>

</head>


<body>


    <!-- HEADER / NAVIGATION -->

    <nav class="nav-container">


        <!-- Logo -->

        <div class="logo-section">

            <img src="<?php echo BASE_URL; ?>/assets/img/logo.jpg"
                alt="Logo">

            <div class="brand-text">
                IMS<span></span>
            </div>

        </div>


        <!-- Navigation Tabs -->

        <div class="tab-container">

            <?php

            switch ($userRole) {


                // =========================
                // SUPER ADMIN
                // =========================

                case 'Super Admin':

            ?>

                    <a class="tab <?php echo ($page === 'manage_accounts') ? 'active' : ''; ?>"
                        href="dashboard.php?page=manage_accounts">

                        Manage Accounts

                    </a>


            <?php
                    break;


                // =========================
                // ADMIN
                // =========================

                case 'Admin':

            ?>

                    <a class="tab <?php echo ($page === 'manage_locations') ? 'active' : ''; ?>"
                        href="dashboard.php?page=manage_locations">

                        Locations

                    </a>


                    <a class="tab <?php echo ($page === 'manage_users') ? 'active' : ''; ?>"
                        href="dashboard.php?page=manage_users">

                        Manage Users

                    </a>


                    <a class="tab <?php echo ($page === 'manage_incidents') ? 'active' : ''; ?>"
                        href="dashboard.php?page=manage_incidents">

                        Incidents

                    </a>


                    <a class="tab <?php echo ($page === 'sla_settings') ? 'active' : ''; ?>"
                        href="dashboard.php?page=manage_sla_settings">

                        SLA Settings

                    </a>

            <?php
                    break;


                // =========================
                // RESPONSE STAFF
                // =========================

                case 'Response Staff':

            ?>

                    <a class="tab <?php echo ($page === 'my_incidents') ? 'active' : ''; ?>"
                        href="dashboard.php?page=my_incidents">

                        My Incidents

                    </a>


                    <a class="tab <?php echo ($page === 'clock_in_out') ? 'active' : ''; ?>"
                        href="dashboard.php?page=clock_in_out">

                        Clock In / Out

                    </a>

            <?php
                    break;


                // =========================
                // REPORTER
                // =========================

                case 'Reporter':

            ?>

                    <a class="tab <?php echo ($page === 'my_reports') ? 'active' : ''; ?>"
                        href="dashboard.php?page=my_reports">

                        My Reports

                    </a>


                    <a class="tab <?php echo ($page === 'report_incident') ? 'active' : ''; ?>"
                        href="dashboard.php?page=report_incident">

                        Report Incident

                    </a>

            <?php
                    break;


                // =========================
                // SECURITY
                // =========================

                case 'Security':

            ?>

                    <a class="tab <?php echo ($page === 'sensitive_incidents') ? 'active' : ''; ?>"
                        href="dashboard.php?page=sensitive_incidents">

                        Sensitive Incidents

                    </a>

            <?php
                    break;

            }

            ?>

        </div>


        <!-- Profile -->

        <div class="profile-container dropdown">

            <a class="dropdown-toggle d-flex align-items-center text-decoration-none"
                href="#"
                role="button"
                data-bs-toggle="dropdown"
                aria-expanded="false">

                <i class="bi bi-person-circle me-3 fs-2"></i>

                <span>
                    <?php
                    echo htmlspecialchars(
                        $_SESSION['user_email'] ?? 'User'
                    );
                    ?>
                </span>

            </a>


            <ul class="dropdown-menu dropdown-menu-end shadow">

                <li>

                    <button type="button"
                        class="dropdown-item text-danger"
                        data-bs-toggle="modal"
                        data-bs-target="#logoutModal">

                        <i class="bi bi-box-arrow-right me-2"></i>

                        Logout

                    </button>

                </li>

            </ul>

        </div>


    </nav>


    <!-- PAGE CONTENT -->

    <div class="content-container">

        <?php

        if ($contentFile) {

            include $contentFile;

        } else {

            echo "
                <div class='alert alert-warning m-4'>
                    Page not found.
                </div>
            ";

        }

        ?>

    </div>


    <!-- LOGOUT MODAL -->

    <div class="modal fade"
        id="logoutModal"
        tabindex="-1">

        <div class="modal-dialog modal-sm">

            <div class="modal-content">

                <div class="modal-header">

                    <h5 class="modal-title">
                        Confirm Logout
                    </h5>

                    <button type="button"
                        class="btn-close"
                        data-bs-dismiss="modal">
                    </button>

                </div>


                <div class="modal-body">

                    <p>
                        Are you sure you want to log out?
                    </p>

                </div>


                <div class="modal-footer">

                    <button type="button"
                        class="btn btn-secondary"
                        data-bs-dismiss="modal">

                        Cancel

                    </button>


                    <form method="POST"
                        action="<?php echo BASE_URL; ?>/app/controller/AuthController.php">

                        <input type="hidden"
                            name="logout"
                            value="1">


                        <button type="submit"
                            class="btn btn-danger">

                            Logout

                        </button>

                    </form>

                </div>

            </div>

        </div>

    </div>


    <!-- Bootstrap -->

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/js/bootstrap.bundle.min.js">
    </script>


    <script src="<?php echo BASE_URL; ?>/assets/js/main.js">
    </script>


    <!-- Current User -->

    <script>

        const loggedInUserId = "<?php
            echo $_SESSION['user_id'] ?? '';
        ?>";

        const loggedInUserRole = "<?php
            echo htmlspecialchars($_SESSION['user_role'] ?? '');
        ?>";

    </script>


    <!-- Page-specific JavaScript -->

    <?php

    $pageScripts = [

        'manage_accounts' => ['manage_admins.js'],

        'admin_requests' => ['superadmin_requests.js'],

        'manage_locations' => ['manage_locations.js'],

        'manage_users' => ['admin_manage_users.js'],

        'manage_incidents' => [],

        'manage_sla_settings' => ['manage_sla_settings.js'],

        'my_incidents' => [],

        'clock_in_out' => [],

        'my_reports' => [],

        'report_incident' => [],

        'sensitive_incidents' => []

    ];


    if (isset($pageScripts[$page])) {

        foreach ($pageScripts[$page] as $script) {

            echo '<script src="' .
                BASE_URL .
                '/assets/js/' .
                $script .
                '"></script>';

        }

    }

    ?>

</body>

</html>