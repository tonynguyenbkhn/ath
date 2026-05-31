Trong template: archive-product.php:23 gọi

$has_products = woocommerce_product_loop();
$is_empty_search = twmp_is_empty_product_search_page();
=> header “Don't have Result” được hiển thị dựa trên $is_empty_search, không dựa trên $has_products.
Khi một search ban đầu không trả kết quả, hook pre_get_posts (hàm twmp_flag_empty_product_search_query) đánh dấu query là fallback và lưu term: archive.php:115.

Sau đó filter the_posts (hàm twmp_expand_empty_product_search_results) chạy và THAY THẾ mảng posts rỗng bằng danh sách sản phẩm “fallback” (từ twmp_get_fallback_product_ids()), đồng thời cập nhật WooCommerce loop props bằng wc_set_loop_prop('total', ...) v.v.: archive.php:161 và [..#L197-L201].

Kết quả: khi template gọi woocommerce_product_loop() / have_posts() sau đó, loop có sản phẩm (do the_posts đã trả về fallback posts và loop props được set) → $has_products là true, nên loop hiển thị sản phẩm. Nhưng $is_empty_search vẫn true (hàm kiểm tra ban đầu rằng search term không có kết quả), nên template hiển thị cả header “Don't have Result” và danh sách fallback products cùng lúc.

Chuỗi thời gian (simplified):

pre_get_posts: phát hiện search rỗng → set flag fallback. ([..#L115])
WP chạy query → posts rỗng.
the_posts filter: trả về fallback posts + set wc loop props. ([..#L161], [..#L197-L201])
Template: $is_empty_search = true (dựa trên trạng thái ban đầu), nhưng $has_products = true (dựa trên posts/loop hiện tại) → header “Don't have Result” + sản phẩm fallback.
Gợi ý ngắn nếu bạn muốn thay đổi hành vi:

Nếu muốn không hiển thị header “Don't have Result” khi fallback đang hiển thị sản phẩm, sửa điều kiện trong archive-product.php:23 thành ví dụ:
$is_empty_search = twmp_is_empty_product_search_page() && !twmp_is_product_search_fallback();
(vì twmp_is_product_search_fallback() true nghĩa đang dùng fallback → không cần header “empty”).