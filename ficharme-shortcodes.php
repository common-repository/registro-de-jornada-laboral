<?php


function ficharme_fichajes(){

	$current_user = wp_get_current_user();

	global $wpdb;

	$consulta_where = " 1  ";

	if ( !is_ficharme_boss() ) {

		$consulta_where .= "AND user_id = $current_user->ID";
	}

	$consulta =  "SELECT * FROM ".$wpdb->prefix."buzz_registro_fichaje WHERE $consulta_where ORDER BY hora_registro DESC";

	$fichajes = $wpdb->get_results( $consulta, OBJECT );

	$return = '<div class="listado-fichajes">';
	if($fichajes):
		$return .= '<table>';
		$return .= '<caption>'.__('Todos los registros', 'ficharme').'</caption>';
		$return .= '<thead>';
		$return .= '<tr>';
		$return .= '<th>'.__('Nombre', 'ficharme').'</th>';
		$return .= '<th>'.__('Hora de fichaje', 'ficharme').'</th>';
		$return .= '<th>'.__('Tipo', 'ficharme').'</th>';

		$return .= '</tr>';
		$return .= '</thead>';
		$return .= '<tbody>';
		foreach ($fichajes as $key => $fichaje):


			$return .= '<tr>';
			$return .= '<td>'.get_userdata($fichaje->user_id)->display_name.'</td>';

			$return .= '<td>'.date( "d/m/Y H:i:s", strtotime($fichaje->hora_registro)) .'</td>';
			$return .= '<td>'.$fichaje->tipo_registro.'</td>';
			$return .= '</tr>';


		endforeach;

		$return .= '</tbody>';
		$return .= '</table>';
	endif;
	$return .= '</div>';

	return $return;

}

add_shortcode( 'ficharme_fichajes', 'ficharme_fichajes' );


function ficharme_fichar(){

	if (!is_user_logged_in()) {

		return __('Usuario no logueado', 'ficharme');
	}

	$current_user = wp_get_current_user();

	global $wpdb;


	$fichajes = $wpdb->get_results( "SELECT * FROM ".$wpdb->prefix."buzz_registro_fichaje WHERE user_id = $current_user->ID ORDER BY hora_registro DESC", OBJECT );



	$return = '<div class="registrar-fichaje">';

	$return .= __('Hola ','ficharme') . $current_user->display_name .'<br>';
	
	if($fichajes):


		$tipo_registro =  ($fichajes[0]->tipo_registro == 'entrada') ? 'salida' : 'entrada';

	else:
		$tipo_registro = 'entrada';

	endif;


	$return .= '<button type="" id="registar" data-tipo-registro="'.$tipo_registro.'" class="'.$tipo_registro.'">'.__("$tipo_registro", "ficharme").'</button>';

	$return .= '</div>';

	return $return;

}

add_shortcode( 'ficharme_fichar', 'ficharme_fichar' );
