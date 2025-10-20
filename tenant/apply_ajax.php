<?php
session_start();
require_once "../classes/database.php";

if (!isset($_SESSION['user_id']) || ($_SESSION['role'] ?? '') !== 'tenant') {
    echo "⚠️ Only tenants can apply.";
    exit();
}

if (isset($_POST['apartment_id'])) {
    $db = new Database();
    $tenant_id = $_SESSION['user_id'];
    $apartment_id = intval($_POST['apartment_id']);

    $result = $db->applyApartment($tenant_id, $apartment_id);

    if ($result === true) {
        echo "✅ Application submitted successfully!";
    } else {
        echo "⚠️ " . $result;
    }
} else {
    echo "⚠️ Invalid request.";
}
