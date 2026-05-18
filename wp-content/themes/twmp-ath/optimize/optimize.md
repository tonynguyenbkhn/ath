# Kế hoạch tối ưu hiệu năng theme Twmp Ath

## Mục tiêu
Tối ưu theme theo hướng:

- giảm thời gian tải lần đầu
- cải thiện LCP, CLS, INP
- giảm JS/CSS không cần thiết
- lazyload đúng chỗ, nhất là Swiper và image nặng
- giảm số query và chi phí render trên server
- cải thiện điểm Google PageSpeed

## Ưu tiên cao

### 1) Lazyload cho các section Swiper
Hiện theme có component Swiper dùng chung cho nhiều section như:

- `logo-slider`
- `testimonials`
- các section khác có carousel/slider

Hướng tối ưu:

- chỉ render slide đầu tiên hoặc vài slide đầu ở chế độ critical
- phần còn lại lazy hydrate khi section đi vào viewport
- nếu section nằm dưới fold thì không khởi tạo Swiper ngay lúc page load
- với ảnh trong slide, ưu tiên `loading="lazy"` cho slide ngoài viewport
- với slide đầu tiên ở hero, dùng `fetchpriority="high"` nếu đó là ảnh LCP

Mục tiêu là giảm JS khởi tạo ban đầu và giảm blocking time.

### 2) Tối ưu query
Các template đang có nhiều nơi query dữ liệu động, đặc biệt là:

- related posts
- grid/list section
- flexible content sections
- WooCommerce fragments

Nên áp dụng:

- `no_found_rows => true` khi không cần pagination count
- `update_post_meta_cache => false` khi không cần meta toàn bộ
- `update_post_term_cache => false` khi không cần term cache
- chỉ lấy fields cần thiết khi phù hợp
- tránh query lặp trong loop hoặc trong nhiều section cùng trang
- cache transient/object cache cho dữ liệu ít đổi

Với related posts, nên cache theo post ID và category set.

### 3) Giảm inline CSS/JS không cần thiết
Hiện theme đang có:

- inline CSS critical
- inline JS cho một số tính năng WooCommerce
- output buffering để xóa inline CSS core

Nên tối ưu theo hướng:

- giữ inline chỉ cho critical thật sự nhỏ
- chuyển logic lặp lại sang file JS/CSS đã build
- tránh regex output buffer để sửa HTML sau render
- chỉ enqueue bundle theo trang cần dùng

Việc này giúp giảm HTML size và giảm rủi ro phá markup.

### 4) Tối ưu font loading
Trong `header.php` theme đang load Google Fonts trực tiếp.

Nên ưu tiên:

- self-host font nếu có thể
- chỉ load đúng weight/style thật sự dùng
- hạn chế số family
- preload font quan trọng nếu self-host
- đảm bảo font fallback hợp lý để giảm CLS

Nếu vẫn dùng Google Fonts, cần giảm số request và kiểm tra tác động đến PageSpeed.

### 5) Tối ưu ảnh
Trong các section hiển thị ảnh lớn như hero, about, banner:

- ảnh trên fold nên dùng `loading="eager"` có kiểm soát
- ảnh ngoài fold dùng `loading="lazy"`
- thêm `decoding="async"`
- dùng đúng size ảnh thay vì full size khi không cần
- đảm bảo `srcset` và `sizes` đúng
- với ảnh chính của hero, cân nhắc `fetchpriority="high"`

Ngoài ra nên kiểm tra:

- ảnh background lớn có thể chuyển sang image tag nếu cần LCP tốt hơn
- icon SVG inline có thể giữ, nhưng ảnh raster nên tối ưu kích thước

## Tối ưu theo component

### 6) Tối ưu `templates/components/swiper.php`
Component Swiper hiện đang render tất cả slide ngay lập tức.

Đề xuất:

- thêm chế độ `defer_init` hoặc `lazy_mount`
- cho phép render placeholder/skeleton trước khi JS kích hoạt
- chỉ tạo pagination/nav khi thực sự cần
- truyền config gọn hơn, tránh JSON quá lớn trên DOM
- nếu section không visible lúc đầu trang, không cần khởi tạo ngay

### 7) Tối ưu `templates/components/image.php`
Component ảnh nên chuẩn hóa:

- set `loading` theo ngữ cảnh
- set `decoding="async"`
- hỗ trợ `fetchpriority`
- tránh fallback alt không đúng ngữ cảnh
- chỉ dùng image size phù hợp

### 8) Tối ưu section data trong flexible content
`templates/content/flexible.php` hiện đóng vai trò renderer cho toàn bộ layout.

Nên thêm:

- cache registry layout nếu chưa cache
- tách data mapping khỏi render nếu layout phức tạp
- chỉ gọi field cần thiết cho layout hiện tại
- giảm việc build data không dùng tới

## Tối ưu WooCommerce

### 9) Giảm chi phí single product và archive
Theme hiện có custom WooCommerce khá nhiều.

Nên xem lại:

- query biến thể sản phẩm
- related products
- fragment mini cart
- HTML sinh ra bằng `ob_start()` trên nhiều template

Đề xuất:

- cache dữ liệu biến thể nếu có thể
- tách phần nặng khỏi render chính
- chỉ load script WooCommerce khi cần
- tránh gọi hàm nặng trong loop nhiều lần

### 10) Tối ưu checkout và page đặc thù
Nếu checkout đang có nhiều logic location/city/state hoặc UI select nặng:

- gom dữ liệu về một nguồn
- dùng cache cho lookup danh mục địa phương
- tránh gọi AJAX lặp lại không cần thiết
- chỉ load script cho checkout ở trang checkout

## Tối ưu Google PageSpeed

### 11) Giảm LCP
Ưu tiên:

- hero image tối ưu kích thước và preload đúng
- font không chặn render
- JS khởi tạo slider chậm hơn phần hero
- hạn chế overlay/animation làm chậm paint

### 12) Giảm CLS
Ưu tiên:

- khai báo width/height cho ảnh
- reserve space cho slider/nav/pagination
- tránh nội dung được inject muộn làm layout nhảy
- đảm bảo font fallback gần với font chính

### 13) Giảm INP
Ưu tiên:

- trì hoãn JS không cần thiết
- tách xử lý DOM nặng sang `requestAnimationFrame`
- giảm listener global
- tối ưu các section có nhiều event handler

## Danh sách hành động đề xuất

1. Tách lazy init cho Swiper theo viewport.
2. Tối ưu query của các section phổ biến bằng cache và tham số query đúng.
3. Dọn asset loading: giảm inline, tự host font, load script theo trang.
4. Chuẩn hóa image component để hỗ trợ `loading`, `decoding`, `fetchpriority`.
5. Rà lại WooCommerce templates để bỏ phần tính toán lặp.
6. Rà lại critical CSS và bỏ regex xóa CSS core sau render.
7. Đo lại bằng PageSpeed/Lighthouse sau từng bước để tránh tối ưu ngược.

## Kết luận
Theme có khả năng tối ưu tốt hơn đáng kể nếu giảm phần render nặng ở đầu trang, lazyload đúng cho slider, cache query, và siết lại asset pipeline. Điểm cần làm trước nhất là Swiper, hero image, và các query động trong flexible content/WooCommerce.
