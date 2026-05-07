<?php

if (!defined('ABSPATH')) {
    exit;
}

get_template_part('templates/blocks/album-feedback', null, ['enable_container' => false]);

if (is_active_sidebar('footer-top')) {
?>
    <div class="footer-top">
        <?php dynamic_sidebar('footer-top'); ?>
    </div><!-- End Footer -->
<?php
}
?>
<?php
if (is_active_sidebar('footer-primary')) {
?>
    <footer id="colophon" class="site-footer">
        <?php dynamic_sidebar('footer-primary'); ?>
    </footer><!-- End Footer -->
<?php
}
?>
<?php
if (is_active_sidebar('footer-absolute')) {
?>
    <div class="footer-absolute">
        <?php dynamic_sidebar('footer-absolute'); ?>
    </div>
<?php
}
?>
<?php
$dataStickyContact['items'] = get_field('sticky_links', 'option') ? get_field('sticky_links', 'option') : [];
get_template_part('templates/sections/back-to-top/section', null, []);
get_template_part('templates/sections/sticky-contact/section', null, $dataStickyContact);
get_template_part('template-parts/footers/modal-search-form', null, []);
get_template_part('template-parts/footers/modal-popup-welcome', null, []);
// get_template_part('template-parts/footers/mini-cart', null, []);
// get_template_part('templates/blocks/menu-mobile-footer', null, []);

if (class_exists('WooCommerce') && (is_shop() || is_product_taxonomy())) {
?>
    <script>
        document.addEventListener('facetwp-loaded', function() {
            const dateFacet = document.querySelector('.facetwp-facet-date_time');

            if (!dateFacet) return;

            const minInput = dateFacet.querySelector('.facetwp-date-min.fdate-alt-input');
            const maxInput = dateFacet.querySelector('.facetwp-date-max.fdate-alt-input');

            if (minInput) {
                minInput.placeholder = 'From date - To date';
            }

            if (maxInput) {
                maxInput.placeholder = 'To date';
            }
        });

        document.addEventListener('facetwp-refresh', function() {
            if (!FWP.loaded) return;

            document.body.classList.add('facetwp-is-loading');
        });

        document.addEventListener('facetwp-loaded', function() {
            document.body.classList.remove('facetwp-is-loading');
        });
    </script>
<?php
}

?>

<script>
    document.addEventListener("DOMContentLoaded", function() {
        document.querySelectorAll(".lang-select").forEach(function(select) {

            const wrapper = select.closest(".lang-wrap");

            // CHECK tồn tại
            if (!wrapper) return;

            const icons = {
                "English": "/wp-content/uploads/english.svg",
                "Viet Nam": "/wp-content/uploads/vietname.svg",
                "French": "/wp-content/uploads/french.svg"
            };

            const custom = document.createElement("div");
            custom.className = "lang-custom";

            const current = document.createElement("div");
            current.className = "lang-current";
            current.innerHTML = "Select language";

            const options = document.createElement("div");
            options.className = "lang-options";

            Array.from(select.options).forEach(function(option) {
                if (!option.value) return;

                const item = document.createElement("div");
                item.className = "lang-option";
                item.dataset.value = option.value;

                item.innerHTML = `
        <img src="${icons[option.text]}" alt="">
        <span>${option.text}</span>
      `;

                item.addEventListener("click", function() {
                    select.value = option.value;

                    current.innerHTML = `
          <img src="${icons[option.text]}" alt="">
          <span>${option.text}</span>
        `;

                    custom.classList.remove("is-open");
                });

                options.appendChild(item);
            });

            current.addEventListener("click", function() {
                custom.classList.toggle("is-open");
            });

            document.addEventListener("click", function(e) {
                if (!custom.contains(e.target)) {
                    custom.classList.remove("is-open");
                }
            });

            custom.appendChild(current);
            custom.appendChild(options);

            wrapper.appendChild(custom);
        });
    });
</script>

<?php wp_footer(); ?>

<script>
</script>

</body>

</html>