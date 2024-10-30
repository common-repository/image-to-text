<?php
/**
 * Image to Text
 *
 * @package    ImageToText
 * @subpackage ImageToText
/*
	Copyright (c) 2018- Katsushi Kawamori (email : dodesyoswift312@gmail.com)
	This program is free software; you can redistribute it and/or modify
	it under the terms of the GNU General Public License as published by
	the Free Software Foundation; version 2 of the License.

	This program is distributed in the hope that it will be useful,
	but WITHOUT ANY WARRANTY; without even the implied warranty of
	MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
	GNU General Public License for more details.

	You should have received a copy of the GNU General Public License
	along with this program; if not, write to the Free Software
	Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
 */

$imagetotext = new ImageToText();

/** ==================================================
 * Main Functions
 */
class ImageToText {

	/** ==================================================
	 * Path
	 *
	 * @var $tmp_dir  tmp_dir.
	 */
	private $tmp_dir;

	/** ==================================================
	 * Path
	 *
	 * @var $font_dir  font_dir.
	 */
	private $font_dir;

	/** ==================================================
	 * Path
	 *
	 * @var $tmp_url  tmp_url.
	 */
	private $tmp_url;

	/** ==================================================
	 * Construct
	 *
	 * @since 1.00
	 */
	public function __construct() {

		$wp_uploads = wp_upload_dir();
		$relation_path_true = strpos( $wp_uploads['baseurl'], '../' );
		if ( $relation_path_true > 0 ) {
			$basepath = substr( $wp_uploads['baseurl'], 0, $relation_path_true );
			$upload_url = $this->realurl( $basepath, $relationalpath );
			$upload_dir = wp_normalize_path( realpath( $wp_uploads['basedir'] ) );
		} else {
			$upload_url = $wp_uploads['baseurl'];
			$upload_dir = wp_normalize_path( $wp_uploads['basedir'] );
		}
		if ( is_ssl() ) {
			$upload_url = str_replace( 'http:', 'https:', $upload_url );
		}
		$upload_dir = untrailingslashit( $upload_dir );
		$upload_url = untrailingslashit( $upload_url );

		$this->tmp_dir = $upload_dir . '/image-to-text';
		$this->font_dir = $upload_dir . '/image-to-text/font';
		$this->tmp_url = $upload_url . '/image-to-text';

		/* Make tmp dir */
		if ( ! is_dir( $this->tmp_dir ) ) {
			wp_mkdir_p( $this->tmp_dir );
		}
		/* Make font dir */
		if ( ! is_dir( $this->font_dir ) ) {
			wp_mkdir_p( $this->font_dir );
		}
		/* Copy default font */
		$fontfiles = scandir( plugin_dir_path( __DIR__ ) . 'font' );
		$fontfiles = array_diff( $fontfiles, array( '.', '..' ) );
		$fontfiles = array_values( $fontfiles );
		foreach ( $fontfiles as $fontfile ) {
			if ( ! is_file( $this->font_dir . '/' . $fontfile ) ) {
				copy( plugin_dir_path( __DIR__ ) . 'font/' . $fontfile, $this->font_dir . '/' . $fontfile );
			}
		}

		add_action( 'init', array( $this, 'imagetotext_block_init' ) );
		add_shortcode( 'imagetotext', array( $this, 'imagetotext_func' ) );
	}

	/** ==================================================
	 * Attribute block
	 *
	 * @since 2.00
	 */
	public function imagetotext_block_init() {

		if ( ! get_option( 'image_to_text' ) ) {
			$image_to_text_tbl = array(
				'font_size' => 12,
				'back_color' => '#ffffff',
				'font_color' => '#000000',
				'font_file' => 'NotoSansJP-Regular.otf',
				'vt_mg' => 1.35,
				'alt_text' => false,
			);
			update_option( 'image_to_text', $image_to_text_tbl );
		}
		$image_to_text_settings = get_option( 'image_to_text' );

		register_block_type(
			plugin_dir_path( __DIR__ ) . 'block/build',
			array(
				'render_callback' => array( $this, 'imagetotext_callback' ),
				'title' => _x( 'Convert text to image', 'block title', 'image-to-text' ),
				'description' => _x( 'Convert text to image.', 'block description', 'image-to-text' ),
				'keywords' => array(
					_x( 'image', 'block keyword', 'image-to-text' ),
					_x( 'text', 'block keyword', 'image-to-text' ),
				),
				'attributes'      => array(
					'font_size' => array(
						'type'      => 'number',
						'default'   => $image_to_text_settings['font_size'],
					),
					'back_color' => array(
						'type'      => 'string',
						'default'   => $image_to_text_settings['back_color'],
					),
					'font_color' => array(
						'type'      => 'string',
						'default'   => $image_to_text_settings['font_color'],
					),
					'font_file'  => array(
						'type'      => 'string',
						'default'   => $image_to_text_settings['font_file'],
					),
					'vt_mg' => array(
						'type'    => 'number',
						'default' => floatval( $image_to_text_settings['vt_mg'] ),
					),
					'alt' => array(
						'type'    => 'boolean',
						'default' => $image_to_text_settings['alt_text'],
					),
					'alt_text' => array(
						'type'    => 'string',
						'default' => null,
					),
					'text'  => array(
						'type'      => 'string',
						'default'   => null,
					),
				),
			)
		);

		$script_handle = generate_block_asset_handle( 'image-to-text/imagetotext-block', 'editorScript' );
		wp_set_script_translations( $script_handle, 'image-to-text' );

		$fontfiles = scandir( $this->font_dir );
		$fontfiles = array_diff( $fontfiles, array( '.', '..' ) );
		$fontfiles = array_values( $fontfiles );
		wp_localize_script(
			$script_handle,
			'imagetotext_file',
			$fontfiles
		);
	}

	/** ==================================================
	 * Server side render
	 *
	 * @param array  $atts  atts.
	 * @param string $content  content.
	 * @return string $content
	 * @since 2.00
	 */
	public function imagetotext_callback( $atts, $content ) {

		if ( empty( $atts['text'] ) ) {
			$content .= '<div style="text-align: center;">';
			$content .= '<div><strong><span class="dashicons dashicons-admin-appearance" style="position: relative; top: 5px;"></span>Image to Text</strong></div>';
			$content .= esc_html__( 'Please input text.', 'image-to-text' );
			$content .= '</div>';
		} else {
			$font_file = $this->font_dir . '/' . $atts['font_file'];
			$vt_mg = floatval( $atts['vt_mg'] );
			$alt_text = null;
			if ( $atts['alt'] ) {
				if ( $atts['alt_text'] ) {
					$alt_text = $atts['alt_text'];
				} else {
					$alt_text = $atts['text'];
				}
			}

			$content = $this->image_to_text( $atts['font_size'], $atts['back_color'], $atts['font_color'], $font_file, $vt_mg, $alt_text, $atts['text'] );

		}

		return $content;
	}

	/** ==================================================
	 * short code
	 *
	 * @param array  $atts  atts.
	 * @param string $content  content.
	 * @return string $content
	 * @since 1.00
	 */
	public function imagetotext_func( $atts, $content = null ) {

		$content = sanitize_text_field( $content );

		$a = shortcode_atts(
			array(
				'font_size' => '',
				'back_color' => '',
				'font_color' => '',
				'alt' => '',
			),
			$atts
		);

		$image_to_text_settings = get_option( 'image_to_text' );

		$alt_text = null;
		if ( empty( $a['font_size'] ) ) {
			$a['font_size'] = $image_to_text_settings['font_size'];
		}
		if ( empty( $a['back_color'] ) ) {
			$a['back_color'] = $image_to_text_settings['back_color'];
		}
		if ( empty( $a['font_color'] ) ) {
			$a['font_color'] = $image_to_text_settings['font_color'];
		}
		if ( empty( $a['alt'] ) ) {
			if ( $image_to_text_settings['alt_text'] ) {
				$alt_text = $content;
			}
		} elseif ( 'on' === $a['alt'] ) {
				$alt_text = $content;
		} else {
			$alt_text = $a['alt'];
		}

		$font_file = $this->font_dir . '/' . $image_to_text_settings['font_file'];
		$vt_mg = floatval( $image_to_text_settings['vt_mg'] );

		$content = $this->image_to_text( $a['font_size'], $a['back_color'], $a['font_color'], $font_file, $vt_mg, $alt_text, $content );

		return do_shortcode( $content );
	}

	/** ==================================================
	 * Main
	 * Special Thanks   http://syake-labo.com/blog/2011/10/php-imagefttext/
	 *
	 * @param int    $font_size  font_size.
	 * @param string $hex_back_color  hex_back_color.
	 * @param string $hex_font_color  hex_font_color.
	 * @param string $font_file  font_file.
	 * @param float  $vt_mg  vt_mg.
	 * @param string $alt_text  alt_text.
	 * @param string $content  content.
	 * @return string $content
	 * @since 1.00
	 */
	private function image_to_text( $font_size, $hex_back_color, $hex_font_color, $font_file, $vt_mg, $alt_text, $content ) {

		$tx = 2;
		$ty = $font_size * $vt_mg;

		$bbox = imagettfbbox( $font_size, 0, $font_file, $content );
		$x0 = $bbox[6];
		$y0 = $bbox[7];
		$x1 = $bbox[2];
		$y1 = $bbox[3];
		$width = $x1 - $x0 + $font_size * 0.35 + 4;
		$height = $font_size * 1.5 + 2;
		$im = imagecreatetruecolor( $width, $height );

		list($red, $green, $blue) = $this->hex_to_rgb( $hex_back_color );
		$back_color = imagecolorallocate( $im, $red, $green, $blue );
		list($red, $green, $blue) = $this->hex_to_rgb( $hex_font_color );
		$font_color = imagecolorallocate( $im, $red, $green, $blue );

		imagefilledrectangle( $im, 0, 0, $width, $height, $back_color );

		imagefttext( $im, $font_size, 0, $tx, $ty, $font_color, $font_file, $content );

		$name_md5 = md5( $content );
		imagepng( $im, $this->tmp_dir . '/' . $name_md5 . '.png' );
		imagedestroy( $im );

		$content = '<div><img style="vertical-align:middle;" src="' . $this->tmp_url . '/' . $name_md5 . '.png?' . wp_date( 'YmdHis' ) . '" alt="' . esc_attr( $alt_text ) . '" /></div>';

		return $content;
	}

	/** ==================================================
	 * HEX -> RGB
	 *
	 * @param string $hex  hex.
	 * @return array $red, $green, $blue
	 * @since 1.00
	 */
	private function hex_to_rgb( $hex ) {

		if ( substr( $hex, 0, 1 ) == '#' ) {
			$hex = substr( $hex, 1 );
		}
		if ( strlen( $hex ) == 3 ) {
			$hex = substr( $hex, 0, 1 ) . substr( $hex, 0, 1 ) . substr( $hex, 1, 1 ) . substr( $hex, 1, 1 ) . substr( $hex, 2, 1 ) . substr( $hex, 2, 1 );
		}

		$red = hexdec( substr( $hex, 0, 2 ) );
		$green = hexdec( substr( $hex, 2, 2 ) );
		$blue = hexdec( substr( $hex, 4, 2 ) );

		return array( $red, $green, $blue );
	}

	/** ==================================================
	 * Real Url
	 *
	 * @param  string $base  base.
	 * @param  string $relationalpath relationalpath.
	 * @return string $realurl realurl.
	 * @since  1.00
	 */
	private function realurl( $base, $relationalpath ) {

		$parse = array(
			'scheme'   => null,
			'user'     => null,
			'pass'     => null,
			'host'     => null,
			'port'     => null,
			'query'    => null,
			'fragment' => null,
		);
		$parse = wp_parse_url( $base );

		if ( strpos( $parse['path'], '/', ( strlen( $parse['path'] ) - 1 ) ) !== false ) {
			$parse['path'] .= '.';
		}

		if ( preg_match( '#^https?://#', $relationalpath ) ) {
			return $relationalpath;
		} elseif ( preg_match( '#^/.*$#', $relationalpath ) ) {
			return $parse['scheme'] . '://' . $parse['host'] . $relationalpath;
		} else {
			$base_path = explode( '/', dirname( $parse['path'] ) );
			$rel_path  = explode( '/', $relationalpath );
			foreach ( $rel_path as $rel_dir_name ) {
				if ( '.' === $rel_dir_name ) {
					array_shift( $base_path );
					array_unshift( $base_path, '' );
				} elseif ( '..' === $rel_dir_name ) {
					array_pop( $base_path );
					if ( count( $base_path ) === 0 ) {
						$base_path = array( '' );
					}
				} else {
					array_push( $base_path, $rel_dir_name );
				}
			}
			$path = implode( '/', $base_path );
			return $parse['scheme'] . '://' . $parse['host'] . $path;
		}
	}
}
