<?php
session_start();
require_once "../classes/database.php";

// PHPMailer for email receipts
$autoload = __DIR__ . '/../vendor/autoload.php';
if (file_exists($autoload)) {
    require_once $autoload;
}
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

if (!isset($_SESSION['user_id']) || ($_SESSION['role'] ?? '') !== 'tenant') {
    header("Location: ../index.php");
    exit();
}

$db = new Database();
$conn = $db->connect();

$payment_id = $_POST['rent_id'] ?? null; // this is actually the payment_id
$method = $_POST['payment_method'] ?? null;
$ref = $_POST['reference_number'] ?? null;
$tenant_id = $_SESSION['user_id'];

if (!$payment_id || !$method) {
    http_response_code(400);
    exit('Invalid input.');
}

// Optional: handle GCash receipt upload
$receiptPath = null;
if (!empty($_FILES['gcash_receipt']['name']) && is_uploaded_file($_FILES['gcash_receipt']['tmp_name'])) {
    $targetDir = __DIR__ . "/../uploads/receipts/";
    if (!file_exists($targetDir)) mkdir($targetDir, 0777, true);
    $ext = pathinfo($_FILES['gcash_receipt']['name'], PATHINFO_EXTENSION);
    $safeExt = preg_replace('/[^a-zA-Z0-9]/', '', $ext);
    $fileName = time() . "_" . $tenant_id . "_" . $payment_id . "." . ($safeExt ?: 'jpg');
    $targetFile = $targetDir . $fileName;
    if (move_uploaded_file($_FILES["gcash_receipt"]["tmp_name"], $targetFile)) {
        // Web-accessible relative path
        $receiptPath = "uploads/receipts/" . $fileName;
    }
}

// Update the payments table (does not assume receipt column exists)
$stmt = $conn->prepare("
    UPDATE payments 
    SET 
        status = 'Paid', 
        payment_method = :method, 
        reference_number = :ref, 
        date_paid = NOW()
    WHERE payment_id = :pid AND tenant_id = :tenant_id
");
$stmt->execute([
    ':method' => $method,
    ':ref' => $ref,
    ':pid' => $payment_id,
    ':tenant_id' => $tenant_id
]);

// Fetch tenant email and name for receipt
$tenantQry = $conn->prepare("SELECT firstname, email FROM tenants WHERE tenant_id = :tid LIMIT 1");
$tenantQry->execute([':tid' => $tenant_id]);
$tenant = $tenantQry->fetch(PDO::FETCH_ASSOC) ?: ['firstname' => 'Tenant', 'email' => null];

// Create session flash for UI receipt modal
$_SESSION['last_payment'] = [
    'payment_id' => $payment_id,
    'amount' => $_POST['amount'] ?? '',
    'method' => $method,
    'reference' => $ref,
    'receipt' => $receiptPath,
    'time' => date('Y-m-d H:i:s')
];

// Try emailing the receipt (best-effort)
if (class_exists(PHPMailer::class) && !empty($tenant['email'])) {
    try {
        $mail = new PHPMailer(true);
        $mail->isSMTP();
        $mail->Host = 'smtp.hostinger.com';
        $mail->SMTPAuth = true;
        $mail->Username = 'apthub@apartmenthub.online';
        $mail->Password = 'Thirdy_090803';
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port = 587;
        $mail->CharSet = 'UTF-8';
        $mail->Timeout = 30;
        $mail->SMTPOptions = [
            'ssl' => [
                'verify_peer' => false,
                'verify_peer_name' => false,
                'allow_self_signed' => true
            ]
        ];

        $mail->setFrom('apthub@apartmenthub.online', 'ApartmentHub');
        $mail->addAddress($tenant['email'], $tenant['firstname'] ?: 'Tenant');
        $mail->isHTML(true);
        $mail->Subject = 'Payment Receipt - ApartmentHub';
        $body = "<div style='font-family:Arial,sans-serif'>"
              . "<h3>Hi " . htmlspecialchars($tenant['firstname'] ?: 'Tenant') . ",</h3>"
              . "<p>We received your payment.</p>"
              . "<ul>"
              . "<li><strong>Payment ID:</strong> " . htmlspecialchars($payment_id) . "</li>"
              . "<li><strong>Amount:</strong> ₱" . htmlspecialchars($_POST['amount'] ?? '') . "</li>"
              . "<li><strong>Method:</strong> " . htmlspecialchars($method) . "</li>"
              . "<li><strong>Reference #:</strong> " . htmlspecialchars($ref ?: '-') . "</li>"
              . "<li><strong>Date:</strong> " . date('M d, Y h:i A') . "</li>"
              . "</ul>"
              . (!empty($receiptPath) ? "<p>Your uploaded proof is attached.</p>" : "")
              . "<p>Thank you!<br>ApartmentHub</p></div>";
        $mail->Body = $body;

        if (!empty($receiptPath)) {
            $abs = __DIR__ . '/../' . $receiptPath; // convert to filesystem path
            if (file_exists($abs)) {
                $mail->addAttachment($abs, 'proof-of-payment.' . pathinfo($abs, PATHINFO_EXTENSION));
            }
        }

        $mail->send();
    } catch (Exception $e) {
        // Silent fail; UI will still show success
        error_log('Payment mail error: ' . $e->getMessage());
    }
}

header("Location: ../tenant/pay_rent.php?success=1");
exit;
