jQuery(function ($) {
    if (!String.prototype.getDecimals) {
        String.prototype.getDecimals = function () {
            var num = this,
                match = ('' + num).match(/(?:\.(\d+))?(?:[eE]([+-]?\d+))?$/);
            if (!match) {
                return 0;
            }
            return Math.max(0, (match[1] ? match[1].length : 0) - (match[2] ? +match[2] : 0));
        }
    }
    // Quantity "plus" and "minus" buttons
    $(document.body).on('click', '.plus, .minus', function () {
        var $qty = $(this).closest('.quantity').find('.qty'),
            currentVal = parseFloat($qty.val()),
            max = parseFloat($qty.attr('max')),
            min = parseFloat($qty.attr('min')),
            step = $qty.attr('step');

        // Format values
        if (!currentVal || currentVal === '' || currentVal === 'NaN') currentVal = 0;
        if (max === '' || max === 'NaN') max = '';
        if (min === '' || min === 'NaN') min = 0;
        if (step === 'any' || step === '' || step === undefined || parseFloat(step) === 'NaN') step = 1;

        // Change the value
        if ($(this).is('.plus')) {
            if (max && (currentVal >= max)) {
                $qty.val(max);
            } else {
                $qty.val((currentVal + parseFloat(step)).toFixed(step.getDecimals()));
            }
        } else {
            if (min && (currentVal <= min)) {
                $qty.val(min);
            } else if (currentVal > 0) {
                $qty.val((currentVal - parseFloat(step)).toFixed(step.getDecimals()));
            }
        }

        // Trigger change event
        $qty.trigger('change');
    });



    $('body').on('change', '.product-quantity input[type="number"]', function (event) {
        setTimeout(function () {
            $('button[name="update_cart"]').trigger('click');
        }, 500);
    });

    $('body').on('click', '.submit-thanh-toan', function (event) {
        $('#place_order').trigger('click');
    });

    /**
     * Helper function to load xã/phường (wards)
     * Usage: window.twmpLoadXaPhuong(maqh, selectedWard, '#ward-select')
     */
    window.twmpLoadXaPhuong = function (maqh, selectedWard = null, selectorOrElement = '.xa-phuong-select') {
        // Get nonce from checkout block settings
        const checkoutBlock = document.querySelector('[data-block="checkout-custom"]');
        let nonce = '';
        if (checkoutBlock) {
            try {
                const settings = JSON.parse(checkoutBlock.getAttribute('data-settings'));
                nonce = settings.nonce || '';
            } catch (e) {
                console.warn('Failed to parse checkout settings:', e);
            }
        }

        if (!maqh) {
            console.warn('maqh (district code) is required');
            return;
        }

        $.ajax({
            url: ajaxurl,
            method: 'POST',
            data: {
                action: 'get_xa_phuong_by_maqh',
                maqh: maqh,
                nonce: nonce
            },
            success: function (res) {
                if (res.success) {
                    const $wardSelect = $(selectorOrElement);
                    $wardSelect.empty();
                    $wardSelect.append($('<option>', { value: '', text: '-- Chọn xã/phường --' }));

                    res.data.forEach(function (item) {
                        const option = $('<option>', {
                            value: item.name,
                            text: item.name
                        });

                        if (item.name === selectedWard) {
                            option.prop('selected', true);
                        }

                        $wardSelect.append(option);
                    });
                }
            },
            error: function (xhr) {
                console.error('Failed to load xã/phường:', xhr);
            }
        });
    };
});