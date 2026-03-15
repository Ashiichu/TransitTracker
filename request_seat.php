<?php
// request_seat.php
require 'db.php';
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Hardcoded passenger ID for testing the prototype
    $passenger_id = 1; 
    
    $driver_id = $_POST['driver_id'] ?? null;
    $route_id = $_POST['route_id'] ?? 'r1';
    $fare = $_POST['fare'] ?? 150.00;
    $pickup_lat = 18.0128; // Mocked GPS coordinates
    $pickup_lng = -76.7989;

    if (!$driver_id) {
        echo json_encode(['success' => false, 'message' => 'Invalid driver.']);
        exit;
    }

    // Simulate network/driver delay (3 seconds) to mimic real-world waiting
    sleep(3);

    // Probability Engine: 60% chance the driver accepts the ride
    $driver_accepts = (rand(1, 100) <= 60);

    if ($driver_accepts) {
        try {
            $conn->begin_transaction();

            // 1. Check if driver has capacity
            $stmt = $conn->prepare("SELECT current_capacity, max_capacity FROM driver_profiles WHERE driver_id = ? FOR UPDATE");
            $stmt->bind_param("i", $driver_id);
            $stmt->execute();
            $driver_data = $stmt->get_result()->fetch_assoc();
            $stmt->close();

            if ($driver_data['current_capacity'] >= $driver_data['max_capacity']) {
                throw new Exception("Vehicle is currently full.");
            }

            // 2. Create the reservation record
            $insert_res = $conn->prepare("INSERT INTO reservations (passenger_id, driver_id, route_id, pickup_lat, pickup_lng, fare, status) VALUES (?, ?, ?, ?, ?, ?, 'accepted')");
            $insert_res->bind_param("iisddd", $passenger_id, $driver_id, $route_id, $pickup_lat, $pickup_lng, $fare);
            $insert_res->execute();
            $reservation_id = $conn->insert_id;
            $insert_res->close();

            // 3. Update Driver Capacity
            $update_cap = $conn->prepare("UPDATE driver_profiles SET current_capacity = current_capacity + 1 WHERE driver_id = ?");
            $update_cap->bind_param("i", $driver_id);
            $update_cap->execute();
            $update_cap->close();

            $conn->commit();
            
            echo json_encode(['success' => true, 'reservation_id' => $reservation_id]);

        } catch (Exception $e) {
            $conn->rollback();
            echo json_encode(['success' => false, 'message' => $e->getMessage()]);
        }
    } else {
        // Driver rejected the request
        echo json_encode(['success' => false, 'message' => 'Driver declined the request.']);
    }
}
?>