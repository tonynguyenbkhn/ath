# Review theme Twmp Ath

## Tổng quan
Theme hiện tại chạy được và đã có cấu trúc chia template khá rõ, nhưng đang có một số điểm lệch chuẩn WordPress và vài chỗ rủi ro về bảo trì.

## Vấn đề mức độ cao

### 1) `functions.php` đang gánh quá nhiều side effect
File `functions.php` vừa bootstrap theme vừa xử lý nhiều logic không nên đặt ở tầng global:

- Gán header CORS cho toàn bộ REST response bằng `rest_pre_serve_request`
- Gọi `wp_enqueue_script('comment-reply')` ở top-level thay vì hook đúng ngữ cảnh
- Hook `save_post` trực tiếp để lưu meta từ `$_POST`
- Dùng `template_redirect` + `ob_start()` + regex để xóa inline CSS của core
- Tạo rewrite rules cho `product_cat` trên mỗi lần `init`
- Redirect URL cũ bằng `wp_redirect()` ở tầng theme
- Ghi log WooCommerce trực tiếp trong theme

Đây là kiểu code có thể chạy, nhưng không phù hợp với WordPress convention cho theme dài hạn. Phần bootstrap nên gọn hơn, còn logic nghiệp vụ nên tách ra module/class rõ ràng.

### 2) Logic rewrite `product_cat` có rủi ro cao
Đoạn xử lý trong `functions.php` đang:

- Lấy toàn bộ term của `product_cat`
- Tạo rewrite rule cho từng term
- Xóa base `/product-category/`
- Redirect URL cũ sang URL mới

Rủi ro chính:

- Dễ xung đột slug với page, post hoặc custom post type
- Tăng độ phức tạp của query rules
- Có thể phát sinh 404 hoặc redirect loop khó debug
- Khi số lượng category lớn, rule sẽ phình ra

Đây là một điểm cần review lại kỹ trước khi mở rộng theme.

### 3) Theme đang chứa code thuộc plugin territory
Theme có các phần logic nên nằm ở plugin hoặc module riêng:

- `add_shortcode()` trong `inc/woocommerces/archive.php`
- xử lý order tracking, checkout, warranty, logging WooCommerce
- custom REST/AJAX logic
- nghiệp vụ liên quan đến lưu meta và trạng thái đơn hàng

Theo convention WordPress, theme nên tập trung vào hiển thị. Logic nghiệp vụ nên tách ra plugin hoặc ít nhất là module độc lập.

## Vấn đề mức độ trung bình

### 4) Metadata theme chưa đồng bộ
Trong `style.css`:

- `Theme Name: Twmp Phonghoa`
- `Text Domain: twmp-phonghoa`

Trong code lại dùng `twmp-ath`.

Cần đồng bộ tên theme, text domain, và các chuỗi dịch để tránh lỗi i18n, nhầm namespace, và khó bảo trì.

### 5) `header.php` đang load Google Fonts trực tiếp
`header.php` đang chèn:

- `preconnect` tới Google Fonts
- link stylesheet Google Fonts trực tiếp trong `<head>`
- dùng thêm `rel="preload"` trên cùng một thẻ `link`

Điểm cần lưu ý:

- Cách load này phụ thuộc bên thứ ba
- Dễ ảnh hưởng TTFB và render
- Có thể gây vấn đề về hiệu năng và kiểm soát cache

Nên cân nhắc self-host font hoặc tối thiểu hóa số font weight đang load.

### 6) Dùng inline `onclick` trong header
Trong `header.php`, nút đổi ngôn ngữ đang dùng inline JS để toggle menu.

Điểm này:

- không thân thiện với convention WordPress
- khó bảo trì
- khó audit CSP
- khó tách logic UI ra file JS

Nên chuyển sang event listener trong JS.

### 7) Một số template đang trộn trách nhiệm
Nhiều file template vừa:

- parse data
- query dữ liệu
- render HTML

Ví dụ:

- `templates/page-blog.php`
- `templates/content/flexible.php`
- một số section template trong `templates/sections/*`

Cách này chạy được nhưng làm giảm khả năng tái sử dụng, cache, và test.

## Vấn đề từ theme-check/local scan

Các cảnh báo từ kiểm tra nội bộ cho thấy thêm vài điểm cần dọn:

- Có `title=""` rỗng ở một số link trong template
- Có template dùng `echo $...` chưa được escape chặt theo context
- Có một số chỗ dùng global `$_SERVER` và `readfile`
- Có pattern code procedural lẫn với class-based bootstrap

Không phải toàn bộ đều là bug, nhưng đủ để đưa vào danh sách review và xử lý dần.

## Kết luận
Theme có nền tảng tốt, nhưng hiện tại cần siết lại convention WordPress:

- gọn bootstrap
- tách plugin-territory ra ngoài theme
- chuẩn hóa escape/sanitize
- giảm side effect global
- đồng bộ naming và text domain

Ưu tiên refactor `functions.php`, rewrite `product_cat`, và phần WooCommerce/custom checkout trước.
