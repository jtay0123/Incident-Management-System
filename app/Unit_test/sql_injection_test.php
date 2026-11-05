
<?php

require_once __DIR__ . "/config/conn.php";
require_once __DIR__ . "/model/UserModel.php";

$userModel = new UserModel($conn);

$testInputs = [
    "' OR '1'='1' -- ",
    "admin'--",
    "' OR 1=1 -- "
];

echo "<h2>SQL Injection Testing - Login</h2>";

foreach ($testInputs as $input) {

    $result = $userModel->getUserByEmail($input);

    if ($result === null) {
        echo "<p>PASS - Input rejected: "
            . htmlspecialchars($input)
            . "</p>";
    } else {
        echo "<p>FAIL - Unexpected user returned</p>";
    }
}

?>
