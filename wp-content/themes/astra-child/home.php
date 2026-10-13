<?php
/**
 * Архив блога (страница записей): рубрикатор + карточки статей.
 *
 * @package astra-child
 */

defined( 'ABSPATH' ) || exit;

get_header();

$jc_seo_map  = function_exists( 'jc_seo_map' ) ? jc_seo_map() : array();
$jc_blog_h1  = isset( $jc_seo_map['blog']['h1'] ) ? $jc_seo_map['blog']['h1'] : __( 'Блог', 'astra-child' );
?>

	<div id="primary" <?php astra_primary_class(); ?>>
		<main id="main" class="site-main">
			<?php astra_get_breadcrumb(); // На странице записей Astra не выводит крошки автоматически. ?>
			<?php if ( have_posts() ) : ?>
				<?php get_template_part( 'template-parts/jc-post-list', null, array( 'title' => $jc_blog_h1 ) ); ?>
			<?php else : ?>
				<header class="page-header jc-posts__header">
					<h1 class="page-title"><?php echo esc_html( $jc_blog_h1 ); ?></h1>
				</header>
				<p><?php esc_html_e( 'Статьи скоро появятся.', 'astra-child' ); ?></p>
			<?php endif; ?>
		</main>
	</div>

<?php
get_footer();
