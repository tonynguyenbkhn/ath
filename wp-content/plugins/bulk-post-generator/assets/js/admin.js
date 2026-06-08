jQuery(document).ready(function($) {
    var mediaFrame;

    $('.bpg-select-images').on('click', function(e) {
        e.preventDefault();

        if (mediaFrame) {
            mediaFrame.open();
            return;
        }

        mediaFrame = wp.media({
            title: 'Select Images for Posts',
            button: { text: 'Use these images' },
            multiple: true
        });

        mediaFrame.on('select', function() {
            var attachments = mediaFrame.state().get('selection').toJSON();
            var ids = [];
            var html = '';

            attachments.forEach(function(attachment) {
                ids.push(attachment.id);
                var url = attachment.sizes && attachment.sizes.thumbnail ? attachment.sizes.thumbnail.url : attachment.url;
                html += '<div class="bpg-img-preview-item"><img src="' + url + '"></div>';
            });

            $('#bpg_image_ids').val(ids.join(','));
            $('#bpg-image-preview').html(html);
        });

        mediaFrame.open();
    });

    $('#bpg-form').on('submit', function(e) {
        e.preventDefault();

        if ($('#bpg-generate-btn').prop('disabled')) return;

        var totalPosts = parseInt($('#bpg_num_posts').val());
        if (!totalPosts || totalPosts < 1) {
            alert('Please enter a valid number of posts.');
            return;
        }

        $('#bpg-message').html('');
        $('#bpg-generate-btn').prop('disabled', true).text('Generating...');
        $('#bpg-progress-wrapper').show();
        updateProgress(0, totalPosts);

        var data = {
            batch_size: 5,
            current_index: 0,
            total_posts: totalPosts,
            title_template: $('#bpg_title_template').val(),
            content_template: $('#bpg_content_template').val(),
            post_status: $('#bpg_post_status').val(),
            category: $('#bpg_category').val(),
            image_ids: $('#bpg_image_ids').val(),
            image_option: $('#bpg_image_option').val()
        };

        processBatch(data);
    });

    function processBatch(data) {
        var ajaxData = $.extend({}, data, {
            action: 'bpg_generate_batch',
            nonce: bpgData.nonce
        });

        $.post(bpgData.ajax_url, ajaxData, function(response) {
            if (response.success) {
                var currentIndex = response.data.current_index;
                updateProgress(Math.min(currentIndex, data.total_posts), data.total_posts);

                if (currentIndex < data.total_posts) {
                    data.current_index = currentIndex;
                    processBatch(data);
                } else {
                    finishGeneration(data.total_posts);
                }
            } else {
                alert('An error occurred: ' + response.data);
                resetUI();
            }
        }).fail(function() {
            alert('A server error occurred. Please check your network tab or server logs.');
            resetUI();
        });
    }

    function updateProgress(current, total) {
        $('#bpg-progress-text').text(current + '/' + total);
        var percent = Math.round((current / total) * 100);
        $('#bpg-progress-fill').css('width', percent + '%');
    }

    function finishGeneration(total) {
        $('#bpg-message').html('<div class="notice notice-success is-dismissible"><p>Successfully generated ' + total + ' posts!</p></div>');
        $('#bpg_image_ids').val('');
        $('#bpg-image-preview').html('');
        resetUI();
    }

    function resetUI() {
        $('#bpg-generate-btn').prop('disabled', false).text('Generate Posts');
        $('#bpg-progress-wrapper').hide();
    }
});
