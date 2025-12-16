<!DOCTYPE html>
<html lang="en" data-theme-lock="light">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>We Build - Login</title>
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome for icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="<?= base_url('public/assets/theme.css') ?>" rel="stylesheet">
    <script src="<?= base_url('public/assets/theme.js') ?>" defer></script>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body.login-page {
            height: 100vh;
            background: url('<?= base_url('public/assets/bg.png') ?>') no-repeat center center !important;
            background-size: cover !important;
            display: flex;
            align-items: center;
            justify-content: center;
            font-family: Arial, sans-serif;
            font-size: 0;
            position: relative;
            color: #ffffff !important;
        }

        body.login-page::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.1);
            backdrop-filter: blur(2px);
        }

        .login-container {
            position: relative;
            z-index: 1;
            background: rgba(255, 255, 255, 0.1);
            border-radius: 30px;
            font-size: 16px;
            padding: 0;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.1);
            width: 100%;
            max-width: 400px;
            overflow: hidden;
        }

        .logo-section {
            background: rgba(255, 255, 255, 0.0);
            padding: 1px 5px;
            text-align: left;
            border-radius: 25px 25px 0 0;
        }

        .logo-wrapper {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
        }

        .logo-wrapper img {
            width: 100px;
            height: auto;
        }

        .logo-wrapper h1 {
            font-size: 38px;
            font-weight: 500;
            color: #000000ff;
            margin: 0;
            font-style: italic;
            font-family: 'Times New Roman', Times, serif;
        }

        .form-section {
            background: linear-gradient(135deg, rgba(255, 255, 255, 0.01) 0%, rgba(255, 255, 255, 0.1) 100%);
            padding: 30px 35px 35px 35px;
            border-radius: 0 0 25px 25px;
        }

        .form-group {
            margin-bottom: 15px;
        }

        .form-group label {
            display: block;
            color: white;
            font-size: 14px;
            margin-bottom: 6px;
            font-weight: 500;
        }

        .form-group input {
            width: 100%;
            padding: 12px 15px;
            border: none;
            border-radius: 8px;
            background: white;
            font-size: 14px;
            transition: all 0.3s;
        }

        .form-group input:focus {
            outline: none;
            box-shadow: 0 0 0 3px rgba(255, 255, 255, 0.3);
        }

        .form-group input::placeholder {
            color: #999;
        }

        .remember-forgot {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
            margin-top: 10px;
        }

        .remember-me {
            display: flex;
            align-items: center;
        }

        .remember-me input[type="checkbox"] {
            width: auto;
            margin-right: 6px;
            cursor: pointer;
            accent-color: #666;
        }

        .remember-me label {
            color: white;
            font-size: 12px;
            margin: 0;
            cursor: pointer;
        }

        .forgot-password {
            color: white;
            font-size: 12px;
            text-decoration: none;
            transition: all 0.3s;
        }

        .forgot-password:hover {
            color: #ddd;
            text-decoration: underline;
        }

        .btn-login {
            width: 100%;
            padding: 12px;
            background: linear-gradient(135deg, #3a3a3a 0%, #1a1a1a 100%);
            color: white;
            border: none;
            border-radius: 20px;
            font-size: 15px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.3);
        }

        .btn-login:hover {
            background: linear-gradient(135deg, #4a4a4a 0%, #2a2a2a 100%);
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(0, 0, 0, 0.4);
        }

        .btn-login:active {
            transform: translateY(0);
        }

        .alert {
            border-radius: 8px;
            font-size: 13px;
            padding: 10px 15px;
            margin-bottom: 15px;
            border: none;
        }

        .alert-danger {
            background: rgba(220, 53, 69, 0.9);
            color: white;
        }

        .alert-success {
            background: rgba(40, 167, 69, 0.9);
            color: white;
        }

        .alert-info {
            background: rgba(23, 162, 184, 0.9);
            color: white;
        }

        .alert i {
            margin-right: 8px;
        }

        .btn-close {
            filter: invert(1);
        }
    </style>
</head>
<body class="login-page">
    <div class="login-container">
        <div class="logo-section">
            <div class="logo-wrapper">
                <img src="<?= base_url('public/assets/logo.png') ?>" alt="We Build Logo">
                <h1>We Build</h1>
            </div>
        </div>

        <div class="form-section">
            <?php if (session()->getFlashdata('error')): ?>
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <i class="fas fa-exclamation-circle"></i><?= esc(session()->getFlashdata('error')) ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php endif; ?>

            <?php if (session()->getFlashdata('info')): ?>
                <div class="alert alert-info alert-dismissible fade show" role="alert">
                    <i class="fas fa-info-circle"></i><?= esc(session()->getFlashdata('info')) ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php endif; ?>

            <?php if (session()->getFlashdata('success')): ?>
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <i class="fas fa-check-circle"></i><?= esc(session()->getFlashdata('success')) ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php endif; ?>

            <?php if (isset($validation)): ?>
                <div class="alert alert-danger" role="alert">
                    <i class="fas fa-exclamation-circle"></i><?= $validation->listErrors() ?>
                </div>
            <?php endif; ?>

            <form method="post" action="<?= site_url('login') ?>">
                <?= csrf_field() ?>
                
                <div class="form-group">
                    <label for="email">Username</label>
                    <input type="text" id="email" name="email" required autofocus>
                </div>

                <div class="form-group">
                    <label for="password">Password</label>
                    <input type="password" id="password" name="password" required>
                </div>

                <div class="remember-forgot">
                    <div class="remember-me">
                        <input type="checkbox" id="remember" name="remember">
                        <label for="remember">Remember Me</label>
                    </div>
                    <a href="#" class="forgot-password">Forgot Password?</a>
                </div>

                <button type="submit" class="btn-login">
                    Log In
                </button>
            </form>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>