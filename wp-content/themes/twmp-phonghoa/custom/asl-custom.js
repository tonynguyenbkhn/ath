jQuery(document).ready(function ($) {
    jQuery(document).ready(function ($) {
        if (window.location.href.includes('create-agile-store') || window.location.href.includes('edit-agile-store')) {

            // Lưu lại giá trị ban đầu trước khi thay input
            const selectedStateName = $('#txt_state').val(); // "Hà Nội"
            const selectedCity = $('#txt_city').val();       // "Quận Ba Đình"

            // Replace input bằng select
            const $stateInput = $('#txt_state');
            const $cityInput = $('#txt_city');

            const $stateSelect = $('<select>', {
                id: 'txt_state',
                name: 'data[state]',
                class: 'form-control validate[required]'
            });

            const $citySelect = $('<select>', {
                id: 'txt_city',
                name: 'data[city]',
                class: 'form-control validate[required]'
            });

            $stateInput.replaceWith($stateSelect);
            $cityInput.replaceWith($citySelect);

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

            // Load tỉnh/thành phố
            $.ajax({
                url: ajaxurl,
                method: 'POST',
                data: {
                    action: 'get_tinh_tp_by_matp',
                    nonce: nonce
                },
                success: function (res) {
                    if (res.success) {
                        const arr = Object.entries(res.data).map(([key, name]) => ({
                            key: key,
                            name: name
                        }));

                        $stateSelect.append($('<option>', {
                            value: '',
                            text: '-- Chọn tỉnh/thành --'
                        }));

                        let selectedMatp = null;

                        arr.forEach(item => {
                            const option = $('<option>', {
                                value: item.name,     // hiển thị và lưu là "Hà Nội"
                                text: item.name,
                                'data-key': item.key  // dùng để load quận/huyện: "HANOI"
                            });

                            if (item.name === selectedStateName) {
                                option.prop('selected', true);
                                selectedMatp = item.key; // Lưu lại key để gọi loadCity
                            }

                            $stateSelect.append(option);
                        });

                        // Nếu có sẵn tỉnh → tự động load quận/huyện
                        if (selectedMatp) {
                            loadCity(selectedMatp, selectedCity);
                        }
                    }
                },
                error: function (xhr) {
                    console.error('Failed to load tỉnh/thành:', xhr);
                }
            });

            // Khi thay đổi tỉnh/thành thì load lại quận/huyện
            $stateSelect.on('change', function () {
                const matpKey = $(this).find('option:selected').data('key');
                loadCity(matpKey, null); // reset city
            });

            // Hàm load quận/huyện theo mã tỉnh
            function loadCity(matp, selectedCity = null) {
                $citySelect.empty();

                if (!matp) {
                    $citySelect.append($('<option>', { value: '', text: '-- Chọn quận/huyện --' }));
                    return;
                }

                $.ajax({
                    url: ajaxurl,
                    method: 'POST',
                    data: {
                        action: 'get_quan_huyen_by_matp',
                        matp: matp,
                        nonce: nonce
                    },
                    success: function (res) {
                        if (res.success) {
                            $citySelect.append($('<option>', { value: '', text: '-- Chọn quận/huyện --' }));

                            res.data.forEach(function (item) {
                                const option = $('<option>', {
                                    value: item.name,
                                    text: item.name
                                });

                                if (item.name === selectedCity) {
                                    option.prop('selected', true);
                                }

                                $citySelect.append(option);
                            });
                        }
                    },
                    error: function (xhr) {
                        console.error('Failed to load quận/huyện:', xhr);
                    }
                });
            }
        }
    });

    // edit country
    const $countrySelect = $('#txt_country');

    // Lấy ID và tên từ option đang chọn hoặc tự đặt cứng nếu muốn
    const selectedValue = '230';
    const selectedText = 'Việt Nam';

    // Tạo input hiển thị tên quốc gia (disabled)
    const $countryTextInput = $('<input>', {
        type: 'text',
        id: 'txt_country',
        class: 'form-control',
        value: selectedText,
        disabled: true
    });

    // Tạo input ẩn để giữ ID khi submit
    const $countryHiddenInput = $('<input>', {
        type: 'hidden',
        name: 'data[country]',
        value: selectedValue
    });

    // Thay thế select bằng input và input hidden
    $countrySelect.replaceWith($countryTextInput);
    $countryTextInput.after($countryHiddenInput);
});
