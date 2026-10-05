<?php
/**
 * Рендер калькулятора (фронт и превью в редакторе — одна и та же разметка).
 *
 * Живой пересчёт — assets/view.js, начальное состояние пустое (итог 0 ₽).
 */

defined( 'ABSPATH' ) || exit;

$config    = jhb_calc_config( $attributes );
// Третий аргумент рендера — объект WP_Block (не массив), своего id у блока нет,
// поэтому имя для скрытых полей формы генерируем сами.
$block_id  = uniqid( 'calc-' );

$tabs = array(
	'furniture' => 'Мебель',
	'curtains'  => 'Шторы',
	'carpets'   => 'Ковры',
);

// Мебель по группам, порядок сохраняем как в прайсе.
$sections = array();
foreach ( $config['furniture'] as $item ) {
	$sections[ $item['section'] ][] = $item;
}

// Только существующие комбинации: «материал|тип» есть в prices.
$curtain_key = '';
foreach ( $config['curtains']['materials'] as $m ) {
	foreach ( $config['curtains']['builds'] as $b ) {
		if ( isset( $config['curtains']['prices'][ $m['id'] . '|' . $b['id'] ] ) ) {
			$curtain_key = $m['id'] . '|' . $b['id'];
			break 2;
		}
	}
}
$default_curtain = explode( '|', $curtain_key );

$carpet_key = '';
foreach ( $config['carpets']['materials'] as $m ) {
	foreach ( $config['carpets']['types'] as $t ) {
		if ( isset( $config['carpets']['prices'][ $m['id'] . '|' . $t['id'] ] ) ) {
			$carpet_key = $m['id'] . '|' . $t['id'];
			break 2;
		}
	}
}
$default_carpet = explode( '|', $carpet_key );

?>
<div <?php echo get_block_wrapper_attributes(
	array(
		'class'          => 'jc-calc',
		'data-jc-calc'   => '',
		'data-jc-config' => wp_json_encode( $config ),
	)
); ?>>

	<div class="jc-calc__tabs" role="tablist">
		<?php $first = true; ?>
		<?php foreach ( $tabs as $key => $label ) : ?>
			<button type="button"
				class="jc-calc__tab<?php echo $first ? ' is-active' : ''; ?>"
				role="tab"
				aria-selected="<?php echo $first ? 'true' : 'false'; ?>"
				data-jc-tab="<?php echo esc_attr( $key ); ?>">
				<?php echo esc_html( $label ); ?>
			</button>
			<?php $first = false; ?>
		<?php endforeach; ?>
	</div>

	<div class="jc-calc__panels">

		<!-- Мебель -->
		<div class="jc-calc__panel is-active" data-jc-panel="furniture">
			<?php foreach ( $sections as $section => $items ) : ?>
				<div class="jc-calc__section">
					<h4 class="jc-calc__section-title"><?php echo esc_html( $section ); ?></h4>

					<?php foreach ( $items as $item ) : ?>
						<div class="jc-calc__row" data-jc-row="<?php echo esc_attr( $item['id'] ); ?>">
							<div class="jc-calc__row-main">
								<span class="jc-calc__row-label"><?php echo esc_html( $item['label'] ); ?></span>
								<span class="jc-calc__row-price">от <?php echo esc_html( jhb_money( $item['price'] ) ); ?> ₽</span>
							</div>

							<div class="jc-calc__stepper">
								<button type="button" class="jc-calc__step" data-jc-step="<?php echo esc_attr( $item['id'] ); ?>" data-jc-dir="-1" aria-label="Уменьшить">−</button>
								<input class="jc-calc__qty" type="number" min="0" step="1" value="0"
									data-jc-qty="<?php echo esc_attr( $item['id'] ); ?>"
									aria-label="Количество: <?php echo esc_attr( $item['label'] ); ?>">
								<button type="button" class="jc-calc__step" data-jc-step="<?php echo esc_attr( $item['id'] ); ?>" data-jc-dir="1" aria-label="Увеличить">+</button>
							</div>

							<?php if ( 'mattress' === $item['type'] ) : ?>
								<label class="jc-calc__both">
									<input type="checkbox" data-jc-both="<?php echo esc_attr( $item['id'] ); ?>">
									<span>Обе стороны ×2</span>
								</label>
							<?php endif; ?>
						</div>
					<?php endforeach; ?>
				</div>
			<?php endforeach; ?>
		</div>

		<!-- Шторы -->
		<div class="jc-calc__panel" data-jc-panel="curtains" hidden>
			<div class="jc-calc__fields">
				<label class="jc-calc__field">
					<span>Материал</span>
					<select data-jc-curtain="material">
						<?php foreach ( $config['curtains']['materials'] as $m ) : ?>
							<option value="<?php echo esc_attr( $m['id'] ); ?>"
								<?php selected( $default_curtain[0] ?? '', $m['id'] ); ?>><?php echo esc_html( $m['label'] ); ?></option>
						<?php endforeach; ?>
					</select>
				</label>

				<label class="jc-calc__field">
					<span>Конструкция</span>
					<select data-jc-curtain="build">
						<?php foreach ( $config['curtains']['builds'] as $b ) : ?>
							<option value="<?php echo esc_attr( $b['id'] ); ?>"
								<?php selected( $default_curtain[1] ?? '', $b['id'] ); ?>><?php echo esc_html( $b['label'] ); ?></option>
						<?php endforeach; ?>
					</select>
				</label>

				<div class="jc-calc__field jc-calc__field--rate">
					<span>Цена</span>
					<strong data-jc-curtain-rate>—</strong>
					<span>₽/м²</span>
				</div>
			</div>

			<div class="jc-calc__fields">
				<label class="jc-calc__field">
					<span>Ширина, м</span>
					<input type="number" min="0" step="0.1" placeholder="3" data-jc-curtain-size="w">
				</label>
				<label class="jc-calc__field">
					<span>Высота, м</span>
					<input type="number" min="0" step="0.1" placeholder="2.5" data-jc-curtain-size="h">
				</label>
				<label class="jc-calc__field">
					<span>Площадь, м²</span>
					<input type="number" min="0" step="0.1" data-jc-curtain-size="area">
				</label>
			</div>

			<p class="jc-calc__hint">Считаем площадь автоматически: ширина × высота. Можно указать готовую площадь.</p>
		</div>

		<!-- Ковры -->
		<div class="jc-calc__panel" data-jc-panel="carpets" hidden>
			<div class="jc-calc__fields">
				<label class="jc-calc__field">
					<span>Материал</span>
					<select data-jc-carpet="material">
						<?php foreach ( $config['carpets']['materials'] as $m ) : ?>
							<?php
							$has_any = false;
							foreach ( $config['carpets']['types'] as $t ) {
								if ( isset( $config['carpets']['prices'][ $m['id'] . '|' . $t['id'] ] ) ) {
									$has_any = true;
									break;
								}
							}
							if ( ! $has_any ) {
								continue;
							}
							?>
							<option value="<?php echo esc_attr( $m['id'] ); ?>"
								<?php selected( $default_carpet[0] ?? '', $m['id'] ); ?>><?php echo esc_html( $m['label'] ); ?></option>
						<?php endforeach; ?>
					</select>
				</label>

				<label class="jc-calc__field">
					<span>Тип</span>
					<select data-jc-carpet="type">
						<?php foreach ( $config['carpets']['types'] as $t ) : ?>
							<?php
							$has_any = false;
							foreach ( $config['carpets']['materials'] as $m ) {
								if ( isset( $config['carpets']['prices'][ $m['id'] . '|' . $t['id'] ] ) ) {
									$has_any = true;
									break;
								}
							}
							if ( ! $has_any ) {
								continue;
							}
							?>
							<option value="<?php echo esc_attr( $t['id'] ); ?>"
								<?php selected( $default_carpet[1] ?? '', $t['id'] ); ?>><?php echo esc_html( $t['label'] ); ?></option>
						<?php endforeach; ?>
					</select>
				</label>

				<div class="jc-calc__field jc-calc__field--rate">
					<span>Цена</span>
					<strong data-jc-carpet-rate>—</strong>
					<span>₽/м²</span>
				</div>
			</div>

			<div class="jc-calc__fields">
				<label class="jc-calc__field">
					<span>Площадь, м²</span>
					<input type="number" min="0" step="0.1" placeholder="6" data-jc-carpet-size="area">
				</label>
			</div>

			<p class="jc-calc__hint">Показываем только те сочетания материала и типа, которые есть в прайсе.</p>
		</div>
	</div>

	<!-- Допы -->
	<div class="jc-calc__extras">
		<h4 class="jc-calc__section-title">Дополнительно</h4>

		<?php foreach ( $config['extras'] as $extra ) : ?>
			<?php
			$price = ( isset( $extra['price'] ) && null !== $extra['price'] && '' !== $extra['price'] ) ? (int) $extra['price'] : null;
			$control = $extra['control'] ?? 'check';
			?>
			<?php if ( null !== $price && 'check' !== $control ) : ?>
				<div class="jc-calc__row">
					<div class="jc-calc__row-main">
						<span class="jc-calc__row-label"><?php echo esc_html( $extra['label'] ); ?></span>
						<span class="jc-calc__row-price">от <?php echo esc_html( jhb_money( $price ) ); ?> ₽<?php echo 'counter' === $control ? ' / 30 мин' : ' / ед.'; ?></span>
					</div>
					<div class="jc-calc__stepper">
						<button type="button" class="jc-calc__step" data-jc-step-extra="<?php echo esc_attr( $extra['id'] ); ?>" data-jc-dir="-1" aria-label="Уменьшить">−</button>
						<input class="jc-calc__qty" type="number" min="0" step="1" value="0"
							data-jc-extra="<?php echo esc_attr( $extra['id'] ); ?>"
							aria-label="<?php echo esc_attr( $extra['label'] ); ?>">
						<button type="button" class="jc-calc__step" data-jc-step-extra="<?php echo esc_attr( $extra['id'] ); ?>" data-jc-dir="1" aria-label="Увеличить">+</button>
					</div>
				</div>
			<?php else : ?>
				<label class="jc-calc__flag">
					<input type="checkbox" data-jc-flag="<?php echo esc_attr( $extra['id'] ); ?>">
					<span>
						<?php echo esc_html( $extra['label'] ); ?>
						<em>— рассчитывается индивидуально, на сумму не влияет</em>
					</span>
				</label>
			<?php endif; ?>
		<?php endforeach; ?>
	</div>

	<!-- Выезд и скидка -->
	<div class="jc-calc__trip">
		<label class="jc-calc__field">
			<span>Км от МКАД</span>
			<input type="number" min="0" step="1" value="0" data-jc-km>
		</label>
		<p class="jc-calc__hint">
			До <?php echo esc_html( $config['trip']['free_km'] ); ?> км — бесплатно,
			дальше <?php echo esc_html( jhb_money( $config['trip']['per_km'] ) ); ?> ₽/км.
		</p>
		<label class="jc-calc__flag">
			<input type="checkbox" data-jc-discount>
			<span>Постоянный клиент — скидка <?php echo esc_html( $config['discount']['percent'] ); ?>%</span>
		</label>
	</div>

	<!-- Итог -->
	<div class="jc-calc__result">
		<p class="jc-calc__total">
			Предварительно: <strong data-jc-total>0 ₽</strong>
		</p>

		<ul class="jc-calc__lines" data-jc-lines hidden></ul>

		<?php if ( $config['texts']['note'] ) : ?>
			<p class="jc-calc__note"><?php echo nl2br( esc_html( $config['texts']['note'] ) ); ?></p>
		<?php endif; ?>

		<?php if ( $config['texts']['lead_time'] ) : ?>
			<p class="jc-calc__note jc-calc__note--muted"><?php echo esc_html( $config['texts']['lead_time'] ); ?></p>
		<?php endif; ?>

		<button type="button" class="jc-btn jc-btn--primary jc-calc__submit" data-jc-calc-submit>
			Отправить расчёт
		</button>
	</div>

	<input type="hidden" class="jc-calc__hidden" id="jc-calc-total-<?php echo esc_attr( $block_id ); ?>"
		name="jc_calc_total[<?php echo esc_attr( $block_id ); ?>]" value="0" data-jc-total-field>
	<input type="hidden" class="jc-calc__hidden" id="jc-calc-details-<?php echo esc_attr( $block_id ); ?>"
		name="jc_calc_details[<?php echo esc_attr( $block_id ); ?>]" value="" data-jc-details-field>
</div>
