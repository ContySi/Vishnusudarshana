<?php
// appointment-process.php
// Complete handler for appointment form: POST-only, validate, insert, PRG redirect

session_start();
require_once __DIR__ . '/config/db.php';

function redirect_to(string $url): void {
	header('Location: ' . $url);
	exit;
}

// 1) Accept POST request only; block direct GET access
if (($_SERVER['REQUEST_METHOD'] ?? '') !== 'POST') {
	redirect_to('service-detail.php?service=book-appointment');
}

// 2) Gather and normalize inputs (accept synonyms for compatibility)
$serviceId       = $_POST['service_id'] ?? $_POST['serviceId'] ?? null;
$appointmentType = strtolower(trim($_POST['appointment_type'] ?? ''));
$preferredDate   = trim($_POST['preferred_date'] ?? '');
$preferredTime   = trim($_POST['preferred_time'] ?? '');
$name            = trim($_POST['name'] ?? ($_POST['full_name'] ?? ''));
$phone           = trim($_POST['phone'] ?? ($_POST['mobile'] ?? ''));
$email           = trim($_POST['email'] ?? '');
$notes           = trim($_POST['notes'] ?? '');
$productIds      = $_POST['product_ids'] ?? [];
$quantities      = $_POST['qty'] ?? [];

// 2b) Validate required fields
$errors = [];
if (!($serviceId !== null && $serviceId !== '' && ctype_digit((string)$serviceId))) {
	$errors[] = 'service_id is required';
}
if (!in_array($appointmentType, ['online', 'offline'], true)) {
	$errors[] = 'appointment_type must be online or offline';
}
if ($preferredDate === '') {
	$errors[] = 'preferred_date is required';
}
if ($preferredTime === '') {
	$errors[] = 'preferred_time is required';
}
if ($name === '') {
	$errors[] = 'name is required';
}
if ($phone === '') {
	$errors[] = 'phone is required';
}
if ($email !== '' && !filter_var($email, FILTER_VALIDATE_EMAIL)) {
	$errors[] = 'email is invalid';
}
if (empty($productIds) || !is_array($productIds)) {
	$errors[] = 'Please select at least one service';
}

if (!empty($errors)) {
	$_SESSION['appointment_errors'] = $errors;
	$_SESSION['appointment_old'] = [
		'service_id' => $serviceId,
		'appointment_type' => $appointmentType,
		'preferred_date' => $preferredDate,
		'preferred_time' => $preferredTime,
		'name' => $name,
		'phone' => $phone,
		'email' => $email,
		'notes' => $notes,
	];
	redirect_to('service-detail.php?service=book-appointment');
}

// 3) Ensure table exists (idempotent); includes service_id column
$pdo->exec("CREATE TABLE IF NOT EXISTS appointments (
	id INT AUTO_INCREMENT PRIMARY KEY,
	service_id INT NULL,
	customer_name VARCHAR(255) NOT NULL,
	mobile VARCHAR(20) NOT NULL,
	email VARCHAR(255) NULL,
	appointment_type VARCHAR(20) NOT NULL,
	preferred_date DATE NOT NULL,
	preferred_time_slot VARCHAR(100) NOT NULL,
	notes TEXT NULL,
	status VARCHAR(20) NOT NULL DEFAULT 'pending',
	payment_status VARCHAR(20) NOT NULL DEFAULT 'unpaid',
	transaction_ref VARCHAR(100) NULL,
	service_date DATE NULL,
	time_from TIME NULL,
	time_to TIME NULL,
	created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
	accepted_at DATETIME NULL,
	completed_at DATETIME NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;");

// Helper: detect if a column exists (for backward compatibility)
function table_has_column(PDO $pdo, string $table, string $column): bool {
	try {
		$stmt = $pdo->prepare("SHOW COLUMNS FROM `{$table}` LIKE :col");
		$stmt->execute([':col' => $column]);
		return (bool)$stmt->fetch();
	} catch (Throwable $e) {
		return false;
	}
}

$hasServiceId = table_has_column($pdo, 'appointments', 'service_id');

// 4) Prepare values and insert with prepared statements
$paymentStatus = ($appointmentType === 'online') ? 'paid' : 'unpaid';

try {
	if ($hasServiceId) {
		$stmt = $pdo->prepare("INSERT INTO appointments 
			(service_id, customer_name, mobile, email, appointment_type, preferred_date, preferred_time_slot, notes, status, payment_status)
			VALUES (:service_id, :name, :mobile, :email, :type, :pdate, :ptime, :notes, 'pending', :pstatus)");
		$stmt->execute([
			':service_id' => (int)$serviceId,
			':name'       => $name,
			':mobile'     => $phone,
			':email'      => $email ?: null,
			':type'       => $appointmentType,
			':pdate'      => $preferredDate,
			':ptime'      => $preferredTime,
			':notes'      => $notes ?: null,
			':pstatus'    => $paymentStatus,
		]);
	} else {
		$stmt = $pdo->prepare("INSERT INTO appointments 
			(customer_name, mobile, email, appointment_type, preferred_date, preferred_time_slot, notes, status, payment_status)
			VALUES (:name, :mobile, :email, :type, :pdate, :ptime, :notes, 'pending', :pstatus)");
		$stmt->execute([
			':name'    => $name,
			':mobile'  => $phone,
			':email'   => $email ?: null,
			':type'    => $appointmentType,
			':pdate'   => $preferredDate,
			':ptime'   => $preferredTime,
			':notes'   => $notes ?: null,
			':pstatus' => $paymentStatus,
		]);
	}

	$appointmentId = (int)$pdo->lastInsertId();

	// 5) Store product selection in session for payment flow
	if (!empty($productIds) && is_array($productIds)) {
		$_SESSION['appointment_products'] = [
			'product_ids' => $productIds,
			'quantities' => $quantities,
		];
	}

	// 6) Redirect (PRG) based on appointment_type
	if ($appointmentType === 'online') {
		redirect_to('payment-init.php?source=appointment&appointment_id=' . urlencode((string)$appointmentId));
	} else {
		redirect_to('service-detail.php?service=book-appointment&submitted=1&appointment_id=' . urlencode((string)$appointmentId));
	}
} catch (Throwable $e) {
	error_log('Appointment insert failed: ' . $e->getMessage());
	$_SESSION['appointment_errors'] = ['We could not process your request at the moment. Please try again.'];
	$_SESSION['appointment_old'] = [
		'service_id' => $serviceId,
		'appointment_type' => $appointmentType,
		'preferred_date' => $preferredDate,
		'preferred_time' => $preferredTime,
		'name' => $name,
		'phone' => $phone,
		'email' => $email,
		'notes' => $notes,
	];
	redirect_to('service-detail.php?service=book-appointment');
}

