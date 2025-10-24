<?php
// Simple test to verify the register endpoint is reachable
header('Content-Type: application/json');
echo json_encode([
    "status" => "success",
    "message" => "Test endpoint is working!",
    "php_version" => phpversion(),
    "post_data" => $_POST
]);
?>
