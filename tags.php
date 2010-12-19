<?php
	session_start();
	if (!isset($_SESSION['temp']))
		$_SESSION['temp'] = array();
		
	//Inizializzazione PHP ActiveRecord	
	require_once 'php-activerecord/ActiveRecord.php';
	ActiveRecord\Config::initialize(function($cfg){
		$cfg->set_model_directory('models');
		$cfg->set_connections(array('development' => 'sqlite://my_database.db'));
	});
	
	if (!isset($_GET['userid'])){
		return "";
	}
		
	//individuo utente
	$user = User::first( 
		array(
			'conditions' => array('id = ?', $_GET['userid'])
		)
	);
	
	//se utente non valido interrompo
	if ($user == null){
		return "";
	}

	$tags = Tag::find(
		'all',
		array(
			'conditions' => array('user_id = ?', $user->id),
		)
	);

	$return_arr = array();
	
	foreach ($tags as $tag){
		$row_array['value'] = $tag->name;
		array_push($return_arr,$row_array);
	}

	echo json_encode($return_arr);
?>