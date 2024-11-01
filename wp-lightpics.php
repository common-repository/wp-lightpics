<?php
/*
Plugin Name: WP-LightPics
Plugin URI: http://www.infogeek.gr
Description: A plugin for displaying your site pictures with lightbox
Version: 1.03
Author: Konstantinos Tsatsarounos
Author URI: http://www.xtnd.it/marketplace
License: GPLv2
*/

/*
This program is free software; you can redistribute it and/or
modify it under the terms of the GNU General Public License
as published by the Free Software Foundation; either version 2
of the License, or (at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program; if not, write to the Free Software
Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA  02110-1301, USA.
*/

if( !defined( 'WPLB-PICTURES' ) )
    define( 'WPLB-PICTURES', true);

define( 'WPLB-PATH', dirname( plugin_basename( __FILE__ ) ) );
require plugin_dir_path(__FILE__).'includes/functions.php';

//Actions
add_action('wp_enqueue_scripts', 'wplb_initialize_scripts', 10);
add_action('wp_enqueue_scripts', 'wplb_initialize_styles', 10);

//Filters
add_filter( 'the_content', 'wplb_prepare_for_lightbox',10 );