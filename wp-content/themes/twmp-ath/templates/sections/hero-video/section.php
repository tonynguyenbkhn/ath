<?php

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Hero Video section template.
 * Expects ACF fields:
 * - video (YouTube URL)
 * - image (image ID, used as poster/fallback)
 * - title (text)
 * - description (textarea)
 * - button_text (text)
 * - button_link (url)
 */

if (!function_exists('_hv_get')) {
    function _hv_get($name) {
        if (function_exists('get_sub_field') && get_sub_field($name) !== null) {
            return get_sub_field($name);
        }
        if (function_exists('get_field')) {
            return get_field($name);
        }
        return null;
    }
}

if (!function_exists('_hv_get_youtube_embed_url')) {
    function _hv_get_youtube_embed_url($url) {
        $url = trim((string) $url);

        if ($url === '') {
            return '';
        }

        $parts = wp_parse_url($url);

        if (empty($parts['host'])) {
            return '';
        }

        $host = strtolower($parts['host']);
        $path = isset($parts['path']) ? trim($parts['path'], '/') : '';
        $video_id = '';

        if (strpos($host, 'youtu.be') !== false) {
            $segments = explode('/', $path);
            $video_id = !empty($segments[0]) ? $segments[0] : '';
        } elseif (strpos($host, 'youtube.com') !== false || strpos($host, 'youtube-nocookie.com') !== false) {
            if (!empty($parts['query'])) {
                parse_str($parts['query'], $query);
                $video_id = !empty($query['v']) ? $query['v'] : '';
            }

            if (!$video_id && $path) {
                $segments = explode('/', $path);

                if (in_array($segments[0], ['embed', 'shorts', 'live'], true) && !empty($segments[1])) {
                    $video_id = $segments[1];
                }
            }
        }

        $video_id = preg_replace('/[^A-Za-z0-9_-]/', '', (string) $video_id);

        if (!$video_id) {
            return '';
        }

        return add_query_arg(
            [
                'autoplay' => '1',
                'mute' => '1',
                'controls' => '0',
                'enablejsapi' => '1',
                'loop' => '1',
                'playlist' => $video_id,
                'playsinline' => '1',
                'rel' => '0',
                'modestbranding' => '1',
                'origin' => home_url(),
            ],
            'https://www.youtube-nocookie.com/embed/' . $video_id
        );
    }
}

$hide_section = _hv_get('hide_section');

if ($hide_section) {
    return;
}

$video = _hv_get('video');
$image_id = _hv_get('image');
$section_id = _hv_get('section_id');
$title = _hv_get('title');
$description = _hv_get('description');
$button_text = _hv_get('button_text');
$button_link = _hv_get('button_link');

$video_url = is_string($video) ? $video : '';
$video_embed_url = _hv_get_youtube_embed_url($video_url);

$image_url = $image_id ? wp_get_attachment_image_url($image_id, 'full') : '';
$image_sizes = $image_id ? wp_get_attachment_image_sizes($image_id, 'full') : '';
$image_alt = '';

if ($image_id) {
    $alt = get_post_meta($image_id, '_wp_attachment_image_alt', true);
    $image_alt = $alt ? $alt : ($title ? $title : '');
}

?>

<?php if ($video_embed_url || $image_url || $title || $description || $button_text): ?>
<section class="hero-video" role="banner"<?php echo $section_id ? ' id="' . esc_attr($section_id) . '"' : ''; ?><?php echo $video_embed_url ? ' data-block="hero-video"' : ''; ?>>
  <div class="hero-video__media position-relative">
    <?php if ($video_embed_url): ?>
      <iframe
        class="hero-video__iframe"
        src="<?php echo esc_url($video_embed_url); ?>"
        title="<?php echo esc_attr($title ? $title : __('Hero video', 'twmp-ath')); ?>"
        allow="autoplay; encrypted-media; picture-in-picture"
        loading="eager"
        referrerpolicy="strict-origin-when-cross-origin"
        aria-hidden="true"
        tabindex="-1"
      ></iframe>
      <div class="hero-video__controls" aria-label="<?php echo esc_attr__('Video controls', 'twmp-ath'); ?>">
        <button class="hero-video__control" type="button" data-hero-video-toggle-play aria-label="<?php echo esc_attr__('Pause video', 'twmp-ath'); ?>" data-label-play="<?php echo esc_attr__('Play', 'twmp-ath'); ?>" data-label-pause="<?php echo esc_attr__('Pause', 'twmp-ath'); ?>">
          <span data-hero-video-play-label><?php echo esc_html__('Pause', 'twmp-ath'); ?></span>
        </button>
        <button class="hero-video__control" type="button" data-hero-video-toggle-mute aria-label="<?php echo esc_attr__('Unmute video', 'twmp-ath'); ?>" data-label-mute="<?php echo esc_attr__('Mute', 'twmp-ath'); ?>" data-label-unmute="<?php echo esc_attr__('Sound', 'twmp-ath'); ?>">
          <span data-hero-video-mute-label><?php echo esc_html__('Sound', 'twmp-ath'); ?></span>
        </button>
      </div>
    <?php elseif ($image_id): ?>
      <?php get_template_part('templates/components/images', null, [
        'id' => $image_id,
        'size' => 'full',
        'class' => 'hero-video__img',
        'alt' => $image_alt,
        'loading' => 'eager',
        'sizes' => $image_sizes,
      ]); ?>
    <?php endif; ?>
    <div class="hero-video__overlay"></div>
  </div>
  <div class="hero-video__overlay"></div>
  <div class="hero-video__content">
    <div class="container">
      <div class="hero-video__grid">
        <div class="hero-video__left">
          <?php if ($description): ?><p class="hero-video__desc typo-text-lg-regular text-system-content-2"><?php echo wp_kses_post($description); ?></p><?php endif; ?>
          <?php if ($button_text && $button_link): ?>
            <p class="hero-video__cta">
              <?php get_template_part('templates/components/button', null, [
                'class' => 'bg-primary-500 text-system-white typo-system-button button-medium',
                'button_text' => $button_text,
                'button_url' => $button_link,
                'button_link_target' => '_self',
              ]); ?>
            </p>
          <?php endif; ?>
        </div>
        <div class="hero-video__right">
          <?php if ($title): ?><h1 class="hero-video__title typo-display-xl-regular text-system-white"><?php echo esc_html($title); ?></h1><?php endif; ?>
        </div>
      </div>
    </div>
  </div>
</section>
<?php endif; ?>
