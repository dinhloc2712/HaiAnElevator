<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Xác thực OTP - Đăng Kiểm Tàu Cá</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <style>
        body {
            background-color: #f8f9fc;
            background-image: url('{{ asset('login-bg.jpg') }}');
            background-size: cover;
            background-position: center;
            background-repeat: no-repeat;
            background-attachment: fixed;
            height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            position: relative;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        body::before {
            content: "";
            position: absolute;
            top: 0; left: 0; right: 0; bottom: 0;
            background: rgba(0, 0, 0, 0.5); /* Sáng tối vừa phải */
            z-index: 1;
        }
        .login-card {
            position: relative;
            z-index: 2;
            width: 90%;
            max-width: 420px;
            padding: 40px;
            border-radius: 20px;
            background: rgba(255, 255, 255, 0.85); /* Semi-transparent white */
            backdrop-filter: blur(10px); /* Glassmorphism */
            box-shadow: 0 15px 35px rgba(0,0,0,0.2);
            border: 1px solid rgba(255, 255, 255, 0.5);
        }
        .text-primary {
            color: #1a4f8b !important; /* Deep sea blue to fit theme */
        }
        .btn-primary {
            background: linear-gradient(135deg, #1a4f8b 0%, #2a75c9 100%);
            border: none;
            border-radius: 50px;
            padding: 12px 20px;
            font-weight: 600;
            letter-spacing: 0.5px;
            transition: all 0.3s;
            color: white;
        }
        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(26, 79, 139, 0.4);
            background: linear-gradient(135deg, #2a75c9 0%, #1a4f8b 100%);
            color: white;
        }
        .form-control, .input-group-text {
            border-radius: 50px;
            background: rgba(255, 255, 255, 0.9);
            border: 1px solid rgba(0,0,0,0.1);
        }
        .form-control {
            padding-left: 15px;
        }
        .form-control:focus {
            box-shadow: 0 0 0 0.25rem rgba(26, 79, 139, 0.25);
            border-color: #1a4f8b;
        }
        .input-group-text {
            border-right: none;
            color: #1a4f8b;
            padding-left: 20px;
        }
        .input-group .form-control {
            border-left: none;
        }
        .form-label {
            font-weight: 600;
            color: #333;
            font-size: 0.9rem;
        }
    </style>
</head>
<body>

<div class="login-card">
    <div class="text-center mb-4">
        <img src="{{ asset('logo.png') }}" alt="Logo" class="img-fluid mb-3" style="max-height: 80px;">
        <h4 class="text-primary fw-bold mb-1">ĐẶT LẠI MẬT KHẨU</h4>
        <p class="text-muted small">Mã OTP đã được gửi đến email:<br><strong>{{ session('reset_email') }}</strong></p>
    </div>

    @if(session('error'))
        <div class="alert alert-danger">
            {{ session('error') }}
        </div>
    @endif
    @if($errors->any())
        <div class="alert alert-danger">
            <ul class="mb-0">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form action="{{ route('password.update') }}" method="POST">
        @csrf
        <div class="mb-3">
            <label class="form-label ps-2">Mã OTP</label>
            <div class="input-group">
                <span class="input-group-text"><i class="fas fa-key"></i></span>
                <input type="text" name="otp" class="form-control" required placeholder="Nhập mã 6 số" autocomplete="off" autofocus>
            </div>
        </div>
        
        <div class="mb-3">
            <label class="form-label ps-2">Mật khẩu mới</label>
            <div class="input-group">
                <span class="input-group-text"><i class="fas fa-lock"></i></span>
                <input type="password" name="password" class="form-control" required placeholder="Nhập mật khẩu mới">
            </div>
        </div>
        
        <div class="mb-4">
            <label class="form-label ps-2">Xác nhận mật khẩu</label>
            <div class="input-group">
                <span class="input-group-text"><i class="fas fa-check-circle"></i></span>
                <input type="password" name="password_confirmation" class="form-control" required placeholder="Nhập lại mật khẩu mới">
            </div>
        </div>

        <button type="submit" class="btn btn-primary w-100 mb-3">Xác nhận Đổi mật khẩu</button>
    </form>
    <div class="text-center mt-3 border-top pt-3">
        <a href="{{ route('password.request') }}" class="text-primary small text-decoration-none fw-bold"><i class="fas fa-redo-alt me-1"></i> Gửi lại OTP</a>
    </div>
</div>

</body>
</html>
