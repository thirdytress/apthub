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
        status = 'Pending',
        payment_method = :method,
        reference_number = :ref
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
    $mail->Subject = 'Payment Submitted - Awaiting Confirmation | ApartmentHub';

        // Branding and helpful vars
        $tenantName = htmlspecialchars($tenant['firstname'] ?: 'Tenant');
        $amountNum = isset($_POST['amount']) ? (float)$_POST['amount'] : 0;
        $amountDisp = '₱' . number_format($amountNum, 2);
        $dateDisp = date('M d, Y h:i A');

        // Compute a best-effort absolute link to the payments page
        $scheme = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
        $host = $_SERVER['HTTP_HOST'] ?? 'localhost';
        $basePath = rtrim(dirname($_SERVER['SCRIPT_NAME'] ?? '/'), '/\\');
        $baseBase = (strpos($basePath, '/ahub') !== false) ? '/ahub' : '';
        $ctaUrl = $scheme . '://' . $host . $baseBase . '/tenant/view_payments.php';

        // Optional embedded logo (if present)
        $logoPath = __DIR__ . '/../images/logo.png';
        $hasLogo = false;
        if (file_exists($logoPath)) {
            try { $mail->addEmbeddedImage($logoPath, 'logo_cid'); $hasLogo = true; } catch (Exception $e) { /* ignore */ }
        }

        // Polished HTML email with inline styles (email-client friendly)
        $body = ""
            . "<div style='background-color:#f6f9fc;padding:24px;font-family:Arial,Helvetica,sans-serif;color:#111827'>"
            . "  <div style='max-width:560px;margin:0 auto;background:#ffffff;border:1px solid #e5e7eb;border-radius:10px;overflow:hidden'>"
            . "    <div style='background:#0f172a;padding:14px 20px;color:#fff'>"
            . ( $hasLogo
                ? "      <img src=\"cid:logo_cid\" alt=\"ApartmentHub\" style=\"height:28px;vertical-align:middle\">"
                : "      <span style=\"font-weight:700;font-size:18px;letter-spacing:.3px\">ApartmentHub</span>"
              )
            . "    </div>"
            . "    <div style='padding:22px'>"
            . "      <h2 style='margin:0 0 8px;font-size:20px;color:#0f172a'>Payment submitted</h2>"
            . "      <p style='margin:0 0 16px;color:#374151'>Hi " . $tenantName . ", we received your payment submission. Our team will review and confirm once verified. Here are the details:</p>"
            . "      <table role='presentation' style='width:100%;border-collapse:collapse'>"
            . "        <tr>"
            . "          <td style='padding:10px;border:1px solid #e5e7eb;width:40%;color:#6b7280'>Payment ID</td>"
            . "          <td style='padding:10px;border:1px solid #e5e7eb;color:#111827'>" . htmlspecialchars($payment_id) . "</td>"
            . "        </tr>"
            . "        <tr>"
            . "          <td style='padding:10px;border:1px solid #e5e7eb;color:#6b7280'>Amount</td>"
            . "          <td style='padding:10px;border:1px solid #e5e7eb;color:#111827'>" . $amountDisp . "</td>"
            . "        </tr>"
            . "        <tr>"
            . "          <td style='padding:10px;border:1px solid #e5e7eb;color:#6b7280'>Method</td>"
            . "          <td style='padding:10px;border:1px solid #e5e7eb;color:#111827'>" . htmlspecialchars($method) . "</td>"
            . "        </tr>"
            . "        <tr>"
            . "          <td style='padding:10px;border:1px solid #e5e7eb;color:#6b7280'>Reference #</td>"
            . "          <td style='padding:10px;border:1px solid #e5e7eb;color:#111827'>" . htmlspecialchars($ref ?: '-') . "</td>"
            . "        </tr>"
            . "        <tr>"
            . "          <td style='padding:10px;border:1px solid #e5e7eb;color:#6b7280'>Date</td>"
            . "          <td style='padding:10px;border:1px solid #e5e7eb;color:#111827'>" . $dateDisp . "</td>"
            . "        </tr>"
            . "      </table>"
            . ( !empty($receiptPath)
                ? "      <p style='margin:16px 0 0;color:#374151'>Your uploaded proof is attached to this email.</p>"
                : ""
              )
            . "      <div style='margin-top:18px'>"
            . "        <a href='" . htmlspecialchars($ctaUrl) . "' style='background:#2563eb;color:#fff;text-decoration:none;padding:10px 16px;border-radius:8px;display:inline-block'>View in dashboard</a>"
            . "      </div>"
            . "      <p style='font-size:12px;color:#6b7280;margin:16px 0 0'>If you did not make this payment, please contact us at apthub@apartmenthub.online.</p>"
            . "    </div>"
            . "    <div style='background:#f3f4f6;padding:12px 20px;color:#6b7280;font-size:12px;text-align:center'>© " . date('Y') . " ApartmentHub</div>"
            . "  </div>"
            . "</div>";
        $mail->Body = $body;
        $mail->AltBody = "Payment submitted (awaiting confirmation)\n" .
            "Payment ID: " . (string)$payment_id . "\n" .
            "Amount: " . str_replace('₱','',$amountDisp) . " PHP\n" .
            "Method: " . ($method ?: '-') . "\n" .
            "Reference #: " . ($ref ?: '-') . "\n" .
            "Date: " . $dateDisp . "\n\n" .
            (!empty($receiptPath) ? "Your uploaded proof is attached.\n\n" : '') .
            "View in dashboard: " . $ctaUrl . "\n";

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
