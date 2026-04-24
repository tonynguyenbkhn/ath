document.addEventListener('DOMContentLoaded', function () {
    // Kiểm tra có phải trang sản phẩm hay không (WooCommerce thường dùng class 'single-product')
    if (document.body.classList.contains('single-product')) {
        const titleElement = document.querySelector('h1.product_title.entry-title');
        const inputElement = document.querySelector('input[name="product-name"]');

        if (titleElement && inputElement) {
            inputElement.value = titleElement.textContent.trim();
        }
    }
});

jQuery(document).ready(function ($) {
    const defaultPriceHtml = $(".entry-summary-wrapper .price").html();

    $('form.variations_form').on('show_variation', function (event, variation) {
        const newPriceHtml = variation.price_html;

        $(".entry-summary-wrapper .price").html(newPriceHtml).show();

        $(".woocommerce-variation-price").hide();
    });

    $('form.variations_form').on('reset_data', function () {
        $(".entry-summary-wrapper .price").html(defaultPriceHtml).show();
        $(".woocommerce-variation-price").show();
    });
});