
<?php

require_once __DIR__ . "/config/conn.php";
require_once __DIR__ . "/model/IncidentModel.php";
$model = new IncidentModel($conn);

$incidentId = 28; 
$assignedBy = 8; 

$testCases = [
    [
        "test_case" => "Successful Assignment",
        "staff_id" => 6, // Active Response Staff
        "expected_success" => true,
        "expected_message" => "Incident assigned successfully."
    ],
    [
        "test_case" => "Invalid Staff Role",
        "staff_id" => 1, // Active user who is NOT Response Staff
        "expected_success" => false,
        "expected_message" => "Selected user is not Response Staff."
    ],
    [
        "test_case" => "Inactive Staff Account",
        "staff_id" => 15, // Inactive Response Staff
        "expected_success" => false,
        "expected_message" => "Selected staff account is not active."
    ]
];

foreach ($testCases as $test) {

    $result = $model->assignIncident(
        $incidentId,
        $test["staff_id"],
        $assignedBy,
        "Manual"
    );

    $passed =
        $result["success"] === $test["expected_success"]
        && $result["message"] === $test["expected_message"];

    echo "<h3>" .
        htmlspecialchars($test["test_case"]) .
        "</h3>";

    echo "<p><strong>Incident ID:</strong> " .
        $incidentId . "</p>";

    echo "<p><strong>Staff ID:</strong> " .
        $test["staff_id"] . "</p>";

    echo "<p><strong>Expected Result:</strong> " .
        htmlspecialchars($test["expected_message"]) .
        "</p>";

    echo "<p><strong>Actual Result:</strong> " .
        htmlspecialchars($result["message"]) .
        "</p>";

    echo "<p><strong>Outcome:</strong> " .
        ($passed ? "Pass" : "Fail") .
        "</p>";

    echo "<hr>";
}


