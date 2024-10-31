<?php
/**
 * @package ficharme_lite
 * @version 1.2.1
 */
/*


Plugin Name: Registro de jornada laboral
Plugin URI: https://ivanbarreda.es/plugins/ficharme/
Description: Plugin para gestión de Fichar.me para el registro de jornada laboral cumpliendo la ley Española de fichaje de trabajadores.
Version: 1.2.1
Author URI: https://ivanbarreda.es/
Author: cuxaro
Author URI: https://ivanbarreda.es/
License: GPLv2 or later
Text Domain: fichame
*/



if ( !defined('ABSPATH') )
	die('-1');


define( 'FICHARME_LITE_PLUGIN_PATH', plugin_dir_path( __FILE__ ) );
define( 'FICHARME_LITE_PLUGIN_URL', plugin_dir_url( __FILE__ ) );


require_once FICHARME_LITE_PLUGIN_PATH . 'ficharme-shortcodes.php';


add_action( 'wp_enqueue_scripts', 'ficharme_ajax_enqueue_scripts' );

function ficharme_ajax_enqueue_scripts() {


	wp_enqueue_script( 'ficharme', plugins_url( '/js/ajax.js', __FILE__ ), array('jquery') );


	wp_localize_script('ficharme', 'ficharme_vars', array( 
		'ajaxurl' => admin_url( 'admin-ajax.php' )
	));
}



// Hook para usuarios no logueados
add_action('wp_ajax_nopriv_ficharme_registrar_fichaje', 'ficharme_registrar_fichaje');

// Hook para usuarios logueados
add_action('wp_ajax_ficharme_registrar_fichaje', 'ficharme_registrar_fichaje');


// Función que procesa la llamada AJAX
function ficharme_registrar_fichaje(){


	$ip = 'not defined';
	if (!empty($_SERVER['HTTP_CLIENT_IP'])) {
		$ip = $_SERVER['HTTP_CLIENT_IP'];
	} elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
		$ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
	} else {
		$ip = $_SERVER['REMOTE_ADDR'];
	}

	global $wpdb;

	$current_user = wp_get_current_user();
	if ( 0 == $current_user->ID ) {
		die();
	}

	$table = $wpdb->prefix.'buzz_registro_fichaje';

	$data = array(
		'user_id' => $current_user->ID, 
		'tipo_registro' => sanitize_text_field($_POST['tipoRegistro']),
		'ip'	=>	$ip
	);
	$wpdb->insert($table,$data);


	$nuevo_tipo_fichaje = sanitize_text_field($_POST['tipoRegistro']) == 'entrada' ? 'salida' : 'entrada';

	echo json_encode(
		array(
			'ultimo_fichaje' =>  date( "d/m/Y H:i:s",strtotime(current_time( 'mysql' ))), 
			'nuevo_tipo_fichaje' => $nuevo_tipo_fichaje
		)
	);

	die();
}



//Instalacion

function ficharme_create_plugin_database_table(){

	
	global $table_prefix, $wpdb;

	$table_name = 'buzz_registro_fichaje';
	$wp_track_table = $table_prefix."$table_name";

    #Check to see if the table exists already, if not, then create it

	if($wpdb->get_var( "show tables like '$wp_track_table'" ) != $wp_track_table) 
	{


		$sql = "CREATE TABLE `". $wp_track_table . "` ( ";

		$sql .= "  `registro_id`  int(11)   NOT NULL auto_increment, ";
		$sql .= "  `user_id`  int(11)   NOT NULL, ";
		$sql .= "  `hora_registro` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP, ";
		$sql .= " `tipo_registro` TEXT NOT NULL, ";
		$sql .= " `ip` TEXT NOT NULL, ";


		$sql .= "  PRIMARY KEY (`registro_id`) "; 
		$sql .= ") ENGINE=MyISAM DEFAULT CHARACTER SET=utf8mb4 COLLATE=utf8mb4_unicode_520_ci	 AUTO_INCREMENT=1 ; ";

		require_once( ABSPATH . '/wp-admin/includes/upgrade.php' );
		dbDelta($sql);
	}
}

register_activation_hook( __FILE__, 'ficharme_create_plugin_database_table' );

// Deleting the table whenever a blog is deleted
function ficharme_on_delete( $tables ) {


	global $wpdb;
	$table_name = 'buzz_registro_fichaje';

	$wpdb->query("DROP TABLE IF EXISTS `".$wpdb->prefix . "$table_name"."`");


}
register_uninstall_hook(__FILE__, 'ficharme_on_delete');



//Añadir Capabilities
function ficharme_add_roles() {

	$role_empresa = get_option('role_empresa_creado');

	if(!$role_empresa){
		add_role( 'empresa', __('Empresa', 'ficharme'), array( 
			'add_users' => true,  
			'read'  => true ,
			'edit_users'	=>	true,
			'delete_users'	=> true,
			'create_users'	=>	true,
			'list_users'	=>	true,
			'remove_users'	=>	true,
			'promote_users'	=>	true,


		) );

		update_option('role_empresa_creado',true);
	}

	$role_trabajador = get_option('role_trabajador_creado');

	if(!$role_trabajador){
		add_role( 'trabajador', __('Trabajador', 'ficharme'), array( 'read' => true ) );

		update_option('role_trabajador_creado',true);
	}

}

add_action('after_setup_theme','ficharme_add_roles');



function is_ficharme_boss(){


	return (current_user_can('administrator') || current_user_can( 'empresa' )) ? true : false;

}



