<?php
/**
 * Архивы: рубрики, теги, даты — тот же список карточек, что и в блоге.
 *
 * @package astra-child
 */

defined( 'ABSPATH' ) || exit;

get_header();
?>

	<div id="primary" <?php astra_primary_class(); ?>>
		<main id="main" class="site-main">
			<?php astra_get_breadcrumb(); // Astra вешает крошки на astra_before_archive_title — этот шаблон хук не вызывает. ?>
			<?php if ( have_posts() ) : ?>
				<?php get_template_part( 'template-parts/jc-post-list', null, array( 'title' => wp_strip_all_tags( get_the_archive_title() ) ) ); ?>
			<?php else : ?>
				<header class="page-header jc-posts__header">
					<h1 class="page-title"><?php echo esc_html( wp_strip_all_tags( get_the_archive_title() ) ); ?></h1>
				</header>
				<p><?php esc_html_e( 'Записей нет.', 'astra-child' ); ?></p>
			<?php endif; ?>
		</main>
	</div>

<?php
get_footer();
