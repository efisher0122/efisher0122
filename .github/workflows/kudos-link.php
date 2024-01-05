<?php
/*
Plugin Name: Kudos Links Developer Edition
Plugin URI: https://kudoslinks.com/pl/plugin-uri
Description: Shrink, track and share any URL using your website and brand!
Version: 3.5.0
Author: Kudos Links
Author URI: http://kudoslinks.com
Text Domain: kudos-link
Copyright: 2004-2020, Caseproof, LLC

GNU General Public License, Free Software Foundation <http://creativecommons.org/licenses/GPL/2.0/>
This program is free software; you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation; either version 2 of the License, or
(at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program; if not, write to the Free Software
Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
*/
if(!defined('ABSPATH')) { die('You are not allowed to call this page directly.'); }

define('KULI_PLUGIN_SLUG','kudos-link/kudos-link.php');
define('KULI_PLUGIN_NAME','kudos-link');
define('KULI_PATH',WP_PLUGIN_DIR.'/'.KULI_PLUGIN_NAME);
define('KULI_CONTROLLERS_PATH',KULI_PATH.'/app/controllers');
define('KULI_MODELS_PATH',KULI_PATH.'/app/models');
define('KULI_HELPERS_PATH',KULI_PATH.'/app/helpers');
define('KULI_VIEWS_PATH',KULI_PATH.'/app/views');
define('KULI_LIB_PATH',KULI_PATH.'/app/lib');
define('KULI_I18N_PATH',KULI_PATH.'/i18n');
define('KULI_CSS_PATH',KULI_PATH.'/css');
define('KULI_JS_PATH',KULI_PATH.'/js');
define('KULI_IMAGES_PATH',KULI_PATH.'/images');
define('KULI_VENDOR_LIB_PATH',KULI_PATH.'/vendor/lib');

define('KULI_URL',plugins_url($path = '/'.KULI_PLUGIN_NAME));
define('KULI_CONTROLLERS_URL',KULI_URL.'/app/controllers');
define('KULI_MODELS_URL',KULI_URL.'/app/models');
define('KULI_HELPERS_URL',KULI_URL.'/app/helpers');
define('KULI_VIEWS_URL',KULI_URL.'/app/views');
define('KULI_LIB_URL',KULI_URL.'/app/lib');
define('KULI_I18N_URL',KULI_URL.'/i18n');
define('KULI_CSS_URL',KULI_URL.'/css');
define('KULI_JS_URL',KULI_URL.'/js');
define('KULI_IMAGES_URL',KULI_URL.'/images');
define('KULI_VENDOR_LIB_URL',KULI_URL.'/vendor/lib');

define('KULI_BROWSER_URL','https://d14715w921jdje.cloudfront.net/browser');
define('KULI_OS_URL','https://d14715w921jdje.cloudfront.net/os');

define('KULI_EDITION', 'kudos-link-pro-developer');

update_option('kuli_activated', true);
update_option('kuli_activation_override',true);
update_option('kulipro-credentials',array('username'=>'GPL','password'=>'GPL'));
update_option('kulipro_activated',true);
update_option('plp_mothership_license','GPL');
wp_cache_delete('alloptions', 'options');
if(!defined('KUDOSLINK_LICENSE_KEY'))
{
define('KUDOSLINK_LICENSE_KEY','GPL');
}

/**
 * Returns current plugin version.
 *
 * @return string Plugin version
 */
function kuli_plugin_info($field) {
  static $plugin_folder, $plugin_file;

  if( !isset($plugin_folder) or !isset($plugin_file) ) {
    if( ! function_exists( 'get_plugins' ) ) {
      require_once( ABSPATH . '/wp-admin/includes/plugin.php' );
    }

    $plugin_folder = get_plugins( '/' . plugin_basename( dirname( __FILE__ ) ) );
    $plugin_file = basename( ( __FILE__ ) );
  }

  if(isset($plugin_folder[$plugin_file][$field])) {
    return $plugin_folder[$plugin_file][$field];
  }

  return '';
}

// Plugin Information from the plugin header declaration
define('KULI_VERSION', kuli_plugin_info('Version'));
define('KULI_DISPLAY_NAME', kuli_plugin_info('Name'));
define('KULI_AUTHOR', kuli_plugin_info('Author'));
define('KULI_AUTHOR_URI', kuli_plugin_info('AuthorURI'));
define('KULI_DESCRIPTION', kuli_plugin_info('Description'));

// Autoload all the requisite classes
function kuli_autoloader($class) {
  // Only load Kudos Link classes here
  if(preg_match('/^Kuli.+$/', $class)) {
    if(preg_match('/^(KuliBaseController)$/', $class)) {
      $filepath = KULI_LIB_PATH."/{$class}.php";
    }
    else if(preg_match('/^.+Controller$/', $class)) {
      $filepath = KULI_CONTROLLERS_PATH."/{$class}.php";
    }
    else if(preg_match('/^.+Helper$/', $class)) {
      $filepath = KULI_HELPERS_PATH."/{$class}.php";
    }
    else {
      $filepath = KULI_MODELS_PATH."/{$class}.php";

      // Now let's try the lib dir if its not a model
      if(!file_exists($filepath)) {
        $filepath = KULI_LIB_PATH."/{$class}.php";
      }
    }

    if(file_exists($filepath)) {
      require_once($filepath);
    }
  }
}

// if __autoload is active, put it on the spl_autoload stack
if(is_array(spl_autoload_functions()) && in_array('__autoload', spl_autoload_functions())) {
  spl_autoload_register('__autoload');
}

// Add the autoloader
spl_autoload_register('kuli_autoloader');

// The number of items per page on a table
global $page_size;
$page_size = 10;

global $kuli_blogurl, $kuli_siteurl, $kuli_blogname, $kuli_blogdescription;

function kuli_get_home_url() {
  $kuli_bid = null;

  if(function_exists('is_multisite') && is_multisite() && function_exists('get_current_blog_id')) {
    $kuli_bid = get_current_blog_id();
  }

  // Fix WPML adding the language code at the start of the URL
  if(defined('ICL_SITEPRESS_VERSION')) {
    if(empty($kuli_bid) || !function_exists('is_multisite') || !is_multisite()) {
      $url = get_option('home');
    }
    else {
      switch_to_blog($kuli_bid);
      $url = get_option('home');
      restore_current_blog();
    }

    return $url;
  }

  return get_home_url($kuli_bid);
}

$kuli_blogurl = kuli_get_home_url();
$kuli_siteurl = get_option('siteurl');
$kuli_blogname = get_option('blogname');
$kuli_blogdescription = get_option('blogdescription');

/***** SETUP OPTIONS OBJECT *****/
global $kuli_options;
$kuli_options = KuliOptions::get_options();

// i18n
add_action('plugins_loaded', 'kuli_load_textdomain');
function kuli_load_textdomain() {
  $plugin_dir = basename(dirname(__FILE__));
  load_plugin_textdomain('kudos-link', false, $plugin_dir.'/i18n/');
}

register_activation_hook( __FILE__, 'kuli_activation' );
function kuli_activation() {
  add_option( 'kuli_just_activated', true );
}

add_action( 'plugins_loaded', 'kuli_run_activation' );
function kuli_run_activation() {
  if ( empty( get_option( 'kuli_just_activated' ) ) ) {
    return;
  }
  $pl_options = KuliOptions::get_options();
  $pl_options->activated_timestamp = time();
  $pl_options->store();
  delete_option( 'kuli_just_activated' );
}

global $kuli_link, $kuli_link_meta, $kuli_click, $kuli_group, $kuli_utils, $plp_update;

$kuli_link      = new KuliLink();
$kuli_link_meta = new KuliLinkMeta();
$kuli_click     = new KuliClick();
$kuli_group     = new KuliGroup();
$kuli_utils     = new KuliUtils();

global $kuli_db_version, $plp_db_version;

$kuli_db_version = 23; // this is the version of the database we're moving to
$plp_db_version = 11; // this is the version of the database we're moving to

global $kuli_app_controller;

// Load our controllers
$controllers = apply_filters( 'kuli_controllers', @glob( KULI_CONTROLLERS_PATH . '/*', GLOB_NOSORT ) );
foreach( $controllers as $controller ) {
  $class = preg_replace( '#\.php#', '', basename($controller) );
  if( preg_match( '#Kuli.*Controller#', $class ) ) {
    $obj = new $class;
    $obj->load_hooks();

    if( $class==='KuliAppController' ) {
      $kuli_app_controller = $obj;
    }
  }
}

$plp_update = new KuliUpdateController();

// Provide Back End Hooks to the Pro version of Kudos Link
if($plp_update->is_installed()) {
  require_once(KULI_PATH.'/pro/kudos-link-pro.php');
}

require_once(KULI_PATH.'/app/lib/KuliNotifications.php');
