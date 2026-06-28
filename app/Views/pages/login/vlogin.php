<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login | BMS</title>
    <link rel="icon" type="image/png" sizes="16x16" href="<?= base_url('assets/favicon/favicon-16x16.png') ?>">
    <link rel="icon" type="image/png" sizes="32x32" href="<?= base_url('assets/favicon/favicon-32x32.png') ?>">
    <link rel="shortcut icon" href="<?= base_url('assets/favicon/favicon.ico') ?>">
    <link rel="apple-touch-icon" href="<?= base_url('assets/favicon/apple-touch-icon.png') ?>">
    <link rel="manifest" href="<?= base_url('assets/favicon/site.webmanifest') ?>">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
    <style>
        body {
            margin: 0;
            font-family: Poppins, sans-serif;
        }
        .background-container {
            position: relative;
            min-height: 100vh;
            background: url("<?= base_url('assets/img/atrium21.png') ?>") no-repeat center center;
            background-size: cover;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 24px;
        }
        .overlay {
            position: absolute;
            inset: 0;
            background-color: rgba(35, 35, 43, 0.72);
        }
        .login-box {
            position: relative;
            z-index: 1;
            width: 340px;
            max-width: 100%;
            background: #fff;
            border-radius: 20px;
            box-shadow: 0 20px 50px rgba(0, 0, 0, 0.18);
            padding: 34px;
        }
        .login-box h3 {
            margin: 0;
            text-align: center;
            color: #829460;
            /* font-size: 28px; */
            letter-spacing: 0.8px;
        }
        .login-subtitle {
            color: #829460;
            text-align: center;
            margin: 10px 0 22px;
            font-size: 12px;
        }
        .textbox {
            position: relative;
            margin-bottom: 14px;
        }
        .textbox input {
            width: 100%;
            border: 1px solid #d7d7d7;
            border-radius: 6px;
            padding: 11px 40px 11px 12px;
            font-size: 13px;
            box-sizing: border-box;
            color: #23232B;
        }
        .toggle-password {
            position: absolute;
            top: 50%;
            right: 12px;
            transform: translateY(-50%);
            cursor: pointer;
            color: #888;
        }
        .forgot-pwd {
            display: inline-block;
            margin-top: 2px;
            color: #7b7b7b;
            text-decoration: none;
            font-size: 12px;
        }
        .forgot-pwd:hover {
            color: #829460;
        }
        .login-button {
            width: 100%;
            border: none;
            border-radius: 6px;
            padding: 11px 14px;
            margin-top: 20px;
            background: #829460;
            color: #fff;
            cursor: pointer;
            font-size: 14px;
            transition: background-color 0.2s ease;
        }
        .login-button:hover {
            background: #97a57d;
        }
    </style>
</head>
<body>
    <div class="background-container">
        <div class="overlay"></div>
        <div class="login-box">
            <h3>BUILDING MANAGEMENT SYSTEM</h3>
            <p class="login-subtitle">Please entry your details</p>
            <form method="post" action="<?= site_url('login/check') ?>">
                <?= csrf_field() ?>
                <div class="textbox">
                    <input type="text" name="email" placeholder="No. Handphone/Username/Email" autocomplete="off" value="<?= old('email') ?>">
                </div>
                <div class="textbox">
                    <input type="password" name="passwd" id="passwd" placeholder="Password" autocomplete="off">
                    <span class="toggle-password" onclick="togglePassword()">
                        <i id="eye-icon" class="fa fa-eye-slash"></i>
                    </span>
                </div>
                <a href="<?= esc($url_forgot) ?>" class="forgot-pwd">Forgot Password?</a>
                <button type="submit" class="login-button">Login</button>
            </form>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        <?php if (session()->getFlashdata('error')): ?>
        Swal.fire({
            icon: 'error',
            title: 'Login Gagal',
            text: <?= json_encode(session()->getFlashdata('error')) ?>,
            confirmButtonColor: '#829460'
        });
        <?php endif; ?>

        function togglePassword() {
            const input = document.getElementById('passwd');
            const icon = document.getElementById('eye-icon');
            const isPassword = input.type === 'password';
            input.type = isPassword ? 'text' : 'password';
            icon.classList.toggle('fa-eye', isPassword);
            icon.classList.toggle('fa-eye-slash', !isPassword);
        }
    </script>
</body>
</html>
