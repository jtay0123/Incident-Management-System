
<?php

require_once __DIR__ . "/config/conn.php";
require_once __DIR__ . "/model/SLALogicModel.php";

$model = new SLALogicModel($conn);

$testCases = [
    [
        "name" => "Incident Within SLA",
        "incident_id" => 31,
        "expected_status" => "On Track",
        "condition" => "within"
    ],
    [
        "name" => "Incident Exceeds SLA",
        "incident_id" => 29,
        "expected_status" => "Overdue",
        "condition" => "overdue"
    ],
    [
        "name" => "Incident Resolved Within SLA",
        "incident_id" => 19,
        "expected_status" => "Completed",
        "condition" => "resolved"
    ]
];

echo "<h2>SLA Status Tracking</h2>";

foreach ($testCases as $test) {

    $id = $test["incident_id"];
    $incident = $model->getIncident($id);
    $sla = $model->getSLARecord($id);

    echo "<hr><h3>" .
        htmlspecialchars($test["name"]) . "</h3>";

    if (!$incident || !$sla) {
        echo "Result: FAIL - Incident or SLA record not found.";
        continue;
    }

    $now = new DateTime();
    $due = new DateTime($sla["due_time"]);

    $isActive = !in_array(
        $incident["status"],
        ["Resolved", "Closed"],
        true
    );

    $conditionValid = false;

    if ($test["condition"] === "within") {
        $conditionValid = $isActive && $now <= $due;
    } elseif ($test["condition"] === "overdue") {
        $conditionValid = $isActive && $now > $due;
    } elseif ($test["condition"] === "resolved") {
        $completed = $incident["resolved_at"] ?? null;

        $conditionValid =
            in_array($incident["status"], ["Resolved", "Closed"], true)
            && !empty($completed)
            && new DateTime($completed) <= $due;
    }

    if (!$conditionValid) {
        echo "Result: NOT TESTED<br>";
        echo "Reason: Incident does not meet the test condition.";
        continue;
    }

    $result = $model->updateSLAStatus($id);
    $updated = $model->getSLARecord($id);
    $actual = $updated["sla_status"] ?? null;

    $pass =
        ($result["success"] ?? false) === true
        && $actual === $test["expected_status"];

    echo "Incident ID: " . $id . "<br>";
    echo "Expected: " .
        htmlspecialchars($test["expected_status"]) . "<br>";
    echo "Actual: " .
        htmlspecialchars($actual ?? "No status") . "<br>";
    echo "Result: <strong>" .
        ($pass ? "PASS" : "FAIL") . "</strong>";
}
?>