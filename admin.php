<?php
	$pagedata['pagetitle'] = "Amministrazione";
	$pagedata['onlyadmin'] = 1;
	include('header.inc.php');
?>

<?php
	
	//**** parametri GET ******************************************************************
	if (isset($_GET['action'])){
		$action = $_GET['action'];
		
		switch ($action){
		
			// NEWUSER - form creazione nuovo utente
			case "newuser":
				
				//recupero dati temporanei 
				$username = "";
				$userlevel = 0;
				$email = "";
				if (isset($_SESSION['temp']['newuser'])){
					$username = $_SESSION['temp']['newuser']['username'];
					$userlevel = $_SESSION['temp']['newuser']['userlevel'];
					$email = $_SESSION['temp']['newuser']['email'];
					unset($_SESSION['temp']['newuser']);
				}
				
				?>
				<form action="admin.php" method="post">
				<fieldset>
					<legend>Creazione nuovo utente</legend>
					Nome utente:<br>
					<input type="text" name="username" class="title" value="<?php echo $username; ?>"><br>
					Email:<br>
					<input type="text" size=40 name="email" value="<?php echo $email; ?>"><br>
					Password:<br>
					<input type="text" name="pwd1"><br>
					Ripeti password:<br>
					<input type="text" name="pwd2"><br>				
					Livello utente:<br>
					<select name="userlevel">
						<option value="0" <?php if ($userlevel == 0) echo "selected" ?>>Utente normale</option>
						<option value="1" <?php if ($userlevel == 1) echo "selected" ?>>Admin</option>
					</select><br>
					<input type=hidden name="action" value="newuser">
					<input type=submit value="Salva">
				</fieldset>
				</form>
				<?php
				break;
			
			// EDITUSER - form modifica dati utente
			case "edituser":
			
				if (!isset($_GET['id'])) {
					err('Errore: passato GET->action = edituser senza ID');
					break;
				}
				
				$id = $_GET['id'];
				$user = User::first(
					array(
						'conditions' => array(
							'id = ?', $_GET['id']
						)
					)
				);
				
				if ($user == null){
					err('Errore: passato GET->action = edituser con ID non esistente');
					break;
				}
				
				?>
				<form action="admin.php" method="post">
				<fieldset>
					<legend>Modifica utente</legend>
					Nome utente:
					<strong><?php echo $user->username; ?></strong>	<br>
					Email:
					<input type="text" size=40 name="email" value="<?php echo $user->email; ?>"><br>
					Livello utente:
					<select name="userlevel">
						<option value="0" <?php if ($user->userlevel == 0) echo "selected" ?> >Utente normale</option>
						<option value="1" <?php if ($user->userlevel == 1) echo "selected" ?> >Admin</option>
					</select><br>
					<input type=hidden name="action" value="edituser">
					<input type=hidden name="id" value="<?php echo $id; ?>">
					<input type=submit value="Salva">					
				</fieldset>
				</form>
				
				<form action="admin.php" method="post">
				<fieldset>
					<legend>Modifica password</legend>

					Nuova password:
					<input type="text" size=40 name="pwd1"><br>
					Ripeti nuova password:
					<input type="text" size=40 name="pwd2"><br>
					
					<input type=hidden name="action" value="edituserpwd">
					<input type=hidden name="id" value="<?php echo $id; ?>">
					<input type=submit value="Modifica">					
				</fieldset>
				</form>
				
				<fieldset><legend>Informazioni utente</legend>
				Data creazione: <?php echo $user->created_at; ?><br>
				Ultima modifica: <?php echo $user->updated_at; ?><br><br>
				<hr>
				Conti:<br>
				<?php 
				foreach ($user->accounts as $account){
					echo $account->id.' - '.$account->description.'<br>';
				} 
				echo "</fieldset>";
				
				break;
				
			// LISTUSER - elenco utenti
			case "listusers":
			
				echo '<fieldset><legend>Elenco utenti</legend>';
				$users = User::all();
				echo '<table>';
				foreach($users as $user){
					echo '<tr>';
					echo '<td>'.$user->id.'</td>';
					echo '<td>'.$user->username.'</td>';
					echo '<td>'.$user->userlevel.'</td>';
					echo '<td>'.$user->email.'</td>';
					echo '<td><a href="admin.php?action=edituser&id='.$user->id.'">Modifica</a></td>';
					echo '</tr>';
				}
				echo '</table></fieldset>';
				
				break;
				
			// DEFAULT
			default:
				err('Errore: passato GET->action = '.$action);
				break;
				
		}//fine switch
		
	}//fine isset($_GET['action'])
	
	//**** parametri POST ****************************************************************
	else if (isset($_POST['action'])){
		$action = $_POST['action'];
		
		switch ($action){
		
			// NEWUSER - creazione nuovo utente
			case "newuser":
			
				if (!isset($_POST['username']) || 
					!isset($_POST['pwd1']) ||
					!isset($_POST['pwd2']) ||
					!isset($_POST['userlevel']) ||
					!isset($_POST['email']) ){
					
					err("Non sono stati passati tutti i parametri necessari.");
					break;
				}
				
				$newusername = $_POST['username'];
				$newpwd1 = $_POST['pwd1'];
				$newpwd2 = $_POST['pwd2'];
				$newuserlevel = $_POST['userlevel'];
				$newemail = $_POST['email'];
				
				//salvataggio temporaneo in caso di mancata validazione
				$_SESSION['temp']['newuser'] = array(
					'username' => $newusername,
					'userlevel' => $newuserlevel,
					'email' => $newemail
				);
				
				if (strcmp($newpwd1, $newpwd2) != 0) {
					errback('Errore: le password non coincidono');
					break;
				}	
				
				if (strcmp($newpwd1, "") == 0) {
					errback('Errore: la password non può essere nulla');
					break;
				}	
			
				$user = new User();
				$user->username = $newusername;
				$user->password = md5($newpwd1);
				$user->userlevel = $newuserlevel; 
				$user->email = $newemail;
		
				$result = $user->save();
				
				if ($result == false){
					$errors = '<ul class="error" style="padding:10px 10px 10px 20px;">';
					foreach ($user->errors as $msg)
						$errors .= '<li>'.$msg;
					$errors .= '</ul>';
					echo $errors;
				}
				else {
					conf('Nuovo utente '.$user->username.' creato correttamente');
					unset($_SESSION['temp']['newuser']);
				}
				
				break;
				
			// EDITUSER - modifica parametri utente
			case "edituser":
			
				if (!isset($_POST['id']) || 
					!isset($_POST['userlevel']) ||
					!isset($_POST['email']) ){
					
					err('Errore: passato POST->action = edituser senza parametri');
					break;
				}
				
				$newuserlevel = $_POST['userlevel'];	
				$newemail = $_POST['email'];
			
				$user = User::first(
					array(
						'conditions' => array(
							'id = ?', $_POST['id']
						)
					)
				);
				
				if ($user == null){
					err('Utente con ID '.$_POST['id'].' non trovato');
					break;
				}
					
				$user->userlevel = $newuserlevel; 
				$user->email = $newemail;
		
				$result = $user->save();
				
				if ($result == false){
					$errors = '<ul class="error" style="padding:10px 10px 10px 20px;">';
					foreach ($user->errors as $msg)
						$errors .= '<li>'.$msg;
					$errors .= '</ul>';
					echo $errors;
				}
				else conf('Utente '.$user->username.' aggiornato correttamente');
				
				break;	
			
			// EDITUSERPWD - modifica password utente
			case "edituserpwd":
				if (!isset($_POST['id']) || 
					!isset($_POST['pwd1']) ||
					!isset($_POST['pwd2']) ){
					
					err('Errore: passato POST->action = edituserpwd senza parametri');
					break;
				}
				
				$userid = $_POST['id'];
				$pwd1 = $_POST['pwd1'];
				$pwd2 = $_POST['pwd2'];
				
				if (strcmp($pwd1, $pwd2) != 0){
					err("Le due password inserite non coincidono.");
					break;
				}
				
				if (strcmp($pwd1, "") == 0){
					err("La nuova password non può essere nulla.");
					break;				
				}
			
				$user = User::first(
					array(
						'conditions' => array(
							'id = ?', $_POST['id']
						)
					)
				);
				
				if ($user == null){
					err('Utente con ID '.$_POST['id'].' non trovato');
					break;
				}
					
				$user->password = md5($pwd1);
		
				$result = $user->save();
				
				if ($result == false){
					$errors = '<ul class="error" style="padding:10px 10px 10px 20px;">';
					foreach ($user->errors as $msg)
						$errors .= '<li>'.$msg;
					$errors .= '</ul>';
					echo $errors;
				}
				else conf('Utente '.$user->username.' aggiornato correttamente');
				
				break;	
			
			// DEFAULT
			default:
				err('Errore: passato POST->action = '.$action);
				break;
			
		}//fine switch
		
	}//fine isset($_POST['action'])
	
	//**** default (menu admin) ***********************************************************
	else {
		?>
		L'amministratore è l'unico che capisca qualcosa di questo casino!
		<br><br>
		Funzioni di amministrazione:<br>
		<ul>
		<li><a href="admin.php?action=newuser">Nuovo utente</a>
		<li><a href="admin.php?action=listusers">Elenco utenti</a>
		</ul>		
		<?php
	}

?>

<?php
	include('footer.inc.php');
?>