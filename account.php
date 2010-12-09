<?php
	$pagedata['pagetitle'] = "Conto";
	$pagedata['onlyadmin'] = 0;
	$pagedata['onlylogged'] = 1;
	include('header.inc.php');
?>

<script>
function mostraDiv(divname){
	$("#"+divname).toggle();
}
</script>

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
			if (strcmp($transaction->note, "") != 0){
				echo '<img src="images/select.jpg" onclick="mostraDiv(\'transNote'.$transaction->id.'\')"/>';
			}
		echo '</div>';
		echo '<div class="transactionDate">';
			echo $transaction->date->format("d/m/y");
		echo '</div>';
		echo '<div class="transactionDescr">';
			echo $transaction->description;
		echo '</div>';
		echo '<div class="transactionCat">';
			echo $transaction->category->name;
		echo '</div>';
		echo '<div class="transactionValue">';
			printf("%01.2f €", $import);
		echo '</div>';
		echo '</div>';
		
		echo '<div id="transNote'.$transaction->id.'" style="display:none;">';
		echo $transaction->note;
		echo '</div>';
	}
	
	function printTotal($text, $import){
		echo '<div class="transaction transactionTotal ';
		if ($import >= 0) echo 'positive';
		if ($import < 0) echo 'negative';
		echo '">';
		echo '<div class="transactionId">';
		echo '</div>';
		echo '<div class="transactionDescr">';
			echo $text;
		echo '</div>';
		echo '<div class="transactionValue">';
			printf("%01.2f €", $import);
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
			
			// NEWTRANSACTION - crea nuova transazione
			case "newtransaction":
			
				if (!isset($_POST['description']) || 
					!isset($_POST['import']) ||
					!isset($_POST['category']) ||
					!isset($_POST['date']) ||
					!isset($_POST['note'])){
					
					err("Non sono stati passati tutti i parametri necessari.");
					break;
				}
				
				$newdescription = $_POST['description'];
				$newimport = $_POST['import'];
				$newcategory = $_POST['category'];
				$newdate = $_POST['date'];
				$newnote = $_POST['note'];
				
				//salvataggio temporaneo in caso di mancata validazione
				$_SESSION['temp']['newtransaction'] = array(
					'description' => $newdescription,
					'import' => $newimport,
					'category' => $newcategory,
					'date' => $newdate,
					'note' => $newnote
				);
				
				if (!is_numeric($newimport)){
					err("L'importo deve essere numerico!");
					break;
				}
			
				$transaction = new Transaction();
				$transaction->description = $newdescription;
				$transaction->import = $newimport;
				$transaction->account_id = $_SESSION['accountid'];
				$transaction->category_id = $newcategory;
				$transaction->date = $newdate;
				$transaction->note = $newnote;
		
				$result = $transaction->save();
				
				if ($result == false){
					$errors = '<ul class="error" style="padding:10px 10px 10px 20px;">';
					foreach ($transaction->errors as $msg)
						$errors .= '<li>'.$msg;
					$errors .= '</ul>';
					echo $errors;
				}
				else {
					conf('Nuova transazione "'.$transaction->description.'" creata correttamente');
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
		?>
		
		<div class="toolbar">

			<a href="account.php?action=listaccount" class="toolbarButton">Cambia conto</a>
			
			<div id="addTransaction" class="toolbarButton">
			  	Nuova voce
			</div>
		
		</div>
		
		<?php
		
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
				$date = "";
				$category = 0;
				$note = "";
				if (isset($_SESSION['temp']['newtransaction'])){
					$description = $_SESSION['temp']['newtransaction']['description'];
					$import = $_SESSION['temp']['newtransaction']['import'];
					$category = $_SESSION['temp']['newtransaction']['category'];
					$date = $_SESSION['temp']['newtransaction']['date'];
					$note = $_SESSION['temp']['newtransaction']['note'];
					unset($_SESSION['temp']['newtransaction']);
					echo '<script>$(document).ready(function() {$(\'div#addTransactionForm\').show();})</script>';
				}
				
				//recupero elenco categorie per l'utente				
				$categories = Category::find(
					'all',
					array(
						'conditions' => array(
							'user_id = ?', $user->id
						)
					)				
				);		
				
				?>
				
				<div id="addTransactionForm" style="display:none">
					<form action="account.php" method="post">
					<fieldset style="width:80%">
						<legend>Aggiungi Transazione</legend>
						
						Descrizione:<br>
						<input type="text" size=60 name="description" value="<?php echo $description; ?>"><br>
						
						Categoria:<br>
						<select name="category">
							<?php
							foreach($categories as $category){
								echo '<option value="'.$category->id.'">'.$category->name.'</option>';
								echo $category->name;
							}
							?>
						</select><br>
						
						Importo:<br>
						<input type="text" name="import" value="<?php echo $import; ?>"><br>
						
						Data:<br>
						<input type="text" id="datepicker" name="date" value="<?php echo $date; ?>"><br>

						Note:<br>
						<textarea rows=4 cols=4 name="note"><?php echo $note; ?></textarea><br>
						
						<input type=hidden name="action" value="newtransaction">
						
						<input type=submit value="Salva">
						<input type=button id="closeTransactionForm" value="Annulla">
	
					</fieldset>
					</form>
				</div>
				
				<script>
				$(function() {
					$( "#datepicker" ).datepicker();
					$( "#datepicker" ).datepicker( "option", "dateFormat", "d-m-yy" );
				});

				$('#addTransaction').click(function() {
				  $('#addTransactionForm').toggle('slow', function() {
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
				$year = date('Y');
				$month = date('m');
				
				if (isset($_GET['year']) && isset($_GET['month'])){
					$year = $_GET['year'];
					$month = $_GET['month'];
				}
				
				$datemin = $year.'-'.$month.'-00';
				$datemax = $year.'-'.$month.'-99';

				$prevTransactions = Transaction::find(
					'all',
					array(
						'conditions' => array('account_id = ? AND date < ?', $conto->id, $datemin),
						'order' => 'date asc'
					)
				);
				
				$prevTotale = 0;
				foreach ($prevTransactions as $prevTransaction){
					$prevTotale += $prevTransaction->import;
				}
				
				$transactions = Transaction::find(
					'all',
					array(
						'conditions' => array('account_id = ? AND date >= ? and DATE <= ?', $conto->id, $datemin, $datemax),
						'order' => 'date asc'
					)
				);
				
				echo '<fieldset style="width:80%"><legend>'.$conto->description.' - '.$month.'/'.$year.'</legend>';

				// Stampa storico
				printTotal("Saldo a inizio mese", $prevTotale);

				$totale = $prevTotale;
				foreach ($transactions as $transaction){
					printTransaction($transaction);
					$totale += $transaction->import;
				}
				
				// Stampa totale conto attuale
				printTotal("Saldo a fine mese", $totale);
				
				echo '</fieldset>';
				
			}

		}
	}


?>

<?php
	include('footer.inc.php');
?>