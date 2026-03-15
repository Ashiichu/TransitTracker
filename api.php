<?php
// api.php
require 'db.php';
header('Content-Type: application/json');

$action = $_POST['action'] ?? '';

switch ($action) {
    // 1. Fetch Online Drivers for the Map
    case 'get_drivers':
        $stmt = $conn->prepare("SELECT driver_id, display_name, vehicle_description, current_capacity, max_capacity, current_lat, current_lng FROM driver_profiles WHERE is_online = 1");
        $stmt->execute();
        $drivers = [];
        $res = $stmt->get_result();
        while ($row = $res->fetch_assoc()) $drivers[] = $row;
        echo json_encode(['success' => true, 'drivers' => $drivers]);
        break;

    // 2. Passenger Requests a Seat
    case 'request_seat':
        $driver_id = $_POST['driver_id'];
        $passenger_id = 1; // Hardcoded for prototype
        $fare = $_POST['fare'] ?? 150.00;
        
        $stmt = $conn->prepare("INSERT INTO reservations (passenger_id, driver_id, route_id, pickup_lat, pickup_lng, fare, status) VALUES (?, ?, 'r1', 18.0128, -76.7989, ?, 'pending')");
        $stmt->bind_param("iid", $passenger_id, $driver_id, $fare);
        $stmt->execute();
        echo json_encode(['success' => true, 'reservation_id' => $conn->insert_id]);
        break;

    // 3. Passenger Polls for Driver Response
    case 'check_passenger_status':
        $res_id = $_POST['reservation_id'];
        $stmt = $conn->prepare("SELECT status FROM reservations WHERE id = ?");
        $stmt->bind_param("i", $res_id);
        $stmt->execute();
        $status = $stmt->get_result()->fetch_assoc()['status'] ?? null;
        echo json_encode(['success' => true, 'status' => $status]);
        break;

    // 4. Passenger Cancels Reservation
    case 'cancel_reservation':
        $res_id = $_POST['reservation_id'];
        $driver_id = $_POST['driver_id'];
        
        $conn->begin_transaction();
        try {
            $conn->query("UPDATE reservations SET status = 'cancelled_by_passenger' WHERE id = $res_id");
            $conn->query("UPDATE driver_profiles SET current_capacity = GREATEST(current_capacity - 1, 0) WHERE driver_id = $driver_id");
            $conn->commit();
            echo json_encode(['success' => true]);
        } catch (Exception $e) {
            $conn->rollback();
            echo json_encode(['success' => false]);
        }
        break;

    // 5. Driver Polls for Pending Requests
    case 'check_driver_requests':
        $driver_id = 45; // Hardcoded for prototype
        $stmt = $conn->prepare("SELECT id, fare, pickup_lat, pickup_lng FROM reservations WHERE driver_id = ? AND status = 'pending' LIMIT 1");
        $stmt->bind_param("i", $driver_id);
        $stmt->execute();
        $request = $stmt->get_result()->fetch_assoc();
        echo json_encode(['success' => true, 'has_request' => !!$request, 'request_data' => $request]);
        break;

    // 6. Driver Accepts/Declines
    case 'driver_respond':
        $res_id = $_POST['reservation_id'];
        $response_status = $_POST['response_status']; 
        $driver_id = 45;

        $conn->begin_transaction();
        try {
            $stmt = $conn->prepare("UPDATE reservations SET status = ? WHERE id = ?");
            $stmt->bind_param("si", $response_status, $res_id);
            $stmt->execute();

            if ($response_status === 'accepted') {
                $conn->query("UPDATE driver_profiles SET current_capacity = current_capacity + 1 WHERE driver_id = $driver_id");
            }
            $conn->commit();
            echo json_encode(['success' => true]);
        } catch (Exception $e) {
            $conn->rollback();
            echo json_encode(['success' => false]);
        }
        break;

    // 7. Driver Completes Drop-off (With Penalty Logic)
    case 'complete_dropoff':
        $driver_id = 45;
        $res_id = $_POST['reservation_id'];

        $conn->begin_transaction();
        try {
            $stmt = $conn->prepare("SELECT d.has_penalty, r.fare FROM driver_profiles d JOIN reservations r ON d.driver_id = r.driver_id WHERE d.driver_id = ? AND r.id = ? FOR UPDATE");
            $stmt->bind_param("ii", $driver_id, $res_id);
            $stmt->execute();
            $data = $stmt->get_result()->fetch_assoc();

            if (!$data) throw new Exception("Valid active reservation not found.");

            $fare = (float)$data['fare'];
            $has_penalty = (bool)$data['has_penalty'];
            $deduction = $has_penalty ? ($fare * 0.10) : 0.00;
            $final_earnings = $fare - $deduction;

            $conn->query("UPDATE reservations SET status = 'completed', completed_at = NOW() WHERE id = $res_id");
            $conn->query("UPDATE driver_profiles SET total_earnings = total_earnings + $final_earnings, has_penalty = FALSE, current_capacity = GREATEST(current_capacity - 1, 0) WHERE driver_id = $driver_id");
            $conn->commit();

            echo json_encode(['success' => true, 'final_earnings' => number_format($final_earnings, 2), 'message' => $has_penalty ? "10% penalty (\${$deduction}) applied. Earned \${$final_earnings}." : "Successfully dropped off passenger. Earned \${$final_earnings}."]);
        } catch (Exception $e) {
            $conn->rollback();
            echo json_encode(['success' => false, 'message' => $e->getMessage()]);
        }
        break;
}
?>