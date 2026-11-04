<?php

require_once __DIR__ . "/config/conn.php";
require_once __DIR__ . "/model/IncidentModel.php";
$model = new IncidentModel($conn);

// Test input
$title = "Problem in Room 204";
$description = "The computer cannot connect to the WiFi network.";

// Execute the function
$result = $model->matchCategoryByKeywords(
    $title,
    $description
);

// Display the result
echo "<h3>Category Matching by Keywords</h3>";

echo "<p><strong>Title:</strong> "
    . htmlspecialchars($title) . "</p>";

echo "<p><strong>Description:</strong> "
    . htmlspecialchars($description) . "</p>";

echo "<p><strong>Actual Result:</strong></p>";

echo "<pre>";
var_export($result);
echo "</pre>";