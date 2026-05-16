# Customize FacetWP cho product search fallback

Tài liệu này mô tả các custom hook / helper đang dùng để làm cho FacetWP hoạt động đúng trên URL search sản phẩm, đặc biệt case `?s=...&post_type=product` nhưng không có kết quả.

Mục tiêu chính:
- Giữ nguyên UI hiện tại của shop / search fallback.
- Khi search product không có kết quả, vẫn render lại toàn bộ catalog product.
- Đảm bảo FacetWP vẫn đọc đúng main query và render được facet options, thay vì bị kẹt ở trạng thái `0` kết quả.

## Luồng tổng quát

1. `pre_get_posts` phát hiện search product rỗng.
2. Query chính được chuyển sang fallback product catalog.
3. `the_posts` bơm lại danh sách product fallback để loop WooCommerce vẫn render.
4. Các filter của FacetWP được ép dùng cùng tập product fallback, đồng thời bỏ các query vars gây nhiễu như `s`, `taxonomy`, `term`, `product_cat`.

## Các function custom

### `twmp_is_product_search_page()`

Mục đích:
- Xác định request hiện tại có phải search product hay không.
- Hàm chỉ trả về `true` khi:
  - đang là `is_search()`, và
  - `post_type` có giá trị `product`.

Vai trò:
- Là điều kiện nền để nhận diện shop/search catalog page.
- Được dùng bởi `twmp_is_shop_catalog_page()` để quyết định có render layout FacetWP shop hay không.

---

### `twmp_is_shop_catalog_page()`

Mục đích:
- Xác định trang nào phải dùng layout shop của theme.
- Trả về `true` cho:
  - trang shop,
  - trang product category,
  - hoặc product search page.

Vai trò:
- Dùng ở phần render layout để bọc markup shop + facet.
- Không trực tiếp thay đổi query, chỉ quyết định hiển thị.

---

### `twmp_is_product_search_fallback()`

Mục đích:
- Kiểm tra hiện tại có đang ở trạng thái fallback của product search hay không.

Nguồn trạng thái:
- `GLOBALS['twmp_product_search_fallback']`
- hoặc query var `twmp_product_search_fallback`

Vai trò:
- Là cờ dùng chung cho cả WordPress loop và FacetWP.
- Dùng để biết lúc nào cần bỏ `s` và các query vars archive/search gây nhiễu.

---

### `twmp_product_search_has_results($search_term)`

Mục đích:
- Kiểm tra nhanh một từ khóa search product có ra kết quả hay không.

Chi tiết:
- Dùng `WP_Query` riêng để search trên `post_type=product`.
- Có cache nội bộ theo `search_term` để tránh query lặp trong cùng request.

Vai trò:
- Là bước xác định fallback.
- Nếu search term không có product nào khớp, các hook tiếp theo sẽ chuyển sang catalog fallback.

Lưu ý:
- Query này không dùng `suppress_filters`, để kết quả kiểm tra phản ánh đúng logic search thực tế của site.

---

### `twmp_get_fallback_product_ids()`

Mục đích:
- Lấy danh sách toàn bộ product publish để dùng làm fallback.

Chi tiết:
- Query product với:
  - `post_type=product`
  - `post_status=publish`
  - `posts_per_page=-1`
  - `orderby=date`
  - `order=DESC`
  - `fields=ids`

Vai trò:
- Là nguồn dữ liệu fallback chung cho:
  - main query,
  - `the_posts`,
  - `facetwp_pre_filtered_post_ids`,
  - `facetwp_filtered_post_ids`.

Lưu ý:
- Có cache tĩnh trong hàm để tránh query lặp trong một request.

---

### `twmp_flag_empty_product_search_query($query)`

Hook:
- `add_action('pre_get_posts', 'twmp_flag_empty_product_search_query', 20);`

Mục đích:
- Phát hiện main query là product search nhưng không có kết quả.
- Chuyển query đó sang trạng thái fallback ngay từ sớm.

Hành vi:
- Nếu là admin, không phải main query, hoặc không phải search thì bỏ qua.
- Nếu query không phải product search thì bỏ qua.
- Nếu search term có kết quả thì giữ nguyên search bình thường.
- Nếu search term rỗng kết quả thì:
  - set cờ `twmp_product_search_fallback`
  - lưu lại search term vào `twmp_product_search_term`
  - xóa `s`
  - set `post_type=product`
  - set các tham số fallback như `posts_per_page=-1`, `no_found_rows=true`, `orderby=date`, `order=DESC`
  - reset `paged=1`
  - set global fallback flag

Vai trò:
- Đây là hook quan trọng nhất để main query đi đúng hướng trước khi FacetWP đọc nó.
- Giúp FacetWP preload nhìn thấy một query product hợp lệ thay vì query search rỗng.

---

### `twmp_expand_empty_product_search_results($posts, $query)`

Hook:
- `add_filter('the_posts', 'twmp_expand_empty_product_search_results', 20, 2);`

Mục đích:
- Nếu main query product search vẫn rỗng ở stage `the_posts`, bơm lại fallback products để WooCommerce loop vẫn có dữ liệu render.

Hành vi:
- Chỉ áp dụng cho main search query của product.
- Nếu `$posts` đã có dữ liệu thì giữ nguyên.
- Nếu rỗng thì:
  - lấy product IDs fallback,
  - cập nhật `found_posts`, `post_count`, `max_num_pages`,
  - set lại `posts_per_page`, `no_found_rows`, `paged`,
  - set cờ fallback,
  - đồng bộ loop props của WooCommerce bằng `wc_set_loop_prop()`

Vai trò:
- Đây là lớp bảo vệ cho loop WooCommerce.
- Dù query ban đầu rỗng, template vẫn render được danh sách product fallback.

---

### `twmp_force_facetwp_fallback_ids($post_ids, $renderer)`

Hook:
- `add_filter('facetwp_pre_filtered_post_ids', 'twmp_force_facetwp_fallback_ids', 20, 2);`

Mục đích:
- Khi FacetWP chuẩn bị tập post IDs ban đầu, nếu đang ở product search fallback thì không để nó trả về mảng rỗng.

Hành vi:
- Chỉ chạy khi `twmp_is_product_search_fallback()` là `true`.
- Nếu FacetWP đã có `post_ids` thì giữ nguyên.
- Nếu rỗng thì trả về toàn bộ fallback product IDs.

Vai trò:
- Là lớp bảo hiểm cho bước prefilter của FacetWP.
- Hữu ích khi FacetWP nội bộ sinh ra danh sách rỗng trước khi apply facet filters.

---

### `twmp_force_facetwp_filtered_fallback_ids($post_ids, $renderer)`

Hook:
- `add_filter('facetwp_filtered_post_ids', 'twmp_force_facetwp_filtered_fallback_ids', 20, 2);`

Mục đích:
- Bảo vệ tầng cuối của FacetWP sau khi các facet đã được áp dụng.

Hành vi:
- Chỉ chạy khi đang fallback và `$renderer` hợp lệ.
- Nếu `$post_ids` là rỗng hoặc là placeholder `[0]` thì kiểm tra các facet đang chọn:
  - nếu có facet nào đang được chọn thì giữ nguyên kết quả hiện tại,
  - nếu không có facet nào được chọn thì trả về toàn bộ fallback product IDs.

Vai trò:
- Giúp FacetWP không rơi về trạng thái `0` khi search fallback chưa có filter chọn.
- Đây là lớp bảo hiểm cuối cùng cho output facet.

---

### `twmp_adjust_facetwp_product_search_query_args($query_args, $renderer)`

Hook:
- `add_filter('facetwp_query_args', 'twmp_adjust_facetwp_product_search_query_args', 20, 2);`

Mục đích:
- Chỉnh query args của FacetWP để nó dùng đúng catalog fallback khi đang ở product search fallback.

Hành vi:
- Chỉ xử lý khi query hiện tại là product search.
- Chỉ can thiệp khi fallback đang active.
- Khi fallback:
  - set global fallback flag,
  - xóa `s`,
  - xóa `twmp_product_search_fallback`,
  - xóa `product_cat`,
  - xóa `taxonomy`,
  - xóa `term`,
  - ép query về:
    - `post_type=product`
    - `post_status=publish`
    - `ignore_sticky_posts=true`
    - `posts_per_page=-1`
    - `no_found_rows=true`
    - `orderby=date`
    - `order=DESC`

Vai trò:
- Đây là phần quan trọng nhất để FacetWP không bị “kẹt” bởi URL search/archive vars cũ.
- Nó làm cho FacetWP render trên cùng tập product fallback giống main loop.

---

## File render liên quan

### `woocommerce/archive-product.php`

Vai trò:
- Dùng main WooCommerce query để render products.
- Khi đang fallback search, vẫn giữ header “Don’t have Result” như trải nghiệm cũ.

Điểm quan trọng:
- Template không còn tạo `WP_Query` phụ riêng cho fallback.
- Điều này giúp FacetWP và main loop không bị lệch luồng query.

---

## Ghi chú triển khai

- Nếu sửa tiếp logic search/facet, ưu tiên sửa trong `archive.php` trước.
- Không tạo thêm query phụ trong template nếu mục tiêu là giữ FacetWP và WooCommerce đi chung một main query.
- Khi debug, kiểm tra theo thứ tự:
  1. main query có đang fallback không,
  2. `the_posts` có trả fallback posts không,
  3. `facetwp_query_args` có bị xóa `s` và `taxonomy/term/product_cat` chưa,
  4. `FWP_JSON.preload_data` có `total_rows > 0` chưa.

## Flow debug nhanh

```text
URL /all/?s=acv&post_type=product
        |
        v
pre_get_posts
        |
        |-- nếu search có kết quả -> giữ nguyên query product search
        |
        |-- nếu search rỗng -> bật twmp_product_search_fallback
        |                         set query sang product catalog sạch
        v
the_posts
        |
        |-- nếu posts rỗng -> bơm toàn bộ product fallback
        |                     set wc loop props
        v
FacetWP render
        |
        |-- facetwp_query_args -> bỏ s, product_cat, taxonomy, term
        |                         ép query về product fallback
        |
        |-- facetwp_pre_filtered_post_ids / facetwp_filtered_post_ids
        |   -> chặn trường hợp FacetWP tự rơi về rỗng
        v
FWP_JSON.preload_data
        |
        |-- total_rows > 0
        |-- facet categories / filters render đúng
```
