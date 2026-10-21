<?php
/**
 * Template Name: Хаб услуг (JetHim)
 *
 * Список услуг собирается автоматически из дочерних страниц —
 * новая услуга появляется в хабе без правки шаблона.
 *
 * @package astra-child
 */

defined( 'ABSPATH' ) || exit;

get_header();

$hub_id = get_queried_object_id();
$children = get_pages(
	array(
		'parent' => $hub_id,
		'number' => 0,
	)
);
?>

	<div id="primary" <?php astra_primary_class(); ?>>
		<main id="main" class="site-main">
			<?php astra_get_breadcrumb(); ?>
			<?php
			while ( have_posts() ) :
				the_post();
				?>
				<article id="post-<?php the_ID(); ?>" <?php post_class(); ?>>
					<header class="entry-header">
						<h1 class="entry-title"><?php echo esc_html( defined( 'JC_HUB_SEO_TITLE' ) ? JC_HUB_SEO_TITLE : get_the_title() ); ?></h1>
					</header>

					<div class="entry-content clear">
						<?php the_content(); ?>
					</div>
				</article>
			<?php endwhile; ?>

			<section class="jc-hub__list" aria-label="<?php esc_attr_e( 'Список услуг', 'astra-child' ); ?>">
				<?php
				foreach ( (array) $children as $child ) :
					$grandkids = get_pages(
						array(
							'parent' => (int) $child->ID,
							'number' => 0,
						)
					);
					?>
					<article class="jc-hub__card">
						<h2 class="jc-hub__title">
							<a href="<?php echo esc_url( get_permalink( $child ) ); ?>"><?php echo esc_html( $child->post_title ); ?></a>
						</h2>

						<?php if ( $child->post_excerpt ) : ?>
							<p class="jc-hub__text"><?php echo esc_html( wp_trim_words( $child->post_excerpt, 28 ) ); ?></p>
						<?php else : ?>
							<p class="jc-hub__text"><?php echo esc_html( wp_trim_words( wp_strip_all_tags( $child->post_content ), 28 ) ); ?></p>
						<?php endif; ?>

						<?php if ( $grandkids ) : ?>
							<ul class="jc-hub__sublist">
								<?php foreach ( $grandkids as $sub ) : ?>
									<li><a href="<?php echo esc_url( get_permalink( $sub ) ); ?>"><?php echo esc_html( $sub->post_title ); ?></a></li>
								<?php endforeach; ?>
							</ul>
						<?php endif; ?>
					</article>
				<?php endforeach; ?>
			</section>

			<section class="jc-block jc-block--calc">
				<h2><?php esc_html_e( 'Не нашли услугу?', 'astra-child' ); ?></h2>
				<p><?php esc_html_e( 'Опишите задачу в калькуляторе или позвоните — подскажем, что подходит именно вашему случаю.', 'astra-child' ); ?></p>
				<div class="jc-block__actions">
					<a class="jc-btn jc-btn--primary" href="<?php echo esc_url( home_url( '/kalkulyator/#calc' ) ); ?>"><?php esc_html_e( 'Рассчитать стоимость', 'astra-child' ); ?></a>
					<a class="jc-btn jc-btn--outline" href="<?php echo esc_url( home_url( '/kontakty/' ) ); ?>"><?php esc_html_e( 'Задать вопрос', 'astra-child' ); ?></a>
				</div>
			</section>

			<section class="jc-block jc-block--why">
				<h2><?php esc_html_e( 'Почему выбирают JetHim?', 'astra-child' ); ?></h2>
				<ul class="jc-list">
					<li><?php esc_html_e( 'Работаем с 2011 года: 15 лет чистой работы и тысячи заказов в Москве и области.', 'astra-child' ); ?></li>
					<li><?php esc_html_e( 'Профессиональное оборудование и сертифицированная химия — безопасны для детей, аллергиков и домашних животных.', 'astra-child' ); ?></li>
					<li><?php esc_html_e( 'Цену называем до выезда по фото и параметрам (метраж, ткань, загрязнение) — если данных не хватает, точную назовём на месте. Согласованная цена не меняется.', 'astra-child' ); ?></li>
					<li><?php esc_html_e( 'Выезд по Москве и области; до 10 км от МКАД — бесплатно.', 'astra-child' ); ?></li>
				</ul>
				<p><?php esc_html_e( 'Закажите профессиональную химчистку сегодня:', 'astra-child' ); ?> <a href="<?php echo esc_url( JC_PHONE_HREF ); ?>"><?php echo esc_html( JC_PHONE_DISPLAY ); ?></a> <?php esc_html_e( 'или оставьте заявку — перезвоним в течение 15 минут и назовём точную цену по фото.', 'astra-child' ); ?></p>
			</section>

			<section class="jc-block jc-block--contact">
				<h2><?php esc_html_e( 'Связаться с нами', 'astra-child' ); ?></h2>
				<p>
					<?php esc_html_e( 'Позвоните:', 'astra-child' ); ?>
					<a href="<?php echo esc_url( JC_PHONE_HREF ); ?>"><?php echo esc_html( JC_PHONE_DISPLAY ); ?></a>
					<?php esc_html_e( '(ежедневно с 10:00 до 21:00) или оставьте заявку — ответим в течение 15 минут.', 'astra-child' ); ?>
				</p>
				<div class="jc-block__actions">
					<a class="jc-btn jc-btn--primary" href="#" data-jc-modal-open="visit"><?php esc_html_e( 'Записаться на чистку', 'astra-child' ); ?></a>
					<a class="jc-btn jc-btn--outline" href="<?php echo esc_url( home_url( '/kontakty/' ) ); ?>"><?php esc_html_e( 'Оставить заявку', 'astra-child' ); ?></a>
				</div>
			</section>
		</main>
	</div>

<?php
get_footer();
