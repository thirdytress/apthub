<?php
class Database {
    private $host = "localhost";
    private $db_name = "apthub_db";
    private $username = "root";
    private $password = "";
    private $conn;

    public function connect() {
        if ($this->conn === null) {
            try {
                $this->conn = new PDO("mysql:host=$this->host;dbname=$this->db_name;charset=utf8", $this->username, $this->password);
                $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            } catch (PDOException $e) {
                die("Database connection failed: " . $e->getMessage());
            }
        }
        return $this->conn;
    }

    // ===========================
    // Admin Functions
    // ===========================
    public function getAdminById($id) {
        $stmt = $this->connect()->prepare("SELECT * FROM admins WHERE admin_id = :id LIMIT 1");
        $stmt->bindParam(':id', $id);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function getAdminByUsername($username) {
        $stmt = $this->connect()->prepare("SELECT * FROM admins WHERE username = :u LIMIT 1");
        $stmt->bindParam(':u', $username);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function updateAdminPassword($admin_id, $new_password) {
        $hashed = password_hash($new_password, PASSWORD_DEFAULT);
        $stmt = $this->connect()->prepare("UPDATE admins SET password = :p WHERE admin_id = :id");
        $stmt->bindParam(':p', $hashed);
        $stmt->bindParam(':id', $admin_id);
        return $stmt->execute();
    }

    public function changeAdminPassword($admin_id, $new_password) {
        return $this->updateAdminPassword($admin_id, $new_password);
    }

    public function checkAdminExists($username) {
        $stmt = $this->connect()->prepare("SELECT * FROM admins WHERE username=:u LIMIT 1");
        $stmt->bindParam(':u', $username);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC) ? true : false;
    }

   public function registerAdmin($firstname, $lastname, $username, $password, $confirm) {
    if ($password !== $confirm) return "Passwords do not match.";

    if ($this->checkAdminExists($username)) return "Admin username already exists.";

    $hashed = password_hash($password, PASSWORD_DEFAULT);
    $stmt = $this->connect()->prepare("
        INSERT INTO admins (firstname, lastname, username, password)
        VALUES (:fn, :ln, :u, :p)
    ");
    $stmt->bindParam(':fn', $firstname);
    $stmt->bindParam(':ln', $lastname);
    $stmt->bindParam(':u', $username);
    $stmt->bindParam(':p', $hashed);

    return $stmt->execute() ? true : "Failed to register admin.";
}


    // Approve / Reject applications


    // ===========================
    // Tenant Functions
    // ===========================
    public function getTenantProfile($tenant_id) {
        $stmt = $this->connect()->prepare("SELECT * FROM tenants WHERE tenant_id = :id LIMIT 1");
        $stmt->bindParam(':id', $tenant_id);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function getAllTenants() {
        $stmt = $this->connect()->prepare("SELECT * FROM tenants ORDER BY created_at DESC");
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function deleteTenant($tenant_id) {
        $stmt = $this->connect()->prepare("DELETE FROM tenants WHERE tenant_id = :id");
        $stmt->bindParam(':id', $tenant_id);
        return $stmt->execute();
    }

    public function updateTenantProfile($tenant_id, $firstname, $lastname, $username, $email, $phone, $password = '', $confirm = '') {
        if ($password !== '') {
            if ($password !== $confirm) return "Passwords do not match.";
            $hashed = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $this->connect()->prepare("
                UPDATE tenants SET firstname=:fn, lastname=:ln, username=:u, email=:e, phone=:ph, password=:p
                WHERE tenant_id=:id
            ");
            $stmt->bindParam(':p', $hashed);
        } else {
            $stmt = $this->connect()->prepare("
                UPDATE tenants SET firstname=:fn, lastname=:ln, username=:u, email=:e, phone=:ph
                WHERE tenant_id=:id
            ");
        }
        $stmt->bindParam(':fn', $firstname);
        $stmt->bindParam(':ln', $lastname);
        $stmt->bindParam(':u', $username);
        $stmt->bindParam(':e', $email);
        $stmt->bindParam(':ph', $phone);
        $stmt->bindParam(':id', $tenant_id);

        return $stmt->execute() ? true : "Failed to update profile.";
    }

    public function countTenants() {
        $stmt = $this->connect()->query("SELECT COUNT(*) as cnt FROM tenants");
        return $stmt->fetch(PDO::FETCH_ASSOC)['cnt'] ?? 0;
    }

    

    public function loginUser($input, $password, $role = 'tenant') {
    $table = ($role === 'admin') ? 'admins' : 'tenants';
    $idCol = ($role === 'admin') ? 'admin_id' : 'tenant_id';

    // Allow login by username OR email
    $stmt = $this->connect()->prepare("SELECT * FROM $table WHERE username = :input OR email = :input LIMIT 1");
    $stmt->bindParam(':input', $input);
    $stmt->execute();
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$user) return "Username or Email not found.";
    if (!password_verify($password, $user['password'])) return "Incorrect password.";

    // Set session
    if (session_status() === PHP_SESSION_NONE) session_start();
    $_SESSION['user_id'] = $user[$idCol];
    $_SESSION['fullname'] = $user['firstname'] . ' ' . $user['lastname'];
    $_SESSION['role'] = $role;

    return $role;
}

    // ===========================
    // Apartment Functions
    // ===========================
    public function addApartment($name, $type, $location, $description, $monthly_rate) {
    $sql = "INSERT INTO apartments (Name, Type, Location, Description, MonthlyRate, Status, DateAdded)
            VALUES (:name, :type, :location, :description, :monthly_rate, 'Available', NOW())";

    $stmt = $this->connect()->prepare($sql);
    $stmt->bindParam(':name', $name);
    $stmt->bindParam(':type', $type);
    $stmt->bindParam(':location', $location);
    $stmt->bindParam(':description', $description);
    $stmt->bindParam(':monthly_rate', $monthly_rate);
    $stmt->execute();

    return $this->connect()->lastInsertId(); // get the new ApartmentID
}

public function addApartmentImage($apartment_id, $image_path) {
    $sql = "INSERT INTO apartment_images (apartment_id, image_path) VALUES (:apartment_id, :image_path)";
    $stmt = $this->connect()->prepare($sql);
    $stmt->bindParam(':apartment_id', $apartment_id);
    $stmt->bindParam(':image_path', $image_path);
    $stmt->execute();
}

public function getApartmentImages($apartment_id) {
    $sql = "SELECT image_path FROM apartment_images WHERE apartment_id = :id";
    $stmt = $this->connect()->prepare($sql);
    $stmt->bindParam(':id', $apartment_id);
    $stmt->execute();
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}




    public function getAllApartments() {
        $stmt = $this->connect()->prepare("SELECT * FROM apartments ORDER BY Name ASC");
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getAvailableApartments($tenant_id) {
        $stmt = $this->connect()->prepare("
            SELECT * FROM apartments
            WHERE Status='Available' 
            AND ApartmentID NOT IN (
                SELECT apartment_id FROM applications WHERE tenant_id=:tid
                UNION
                SELECT apartment_id FROM leases WHERE tenant_id=:tid
            )
            ORDER BY Name ASC
        ");
        $stmt->bindParam(':tid', $tenant_id);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function updateApartmentStatus($apartment_id, $status) {
        $stmt = $this->connect()->prepare("UPDATE apartments SET Status = :status WHERE ApartmentID = :id");
        $stmt->bindParam(':status', $status);
        $stmt->bindParam(':id', $apartment_id);
        return $stmt->execute();
    }

    public function countApartments() {
        $stmt = $this->connect()->query("SELECT COUNT(*) as cnt FROM apartments");
        return $stmt->fetch(PDO::FETCH_ASSOC)['cnt'] ?? 0;
    }

    public function createLease($tenant_id, $apartment_id, $duration_months = 12) {
    $start_date = date('Y-m-d');
    $end_date = date('Y-m-d', strtotime("+$duration_months months"));

    $stmt = $this->connect()->prepare("
        INSERT INTO leases (tenant_id, apartment_id, start_date, end_date)
        VALUES (:tenant, :apartment, :start, :end)
    ");
    $stmt->bindParam(':tenant', $tenant_id);
    $stmt->bindParam(':apartment', $apartment_id);
    $stmt->bindParam(':start', $start_date);
    $stmt->bindParam(':end', $end_date);
    return $stmt->execute();
}


    // ===========================
    // Application Functions
    // ===========================
    public function applyApartment($tenant_id, $apartment_id) {
        $stmt = $this->connect()->prepare("SELECT * FROM applications WHERE tenant_id=:tid AND apartment_id=:aid LIMIT 1");
        $stmt->bindParam(':tid', $tenant_id);
        $stmt->bindParam(':aid', $apartment_id);
        $stmt->execute();
        if ($stmt->fetch()) return "You have already applied for this apartment.";

        $stmt = $this->connect()->prepare("INSERT INTO applications (tenant_id, apartment_id, status, date_applied) VALUES (:tid, :aid, 'Pending', NOW())");
        $stmt->bindParam(':tid', $tenant_id);
        $stmt->bindParam(':aid', $apartment_id);
        return $stmt->execute() ? true : "Failed to submit application.";
    }

    public function getAllApplications() {
        $stmt = $this->connect()->prepare("
            SELECT a.application_id, a.status as app_status, a.date_applied, 
                   t.firstname, t.lastname, t.username as tenant_username,
                   p.Name as apartment_name, p.Location, a.tenant_id, a.apartment_id
            FROM applications a
            JOIN tenants t ON a.tenant_id = t.tenant_id
            JOIN apartments p ON a.apartment_id = p.ApartmentID
            ORDER BY a.date_applied DESC
        ");
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getTenantApplications($tenant_id) {
        $stmt = $this->connect()->prepare("
            SELECT a.*, p.Name as apartment_name, p.Location
            FROM applications a
            JOIN apartments p ON a.apartment_id = p.ApartmentID
            WHERE a.tenant_id=:tid
            ORDER BY a.date_applied DESC
        ");
        $stmt->bindParam(':tid', $tenant_id);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // --- safer updateApplicationStatus with transaction ---
public function updateApplicationStatus($application_id, $status) {
    $conn = $this->connect();

    try {
        $conn->beginTransaction();

        // 1) Update application status
        $stmt = $conn->prepare("UPDATE applications SET status = :status WHERE application_id = :id");
        $stmt->bindParam(':status', $status);
        $stmt->bindParam(':id', $application_id, PDO::PARAM_INT);
        $stmt->execute();

        // 2) Get related application row
        $getApp = $conn->prepare("SELECT tenant_id, apartment_id FROM applications WHERE application_id = :id FOR UPDATE");
        $getApp->bindParam(':id', $application_id, PDO::PARAM_INT);
        $getApp->execute();
        $app = $getApp->fetch(PDO::FETCH_ASSOC);

        if (!$app) {
            // application not found -> rollback and return false
            $conn->rollBack();
            return false;
        }

        $apartment_id = (int)$app['apartment_id'];
        $tenant_id = (int)$app['tenant_id'];

        if ($status === 'Approved') {
            // Create lease (if you want default duration, use createLease method)
            $created = $this->createLease($tenant_id, $apartment_id);
            if ($created === false) {
                $conn->rollBack();
                return false;
            }

            // Mark apartment occupied
            $updated = $this->updateApartmentStatus($apartment_id, 'Occupied');
            if ($updated === false) {
                $conn->rollBack();
                return false;
            }

        } elseif ($status === 'Rejected') {
            // Ensure apartment stays available
            $this->updateApartmentStatus($apartment_id, 'Available');
        }

        $conn->commit();
        return true;

    } catch (Exception $e) {
        if ($conn->inTransaction()) $conn->rollBack();
        // For debugging, you can log $e->getMessage() or rethrow
        // error_log("updateApplicationStatus error: " . $e->getMessage());
        return false;
    }
}

// --- simple wrappers (these should not error) ---
public function approveApplication($application_id) {
    $application_id = (int)$application_id;
    return $this->updateApplicationStatus($application_id, 'Approved');
}

public function rejectApplication($application_id) {
    $application_id = (int)$application_id;
    return $this->updateApplicationStatus($application_id, 'Rejected');
}





    public function countApplications() {
        $stmt = $this->connect()->query("SELECT COUNT(*) as cnt FROM applications");
        return $stmt->fetch(PDO::FETCH_ASSOC)['cnt'] ?? 0;
    }

    // ===========================
    // Lease Functions
    // ===========================
    public function getAllLeases() {
        $stmt = $this->connect()->prepare("
            SELECT l.lease_id, l.start_date, l.end_date, 
                   t.firstname, t.lastname, t.username AS tenant_username,
                   p.Name AS apartment_name, p.Location, p.MonthlyRate
            FROM leases l
            JOIN tenants t ON l.tenant_id = t.tenant_id
            JOIN apartments p ON l.apartment_id = p.ApartmentID
            ORDER BY l.start_date DESC
        ");
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

   public function getTenantLeases($tenant_id) {
    $stmt = $this->connect()->prepare("
        SELECT l.*, p.Name as apartment_name, p.Location, p.MonthlyRate
        FROM leases l
        JOIN apartments p ON l.apartment_id = p.ApartmentID
        WHERE l.tenant_id=:tid
        ORDER BY l.start_date DESC
    ");
    $stmt->bindParam(':tid', $tenant_id);
    $stmt->execute();
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}


    public function countLeases() {
        $stmt = $this->connect()->query("SELECT COUNT(*) as cnt FROM leases");
        return $stmt->fetch(PDO::FETCH_ASSOC)['cnt'] ?? 0;
    }

    // ===========================
    // Notifications Functions
    // ===========================
    public function getNotifications($tenant_id) {
        $stmt = $this->connect()->prepare("SELECT * FROM notifications WHERE tenant_id=:tid ORDER BY created_at DESC");
        $stmt->bindParam(':tid', $tenant_id);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function markNotificationsRead($tenant_id) {
        $stmt = $this->connect()->prepare("UPDATE notifications SET status='Read' WHERE tenant_id=:tid AND status='Unread'");
        $stmt->bindParam(':tid', $tenant_id);
        return $stmt->execute();
    }

// ===========================
// Authentication Functions
// ===========================


public function registerTenant($firstname, $lastname, $username, $email, $phone, $password, $confirm) {
    if ($password !== $confirm) return "Passwords do not match.";

    // Check username/email
    $stmt = $this->connect()->prepare("SELECT * FROM tenants WHERE username=:u OR email=:e LIMIT 1");
    $stmt->bindParam(':u', $username);
    $stmt->bindParam(':e', $email);
    $stmt->execute();
    if ($stmt->fetch()) return "Username or Email already exists.";

    // Insert tenant
    $hashed = password_hash($password, PASSWORD_DEFAULT);
    $stmt = $this->connect()->prepare("
        INSERT INTO tenants (firstname, lastname, username, email, phone, password)
        VALUES (:fn, :ln, :u, :e, :ph, :p)
    ");
    $stmt->bindParam(':fn', $firstname);
    $stmt->bindParam(':ln', $lastname);
    $stmt->bindParam(':u', $username);
    $stmt->bindParam(':e', $email);
    $stmt->bindParam(':ph', $phone);
    $stmt->bindParam(':p', $hashed);

    return $stmt->execute() ? true : "Failed to register tenant.";
}

public function getTenantById($id) {
    $stmt = $this->connect()->prepare("SELECT * FROM tenants WHERE tenant_id = :id LIMIT 1");
    $stmt->bindParam(':id', $id, PDO::PARAM_INT);
    $stmt->execute();
    return $stmt->fetch(PDO::FETCH_ASSOC);
}

public function getTenantByUsername($usernameOrEmail) {
    $stmt = $this->connect()->prepare("SELECT * FROM tenants WHERE username = :u OR email = :u LIMIT 1");
    $stmt->bindParam(':u', $usernameOrEmail);
    $stmt->execute();
    return $stmt->fetch(PDO::FETCH_ASSOC);
}

 // ➤ Add Utility Bill (Admin)
    public function addUtility($tenant_id, $type, $amount, $due_date, $status = 'Unpaid') {
        $stmt = $this->connect()->prepare("
            INSERT INTO utilities (tenant_id, type, amount, due_date, status)
            VALUES (:tenant_id, :type, :amount, :due_date, :status)
        ");
        $stmt->bindParam(':tenant_id', $tenant_id);
        $stmt->bindParam(':type', $type);
        $stmt->bindParam(':amount', $amount);
        $stmt->bindParam(':due_date', $due_date);
        $stmt->bindParam(':status', $status);
        return $stmt->execute();
    }

    // ➤ Get all utilities (Admin)
    public function getAllUtilities() {
        $stmt = $this->connect()->prepare("
            SELECT u.*, t.firstname, t.lastname, t.username
            FROM utilities u
            JOIN tenants t ON u.tenant_id = t.tenant_id
            ORDER BY u.due_date DESC
        ");
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // ➤ Get utilities by tenant (Tenant)
    public function getTenantUtilities($tenant_id) {
        $stmt = $this->connect()->prepare("
            SELECT * FROM utilities 
            WHERE tenant_id = :tid 
            ORDER BY due_date DESC
        ");
        $stmt->bindParam(':tid', $tenant_id);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // ➤ Update Utility Status (Admin)
    public function updateUtilityStatus($id, $status) {
        $stmt = $this->connect()->prepare("
            UPDATE utilities 
            SET status = :status 
            WHERE id = :id
        ");
        $stmt->bindParam(':status', $status);
        $stmt->bindParam(':id', $id);
        return $stmt->execute();
    }

    // ➤ Delete a Utility (optional)
    public function deleteUtility($id) {
        $stmt = $this->connect()->prepare("DELETE FROM utilities WHERE id = :id");
        $stmt->bindParam(':id', $id);
        return $stmt->execute();
    }

    public function countUtilities() {
    $stmt = $this->connect()->query("SELECT COUNT(*) AS total FROM utilities");
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    return $row['total'] ?? 0;
}

// ---------- PAYMENTS ----------
public function addPayment($tenant_id, $amount, $payment_type = 'Rent') {
    $stmt = $this->connect()->prepare("
        INSERT INTO payments (tenant_id, amount, payment_type)
        VALUES (?, ?, ?)
    ");
    return $stmt->execute([$tenant_id, $amount, $payment_type]);
}

public function getPaymentsByTenant($tenant_id) {
    $stmt = $this->connect()->prepare("
        SELECT * FROM payments WHERE tenant_id = ? ORDER BY payment_date DESC
    ");
    $stmt->execute([$tenant_id]);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}


public function updatePaymentStatus($id, $status) {
    $stmt = $this->connect()->prepare("UPDATE payments SET status = ? WHERE id = ?");
    return $stmt->execute([$status, $id]);
}

// Update payment method and mark as paid
public function updatePayment($payment_id, $method, $reference_number = null) {
    $stmt = $this->connect()->prepare("
        UPDATE payments 
        SET status = 'Paid', 
            payment_method = :method, 
            reference_number = :ref, 
            date_paid = NOW() 
        WHERE payment_id = :pid
    ");
    $stmt->bindParam(':method', $method);
    $stmt->bindParam(':ref', $reference_number);
    $stmt->bindParam(':pid', $payment_id);
    return $stmt->execute();
}


// Automatically generate next month’s billing
public function generateMonthlyPayments() {
    $stmt = $this->connect()->prepare("
        SELECT l.lease_id, l.tenant_id, p.MonthlyRate
        FROM leases l
        JOIN apartments p ON l.apartment_id = p.ApartmentID
        WHERE CURDATE() BETWEEN l.start_date AND l.end_date
    ");
    $stmt->execute();
    $leases = $stmt->fetchAll(PDO::FETCH_ASSOC);

    foreach ($leases as $lease) {
        $lease_id = $lease['lease_id'];
        $tenant_id = $lease['tenant_id'];
        $amount = $lease['MonthlyRate'];
        $due_date = date('Y-m-t'); // end of current month

        // Check if already generated this month
        $check = $this->connect()->prepare("
            SELECT * FROM payments 
            WHERE lease_id = :lid AND MONTH(due_date) = MONTH(CURDATE()) AND YEAR(due_date) = YEAR(CURDATE())
        ");
        $check->bindParam(':lid', $lease_id);
        $check->execute();
        if (!$check->fetch()) {
            $insert = $this->connect()->prepare("
                INSERT INTO payments (tenant_id, lease_id, amount, due_date, status)
                VALUES (:tid, :lid, :amt, :due, 'Unpaid')
            ");
            $insert->bindParam(':tid', $tenant_id);
            $insert->bindParam(':lid', $lease_id);
            $insert->bindParam(':amt', $amount);
            $insert->bindParam(':due', $due_date);
            $insert->execute();
        }
    }
}

// Get all payments for admin view
public function getAllPayments() {
    $stmt = $this->connect()->prepare("
        SELECT p.*, t.firstname, t.lastname
        FROM payments p
        JOIN tenants t ON p.tenant_id = t.tenant_id
        ORDER BY p.due_date DESC
    ");
    $stmt->execute();
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

// Get tenant payments
public function getTenantPayments($tenant_id) {
    $stmt = $this->connect()->prepare("
        SELECT p.*, a.Name as apartment_name
        FROM payments p
        JOIN leases l ON p.lease_id = l.lease_id
        JOIN apartments a ON l.apartment_id = a.ApartmentID
        WHERE p.tenant_id = :tid
        ORDER BY p.due_date DESC
    ");
    $stmt->bindParam(':tid', $tenant_id);
    $stmt->execute();
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

// Mark payment as paid
public function markPaymentPaid($payment_id) {
    $stmt = $this->connect()->prepare("
        UPDATE payments 
        SET status = 'Paid', date_paid = NOW() 
        WHERE payment_id = :pid
    ");
    $stmt->bindParam(':pid', $payment_id);
    return $stmt->execute();
}



public function logout() {
    if (session_status() === PHP_SESSION_NONE) session_start();
    session_unset();
    session_destroy();
}

}
?>
