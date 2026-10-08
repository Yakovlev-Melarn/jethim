<?php
/**
 * Рендер блока «контакты / CTA».
 *
 * Телефон, адрес и ссылки — из jc_contacts() дочерней темы, поэтому
 * блок всегда показывает актуальные контакты без правки страниц.
 */

defined( 'ABSPATH' ) || exit;

$contacts = function_exists( 'jc_contacts' ) ? jc_contacts() : array();

$title        = isset( $attributes['title'] ) ? $attributes['title'] : '';
$text         = isset( $attributes['text'] ) ? $attributes['text'] : '';
$button_label = isset( $attributes['buttonLabel'] ) && $attributes['buttonLabel'] !== '' ? $attributes['buttonLabel'] : 'Рассчитать стоимость';
$show_phone   = ! isset( $attributes['showPhone'] ) || $attributes['showPhone'];
$show_social  = ! isset( $attributes['showSocial'] ) || $attributes['showSocial'];
$show_address = isset( $attributes['showAddress'] ) && $attributes['showAddress'];

/**
 * Иконка из дочерней темы, если она есть.
 * Обёртка в function_exists: render.php можно вызвать дважды на странице.
 *
 * @param string $name Имя иконки.
 * @return string
 */
if ( ! function_exists( 'jhb_cta_icon' ) ) {
	function jhb_cta_icon( $name ) {
		return function_exists( 'jc_icon' ) ? jc_icon( $name ) : '';
	}
}

?>
<div <?php echo get_block_wrapper_attributes( array( 'class' => 'jc-cta' ) ); ?>>
	<div class="jc-cta__inner">
		<div class="jc-cta__text">
			<?php if ( $title ) : ?>
				<h2 class="jc-cta__title"><?php echo esc_html( $title ); ?></h2>
			<?php endif; ?>
			<?php if ( $text ) : ?>
				<p class="jc-cta__lead"><?php echo esc_html( $text ); ?></p>
			<?php endif; ?>
			<?php if ( $show_address && ! empty( $contacts['address'] ) ) : ?>
				<p class="jc-cta__address">
					<?php echo jhb_cta_icon( 'pin' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
					<span><?php echo esc_html( $contacts['address'] ); ?></span>
				</p>
			<?php endif; ?>
		</div>

		<div class="jc-cta__actions">
			<a class="jc-btn jc-btn--primary" href="<?php echo esc_url( home_url( '/kalkulyator/#calc' ) ); ?>">
				<span><?php echo esc_html( $button_label ); ?></span>
			</a>
			<?php if ( $show_phone && ! empty( $contacts['phone_href'] ) ) : ?>
				<a class="jc-cta__phone" href="<?php echo esc_url( $contacts['phone_href'] ); ?>">
					<?php echo esc_html( $contacts['phone_display'] ); ?>
				</a>
			<?php endif; ?>

			<?php if ( $show_social ) : ?>
				<?php if ( ! empty( $contacts['telegram'] ) ) : ?>
					<a class="jc-btn jc-btn--tg" href="<?php echo esc_url( $contacts['telegram'] ); ?>" target="_blank" rel="noopener noreferrer">
						<?php echo jhb_cta_icon( 'telegram' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
						<span>Telegram</span>
					</a>
				<?php endif; ?>
				<?php if ( ! empty( $contacts['vk'] ) ) : ?>
					<a class="jc-btn jc-btn--vk" href="<?php echo esc_url( $contacts['vk'] ); ?>" target="_blank" rel="noopener noreferrer">
						<?php echo jhb_cta_icon( 'vk' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
						<span>ВКонтакте</span>
					</a>
				<?php endif; ?>
			<?php endif; ?>
		</div>
	</div>
</div>
