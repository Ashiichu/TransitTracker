<?php include 'includes/header.php'; ?>
<style>
    /* CRITICAL FIX: The body must be a flex container for the map to calculate its 45vh height properly */
    body { display: flex; flex-direction: column; min-height: 100vh; margin: 0; overflow-x: hidden; }

    .driver-header { background: var(--panel-bg); padding: 1rem; display: flex; justify-content: space-between; align-items: center; box-shadow: 0 2px 10px rgba(0,0,0,0.05); z-index: 500; position: sticky; top: 0; }
    .switch-container { display: flex; flex-direction: column; align-items: flex-end; gap: 0.2rem; }
    .switch { position: relative; display: inline-block; width: 44px; height: 24px; }
    .switch input { opacity: 0; width: 0; height: 0; }
    .slider { position: absolute; cursor: pointer; top: 0; left: 0; right: 0; bottom: 0; background-color: #ccc; transition: .4s; border-radius: 34px; }
    .slider:before { position: absolute; content: ""; height: 16px; width: 16px; left: 4px; bottom: 4px; background-color: white; transition: .4s; border-radius: 50%; }
    input:checked + .slider { background-color: var(--success); }
    input:checked + .slider:before { transform: translateX(20px); }
    .duty-text { font-size: 0.75rem; font-weight: bold; text-transform: uppercase; letter-spacing: 0.5px; }

    .map-wrapper { position: relative; width: 100%; flex: 0 0 45vh; min-height: 300px; border-bottom: 2px solid var(--border-color); }
    #driver-map { width: 100%; height: 100%; z-index: 1; }
    
    .offline-overlay { position: absolute; top: 0; left: 0; width: 100%; height: 100%; background: rgba(31, 41, 55, 0.85); z-index: 400; display: flex; flex-direction: column; justify-content: center; align-items: center; color: white; text-align: center; backdrop-filter: blur(4px); transition: opacity 0.3s; pointer-events: none; opacity: 0; }
    .offline-overlay.active { opacity: 1; pointer-events: auto; }

    .dashboard-content { padding: 1.5rem; display: flex; flex-direction: column; gap: 1.5rem; flex-grow: 1; }
    .passenger-view-card { background: var(--panel-bg); padding: 1.5rem; border-radius: 12px; box-shadow: 0 2px 5px rgba(0,0,0,0.02); border: 1px solid var(--border-color); border-left: 4px solid var(--primary); }
    .pv-title { font-size: 0.75rem; color: var(--text-muted); text-transform: uppercase; font-weight: bold; margin-bottom: 0.75rem; }
    .pv-header { display: flex; justify-content: space-between; align-items: flex-start; }
    .v-name { font-size: 1.3rem; font-weight: 900; display: flex; align-items: center; gap: 0.3rem; }
    .verified-icon { color: #3b82f6; font-size: 1rem; }
    .v-type-badge { display: inline-block; padding: 0.2rem 0.5rem; border-radius: 4px; font-size: 0.75rem; font-weight: bold; color: white; margin-top: 0.25rem; }
    .badge-taxi { background-color: var(--primary); color: black; }
    .rating-badge { background: #fefce8; color: var(--warning); font-weight: bold; padding: 0.25rem 0.5rem; border-radius: 4px; border: 1px solid #fde047; font-size: 0.9rem; }

    .stats-row { display: grid; grid-template-columns: 1fr 1fr; gap: 1rem; }
    .stat-box { background: var(--panel-bg); padding: 1rem; border-radius: 12px; box-shadow: 0 2px 5px rgba(0,0,0,0.02); border: 1px solid var(--border-color); text-align: center; }
    .stat-label { font-size: 0.8rem; color: var(--text-muted); text-transform: uppercase; font-weight: bold; margin-bottom: 0.25rem; }
    .stat-value { font-size: 1.5rem; font-weight: 900; color: var(--text-main); }
    .stat-value.money { color: var(--success); }

    .driver-controls { background: var(--panel-bg); padding: 1.5rem; border-radius: 12px; box-shadow: 0 2px 5px rgba(0,0,0,0.02); border: 1px solid var(--border-color); text-align: center; }
    .control-title { font-size: 0.9rem; color: var(--text-main); font-weight: bold; margin-bottom: 1rem; }
    .capacity-manager { display: flex; align-items: center; justify-content: center; gap: 1.5rem; }
    .cap-btn { width: 50px; height: 50px; border-radius: 50%; border: none; font-size: 1.5rem; font-weight: bold; cursor: pointer; display: flex; align-items: center; justify-content: center; box-shadow: 0 4px 6px rgba(0,0,0,0.1); transition: transform 0.1s; }
    .cap-btn:active { transform: scale(0.95); }
    .cap-minus { background: #fee2e2; color: var(--danger); }
    .cap-plus { background: #dcfce7; color: var(--success); }
    .cap-display { font-size: 2rem; font-weight: 900; width: 100px; }
    .cap-sub { display: block; font-size: 0.8rem; font-weight: normal; color: var(--text-muted); }

    .pickup-list { display: flex; flex-direction: column; gap: 0.75rem; }
    .pickup-card { background: var(--panel-bg); padding: 1rem; border-radius: 12px; border: 1px solid var(--border-color); border-left: 4px solid var(--primary); display: flex; flex-direction: column; gap: 0.75rem; }
    .pickup-header { display: flex; justify-content: space-between; font-weight: bold; }
    .pickup-actions { display: grid; grid-template-columns: 1fr 1fr; gap: 0.5rem; }

    .toast-notification { position: fixed; top: -100vh; left: 50%; transform: translateX(-50%); width: 90%; max-width: 400px; background: var(--panel-bg); border-radius: 16px; box-shadow: 0 10px 30px rgba(0,0,0,0.2); z-index: 2000; transition: top 0.5s cubic-bezier(0.175, 0.885, 0.32, 1.275), opacity 0.3s ease; border: 2px solid var(--primary); overflow: hidden; opacity: 0; pointer-events: none; }
    .toast-notification.active { top: 85px; opacity: 1; pointer-events: auto; }
    .toast-header { background: var(--primary); padding: 0.75rem 1rem; font-weight: bold; display: flex; justify-content: space-between; align-items: center; font-size: 1rem; }
    .toast-body { padding: 1.25rem; text-align: center; }
    .toast-actions { display: flex; gap: 0.75rem; padding: 0 1.25rem 1.25rem 1.25rem; }

    .my-taxi-marker { width: 45px; height: 45px; background: white; border: 4px solid var(--primary); border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 22px; box-shadow: 0 0 20px rgba(250, 204, 21, 0.8); }
    .passenger-pin { width: 30px; height: 30px; background: var(--danger); border: 3px solid white; border-radius: 50% 50% 50% 0; transform: rotate(-45deg); display: flex; align-items: center; justify-content: center; box-shadow: -2px 2px 5px rgba(0,0,0,0.3); }
    .passenger-pin-inner { width: 10px; height: 10px; background: white; border-radius: 50%; }
</style>
<body>

    <div class="driver-header">
        <a href="index.php" style="text-decoration: none; color: var(--text-muted); font-size: 1.5rem;" title="Exit">⬅</a>
        <div style="font-weight: bold; font-size: 1.2rem;">Driver Portal</div>
        <div class="switch-container">
            <label class="switch">
                <input type="checkbox" id="dutyToggle" checked onchange="toggleDutyStatus()">
                <span class="slider"></span>
            </label>
            <span id="dutyText" class="duty-text" style="color: var(--success);">Online</span>
        </div>
    </div>

    <div class="map-wrapper">
        <div id="driver-map"></div>
        <div id="offlineOverlay" class="offline-overlay">
            <h2 style="margin-bottom: 0.5rem;">You are Offline</h2>
            <p style="font-size: 0.9rem; color: #cbd5e1;">Toggle duty status to go online<br>and receive requests.</p>
        </div>
    </div>

    <div class="dashboard-content">
        <div class="passenger-view-card">
            <div class="pv-title">👁️ How Passengers See You</div>
            <div class="pv-header">
                <div>
                    <div class="v-name" id="myProfileName">Driver 45 <span class="verified-icon" title="Verified Driver">✔️</span></div>
                    <div class="v-type-badge badge-taxi">Route Taxi</div>
                    <div style="font-size: 0.85rem; color: var(--text-muted); margin-top: 0.3rem;" id="myProfileVehicle">2013 Subaru G4 Impreza (FB20)</div>
                </div>
                <div class="rating-badge">⭐ <span id="myProfileRating">4.8</span></div>
            </div>
        </div>
        
        <div class="stats-row">
            <div class="stat-box">
                <div class="stat-label">Earnings Today</div>
                <div class="stat-value money">$<span id="ui-earnings">0.00</span></div>
            </div>
            <div class="stat-box">
                <div class="stat-label">Penalty Status</div>
                <div class="stat-value" id="ui-penalty" style="font-size: 1.1rem; color: var(--success); margin-top: 0.4rem;">Clean</div>
            </div>
        </div>

        <div class="driver-controls">
            <div class="control-title">Street Pickups (Manual Update)</div>
            <div class="capacity-manager">
                <button class="cap-btn cap-minus" onclick="updateCapacity(-1)">-</button>
                <div class="cap-display">
                    <span id="ui-current-cap">0</span> / 4
                    <span class="cap-sub">Seats Filled</span>
                </div>
                <button class="cap-btn cap-plus" onclick="updateCapacity(1)">+</button>
            </div>
        </div>

        <div id="active-pickups-container">
            <h3 style="margin-bottom: 1rem; font-size: 1rem;">Active Pickups & Routing</h3>
            <div id="no-pickups-msg" style="color: var(--text-muted); text-align: center; padding: 1rem; border: 1px dashed var(--border-color); border-radius: 8px;">
                No assigned passengers currently.
            </div>
            <div class="pickup-list" id="pickupList"></div>
        </div>
    </div>

    <div class="toast-notification" id="incomingRequestToast">
        <div class="toast-header"><span>🔔 Passenger Request</span><span style="font-size: 0.8rem; font-weight: normal;">Just now</span></div>
        <div class="toast-body">
            <div style="font-size: 1.4rem; font-weight: 900; margin-bottom: 0.5rem;">1 Seat Needed</div>
            <div style="color: var(--text-muted); font-size: 0.95rem; margin-bottom: 0.5rem;">Pickup near: <strong style="color: var(--text-main)" id="reqLocation">Loading...</strong></div>
            <div style="color: var(--success); font-weight: bold; font-size: 1.1rem;">Fare: $<span id="reqFare">150</span></div>
        </div>
        <div class="toast-actions">
            <button class="btn btn-outline" style="color: var(--danger); border-color: var(--danger);" onclick="respondToRequest('rejected')">Decline</button>
            <button class="btn btn-primary" onclick="respondToRequest('accepted')">Accept Pickup</button>
        </div>
    </div>

    <div class="modal-overlay" id="notificationModal">
        <div class="modal-card">
            <h3 id="notifTitle" style="margin-bottom: 0.5rem;">Notice</h3>
            <p id="notifMessage" style="color: var(--text-muted); font-size: 0.95rem;"></p>
            <div class="modal-actions-col">
                <button class="btn btn-primary" onclick="document.getElementById('notificationModal').classList.remove('active')">Understood</button>
            </div>
        </div>
    </div>

    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
    <script>
        let map;
        let isOnline = true;
        let pendingReservationId = null;
        let activePickups = [];
        let earnings = 0;
        let myCapacity = 0;
        let pollInterval;

        document.addEventListener('DOMContentLoaded', () => {
            initLeafletMap();
            pollInterval = setInterval(pollForRequests, 4000);
        });

        function initLeafletMap() {
            map = L.map('driver-map', { zoomControl: false, scrollWheelZoom: false }).setView([18.005, -76.81], 13);
            L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', { maxZoom: 18 }).addTo(map);
            
            // Wait for DOM to paint, then invalidate size to ensure it renders correctly
            setTimeout(() => { map.invalidateSize(); }, 200);
            
            // Restoring the Demand Bubbles (Heatmaps)
            L.circle([18.0128, -76.7989], { color: 'red', fillColor: '#ef4444', fillOpacity: 0.3, radius: 800 }).addTo(map);
            L.circle([18.0050, -76.7470], { color: 'orange', fillColor: '#f59e0b', fillOpacity: 0.3, radius: 600 }).addTo(map);
            L.circle([17.9700, -76.8500], { color: 'yellow', fillColor: '#facc15', fillOpacity: 0.3, radius: 1000 }).addTo(map);

            const myIcon = L.divIcon({ className: '', html: `<div class="my-taxi-marker">🚕</div>`, iconSize: [45, 45], iconAnchor: [22, 22] });
            L.marker([18.0128, -76.7989], { icon: myIcon }).addTo(map);
        }

        function toggleDutyStatus() {
            const toggle = document.getElementById('dutyToggle');
            const text = document.getElementById('dutyText');
            const overlay = document.getElementById('offlineOverlay');
            
            isOnline = toggle.checked;

            if (isOnline) {
                text.textContent = "Online";
                text.style.color = "var(--success)";
                overlay.classList.remove('active');
                pollInterval = setInterval(pollForRequests, 4000); 
            } else {
                text.textContent = "Offline";
                text.style.color = "var(--text-muted)";
                overlay.classList.add('active');
                clearInterval(pollInterval);
                document.getElementById('incomingRequestToast').classList.remove('active'); 
            }
        }

        function updateCapacity(change) {
            let newCap = myCapacity + change;
            if (newCap >= 0 && newCap <= 4) {
                myCapacity = newCap;
                document.getElementById('ui-current-cap').textContent = myCapacity;
            }
        }

        async function pollForRequests() {
            if (pendingReservationId || !isOnline) return; 

            const formData = new FormData();
            formData.append('action', 'check_driver_requests');
            const res = await fetch('api.php', { method: 'POST', body: formData });
            const data = await res.json();

            if (data.success && data.has_request) {
                pendingReservationId = data.request_data.id;
                document.getElementById('reqLocation').textContent = "Half Way Tree";
                document.getElementById('reqFare').textContent = data.request_data.fare;
                document.getElementById('incomingRequestToast').classList.add('active');
            }
        }

        async function respondToRequest(status) {
            const formData = new FormData();
            formData.append('action', 'driver_respond');
            formData.append('reservation_id', pendingReservationId);
            formData.append('response_status', status);

            await fetch('api.php', { method: 'POST', body: formData });
            
            document.getElementById('incomingRequestToast').classList.remove('active');
            
            if (status === 'accepted') {
                const newPickup = { id: pendingReservationId, name: "Half Way Tree", fare: 150 };
                activePickups.push(newPickup);
                updateCapacity(1);
                renderPickupList();
            }
            pendingReservationId = null; 
        }

        function renderPickupList() {
            const list = document.getElementById('pickupList');
            const emptyMsg = document.getElementById('no-pickups-msg');
            list.innerHTML = '';

            if (activePickups.length === 0) {
                emptyMsg.style.display = 'block';
                return;
            }
            emptyMsg.style.display = 'none';

            activePickups.forEach(p => {
                const card = document.createElement('div');
                card.className = 'pickup-card';
                card.innerHTML = `
                    <div class="pickup-header">
                        <span>📍 ${p.name}</span>
                        <span style="color: var(--success);">$${p.fare}</span>
                    </div>
                    <div class="pickup-actions">
                        <button class="btn btn-outline" style="color: var(--danger); border-color: var(--danger); padding: 0.5rem;" onclick="alert('Driver cancellation penalty flow triggered')">Cancel</button>
                        <button class="btn btn-primary" style="padding: 0.5rem;" onclick="completeDropoff(${p.id})">Drop Off</button>
                    </div>
                `;
                list.appendChild(card);
            });
        }

        async function completeDropoff(reservationId) {
            const formData = new FormData();
            formData.append('action', 'complete_dropoff');
            formData.append('reservation_id', reservationId);

            try {
                const response = await fetch('api.php', { method: 'POST', body: formData });
                const result = await response.json();

                if (result.success) {
                    const index = activePickups.findIndex(p => p.id === reservationId);
                    if (index > -1) activePickups.splice(index, 1);
                    
                    updateCapacity(-1);
                    renderPickupList();
                    
                    earnings += parseFloat(result.final_earnings);
                    document.getElementById('ui-earnings').textContent = earnings.toFixed(2);
                    document.getElementById('ui-penalty').textContent = "Clean";
                    document.getElementById('ui-penalty').style.color = "var(--success)";

                    document.getElementById('notifTitle').textContent = "Drop-off Complete";
                    document.getElementById('notifMessage').textContent = result.message;
                    document.getElementById('notificationModal').classList.add('active');
                }
            } catch (error) {
                alert("Database connection failed.");
            }
        }
    </script>
</body></html>