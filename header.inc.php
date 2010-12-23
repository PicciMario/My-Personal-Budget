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
	
	include_once('include/printfunctions.inc.php');
	
	//debug
	$GLOBALS['debug'] = 1;
?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html>
    <head>
        <meta http-equiv="Content-type" content="text/html; charset=utf-8" />
        <title>
        <?php
        	if (isset($pagedata['pagetitle']))
        		echo $pagedata['pagetitle'];
        ?>
        </title>
        
        <!-- BluePrint CSS Framework -->
		<link rel="stylesheet" href="css/blueprint/screen.css" type="text/css" media="screen, projection">
		<link rel="stylesheet" href="css/blueprint/print.css" type="text/css" media="print">	
		<!--[if lt IE 8]>
			<link rel="stylesheet" href="css/blueprint/ie.css" type="text/css" media="screen, projection">
		<![endif]-->

		<!-- JQuery UI -->
		<link type="text/css" href="css/ui-lightness/jquery-ui-1.8.6.custom.css" rel="stylesheet" />	
		<script type="text/javascript" src="js/jquery-1.4.2.min.js"></script>
		<script type="text/javascript" src="js/jquery-ui-1.8.6.custom.min.js"></script>
		
		<!-- CSS applicazione -->
		<link type="text/css" href="css/pmstyle.css" rel="stylesheet" />	

		<script>
			function mostraDiv(divname){
				$("#"+divname).toggle();
			}
			function mostraDivSlow(divname){
				$("#"+divname).toggle('slow');
			}
		</script>
   		
    </head>
    
<body>

<?php

	//******* FUNZIONI STANDARD *************************************************************

	//restituisce 1 se l'utente loggato è admin
	function admin(){
		if (!isset($_SESSION['username']) || !isset($_SESSION['userlevel'])) {
			return 0;
		}
		if ($_SESSION['userlevel'] == 1) return 1;
		else return 0;
	}

	//restituisce 1 se l'utente è loggato
	function logged(){
		if (!isset($_SESSION['username']) || !isset($_SESSION['userlevel'])) {
			return 0;
		}
		return 1;
	}
	
	//stampa errore
	function err($text){
		echo '<p class="error">'.$text.'</p>';
	}

	//stampa errore con pulsante back
	function errback($text){
		echo '<p class="error">'.$text;
		echo '<br><a href="#" onClick="history.go(-1)">Torna indietro</a> ';
		echo '</p>';
	}
	
	//stampa conferma
	function conf($text){
		echo '<p class="success">'.$text.'</p>';
	}
	
	//stampa notice
	function notice($text){
		echo '<p class="notice">'.$text.'</p>';
	}
	
	//stampa debug
	function debug($text){
		global $debug;
		if ($debug != 0)
			echo '<p class="info"><strong>DEBUG:</strong> '.$text.'</p>';
	}
	
?>


<?php

	// Funzione di Login
	if (isset($_POST['username']) && isset($_POST['password'])){
		$user = User::first( 
			array(
				'conditions' => array(
					'username = ? AND password = ?', $_POST['username'], md5($_POST['password'])
				)
			)
		);

		if ($user != null){
			$_SESSION['temp'] = array();
			$_SESSION['username'] = $user->username;
			$_SESSION['userlevel'] = $user->userlevel;
			$_SESSION['userid'] = $user->id;
			
			//selezione primo account
			$account = Account::first(
				array(
					'conditions' => array('user_id = ?', $user->id)
				)
			);
			if ($account != null) $_SESSION['accountid'] = $account->id;
			
			?>
			<script>
				$(document).ready(function(){
					$("div#successmessage").show().append("Login eseguito<br>");
		   		});
			</script>
			<?php
		}
		else{
			?>
			<script>
		   		$(document).ready(function(){
			   		$(function(){
			   			$("div#errormessage").show().append("Credenziali errate<br>");
			   		})
		   		})
			</script>
			<?php
		}
	}

	// Funzione di logout
	if (isset($_GET['logout'])){
		unset($_SESSION['userid']);
		unset($_SESSION['userlevel']);
		unset($_SESSION['username']);
		unset($_SESSION['accountid']);
		
		?>
		<script>
	   		$(function(){
	   			$("div#successmessage").show().append("Logout eseguito correttamente<br>");
	   		})
		</script>
		<?php
	}
?>

<div class="container">

	<div class="span-24 last">
		Applicazione ottimizzata per Firefox, Safari e Google Chrome.. Se hai Internet Explorer mi spiace ma sono affari tuoi.
	</div>
	
	<hr>
	
	<!-- Header -->
	<div class="span-17 colborder">
		<h3>My Personal Budget.</h3>
		<p>Tu spendi, io prendo nota. Tu incassi, io prendo nota. Semplice.</p>
	</div>
	
	<div class="span-6 last">
			<?php
			if (isset($_SESSION['username'])){
				echo '<div style="text-align:right;">';
				echo '<a id="logoutlink" href="index.php?logout">'.$_SESSION['username'].'</a>';
				echo '</div>';
			}
			else{
				?>
				<div style="text-align:right;">
				<form action="index.php" method=post>
				Username: <input type="text" name="username"></input>
				Password: <input type="password" name="password"></input>
				<input type=submit value="Login">
				</form>
				</div>
				<?php
			}
		?>
	</div>
	
	<!-- Login -->
	<div class="span-24 last">
	</div>	
	
	<hr>
	
	<!-- Sidebar -->
	<div class="span-3">
		<a href="index.php" class="sbarlink">Home</a>
		
		<?php
			if (logged()){
			?>
				<a href="account.php?action=listaccount" class="sbarlink">Conti</a>
				<a href="account.php" class="sbarlink">Movimenti</a>
				<a href="categories.php" class="sbarlink">Categorie</a>
			<?php
			}
			if (admin()){
			?>
				<a href="admin.php" class="sbarlink">Admin</a>
			<?php
			}
		?>
	</div>	
	
	<script>
		$( ".sbarlink" ).button();
	</script>

	<!-- Corpo della pagina -->
	<div class="column span-18">
		<div id="errormessage" class="error" style="display:none;"></div>
		<div id="successmessage" class="success" style="display:none;"></div>
		
		<?php
			
			if (isset($pagedata['pagetitle'])){
				echo '<h3>'.$pagedata['pagetitle'].'</h3>';
			}
			
			if (isset($pagedata['onlyadmin'])){
				if ($pagedata['onlyadmin'] == 1 && admin() == 0){
					?>
					<script>
		   				$(function(){
		   					$("div#errormessage").show().append("Pagina riservata agli amministratori<br>");
		   				})
					</script>
					<?php
					include('footer.inc.php');
					die();
				}
			}
			
			if (isset($pagedata['onlylogged'])){
				if ($pagedata['onlylogged'] == 1 && logged() == 0){
					?>
					<script>
		   				$(function(){
		   					$("div#errormessage").show().append("Pagina riservata agli utenti registrati<br>");
		   				})
					</script>
					<?php
					include('footer.inc.php');
					die();
				}
			}
		
			
		?>
