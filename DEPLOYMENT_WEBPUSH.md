# Hướng dẫn Triển khai Web Push (Laravel)

Tài liệu này hướng dẫn cách cấu hình và triển khai hệ thống thông báo đẩy từ môi trường Phát triển (Windows) lên môi trường Sản xuất (Linux).

## 1. Môi trường Sản xuất (Linux/Ubuntu/Nginx/Apache)
Trên các máy chủ Linux, hệ thống sẽ hoạt động **ngay lập tức** sau khi bạn chạy lệnh cài đặt mà không cần cấu hình thêm OpenSSL.

### Các bước triển khai:
1.  **Cài đặt**: Chạy `composer install --optimize-autoloader --no-dev`.
2.  **Cấu hình .env**: Đảm bảo các khóa VAPID đã có mặt:
    ```env
    VAPID_PUBLIC_KEY=your_public_key
    VAPID_PRIVATE_KEY=your_private_key
    VAPID_SUBJECT=mailto:admin@yourdomain.com
    ```
3.  **Quyền hạn**: Đảm bảo thư mục `storage` và `bootstrap/cache` có quyền ghi.
4.  **HTTPS**: Website **BẮT BUỘC** phải chạy trên HTTPS để Web Push hoạt động trên trình duyệt.

## 2. Môi trường Phát triển (Windows/Laragon/XAMPP)
Nếu bạn gặp lỗi `Unable to create the local key`, hãy thiết lập biến môi trường Windows để code luôn sạch (không cần nạp thủ công trong code):

1.  Mở **Environment Variables** (Biến môi trường) trên Windows.
2.  Thêm biến hệ thống mới:
    - **Tên**: `OPENSSL_CONF`
    - **Giá trị**: `C:\laragon\bin\php\php\extras\ssl\openssl.cnf` (Hoặc đường dẫn tương đương trên máy bạn).
3.  **Khởi động lại Terminal/IDE** để thay đổi có hiệu lực.

## 3. Khắc phục sự cố
- **Lỗi 403/Forbidden**: Kiểm tra CSRF token trong các yêu cầu POST đến `/admin/push-subscriptions`.
- **Không nhận được tin**: Kiểm tra hàng đợi (Queues) nếu bạn cấu hình `MaintenanceNotification` sử dụng `ShouldQueue`. Chạy `php artisan queue:work` để xử lý thông báo.

---
*Hải An Elevator - Hệ thống thông báo đẩy thông minh.*
