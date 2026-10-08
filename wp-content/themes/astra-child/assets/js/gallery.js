/**
 * Фотогалерея примеров работ (.jc-gallery) и её лайтбокс.
 *
 * Клик по фото — полноэкранный просмотр: зум по клику на картинку,
 * стрелки и свайп между фото, счётчик, подпись, Esc/←/→/+/−/0 с клавиатуры.
 * Файл подключается только на страницах, где в контенте есть .jc-gallery
 * (см. jc_enqueue_gallery в functions.php).
 */
( function () {
	'use strict';

	var box = null;
	var imgEl = null;
	var captionEl = null;
	var counterEl = null;
	var items = [];
	var index = 0;
	var zoomed = false;
	var touchX = null;

	function build() {
		if ( box ) {
			return;
		}
		box = document.createElement( 'div' );
		box.className = 'jc-lightbox';
		box.hidden = true;
		box.innerHTML =
			'<button type="button" class="jc-lightbox__btn jc-lightbox__close" aria-label="Закрыть">&times;</button>' +
			'<button type="button" class="jc-lightbox__btn jc-lightbox__prev" aria-label="Предыдущее фото">&#8249;</button>' +
			'<img class="jc-lightbox__img" alt="">' +
			'<button type="button" class="jc-lightbox__btn jc-lightbox__next" aria-label="Следующее фото">&#8250;</button>' +
			'<div class="jc-lightbox__meta">' +
			'<span class="jc-lightbox__caption"></span>' +
			'<span class="jc-lightbox__counter"></span>' +
			'</div>' +
			'<span class="jc-lightbox__hint">Клик по фото — увеличить · Esc — закрыть</span>';
		document.body.appendChild( box );

		imgEl = box.querySelector( '.jc-lightbox__img' );
		captionEl = box.querySelector( '.jc-lightbox__caption' );
		counterEl = box.querySelector( '.jc-lightbox__counter' );

		box.querySelector( '.jc-lightbox__close' ).addEventListener( 'click', close );
		box.querySelector( '.jc-lightbox__prev' ).addEventListener( 'click', function ( e ) {
			e.stopPropagation();
			step( -1 );
		} );
		box.querySelector( '.jc-lightbox__next' ).addEventListener( 'click', function ( e ) {
			e.stopPropagation();
			step( 1 );
		} );
		box.addEventListener( 'click', function ( e ) {
			if ( e.target === box ) {
				close();
			}
		} );
		imgEl.addEventListener( 'click', function ( e ) {
			e.stopPropagation();
			setZoom( ! zoomed );
		} );
		box.addEventListener( 'touchstart', function ( e ) {
			touchX = e.changedTouches[ 0 ].clientX;
		}, { passive: true } );
		box.addEventListener( 'touchend', function ( e ) {
			if ( touchX === null ) {
				return;
			}
			var delta = e.changedTouches[ 0 ].clientX - touchX;
			touchX = null;
			if ( Math.abs( delta ) > 50 ) {
				setZoom( false );
				step( delta < 0 ? 1 : -1 );
			}
		}, { passive: true } );
	}

	function setZoom( on ) {
		zoomed = !! on;
		imgEl.classList.toggle( 'is-zoomed', zoomed );
	}

	function show( i ) {
		if ( ! items.length ) {
			return;
		}
		index = ( i + items.length ) % items.length;
		var link = items[ index ];
		var thumb = link.querySelector( 'img' );

		setZoom( false );
		imgEl.src = link.getAttribute( 'href' );
		imgEl.alt = thumb ? thumb.alt || '' : '';
		captionEl.textContent = imgEl.alt;
		counterEl.textContent = ( index + 1 ) + ' / ' + items.length;

		// подгружаем соседние фото, чтобы листалось без задержки
		[ 1, -1 ].forEach( function ( offset ) {
			var next = items[ ( index + offset + items.length ) % items.length ];
			if ( next ) {
				var pre = new Image();
				pre.src = next.getAttribute( 'href' );
			}
		} );
	}

	function step( delta ) {
		show( index + delta );
	}

	function open( i ) {
		build();
		items = Array.prototype.slice.call(
			document.querySelectorAll( '.jc-gallery a[href]' )
		);
		if ( ! items.length ) {
			return;
		}
		box.hidden = false;
		document.body.style.overflow = 'hidden';
		show( i );
	}

	function close() {
		if ( ! box ) {
			return;
		}
		box.hidden = true;
		setZoom( false );
		document.body.style.overflow = '';
	}

	function onKey( e ) {
		if ( ! box || box.hidden ) {
			return;
		}
		if ( e.key === 'Escape' ) {
			close();
		} else if ( e.key === 'ArrowRight' ) {
			step( 1 );
		} else if ( e.key === 'ArrowLeft' ) {
			step( -1 );
		} else if ( e.key === '+' || e.key === '=' ) {
			setZoom( true );
		} else if ( e.key === '-' ) {
			setZoom( false );
		} else if ( e.key === '0' ) {
			setZoom( false );
		}
	}

	function init() {
		var gallery = document.querySelector( '.jc-gallery' );
		if ( ! gallery ) {
			return;
		}
		var links = Array.prototype.slice.call( gallery.querySelectorAll( 'a[href]' ) );
		links.forEach( function ( link, i ) {
			link.addEventListener( 'click', function ( e ) {
				e.preventDefault();
				open( i );
			} );
		} );
		document.addEventListener( 'keydown', onKey );
	}

	if ( document.readyState === 'loading' ) {
		document.addEventListener( 'DOMContentLoaded', init );
	} else {
		init();
	}
} )();
