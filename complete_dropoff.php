<?php
// complete_dropoff.php
require 'db.php'; 
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $driver_id = $_POST['driver_id'] ?? null;
    $reservation_id = $_POST['reservation_id'] ?? null;

    if (!$driver_id || !$reservation_id) { echo json_encode(['success' => false, 'message' => 'Missing data.']); exit; }

    try {
        $conn->begin_transaction();

        $stmt = $conn->prepare("SELECT d.has_penalty, r.fare FROM driver_profiles d JOIN reservations r ON d.driver_id = r.driver_id WHERE d.driver_id = ? AND r.id = ? FOR UPDATE");
        $stmt->bind_param("ii", $driver_id, $reservation_id);
        $stmt->execute();
        $data = $stmt->get_result()->fetch_assoc();
        $stmt->close();

        if (!$data) { throw new Exception("Valid reservation not found."); }

        $fare = (float)$data['fare'];
        $has_penalty = (bool)$data['has_penalty'];
        $deduction = $has_penalty ? ($fare * 0.10) : 0.00;
        $final_earnings = $fare - $deduction;

        $update_res = $conn->prepare("UPDATE reservations SET status = 'completed', completed_at = NOW() WHERE id = ?");
        $update_res->bind_param("i", $reservation_id);
        $update_res->execute();
        $update_res->close();

        $update_driver = $conn->prepare("UPDATE driver_profiles SET total_earnings = total_earnings + ?, has_penalty = FALSE, current_capacity = GREATEST(current_capacity - 1, 0) WHERE driver_id = ?");
        $update_driver->bind_param("di", $final_earnings, $driver_id);
        $update_driver->execute();
        $update_driver->close();

        $conn->commit();

        echo json_encode([
            'success' => true,
            'final_earnings' => number_format($final_earnings, 2),
            'message' => $has_penalty ? "10% penalty (\${$deduction}) applied. Earned \${$final_earnings}." : "Successfully dropped off. Earned \${$final_earnings}."
        ]);

    } catch (Exception $e) {
        $conn->rollback();
        echo json_encode(['success' => false, 'message' => $e->getMessage()]);
    }
}
?>