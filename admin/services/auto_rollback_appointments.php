<?php
// admin/services/auto_rollback_appointments.php
// Auto-rollback accepted appointments not completed on assigned date
// Safe to run multiple times, no UI output, logs via error_log()

require_once __DIR__ . '/../../config/db.php';

try {
    $pdo->beginTransaction();
    $sql = "
        UPDATE service_requests
        SET service_status = 'Received',
            form_data = JSON_SET(
                form_data,
                '$.assigned_date', NULL,
                '$.assigned_from_time', NULL,
                '$.assigned_to_time', NULL
            ),
            updated_at = NOW()
        WHERE category_slug = 'appointment'
          AND payment_status = 'Paid'
          AND service_status = 'Accepted'
          AND COALESCE(JSON_UNQUOTE(JSON_EXTRACT(form_data,'$.assigned_date')), '') <> ''
          AND JSON_UNQUOTE(JSON_EXTRACT(form_data,'$.assigned_date')) < CURDATE()
          AND service_status != 'Completed'
    ";
    $stmt = $pdo->prepare($sql);
    $stmt->execute();
    $count = $stmt->rowCount();
    $pdo->commit();
    error_log("Auto-rollback executed: $count records reverted");
} catch (Throwable $e) {
    $pdo->rollBack();
    error_log('Auto-rollback failed: ' . $e->getMessage());
}
