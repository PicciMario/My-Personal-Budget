<?php
	$pagedata['pagetitle'] = "Conto";
	$pagedata['onlyadmin'] = 0;
	$pagedata['onlylogged'] = 1;
	include('header.inc.php');
?>


<?php
	function printAccount($account){
		if ($account == null) return;
		echo '<div class="accountList">';
		echo '<div class="accountListId">';
			echo '<a href="account.php?action=selectaccount&id='.$account->id.'">';
			echo '<img src="images/select.jpg"/>';
			echo '</a>';
		echo '</div>';
		echo '<div class="accountListDescr">';
			echo $account->description;
		echo '</div>';
		echo '<div class="accountListDescr2">';
			echo count($account->transactions)." voci inserite.";
		echo '</div>';
		echo '</div>';
	}
	
	function printTransaction($transaction){
		if ($transaction == null) return;
		$import = $transaction->import;
		echo '<div class="transaction ';
		if ($import >= 0) echo 'positive';
		if ($import < 0) echo 'negative';
		echo '">';
		echo '<div class="transactionId">';
			echo '<a href="account.php?action=selectaccount&id='.$transaction->id.'">';
			echo '<img src="images/select.jpg"/>';
			echo '</a>';
		echo '</div>';
		echo '<div class="transactionDescr">';
			echo $transaction->description;
		echo '</div>';
		echo '<div class="transactionValue">';
			printf("%01.2f", $import);
		echo '</div>';
		echo '</div>';
	}
	
	function printTotal($import){
		echo '<div class="transaction transactionTotal ';
		if ($import >= 0) echo 'positive';
		if ($import < 0) echo 'negative';
		echo '">';
		echo '<div class="transactionId">';
		echo '</div>';
		echo '<div class="transactionDescr">';
			echo 'Totale';
		echo '</div>';
		echo '<div class="transactionValue">';
			printf("%01.2f", $import);
		echo '</div>';
		echo '</div>';
	}
	
?>

<?php
	
	$showlist = 1;
	
	//**** parametri GET ******************************************************************

	if (isset($_GET['action'])){
		
		$action = $_GET['action'];
		$showlist = 0;
		
		switch ($action){
			
			// LISTACCOUNT - mostra la lista dei conti per l'utente corrente
			case "listaccount":
				
				if (!isset($_SESSION['userid'])) {
					err('Errore: nessuna informazione di login.');
					break;
				}
				
				$curaccount = -1;
				if (isset($_SESSION['accountid'])){
					$curaccount = $_SESSION['accountid'];
				}
				
				$user = User::first( 
					array(
						'conditions' => array(
							'id = ?', $_SESSION['userid']
						)
					)
				);
	
				if ($user == null){
					err('Errore: passato ID di utente inesistente.');
					break;
				}	
				
				echo '<fieldset style="width:80%"><legend>Selezione conto</legend>';
				foreach ($user->accounts as $account) {
					echo printAccount($account);
				}
				echo '</fieldset>';
	
				break;
			
			// SELECTACCOUNT - seleziona un conto per l'utente corrente
			case "selectaccount":
				
				if (!isset($_SESSION['userid'])) {
					err('Errore: nessuna informazione di login.');
					break;
				}
				
				if (!isset($_GET['id'])) {
					err('Errore: non passato ID conto.');
					break;
				}				
				
				$user = User::first( 
					array(
						'conditions' => array(
							'id = ?', $_SESSION['userid']
						)
					)
				);
	
				if ($user == null){
					err('Errore: passato ID di utente inesistente.');
					break;
				}
				
				$conto = Account::first(
					array(
						'conditions' => array(
							'user_id = ? AND id = ?', $_SESSION['userid'], $_GET['id']
						)
					)				
				);
				
				if ($conto == null) {
					err("Impossibile attivare il conto indicato");
					break;
				}
				else {
					$_SESSION['accountid'] = $conto->id;
					//conf('Selezionato conto: '.$conto->description);
					$showlist = 1;
				}
				
				break;
			
			default:
				err("Passato parametro action sconosciuto: ".$_GET['action']);
				break;
			
		}//fine switch $action
			
	}//fine isset($_GET['action'])

	//**** parametri POST ******************************************************************

	if (isset($_POST['action'])){
		
		$action = $_POST['action'];
		$showlist = 1;
		
		switch ($action){
			
			// LISTACCOUNT - mostra la lista dei conti per l'utente corrente
			case "newtransaction":
			
				if (!isset($_POST['description']) || 
					!isset($_POST['import']) ){
					
					err("Non sono stati passati tutti i parametri necessari.");
					break;
				}
				
				$newdescription = $_POST['description'];
				$newimport = $_POST['import'];
				
				//salvataggio temporaneo in caso di mancata validazione
				$_SESSION['temp']['newtransaction'] = array(
					'description' => $newdescription,
					'import' => $newimport
				);	
				
				if (!is_numeric($newimport)){
					err("L'importo deve essere numerico!");
					break;
				}
			
				$transaction = new Transaction();
				$transaction->description = $newdescription;
				$transaction->import = $newimport;
				$transaction->account_id = $_SESSION['accountid'];
		
				$result = $transaction->save();
				
				if ($result == false){
					$errors = '<ul class="error" style="padding:10px 10px 10px 20px;">';
					foreach ($transaction->errors as $msg)
						$errors .= '<li>'.$msg;
					$errors .= '</ul>';
					echo $errors;
				}
				else {
					conf('Nuova transazione '.$transaction->description.' creato correttamente');
					unset($_SESSION['temp']['newtransaction']);
				}
				
				break;

			default:
				err("Passato parametro action sconosciuto: ".$_POST['action']);
				break;

		}//fine switch $action
			
	}//fine isset($_POST['action'])
	
	
	//**** corpo della pagina ***************************************************************
	
	if ($showlist == 1){
		echo '<p><a href="account.php?action=listaccount">Seleziona conto</a></p>';
		
		if (!isset($_SESSION['userid'])){
			err("Utente non valido");
		}
		else{
			
			//individuo utente
			$user = User::first( 
				array(
					'conditions' => array(
						'id = ?', $_SESSION['userid']
					)
				)
			);
			
			//se utente non valido interrompo
			if ($user == null){
				err('Errore: passato ID di utente inesistente.');
				break;
			}
			
			//individuo conto
			//per sicurezza verifico che sia collegato all'utente
			$conto = Account::first(
				array(
					'conditions' => array(
						'user_id = ? AND id = ?', $_SESSION['userid'], $_SESSION['accountid']
					)
				)				
			);
			
			//se trovo il conto mostro l'elenco
			if ($conto == null) {
				err("Impossibile attivare il conto indicato");
			}
			else {
				
				//recupero cache da validazione
				$description = "";
				$import = 0;
				if (isset($_SESSION['temp']['newtransaction'])){
					$description = $_SESSION['temp']['newtransaction']['description'];
					$import = $_SESSION['temp']['newtransaction']['import'];
					unset($_SESSION['temp']['newuser']);
					echo '<script>$(document).ready(function() {$(\'div#addTransactionForm\').show();})</script>';
				}
				?>
				
				<div id="addTransaction">
				  	Aggiungi transazione
				</div>
				
				<div id="addTransactionForm" style="display:none">
					<form action="account.php" method="post">
					<fieldset style="width:80%">
						<legend>Aggiungi Transazione</legend>
						Descrizione:<br>
						<input type="text" size=60 name="description" value="<?php echo $description; ?>"><br>
						Importo:<br>
						<input type="text" name="import" value="<?php echo $import; ?>"><br>
						<input type=hidden name="action" value="newtransaction">
						<input type=submit value="Salva">
						<input type=button id="closeTransactionForm" value="Annulla">
	
					</fieldset>
					</form>
				</div>
				
				
				<script>
				$('#addTransaction').click(function() {
				  $('#addTransactionForm').show('slow', function() {
				    // Animation complete.
				  });
				});
				$('#closeTransactionForm').click(function() {
				  $('#addTransactionForm').hide('slow', function() {
				    // Animation complete.
				  });
				});				
				
				</script>

				<?php
				
				// Stampa transazioni conto attuale
				
				echo '<fieldset style="width:80%"><legend>'.$conto->description.'</legend>';
				$totale = 0;
				foreach ($conto->transactions as $transaction){
					printTransaction($transaction);
					$totale += $transaction->import;
				}
				
				// Stampa totale conto attuale
				printTotal($totale);
				echo '</fieldset>';
				
			}

		}
	}


?>

<?php
	include('footer.inc.php');
?>