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
		echo '<div class="accountListIcon1">';
			echo '<a href="account.php?action=selectaccount&id='.$account->id.'">';
			echo '<img src="images/select.jpg"/>';
			echo '</a>';
		echo '</div>';
		echo '<div class="accountListIcon2">';
			echo '<a href="account.php?action=selectaccount&id='.$account->id.'">';
			echo '<img src="images/select.jpg"/>';
			echo '</a>';
		echo '</div>';		
		echo '<div class="accountListDescr">';
			echo $account->description;
		echo '</div>';
		echo '<div class="accountListDescr2">';
			echo count($account->transactions)." voci.";
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
				echo '<img src="images/downTriangle.png" onclick="mostraDiv(\'transNote'.$transaction->id.'\')"/>';
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
		
		echo '<div id="transNote'.$transaction->id.'" style="display:none;" class="transactionNote note">';
		echo nl2br(htmlspecialchars($transaction->note));
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
						'conditions' => array('id = ?', $_SESSION['userid'])
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
			
				//parametri passati da POST
				//importante: i nomi devono essere uguali a quelli passati da
				//form, i quali a loro volta devono essere uguali ai nomi dei
				//record della tabella interessata!
				$parametri = array(
					'description',
					'import',
					'category_id',
					'date',
					'note'
				);
				
				//verifica presenza e acquisizione parametri
				$newValue = array();
				foreach ($parametri as $parametro){
					if (!isset($_POST[$parametro])){
						err('Non sono stati passati tutti i parametri necessari (manca '.$parametro.').');
						break;					
					}else{
						$newValue[$parametro] = $_POST[$parametro];
					}
				}
				
				//salvataggio temporaneo in caso di mancata validazione
				$_SESSION['temp']['newtransaction'] = $newValue;
				
				//controllo parametri
				if (!is_numeric($newValue['import'])){
					err("L'importo deve essere numerico!");
					break;
				}
				
				//impostazione parametri mancanti (non passati da POST)
				$newValue['account_id'] = $_SESSION['accountid'];
				
				//creazione nuovo oggetto e salvataggio
				$transaction = new Transaction($newValue);
				$result = $transaction->save();
				
				//verifica salvataggio
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
	
	switch ($showlist){
		
		case 1:
		
		?>
		<div class="toolbar">
			<a href="account.php?action=listaccount" class="toolbarButton">Lista conti</a>
			<a href="#" class="toolbarButton" id="addTransaction">Nuova voce</a>
		</div>	
		<?php
		
		if (!isset($_SESSION['userid'])){
			err("Utente non valido");
			break;
		}
			
		//individuo utente
		$user = User::first( 
			array(
				'conditions' => array('id = ?', $_SESSION['userid'])
			)
		);
		
		//se utente non valido interrompo
		if ($user == null){
			err('Errore: passato ID di utente inesistente.');
			break;
		}
		
		//se nessun conto selezionato
		if (!isset($_SESSION['accountid'])){
			err('Nessun conto selezionato');
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
			break;
		}
			
		//recupero cache da validazione
		$description = "";
		$import = 0;
		$date = "";
		$category = 0;
		$note = "";
		if (isset($_SESSION['temp']['newtransaction'])){
			$description = $_SESSION['temp']['newtransaction']['description'];
			$import = $_SESSION['temp']['newtransaction']['import'];
			$category = $_SESSION['temp']['newtransaction']['category_id'];
			$date = $_SESSION['temp']['newtransaction']['date'];
			$note = $_SESSION['temp']['newtransaction']['note'];
			unset($_SESSION['temp']['newtransaction']);
			echo '<script>$(document).ready(function() {$(\'div#addTransactionForm\').show();})</script>';
		}
		
		//recupero elenco categorie per l'utente				
		$categories = Category::find(
			'all',
			array('conditions' => array('user_id = ?', $user->id))				
		);		
		
		?>
		
		<div id="addTransactionForm" style="display:none">
			<form action="account.php" method="post">
			<fieldset>
				<legend>Aggiungi Transazione</legend>
				
				Descrizione:<br>
				<input type="text" size=60 name="description" value="<?php echo $description; ?>"><br>
				
				Categoria:<br>
				<select name="category_id">
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
				<textarea style="max-height:100px;" name="note"><?php echo $note; ?></textarea><br>
				
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
		
		$datemin = date("Y-m-d", mktime(0, 0, 0, $month, 1, $year));
		$datemax = date("Y-m-d", mktime(0, 0, 0, $month+1, 0, $year));

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
		
		//calcola mese precedente
		$prevyear = $year;
		$prevmonth = $month - 1;
		if ($prevmonth == 0){
			$prevmonth = 12;
			$prevyear = $prevyear - 1;
		}
		
		//calcola mese successivo
		$nextyear = $year;
		$nextmonth = $month + 1;
		if ($nextmonth > 12){
			$nextmonth = 1;
			$nextyear = $nextyear + 1;
		}
		
		//barra di cambio mese
		echo '<fieldset><legend>'.$conto->description.' - '.$month.'/'.$year.'</legend>';
		echo '<div class="toolbar">';
		echo '<div align=center>';
		echo '<a href="account.php?year='.$prevyear.'&month='.$prevmonth.'" class="toolbarButton">&lt;&lt;&lt;</a>';
		echo '<a href="account.php" class="toolbarButton">Oggi</a>';
		echo '<a href="account.php?year='.$nextyear.'&month='.$nextmonth.'" class="toolbarButton">&gt;&gt;&gt;</a>';
		echo '</div>';
		echo '</div><hr>';
	
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

?>

<?php
	include('footer.inc.php');
?>