<?php
session_start();

// 如果已经登录，直接跳转到管理页面
if (isset($_SESSION['loggedin']) && $_SESSION['loggedin'] === true) {
    header('Location: admin.php');
    exit;
}

$login_error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {      
    $username = isset($_POST['username']) ? trim($_POST['username']) : '';
    $password = isset($_POST['password']) ? trim($_POST['password']) : '';

    // 硬编码的管理员凭据（实际应用中应该使用更安全的方式存储和验证）
    $admin_username = 'admin'; //管理员登录账号
    $admin_password = '123456'; // 管理员登录密码

    // 验证用户名和密码
    if ($username === $admin_username && $password === $admin_password) {
        $_SESSION['loggedin'] = true;
        header('Location: admin.php');
        exit;
    } else {
        $login_error = '用户名或密码错误';
    }
}
?>
<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>管理员登录</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
            --primary: #4361ee;
            --primary-hover: #3a56d4;
            --primary-active: #324bb4;
            --primary-light: #ebedfd;
            --secondary: #f8f9fa;
            --text: #212529;
            --text-light: #6c757d;
            --border: #dee2e6;
            --white: #ffffff;
            --error: #dc3545;
            --error-bg: #f8d7da;
            --success: #28a745;
            --shadow: 0 4px 12px rgba(0, 0, 0, 0.08);
            --transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            --radius: 12px;
            --radius-sm: 8px;
            --input-padding: 1rem 1.25rem;
        }
        
        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }
        
        body {
            font-family: 'Inter', 'PingFang SC', 'Microsoft YaHei', sans-serif;
            background: linear-gradient(135deg, #f5f7fa 0%, #e4e8f0 100%);
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            color: var(--text);
            line-height: 1.6;
            padding: 20px;
        }
        
        .login-container {
            background-color: var(--white);
            padding: 2.5rem;
            border-radius: var(--radius);
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.08);
            width: 100%;
            max-width: 420px;
            transition: var(--transition);
            border: 1px solid rgba(0, 0, 0, 0.05);
            position: relative;
            overflow: hidden;
            transform: translateY(0);
            animation: fadeInUp 0.6s ease;
        }
        
        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(20px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
        
        .login-container::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 6px;
            background: linear-gradient(90deg, var(--primary), #7209b7);
        }
        
        .login-header {
            text-align: center;
            margin-bottom: 2rem;
        }
        
        .login-header .logo {
            width: 72px;
            height: 72px;
            background: linear-gradient(135deg, var(--primary), #7209b7);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 1.5rem;
            color: white;
            font-size: 2rem;
            box-shadow: 0 4px 20px rgba(67, 97, 238, 0.3);
            position: relative;
            overflow: hidden;
        }
        
        .login-header .logo::after {
            content: '';
            position: absolute;
            width: 100%;
            height: 100%;
            background: radial-gradient(circle at center, rgba(255,255,255,0.2) 0%, transparent 70%);
            animation: pulse 2s infinite;
        }
        
        @keyframes pulse {
            0% { transform: scale(1); opacity: 0.4; }
            50% { transform: scale(1.2); opacity: 0.2; }
            100% { transform: scale(1); opacity: 0.4; }
        }
        
        .login-header h1 {
            color: var(--text);
            margin-bottom: 0.5rem;
            font-weight: 700;
            font-size: 1.5rem;
            letter-spacing: -0.5px;
        }
        
        .login-header p {
            color: var(--text-light);
            font-size: 0.95rem;
            font-weight: 500;
        }
        
        .form-group {
            margin-bottom: 1.5rem;
            position: relative;
        }
        
        .form-group label {
            display: block;
            margin-bottom: 0.75rem;
            color: var(--text);
            font-weight: 600;
            font-size: 0.875rem;
            letter-spacing: 0.2px;
        }
        
        .form-control {
            width: 100%;
            padding: var(--input-padding);
            border: 2px solid var(--border);
            border-radius: var(--radius-sm);
            font-size: 0.95rem;
            transition: var(--transition);
            background-color: var(--white);
            font-weight: 500;
        }
        
        .form-control:focus {
            border-color: var(--primary);
            outline: none;
            box-shadow: 0 0 0 4px rgba(67, 97, 238, 0.15);
        }
        
        .form-control::placeholder {
            color: var(--text-light);
            opacity: 0.6;
            font-weight: 400;
        }
        
        .input-icon {
            position: relative;
        }
        
        .input-icon i {
            position: absolute;
            left: 1.25rem;
            top: 50%;
            transform: translateY(-50%);
            color: var(--text-light);
            font-size: 1.1rem;
            transition: var(--transition);
        }
        
        .input-icon .form-control {
            padding-left: 3rem;
        }
        
        .btn {
            display: inline-block;
            width: 100%;
            padding: 1rem;
            border-radius: var(--radius-sm);
            font-weight: 600;
            text-align: center;
            cursor: pointer;
            transition: var(--transition);
            border: none;
            font-size: 0.95rem;
            letter-spacing: 0.5px;
            text-transform: uppercase;
            position: relative;
            overflow: hidden;
        }
        
        .btn-primary {
            background: linear-gradient(90deg, var(--primary), #4895ef);
            color: var(--white);
            box-shadow: 0 4px 15px rgba(67, 97, 238, 0.3);
        }
        
        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(67, 97, 238, 0.4);
            background: linear-gradient(90deg, var(--primary-hover), #3a7bd5);
        }
        
        .btn-primary:active {
            transform: translateY(0);
            background: linear-gradient(90deg, var(--primary-active), #3268c7);
        }
        
        .btn-primary::after {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255,255,255,0.2), transparent);
            transition: var(--transition);
        }
        
        .btn-primary:hover::after {
            left: 100%;
        }
        
        .error-message {
            color: var(--error);
            background-color: var(--error-bg);
            padding: 0.875rem 1rem;
            border-radius: var(--radius-sm);
            font-size: 0.875rem;
            margin-bottom: 1.5rem;
            display: flex;
            align-items: center;
            gap: 0.75rem;
            border: 1px solid rgba(220, 53, 69, 0.2);
            animation: shake 0.5s ease;
            font-weight: 500;
        }
        
        @keyframes shake {
            0%, 100% { transform: translateX(0); }
            20%, 60% { transform: translateX(-5px); }
            40%, 80% { transform: translateX(5px); }
        }
        
        .footer {
            text-align: center;
            margin-top: 1.5rem;
            font-size: 0.8rem;
            color: var(--text-light);
            font-weight: 500;
        }
        
        .footer a {
            color: var(--primary);
            text-decoration: none;
            transition: var(--transition);
            font-weight: 600;
        }
        
        .footer a:hover {
            text-decoration: underline;
            color: var(--primary-hover);
        }
        
        .password-container {
            position: relative;
        }
        
        .password-toggle {
            position: absolute;
            right: 1.25rem;
            top: 50%;
            transform: translateY(-50%);
            color: var(--text-light);
            font-size: 1rem;
            cursor: pointer;
            background: none;
            border: none;
            padding: 0.5rem;
            border-radius: 50%;
            width: 32px;
            height: 32px;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: var(--transition);
        }
        
        .password-toggle:hover {
            background-color: rgba(67, 97, 238, 0.1);
            color: var(--primary);
        }
        
        .form-footer {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-top: 1.25rem;
        }
        
        .remember-me {
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }
        
        .remember-me input {
            width: 16px;
            height: 16px;
            accent-color: var(--primary);
        }
        
        .remember-me label {
            font-size: 0.85rem;
            color: var(--text-light);
            cursor: pointer;
            user-select: none;
        }
        
        /* 加载动画 */
        .loading-spinner {
            display: none;
            width: 18px;
            height: 18px;
            border: 2px solid rgba(255, 255, 255, 0.3);
            border-radius: 50%;
            border-top-color: white;
            animation: spin 1s ease-in-out infinite;
            margin-right: 8px;
        }
        
        .btn.loading .loading-spinner {
            display: inline-block;
        }
        
        .btn.loading i {
            display: none;
        }
        
        @keyframes spin {
            to { transform: rotate(360deg); }
        }
        
        /* 响应式设计 */
        @media (max-width: 480px) {
            .login-container {
                padding: 1.5rem;
            }
            
            .login-header h1 {
                font-size: 1.35rem;
            }
            
            .form-control {
                padding: 0.875rem 1rem;
            }
            
            .input-icon i {
                left: 1rem;
            }
            
            .input-icon .form-control {
                padding-left: 2.75rem;
            }
            
            .btn {
                padding: 0.875rem;
            }
        }
        
        /* 卡片悬浮效果 */
        .login-container:hover {
            box-shadow: 0 15px 35px rgba(0, 0, 0, 0.12);
        }
        
        /* 输入框聚焦效果增强 */
        .form-control:focus + i {
            color: var(--primary) !important;
        }
    </style>
</head>
<body>
    <div class="login-container">
        <div class="login-header">
            <div class="logo">
                <i class="fas fa-shield-alt"></i>
            </div>
            <h1>系统管理后台</h1>
            <p>安全登录以访问管理面板</p>
        </div>
        
        <?php if (!empty($login_error)): ?>
            <div class="error-message">
                <i class="fas fa-exclamation-triangle"></i> 
                <span><?= htmlspecialchars($login_error) ?></span>
            </div>
        <?php endif; ?>
        
        <form method="post" id="loginForm">
            <div class="form-group">
                <label for="username">管理员账号</label>
                <div class="input-icon">
                    <i class="fas fa-user-circle"></i>
                    <input type="text" id="username" name="username" class="form-control" placeholder="请输入管理员账号" required>
                </div>
            </div>
            
            <div class="form-group">
                <label for="password">密码</label>
                <div class="password-container">
                    <div class="input-icon">
                        <i class="fas fa-lock"></i>
                        <input type="password" id="password" name="password" class="form-control" placeholder="请输入密码" required>
                    </div>
                    <button type="button" class="password-toggle" onclick="togglePassword()">
                        <i class="fas fa-eye"></i>
                    </button>
                </div>
            </div>
            
            <div class="form-footer">
                <div class="remember-me">
                    <input type="checkbox" id="remember" name="remember">
                    <label for="remember">记住我</label>
                </div>
            </div>
            
            <div class="form-group">
                <button type="submit" class="btn btn-primary" id="loginButton">
                    <span class="loading-spinner"></span>
                    <i class="fas fa-sign-in-alt"></i> 安全登录
                </button>
            </div>
        </form>
        
        <div class="footer">
            © <?= date('Y') ?> 管理后台系统 | 版本 1.0.0
        </div>
    </div>

    <script>
        function togglePassword() {
            const passwordInput = document.getElementById('password');
            const icon = document.querySelector('.password-toggle i');
            
            if (passwordInput.type === 'password') {
                passwordInput.type = 'text';
                icon.classList.remove('fa-eye');
                icon.classList.add('fa-eye-slash');
            } else {
                passwordInput.type = 'password';
                icon.classList.remove('fa-eye-slash');
                icon.classList.add('fa-eye');
            }
        }
        
        // 添加输入框焦点动画
        document.querySelectorAll('.form-control').forEach(input => {
            input.addEventListener('focus', function() {
                const icon = this.parentElement.querySelector('.input-icon i');
                if (icon) {
                    icon.style.color = 'var(--primary)';
                }
            });
            
            input.addEventListener('blur', function() {
                const icon = this.parentElement.querySelector('.input-icon i');
                if (icon) {
                    icon.style.color = 'var(--text-light)';
                }
            });
        });
        
        // 表单提交处理
        document.getElementById('loginForm').addEventListener('submit', function(e) {
            const button = document.getElementById('loginButton');
            button.classList.add('loading');
            button.disabled = true;
            
            // 模拟提交延迟（实际使用时移除）
            setTimeout(() => {
                // 这里可以添加表单验证或其他逻辑
                // 实际提交时不需要这个setTimeout
            }, 1000);
        });
    </script>
</body>
</html>