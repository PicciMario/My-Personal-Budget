<?php
	session_start();
	if (!isset($_SESSION['temp']))
		$_SESSION['temp'] = array();
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
		<link rel="stylesheet" href="blueprint/screen.css" type="text/css" media="screen, projection">
		<link rel="stylesheet" href="blueprint/print.css" type="text/css" media="print">	
		<!--[if lt IE 8]>
			<link rel="stylesheet" href="css/blueprint/ie.css" type="text/css" media="screen, projection">
		<![endif]-->

		<!-- JQuery UI -->
		<link type="text/css" href="css/ui-lightness/jquery-ui-1.8.6.custom.css" rel="stylesheet" />	
		<script type="text/javascript" src="js/jquery-1.4.2.min.js"></script>
		<script type="text/javascript" src="js/jquery-ui-1.8.6.custom.min.js"></script>
		
		<link type="text/css" href="pmstyle.css" rel="stylesheet" />	
		
		<!-- PHP Active Record -->
		<?php
			require_once 'php-activerecord/ActiveRecord.php';
			ActiveRecord\Config::initialize(function($cfg){
				$cfg->set_model_directory('models');
				$cfg->set_connections(array('development' => 'sqlite://my_database.db'));
			});
						
		?>
   		
   		<script>
   		$(function(){
   			$("a#logoutlink").button();
   		}
   		)
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
		unset($_SESSION['accountid']);
		unset($_SESSION['username']);
		
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
		Gestione contabilit&agrave; by <a href="mailto:mario.piccinelli@gmail.com">PicciMario</a>
	</div>
	
	<hr>
	
	<!-- Header -->
	<div class="span-18">
		<h3>Gestione Contabilit&agrave; personale.</h3>
		<p>Software messo assieme alla meno peggio ma comunque interessante :-)</p>
	</div>
	
	<div class="span-6 last">
			<?php
			if (isset($_SESSION['username'])){
				echo '<div style="text-align:right;">';
				echo '<a id="logoutlink" href="index.php?logout">Esci: '.$_SESSION['username'].'</a>';
				echo '</div>';
			}
		?>
	</div>
	
	<!-- Login -->
	<div class="span-24 last">
	<?php
	
	if (!isset($_SESSION['userid'])){
		?>
		<div style="background-color:#E0FFFF; text-align:right;">
		<form action="index.php" method=post class="inline">
		Username: <input type="text" name="username"></input>
		Password: <input type="password" name="password"></input>
		<input type=submit value="Login">
		</form>
		</div>
		<?php
	}
	
	?>
	</div>	
	
	<hr>
	
	<!-- Sidebar -->
	<div class="span-4">
		<a href="index.php" class="sbarlink">Home</a>
		
		<?php
			if (logged()){
			?>
				<a href="account.php" class="sbarlink">Conti</a>
			<?php
			}
			if (admin()){
			?>
				<a href="admin.php" class="sbarlink">Admin</a>
			<?php
			}
		?>
	</div>	
	

	<!-- Corpo della pagina -->
	<div class="column span-20 last">
		<div id="errormessage" class="error" style="display:none;"></div>
		<div id="successmessage" class="success" style="display:none;"></div>
		
		<?php
			
			if (isset($pagedata['pagetitle'])){
				echo '<h2>'.$pagedata['pagetitle'].'</h2>';
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
