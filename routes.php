<?php include 'includes/header.php'; ?>
<style>
    .container { max-width: 800px; margin: 0 auto; display: flex; flex-direction: column; height: 100vh; }
    .header-container { padding: 2rem; background: white; border-bottom: 1px solid var(--border-color); position: sticky; top: 0; z-index: 10; }
    .info-banner { background: #eff6ff; border: 1px solid #bfdbfe; border-radius: 8px; padding: 1rem; margin: 1rem 2rem 0 2rem; display: flex; gap: 0.75rem; }
    .route-card { background: white; padding: 1.5rem; border-radius: 12px; margin: 1rem 2rem; box-shadow: 0 2px 4px rgba(0,0,0,0.05); cursor: pointer; border: 2px solid transparent; }
    .route-card:hover { border-color: var(--primary); }
</style>
<body>
    <div class="container">
        <div class="header-container">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1rem;">
                <h2>Find Your Route</h2>
                <a href="index.php" style="color: #ef4444; text-decoration: none; font-weight: bold;">Logout</a>
            </div>
            <input type="text" style="width: 100%; padding: 1rem; border-radius: 12px; border: 2px solid var(--border-color);" placeholder="Search destinations...">
        </div>

        <div class="info-banner">
            <div style="font-size: 1.25rem;">ℹ️</div>
            <div style="font-size: 0.85rem; color: #1e3a8a; line-height: 1.4;">
                <strong>Privacy & Demand Routing:</strong> By selecting a route, you anonymously join a localized "demand bubble" to help drivers gauge passenger volume. Your exact location is never shared with a driver until you explicitly request a seat.
            </div>
        </div>
        
        <div id="routeList">
            <div class="route-card" onclick="window.location.href='map.php?id=r1'">
                <div style="font-size: 1.1rem; font-weight: bold;">📍 Portmore to Half Way Tree</div>
            </div>
            <div class="route-card" onclick="window.location.href='map.php?id=r2'">
                <div style="font-size: 1.1rem; font-weight: bold;">📍 Downtown to Papine</div>
            </div>
        </div>
    </div>
</body></html>