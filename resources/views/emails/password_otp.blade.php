<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <title>Mã xác nhận thay đổi mật khẩu - Đăng Kiểm Tàu Cá</title>
</head>

<body style="margin:0; padding:0; background-color:#f4f4f4; font-family:Arial, Helvetica, sans-serif;">

    <table width="100%" cellpadding="0" cellspacing="0" style="background-color:#f4f4f4; padding:40px 0;">
        <tr>
            <td align="center">
                <!-- Card -->
                <table width="600" cellpadding="0" cellspacing="0"
                    style="background-color:#ffffff; border-radius:12px; border:1px solid #ddd; box-shadow:0 10px 30px rgba(0,0,0,0.1);">

                    <!-- Logo -->
                    <tr>
                        <td align="center" style="padding:30px 20px 10px;">
                            <img src="{{ $message->embed(public_path('logo.png')) }}" alt="Đăng Kiểm Tàu Cá"
                                style="height:50px;">
                        </td>
                    </tr>

                    <!-- Title -->
                    <tr>
                        <td align="center" style="padding:10px 30px;">
                            <h1 style="margin:0; font-size:24px; color:#1a4f8b;">
                                Xác nhận thay đổi mật khẩu
                            </h1>
                        </td>
                    </tr>

                    <!-- Content -->
                    <tr>
                        <td style="padding:25px 40px; color:#333333; font-size:15px; line-height:1.6;">
                            <p>Xin chào,</p>
                            <p>
                                Bạn vừa yêu cầu <strong>thay đổi mật khẩu</strong> cho tài khoản của mình.
                                Vui lòng sử dụng mã xác nhận (OTP) bên dưới để tiếp tục:
                            </p>

                            <!-- OTP -->
                            <div style="text-align:center; margin:30px 0;">
                                <span
                                    style="
                                display:inline-block;
                                padding:15px 30px;
                                font-size:28px;
                                letter-spacing:6px;
                                font-weight:bold;
                                color:#ffffff;
                                background:linear-gradient(135deg,#1a4f8b,#2a75c9);
                                border-radius:10px;
                            ">
                                    {{ $otp }}
                                </span>
                            </div>

                            <p style="color:#1a4f8b;">
                                ⏱ Mã này sẽ hết hạn sau <strong>10 phút</strong>.
                            </p>

                            <p style="color:#666666;">
                                Nếu bạn <strong>không thực hiện yêu cầu này</strong>, vui lòng bỏ qua email.
                            </p>
                        </td>
                    </tr>

                    <!-- Divider -->
                    <tr>
                        <td style="padding:0 40px;">
                            <hr style="border:none; border-top:1px solid #eee;">
                        </td>
                    </tr>

                    <!-- Footer -->
                    <tr>
                        <td align="center" style="padding:20px; font-size:12px; color:#999;">
                            Công ty cổ phần đăng kiểm tàu cá và dịch vụ hậu cần nghề các Nghệ An.<br>
                            © {{ date('Y') }} All rights reserved.
                        </td>
                    </tr>

                </table>
            </td>
        </tr>
    </table>

</body>

</html>
