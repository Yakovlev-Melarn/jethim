<?php
/**
 * Архив блога (страница записей): рубрикатор + карточки статей.
 *
 * @package astra-child
 */

defined( 'ABSPATH' ) || exit;

get_header();
?>

	<div id="primary" <?php astra_primary_class(); ?>>
		<main id="main" class="site-main">
			<?php astra_get_breadcrumb(); // На странице записей Astra не выводит крошки автоматически. ?>
			<?php if ( have_posts() ) : ?>
				<?php get_template_part( 'template-parts/jc-post-list', null, array( 'title' => __( 'Блог', 'astra-child' ) ) ); ?>
			<?php else : ?>
				<header class="page-header jc-posts__header">
					<h1 class="page-title"><?php esc_html_e( 'Блог', 'astra-child' ); ?></h1>
				</header>
				<p><?php esc_html_e( 'Статьи скоро появятся.', 'astra-child' ); ?></p>
			<?php endif; ?>
		</main>
	</div>

<?php
get_footer();
