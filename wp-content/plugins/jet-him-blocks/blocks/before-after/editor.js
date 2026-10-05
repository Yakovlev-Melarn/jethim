/**
 * JetHim: редактор блока «до/после».
 */
( function ( wp ) {
	'use strict';

	var el = wp.element.createElement;
	var Fragment = wp.element.Fragment;
	var registerBlockType = wp.blocks.registerBlockType;
	var __ = wp.i18n.__;
	var InspectorControls = wp.blockEditor.InspectorControls;
	var useBlockProps = wp.blockEditor.useBlockProps;
	var MediaUpload = wp.blockEditor.MediaUpload;
	var MediaUploadCheck = wp.blockEditor.MediaUploadCheck;
	var PanelBody = wp.components.PanelBody;
	var TextControl = wp.components.TextControl;
	var RangeControl = wp.components.RangeControl;
	var Button = wp.components.Button;
	var ServerSideRender = wp.serverSideRender.ServerSideRender;

	function ImagePicker( props ) {
		return el( 'div', { className: 'jhb-image' },
			el( 'p', { className: 'jhb-image__label' }, props.label ),
			el( MediaUploadCheck, null,
				el( MediaUpload, {
					onSelect: function ( media ) {
						props.onChange( { id: media.id, url: media.url } );
					},
					allowedTypes: [ 'image' ],
					value: props.id,
					render: function ( obj ) {
						return el( Button, { variant: 'secondary', onClick: obj.open },
							props.id ? __( 'Заменить фото', 'jet-him-blocks' ) : __( 'Выбрать фото', 'jet-him-blocks' )
						);
					}
				} )
			),
			props.url ?
				el( 'img', { className: 'jhb-image__preview', src: props.url, alt: '' } ) :
				el( 'span', { className: 'jhb-image__empty' }, __( 'Фото не выбрано', 'jet-him-blocks' ) ),
			props.url ?
				el( Button, { variant: 'link', isDestructive: true, onClick: function () { props.onChange( { id: 0, url: '' } ); } },
					__( 'Убрать', 'jet-him-blocks' ) ) :
				null
		);
	}

	function BaEdit( props ) {
		var attrs = props.attributes;
		var setAttributes = props.setAttributes;

		var blockProps = useBlockProps( { className: 'jhb-ba-editor' } );

		return el( Fragment, null,
			el( InspectorControls, null,
				el( PanelBody, { title: __( 'Фотографии', 'jet-him-blocks' ), initialOpen: true },
					el( ImagePicker, {
						label: __( 'Фото «до»', 'jet-him-blocks' ),
						id: attrs.beforeId,
						url: attrs.beforeUrl,
						onChange: function ( media ) {
							setAttributes( { beforeId: media.id, beforeUrl: media.url } );
						}
					} ),
					el( ImagePicker, {
						label: __( 'Фото «после»', 'jet-him-blocks' ),
						id: attrs.afterId,
						url: attrs.afterUrl,
						onChange: function ( media ) {
							setAttributes( { afterId: media.id, afterUrl: media.url } );
						}
					} ),
					el( TextControl, {
						label: __( 'Подпись слева', 'jet-him-blocks' ),
						value: attrs.beforeLabel,
						onChange: function ( v ) { setAttributes( { beforeLabel: v } ); }
					} ),
					el( TextControl, {
						label: __( 'Подпись справа', 'jet-him-blocks' ),
						value: attrs.afterLabel,
						onChange: function ( v ) { setAttributes( { afterLabel: v } ); }
					} ),
					el( TextControl, {
						label: __( 'Подпись под слайдером', 'jet-him-blocks' ),
						value: attrs.caption,
						onChange: function ( v ) { setAttributes( { caption: v } ); }
					} ),
					el( RangeControl, {
						label: __( 'Стартовая позиция делителя, %', 'jet-him-blocks' ),
						value: attrs.start,
						min: 0,
						max: 100,
						step: 5,
						onChange: function ( v ) { setAttributes( { start: v } ); }
					} )
				)
			),
			el( 'div', blockProps,
				el( ServerSideRender, {
					block: 'jc/before-after',
					attributes: attrs,
					httpMethod: 'POST'
				} )
			)
		);
	}

	registerBlockType( 'jc/before-after', {
		title: __( 'JetHim: до / после', 'jet-him-blocks' ),
		description: __( 'Слайдер сравнения: мышь, палец, клавиши.', 'jet-him-blocks' ),
		category: 'jet-him',
		icon: 'format-image',
		keywords: [ 'до', 'после', 'слайдер', 'сравнение' ],
		attributes: {
			beforeUrl: { type: 'string', default: '' },
			beforeId: { type: 'number', default: 0 },
			afterUrl: { type: 'string', default: '' },
			afterId: { type: 'number', default: 0 },
			beforeLabel: { type: 'string', default: 'До' },
			afterLabel: { type: 'string', default: 'После' },
			caption: { type: 'string', default: '' },
			start: { type: 'number', default: 50 }
		},
		supports: {
			html: false,
			align: [ 'wide', 'center' ]
		},
		edit: BaEdit,
		save: function () {
			return null;
		}
	} );
} )( window.wp );
