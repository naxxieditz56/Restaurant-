<?php
session_start();
require_once 'includes/config.php';

// Redirect if already logged in
if (isLoggedIn()) {
    header('Location: dashboard.php');
    exit();
}

$error = '';
$success = '';

// Handle login form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!isset($_POST['csrf_token']) || !validateCSRFToken($_POST['csrf_token'])) {
        $error = 'Invalid CSRF token.';
    } else {
        $username = sanitize($_POST['username']);
        $password = $_POST['password'];
        $remember = isset($_POST['remember']);
        
        // Validate input
        if (empty($username) || empty($password)) {
            $error = 'Please enter both username and password.';
        } else {
            // Check user credentials
            $stmt = $pdo->prepare("SELECT * FROM admin_users WHERE username = ? AND status = 'active'");
            $stmt->execute([$username]);
            $user = $stmt->fetch();
            
            if ($user && password_verify($password, $user['password_hash'])) {
                // Set session variables
                $_SESSION['admin_id'] = $user['id'];
                $_SESSION['admin_username'] = $user['username'];
                $_SESSION['admin_role'] = $user['role'];
                $_SESSION['admin_name'] = $user['full_name'];
                $_SESSION['admin_avatar'] = $user['avatar'];
                
                // Update last login
                $stmt = $pdo->prepare("UPDATE admin_users SET last_login = NOW() WHERE id = ?");
                $stmt->execute([$user['id']]);
                
                // Log activity
                logActivity('login', 'User logged in');
                
                // Set remember me cookie if requested
                if ($remember) {
                    $token = bin2hex(random_bytes(32));
                    $expiry = time() + (30 * 24 * 60 * 60); // 30 days
                    
                    setcookie('remember_token', $token, $expiry, '/', '', true, true);
                    
                    $stmt = $pdo->prepare("UPDATE admin_users SET remember_token = ? WHERE id = ?");
                    $stmt->execute([hash('sha256', $token), $user['id']]);
                }
                
                // Redirect to dashboard
                header('Location: dashboard.php');
                exit();
            } else {
                $error = 'Invalid username or password.';
                logActivity('failed_login', 'Failed login attempt for username: ' . $username);
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login | Jack Fry's Admin</title>
    <link rel="stylesheet" href="assets/css/admin.css">
    <link rel="stylesheet" href="assets/css/responsive.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
            --gradient-start: #667eea;
            --gradient-end: #764ba2;
        }
        
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, sans-serif;
            background: linear-gradient(135deg, var(--gradient-start) 0%, var(--gradient-end) 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
            color: #333;
        }
        
        .login-wrapper {
            width: 100%;
            max-width: 420px;
        }
        
        .login-container {
            background: white;
            border-radius: 16px;
            box-shadow: 0 20px 60px rgba(0,0,0,0.3);
            overflow: hidden;
            animation: slideUp 0.6s ease-out;
        }
        
        @keyframes slideUp {
            from {
                opacity: 0;
                transform: translateY(30px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
        
        .login-header {
            background: linear-gradient(135deg, #8B0000 0%, #a52a2a 100%);
            color: white;
            padding: 40px 30px 30px;
            text-align: center;
            position: relative;
        }
        
        .login-header::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 4px;
            background: linear-gradient(90deg, #D4AF37, #ffd700);
        }
        
        .logo {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 15px;
            margin-bottom: 20px;
        }
        
        .logo-icon {
            width: 50px;
            height: 50px;
            background: white;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #8B0000;
            font-size: 24px;
            font-weight: bold;
            box-shadow: 0 4px 12px rgba(0,0,0,0.1);
        }
        
        .logo-text {
            font-size: 24px;
            font-weight: 700;
            letter-spacing: 1px;
        }
        
        .logo-tagline {
            font-size: 14px;
            opacity: 0.9;
            margin-top: 5px;
            font-style: italic;
        }
        
        .login-body {
            padding: 40px;
        }
        
        .alert {
            padding: 14px 18px;
            border-radius: 10px;
            margin-bottom: 25px;
            display: flex;
            align-items: center;
            gap: 12px;
            animation: fadeIn 0.3s ease;
        }
        
        @keyframes fadeIn {
            from { opacity: 0; }
            to { opacity: 1; }
        }
        
        .alert-error {
            background: #fee;
            border: 1px solid #fcc;
            color: #c00;
        }
        
        .alert-success {
            background: #efe;
            border: 1px solid #cfc;
            color: #090;
        }
        
        .alert i {
            font-size: 18px;
        }
        
        .form-group {
            margin-bottom: 24px;
        }
        
        .form-label {
            display: block;
            margin-bottom: 8px;
            font-weight: 500;
            color: #444;
            font-size: 14px;
        }
        
        .input-group {
            position: relative;
        }
        
        .input-icon {
            position: absolute;
            left: 16px;
            top: 50%;
            transform: translateY(-50%);
            color: #777;
            font-size: 16px;
        }
        
        .form-control {
            width: 100%;
            padding: 14px 16px 14px 48px;
            border: 2px solid #e1e5e9;
            border-radius: 10px;
            font-size: 15px;
            font-family: inherit;
            transition: all 0.3s ease;
            background: #f8f9fa;
        }
        
        .form-control:focus {
            outline: none;
            border-color: #8B0000;
            background: white;
            box-shadow: 0 0 0 3px rgba(139, 0, 0, 0.1);
        }
        
        .form-control.error {
            border-color: #dc3545;
            background: #fff5f5;
        }
        
        .checkbox-group {
            display: flex;
            align-items: center;
            gap: 10px;
            margin-bottom: 25px;
        }
        
        .checkbox {
            width: 18px;
            height: 18px;
            accent-color: #8B0000;
            cursor: pointer;
        }
        
        .checkbox-label {
            font-size: 14px;
            color: #666;
            cursor: pointer;
            user-select: none;
        }
        
        .btn-login {
            width: 100%;
            padding: 16px;
            background: linear-gradient(135deg, #8B0000 0%, #a52a2a 100%);
            color: white;
            border: none;
            border-radius: 10px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
            letter-spacing: 0.5px;
        }
        
        .btn-login:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(139, 0, 0, 0.3);
        }
        
        .btn-login:active {
            transform: translateY(0);
        }
        
        .btn-login i {
            font-size: 18px;
        }
        
        .login-footer {
            text-align: center;
            padding-top: 25px;
            border-top: 1px solid #eee;
            margin-top: 25px;
        }
        
        .footer-text {
            color: #777;
            font-size: 13px;
        }
        
        .copyright {
            color: #999;
            font-size: 12px;
            margin-top: 10px;
        }
        
        .password-toggle {
            position: absolute;
            right: 16px;
            top: 50%;
            transform: translateY(-50%);
            background: none;
            border: none;
            color: #777;
            cursor: pointer;
            font-size: 16px;
            padding: 4px;
        }
        
        .password-toggle:hover {
            color: #8B0000;
        }
        
        /* Responsive */
        @media (max-width: 480px) {
            .login-container {
                border-radius: 12px;
            }
            
            .login-header {
                padding: 30px 20px 25px;
            }
            
            .login-body {
                padding: 30px 25px;
            }
            
            .logo {
                flex-direction: column;
                gap: 10px;
            }
            
            .logo-text {
                font-size: 20px;
            }
        }
        
        @media (max-height: 600px) {
            body {
                padding: 10px;
            }
            
            .login-container {
                max-height: 95vh;
                overflow-y: auto;
            }
        }
        
        /* Loading animation */
        .btn-login.loading {
            position: relative;
            color: transparent;
        }
        
        .btn-login.loading::after {
            content: '';
            position: absolute;
            width: 20px;
            height: 20px;
            border: 2px solid rgba(255,255,255,0.3);
            border-top-color: white;
            border-radius: 50%;
            animation: spin 0.8s linear infinite;
        }
        
        @keyframes spin {
            to { transform: rotate(360deg); }
        }
        
        /* Pulse animation for new login */
        @keyframes pulse {
            0% { box-shadow: 0 0 0 0 rgba(139, 0, 0, 0.7); }
            70% { box-shadow: 0 0 0 10px rgba(139, 0, 0, 0); }
            100% { box-shadow: 0 0 0 0 rgba(139, 0, 0, 0); }
        }
        
        .pulse {
            animation: pulse 2s infinite;
        }
    </style>
</head>
<body>
    <div class="login-wrapper">
        <div class="login-container">
            <div class="login-header">
                <div class="logo">
                    <div class="logo-icon">
                        <i class="fas fa-utensils"></i>
                    </div>
                    <div>
                        <div class="logo-text">JACK FRY'S</div>
                        <div class="logo-tagline">Admin Portal</div>
                    </div>
                </div>
                <p>Sign in to manage your restaurant</p>
            </div>
            
            <div class="login-body">
                <?php if ($error): ?>
                    <div class="alert alert-error">
                        <i class="fas fa-exclamation-circle"></i>
                        <div><?php echo htmlspecialchars($error); ?></div>
                    </div>
                <?php endif; ?>
                
                <?php if ($success): ?>
                    <div class="alert alert-success">
                        <i class="fas fa-check-circle"></i>
                        <div><?php echo htmlspecialchars($success); ?></div>
                    </div>
                <?php endif; ?>
                
                <form method="POST" action="" id="loginForm">
                    <input type="hidden" name="csrf_token" value="<?php echo generateCSRFToken(); ?>">
                    
                    <div class="form-group">
                        <label class="form-label" for="username">Username</label>
                        <div class="input-group">
                            <span class="input-icon">
                                <i class="fas fa-user"></i>
                            </span>
                            <input type="text" 
                                   id="username" 
                                   name="username" 
                                   class="form-control" 
                                   required 
                                   autocomplete="username"
                                   value="<?php echo isset($_POST['username']) ? htmlspecialchars($_POST['username']) : ''; ?>"
                                   placeholder="Enter your username">
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label" for="password">Password</label>
                        <div class="input-group">
                            <span class="input-icon">
                                <i class="fas fa-lock"></i>
                            </span>
                            <input type="password" 
                                   id="password" 
                                   name="password" 
                                   class="form-control" 
                                   required 
                                   autocomplete="current-password"
                                   placeholder="Enter your password">
                            <button type="button" class="password-toggle" id="togglePassword">
                                <i class="fas fa-eye"></i>
                            </button>
                        </div>
                    </div>
                    
                    <div class="checkbox-group">
                        <input type="checkbox" 
                               id="remember" 
                               name="remember" 
                               class="checkbox">
                        <label for="remember" class="checkbox-label">
                            Remember me for 30 days
                        </label>
                    </div>
                    
                    <button type="submit" class="btn-login pulse" id="loginButton">
                        <i class="fas fa-sign-in-alt"></i>
                        Sign In
                    </button>
                </form>
                
                <div class="login-footer">
                    <p class="footer-text">
                        <i class="fas fa-shield-alt"></i>
                        Secure admin portal
                    </p>
                    <p class="copyright">
                        &copy; <?php echo date('Y'); ?> Jack Fry's Restaurant. All rights reserved.
                    </p>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Password toggle
        const togglePassword = document.getElementById('togglePassword');
        const passwordInput = document.getElementById('password');
        
        togglePassword.addEventListener('click', function() {
            const type = passwordInput.getAttribute('type') === 'password' ? 'text' : 'password';
            passwordInput.setAttribute('type', type);
            this.innerHTML = type === 'password' ? '<i class="fas fa-eye"></i>' : '<i class="fas fa-eye-slash"></i>';
        });
        
        // Form validation and submission
        const loginForm = document.getElementById('loginForm');
        const loginButton = document.getElementById('loginButton');
        
        loginForm.addEventListener('submit', function(e) {
            // Clear previous errors
            document.querySelectorAll('.form-control.error').forEach(input => {
                input.classList.remove('error');
            });
            
            // Validate inputs
            const username = document.getElementById('username').value.trim();
            const password = document.getElementById('password').value.trim();
            let isValid = true;
            
            if (!username) {
                showError('username', 'Username is required');
                isValid = false;
            }
            
            if (!password) {
                showError('password', 'Password is required');
                isValid = false;
            }
            
            if (!isValid) {
                e.preventDefault();
                return;
            }
            
            // Show loading state
            loginButton.classList.add('loading');
            loginButton.disabled = true;
            loginButton.innerHTML = '';
            
            // Prevent multiple submissions
            loginForm.querySelectorAll('input, button').forEach(element => {
                element.disabled = true;
            });
        });
        
        function showError(fieldId, message) {
            const field = document.getElementById(fieldId);
            field.classList.add('error');
            
            // Remove existing error message
            const existingError = field.parentNode.querySelector('.error-message');
            if (existingError) {
                existingError.remove();
            }
            
            // Add error message
            const errorDiv = document.createElement('div');
            errorDiv.className = 'error-message';
            errorDiv.style.color = '#dc3545';
            errorDiv.style.fontSize = '13px';
            errorDiv.style.marginTop = '5px';
            errorDiv.textContent = message;
            
            field.parentNode.appendChild(errorDiv);
        }
        
        // Auto-focus username field
        document.getElementById('username').focus();
        
        // Enter key to submit
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Enter' && !loginButton.disabled) {
                loginForm.requestSubmit();
            }
        });
        
        // Remove error on input
        document.querySelectorAll('.form-control').forEach(input => {
            input.addEventListener('input', function() {
                this.classList.remove('error');
                const errorMsg = this.parentNode.querySelector('.error-message');
                if (errorMsg) {
                    errorMsg.remove();
                }
            });
        });
        
        // Prevent form resubmission on refresh
        if (window.history.replaceState) {
            window.history.replaceState(null, null, window.location.href);
        }
        
        // Add some visual feedback
        document.querySelectorAll('.form-control').forEach(input => {
            input.addEventListener('focus', function() {
                this.parentNode.style.transform = 'translateY(-2px)';
            });
            
            input.addEventListener('blur', function() {
                this.parentNode.style.transform = 'translateY(0)';
            });
        });
    </scrip
