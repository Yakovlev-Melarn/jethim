<?php
/**
 * Рендер слайдера «до/после».
 *
 * Разметка одна для фронта и превью в редакторе; позиция делителя —
 * CSS-переменная --pos, её двигает assets/view.js (мышь/палец/клавиши —
 * это нативный input[type=range], перетаскивание и стрелки работают сами).
 */

defined( 'ABSPATH' ) || exit;

$before       = isset( $attributes['beforeUrl'] ) ? $attributes['beforeUrl'] : '';
$after        = isset( $attributes['afterUrl'] ) ? $attributes['afterUrl'] : '';
$before_label = isset( $attributes['beforeLabel'] ) && $attributes['beforeLabel'] !== '' ? $attributes['beforeLabel'] : 'До';
$after_label  = isset( $attributes['afterLabel'] ) && $attributes['afterLabel'] !== '' ? $attributes['afterLabel'] : 'После';
$caption      = isset( $attributes['caption'] ) ? $attributes['caption'] : '';
$start        = isset( $attributes['start'] ) ? (int) $attributes['start'] : 50;
$start        = max( 0, min( 100, $start ) );

?>
<figure <?php echo get_block_wrapper_attributes( array( 'class' => 'jc-ba' ) ); ?>>
	<?php if ( $before && $after ) : ?>
		<div class="jc-ba__frame" data-jc-ba style="--pos: <?php echo esc_attr( $start ); ?>%;">
			<img class="jc-ba__img jc-ba__img--after" src="<?php echo esc_url( $after ); ?>"
				alt="<?php echo esc_attr( $after_label ); ?>" loading="lazy">
			<img class="jc-ba__img jc-ba__img--before" src="<?php echo esc_url( $before ); ?>"
				alt="<?php echo esc_attr( $before_label ); ?>" loading="lazy">

			<span class="jc-ba__tag jc-ba__tag--before"><?php echo esc_html( $before_label ); ?></span>
			<span class="jc-ba__tag jc-ba__tag--after"><?php echo esc_html( $after_label ); ?></span>

			<span class="jc-ba__divider" aria-hidden="true">
				<span class="jc-ba__handle">‹ ›</span>
			</span>

			<input class="jc-ba__range" type="range" min="0" max="100" step="1"
				value="<?php echo esc_attr( $start ); ?>" data-jc-ba-range
				aria-label="<?php echo esc_attr( 'Сравнение: ' . $before_label . ' и ' . $after_label ); ?>">
		</div>
	<?php else : ?>
		<div class="jc-ba__frame jc-ba__frame--empty" data-jc-ba style="--pos: <?php echo esc_attr( $start ); ?>%;">
			<p class="jc-ba__empty">Выберите два фото «до» и «после» в настройках блока.</p>
		</div>
	<?php endif; ?>

	<?php if ( $caption ) : ?>
		<figcaption class="jc-ba__caption"><?php echo esc_html( $caption ); ?></figcaption>
	<?php endif; ?>
</figure>
