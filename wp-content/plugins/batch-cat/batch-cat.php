<?php
/*
Plugin Name: Batch Cat
Version: 0.3
Description: A plugin which offers the abillity to change categories of articles in batches
Author: Lenin Lee
Author URI: http://sinolog.it
Plugin URI: http://sinolog.it/?p=1603
 */
require_once dirname(__FILE__).'/admin.php';

global $wp_version;

$numVerBase = 2.8;
$strVerMsg = "Batch Cat requires Wordpress $numVerBase or newer !";

if (version_compare($wp_version, $numVerBase, '<')) {
    exit($strVerMsg);
}

$bcat_plugin_url = trailingslashit('/wp-content/plugins/'.dirname(plugin_basename(__FILE__)));

register_activation_hook(__FILE__, 'bcat_reset_options');
add_action('admin_init', 'bcat_register_settings');
add_action('admin_menu', 'bcat_add_admin_page');
add_action('admin_menu', 'bcat_add_config_page');
add_action('wp_print_scripts', 'bcat_script_action');
add_action('wp_ajax_bcat_set_category', 'bcat_ajax_set_category');
add_action('wp_ajax_bcat_add_category', 'bcat_ajax_add_category');
add_action('wp_ajax_bcat_del_category', 'bcat_ajax_del_category');
?>
