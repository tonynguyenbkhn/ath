# Plan Optimize Theme twmp-ath

## Tong ket phan tich

Ba file `optimize.md`, `optimize-v1.md`, va `optimize-v2.md` khong con khop hoan toan voi trang thai code hien tai.

- `templates/blocks` duoc nhac trong `optimize-v2.md` hien khong con ton tai.
- Flexible content da duoc cai thien: `templates/content/flexible.php` hien goi registry qua `templates/flexible/registry.php`.
- Rewrite `product_cat` nguy hiem trong `functions.php` hien dang bi comment, nen khong con la rui ro active lon nhat.
- Mot so security/coding issue trong `optimize.md` da duoc cai thien, vi du `class-assets-theme.php` da dung `protected`.
- Van con cac van de dang uu tien: `functions.php` co nhieu side effect global, CORS REST mo toan cuc, debug `error_log()` WooCommerce con active, WooCommerce checkout/single/archive van la file lon, asset loading con inline nhieu.

## Phase 1: On dinh nen theme

Muc tieu: giam rui ro production truoc khi refactor lon.

1. Don `functions.php`
   - Tach CORS, block style/pattern, image settings, debug WooCommerce thanh module/class rieng.

==================================================

• Đã tách các phần đó khỏi functions.php thành class riêng:

- inc/classes/class-cors-theme.php: REST CORS headers.
- inc/classes/class-blocks-theme.php: register_block_style() và register_block_pattern().
- inc/classes/class-media-theme.php: image settings, gồm remove image sizes và wp_img_tag_add_auto_sizes.
- inc/classes/class-woo-debug-theme.php: debug hooks WooCommerce order/checkout.

Đã cập nhật inc/classes/class-twmp-theme.php để khởi tạo các module mới, và gỡ các hook tương ứng khỏi functions.php để tránh đăng ký trùng.

==================================================

   - Xoa hoac tat debug `error_log()` dang active o checkout/order flow.
   - Thay `ob_start()` regex xoa core inline CSS bang cach xu ly enqueue/theme support ro rang hon.

2. Siet REST/CORS
   - Khong dung `Access-Control-Allow-Origin: *` toan cuc.
   - Voi REST route public, them validate params ro rang.
   - Route nao khong that su public thi them nonce/capability check.
==================================================
Thay đổi chính:

- wp-content/themes/twmp-ath/inc/classes/class-cors-theme.php: bỏ CORS *, thay bằng allowlist origin từ home_url(), site_url(), admin_url(), và optional TWMP_ALLOWED_REST_ORIGINS.
- Disable CORS mặc định của core bằng remove_filter('rest_pre_serve_request', 'rest_send_cors_headers') để tránh WordPress mirror mọi origin.
- wp-content/themes/twmp-ath/inc/classes/class-calendar-theme.php: route calendar-events dùng WP_REST_Server::READABLE, permission callback có tên, thêm validate/sanitize args.
- wp-content/themes/twmp-ath/inc/classes/class-views-theme.php: route update_post_views dùng WP_REST_Server::CREATABLE, thêm schema cho post_id.
- wp-content/themes/twmp-ath/inc/classes/class-rest-api-theme.php: route filter_theme được thêm permission callback có tên và args validation. Class này hiện không thấy được khởi tạo trong bootstrap, nên mình không tự bật route
  mới.

==================================================
3. Kiem tra template path cu
   - Tim toan bo `templates/blocks/...` con sot.
   - Doi sang cau truc moi: `templates/sections`, `templates/components`, `templates/woocommerces`.

## Phase 2: Chuan hoa WooCommerce

Muc tieu: giam file procedural lon va tranh bug khi WooCommerce update.

1. Tach `inc/woocommerces/checkout.php`
   - `Checkout_Fields`
   - `Checkout_Validation`
   - `Checkout_Order_Meta`
   - `Checkout_Ajax_Or_Rest`
   - `Buy_Now`

2. Tach `single.php`, `archive.php`
   - Gom hook registration vao module rieng.
   - Chuyen inline CSS/JS sang asset bundle.
   - Cache cac query nang nhu promotions, artist/event/product lookup neu co.

3. Chuan hoa data tinh/huyen/xa neu con dung
   - Mot nguon du lieu duy nhat.
   - Uu tien REST hoac local JSON cacheable, khong song song nhieu co che.

## Phase 3: Hoan thien Flexible/Section Architecture

Muc tieu: giu huong refactor hien tai nhung lam sach hon.

1. Giu `templates/flexible/registry.php`, nhung chuan hoa moi section:
   - `section.php`
   - `item.php` neu can
   - `config.php` hoac registry entry rieng neu section phuc tap
   - `README.md`/`plan.md` chi giu khi that su co gia tri

2. Tach query khoi view
   - Section nhu class/workshop/event/product grid khong nen vua query vua render.
   - Dua query/data prepare vao helper/module, template chi nhan `$args`.

3. Thong nhat naming
   - Section folder dung kebab-case.
   - Component dung ten generic: `button.php`, `heading.php`, `swiper.php`.
   - Woo fragment de trong `templates/woocommerces`.

## Phase 4: Asset & Performance

Muc tieu: giam payload va tranh inline qua nhieu.

1. Ra lai `class-assets-theme.php`
   - Inline chi critical CSS nho.
   - Product-only inline style/script chuyen vao `src/woocommerce.js` hoac file module rieng.
   - Chi enqueue Woo assets tren trang Woo can thiet.

2. Build production on dinh
   - Kiem tra Webpack/PurgeCSS neu co dung.
   - Dam bao dynamic class tu JS, Swiper, Fancybox, WooCommerce khong bi purge sai.

3. Font/image cleanup
   - Chi giu font weight that su dung.
   - Kiem tra anh/design assets trong `templates/sections/**/design` co can commit khong.

## Phase 5: Verification

Sau moi phase:

- Chay `php -l` cho PHP file da sua.
- Chay `npm run build` tu `wp-content/themes`.
- Test thu cong:
  - Home/page flexible
  - Product single
  - Product archive/category
  - Checkout
  - Contact/form section
  - Mobile responsive

## Uu tien thuc thi

Nen lam Phase 1 truoc, vi no giam rui ro ngay ma chua dung sau WooCommerce. Sau do moi refactor `checkout.php`, vi file nay lon va anh huong truc tiep den flow mua hang.
