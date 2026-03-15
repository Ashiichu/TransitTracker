<?php include 'includes/header.php'; ?>
<style>
    body { background: linear-gradient(135deg, #facc15 0%, #f59e0b 100%); display: flex; justify-content: center; align-items: center; height: 100vh; color: var(--text-main); }
    .login-card { background: white; padding: 3rem; border-radius: 16px; box-shadow: 0 10px 25px rgba(0,0,0,0.1); width: 100%; max-width: 400px; text-align: center; }
    h1 { margin-bottom: 0.5rem; font-size: 2rem; }
    p { color: var(--text-muted); margin-bottom: 2rem; font-weight: 500; }
    .input-group { margin-bottom: 1rem; text-align: left; }
    input { width: 100%; padding: 0.75rem; border: 1px solid var(--border-color); border-radius: 8px; font-size: 1rem; outline: none; }
    input:focus { border-color: var(--primary); }
    
    .btn-group { display: flex; flex-direction: column; gap: 1rem; margin-top: 1.5rem; }
    .btn-passenger { background-color: var(--text-main); color: var(--primary); }
    .btn-passenger:hover { background-color: #000; }
    .btn-driver { background-color: #f3f4f6; color: var(--text-main); border: 2px solid var(--border-color); }
    .btn-driver:hover { border-color: var(--secondary); background-color: #eff6ff; }
    
    #error-msg { color: var(--danger); font-size: 0.9rem; margin-top: 1rem; font-weight: bold; display: none; }
</style>
<body>
    <div class="login-card">
        <h1>Transit Tracker</h1>
        <p>Jamaica's Official Transit Network</p>
        
        <div class="input-group">
            <input type="text" id="emailInput" placeholder="Email" value="demo@network.com">
        </div>
        <div class="input-group">
            <input type="password" id="passwordInput" placeholder="Password" value="password">
        </div>
        
        <div id="error-msg">Invalid credentials. Please try again.</div>
        
        <div class="btn-group">
            <button class="btn btn-passenger" onclick="attemptLogin('passenger')">👤 Login as Passenger</button>
            <button class="btn btn-driver" onclick="attemptLogin('driver')">🚕 Login as Driver</button>
        </div>
    </div>

    <script>
        async function attemptLogin(role) {
            const email = document.getElementById('emailInput').value;
            const password = document.getElementById('passwordInput').value;
            const errorMsg = document.getElementById('error-msg');
            
            errorMsg.style.display = 'none'; // Reset error text

            const formData = new FormData();
            formData.append('email', email);
            formData.append('password', password);
            formData.append('role', role);

            try {
                const response = await fetch('login_api.php', { method: 'POST', body: formData });
                const result = await response.json();

                if (result.success) {
                    // Redirect to the appropriate dashboard
                    window.location.href = result.redirect;
                } else {
                    errorMsg.textContent = result.message;
                    errorMsg.style.display = 'block';
                }
            } catch (error) {
                errorMsg.textContent = "Network error. Could not reach server.";
                errorMsg.style.display = 'block';
            }
        }
    </script>
</body>
</html>