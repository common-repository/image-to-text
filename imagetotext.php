<?php
/**
 * Plugin Name: Image to Text
 * Plugin URI:  https://wordpress.org/plugins/image-to-text/
 * Description: Image to text.
 * Version:     2.10
 * Author:      Katsushi Kawamori
 * Author URI:  https://riverforest-wp.info/
 * License:     GPLv2 or later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain: image-to-text
 *
 * @package Image to Text
 */

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

if ( ! class_exists( 'ImageToText' ) ) {
	require_once __DIR__ . '/lib/class-imagetotext.php';
}
if ( ! class_exists( 'ImageToTextAdmin' ) ) {
	require_once __DIR__ . '/lib/class-imagetotextadmin.php';
}
