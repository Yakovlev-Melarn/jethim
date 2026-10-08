<?php
/**
 * Список статей: карточки + рубрикатор. Используется в home.php и archive.php.
 *
 * @package astra-child
 *
 * @var array $args Аргументы: 'title' — заголовок раздела.
 */

defined( 'ABSPATH' ) || exit;

$jc_title = ! empty( $args['title'] ) ? $args['title'] : get_the_archive_title();
$jc_uncat = get_term_by( 'slug', 'uncategorized', 'category' );
$jc_cats  = get_categories(
	array(
		'hide_empty' => true,
		'exclude'    => $jc_uncat ? array( (int) $jc_uncat->term_id ) : array(),
	)
);
?>

<header class="page-header jc-posts__header">
	<h1 class="page-title"><?php echo esc_html( wp_strip_all_tags( $jc_title ) ); ?></h1>

	<?php if ( $jc_cats ) : ?>
		<nav class="jc-rubricator" aria-label="<?php esc_attr_e( 'Рубрики блога', 'astra-child' ); ?>">
			<?php foreach ( $jc_cats as $jc_cat ) : ?>
				<a class="jc-rubricator__link"
					href="<?php echo esc_url( get_category_link( $jc_cat ) ); ?>">
					<?php echo esc_html( $jc_cat->name ); ?>
					<span class="jc-rubricator__count"><?php echo (int) $jc_cat->count; ?></span>
				</a>
			<?php endforeach; ?>
		</nav>
	<?php endif; ?>
</header>

<div class="jc-posts">
	<?php
	while ( have_posts() ) :
		the_post();
		?>
		<article id="post-<?php the_ID(); ?>" <?php post_class( 'jc-card' ); ?>>
			<?php if ( has_post_thumbnail() ) : ?>
				<a class="jc-card__thumb" href="<?php the_permalink(); ?>" tabindex="-1" aria-hidden="true">
					<?php the_post_thumbnail( 'medium_large' ); ?>
				</a>
			<?php endif; ?>

			<div class="jc-card__body">
				<h2 class="jc-card__title">
					<a href="<?php the_permalink(); ?>"><?php the_title(); ?></a>
				</h2>

				<p class="jc-card__meta">
					<time datetime="<?php echo esc_attr( get_the_date( 'c' ) ); ?>"><?php echo esc_html( get_the_date() ); ?></time>
					<?php
					$jc_in = get_the_category_list( ', ' );
					if ( $jc_in ) {
						echo ' · ' . $jc_in; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- ссылки категорий.
					}
					?>
				</p>

				<div class="jc-card__excerpt"><?php the_excerpt(); ?></div>

				<a class="jc-card__more" href="<?php the_permalink(); ?>">
					<?php esc_html_e( 'Читать статью', 'astra-child' ); ?> →
				</a>
			</div>
		</article>
	<?php endwhile; ?>
</div>

<?php
the_posts_pagination(
	array(
		'mid_size'  => 1,
		'prev_text' => esc_html__( '← Новые', 'astra-child' ),
		'next_text' => esc_html__( 'Раньше →', 'astra-child' ),
	)
);
