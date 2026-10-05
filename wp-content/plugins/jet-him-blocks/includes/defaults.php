<?php
/**
 * Дерево цен калькулятора — источник истины: PRICE.md (бриф, Этап 0).
 *
 * Всё, что правит заказчик в инспекторе блока, лежит в атрибуте data блока;
 * эти значения — только значения по умолчанию для первого встава блока
 * и подстраховка при пустом атрибуте.
 */

defined( 'ABSPATH' ) || exit;

/**
 * @return array
 */
function jhb_calc_defaults() {
	return array(
		/*
		 * Мебель — прайс § A. type:
		 *  qty      — обычный счётчик штук,
		 *  mattress — счётчик + чекбокс «обе стороны» (×2).
		 * section — заголовок группы в UI.
		 */
		'furniture' => array(
			array( 'id' => 'sofa-2', 'section' => 'Диваны и кресла', 'label' => 'Диван прямой 2-местный (100–120 см)', 'price' => 3000, 'type' => 'qty' ),
			array( 'id' => 'sofa-3', 'section' => 'Диваны и кресла', 'label' => 'Диван прямой 3-местный (150–180 см)', 'price' => 4000, 'type' => 'qty' ),
			array( 'id' => 'sofa-4', 'section' => 'Диваны и кресла', 'label' => 'Диван прямой 4-местный (200–240 см)', 'price' => 6000, 'type' => 'qty' ),
			array( 'id' => 'sofa-corner-3', 'section' => 'Диваны и кресла', 'label' => 'Диван угловой 3-местный (150–180 см)', 'price' => 6500, 'type' => 'qty' ),
			array( 'id' => 'sofa-corner-4', 'section' => 'Диваны и кресла', 'label' => 'Диван угловой 4-местный (200–240 см)', 'price' => 8500, 'type' => 'qty' ),
			array( 'id' => 'kitchen-corner', 'section' => 'Диваны и кресла', 'label' => 'Кухонный уголок', 'price' => 4000, 'type' => 'qty' ),
			array( 'id' => 'armchair', 'section' => 'Диваны и кресла', 'label' => 'Кресло', 'price' => 1500, 'type' => 'qty' ),
			array( 'id' => 'chair', 'section' => 'Диваны и кресла', 'label' => 'Стул', 'price' => 500, 'type' => 'qty' ),
			array( 'id' => 'sleep-place', 'section' => 'Спальные места и подушки', 'label' => 'Спальное место (доп к мебели)', 'price' => 1500, 'type' => 'qty' ),
			array( 'id' => 'pillow', 'section' => 'Спальные места и подушки', 'label' => 'Съёмная подушка', 'price' => 400, 'type' => 'qty' ),
			array( 'id' => 'bed-kids', 'section' => 'Кровати', 'label' => 'Детская', 'price' => 1000, 'type' => 'qty' ),
			array( 'id' => 'bed-single', 'section' => 'Кровати', 'label' => 'Односпальная', 'price' => 1800, 'type' => 'qty' ),
			array( 'id' => 'bed-15', 'section' => 'Кровати', 'label' => 'Полутороспальная', 'price' => 2000, 'type' => 'qty' ),
			array( 'id' => 'bed-double', 'section' => 'Кровати', 'label' => 'Двуспальная', 'price' => 2500, 'type' => 'qty' ),
			array( 'id' => 'bed-king', 'section' => 'Кровати', 'label' => 'Кинг-сайз', 'price' => 3500, 'type' => 'qty' ),
			array( 'id' => 'matt-kids', 'section' => 'Матрасы (одна сторона)', 'label' => 'Детский 80 × 60 см', 'price' => 800, 'type' => 'mattress' ),
			array( 'id' => 'matt-single', 'section' => 'Матрасы (одна сторона)', 'label' => 'Односпальный 90 × 200 см', 'price' => 1500, 'type' => 'mattress' ),
			array( 'id' => 'matt-15', 'section' => 'Матрасы (одна сторона)', 'label' => 'Полутороспальный 120 × 200 см', 'price' => 2500, 'type' => 'mattress' ),
			array( 'id' => 'matt-double', 'section' => 'Матрасы (одна сторона)', 'label' => 'Двуспальный 140 × 200 см', 'price' => 3000, 'type' => 'mattress' ),
			array( 'id' => 'matt-king', 'section' => 'Матрасы (одна сторона)', 'label' => 'Кинг-сайз 160 × 200 см', 'price' => 3500, 'type' => 'mattress' ),
			array( 'id' => 'matt-super-king', 'section' => 'Матрасы (одна сторона)', 'label' => 'Супер кинг-сайз 180 × 200 см', 'price' => 4000, 'type' => 'mattress' ),
		),

		// Шторы — прайс § B, цена за м². Ключ: "материал|конструкция".
		'curtains' => array(
			'materials' => array(
				array( 'id' => 'synth', 'label' => 'Синтетика' ),
				array( 'id' => 'linen', 'label' => 'Лен/хлопок' ),
				array( 'id' => 'velvet', 'label' => 'Бархат/шелк' ),
			),
			'builds'    => array(
				array( 'id' => 'tulle', 'label' => 'Тюль' ),
				array( 'id' => 'single', 'label' => 'Однослойные' ),
				array( 'id' => 'double', 'label' => 'Двуслойные' ),
			),
			'prices'    => array(
				'synth|tulle'  => 200,
				'synth|single' => 300,
				'synth|double' => 400,
				'linen|tulle'  => 300,
				'linen|single' => 400,
				'linen|double' => 600,
				'velvet|tulle' => 600,
				'velvet|single' => 900,
				'velvet|double' => 1200,
			),
		),

		// Ковры — прайс § C, цена за м². Пустые ячейки не существуют (их нет в prices).
		'carpets'   => array(
			'materials' => array(
				array( 'id' => 'synth', 'label' => 'Синтетика' ),
				array( 'id' => 'wool', 'label' => 'Шерсть' ),
				array( 'id' => 'cotton', 'label' => 'Хлопок/лен' ),
				array( 'id' => 'viscose', 'label' => 'Вискоза' ),
				array( 'id' => 'silk', 'label' => 'Шелк' ),
				array( 'id' => 'hand', 'label' => 'Ручная работа' ),
			),
			'types'     => array(
				array( 'id' => 'short', 'label' => 'Ковёр (ворс до 1 см)' ),
				array( 'id' => 'linoleum', 'label' => 'Ковролин' ),
				array( 'id' => 'mid', 'label' => 'Ворс 1–2 см' ),
				array( 'id' => 'long', 'label' => 'Ворс более 2 см' ),
			),
			'prices'    => array(
				'synth|short'    => 250,
				'synth|linoleum' => 250,
				'synth|mid'      => 350,
				'synth|long'     => 500,
				'wool|short'     => 500,
				'wool|linoleum'  => 500,
				'wool|mid'       => 750,
				'wool|long'      => 1000,
				'cotton|short'   => 500,
				'cotton|linoleum' => 500,
				'viscose|short'  => 800,
				'silk|short'     => 1200,
				'hand|linoleum'  => 1000,
				'hand|mid'       => 1000,
				'hand|long'      => 1000,
			),
		),

		/*
		 * Допы — прайс § D. control:
		 *  counter — счётчик интервалов (сушка 30 мин),
		 *  qty     — количество единиц (катышки, запахи),
		 *  check   — чекбокс без цены (пропитка, разбор/сборка).
		 * price = null — «рассчитывается индивидуально», на сумму не влияет.
		 */
		'extras'    => array(
			array( 'id' => 'drying', 'label' => 'Сушка мебели, 30 минут', 'price' => 500, 'control' => 'counter' ),
			array( 'id' => 'lint', 'label' => 'Удаление катышков', 'price' => 500, 'control' => 'qty' ),
			array( 'id' => 'odor', 'label' => 'Удаление запахов', 'price' => 500, 'control' => 'qty' ),
			array( 'id' => 'protect', 'label' => 'Защитная пропитка ткани', 'price' => null, 'control' => 'check' ),
			array( 'id' => 'assembly', 'label' => 'Разбор/сборка мебели', 'price' => null, 'control' => 'check' ),
		),

		// Выезд и скидка — прайс § E.
		'trip'      => array(
			'free_km' => 10,
			'per_km'  => 45,
		),
		'discount'  => array(
			'percent' => 10,
		),

		// Тексты под итогом — прайс § F (+ честное про сроки выезда, § E).
		'texts'     => array(
			'note'      => "Расчёт предварительный. Все цены — «от».\nТочную стоимость мастер назовёт после просмотра фото.\nМы не обещаем 100% удаление пятен — каждый заказ индивидуален.",
			'lead_time' => 'Выезд не в день заказа, а за 2–3 дня.',
		),
	);
}
