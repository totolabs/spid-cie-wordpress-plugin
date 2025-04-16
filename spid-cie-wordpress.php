<?php
/*
 * Plugin Name: SPID/CIE Wordpress Plugin
 * Description: Plugin Wordpress per configurare velocemente l'accesso tramite SPID/CIE, basato su SimpleSAMLphp.
 * Version: 1.1.0
 * Author: Totolabs Srl
 * Author URI: https://totolabs.it
 * License: GPL3
*/

// Inclusione file
require_once plugin_dir_path(__FILE__) . 'includes/admin.php';
require_once plugin_dir_path(__FILE__) . 'includes/functions.php';
require_once plugin_dir_path(__FILE__) . 'includes/frontend.php';

// Plugin activation & deactivation
register_activation_hook(__FILE__, 'wpsci_plugin_activate');
register_deactivation_hook(__FILE__, 'wpsci_plugin_deactivate');

function wpsci_plugin_activate() {
    // codice opzionale in fase di attivazione
}

function wpsci_plugin_deactivate() {
    // codice opzionale in fase di disattivazione
}
