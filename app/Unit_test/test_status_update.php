
<?php

require_once __DIR__ . "/config/conn.php";
require_once __DIR__ . "/model/IncidentModel.php";

$model = new IncidentModel($conn);


$incidentId = 28;
$userId = 6; //  C_UI

$testCases = [
    [
        "test_case" => "Update to a Valid Status",
        "incident_id" => $incidentId,
        "new_status" => "In Progress",
        "expected_success" => true,
        "expected_message" => "Incident status updated successfully."
    ],
    [
        "test_case" => "Invalid Incident Status",
        "incident_id" => $incidentId,
        "new_status" => "Pending",
        "expected_success" => false,
        "expected_message" => "Invalid incident status."
    ],
    [
        "test_case" => "Non-existing Incident",
        "incident_id" => 999999,
        "new_status" => "In Progress",
        "expected_success" => false,
        "expected_message" => "Incident not found."
    ]
];

foreach ($testCases as $test) {

    $result = $model->updateIncidentStatus(
        $test["incident_id"],
        $test["new_status"],
        $userId,
        "Unit testing status update"
    );

    $passed =
        $result["success"] === $test["expected_success"]
        && $result["message"] === $test["expected_message"];

    echo "<h3>" .
        htmlspecialchars($test["test_case"]) .
        "</h3>";

    echo "<p><strong>Incident ID:</strong> " .
        $test["incident_id"] . "</p>";

    echo "<p><strong>New Status:</strong> " .
        htmlspecialchars($test["new_status"]) . "</p>";

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
