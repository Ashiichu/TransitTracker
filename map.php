<?php include 'includes/header.php'; ?>
<style>
    /* 100% Original CSS Restored */
    body { overflow: hidden; height: 100vh; }
    #map-container { position: absolute; top: 0; left: 0; width: 100%; height: 100%; z-index: 1; }
    .back-btn { position: absolute; top: 1rem; left: 1rem; background: white; padding: 0.75rem 1rem; border-radius: 12px; font-weight: bold; cursor: pointer; z-index: 500; box-shadow: 0 4px 10px rgba(0,0,0,0.1); text-decoration: none; color: black; }
    
    .leaflet-div-icon { background: transparent; border: none; }
    .vehicle-marker { position: absolute; transform: translate(-50%, -50%); width: 40px; height: 40px; background-color: white; border: 3px solid; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 18px; box-shadow: 0 4px 6px rgba(0,0,0,0.2); transition: transform 0.2s; }
    .vehicle-marker.taxi { border-color: var(--primary); }
    .vehicle-marker.active { transform: translate(-50%, -50%) scale(1.4); z-index: 1000 !important; background-color: #fefce8; box-shadow: 0 0 15px rgba(250, 204, 21, 0.5);}
    .capacity-dot { position: absolute; top: -4px; right: -4px; width: 14px; height: 14px; border-radius: 50%; border: 2px solid white; background-color: var(--success); }

    .bottom-sheet { position: absolute; bottom: 0; left: 0; width: 100%; height: 68vh; background: var(--bg-color); border-top-left-radius: 24px; border-top-right-radius: 24px; box-shadow: 0 -10px 25px rgba(0,0,0,0.1); z-index: 1000; display: flex; flex-direction: column; transition: transform 0.3s cubic-bezier(0.4, 0, 0.2, 1); }
    .bottom-sheet.collapsed { transform: translateY(calc(100% - 60px)); }
    .drag-area { width: 100%; height: 60px; display: flex; justify-content: center; align-items: center; cursor: grab; background: var(--panel-bg); border-top-left-radius: 24px; border-top-right-radius: 24px; flex-shrink: 0; border-bottom: 1px solid var(--border-color);}
    .drag-handle { width: 50px; height: 6px; background: #cbd5e1; border-radius: 3px; }

    .sheet-content { display: flex; flex-direction: column; height: calc(100% - 60px); }
    .selected-vehicle-area { padding: 1.5rem; background: var(--panel-bg); flex-shrink: 0; box-shadow: 0 4px 6px rgba(0,0,0,0.02); z-index: 2; }
    .v-name { font-size: 1.3rem; font-weight: 900; }
    .v-type-badge { display: inline-block; padding: 0.2rem 0.5rem; border-radius: 4px; font-size: 0.75rem; font-weight: bold; color: white; margin-top: 0.25rem; }
    .badge-taxi { background-color: var(--primary); color: black; }
    
    .stats-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 1rem; margin-top: 1rem; }
    .stat-card { background: var(--bg-color); padding: 0.5rem; border-radius: 8px; text-align: center; border: 1px solid var(--border-color);}
    .warning-text { font-size: 0.75rem; color: var(--danger); text-align: center; margin-top: 0.75rem; font-weight: bold; line-height: 1.3; }
    .btn-loading { background-color: #e5e7eb; color: var(--text-muted); cursor: wait; }

    /* Restored Star Rating UI CSS */
    .rating-section { margin-top: 1.25rem; padding-top: 1.25rem; border-top: 1px solid var(--border-color); text-align: center; transition: all 0.4s; }
    .rating-section.disabled { filter: grayscale(100%); opacity: 0.4; pointer-events: none; }
    .rating-section.newly-active { animation: popIn 0.5s cubic-bezier(0.175, 0.885, 0.32, 1.275) forwards; }
    .rating-title { font-size: 0.85rem; font-weight: bold; color: var(--text-main); margin-bottom: 0.25rem; }
    .stars-container { display: flex; justify-content: center; gap: 0.25rem; cursor: pointer; }
    .star-icon { font-size: 2rem; color: #d1d5db; transition: color 0.2s, transform 0.1s; }
    .star-icon.hovered, .star-icon.selected { color: var(--warning); }
    .star-icon:active { transform: scale(0.8); }
    .rating-thanks { color: var(--success); font-weight: bold; font-size: 0.85rem; margin-top: 0.5rem; display: none; }
    @keyframes popIn { 0% { transform: scale(0.9); opacity: 0.4; filter: grayscale(100%); } 50% { transform: scale(1.05); opacity: 1; filter: grayscale(0%); } 100% { transform: scale(1); opacity: 1; filter: grayscale(0%); } }
</style>
<body>

    <a href="routes.php" class="back-btn">⬅ Back</a>
    <div id="map-container"></div>

    <div class="bottom-sheet collapsed" id="bottomSheet">
        <div class="drag-area" onclick="document.getElementById('bottomSheet').classList.toggle('collapsed')"><div class="drag-handle"></div></div>
        <div class="sheet-content">
            <div class="selected-vehicle-area" id="selected-vehicle-container">
                <div style="text-align:center; color: var(--text-muted); padding: 1rem 0;">Select a vehicle on the map</div>
            </div>
        </div>
    </div>

    <div class="modal-overlay" id="cancelModal">
        <div class="modal-card">
            <h3 style="color: var(--danger); margin-bottom: 0.5rem;">⚠️ Cancel Reservation?</h3>
            <p style="color: var(--text-muted); font-size: 0.95rem;">A <strong>10% cancellation fee</strong> will be deducted.</p>
            <div class="modal-actions-col">
                <button class="btn btn-danger" onclick="confirmPassengerCancellation()">Yes, Cancel & Pay Fee</button>
                <button class="btn btn-outline" onclick="document.getElementById('cancelModal').classList.remove('active')">Keep My Seat</button>
            </div>
        </div>
    </div>

    <div class="modal-overlay" id="rejectionModal">
        <div class="modal-card">
            <h3 style="color: var(--warning); margin-bottom: 0.5rem;">Request Declined</h3>
            <p style="color: var(--text-muted); font-size: 0.95rem;">The driver is unable to accept your request at this time.</p>
            <div class="modal-actions-col">
                <button class="btn btn-primary" onclick="document.getElementById('rejectionModal').classList.remove('active')">Understood</button>
            </div>
        </div>
    </div>

    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
    <script>
        let map;
        let driverMarkers = {}; 
        let currentReservationId = null;
        let selectedDriverId = null;
        let pollInterval;

        document.addEventListener('DOMContentLoaded', () => {
            map = L.map('map-container', { zoomControl: false }).setView([18.0128, -76.7989], 13);
            L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', { maxZoom: 18 }).addTo(map);
            const routeLine = L.polyline([[17.9548, -76.8839], [17.9700, -76.8500], [17.9900, -76.8200], [18.0128, -76.7989]], { color: '#facc15', weight: 6, dashArray: '10, 10' }).addTo(map);
            
            fetchLiveDrivers();
            setInterval(fetchLiveDrivers, 5000); 
            setInterval(simulateMovement, 1000);
        });

        async function fetchLiveDrivers() {
            const formData = new FormData();
            formData.append('action', 'get_drivers');
            const res = await fetch('api.php', { method: 'POST', body: formData });
            const data = await res.json();

            if (data.success) {
                data.drivers.forEach(driver => {
                    if (!driverMarkers[driver.driver_id]) {
                        let marker = L.marker([driver.current_lat, driver.current_lng]).addTo(map);
                        marker.setIcon(L.divIcon({ className: '', html: `<div class="vehicle-marker taxi">🚕</div>`, iconSize: [40,40], iconAnchor: [20, 20] }));
                        marker.on('click', () => selectDriver(driver));
                        
                        marker.baseLat = parseFloat(driver.current_lat);
                        marker.baseLng = parseFloat(driver.current_lng);
                        driverMarkers[driver.driver_id] = marker;
                    }
                });
            }
        }

        function simulateMovement() {
            Object.values(driverMarkers).forEach(marker => {
                let latOffset = (Math.random() - 0.5) * 0.0005;
                let lngOffset = (Math.random() - 0.5) * 0.0005;
                marker.setLatLng([marker.baseLat + latOffset, marker.baseLng + lngOffset]);
            });
        }

        function selectDriver(driver) {
            selectedDriverId = driver.driver_id;
            document.getElementById('bottomSheet').classList.remove('collapsed');
            const container = document.getElementById('selected-vehicle-container');
            
            let btnHTML = `<button class="btn btn-primary" style="margin-top: 1rem;" onclick="requestSeat(${driver.driver_id})">Request Seat ($150)</button>`;
            let warningHTML = `<div class="warning-text">⚠️ Warning: Taxi/bus may not have a seat once it arrives at your location if no reservation is done.</div>`;
            let ratingClass = 'rating-section disabled';

            if (currentReservationId === "loading") {
                btnHTML = `<button class="btn btn-loading" style="margin-top: 1rem;" disabled>Awaiting Confirmation... ⏳</button>`;
                warningHTML = '';
            } else if (currentReservationId) {
                btnHTML = `<button class="btn btn-danger" style="margin-top: 1rem;" onclick="document.getElementById('cancelModal').classList.add('active')">Cancel Reservation</button>`;
                warningHTML = '';
                ratingClass = 'rating-section newly-active'; // Unlock rating system when ride is accepted
            }

            container.innerHTML = `
                <div style="display:flex; justify-content:space-between; align-items:center;">
                    <div class="v-name">${driver.display_name}</div>
                    <div class="v-type-badge badge-taxi">Route Taxi</div>
                </div>
                <div class="stats-grid">
                    <div class="stat-card"><div style="font-size:0.75rem; color:var(--text-muted); font-weight: bold;">ETA</div><div style="font-size: 1.1rem; font-weight: bold;">2 mins</div></div>
                    <div class="stat-card"><div style="font-size:0.75rem; color:var(--text-muted); font-weight: bold;">CAPACITY</div><div style="font-size: 1.1rem; font-weight: bold; color: var(--success);">${driver.current_capacity}/${driver.max_capacity}</div></div>
                </div>
                ${btnHTML}
                ${warningHTML}
                
                <div class="${ratingClass}" id="rating-panel">
                    <div class="rating-title">How was your driver?</div>
                    <div class="stars-container">
                        <span class="star-icon">★</span><span class="star-icon">★</span><span class="star-icon">★</span><span class="star-icon">★</span><span class="star-icon">★</span>
                    </div>
                </div>
            `;
        }

        async function requestSeat(driverId) {
            currentReservationId = "loading";
            selectDriver({driver_id: driverId, display_name: "Driver 45", current_capacity: 1, max_capacity: 4}); 

            const formData = new FormData();
            formData.append('action', 'request_seat');
            formData.append('driver_id', driverId);

            const res = await fetch('api.php', { method: 'POST', body: formData });
            const data = await res.json();

            if (data.success) {
                currentReservationId = data.reservation_id;
                pollInterval = setInterval(() => checkStatus(driverId), 3000); 
            }
        }

        async function checkStatus(driverId) {
            const formData = new FormData();
            formData.append('action', 'check_passenger_status');
            formData.append('reservation_id', currentReservationId);
            
            const res = await fetch('api.php', { method: 'POST', body: formData });
            const data = await res.json();

            if (data.status === 'accepted') {
                clearInterval(pollInterval);
                selectDriver({driver_id: driverId, display_name: "Driver 45", current_capacity: 2, max_capacity: 4}); 
            } else if (data.status === 'rejected') {
                clearInterval(pollInterval);
                currentReservationId = null;
                document.getElementById('rejectionModal').classList.add('active');
                selectDriver({driver_id: driverId, display_name: "Driver 45", current_capacity: 1, max_capacity: 4});
            }
        }

        // Fix: Passenger cancellation now accurately pings the database
        async function confirmPassengerCancellation() {
            if (!currentReservationId) return;

            const formData = new FormData();
            formData.append('action', 'cancel_reservation');
            formData.append('reservation_id', currentReservationId);
            formData.append('driver_id', selectedDriverId);

            await fetch('api.php', { method: 'POST', body: formData });
            
            currentReservationId = null; 
            document.getElementById('cancelModal').classList.remove('active');
            
            // Re-render UI to default state
            selectDriver({driver_id: selectedDriverId, display_name: "Driver 45", current_capacity: 1, max_capacity: 4});
        }
    </script>
</body></html>