<?php

class FTP_CSV_Sync_B2BKing
{
    private $plugin_name;
    private $version;

    public function __construct( $plugin_name, $version )
    {
        $this->plugin_name = $plugin_name;
        $this->version = $version;
    }

    public function activate()
    {
        // Activar acciones del plugin
    }

    public function deactivate()
    {
        // Desactivar acciones del plugin
    }

    public function enqueue_styles()
    {
        wp_enqueue_style( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'css/ftp-csv-sync-b2bking-public.css', array(), $this->version, 'all' );
    }

    public function enqueue_scripts()
    {
        wp_enqueue_script( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'js/ftp-csv-sync-b2bking-public.js', array( 'jquery' ), $this->version, false );
    }

    public function add_plugin_page()
    {
        add_options_page(
            'FTP CSV Sync B2BKing',
            'FTP CSV Sync B2BKing',
            'manage_options',
            'ftp-csv-sync-b2bking',
            array( $this, 'create_admin_page' )
        );
    }
private function get_ftp_tree( $ftp_conn, $dir )
{
	 error_log('In get_ftp_tree function');

    $ftp_tree = array();
    $file_list = ftp_nlist($ftp_conn, $dir);

    if ($file_list === false) {
        error_log('Error: ftp_nlist failed');
    }
    $items = ftp_nlist( $ftp_conn, $dir );

    $tree = array();
    foreach ( $items as $item )
    {
        if ( substr( $item, -1 ) == '.' )
        {
            continue;
        }

        $path = $dir . '/' . $item;
        $is_dir = @ftp_chdir( $ftp_conn, $path); // Try to change directory
        if ( $is_dir )
        {
            ftp_chdir( $ftp_conn, $dir ); // Go back to the parent directory
            $tree[ $item ] = $this->get_ftp_tree( $ftp_conn, $path ); // Recursion
        }
        else
        {
            $tree[] = $item;
        }
    }

    return $tree;
}


private function display_ftp_tree( $tree )
{
	 error_log('In display_ftp_tree function');

    if (empty($tree)) {
        error_log('Error: FTP tree is empty');
    }
    if ( empty( $tree ) )
    {
        return;
    }

    echo '<ul>';
    foreach ( $tree as $key => $value )
    {
        if ( is_array( $value ) )
        {
            echo '<li><strong>' . htmlspecialchars( $key ) . '</strong>';
            $this->display_ftp_tree( $value ); // Recursion
            echo '</li>';
        }
        else
        {
            echo '<li>' . htmlspecialchars( $value ) . '</li>';
        }
    }
    echo '</ul>';
}
   public function create_admin_page()
{
    // Check user capabilities
    if ( ! current_user_can( 'manage_options' ) )
    {
        return;
    }

    // Enqueue CSS and JS files
    wp_enqueue_style( 'admin-sync-css', plugin_dir_url( __FILE__ ) . '../admin/css/admin-style.css' );
    wp_enqueue_script( 'admin-sync-js', plugin_dir_url( __FILE__ ) . '../admin/js/admin-scripts.js', array( 'jquery' ), '1.0.0', true );

    // Process form submission
    $file_list = array();
    if ( isset( $_POST['submit'] ) )
    {
        $ftp_server = sanitize_text_field( $_POST['ftp_server'] );
        $ftp_username = sanitize_text_field( $_POST['ftp_username'] );
        $ftp_password = sanitize_text_field( $_POST['ftp_password'] );

        // Try to connect to FTP server
        $connection = $this->connect_to_ftp( $ftp_server, $ftp_username, $ftp_password );

        if ( $connection )
        {
            echo '<div class="notice notice-success is-dismissible"><p>Conexión FTP exitosa.</p></div>';

            // Get the list of files in the current directory
            $file_list = ftp_nlist( $connection, "." );

            ftp_close( $connection );
        }
        else
        {
            echo '<div class="notice notice-error is-dismissible"><p>No se pudo conectar al servidor FTP. Por favor, verifica tus credenciales.</p></div>';
        }
    }

    // Display the form
    ?>
    <div class="wrap">
        <h1>FTP CSV Sync B2BKing</h1>
        <form method="post" action="">
            <table class="form-table">
                <tbody>
                    <tr>
                        <th scope="row"><label for="ftp_server">Servidor FTP</label></th>
                        <td><input name="ftp_server" type="text" id="ftp_server" value="" class="regular-text"></td>
                    </tr>
                    <tr>
                        <th scope="row"><label for="ftp_username">Nombre de usuario</label></th>
                        <td><input name="ftp_username" type="text" id="ftp_username" value="" class="regular-text"></td>
                    </tr>
                    <tr>
                        <th scope="row"><label for="ftp_password">Contraseña</label></th>
                        <td><input name="ftp_password" type="password" id="ftp_password" value="" class="regular-text"></td>
                    </tr>
                </tbody>
            </table>
            <?php submit_button(); ?>
        </form>
    </div>
    <?php
    // Display the file list
if ( $connection )
{
	  if ( $connection ) {
        error_log('In create_admin_page, connection successful');
        echo '<h2>Lista de archivos y directorios:</h2>';
        $ftp_tree = $this->get_ftp_tree( $connection, '.' );
        $this->display_ftp_tree( $ftp_tree );
    }
    echo '<h2>Lista de archivos y directorios:</h2>';
    $ftp_tree = $this->get_ftp_tree( $connection, '.' );
    $this->display_ftp_tree( $ftp_tree );
}
}

    private function connect_to_ftp( $ftp_server, $ftp_username, $ftp_password )
    {
        $connection = ftp_connect( $ftp_server );

        if ( $connection )
        {
            $login_result = ftp_login( $connection, $ftp_username, $ftp_password );

            if ( $login_result )
            {
                return $connection;
            }
        }

        return false;
    }
}


