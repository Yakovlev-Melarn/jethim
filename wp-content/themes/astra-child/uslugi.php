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
						<h1 class="entry-title"><?php the_title(); ?></h1>
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
					<a class="jc-btn jc-btn--primary" href="<?php echo esc_url( home_url( '/tseny/#calc' ) ); ?>"><?php esc_html_e( 'Рассчитать стоимость', 'astra-child' ); ?></a>
					<a class="jc-btn jc-btn--outline" href="<?php echo esc_url( home_url( '/kontakty/' ) ); ?>"><?php esc_html_e( 'Задать вопрос', 'astra-child' ); ?></a>
				</div>
			</section>
		</main>
	</div>

<?php
get_footer();
