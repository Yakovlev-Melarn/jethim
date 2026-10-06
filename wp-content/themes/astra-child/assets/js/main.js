/**
 * JetHim — скрипты дочерней темы.
 *
 * Этап 3: сюда пойдут логика калькулятора (jc/calc) и слайдера до/после
 * (jc/before-after). Блоки подключают собственные файлы, этот — только
 * общее: поведение шапки, подвала, мелкие UI-фиксы.
 */
( function () {
	'use strict';

	// Общие точки расширения для будущих блоков.
	window.JC = window.JC || {
		contacts: window.JC && window.JC.contacts ? window.JC.contacts : {},
		hooks: {}
	};

	/**
	 * Зарегистрировать обработчик события блока.
	 *
	 * @param {string}   name Имя события.
	 * @param {Function} fn   Обработчик.
	 */
	window.JC.on = function ( name, fn ) {
		( this.hooks[ name ] = this.hooks[ name ] || [] ).push( fn );
	};

	/**
	 * Отправить событие блока.
	 *
	 * @param {string} name Имя события.
	 * @param {*}      data Данные.
	 */
	window.JC.emit = function ( name, data ) {
		( this.hooks[ name ] || [] ).forEach( function ( fn ) {
			fn( data );
		} );
	};

	/* ------------------------------------------------------------------
	   Тема: по умолчанию светлая, тёмная — по кнопке в верхней панели.
	   Атрибут data-theme уже стоит (скрипт в <head>), здесь — только
	   переключение и сохранение выбора.
	------------------------------------------------------------------ */
	var THEME_KEY = 'jc-theme';

	function jcGetTheme() {
		var saved = null;
		try { saved = window.localStorage.getItem( THEME_KEY ); } catch ( e ) {}
		return saved === 'dark' ? 'dark' : 'light';
	}

	function jcSetTheme( theme ) {
		document.documentElement.setAttribute( 'data-theme', theme );
		try { window.localStorage.setItem( THEME_KEY, theme ); } catch ( e ) {}
		window.JC.emit( 'theme:change', theme );
	}

	var themeToggle = document.querySelector( '[data-jc-theme-toggle]' );
	if ( themeToggle ) {
		themeToggle.addEventListener( 'click', function () {
			jcSetTheme( jcGetTheme() === 'dark' ? 'light' : 'dark' );
		} );
	}

	/* ------------------------------------------------------------------
	   Этап 5: калькулятор → скрытые поля формы Fluent Forms.
	   Блок calc рисует свои поля jc_calc_total[<id>], а форма заявки
	   несёт скрытые jc_calc_total / jc_calc_details — копируем значение,
	   чтобы в заявке уехала сумма и разбивка.
	------------------------------------------------------------------ */
	function jcSyncCalcToForm() {
		var calc = document.querySelector( '[data-jc-calc]' );
		if ( ! calc ) {
			return;
		}
		var totalField = calc.querySelector( '[data-jc-total-field]' );
		var detailsField = calc.querySelector( '[data-jc-details-field]' );
		if ( ! totalField ) {
			return;
		}
		var total = totalField.value;
		var details = detailsField ? detailsField.value : '';

		Array.prototype.forEach.call(
			document.querySelectorAll( 'form [name="jc_calc_total"]' ),
			function ( el ) { el.value = total; }
		);
		Array.prototype.forEach.call(
			document.querySelectorAll( 'form [name="jc_calc_details"]' ),
			function ( el ) { el.value = details; }
		);
	}

	[ 'input', 'change', 'click' ].forEach( function ( type ) {
		document.addEventListener( type, function ( e ) {
			var t = e.target;
			if ( t && t.closest && t.closest( '[data-jc-calc]' ) ) {
				jcSyncCalcToForm();
			}
		}, true );
	} );

	if ( window.JC && window.JC.on ) {
		window.JC.on( 'calc:submit', jcSyncCalcToForm );
	}
	jcSyncCalcToForm();

	/* Имя услуги в скрытое поле формы (страница услуги). */
	var serviceField = document.querySelector( 'form [name="service"]' );
	if ( serviceField && ! serviceField.value ) {
		var h1 = document.querySelector( 'h1' );
		if ( h1 ) {
			serviceField.value = h1.textContent.replace( /\s+/g, ' ' ).trim();
		}
	}
} )();
