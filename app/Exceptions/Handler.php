<?php

namespace App\Exceptions;

use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Throwable;

class Handler extends ExceptionHandler
{
    /**
     * A list of exception types with their corresponding custom log levels.
     *
     * @var array<class-string<\Throwable>, \Psr\Log\LogLevel::*>
     */
    protected $levels = [
        //
    ];

    /**
     * A list of the exception types that are not reported.
     *
     * @var array<int, class-string<\Throwable>>
     */
    protected $dontReport = [
        //
    ];

    /**
     * A list of the inputs that are never flashed to the session on validation exceptions.
     *
     * @var array<int, string>
     */
    protected $dontFlash = [
        'current_password',
        'password',
        'password_confirmation',
    ];

    /**
     * Register the exception handling callbacks for the application.
     */
    public function register(): void
    {
        $this->reportable(function (Throwable $e) {
            //
        });
    }

    /**
     * Render an exception into an HTTP response.
     */
    public function render($request, Throwable $e)
    {
        // Nếu là yêu cầu API (JSON), hãy để Laravel xử lý mặc định
        if ($request->expectsJson()) {
            return parent::render($request, $e);
        }

        // Cho phép các lỗi mặc định của Laravel (Validation, Auth, 404) hoạt động bình thường
        if ($e instanceof \Illuminate\Validation\ValidationException || 
            $e instanceof \Illuminate\Auth\AuthenticationException ||
            $e instanceof \Illuminate\Auth\Access\AuthorizationException ||
            $e instanceof \Symfony\Component\HttpKernel\Exception\NotFoundHttpException) {
            return parent::render($request, $e);
        }

        // Xác định thông báo lỗi (Lọc bỏ chi tiết kỹ thuật/SQL nếu cần)
        $message = $e->getMessage();
        if ($e instanceof \Illuminate\Database\QueryException) {
            $message = 'Lỗi truy vấn cơ sở dữ liệu (Mã: ' . ($e->getCode() ?: 'SQL-ERR') . ')';
        } elseif ($e instanceof \ErrorException) {
            $message = 'Lỗi xử lý hệ thống (Mã: ' . ($e->getCode() ?: 'SYS-ERR') . ')';
        }

        // Với tất cả các lỗi khác (SQL, Logic code...), trả về trang trung gian để hiện Modal
        $previousUrl = url()->previous();
        $currentFullUrl = url()->full(); // Lấy cả query string (ví dụ ?search=...)
        $redirectUrl = $previousUrl;

        // Nếu là yêu cầu POST (Thêm/Sửa), luôn quay lại trang trước đó (Form)
        if ($request->isMethod('post') || $request->isMethod('put') || $request->isMethod('patch')) {
            $redirectUrl = $previousUrl ?: route('admin.dashboard');
        } 
        // Nếu là yêu cầu GET (Xem danh sách/chi tiết)
        else {
            // Nếu trang trước đó giống hệt trang hiện tại (tránh lặp lỗi vô hạn)
            // Hoặc không có trang trước đó
            if ($previousUrl == $currentFullUrl || !$previousUrl) {
                $redirectUrl = route('admin.dashboard');
            }
        }

        return response()->view('errors.error_redirect', [
            'message' => $message,
            'redirect' => $redirectUrl
        ]);
    }
}
