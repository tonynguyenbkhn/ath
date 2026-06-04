Mô tả animation cho trang chủ (GSAP + ScrollTrigger)

TL;DR
Dùng GSAP + ScrollTrigger để tạo các animation nhẹ, GPU-accelerated cho từng section trên trang chủ. Viết logic thực thi trong home-animation.js theo convention hiện có; import vào src/frontend.js. Tôn trọng prefers-reduced-motion.

Quy tắc chung

Kiểm tra reduced-motion: nếu window.matchMedia('(prefers-reduced-motion: reduce)').matches → bỏ qua animation.
Dùng transform + opacity (translate3d, scale, rotate) để tối ưu GPU.
Kiểm tra tồn tại selector trước khi animate.
Dùng GSAP batch cho danh sách lớn, matchMedia cho breakpoint khác nhau.
Hủy/refresh ScrollTrigger trên resize nếu cần.

Hero Banner (.hero-banner, .hero-banner__left, .hero-banner__right, .hero-cta)

Mục tiêu: thu hút, sequence mượt.
Kiểu: từ opacity 0 + translateY(20px) → opacity 1 + translateY(0).
Thực hiện: timeline ở trang đầu (không cần ScrollTrigger):
eyebrow/logo → title → subtitle → CTA.
từng item duration 0.6s, stagger 0.12s, ease power3.out.
Mobile: duration giảm ~0.45s, giảm stagger.
Reduced-motion: hiển thị trạng thái cuối cùng.

Product Category Grid (.product-cat-grid, .product-cat-grid__item)

Mục tiêu: reveal staggered khi section vào viewport.
Kiểu: opacity 0 → 1, translateY(18px), scale 0.98 → 1.
Thực hiện: gsap.fromTo(items, {...}, {..., stagger:0.12, duration:0.6, scrollTrigger:{trigger: section, start:'top 80%'}}).
Nếu nhiều card, dùng gsap.batch() để tối ưu.
Decorative Shapes (.section-shape--square, .section-shape--triangle)

Mục tiêu: subtle float/parallax.
Kiểu: nhẹ translateY hoặc slow rotate, looped yoyo (duration 6–10s) bằng transforms.
Không block render; thêm will-change: transform.
Mobile/Reduced-motion: disable loop, chỉ reveal opacity.

About Us (.about-us__media, .about-us__content, .about-us__stats)

Mục tiêu: media và nội dung reveal đối xứng, stats count-up.
Kiểu: media slide-in từ trái, content slide-in từ phải; stats fade + number tween (GSAP).
ScrollTrigger start top 85%, toggleActions play none none none.
Numbers: gsap.to({val:0}, {val: target, duration:1.2, onUpdate:()=> el.innerText = Math.round(this.val)}).

Show Event / Carousels (.show-event, Swiper .swiper-slide)

Mục tiêu: animate các phần tử của slide khi active.
Cách: hook Swiper events (slideChange, slideChangeTransitionEnd) → play small timeline for active slide (title fade + image scale 1 → 1.02).
Initial load: animate first visible slide.
Tránh animate nặng trong loop; transform/opacity only.

Class Workshop Section (.class-workshop-section)

Mục tiêu: reveal cards + subtle horizontal parallax trên background/decoratives.
Cách: ScrollTrigger với scrub: 0.6 cho các decorative translateX; cards reveal bằng fade-up stagger.
Mobile: chuyển parallax sang simple fade.

For Schools Section (.for-school-section)

Mục tiêu: highlight features.
Kiểu: fade/slide từng item; icon scale on hover (CSS transform).
Use gsap.from with stagger khi section vào view.

Footer / CTA cuối trang (.site-footer, .footer-cta)

Mục tiêu: reveal nhẹ khi đến gần cuối trang.
Kiểu: fade-in, start top 90%.