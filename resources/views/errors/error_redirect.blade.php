<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Thông báo hệ thống</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <style>
        body { background-color: #f4f7f6; height: 100vh; display: flex; align-items: center; justify-content: center; font-family: 'Inter', sans-serif; }
        .error-card { background: white; padding: 2rem; border-radius: 1rem; shadow: 0 10px 25px rgba(0,0,0,0.05); text-align: center; max-width: 500px; width: 90%; }
    </style>
</head>
<body>
    <div class="error-card shadow-lg">
        <div class="mb-4 text-warning">
            <i class="fas fa-exclamation-triangle fa-4x"></i>
        </div>
        <h4 class="fw-bold mb-3">Thông báo từ hệ thống</h4>
        <p class="text-muted mb-4">{{ $message }}</p>
        <button class="btn btn-primary px-5 py-2 rounded-pill fw-bold" onclick="handleRedirect()">
            Đóng & Quay lại
        </button>
    </div>

    <script>
        function handleRedirect() {
            window.location.href = "{{ $redirect }}";
        }

        // Tự động hiện SweetAlert khi trang load
        document.addEventListener('DOMContentLoaded', function() {
            Swal.fire({
                title: 'Thông báo',
                text: '{!! addslashes($message) !!}',
                icon: 'info',
                confirmButtonText: 'Đóng',
                confirmButtonColor: '#3085d6',
                allowOutsideClick: false
            }).then((result) => {
                if (result.isConfirmed) {
                    handleRedirect();
                }
            });
        });
    </script>
</body>
</html>
