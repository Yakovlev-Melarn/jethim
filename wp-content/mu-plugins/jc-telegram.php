<?php
/**
 * Plugin Name: JetHim — уведомления в Telegram
 * Description: Отправка заявок из Fluent Forms в Telegram: текст + фото (из файла в formData), в несколько чатов. Токен и chat_id хранятся в опциях БД (jc_tg_token / jc_tg_chat_ids).
 *
 * Этап 5 → демо (2026-10): поддержка НЕСКОЛЬКИХ получателей и вложений.
 * Настройка:
 *   wp option update jc_tg_token <токен>            — уже сделано e5_setup.php
 *   боту /start → chat_id подхватится сам
 *   wp jc-tg discover --save                        — сохранить все chat_id в список
 *
 * Несколько получателей: каждый, кто нажал /start боту, попадает в список
 * после `wp jc-tg discover --save`. Заявки уходят ВСЕМ из option jc_tg_chat_ids.
 *
 * CLI:
 *   wp jc-tg status           — токен и список chat_id
 *   wp jc-tg discover [--save]— все chat_id из getUpdates; --save — слить в список
 *   wp jc-tg send "Текст" [--file=<путь|URL>] — тест (файлом можно несколько раз)
 *
 * Сеть: с локальной машины api.telegram.org недоступен — проверяем на VDS
 * через деплой: .scripts/deploy_demo.sh, затем wp jc-tg send на сервере.
 */

defined( 'ABSPATH' ) || exit;

/**
 * Токен бота (опция, не в git).
 */
function jc_tg_token() {
	return (string) get_option( 'jc_tg_token', '' );
}

/**
 * Список chat_id (опция-массив; legacy jc_tg_chat_id подмешиваем).
 *
 * @return string[]
 */
function jc_tg_chat_ids() {
	$ids = get_option( 'jc_tg_chat_ids', array() );
	if ( ! is_array( $ids ) ) {
		$ids = '' !== (string) $ids ? array( (string) $ids ) : array();
	}

	$legacy = (string) get_option( 'jc_tg_chat_id', '' );
	if ( $legacy && ! in_array( $legacy, $ids, true ) ) {
		$ids[] = $legacy;
	}

	$ids = array_filter( array_map( 'strval', $ids ) );

	return array_values( array_unique( $ids ) );
}

/**
 * Последние апдейты бота (для поиска chat_id).
 */
function jc_tg_get_updates() {
	$token = jc_tg_token();
	if ( ! $token ) {
		return new WP_Error( 'jc_tg_no_token', 'Не задан токен (опция jc_tg_token)' );
	}

	$res = wp_remote_get(
		'https://api.telegram.org/bot' . $token . '/getUpdates?timeout=0',
		array( 'timeout' => 15 )
	);

	if ( is_wp_error( $res ) ) {
		return $res;
	}

	$body = json_decode( wp_remote_retrieve_body( $res ), true );

	return is_array( $body ) ? $body : new WP_Error( 'jc_tg_bad_response', 'Пустой ответ Telegram' );
}

/**
 * Все уникальные chat_id из getUpdates (кто писал боту, обычно /start).
 *
 * @return string[]
 */
function jc_tg_discover_chat_ids() {
	$data = jc_tg_get_updates();
	if ( is_wp_error( $data ) || empty( $data['ok'] ) || empty( $data['result'] ) ) {
		return array();
	}

	$ids = array();
	foreach ( $data['result'] as $update ) {
		foreach ( array( 'message', 'edited_message', 'my_chat_member', 'channel_post' ) as $type ) {
			if ( ! empty( $update[ $type ]['chat']['id'] ) ) {
				$ids[] = (string) $update[ $type ]['chat']['id'];
			}
		}
	}

	return array_values( array_unique( $ids ) );
}

/**
 * Слить chat_id в опцию (не затирая существующих).
 *
 * @param string[] $ids Новые chat_id.
 * @return string[] Итоговый список.
 */
function jc_tg_save_chat_ids( array $ids ) {
	$all       = array_values( array_unique( array_merge( jc_tg_chat_ids(), $ids ) ) );
	$all       = array_filter( array_map( 'strval', $all ) );
	$all       = array_values( array_unique( $all ) );
	update_option( 'jc_tg_chat_ids', $all );

	return $all;
}

/**
 * POST в Bot API (form-urlencoded).
 *
 * @param string $method Метод, напр. sendMessage.
 * @param array  $body   Поля.
 * @return array|WP_Error Декодированный ответ.
 */
function jc_tg_api( $method, array $body ) {
	$token = jc_tg_token();
	if ( ! $token ) {
		return new WP_Error( 'jc_tg_no_token', 'Не задан токен (опция jc_tg_token)' );
	}

	$res = wp_remote_post(
		'https://api.telegram.org/bot' . $token . '/' . $method,
		array(
			'timeout' => 20,
			'body'    => $body,
		)
	);

	if ( is_wp_error( $res ) ) {
		return $res;
	}

	$decoded = json_decode( wp_remote_retrieve_body( $res ), true );
	if ( ! is_array( $decoded ) ) {
		return new WP_Error( 'jc_tg_bad_response', 'Пустой ответ Telegram (' . $method . ')' );
	}
	if ( empty( $decoded['ok'] ) ) {
		return new WP_Error( 'jc_tg_api_failed', 'Telegram (' . $method . '): ' . wp_json_encode( $decoded ) );
	}

	return $decoded;
}

/**
 * POST multipart в Bot API (загрузка файла) — WP HTTP API не умеет
 * multipart, собираем тело вручную.
 *
 * @param string $method Метод, напр. sendPhoto.
 * @param array  $fields Обычные поля (chat_id, caption…).
 * @param array  $files  Файлы: имя_поля => array( 'filename', 'type', 'content' ).
 * @return array|WP_Error Декодированный ответ.
 */
function jc_tg_api_upload( $method, array $fields, array $files ) {
	$token = jc_tg_token();
	if ( ! $token ) {
		return new WP_Error( 'jc_tg_no_token', 'Не задан токен (опция jc_tg_token)' );
	}

	$boundary = '----jctg' . md5( (string) microtime( true ) . wp_rand() );
	$body     = '';

	foreach ( $fields as $name => $value ) {
		$body .= '--' . $boundary . "\r\n"
			. 'Content-Disposition: form-data; name="' . $name . '"' . "\r\n\r\n"
			. (string) $value . "\r\n";
	}

	foreach ( $files as $field => $file ) {
		$body .= '--' . $boundary . "\r\n"
			. 'Content-Disposition: form-data; name="' . $field . '"; filename="' . $file['filename'] . '"' . "\r\n"
			. 'Content-Type: ' . $file['type'] . "\r\n\r\n"
			. $file['content'] . "\r\n";
	}

	$body .= '--' . $boundary . '--' . "\r\n";

	$res = wp_remote_post(
		'https://api.telegram.org/bot' . $token . '/' . $method,
		array(
			'timeout' => 60,
			'headers' => array( 'Content-Type' => 'multipart/form-data; boundary=' . $boundary ),
			'body'    => $body,
		)
	);

	if ( is_wp_error( $res ) ) {
		return $res;
	}

	$decoded = json_decode( wp_remote_retrieve_body( $res ), true );
	if ( ! is_array( $decoded ) ) {
		return new WP_Error( 'jc_tg_bad_response', 'Пустой ответ Telegram (' . $method . ')' );
	}
	if ( empty( $decoded['ok'] ) ) {
		return new WP_Error( 'jc_tg_api_failed', 'Telegram (' . $method . '): ' . wp_json_encode( $decoded ) );
	}

	return $decoded;
}

/**
 * Достать байты картинки: свои файлы — с диска, внешние — по URL.
 *
 * @param string $src Путь (URL сайта) или абсолютный URL.
 * @return array|WP_Error array( 'filename', 'type', 'content' ).
 */
function jc_tg_fetch_file( $src ) {
	$src = trim( (string) $src );
	if ( '' === $src ) {
		return new WP_Error( 'jc_tg_no_file', 'Пустая ссылка на файл' );
	}

	$filename = basename( wp_parse_url( $src, PHP_URL_PATH ) ?: $src );
	$is_image = (bool) preg_match( '/\.(jpe?g|png|webp)$/i', $filename );

	// Свой файл → читаем с диска (URL сайта = путь в ABSPATH).
	if ( 0 === strpos( $src, home_url( '/' ) ) ) {
		$path = str_replace( home_url( '/' ), ABSPATH, strtok( $src, '?' ) );
		$path = rawurldecode( $path );
		if ( $path && file_exists( $path ) && is_readable( $path ) && filesize( $path ) <= 10 * 1024 * 1024 ) {
			$contents = file_get_contents( $path );
			if ( false !== $contents ) {
				return array(
					'filename' => $filename,
					'type'     => $is_image ? ( 'png' === strtolower( pathinfo( $filename, PATHINFO_EXTENSION ) ) ? 'image/png' : ( 'webp' === strtolower( pathinfo( $filename, PATHINFO_EXTENSION ) ) ? 'image/webp' : 'image/jpeg' ) ) : 'application/octet-stream',
					'content'  => $contents,
				);
			}
		}
	}

	// Внешний URL (или файл не найден) → скачиваем.
	if ( preg_match( '#^https?://#i', $src ) ) {
		$res = wp_remote_get( $src, array( 'timeout' => 30, 'redirection' => 5 ) );
		if ( ! is_wp_error( $res ) && 200 === (int) wp_remote_retrieve_response_code( $res ) ) {
			$contents = wp_remote_retrieve_body( $res );
			if ( '' !== $contents && strlen( $contents ) <= 10 * 1024 * 1024 ) {
				$type = wp_remote_retrieve_header( $res, 'content-type' );
				$type = is_array( $type ) ? reset( $type ) : (string) $type;
				$type = strtok( $type, ';' );
				if ( ! $type ) {
					$type = $is_image ? 'image/jpeg' : 'application/octet-stream';
				}

				return array(
					'filename' => $filename,
					'type'     => $type,
					'content'  => $contents,
				);
			}
		}
	}

	return new WP_Error( 'jc_tg_fetch_failed', 'Не удалось скачать файл: ' . $src );
}

/**
 * Отправить файл в чат (картинка → sendPhoto, иное → sendDocument).
 *
 * @param string $chat_id Чат.
 * @param string $src     Путь/URL.
 * @param string $caption Подпись.
 * @return true|WP_Error
 */
function jc_tg_send_file( $chat_id, $src, $caption = '' ) {
	$file = jc_tg_fetch_file( $src );
	if ( is_wp_error( $file ) ) {
		// Файл не скачался — Telegram попробует сам по URL.
		$is_image = (bool) preg_match( '/\.(jpe?g|png|webp)(\?|$)/i', $src );
		$method   = $is_image ? 'sendPhoto' : 'sendDocument';
		$field    = $is_image ? 'photo' : 'document';
		$result   = jc_tg_api(
			$method,
			array(
				'chat_id' => $chat_id,
				$field    => $src,
				'caption' => $caption,
			)
		);

		return is_wp_error( $result ) ? $result : true;
	}

	$is_image = 0 === strpos( $file['type'], 'image/' );
	$method   = $is_image ? 'sendPhoto' : 'sendDocument';
	$field    = $is_image ? 'photo' : 'document';

	$result = jc_tg_api_upload(
		$method,
		array(
			'chat_id' => $chat_id,
			'caption' => $caption,
		),
		array( $field => $file )
	);

	return is_wp_error( $result ) ? $result : true;
}

/**
 * Отправить текст (+ файлы) всем получателям.
 *
 * @param string   $text  Текст (HTML).
 * @param string[] $files Ссылки/пути на вложения.
 * @return true|WP_Error
 */
function jc_tg_send( $text, array $files = array() ) {
	if ( ! jc_tg_token() ) {
		return new WP_Error( 'jc_tg_no_token', 'Не задан токен (опция jc_tg_token)' );
	}

	$ids = jc_tg_chat_ids();
	if ( ! $ids ) {
		// Автопоиск: кто нажал /start.
		$found = jc_tg_discover_chat_ids();
		if ( $found ) {
			$ids = jc_tg_save_chat_ids( $found );
		}
	}

	if ( ! $ids ) {
		return new WP_Error( 'jc_tg_no_chat', 'Нет chat_id — отправьте /start боту и выполните: wp jc-tg discover --save' );
	}

	$first_error = null;

	foreach ( $ids as $chat_id ) {
		$result = jc_tg_api(
			'sendMessage',
			array(
				'chat_id'                  => $chat_id,
				'text'                     => $text,
				'parse_mode'               => 'HTML',
				'disable_web_page_preview' => true,
			)
		);

		if ( is_wp_error( $result ) && ! $first_error ) {
			$first_error = $result;
			continue;
		}

		foreach ( $files as $src ) {
			$file_result = jc_tg_send_file( $chat_id, (string) $src );
			if ( is_wp_error( $file_result ) && ! $first_error ) {
				$first_error = $file_result;
			}
		}
	}

	return $first_error ? $first_error : true;
}

/**
 * Картинки из значений формы (photo_url и любые URL на изображения).
 *
 * @param array $formData Данные сабмита.
 * @return string[] Уникальные URL (не больше 4).
 */
function jc_tg_extract_images( array $formData ) {
	$images = array();

	$walk = function ( $value ) use ( &$walk, &$images ) {
		if ( is_array( $value ) ) {
			foreach ( $value as $item ) {
				$walk( $item );
			}
			return;
		}
		if ( ! is_string( $value ) ) {
			return;
		}
		foreach ( preg_split( '/[\s,]+/', $value ) ?: array() as $token ) {
			$token = trim( $token, " \t\n\r\0\x0B\"" );
			if ( ! preg_match( '#^https?://\S+\.(jpe?g|png|webp)(\?\S*)?$#i', $token ) ) {
				continue;
			}
			if ( ! in_array( $token, $images, true ) ) {
				$images[] = $token;
			}
		}
	};

	foreach ( $formData as $value ) {
		$walk( $value );
		if ( count( $images ) >= 4 ) {
			break;
		}
	}

	return array_slice( $images, 0, 4 );
}

/* --------------------------------------------------------------------
 * Отправка после каждой записи заявки
 * ------------------------------------------------------------------ */
add_action(
	'fluentform/submission_inserted',
	function ( $insert_id, $formData, $form ) {
		$labels = jc_tg_field_labels( $form );
		if ( ! $labels ) {
			return;
		}

		$lines   = array();
		$lines[] = "\xF0\x9F\x93\x8B <b>Новая заявка — " . esc_html( $form->title ) . '</b>';

		foreach ( $labels as $name => $label ) {
			if ( ! isset( $formData[ $name ] ) ) {
				continue;
			}
			$value = jc_tg_value_string( $formData[ $name ] );
			if ( '' === $value ) {
				continue;
			}
			$lines[] = '<b>' . esc_html( $label ) . ':</b> ' . esc_html( $value );
		}

		$lines[] = '<b>Страница:</b> ' . esc_url( wp_get_referer() ? wp_get_referer() : home_url( '/' ) );
		$lines[] = '<b>Заявка №</b>' . (int) $insert_id;
		$lines[] = '<i>' . current_time( 'd.m.Y H:i' ) . '</i>';

		$images = jc_tg_extract_images( is_array( $formData ) ? $formData : array() );

		$result = jc_tg_send( implode( "\n", $lines ), $images );
		if ( is_wp_error( $result ) ) {
			// Не ломаем отправку формы — просто пишем в лог.
			error_log( '[jc-telegram] ' . $result->get_error_message() );
		}
	},
	10,
	3
);

/**
 * Карта «имя поля → подпись» из form_fields формы.
 */
function jc_tg_field_labels( $form ) {
	$labels  = array();
	$decoded = json_decode( (string) $form->form_fields, true );
	if ( empty( $decoded['fields'] ) || ! is_array( $decoded['fields'] ) ) {
		return $labels;
	}

	$walk = function ( $fields ) use ( &$walk, &$labels ) {
		foreach ( $fields as $field ) {
			if ( empty( $field['element'] ) ) {
				continue;
			}
			$name = isset( $field['attributes']['name'] ) ? $field['attributes']['name'] : '';
			if ( $name && isset( $field['settings']['label'] ) && '' !== $field['settings']['label'] ) {
				$labels[ $name ] = $field['settings']['label'];
			}
			if ( ! empty( $field['fields'] ) && is_array( $field['fields'] ) ) {
				$walk( $field['fields'] );
			}
			if ( ! empty( $field['columns'] ) && is_array( $field['columns'] ) ) {
				foreach ( $field['columns'] as $column ) {
					if ( ! empty( $column['fields'] ) ) {
						$walk( $column['fields'] );
					}
				}
			}
		}
	};

	$walk( $decoded['fields'] );

	return $labels;
}

/**
 * Значение поля → строка для сообщения.
 */
function jc_tg_value_string( $value ) {
	if ( is_array( $value ) ) {
		$value = implode( ', ', array_filter( array_map( 'strval', $value ) ) );
	}
	if ( is_bool( $value ) ) {
		return $value ? 'да' : 'нет';
	}

	return trim( (string) $value );
}

/* --------------------------------------------------------------------
 * CLI: wp jc-tg status|discover|send
 * ------------------------------------------------------------------ */
if ( defined( 'WP_CLI' ) && WP_CLI ) {
	class Jc_Tg_Command {

		/**
		 * Статус настроек.
		 */
		public function status() {
			WP_CLI::log( 'token: ' . ( jc_tg_token() ? 'задан (' . substr( jc_tg_token(), 0, 10 ) . '…)' : 'НЕ ЗАДАН' ) );
			$ids = jc_tg_chat_ids();
			if ( $ids ) {
				WP_CLI::log( 'chat_ids (' . count( $ids ) . '): ' . implode( ', ', $ids ) );
			} else {
				WP_CLI::log( 'chat_ids: пусто (нужен /start боту → wp jc-tg discover --save)' );
			}
		}

		/**
		 * Поиск chat_id в getUpdates.
		 *
		 * ## OPTIONS
		 * [--save]
		 * : Слить найденные chat_id в опцию jc_tg_chat_ids (существующие не затираются).
		 */
		public function discover( $args = array(), $assoc_args = array() ) {
			$ids = jc_tg_discover_chat_ids();
			if ( ! $ids ) {
				WP_CLI::error( 'chat_id не найдены — отправьте боту /start и повторите' );
			}
			WP_CLI::log( 'найдены chat_id: ' . implode( ', ', $ids ) );
			if ( ! empty( $assoc_args['save'] ) ) {
				$all = jc_tg_save_chat_ids( $ids );
				WP_CLI::success( 'Сохранено, всего получателей: ' . count( $all ) . ' (' . implode( ', ', $all ) . ')' );
			}
		}

		/**
		 * Добавить chat_id вручную.
		 *
		 * ## OPTIONS
		 * <chat_id>
		 * : ID чата.
		 */
		public function add( $args ) {
			$all = jc_tg_save_chat_ids( array( (string) $args[0] ) );
			WP_CLI::success( 'Получателей: ' . count( $all ) . ' (' . implode( ', ', $all ) . ')' );
		}

		/**
		 * Тестовое сообщение (всем получателям).
		 *
		 * ## OPTIONS
		 * <text>
		 * : Текст сообщения.
		 *
		 * ## OPTIONS
		 * [--file=<src>]
		 * : Путь или URL вложения (можно повторять).
		 */
		public function send( $args, $assoc_args = array() ) {
			$files = array();
			if ( ! empty( $assoc_args['file'] ) ) {
				$files = (array) $assoc_args['file'];
			}

			$result = jc_tg_send( (string) $args[0], $files );
			if ( is_wp_error( $result ) ) {
				WP_CLI::error( $result->get_error_message() );
			}
			WP_CLI::success( 'Отправлено получателям: ' . count( jc_tg_chat_ids() ) );
		}
	}

	WP_CLI::add_command( 'jc-tg', 'Jc_Tg_Command' );
}
