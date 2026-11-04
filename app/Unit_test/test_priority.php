
<?php
require_once __DIR__ . "/config/conn.php";
require_once __DIR__ . "/model/IncidentModel.php";

$model = new IncidentModel($conn);

// Test cases
$testCases = [
    [
        "test_case" => "Emergency Priority",
        "category_id" => 33,
        "expected" => "Emergency"
    ],
    [
        "test_case" => "High Priority",
        "category_id" => 28,
        "expected" => "High"
    ],
    [
        "test_case" => "Medium Priority",
        "category_id" => 23,
        "expected" => "Medium"
    ],
    [
        "test_case" => "Low Priority",
        "category_id" => 100,
        "expected" => "Low"
    ]
];

foreach ($testCases as $test) {

    $actual = $model->determinePriority(
        $test["category_id"]
    );

    echo "<h3>" .
        htmlspecialchars($test["test_case"]) .
        "</h3>";

    echo "<p><strong>Category ID:</strong> " .
        $test["category_id"] . "</p>";

    echo "<p><strong>Expected Priority:</strong> " .
        htmlspecialchars($test["expected"]) . "</p>";

    echo "<p><strong>Actual Priority:</strong> " .
        htmlspecialchars($actual ?? "null") . "</p>";

    echo "<p><strong>Outcome:</strong> " .
        (($actual === $test["expected"]) ? "Pass" : "Fail") .
        "</p>";

    echo "<hr>";
}
