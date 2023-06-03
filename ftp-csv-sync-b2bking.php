<?php
/*
Plugin Name: FTP CSV Sync B2BKing
Description: Este plugin sincroniza archivos CSV desde un servidor FTP.
Version: 1.0.0
Author: Aldair Florez Acuña
*/

// Si este archivo es llamado directamente, abortar.
if ( ! defined( 'WPINC' ) ) {
    die;
}

require_once plugin_dir_path( __FILE__ ) . 'includes/class-ftp-csv-sync-b2bking.php';

function run_ftp_csv_sync_b2bking() {
    $plugin = new FTP_CSV_Sync_B2BKing( 'ftp-csv-sync-b2bking', '1.0.0' );

    // Registrar los hooks de activación y desactivación
    register_activation_hook( __FILE__, array( $plugin, 'activate' ) );
    register_deactivation_hook( __FILE__, array( $plugin, 'deactivate' ) );

    // Encolar estilos y scripts
    add_action( 'wp_enqueue_scripts', array( $plugin, 'enqueue_styles' ) );
    add_action( 'wp_enqueue_scripts', array( $plugin, 'enqueue_scripts' ) );

    // Agregar la página de configuración del plugin en el menú de administración
    if ( is_admin() ) {
        add_action( 'admin_menu', array( $plugin, 'add_plugin_page' ) );
    }
}

run_ftp_csv_sync_b2bking();

// Cambios 
