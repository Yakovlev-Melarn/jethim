<?php
/**
 * Шаблон записи блога: заголовок, мета, текст, соседние статьи.
 *
 * @package astra-child
 */

defined( 'ABSPATH' ) || exit;

get_header();
?>

	<div id="primary" <?php astra_primary_class(); ?>>
		<main id="main" class="site-main">
			<?php
			while ( have_posts() ) :
				the_post();
				?>
				<article id="post-<?php the_ID(); ?>" <?php post_class( 'jc-single' ); ?>>
					<header class="entry-header">
						<h1 class="entry-title"><?php the_title(); ?></h1>
						<p class="jc-card__meta">
							<time datetime="<?php echo esc_attr( get_the_date( 'c' ) ); ?>"><?php echo esc_html( get_the_date() ); ?></time>
							<?php
							$jc_in = get_the_category_list( ', ' );
							if ( $jc_in ) {
								echo ' · ' . $jc_in; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- ссылки категорий.
							}
							?>
						</p>
				</header>

				<?php if ( has_post_thumbnail() ) : ?>
					<figure class="jc-single__thumb">
						<?php the_post_thumbnail( 'full' ); ?>
					</figure>
				<?php endif; ?>

				<div class="entry-content clear">
					<?php the_content(); ?>
				</div>

					<footer class="entry-footer jc-single__footer">
						<a class="jc-btn jc-btn--outline" href="<?php echo esc_url( home_url( '/blog/' ) ); ?>">
							← <?php esc_html_e( 'Все статьи', 'astra-child' ); ?>
						</a>
						<a class="jc-btn jc-btn--primary" href="<?php echo esc_url( home_url( '/tseny/#calc' ) ); ?>">
							<?php esc_html_e( 'Рассчитать стоимость', 'astra-child' ); ?>
						</a>
					</footer>
				</article>

				<nav class="jc-single__nav" aria-label="<?php esc_attr_e( 'Соседние статьи', 'astra-child' ); ?>">
					<span class="jc-single__nav-item jc-single__nav-item--prev"><?php previous_post_link( '%link', '← %title' ); ?></span>
					<span class="jc-single__nav-item jc-single__nav-item--next"><?php next_post_link( '%link', '%title →' ); ?></span>
				</nav>
			<?php endwhile; ?>
		</main>
	</div>

<?php
get_footer();
