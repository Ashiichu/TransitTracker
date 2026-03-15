<?php
// includes/header.php
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Transit Tracker Jamaica</title>
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
    <style>
        /* Core Variables & Reset */
        :root { 
            --primary: #facc15; 
            --secondary: #3b82f6; 
            --bg-color: #f3f4f6; 
            --panel-bg: #ffffff; 
            --text-main: #1f2937; 
            --text-muted: #6b7280; 
            --border-color: #e5e7eb; 
            --success: #22c55e; 
            --danger: #ef4444; 
            --warning: #f59e0b; 
        }
        * { box-sizing: border-box; margin: 0; padding: 0; font-family: system-ui, sans-serif; }
        body { background-color: var(--bg-color); color: var(--text-main); }
        
        /* Universal Button Styles */
        .btn { width: 100%; padding: 1rem; border: none; border-radius: 8px; font-weight: bold; font-size: 1.1rem; cursor: pointer; transition: all 0.2s; }
        .btn-primary { background-color: var(--text-main); color: var(--primary); }
        .btn-primary:hover { background-color: #000; }
        .btn-danger { background-color: #fee2e2; color: var(--danger); border: 1px solid var(--danger); }
        .btn-outline { background-color: transparent; border: 1px solid var(--border-color); color: var(--text-main); }
        
        /* Standardized Modals (Used for 10% fee & Rejections) */
        .modal-overlay { position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); z-index: 3000; display: none; justify-content: center; align-items: center; padding: 1rem; }
        .modal-overlay.active { display: flex; animation: fadeIn 0.2s; }
        .modal-card { background: white; padding: 2rem; border-radius: 16px; width: 100%; max-width: 350px; text-align: center; box-shadow: 0 10px 25px rgba(0,0,0,0.2); }
        .modal-actions, .modal-actions-col { display: flex; gap: 1rem; margin-top: 1.5rem; }
        .modal-actions-col { flex-direction: column; gap: 0.5rem; }
        @keyframes fadeIn { from { opacity: 0; } to { opacity: 1; } }
    </style>
</head>
<body>