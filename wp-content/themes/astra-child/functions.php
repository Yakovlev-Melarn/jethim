<?php
/**
 * Дочерняя тема JetHim (на базе Astra).
 *
 * Правки — только в этой теме и в assets/. Файлы Astra не изменяются.
 *
 * @package astra-child
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/* =====================================================================
 * 0. КОНТАКТЫ
 *
 * Телефон и адрес — от заказчика (05.10). Telegram — заглушка
 * (ссылку на канал/профиль уточнить). VK и Юда — рабочие ссылки.
 * Группа в ВК называется Jet-Cleaning (старый домен утрачен,
 * поэтому сайт — JetHim), ссылки остались на vk.ru/jetcleaning.
 * ================================================================== */
define( 'JC_PHONE_DISPLAY', '+7 (916) 249-34-99' );
define( 'JC_PHONE_HREF', 'tel:+79162493499' );
define( 'JC_ADDRESS', 'Кутузовский проспект, Москва' );
define( 'JC_TELEGRAM_URL', 'https://t.me/jethim' );
define( 'JC_VK_URL', 'https://vk.ru/jetcleaning' );        // группа Jet-Cleaning
define( 'JC_VK_URL_ALT', 'https://vk.ru/jetcleaningprom' ); // вторая группа
define( 'JC_YOUDO_URL', 'https://youdo.com/u1933829' );

/**
 * Единая точка правки контактов — используется в шапке, подвале
 * и позже в формах (Этап 5) и разметке LocalBusiness (Этап 8).
 *
 * @return array
 */
function jc_contacts() {
	$contacts = array(
		'phone_display' => JC_PHONE_DISPLAY,
		'phone_href'    => JC_PHONE_HREF,
		'address'       => JC_ADDRESS,
		'telegram'      => JC_TELEGRAM_URL,
		'vk'            => JC_VK_URL,
		'vk_alt'        => JC_VK_URL_ALT,
		'youdo'         => JC_YOUDO_URL,
	);

	return apply_filters( 'jc_contacts', $contacts );
}

/* =====================================================================
 * Этап 5: формы Fluent Forms
 *
 * Ключи форм лежат в опции jc_forms (заполняется .scripts/e5_setup.php),
 * поэтому здесь нет жёстко зашитых id — форма меняется в админке FF.
 * ================================================================== */
function jc_fluentform( $key ) {
	if ( ! class_exists( 'FluentForm\App\Services\Form\FormService' ) ) {
		return '';
	}

	$forms = get_option( 'jc_forms', array() );
	if ( empty( $forms[ $key ] ) ) {
		return '';
	}

	return do_shortcode( '[fluentform id="' . (int) $forms[ $key ] . '"]' );
}

/* =====================================================================
 * 1. Подключение ресурсов дочерней темы
 * ================================================================== */
add_action( 'wp_enqueue_scripts', 'jc_enqueue_assets', 20 );
function jc_enqueue_assets() {
	$css = get_stylesheet_directory() . '/assets/css/main.css';
	if ( file_exists( $css ) ) {
		$deps = wp_style_is( 'astra-theme-css', 'registered' ) ? array( 'astra-theme-css' ) : array();

		wp_enqueue_style(
			'jc-main',
			get_stylesheet_directory_uri() . '/assets/css/main.css',
			$deps,
			filemtime( $css )
		);
	}

	$js = get_stylesheet_directory() . '/assets/js/main.js';
	if ( file_exists( $js ) ) {
		wp_enqueue_script(
			'jc-main',
			get_stylesheet_directory_uri() . '/assets/js/main.js',
			array(),
			filemtime( $js ),
			true
		);
	}
}

/* =====================================================================
 * Тема: по умолчанию всегда светлая, тёмная — только по кнопке в
 * верхней панели. Атрибут data-theme ставим сразу в <head> (раньше
 * рендера и стилей), чтобы страница не «мигала» при загрузке.
 * ================================================================== */
add_action( 'wp_head', 'jc_theme_bootstrap', 1 );
function jc_theme_bootstrap() {
	?>
	<script>
	( function () {
		var t = null;
		try { t = window.localStorage.getItem( 'jc-theme' ); } catch ( e ) {}
		document.documentElement.setAttribute( 'data-theme', t === 'dark' ? 'dark' : 'light' );
	} )();
	</script>
	<?php
}

/* =====================================================================
 * 2. Иконки (инлайновый SVG, без внешних запросов)
 * ================================================================== */
function jc_icon( $name ) {
	$icons = array(
		'phone'     => '<path d="M6.6 10.8a15.1 15.1 0 0 0 6.6 6.6l2.2-2.2c.3-.3.7-.4 1-.2 1.1.4 2.3.6 3.6.6.6 0 1 .4 1 1V20c0 .6-.4 1-1 1C11 21 3 13 3 3c0-.6.4-1 1-1h3.5c.6 0 1 .4 1 1 0 1.2.2 2.4.6 3.6.1.4 0 .7-.2 1L6.6 10.8z"/>',
		'telegram'  => '<path d="M9.8 16.6 9.6 21c.4 0 .6-.2.8-.4l2-1.9 4.1 3c.8.4 1.3.2 1.5-.7l3.8-14.7c.3-1.1-.4-1.6-1.1-1.3L3.6 10.9c-1.1.4-1.1 1.1-.2 1.3l4.5 1.4 10.4-6.5c.5-.3.9-.1.6.2L9.8 16.6z"/>',
		'vk'        => '<path d="M2 6a4 4 0 0 1 4-4h12a4 4 0 0 1 4 4v12a4 4 0 0 1-4 4H6a4 4 0 0 1-4-4V6z" opacity=".22"/><text x="12" y="15.5" text-anchor="middle" font-family="Arial, Helvetica, sans-serif" font-size="9" font-weight="700" fill="currentColor">VK</text>',
		'star'      => '<path d="M12 3.4l2.7 5.4 6 .9-4.3 4.2 1 6-5.4-2.8-5.4 2.8 1-6L3.3 9.7l6-.9L12 3.4z"/>',
		'pin'       => '<path d="M12 2.5a7 7 0 0 0-7 7c0 5.1 7 12 7 12s7-6.9 7-12a7 7 0 0 0-7-7zm0 9.6a2.6 2.6 0 1 1 0-5.2 2.6 2.6 0 0 1 0 5.2z"/>',
		'sun'       => '<circle cx="12" cy="12" r="4.2"/><path d="M12 2.4v2.3M12 19.3v2.3M2.4 12h2.3M19.3 12h2.3M5.3 5.3l1.6 1.6M17.1 17.1l1.6 1.6M18.7 5.3l-1.6 1.6M6.9 17.1l-1.6 1.6" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round"/>',
		'moon'      => '<path d="M20.2 14.6A8.6 8.6 0 0 1 9.4 3.8a8.6 8.6 0 1 0 10.8 10.8z"/>',
	);

	if ( ! isset( $icons[ $name ] ) ) {
		return '';
	}

	return '<svg viewBox="0 0 24 24" aria-hidden="true" focusable="false" xmlns="http://www.w3.org/2000/svg" fill="currentColor">' . $icons[ $name ] . '</svg>';
}

/* =====================================================================
 * 3. Верхняя панель: телефон + кнопки мессенджеров
 * ================================================================== */
add_action( 'astra_header_before', 'jc_header_topbar', 5 );
function jc_header_topbar() {
	$c = jc_contacts();
	?>
	<div class="jc-topbar">
		<div class="jc-topbar__inner">
			<div class="jc-topbar__contacts">
				<a class="jc-topbar__phone" href="<?php echo esc_url( $c['phone_href'] ); ?>">
					<?php echo esc_html( $c['phone_display'] ); ?>
				</a>
				<span class="jc-topbar__muted">Химчистка с выездом по Москве и МО</span>
			</div>
			<div class="jc-topbar__actions">
				<a class="jc-btn jc-btn--primary" href="<?php echo esc_url( home_url( '/zapis-na-vyezd/' ) ); ?>">
					<span class="jc-btn__label">Записаться</span>
				</a>
				<a class="jc-btn jc-btn--tg" href="<?php echo esc_url( $c['telegram'] ); ?>" target="_blank" rel="noopener noreferrer">
					<?php echo jc_icon( 'telegram' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
					<span class="jc-btn__label">Telegram</span>
				</a>
				<a class="jc-btn jc-btn--vk" href="<?php echo esc_url( $c['vk'] ); ?>" target="_blank" rel="noopener noreferrer">
					<?php echo jc_icon( 'vk' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
					<span class="jc-btn__label">ВКонтакте</span>
				</a>
				<button type="button" class="jc-theme-toggle" data-jc-theme-toggle
					aria-label="Переключить тёмную тему" title="Тёмная тема">
					<?php echo jc_icon( 'sun' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
					<?php echo jc_icon( 'moon' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
				</button>
			</div>
		</div>
	</div>
	<?php
}

/* =====================================================================
 * 4. Подвал: навигация, контакты, соцсети, политика
 * ================================================================== */
add_action( 'astra_footer_content_top', 'jc_footer_columns', 5 );
function jc_footer_columns() {
	$c = jc_contacts();
	?>
	<div class="jc-footer">
		<div class="jc-footer__inner">
			<div class="jc-footer__col">
				<span class="jc-footer__brand"><?php jc_logo(); ?></span>
				<p class="jc-footer__text"><?php esc_html_e( 'Химчистка мебели, штор и ковров с выездом по Москве и области. Работаем с 2010 года.', 'astra-child' ); ?></p>
			</div>

			<div class="jc-footer__col">
				<h3 class="jc-footer__title"><?php esc_html_e( 'Навигация', 'astra-child' ); ?></h3>
				<?php
				wp_nav_menu(
					array(
						'theme_location' => 'primary',
						'container'      => false,
						'depth'          => 1,
						'fallback_cb'    => false,
					)
				);
				?>
			</div>

			<div class="jc-footer__col">
				<h3 class="jc-footer__title"><?php esc_html_e( 'Контакты', 'astra-child' ); ?></h3>
				<a class="jc-footer__phone" href="<?php echo esc_url( $c['phone_href'] ); ?>"><?php echo esc_html( $c['phone_display'] ); ?></a>
				<p class="jc-footer__address">
					<?php echo jc_icon( 'pin' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
					<span><?php echo esc_html( $c['address'] ); ?></span>
				</p>
				<ul class="jc-contacts-list">
					<li>
						<a href="<?php echo esc_url( $c['telegram'] ); ?>" target="_blank" rel="noopener noreferrer">
							<?php echo jc_icon( 'telegram' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
							<span>Telegram</span>
						</a>
					</li>
					<li>
						<a href="<?php echo esc_url( $c['vk'] ); ?>" target="_blank" rel="noopener noreferrer">
							<?php echo jc_icon( 'vk' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
							<span>ВКонтакте — Jet-Cleaning</span>
						</a>
					</li>
					<li>
						<a href="<?php echo esc_url( $c['vk_alt'] ); ?>" target="_blank" rel="noopener noreferrer">
							<?php echo jc_icon( 'vk' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
							<span>ВКонтакте — Jet-Cleaning (пром)</span>
						</a>
					</li>
					<li>
						<a href="<?php echo esc_url( $c['youdo'] ); ?>" target="_blank" rel="noopener noreferrer">
							<?php echo jc_icon( 'star' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
							<span>Отзывы на Юду</span>
						</a>
					</li>
				</ul>
			</div>

			<div class="jc-footer__col jc-footer__col--form">
				<h3 class="jc-footer__title"><?php esc_html_e( 'Записаться', 'astra-child' ); ?></h3>
				<?php echo jc_fluentform( 'callback' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped — рендер формы Fluent Forms ?>
				<p class="jc-footer__text">
					<a href="<?php echo esc_url( home_url( '/zapis-na-vyezd/' ) ); ?>"><?php esc_html_e( 'Запись на выезд по дате →', 'astra-child' ); ?></a>
				</p>
			</div>
		</div>
	</div>
	<?php
}

add_action( 'astra_footer_content_bottom', 'jc_footer_copyright', 5 );
function jc_footer_copyright() {
	?>
	<div class="jc-copyright">
		<div class="jc-copyright__inner">
			<span>
				<?php
				printf(
					/* translators: 1: year, 2: site name. */
					esc_html__( '© %1$s %2$s. Все права защищены.', 'astra-child' ),
					esc_html( gmdate( 'Y' ) ),
					esc_html( get_bloginfo( 'name' ) )
				);
				?>
			</span>
			<a href="<?php echo esc_url( home_url( '/privacy-policy/' ) ); ?>"><?php esc_html_e( 'Политика конфиденциальности', 'astra-child' ); ?></a>
		</div>
	</div>
	<?php
}

/**
 * Логотип: медиа-файл из кастомайзера, иначе — SVG из темы.
 */
function jc_logo() {
	if ( has_custom_logo() ) {
		the_custom_logo();
		return;
	}
	$logo = get_stylesheet_directory_uri() . '/assets/logo.svg';
	printf(
		'<img src="%s" alt="%s" width="220" height="48" loading="lazy" />',
		esc_url( $logo ),
		esc_attr( get_bloginfo( 'name' ) )
	);
}

/* =====================================================================
 * 5. SVG в медиатеке (только для пользователей с unfiltered_html)
 * ================================================================== */
add_filter( 'upload_mimes', 'jc_allow_svg_upload' );
function jc_allow_svg_upload( $mimes ) {
	if ( current_user_can( 'unfiltered_html' ) ) {
		$mimes['svg'] = 'image/svg+xml';
	}
	return $mimes;
}

/* =====================================================================
 * 6. Этап 4: хлебные крошки Astra (штатный аддон темы)
 *
 * Позиция — «Before Title» (astra_entry_top): крошки идут над заголовком
 * в основной колонке контента; на главной не показываем, на вложенных
 * страницах цепочка строится автоматически по иерархии страниц
 * (Хаб услуг → услуга → подуслуга).
 * ================================================================== */
add_filter( 'astra_get_option_breadcrumb-position', 'jc_breadcrumb_position' );
function jc_breadcrumb_position() {
	return 'astra_entry_top';
}

add_filter( 'astra_get_option_breadcrumb-disable-home-page', 'jc_breadcrumb_disable_home' );
function jc_breadcrumb_disable_home() {
	return '0'; // '0' = спрятать на главной (правило Astra).
}

// Русские подписи в цепочке (у Astra по умолчанию английская «Home»).
add_filter( 'astra_breadcrumb_trail_labels', 'jc_breadcrumb_labels' );
function jc_breadcrumb_labels( $labels ) {
	$labels['home']       = esc_html__( 'Главная', 'astra-child' );
	$labels['browse']     = esc_html__( 'Вы здесь:', 'astra-child' );
	$labels['aria_label'] = esc_html__( 'Хлебные крошки', 'astra-child' );

	return $labels;
}

/* =====================================================================
 * 7. Этап 4: единый шаблон услуги
 *
 * Страница услуги хранит только свой текст, а общие секции (расчёт по
 * прайсу, список других услуг, финальный CTA) добавляются здесь же —
 * дублирование сводится к контенту.
 * ================================================================== */
add_filter( 'the_content', 'jc_service_shared_sections', 20 );
function jc_service_shared_sections( $content ) {
	if ( is_admin() || ! is_singular( 'page' ) || 'service.php' !== get_page_template_slug() ) {
		return $content;
	}

	$prices_url = home_url( '/tseny/' );
	$hub_url    = home_url( '/uslugi/' );

	// Этап 5: заявка по услуге — сразу после текста страницы.
	$append  = '<section class="jc-block jc-block--form">';
	$append .= '<h2>' . esc_html__( 'Оставить заявку по этой услуге', 'astra-child' ) . '</h2>';
	$append .= '<p>' . esc_html__( 'Опишите задачу и приложите фото — ответим в течение 15 минут и назовём предварительную цену.', 'astra-child' ) . '</p>';
	$append .= jc_fluentform( 'service' );
	$append .= '</section>';

	$append .= '<section class="jc-block jc-block--calc">';
	$append .= '<h2>' . esc_html__( 'Считаем по прайсу', 'astra-child' ) . '</h2>';
	$append .= '<p>' . esc_html__( 'Цены на сайте — «от»: на итог влияют размер, загрязнение и выезд. Соберите расчёт в калькуляторе за минуту — покажем предварительную сумму до выезда мастера.', 'astra-child' ) . '</p>';
	$append .= '<div class="jc-block__actions">';
	$append .= '<a class="jc-btn jc-btn--primary" href="' . esc_url( $prices_url ) . '#calc">' . esc_html__( 'Рассчитать стоимость', 'astra-child' ) . '</a>';
	$append .= '<a class="jc-btn jc-btn--outline" href="' . esc_url( $hub_url ) . '">' . esc_html__( 'Все услуги', 'astra-child' ) . '</a>';
	$append .= '</div></section>';

	$append .= jc_related_services_html();

	$append .= do_blocks( '<!-- wp:jc/cta /-->' );

	return $content . $append;
}

/**
 * Ссылки на другие услуги той же ветки (без текущей страницы).
 *
 * @return string HTML-блок со списком.
 */
function jc_related_services_html() {
	$current_id = get_queried_object_id();
	$hub        = get_page_by_path( 'uslugi', OBJECT, 'page' );

	if ( ! $hub ) {
		return '';
	}

	$parent_id = wp_get_post_parent_id( $current_id );
	$list_from  = ( $parent_id && (int) $parent_id !== (int) $hub->ID ) ? (int) $parent_id : (int) $hub->ID;

	$siblings = get_pages(
		array(
			'parent' => $list_from,
			'number' => 0,
		)
	);

	if ( empty( $siblings ) ) {
		return '';
	}

	$items = '';
	foreach ( $siblings as $sibling ) {
		if ( (int) $sibling->ID === (int) $current_id ) {
			continue;
		}
		$items .= '<li><a href="' . esc_url( get_permalink( $sibling ) ) . '">' . esc_html( $sibling->post_title ) . '</a></li>';
	}

	if ( '' === $items ) {
		return '';
	}

	$html  = '<section class="jc-block jc-block--related">';
	$html .= '<h2>' . esc_html__( 'Другие услуги', 'astra-child' ) . '</h2>';
	$html .= '<ul class="jc-related__list">' . $items . '</ul>';
	$html .= '<p class="jc-block__note"><a href="' . esc_url( get_permalink( $hub ) ) . '">' . esc_html__( 'Все услуги →', 'astra-child' ) . '</a></p>';
	$html .= '</section>';

	return $html;
}

/* =====================================================================
 * 8. Точки расширения для следующих этапов:
 *    - Этап 5 (формы) — контакты брать через jc_contacts() / фильтр jc_contacts.
 * ================================================================== */
