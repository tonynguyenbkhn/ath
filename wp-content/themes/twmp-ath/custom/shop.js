jQuery(document).ready(function ($) {
    $('body').on('click', '.filterModule .isures-wd--title', function (event) {
        event.preventDefault();
        var position_this = $(this).position();
        var clone_attribute = $(this).next().clone();
        var height_this = $(this).outerHeight();
        var top_this = height_this + position_this.top + 13;
        var this_width = $(this).outerWidth();
        var filterModule = $(this).closest('.filterModule').outerWidth();
        $(this).closest('.filterModule').find('.isures-wd--title').removeClass('active');
        $(this).addClass('active');
        var outerWidth = filterModule / 2;
        var position = position_this.left;
        var left_attr = position + (this_width / 2);
        if (position_this.left > outerWidth) {
            position = position_this.left + this_width;
            var right = filterModule - position;
            $(this).closest('.filterModule').children('.products-attribute').html(clone_attribute).addClass('show-attribute attribute-right').attr('style', '').css({
                'right': right,
                'top': top_this
            })
        } else {
            $(this).closest('.filterModule').children('.products-attribute').html(clone_attribute).removeClass('attribute-right').addClass('show-attribute').attr('style', '').css({
                'left': position_this.left,
                'top': top_this
            })
        }
        if (jQuery(window).width() < 768) {
            $(this).closest('.filterModule').children('.products-attribute').css('--dhdhdk', left_attr + 'px')
        }
        $('body').addClass('show-attribute')
    });
    $(document).mouseup(function (e) {
        var container = $('.filter-products .products-attribute.show-attribute');
        if (!container.is(e.target) && container.has(e.target).length === 0 && $('body').hasClass('show-attribute') && e.which === 1) {
            container.removeClass('show-attribute');
            $('body').removeClass('show-attribute');
            $('.filterModule .isures-wd--title').removeClass('active')
        };
        var container2 = $('.filter-products .productssss-total.show-attributes');
        if (!container2.is(e.target) && container2.has(e.target).length === 0 && $('body').hasClass('show-attributes') && e.which === 1) {
            container2.removeClass('show-attributes');
            $('body').removeClass('show-attributes');
            $('.filterModule .button__filter-parent span').removeClass('active')
        }
        var container3 = $('.orderby-product .orderbys_price');
        if (!container3.is(e.target) && container3.has(e.target).length === 0 && $('body').hasClass('show-price') && e.which === 1) {
            container3.removeClass('active').children('.price').removeClass('show-attributes');
            $('body').removeClass('show-price');
            $('.orderby-product .orderbys_price').removeClass('active')
        }
    });
    $('body').on('click', '.filterModule .button__filter-parent span', function (event) {
        $(this).addClass('active').closest('.filterModule').children('.productssss-total').addClass('show-attributes');
        $('body').addClass('show-attributes')
    });
    $('body').on('click', '.orderby-product .orderbys_price', function (event) {
        $(this).toggleClass('active').children('.price').toggleClass('show-attributes');
        $('body').toggleClass('show-price')
    });
})