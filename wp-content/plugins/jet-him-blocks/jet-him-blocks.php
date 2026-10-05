<?php
/**
 * Plugin Name:       JetHim Blocks
 * Description:       Кастомные блоки JetHim: калькулятор стоимости, слайдер «до/после», CTA/контакты.
 * Version:           1.0.0
 * Requires at least: 6.0
 * Requires PHP:      8.0
 * Author:            JetHim
 * License:           GPL-2.0-or-later
 * Text Domain:       jet-him-blocks
 *
 * Блоки регистрируются из метаданных (block.json), рендерятся на сервере
 * (save = null), живой пересчёт — на чистом JS без сборщика.
 */

defined( 'ABSPATH' ) || exit;

define( 'JHB_VERSION', '1.0.0' );
define( 'JHB_DIR', plugin_dir_path( __FILE__ ) );
define( 'JHB_URL', plugin_dir_url( __FILE__ ) );

require_once JHB_DIR . 'includes/defaults.php';
require_once JHB_DIR . 'includes/calc.php';

/**
 * Регистрация блоков и их ассетов.
 */
add_action( 'init', 'jhb_bootstrap' );
function jhb_bootstrap() {
	jhb_register_assets();
	jhb_register_categories();

	foreach ( array( 'calc', 'before-after', 'cta' ) as $slug ) {
		register_block_type( JHB_DIR . 'blocks/' . $slug );
	}
}

/**
 * Скрипты и стили. Хэндлы указываются в block.json напрямую (без "file:"),
 * чтобы задать зависимости: данные прайса (jhb-data) грузятся раньше всего.
 */
function jhb_register_assets() {
	wp_register_script( 'jhb-data', false, array(), JHB_VERSION, true );
	wp_add_inline_script(
		'jhb-data',
		'window.JHB = ' . wp_json_encode( array( 'calc' => jhb_calc_defaults() ) ) . ';'
	);

	wp_register_style( 'jhb-style', JHB_URL . 'assets/style.css', array(), JHB_VERSION );
	wp_register_style( 'jhb-editor-style', JHB_URL . 'assets/editor.css', array( 'jhb-style' ), JHB_VERSION );

	wp_register_script(
		'jhb-view',
		JHB_URL . 'assets/view.js',
		array( 'jhb-data' ),
		JHB_VERSION,
		true
	);

	$editor_deps = array(
		'wp-blocks',
		'wp-element',
		'wp-block-editor',
		'wp-components',
		'wp-i18n',
		'wp-compose',
		'wp-server-side-render',
		'wp-media-utils',
		'jhb-data',
		'jhb-view',
	);

	wp_register_script( 'jhb-calc-editor', JHB_URL . 'blocks/calc/editor.js', $editor_deps, JHB_VERSION, true );
	wp_register_script( 'jhb-ba-editor', JHB_URL . 'blocks/before-after/editor.js', $editor_deps, JHB_VERSION, true );
	wp_register_script( 'jhb-cta-editor', JHB_URL . 'blocks/cta/editor.js', $editor_deps, JHB_VERSION, true );
}

/**
 * Своя категория в редакторе — блоки ищутся по имени «JetHim».
 */
function jhb_register_categories() {
	add_filter(
		'block_categories_all',
		function ( $categories ) {
			$categories[] = array(
				'slug'  => 'jet-him',
				'title' => 'JetHim',
				'icon'  => null,
			);

			return $categories;
		}
	);
}
