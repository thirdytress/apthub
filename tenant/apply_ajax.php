<?php
session_start();
require_once "../classes/database.php";

if (!isset($_SESSION['user_id']) || ($_SESSION['role'] ?? '') !== 'tenant') {
    echo "⚠️ Only tenants can apply.";
    exit();
}

if (isset($_POST['apartment_id'])) {
    $db = new Database();
    $conn = $db->connect();
    $tenant_id = $_SESSION['user_id'];
    $apartment_id = intval($_POST['apartment_id']);

    // Check if there is an existing application
    $check = $conn->prepare("SELECT status FROM applications WHERE tenant_id = :tid AND apartment_id = :aid LIMIT 1");
    $check->execute([':tid' => $tenant_id, ':aid' => $apartment_id]);
    $existing = $check->fetch(PDO::FETCH_ASSOC);

    if ($existing) {
        if (strcasecmp($existing['status'], 'Rejected') === 0) {
            // Set a one-time flag for popup on my_applications.php
            $_SESSION['apply_rejected'] = 1;
            echo "⚠️ Your application was previously rejected for this apartment. You cannot apply again.";
            exit();
        } else {
            echo "⚠️ You have already applied for this apartment.";
            exit();
        }
    }

    // No existing app; proceed via model helper
    $result = $db->applyApartment($tenant_id, $apartment_id);

    if ($result === true) {
        echo "✅ Application submitted successfully!";
    } else {
        echo "⚠️ " . $result;
    }
} else {
    echo "⚠️ Invalid request.";
}
