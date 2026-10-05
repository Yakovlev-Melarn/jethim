<?php
/**
 * Template Name: Услуга (JetHim)
 *
 * Единый шаблон страниц услуги: страница хранит только свой текст,
 * общие секции (расчёт, другие услуги, CTA) добавляет functions.php
 * через фильтр the_content — дублирование сводится к контенту.
 *
 * @package astra-child
 */

defined( 'ABSPATH' ) || exit;

// Полный лэйаут страницы берём у Astra (шапка, петля, сайдбары, футер).
$astra_page = locate_template( 'page.php' );
if ( $astra_page ) {
	include $astra_page;
}
