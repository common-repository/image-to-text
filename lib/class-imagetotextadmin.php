<?php
/**
 * Image to Text
 *
 * @package    ImageToText
 * @subpackage ImageToText Management screen
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

$imagetotextadmin = new ImageToTextAdmin();

/** ==================================================
 * Management screen
 */
class ImageToTextAdmin {

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
	 * Construct
	 *
	 * @since 1.00
	 */
	public function __construct() {

		$wp_uploads = wp_upload_dir();
		$relation_path_true = strpos( $wp_uploads['baseurl'], '../' );
		if ( $relation_path_true > 0 ) {
			$upload_dir = wp_normalize_path( realpath( $wp_uploads['basedir'] ) );
		} else {
			$upload_dir = wp_normalize_path( $wp_uploads['basedir'] );
		}
		$upload_dir = untrailingslashit( $upload_dir );
		$this->tmp_dir = $upload_dir . '/image-to-text';
		$this->font_dir = $upload_dir . '/image-to-text/font';

		add_action( 'admin_menu', array( $this, 'plugin_menu' ) );
		add_action( 'admin_enqueue_scripts', array( $this, 'load_custom_wp_admin_style' ) );
		add_filter( 'plugin_action_links', array( $this, 'settings_link' ), 10, 2 );
		add_action( 'admin_print_footer_scripts', array( $this, 'imagetotext_add_quicktags' ), 100 );
	}

	/** ==================================================
	 * Add a "Settings" link to the plugins page
	 *
	 * @param  array  $links  links array.
	 * @param  string $file   file.
	 * @return array  $links  links array.
	 * @since 1.00
	 */
	public function settings_link( $links, $file ) {
		static $this_plugin;
		if ( empty( $this_plugin ) ) {
			$this_plugin = 'image-to-text/imagetotext.php';
		}
		if ( $file == $this_plugin ) {
			$links[] = '<a href="' . admin_url( 'options-general.php?page=ImageToText' ) . '">' . __( 'Settings' ) . '</a>';
		}
			return $links;
	}

	/** ==================================================
	 * Settings page
	 *
	 * @since 1.00
	 */
	public function plugin_menu() {
		add_options_page( 'Image to Text Options', 'Image to Text', 'manage_options', 'ImageToText', array( $this, 'plugin_options' ) );
	}

	/** ==================================================
	 * Add Css and Script
	 *
	 * @since 1.00
	 */
	public function load_custom_wp_admin_style() {
		if ( $this->is_my_plugin_screen() ) {
			wp_enqueue_style( 'wp-color-picker' );
			wp_enqueue_script( 'jquery' );
			wp_enqueue_script( 'colorpicker-admin-js', plugin_dir_url( __DIR__ ) . 'js/jquery.colorpicker.admin.js', array( 'wp-color-picker' ), '1.0.0', false );
		}
	}

	/** ==================================================
	 * For only admin style
	 *
	 * @since 1.00
	 */
	private function is_my_plugin_screen() {
		$screen = get_current_screen();
		if ( is_object( $screen ) && 'settings_page_ImageToText' == $screen->id ) {
			return true;
		} else {
			return false;
		}
	}

	/** ==================================================
	 * Settings page
	 *
	 * @since 1.00
	 */
	public function plugin_options() {

		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( esc_html__( 'You do not have sufficient permissions to access this page.' ) );
		}

		$this->options_updated();

		$scriptname = admin_url( 'options-general.php?page=ImageToText' );

		$imagetotext_option = get_option( 'image_to_text' );

		?>
		<div class="wrap">
		<h2>Image to Text</h2>

			<details>
			<summary><strong><?php esc_html_e( 'Various links of this plugin', 'image-to-text' ); ?></strong></summary>
			<?php $this->credit(); ?>
			</details>

			<div class="wrap">
				<details style="margin-bottom: 5px;" open>
					<summary style="cursor: pointer; padding: 10px; border: 1px solid #ddd; background: #f4f4f4; color: #000;"><strong><?php esc_html_e( 'Settings' ); ?></strong></summary>				<form method="post" action="<?php echo esc_url( $scriptname . '#imagetotext-admin-tabs-2' ); ?>">
					<?php wp_nonce_field( 'imgt_set', 'imagetotext_settings' ); ?>

					<div class="submit">
						<?php submit_button( __( 'Save Changes' ), 'large', 'ImageToTextSetApply', false ); ?>
						<?php submit_button( __( 'Default' ), 'large', 'Default', false ); ?>
					</div>

					<div style="margin: 5px; padding: 5px;">
						<div style="display: block; padding:5px 5px;">
							<code>font_size</code> : 
							<input type="number" step="1" min="3" max="100" class="screen-per-page" name="imagetotext_font_size" maxlength="3" value="<?php echo esc_attr( $imagetotext_option['font_size'] ); ?>"> <?php esc_html_e( 'Font Size', 'image-to-text' ); ?>
						</div>
						<div style="display: block; padding:5px 5px;">
							<code>back_color</code> : 
							<input type="text" class="wpcolor" name="imagetotext_back_color" value="<?php echo esc_attr( $imagetotext_option['back_color'] ); ?>" size="10" /> <?php esc_html_e( 'Back Color', 'image-to-text' ); ?>
						</div>
						<div style="display: block; padding:5px 5px;">
							<code>font_color</code> : 
							<input type="text" class="wpcolor" name="imagetotext_font_color" value="<?php echo esc_attr( $imagetotext_option['font_color'] ); ?>" size="10" /> <?php esc_html_e( 'Font Color', 'image-to-text' ); ?>
						</div>
						<div style="display: block; padding:5px 5px">
						<code>alt</code> : 
						<input type="checkbox" name="imagetotext_alt_text" value="1" <?php checked( '1', $imagetotext_option['alt_text'] ); ?> /> <?php esc_html_e( 'alt text', 'image-to-text' ); ?>
						</div>
						<hr>
						<div style="display: block; padding:5px 5px;">
							<?php esc_html_e( 'Font', 'image-to-text' ); ?> : 
							<?php
							$fontselectbox = null;
							$fontselectbox .= '<select name="imagetotext_font_file" style="width: 250px">';
							$fontfiles = scandir( $this->font_dir );
							$fontfiles = array_diff( $fontfiles, array( '.', '..' ) );
							$fontfiles = array_values( $fontfiles );
							foreach ( $fontfiles as $fontfile ) {
								if ( $imagetotext_option['font_file'] === $fontfile ) {
									$fontselect = '<option value="' . $fontfile . '" selected>' . $fontfile . '</option>';
								} else {
									$fontselect = '<option value="' . $fontfile . '">' . $fontfile . '</option>';
								}
								$fontselectbox .= $fontselect;
							}
							$fontselectbox .= '</select>';
							$allowed_html = array(
								'select'  => array(
									'name'  => array(),
									'style'  => array(),
								),
								'option'  => array(
									'value'  => array(),
									'selected'  => array(),
								),
							);
							echo wp_kses( $fontselectbox, $allowed_html );
							?>
						</div>
						<div style="display: block; padding:5px 5px">
						<?php esc_html_e( 'Font baseline adjust', 'image-to-text' ); ?> : 
						<?php esc_html_e( 'Up', 'image-to-text' ); ?><input type="range" style="vertical-align:middle;" step="0.01" min="1.20" max="1.45" name="imagetotext_vt_mg" value="<?php echo esc_attr( floatval( $imagetotext_option['vt_mg'] ) ); ?>" /><?php esc_html_e( 'Down', 'image-to-text' ); ?>
						</div>

					</div>

					<div class="submit">
						<?php submit_button( __( 'Save Changes' ), 'large', 'ImageToTextSetApply', false ); ?>
						<?php submit_button( __( 'Image File' ) . ' ' . __( 'Delete' ), 'large', 'DeleteFiles', false ); ?>
					</div>

					</form>
				</details>

				<details style="margin-bottom: 5px;">
				<summary style="cursor: pointer; padding: 10px; border: 1px solid #ddd; background: #f4f4f4; color: #000;"><strong><?php esc_html_e( 'Shortcode', 'image-to-text' ); ?></strong></summary>
					<h4><?php esc_html_e( 'Example', 'image-to-text' ); ?></h4>

					<li style="margin: 0px 40px;">
						<div><?php esc_html_e( 'Write a Shortcode. The following text field. Enclose text.', 'image-to-text' ); ?></div>
						<div><?php esc_html_e( 'Code' ); ?>: <code>&#91imagetotext font_size=12 back_color="#ff0000" font_color="#000000" alt="on"&#93This is test.&#91/imagetotext&#93</code></div>
					</li>
					<li style="margin: 0px 40px;">
						<div><?php esc_html_e( 'Write a Shortcode. The following template. Enclose text.', 'image-to-text' ); ?></div>
						<div><?php esc_html_e( 'Code' ); ?>: <code>&lt;?php echo do_shortcode('&#91imagetotext font_size=12 back_color="#ff0000" font_color="#000000" alt="test"&#93This is test.&#91/imagetotext&#93'); ?&gt;</code></div>
					</li>

					<h4><?php esc_html_e( 'Atribute', 'image-to-text' ); ?></h4>
					<li style="margin: 0px 40px;">
						<div><code>font_size</code> : <?php esc_html_e( 'Font size', 'image-to-text' ); ?></div>
					</li>
					<li style="margin: 0px 40px;">
						<div><code>back_color</code> : <?php esc_html_e( 'Background color', 'image-to-text' ); ?></div>
					</li>
					<li style="margin: 0px 40px;">
						<div><code>font_color</code> : <?php esc_html_e( 'Font color', 'image-to-text' ); ?></div>
					</li>
					<li style="margin: 0px 40px;">
						<div><code>alt</code> : <?php esc_html_e( 'If the alt is the same as the content, specify "on". To specify texts other than content, enter text.', 'image-to-text' ); ?></div>
					</li>

					<h4><?php esc_html_e( 'If there is no value in the attribute of the short code, settings is read.', 'image-to-text' ); ?></h4>
					<?php $html_font_dir = '<code>' . $this->font_dir . '</code>'; ?>
					<h4>
					<?php
					/* translators: Upload and select font */
					echo wp_kses_post( sprintf( __( 'If you want to add fonts, upload the file to %1$s. You can select from select boxes.', 'image-to-text' ), $html_font_dir ) );
					?>
					</h4>
				</details>

			</div>
		</div>
		<?php
	}

	/** ==================================================
	 * Credit
	 *
	 * @since 1.00
	 */
	private function credit() {

		$plugin_name    = null;
		$plugin_ver_num = null;
		$plugin_path    = plugin_dir_path( __DIR__ );
		$plugin_dir     = untrailingslashit( wp_normalize_path( $plugin_path ) );
		$slugs          = explode( '/', $plugin_dir );
		$slug           = end( $slugs );
		$files          = scandir( $plugin_dir );
		foreach ( $files as $file ) {
			if ( '.' === $file || '..' === $file || is_dir( $plugin_path . $file ) ) {
				continue;
			} else {
				$exts = explode( '.', $file );
				$ext  = strtolower( end( $exts ) );
				if ( 'php' === $ext ) {
					$plugin_datas = get_file_data(
						$plugin_path . $file,
						array(
							'name'    => 'Plugin Name',
							'version' => 'Version',
						)
					);
					if ( array_key_exists( 'name', $plugin_datas ) && ! empty( $plugin_datas['name'] ) && array_key_exists( 'version', $plugin_datas ) && ! empty( $plugin_datas['version'] ) ) {
						$plugin_name    = $plugin_datas['name'];
						$plugin_ver_num = $plugin_datas['version'];
						break;
					}
				}
			}
		}
		$plugin_version = __( 'Version:' ) . ' ' . $plugin_ver_num;
		/* translators: FAQ Link & Slug */
		$faq       = sprintf( __( 'https://wordpress.org/plugins/%s/faq', 'image-to-text' ), $slug );
		$support   = 'https://wordpress.org/support/plugin/' . $slug;
		$review    = 'https://wordpress.org/support/view/plugin-reviews/' . $slug;
		$translate = 'https://translate.wordpress.org/projects/wp-plugins/' . $slug;
		$facebook  = 'https://www.facebook.com/katsushikawamori/';
		$twitter   = 'https://twitter.com/dodesyo312';
		$youtube   = 'https://www.youtube.com/channel/UC5zTLeyROkvZm86OgNRcb_w';
		$donate    = __( 'https://shop.riverforest-wp.info/donate/', 'image-to-text' );

		?>
		<span style="font-weight: bold;">
		<div>
		<?php echo esc_html( $plugin_version ); ?> | 
		<a style="text-decoration: none;" href="<?php echo esc_url( $faq ); ?>" target="_blank" rel="noopener noreferrer">FAQ</a> | <a style="text-decoration: none;" href="<?php echo esc_url( $support ); ?>" target="_blank" rel="noopener noreferrer">Support Forums</a> | <a style="text-decoration: none;" href="<?php echo esc_url( $review ); ?>" target="_blank" rel="noopener noreferrer">Reviews</a>
		</div>
		<div>
		<a style="text-decoration: none;" href="<?php echo esc_url( $translate ); ?>" target="_blank" rel="noopener noreferrer">
		<?php
		/* translators: Plugin translation link */
		echo esc_html( sprintf( __( 'Translations for %s' ), $plugin_name ) );
		?>
		</a> | <a style="text-decoration: none;" href="<?php echo esc_url( $facebook ); ?>" target="_blank" rel="noopener noreferrer"><span class="dashicons dashicons-facebook"></span></a> | <a style="text-decoration: none;" href="<?php echo esc_url( $twitter ); ?>" target="_blank" rel="noopener noreferrer"><span class="dashicons dashicons-twitter"></span></a> | <a style="text-decoration: none;" href="<?php echo esc_url( $youtube ); ?>" target="_blank" rel="noopener noreferrer"><span class="dashicons dashicons-video-alt3"></span></a>
		</div>
		</span>

		<div style="width: 250px; height: 180px; margin: 5px; padding: 5px; border: #CCC 2px solid;">
		<h3><?php esc_html_e( 'Please make a donation if you like my work or would like to further the development of this plugin.', 'image-to-text' ); ?></h3>
		<div style="text-align: right; margin: 5px; padding: 5px;"><span style="padding: 3px; color: #ffffff; background-color: #008000">Plugin Author</span> <span style="font-weight: bold;">Katsushi Kawamori</span></div>
		<button type="button" style="margin: 5px; padding: 5px;" onclick="window.open('<?php echo esc_url( $donate ); ?>')"><?php esc_html_e( 'Donate to this plugin &#187;' ); ?></button>
		</div>

		<?php
	}

	/** ==================================================
	 * Update wp_options table.
	 *
	 * @since 1.00
	 */
	private function options_updated() {

		if ( isset( $_POST['Default'] ) && ! empty( $_POST['Default'] ) ) {
			if ( check_admin_referer( 'imgt_set', 'imagetotext_settings' ) ) {
				$image_to_text_reset_tbl = array(
					'font_size' => 12,
					'back_color' => '#ffffff',
					'font_color' => '#000000',
					'font_file' => 'NotoSansJP-Regular.otf',
					'vt_mg' => 1.35,
					'alt_text' => false,
				);
				update_option( 'image_to_text', $image_to_text_reset_tbl );
				echo '<div class="notice notice-success is-dismissible"><ul><li>' . esc_html( __( 'Settings' ) . ' --> ' . __( 'Default' ) . ' --> ' . __( 'Changes saved.' ) ) . '</li></ul></div>';
			}
		}

		if ( isset( $_POST['DeleteFiles'] ) && ! empty( $_POST['DeleteFiles'] ) ) {
			if ( check_admin_referer( 'imgt_set', 'imagetotext_settings' ) ) {
				$del_filename = $this->tmp_dir . '/*.*';
				foreach ( glob( $del_filename ) as $val ) {
					wp_delete_file( $val );
				}
				echo '<div class="notice notice-success is-dismissible"><ul><li>' . esc_html( __( 'Image File' ) . ' --> ' . __( 'Delete' ) ) . '</li></ul></div>';
			}
		}

		if ( isset( $_POST['ImageToTextSetApply'] ) && ! empty( $_POST['ImageToTextSetApply'] ) ) {
			if ( check_admin_referer( 'imgt_set', 'imagetotext_settings' ) ) {
				$image_to_text_settings = get_option( 'image_to_text' );
				if ( ! empty( $_POST['imagetotext_font_size'] ) ) {
					$image_to_text_settings['font_size'] = intval( $_POST['imagetotext_font_size'] );
				}
				if ( ! empty( $_POST['imagetotext_back_color'] ) ) {
					$image_to_text_settings['back_color'] = sanitize_text_field( wp_unslash( $_POST['imagetotext_back_color'] ) );
				}
				if ( ! empty( $_POST['imagetotext_font_color'] ) ) {
					$image_to_text_settings['font_color'] = sanitize_text_field( wp_unslash( $_POST['imagetotext_font_color'] ) );
				}
				if ( ! empty( $_POST['imagetotext_font_file'] ) ) {
					$image_to_text_settings['font_file'] = sanitize_text_field( wp_unslash( $_POST['imagetotext_font_file'] ) );
				}
				if ( ! empty( $_POST['imagetotext_vt_mg'] ) ) {
					$image_to_text_settings['vt_mg'] = floatval( $_POST['imagetotext_vt_mg'] );
				}
				if ( ! empty( $_POST['imagetotext_alt_text'] ) ) {
					$image_to_text_settings['alt_text'] = true;
				} else {
					$image_to_text_settings['alt_text'] = false;
				}
				update_option( 'image_to_text', $image_to_text_settings );
				echo '<div class="notice notice-success is-dismissible"><ul><li>' . esc_html( __( 'Settings' ) . ' --> ' . __( 'Changes saved.' ) ) . '</li></ul></div>';
			}
		}
	}

	/** ==================================================
	 * Add Quick Tag
	 *
	 * @since 1.00
	 */
	public function imagetotext_add_quicktags() {
		if ( wp_script_is( 'quicktags' ) ) {
			?>
		<script type="text/javascript">
			QTags.addButton( 'imagetotext', 'imagetotext', '[imagetotext]', '[/imagetotext]' );
		</script>
			<?php
		}
	}
}


