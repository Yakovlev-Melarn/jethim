<?php
/**
 * Расчёт итога калькулятора на сервере.
 *
 * Нужен для: (1) тестов сверки с PRICE.md на Этапе 3, (2) предзаполнения
 * итога при первом рендере, (3) сводки в письме/Telegram на Этапе 5.
 * Живой пересчёт в браузере — assets/view.js, формулы обязаны совпадать
 * строка в строку (см. комментарии к каждому блоку).
 */

defined( 'ABSPATH' ) || exit;

/**
 * Конфигурация калькулятора: атрибут data блока или значения по умолчанию.
 *
 * @param array $attributes Атрибуты блока.
 * @return array
 */
function jhb_calc_config( $attributes ) {
	$data = ( isset( $attributes['data'] ) && is_array( $attributes['data'] ) ) ? $attributes['data'] : array();

	if ( ! $data ) {
		return jhb_calc_defaults();
	}

	// Атрибут авторитетен целиком на верхнем уровне: если заказчик удалил
	// позицию — она не должна вернуться из дефолтов.
	return array_merge( jhb_calc_defaults(), $data );
}

/**
 * Состояние по умолчанию (ничего не выбрано).
 *
 * @return array
 */
function jhb_calc_state_defaults() {
	return array(
		'qty'     => array(),
		'both'    => array(),
		'curtain' => array( 'material' => '', 'build' => '', 'area' => 0 ),
		'carpet'  => array( 'material' => '', 'type' => '', 'area' => 0 ),
		'extras'  => array(),
		'flags'   => array(),
		'km'      => 0,
		'discount' => false,
	);
}

/**
 * Итог по модели из PLAN.md § 5 / PRICE.md:
 *   Итог = сумма(позиции) + допы с ценой + выезд, минус скидка −10%.
 *
 * @param array $config Конфигурация (jhb_calc_config).
 * @param array $state  Состояние выбора пользователя.
 * @return array Разбивка: furniture, curtains, carpets, extras, trip,
 *               subtotal, discount, total, lines[], flags[].
 */
function jhb_calc_total( array $config, array $state ) {
	$state = array_merge( jhb_calc_state_defaults(), $state );

	$lines = array();
	$flags = array();

	/* --- Мебель: цена × кол-во, матрас с «обе стороны» → ×2 --- */
	$furniture = 0;
	$qty       = is_array( $state['qty'] ) ? $state['qty'] : array();
	$both      = is_array( $state['both'] ) ? $state['both'] : array();

	foreach ( $config['furniture'] as $item ) {
		$n = (int) ( isset( $qty[ $item['id'] ] ) ? $qty[ $item['id'] ] : 0 );
		if ( $n < 1 ) {
			continue;
		}

		$sum = (int) $item['price'] * $n;

		if ( 'mattress' === $item['type'] && ! empty( $both[ $item['id'] ] ) ) {
			$sum *= 2; // чекбокс «обе стороны» (PRICE.md § A).
		}

		$furniture += $sum;
		$lines[]    = array(
			'label' => $item['label'] . ( 'mattress' === $item['type'] && ! empty( $both[ $item['id'] ] ) ? ', обе стороны' : '' ) . ' × ' . $n,
			'sum'   => $sum,
		);
	}

	/* --- Шторы: цена за м² × площадь --- */
	$curtains = 0;
	$curtain  = is_array( $state['curtain'] ) ? $state['curtain'] : array();
	$area     = isset( $curtain['area'] ) ? round( (float) $curtain['area'], 2 ) : 0;

	if ( $area > 0 && ! empty( $curtain['material'] ) && ! empty( $curtain['build'] ) ) {
		$key  = $curtain['material'] . '|' . $curtain['build'];
		$rate = isset( $config['curtains']['prices'][ $key ] ) ? (int) $config['curtains']['prices'][ $key ] : 0;
		$curtains = $rate * $area;

		if ( $curtains > 0 ) {
			$lines[] = array(
				'label' => jhb_calc_label( $config['curtains']['materials'], $curtain['material'] ) . ', '
					. jhb_calc_label( $config['curtains']['builds'], $curtain['build'] ) . ' — ' . jhb_calc_num( $area ) . ' м²',
				'sum'   => $curtains,
			);
		}
	}

	/* --- Ковры: цена за м² × площадь, только существующие комбинации --- */
	$carpets = 0;
	$carpet  = is_array( $state['carpet'] ) ? $state['carpet'] : array();
	$c_area  = isset( $carpet['area'] ) ? round( (float) $carpet['area'], 2 ) : 0;

	if ( $c_area > 0 && ! empty( $carpet['material'] ) && ! empty( $carpet['type'] ) ) {
		$key    = $carpet['material'] . '|' . $carpet['type'];
		$rate   = isset( $config['carpets']['prices'][ $key ] ) ? (int) $config['carpets']['prices'][ $key ] : 0;
		$carpets = $rate * $c_area;

		if ( $carpets > 0 ) {
			$lines[] = array(
				'label' => jhb_calc_label( $config['carpets']['materials'], $carpet['material'] ) . ', '
					. jhb_calc_label( $config['carpets']['types'], $carpet['type'] ) . ' — ' . jhb_calc_num( $c_area ) . ' м²',
				'sum'   => $carpets,
			);
		}
	}

	/* --- Допы: с ценой — в сумму; без цены — только флаги --- */
	$extras      = 0;
	$extra_state = is_array( $state['extras'] ) ? $state['extras'] : array();

	foreach ( $config['extras'] as $extra ) {
		$price = ( isset( $extra['price'] ) && null !== $extra['price'] && '' !== $extra['price'] ) ? (int) $extra['price'] : null;

		if ( null === $price ) {
			if ( ! empty( $state['flags'][ $extra['id'] ] ) ) {
				$flags[] = $extra['label'];
			}
			continue;
		}

		$n = (int) ( isset( $extra_state[ $extra['id'] ] ) ? $extra_state[ $extra['id'] ] : 0 );
		if ( $n < 1 ) {
			continue;
		}

		$extras += $price * $n;
		$lines[]  = array(
			'label' => $extra['label'] . ' × ' . $n,
			'sum'   => $price * $n,
		);
	}

	/* --- Выезд: до 10 км бесплатно, далее 45 ₽/км --- */
	$km    = (int) $state['km'];
	$free  = (int) $config['trip']['free_km'];
	$trip  = $km > $free ? ( $km - $free ) * (int) $config['trip']['per_km'] : 0;

	if ( $trip > 0 ) {
		$lines[] = array(
			'label' => 'Выезд: ' . $km . ' км от МКАД',
			'sum'   => $trip,
		);
	}

	$subtotal = $furniture + $curtains + $carpets + $extras + $trip;
	$discount = ! empty( $state['discount'] ) ? (int) round( $subtotal * (int) $config['discount']['percent'] / 100 ) : 0;

	if ( $discount > 0 ) {
		$lines[] = array(
			'label' => 'Скидка постоянному клиенту −' . (int) $config['discount']['percent'] . '%',
			'sum'   => -$discount,
		);
	}

	return array(
		'furniture' => $furniture,
		'curtains'  => $curtains,
		'carpets'   => $carpets,
		'extras'    => $extras,
		'trip'      => $trip,
		'subtotal'  => $subtotal,
		'discount'  => $discount,
		'total'     => $subtotal - $discount,
		'lines'     => $lines,
		'flags'     => $flags,
	);
}

/**
 * Подпись позиции по id — чтобы не таскать текст в состоянии.
 *
 * @param array  $list Список элементов [{id,label}].
 * @param string $id   Идентификатор.
 * @return string
 */
function jhb_calc_label( $list, $id ) {
	foreach ( (array) $list as $item ) {
		if ( isset( $item['id'] ) && $item['id'] === $id ) {
			return isset( $item['label'] ) ? $item['label'] : '';
		}
	}

	return '';
}

/**
 * Оформление суммы: 8500 → «8 500» (неразрывный пробел, как в прайсе).
 *
 * @param int|float|string $amount Сумма.
 * @return string
 */
function jhb_money( $amount ) {
	return number_format( (float) $amount, 0, ',', "\u{00A0}" );
}

/**
 * Число для подписей: 7.5 → «7,5» — как num() в assets/view.js,
 * чтобы разбивка в скрытом поле формы совпадала в обеих реализациях.
 *
 * @param int|float|string $value Значение.
 * @return string
 */
function jhb_calc_num( $value ) {
	$rounded = round( ( (float) $value ) * 100 ) / 100;
	$out     = rtrim( rtrim( sprintf( '%.2F', $rounded ), '0' ), '.' );

	return str_replace( '.', ',', $out );
}
