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
} )();
