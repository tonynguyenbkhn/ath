<?php

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Hero Video section template.
 * Expects ACF fields:
 * - video (file ID/array/url)
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

$video_url = '';

if (is_array($video)) {
    $video_url = !empty($video['url']) ? $video['url'] : '';
} elseif (is_numeric($video)) {
    $video_url = wp_get_attachment_url((int) $video);
} elseif (is_string($video)) {
    $video_url = $video;
}

$image_url = $image_id ? wp_get_attachment_image_url($image_id, 'full') : '';
$image_sizes = $image_id ? wp_get_attachment_image_sizes($image_id, 'full') : '';
$image_alt = '';

if ($image_id) {
    $alt = get_post_meta($image_id, '_wp_attachment_image_alt', true);
    $image_alt = $alt ? $alt : ($title ? $title : '');
}

?>

<?php if ($video_url || $image_url || $title || $description || $button_text): ?>
<section class="hero-video" role="banner"<?php echo $section_id ? ' id="' . esc_attr($section_id) . '"' : ''; ?>>
  <div class="hero-video__media position-relative">
    <?php if ($video_url): ?>
      <video
        class="hero-video__video"
        autoplay
        muted
        loop
        playsinline
        preload="metadata"
        <?php echo $image_url ? ' poster="' . esc_url($image_url) . '"' : ''; ?>
      >
        <source src="<?php echo esc_url($video_url); ?>">
      </video>
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
