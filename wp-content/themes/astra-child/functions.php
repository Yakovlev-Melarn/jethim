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
define( 'JC_TELEGRAM_URL', 'https://t.me/Nyankosen' );
define( 'JC_VK_URL', 'https://vk.ru/jetcleaning' );        // группа Jet-Cleaning
define( 'JC_VK_URL_ALT', 'https://vk.ru/jetcleaningprom' ); // вторая группа
define( 'JC_YOUDO_URL', 'https://youdo.com/u1933829' );

/**
 * SEO-заголовок хаба услуг (/uslugi/): его показываем в <title> документа
 * и в H1 страницы (см. uslugi.php), а в меню, крошках и ссылках остаётся
 * короткое «Услуги» — имя страницы в БД не трогаем.
 */
define( 'JC_HUB_SEO_TITLE', 'Профессиональная химчистка мягкой мебели, ковров и штор в Москве — JetHim' );

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

/**
 * Видеоплеер «строго по клику»: обложка + кнопка play, автовоспроизведения
 * нет. Логика — jcVideoPlayers() в main.js (клик по кнопке/оверлею запускает
 * и включает нативные контролы; на ended оверлей возвращается как «повтор»).
 * Файлы: wp-content/uploads/2026/10/record.mp4 + record-poster.jpg
 * (клонируются из .scripts/img/record.mp4, кадр постера — video_probe.js).
 */
function jc_video_player() {
	$upload = wp_upload_dir();
	if ( empty( $upload['baseurl'] ) ) {
		return '';
	}
	$src        = $upload['baseurl'] . '/2026/10/record.mp4';
	$poster_url = $upload['baseurl'] . '/2026/10/record-poster.jpg';
	$poster     = $upload['basedir'] . '/2026/10/record-poster.jpg';
	if ( ! file_exists( $upload['basedir'] . '/2026/10/record.mp4' ) ) {
		return '';
	}

	$html  = '<figure class="jc-video" data-jc-video>';
	$html .= '<video class="jc-video__el" preload="metadata" playsinline';
	if ( file_exists( $poster ) ) {
		$html .= ' poster="' . esc_url( $poster_url ) . '"';
	}
	$html .= ' src="' . esc_url( $src ) . '"></video>';
	$html .= '<button class="jc-video__play" type="button" aria-label="' . esc_attr__( 'Смотреть видео', 'astra-child' ) . '">';
	$html .= '<span class="jc-video__play-icon" aria-hidden="true">'
		. '<svg viewBox="0 0 24 24" focusable="false"><path d="M8 5v14l11-7z"/></svg>'
		. '</span>';
	$html .= '<span class="jc-video__label">Видео о нашей работе — 1 минута</span>';
	$html .= '</button>';
	$html .= '<figcaption class="jc-video__error" hidden>Не удалось загрузить видео — обновите страницу или позвоните ' . esc_html( JC_PHONE_DISPLAY ) . '.</figcaption>';
	$html .= '</figure>';

	return $html;
}

/* =====================================================================
 * 1. Подключение ресурсов дочерней темы
 * ================================================================== */
add_action( 'wp_enqueue_scripts', 'jc_enqueue_assets', 20 );
function jc_enqueue_assets() {
	// Редизайн: Google Fonts — Montserrat (заголовки) + Inter (текст).
	wp_enqueue_style(
		'jc-fonts',
		'https://fonts.googleapis.com/css2?family=Inter:wght@400;600&family=Montserrat:wght@600;700;800&display=swap',
		array(),
		null
	);

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

/**
 * Лайтбокс фотогалереи — только там, где он есть в контенте
 * (.jc-gallery ставит e6_case_gallery() на странице «Примеры работ»).
 */
add_action( 'wp_enqueue_scripts', 'jc_enqueue_gallery', 30 );
function jc_enqueue_gallery() {
	if ( ! is_singular() ) {
		return;
	}

	$content = (string) get_post_field( 'post_content', get_queried_object_id() );
	if ( false === strpos( $content, 'jc-gallery' ) ) {
		return;
	}

	$js = get_stylesheet_directory() . '/assets/js/gallery.js';
	if ( ! file_exists( $js ) ) {
		return;
	}

	wp_enqueue_script(
		'jc-gallery',
		get_stylesheet_directory_uri() . '/assets/js/gallery.js',
		array(),
		filemtime( $js ),
		true
	);
}

/**
 * Все ссылки на Юду открываются в новой вкладке — страховка поверх
 * генераторов (.scripts/e6_pages.php): ловит и кнопки, и ссылки в тексте.
 */
add_filter( 'the_content', 'jc_youdo_blank', 99 );
function jc_youdo_blank( $html ) {
	if ( false === stripos( $html, 'youdo.com' ) ) {
		return $html;
	}

	return preg_replace_callback(
		'/<a\b([^>]*?)href=(["\'])(https?:\/\/youdo\.com[^"\']*)\2([^>]*)>/i',
		function ( $m ) {
			$attrs = $m[1] . $m[4];
			if ( false !== stripos( $attrs, 'target=' ) ) {
				return $m[0];
			}

			return '<a' . $m[1] . 'href=' . $m[2] . $m[3] . $m[2] . $m[4]
				. ' target="_blank" rel="noopener noreferrer">';
		},
		$html
	);
}

/* =====================================================================
 * Тема: всегда светлая (референс клиента — himchistka-kaplya.ru).
 * ================================================================== */

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
 * 3. Шапка: полоса меню во всю ширину + строка логотипа/контактов
 *
 * Порядок сверху вниз (обе — astra_header_before, Astra-шапка скрыта
 * CSS-правилом .site-header { display:none }):
 *   1. .jc-menubar  — меню сайта на всю ширину экрана;
 *   2. .jc-header   — логотип, телефон, «Вызвать мастера», мессенджеры.
 * ================================================================== */
	add_action( 'astra_header_before', 'jc_header_bars', 5 );
	function jc_header_bars() {
		$c = jc_contacts();
		?>
		<div class="jc-menubar">
			<div class="jc-menubar__inner">
				<button class="jc-menubar__toggle" type="button" aria-expanded="false"
					aria-label="<?php esc_attr_e( 'Меню', 'astra-child' ); ?>">
					<span></span>
					<span></span>
					<span></span>
				</button>
				<nav class="jc-menubar__nav" aria-label="<?php esc_attr_e( 'Основное меню', 'astra-child' ); ?>">
					<?php
					wp_nav_menu(
						array(
							'theme_location' => 'primary',
							'container'      => false,
							'depth'          => 3,
							'fallback_cb'    => false,
						)
					);
					?>
				</nav>
			</div>
		</div>
		<div class="jc-header">
			<div class="jc-header__inner">
				<?php jc_logo(); ?>
				<div class="jc-header__actions">
					<a class="jc-header__phone" href="<?php echo esc_url( $c['phone_href'] ); ?>">
						<?php echo esc_html( $c['phone_display'] ); ?>
					</a>
					<a class="jc-btn jc-btn--primary jc-btn--sm" href="<?php echo esc_url( home_url( '/zapis-na-vyezd/' ) ); ?>" data-jc-modal-open="visit">
						<?php esc_html_e( 'Вызвать мастера', 'astra-child' ); ?>
					</a>
					<a class="jc-circle" href="<?php echo esc_url( $c['telegram'] ); ?>" target="_blank" rel="noopener noreferrer" aria-label="Telegram">
						<?php echo jc_icon( 'telegram' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
					</a>
					<a class="jc-circle" href="<?php echo esc_url( $c['vk'] ); ?>" target="_blank" rel="noopener noreferrer" aria-label="ВКонтакте">
						<?php echo jc_icon( 'vk' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
					</a>
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
				<p class="jc-footer__text"><?php esc_html_e( 'Химчистка мягкой мебели, штор, ковров и матрасов с выездом по Москве и Московской области. Работаем с 2011 года. Профессиональное оборудование, сертифицированная химия, безопасная для детей и животных.', 'astra-child' ); ?></p>
			</div>

			<div class="jc-footer__col jc-footer__col--nav">
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

			<div class="jc-footer__col jc-footer__col--actions">
				<h3 class="jc-footer__title"><?php esc_html_e( 'Связаться', 'astra-child' ); ?></h3>
				<div class="jc-footer__actions">
					<a class="jc-btn jc-btn--outline" href="<?php echo esc_url( home_url( '/kontakty/' ) ); ?>" data-jc-modal-open="photo">
						<?php esc_html_e( 'Отправить фото', 'astra-child' ); ?>
					</a>
					<a class="jc-btn jc-btn--outline" href="<?php echo esc_url( home_url( '/kontakty/' ) ); ?>" data-jc-modal-open="callback">
						<?php esc_html_e( 'Консультация', 'astra-child' ); ?>
					</a>
					<a class="jc-btn jc-btn--primary" href="<?php echo esc_url( home_url( '/zapis-na-vyezd/' ) ); ?>" data-jc-modal-open="visit">
						<?php esc_html_e( 'Вызов мастера', 'astra-child' ); ?>
					</a>
				</div>
				<p class="jc-footer__text"><?php esc_html_e( 'Отвечаем ежедневно с 10:00 до 21:00.', 'astra-child' ); ?></p>
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
			<a href="<?php echo esc_url( home_url( '/politika-konfidencialnosti/' ) ); ?>" data-jc-modal-open="doc"><?php esc_html_e( 'Политика конфиденциальности', 'astra-child' ); ?></a>
			<a href="<?php echo esc_url( home_url( '/obrabotka-personalnyh-dannyh/' ) ); ?>" data-jc-modal-open="doc"><?php esc_html_e( 'Обработка персональных данных', 'astra-child' ); ?></a>
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
		'<a class="jc-logo-link" href="%s"><img src="%s" alt="%s" width="220" height="48" loading="lazy" /></a>',
		esc_url( home_url( '/' ) ),
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

	$prices_url  = home_url( '/kalkulyator/#calc' );
	$hub_url     = home_url( '/uslugi/' );

	// Заявка по услуге — модальное окно (форма живёт в подвале страницы).
	$append  = '<section class="jc-block jc-block--form">';
	$append .= '<h2>' . esc_html__( 'Оставить заявку по этой услуге', 'astra-child' ) . '</h2>';
	$append .= '<p>' . esc_html__( 'Опишите задачу, укажите размеры и приложите фото — ответим в течение 15 минут и назовём предварительную цену. Данных не хватит — точную стоимость назовёт мастер на месте. Форма откроется в окне.', 'astra-child' ) . '</p>';
	$append .= '<div class="jc-block__actions">';
	$append .= '<a class="jc-btn jc-btn--primary" href="#" data-jc-modal-open="service">' . esc_html__( 'Оставить заявку', 'astra-child' ) . '</a>';
	$append .= '<a class="jc-btn jc-btn--outline" href="' . esc_url( $hub_url ) . '">' . esc_html__( 'Все услуги', 'astra-child' ) . '</a>';
	$append .= '</div></section>';

	$append .= '<section class="jc-block jc-block--calc">';
	$append .= '<h2>' . esc_html__( 'Считаем по прайсу', 'astra-child' ) . '</h2>';
	$append .= '<p>' . esc_html__( 'Цены на сайте — «от»: на итог влияют размер и метраж, ткань, загрязнение и выезд. Соберите расчёт в калькуляторе за минуту — покажем предварительную сумму до выезда мастера.', 'astra-child' ) . '</p>';
	$append .= '<div class="jc-block__actions">';
	$append .= '<a class="jc-btn jc-btn--primary" href="' . esc_url( $prices_url ) . '">' . esc_html__( 'Рассчитать стоимость', 'astra-child' ) . '</a>';
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
 * 8. Модальные окна с формами (заявка / запись / консультация)
 *
 * Формы Fluent Forms рендерятся в подвале страницы один раз и
 * показываются по клику на [data-jc-modal-open="ключ"] (main.js).
 * ================================================================== */
add_action( 'wp_footer', 'jc_modals', 5 );
function jc_modals() {
	$modals = array(
		'visit'    => array(
			'form' => 'visit',
			'title' => 'Вызвать мастера',
			'text'  => 'Выберите дату и время — мастер приедет в указанный интервал. Ближайший возможный выезд — послезавтра.',
		),
		'service'  => array(
			'form' => 'service',
			'title' => 'Оставить заявку',
			'text'  => 'Опишите задачу и приложите фото — ответим в течение 15 минут и назовём предварительную цену. Есть паспорт изделия или ярлык с составом ткани — пришлите фото и их; деталей не хватает — точную цену назовём на месте.',
		),
		'callback' => array(
			'form' => 'callback',
			'title' => 'Консультация',
			'text'  => 'Оставьте номер — перезвоним в рабочее время и ответим на вопросы.',
		),
		'photo'    => array(
			'form' => 'photo',
			'title' => 'Отправить фото для расчёта',
			'text'  => 'Приложите фото загрязнения, размеры (метраж) и известные детали — назовём предварительную цену до выезда. Не знаете, что за пятно и как давно оно — не страшно: точную оценку даст мастер на месте. Фото паспорта или ярлыка ткани, если есть, тоже пригодится — на коврах он обычно на обратной стороне, на мебели — вшит или приклеен.',
		),
	);

	foreach ( $modals as $key => $m ) {
		$form = jc_fluentform( $m['form'] );
		if ( ! $form ) {
			continue;
		}
		?>
		<div class="jc-modal" id="jc-modal-<?php echo esc_attr( $key ); ?>" role="dialog"
			aria-modal="true" aria-hidden="true" aria-label="<?php echo esc_attr( $m['title'] ); ?>">
			<div class="jc-modal__backdrop" data-jc-modal-close></div>
			<div class="jc-modal__card" role="document" tabindex="-1">
				<button class="jc-modal__close" type="button" data-jc-modal-close aria-label="<?php esc_attr_e( 'Закрыть', 'astra-child' ); ?>">
					&times;
				</button>
				<h3 class="jc-modal__title"><?php echo esc_html( $m['title'] ); ?></h3>
				<p class="jc-modal__text"><?php echo esc_html( $m['text'] ); ?></p>
				<?php echo $form; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped — рендер формы Fluent Forms ?>
			</div>
		</div>
		<?php
	}
}

/* =====================================================================
 * 8.5 Cookie consent: плашка снизу на всех страницах, пока пользователь
 *     не нажмёт «Принимаю» (ответ хранится в cookie jc_cookie_consent).
 * ================================================================== */
add_action( 'wp_footer', 'jc_cookie_banner', 10 );
function jc_cookie_banner() {
	?>
	<div class="jc-cookie" id="jc-cookie" hidden>
		<p class="jc-cookie__text">
			<?php
			printf(
				wp_kses_post(
					'Используя сайт jethim.ru, вы соглашаете на использование файлов cookie и сторонних сервисов, а также принимаете условия <a href="%1$s" data-jc-modal-open="doc">Политики конфиденциальности</a> и <a href="%2$s" data-jc-modal-open="doc">Обработки персональных данных</a>.'
				),
				esc_url( home_url( '/politika-konfidencialnosti/' ) ),
				esc_url( home_url( '/obrabotka-personalnyh-dannyh/' ) )
			);
			?>
		</p>
		<button type="button" class="jc-cookie__btn" id="jc-cookie-accept"><?php esc_html_e( 'Принимаю', 'astra-child' ); ?></button>
	</div>
	<?php
}

/* =====================================================================
 * 8.6 Модалка документов (политика / обработка ПД): ссылки с
 *     data-jc-modal-open="doc" открывают текст в модалке (main.js
 *     подгружает контент через REST), чтобы не терять заполненную форму.
 * ================================================================== */
add_action( 'wp_footer', 'jc_doc_modal', 6 );
function jc_doc_modal() {
	?>
	<div class="jc-modal" id="jc-modal-doc" role="dialog"
		aria-modal="true" aria-hidden="true" aria-label="<?php esc_attr_e( 'Документы', 'astra-child' ); ?>">
		<div class="jc-modal__backdrop" data-jc-modal-close></div>
		<div class="jc-modal__card" role="document" tabindex="-1">
			<button type="button" class="jc-modal__close" data-jc-modal-close aria-label="<?php esc_attr_e( 'Закрыть', 'astra-child' ); ?>">
				&times;
			</button>
			<h3 class="jc-modal__title"><?php esc_html_e( 'Документы', 'astra-child' ); ?></h3>
			<div class="jc-modal__doc"></div>
		</div>
	</div>
	<?php
}

/* =====================================================================
 * 8.7 Промо-баннер расчёта: через 15 сек новым пользователям (без
 *     cookie jc_promo_seen) по центру экрана. Закрытие крестиком /
 *     по фону, переход в калькулятор или успешная отправка формы
 *     обратного звонка пишут cookie — баннер больше не показывается.
 *     Логика — в конце main.js (jcPromo).
 * ================================================================== */
add_action( 'wp_footer', 'jc_promo_banner', 7 );
function jc_promo_banner() {
	?>
	<div class="jc-promo" id="jc-promo" hidden>
		<div class="jc-promo__backdrop" data-jc-promo-close></div>
		<div class="jc-promo__card" role="dialog" aria-modal="true" aria-label="<?php esc_attr_e( 'Рассчитать стоимость', 'astra-child' ); ?>">
			<button class="jc-promo__close" id="jc-promo-close" type="button" aria-label="<?php esc_attr_e( 'Закрыть', 'astra-child' ); ?>">
				&times;
			</button>
			<div class="jc-promo__media">
				<img src="<?php echo esc_url( get_theme_file_uri( 'assets/img/bannerbg.jpg' ) ); ?>"
					alt="" width="900" height="849" loading="lazy" />
			</div>
			<div class="jc-promo__body">
				<p class="jc-promo__title"><?php esc_html_e( 'Узнайте стоимость чистки за минуту', 'astra-child' ); ?></p>
				<p class="jc-promo__text"><?php esc_html_e( 'Калькулятор посчитает цену онлайн — или оставьте номер, и мы перезвоним в течение 15 минут.', 'astra-child' ); ?></p>
				<div class="jc-promo__actions">
					<a class="jc-btn jc-btn--red" id="jc-promo-calc" href="<?php echo esc_url( home_url( '/kalkulyator/' ) ); ?>"><?php esc_html_e( 'Рассчитать стоимость', 'astra-child' ); ?></a>
					<button class="jc-btn jc-btn--outline" id="jc-promo-callback" type="button" data-jc-modal-open="callback"><?php esc_html_e( 'Заказать обратный звонок', 'astra-child' ); ?></button>
				</div>
			</div>
		</div>
	</div>
	<?php
}

/* =====================================================================
 * 9. Загрузка фото к заявке (REST jc/v1/photo)
 *
 * Fluent Forms free не умеет загрузку файлов (PRO), поэтому фото
 * грузим своим endpoint'ом (main.js), а ссылку кладём в скрытое поле
 * формы photo_url.
 * ================================================================== */
add_action( 'wp_enqueue_scripts', 'jc_enqueue_rest_nonce', 30 );
function jc_enqueue_rest_nonce() {
	if ( wp_script_is( 'jc-main', 'enqueued' ) ) {
		wp_localize_script(
			'jc-main',
			'JC_REST',
			array(
				'root'  => esc_url_raw( rest_url( 'jc/v1' ) ),
				'nonce' => wp_create_nonce( 'wp_rest' ),
			)
		);
	}
}

add_action( 'rest_api_init', 'jc_photo_route' );
function jc_photo_route() {
	register_rest_route(
		'jc/v1',
		'/photo',
		array(
			'methods'             => 'POST',
			'callback'            => 'jc_photo_upload',
			'permission_callback' => 'jc_photo_permission',
		)
	);
}

function jc_photo_permission( $request ) {
	$nonce = (string) $request->get_header( 'X-WP-Nonce' );
	if ( ! $nonce || ! wp_verify_nonce( $nonce, 'wp_rest' ) ) {
		return false;
	}

	// Простейший rate-limit: не больше 10 загрузок в час с одного IP.
	$ip = isset( $_SERVER['REMOTE_ADDR'] ) ? sanitize_text_field( wp_unslash( $_SERVER['REMOTE_ADDR'] ) ) : 'na';
	$key = 'jc_photo_' . md5( $ip );
	$n   = (int) get_transient( $key );
	if ( $n >= 10 ) {
		return false;
	}
	set_transient( $key, $n + 1, HOUR_IN_SECONDS );

	return true;
}

function jc_photo_upload() {
	if ( empty( $_FILES['photo'] ) ) {
		return new WP_Error( 'jc_no_file', 'Файл не получен', array( 'status' => 400 ) );
	}

	$file = $_FILES['photo']; // phpcs:ignore WordPress.Security.ValidatedSanitizedInput

	if ( ! empty( $file['error'] ) ) {
		return new WP_Error( 'jc_upload_error', 'Ошибка загрузки файла', array( 'status' => 400 ) );
	}

	if ( (int) $file['size'] > 10 * 1024 * 1024 ) {
		return new WP_Error( 'jc_too_big', 'Фото больше 10 МБ', array( 'status' => 400 ) );
	}

	$check = wp_check_filetype_and_ext( $file['tmp_name'], $file['name'] );
	$ok    = array( 'image/jpeg', 'image/png', 'image/webp' );
	if ( empty( $check['type'] ) || ! in_array( $check['type'], $ok, true ) ) {
		return new WP_Error( 'jc_bad_type', 'Нужен JPG, PNG или WebP', array( 'status' => 400 ) );
	}

	$upload = wp_handle_upload(
		$file,
		array(
			'test_form' => false,
			'test_size' => false,
		)
	);

	if ( isset( $upload['error'] ) ) {
		return new WP_Error( 'jc_upload_failed', $upload['error'], array( 'status' => 500 ) );
	}

	return array(
		'url'  => $upload['url'],
		'name' => sanitize_file_name( $file['name'] ),
	);
}

/* =====================================================================
 * 10. og:image — превью ссылок в Telegram/ВК (картинка из e7_img_import.php).
 * ================================================================== */
add_action( 'wp_head', 'jc_og_meta', 5 );
function jc_og_meta() {
	$url = '';
	$id  = (int) get_option( 'jc_og_image_id' );

	if ( $id ) {
		$url = (string) wp_get_attachment_image_url( $id, 'full' );
	}

	if ( ! $url ) {
		$found = get_posts(
			array(
				'post_type'      => 'attachment',
				'post_status'    => 'inherit',
				'numberposts'    => 1,
				'meta_key'       => '_wp_attached_file',
				'meta_compare'   => 'LIKE',
				'meta_value'     => 'jc-og.jpg',
			)
		);
		$url = $found ? (string) wp_get_attachment_image_url( (int) $found[0]->ID, 'full' ) : '';
	}

	if ( ! $url ) {
		return;
	}

	$req      = isset( $GLOBALS['wp'] ) ? trim( (string) $GLOBALS['wp']->request, '/' ) : '';
	$page_url = $req ? home_url( '/' . $req . '/' ) : home_url( '/' );
	$desc     = jc_seo_description();
	$title    = (string) wp_get_document_title();

	echo '<meta property="og:image" content="' . esc_url( $url ) . '" />' . "\n";
	echo '<meta property="og:image:width" content="1200" />' . "\n";
	echo '<meta property="og:image:height" content="630" />' . "\n";
	echo '<meta property="og:image:alt" content="' . esc_attr( $title ) . '" />' . "\n";
	echo '<meta property="og:url" content="' . esc_url( $page_url ) . '" />' . "\n";
	echo '<meta property="og:type" content="' . ( is_singular() && ! is_front_page() ? 'article' : 'website' ) . '" />' . "\n";
	echo '<meta property="og:site_name" content="' . esc_attr( get_bloginfo( 'name' ) ) . '" />' . "\n";
	echo '<meta property="og:title" content="' . esc_attr( $title ) . '" />' . "\n";
	echo '<meta property="og:description" content="' . esc_attr( $desc ) . '" />' . "\n";
	echo '<meta name="twitter:card" content="summary_large_image" />' . "\n";
}

/* =====================================================================
 * 12. SEO (Этап 8): title/description по страницам, схемы, robots.
 *
 * Карта jc_seo_map() — единая точка правки SEO-строк. Заголовки в
 * <title> подставляются без хвоста «— JetHim»: его добавляет название
 * сайта. H1 страницы меняется отдельно (ключ h1) — крошки и меню
 * продолжают короткие имена страниц из БД (get_the_title не трогаем).
 * Description страницы: карта → post_excerpt → blogdescription.
 * ================================================================== */
function jc_seo_map() {
	return array(
		'glavnaya'                     => array(
			'description' => 'Профессиональная химчистка мягкой мебели, штор, ковров и матрасов с выездом по Москве и области. Безопасная химия, сушка на месте, расчёт по фото за 15 минут. Работаем с 2011 года.',
		),
		'uslugi'                       => array(
			'description' => 'Все услуги JetHim: химчистка диванов, кресел, штор, ковров, ковролина и матрасов с выездом по Москве. Профессиональное оборудование, безопасные составы, сушка на месте.',
		),
		'tseny'                        => array(
			'title'       => 'Цены на химчистку мебели, штор и ковров в Москве',
			'h1'          => 'Цены на химчистку мебели, штор и ковров',
			'description' => 'Прайс на химчистку: диван от 3 000 ₽, кресло от 1 500 ₽, ковёр от 400 ₽/м², шторы от 250 ₽/м². Пакеты «Гостиная/Спальня/Кухня», скидка постоянным клиентам 10%.',
		),
		'kalkulyator'                  => array(
			'title'       => 'Калькулятор стоимости химчистки — рассчитать онлайн',
			'h1'          => 'Калькулятор стоимости химчистки',
			'description' => 'Соберите состав заказа в калькуляторе и сразу увидите предварительную стоимость химчистки. Расчёт бесплатный и ни к чему не обязывает — подтвердим цену по фото.',
		),
		'otzyvy'                       => array(
			'title'       => 'Отзывы о химчистке JetHim — реальные отзывы клиентов',
			'h1'          => 'Отзывы о химчистке JetHim',
			'description' => 'Реальные отзывы клиентов о химчистке JetHim: 600+ отзывов на Юду с именами и датами. Негатив не прячем — на каждый отзыв отвечаем.',
		),
		'do-posle'                     => array(
			'title'       => 'Примеры работ до и после — химчистка мебели и ковров',
			'h1'          => 'Примеры работ: до и после',
			'description' => 'Фотографии заказов до и после химчистки: диваны, шторы, ковры и матрасы из реальных выездов мастеров JetHim по Москве.',
		),
		'kontakty'                     => array(
			'title'       => 'Контакты химчистки JetHim — Москва и МО',
			'h1'          => 'Контакты химчистки JetHim',
			'description' => 'Телефон, Telegram и ВКонтакте химчистки JetHim: +7 (916) 249-34-99. Работаем ежедневно 10:00–21:00 по Москве и области, офиса-приёмки нет.',
		),
		'o-kompanii'                   => array(
			'title'       => 'О компании JetHim — химчистка с 2011 года',
			'h1'          => 'О компании JetHim',
			'description' => 'JetHim — московская химчистка с выездом с 2011 года. Мастера из Международного центра клининга, работа с антиквариатом и дорогими тканями.',
		),
		'zona-vyezda'                  => array(
			'title'       => 'Зона выезда химчистки — Москва до 10 км от МКАД бесплатно',
			'h1'          => 'Зона выезда химчистки',
			'description' => 'Выезжаем по всей Москве и области — без ограничений по направлению. Бесплатный выезд до 10 км от МКАД, дальше — 45 ₽/км по фактическому маршруту.',
		),
		'faq'                          => array(
			'title'       => 'Частые вопросы о химчистке — цены, сроки, гарантии',
			'h1'          => 'Частые вопросы о химчистке',
			'description' => 'Ответы на вопросы о химчистке: цены, сроки сушки, безопасность химии для детей и животных, гарантии, оплата и выезд мастера.',
		),
		'garantii'                     => array(
			'title'       => 'Гарантии на химчистку — бесплатная переделка',
			'h1'          => 'Гарантии на химчистку',
			'description' => 'Цена фиксируется до выезда, бесплатная переделка по нашей вине, профессиональная химия и оборудование. Честно говорим, какие пятна не убрать.',
		),
		'kak-my-rabotaem'              => array(
			'title'       => 'Как мы работаем — 5 шагов до чистой мебели',
			'description' => 'Пять шагов от заявки до чистой мебели: расчёт по фото, выезд, чистка экстрактором, сушка 3–6 часов и приёмка результата. Без сюрпризов в счёте.',
		),
		'blog'                         => array(
			'title'       => 'Блог о химчистке — советы по уходу за мебелью',
			'h1'          => 'Блог о химчистке JetHim',
			'description' => 'Статьи о химчистке: как ухаживать за мебелью, из чего складывается цена, чем отличаются технологии чистки. Советы мастеров JetHim.',
		),
		'zapis-na-vyezd'               => array(
			'title'       => 'Запись на выезд химчистки — выбрать дату',
			'h1'          => 'Запись на выезд',
			'description' => 'Выберите дату и время — мастер JetHim приедет в указанный интервал. Выезд до 10 км от МКАД бесплатный.',
		),
		'divany'                       => array(
			'title' => 'Химчистка диванов в Москве с выездом на дом',
			'h1'    => 'Химчистка диванов',
		),
		'kresla-stulya-pufy'           => array(
			'title' => 'Химчистка кресел, стульев и пуфов в Москве',
			'h1'    => 'Химчистка кресел, стульев и пуфов',
		),
		'krovati-podgolovniki'         => array(
			'title' => 'Химчистка кроватей и подголовников',
			'h1'    => 'Химчистка кроватей и подголовников',
		),
		'mebel-iz-kozhi'               => array(
			'title' => 'Химчистка кожаной мебели в Москве',
			'h1'    => 'Химчистка кожаной мебели',
		),
		'himchistka-myagkoy-mebeli'    => array(
			'title' => 'Химчистка мягкой мебели на дому в Москве',
		),
		'himchistka-shtor'             => array(
			'title' => 'Химчистка штор на весу в Москве',
		),
		'himchistka-kovrov'            => array(
			'title' => 'Химчистка ковров на дому в Москве',
		),
		'himchistka-kovrolina'         => array(
			'title' => 'Химчистка ковролина с выездом по Москве',
		),
		'himchistka-matrasov'          => array(
			'title' => 'Химчистка матрасов на дому в Москве',
		),
		'udalenie-zapahov'             => array(
			'title' => 'Удаление запахов из мебели и ковров',
		),
		'udalenie-katyshkov'           => array(
			'title' => 'Удаление катышков с мебели',
		),
		'zashchitnaya-propitka-tkani'  => array(
			'title' => 'Защитная пропитка ткани после чистки',
		),
		'sushka-mebeli'                => array(
			'title' => 'Сушка мебели после химчистки',
		),
		'dlya-yurlic'                  => array(
			'title' => 'Химчистка для юрлиц — офисы, кафе, гостиницы',
			'h1'    => 'Химчистка для юрлиц',
		),
	);
}

/**
 * Description текущей страницы: карта → post_excerpt → blogdescription.
 *
 * @return string
 */
function jc_seo_description() {
	$map = jc_seo_map();

	if ( is_front_page() ) {
		return isset( $map['glavnaya']['description'] ) ? $map['glavnaya']['description'] : (string) get_bloginfo( 'description' );
	}

	if ( is_home() ) {
		return isset( $map['blog']['description'] ) ? $map['blog']['description'] : (string) get_bloginfo( 'description' );
	}

	if ( is_singular() ) {
		$obj = get_queried_object();
		if ( $obj instanceof WP_Post ) {
			$slug = (string) $obj->post_name;
			if ( isset( $map[ $slug ]['description'] ) ) {
				return $map[ $slug ]['description'];
			}
			$excerpt = trim( (string) $obj->post_excerpt );
			if ( '' !== $excerpt ) {
				return $excerpt;
			}
		}
	}

	return (string) get_bloginfo( 'description' );
}

// <meta name="description"> — раньше og-тегов (приоритет 1).
add_action( 'wp_head', 'jc_seo_meta_description', 1 );
function jc_seo_meta_description() {
	$desc = jc_seo_description();
	if ( '' !== $desc ) {
		echo '<meta name="description" content="' . esc_attr( $desc ) . '" />' . "\n";
	}
}

// <title> из карты; хаб услуг по-прежнему через JC_HUB_SEO_TITLE.
add_filter( 'document_title_parts', 'jc_hub_seo_title' );
function jc_hub_seo_title( $parts ) {
	if ( is_front_page() ) {
		return $parts; // «JetHim — {tagline}» уже хороший заголовок главной.
	}

	if ( is_home() ) {
		$map = jc_seo_map();
		if ( isset( $map['blog']['title'] ) ) {
			$parts['title'] = $map['blog']['title'];
		}
		return $parts;
	}

	$obj = get_queried_object();
	if ( $obj instanceof WP_Post && 'page' === $obj->post_type ) {
		if ( 'uslugi' === $obj->post_name && defined( 'JC_HUB_SEO_TITLE' ) ) {
			$parts['title'] = preg_replace( '/\s*—\s*JetHim$/u', '', JC_HUB_SEO_TITLE );
			return $parts;
		}

		$map = jc_seo_map();
		$slug = (string) $obj->post_name;
		if ( isset( $map[ $slug ]['title'] ) ) {
			$parts['title'] = $map[ $slug ]['title'];
		}
	}

	return $parts;
}

// H1 из карты (страница-запроситель или блог; крошки/меню не затронуты).
add_filter( 'astra_the_title', 'jc_seo_h1', 10, 2 );
function jc_seo_h1( $title, $post_id = 0 ) {
	$map  = jc_seo_map();
	$slug = '';

	if ( is_singular() ) {
		// $post_id = 0 означает «текущий пост» (astra_the_title без аргумента).
		$obj = get_queried_object();
		if ( ! ( $obj instanceof WP_Post ) || ( $post_id && (int) $post_id !== (int) $obj->ID ) ) {
			return $title;
		}
		$slug = (string) $obj->post_name;
	} elseif ( is_home() && ! is_front_page() ) {
		// Страница записей (Blog): H1 рендерится как archive-title, не singular.
		$slug = 'blog';
	} else {
		return $title;
	}

	if ( isset( $map[ $slug ]['h1'] ) ) {
		return $map[ $slug ]['h1'];
	}

	return $title;
}

// На главной прячем H1 «Главная» из шаблона Astra — в контенте есть
// настоящий H1 «Химчистка мягкой мебели, штор и ковров…».
add_filter( 'astra_the_title_enabled', 'jc_seo_hide_front_title' );
function jc_seo_hide_front_title( $enabled ) {
	if ( is_front_page() ) {
		return false;
	}

	return $enabled;
}

// Архив блога: H1 вместо сухого «Блог».
add_filter( 'the_archive_title', 'jc_seo_archive_title' );
function jc_seo_archive_title( $title ) {
	if ( is_home() ) {
		return 'Блог о химчистке JetHim';
	}

	return $title;
}

// Страница «Страница не найдена» — по-русски.
add_filter( 'astra_the_404_page_title', 'jc_seo_404_title' );
function jc_seo_404_title() {
	return 'Страница не найдена';
}

add_filter( 'astra_default_strings', 'jc_seo_404_strings' );
function jc_seo_404_strings( $strings ) {
	$strings['string-404-sub-title'] = 'Возможно, ссылка устарела или страница была перемещена. Вернитесь на главную или посмотрите услуги и цены.';

	return $strings;
}

// Дымовую страницу (тест блоков) не индексируем и убираем из sitemap.
add_filter( 'wp_robots', 'jc_seo_robots' );
function jc_seo_robots( $robots ) {
	if ( is_page( 'jhb-smoke' ) ) {
		$robots['noindex'] = true;
		$robots['follow']  = true;
	}

	return $robots;
}

add_filter( 'wp_sitemaps_posts_query_args', 'jc_seo_sitemap_skip', 10, 2 );
function jc_seo_sitemap_skip( $args, $post_type ) {
	if ( 'page' !== $post_type ) {
		return $args;
	}

	$smoke = get_page_by_path( 'jhb-smoke', OBJECT, 'page' );
	if ( $smoke ) {
		$args['post__not_in'][] = (int) $smoke->ID;
	}

	return $args;
}

// LocalBusiness — на всех страницах (адрес/гео — Москва, Кутузовский).
add_action( 'wp_head', 'jc_seo_local_business', 6 );
function jc_seo_local_business() {
	$contacts = jc_contacts();
	$og_id    = (int) get_option( 'jc_og_image_id' );
	$image    = $og_id ? (string) wp_get_attachment_image_url( $og_id, 'full' ) : '';

	$data = array(
		'@context'      => 'https://schema.org',
		'@type'         => 'LocalBusiness',
		'name'          => 'JetHim',
		'description'   => 'Химчистка мягкой мебели, штор, ковров и матрасов с выездом по Москве и Московской области.',
		'url'           => home_url( '/' ),
		'telephone'     => '+79162493499',
		'priceRange'    => '₽₽',
		'address'       => array(
			'@type'           => 'PostalAddress',
			'streetAddress'   => 'Кутузовский проспект',
			'addressLocality' => 'Москва',
			'addressCountry'  => 'RU',
		),
		'geo'           => array(
			'@type'     => 'GeoCoordinates',
			'latitude'  => 55.744,
			'longitude' => 37.43,
		),
		'openingHoursSpecification' => array(
			'@type'     => 'OpeningHoursSpecification',
			'dayOfWeek' => array( 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday' ),
			'opens'     => '10:00',
			'closes'    => '21:00',
		),
		'areaServed'   => array(
			array(
				'@type' => 'City',
				'name'  => 'Москва',
			),
			array(
				'@type' => 'AdministrativeArea',
				'name'  => 'Московская область',
			),
		),
		'sameAs'        => array( $contacts['vk'], $contacts['telegram'], $contacts['youdo'] ),
	);

	if ( $image ) {
		$data['image'] = $image;
	}

	echo '<script type="application/ld+json">' . wp_json_encode( $data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES ) . '</script>' . "\n";
}

// FAQPage — на /faq/ и на главной (опции пишет e6_pages.php).
add_action( 'wp_head', 'jc_seo_faq_schema', 7 );
function jc_seo_faq_schema() {
	$items = array();

	if ( is_page( 'faq' ) ) {
		$items = (array) get_option( 'jc_faq_items', array() );
	} elseif ( is_front_page() ) {
		$items = (array) get_option( 'jc_faq_home', array() );
	}

	if ( empty( $items ) ) {
		return;
	}

	$questions = array();
	foreach ( $items as $q => $a ) {
		$questions[] = array(
			'@type'          => 'Question',
			'name'           => (string) $q,
			'acceptedAnswer' => array(
				'@type' => 'Answer',
				'text'  => (string) $a,
			),
		);
	}

	$data = array(
		'@context'   => 'https://schema.org',
		'@type'      => 'FAQPage',
		'mainEntity' => $questions,
	);

	echo '<script type="application/ld+json">' . wp_json_encode( $data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES ) . '</script>' . "\n";
}

/* =====================================================================
 * 11. Точки расширения для следующих этапов:
 *    - Этап 5 (формы) — контакты брать через jc_contacts() / фильтр jc_contacts.
 * ================================================================== */
