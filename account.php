<?php
	$pagedata['pagetitle'] = "Gestione conti";
	$pagedata['onlyadmin'] = 0;
	$pagedata['onlylogged'] = 1;
	include('header.inc.php');
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
				
				?>
				

				<div class="toolbar">
					<a href="#" class="toolbarButton" id="addTransaction" onclick="mostraDivSlow('addAccountForm')">Nuovo conto</a>
				</div>	
				
				<div id="addAccountForm" style="display:none">
					<form action="account.php" method="post">
					<fieldset>
						<legend>Aggiungi Conto</legend>
						
						Descrizione:<br>
						<input type="text" size=60 name="description"><br>
						
						<input type=hidden name="action" value="newaccount">
						
						<input type=submit value="Salva">
						<input type=button id="closeAccountForm" onclick="mostraDivSlow('addAccountForm')" value="Annulla">
		
					</fieldset>
					</form>
				</div>
				
				<?php
				
				//stampa lista conti
				echo '<fieldset><legend>Selezione conto</legend>';
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
				
			// DELETEACCOUNT - elimina un conto
			case "deleteaccount":
				
				//verifica che un utente sia loggato
				if (!isset($_SESSION['userid'])) {
					err('Errore: nessuna informazione di login.');
					break;
				}
				
				//verifica che sia stato passato il codice conto da eliminare
				if (!isset($_GET['accountid'])){
					err('Errore: nessun conto selezionato per la cancellazione.');
					break;
				}
				$accountid = $_GET['accountid'];
				
				//individua l'utente loggato
				$user = User::first( 
					array(
						'conditions' => array('id = ?', $_SESSION['userid'])
					)
				);
				
				//blocca se l'utente loggato non è valido
				if ($user == null){
					err('Errore: passato ID di utente inesistente.');
					break;
				}	
				
				//individua il conto richiesto
				$account = Account::first( 
					array(
						'conditions' => array('id = ? AND user_id = ?', $accountid, $user->id)
					)
				);
				
				//blocca se il conto non è stato individuato per l'utente
				if ($account == null){
					err('Impossibile selezionare il conto indicato');
					break;
				}

				if (isset($_GET['confirm'])){
					foreach($account->transactions as $transaction)
						$transaction->delete();
					$account->delete();
					conf('confermata cancellazione del conto');
					if ($_SESSION['accountid'] == $account->id)
						unset($_SESSION['accountid']);
					break;
				}
				else{
					$mess = 'Procedo a eliminare il conto "'.$account->description;
					$mess .= '" e le sue '.count($account->transactions).' voci.<br>';
					$mess .= '<a href="account.php?action=deleteaccount&accountid='.$account->id.'&confirm">';
					$mess .= 'clicca per confermare';
					$mess .= '</a>';
					notice($mess);	
					break;
				}
	
				break;
						
			// DELETETRANSACTION - elimina una transazione
			case "deletetransaction":
				
				//verifica che un utente sia loggato
				if (!isset($_SESSION['userid'])) {
					err('Errore: nessuna informazione di login.');
					break;
				}
				
				//verifica che sia stato passato il codice transazione da eliminare
				if (!isset($_GET['transactionid'])){
					err('Errore: nessuna voce selezionata per la cancellazione.');
					break;
				}
				$transactionid = $_GET['transactionid'];
				
				//individua l'utente loggato
				$user = User::first( 
					array(
						'conditions' => array('id = ?', $_SESSION['userid'])
					)
				);
				
				//blocca se l'utente loggato non è valido
				if ($user == null){
					err('Errore: passato ID di utente inesistente.');
					break;
				}	
				
				//individua la voce richiesta
				$transaction = Transaction::first( 
					array(
						'conditions' => array('id = ?', $transactionid)
					)
				);
				
				//conto cui appartiene la voce
				$account = $transaction->account;
				
				//verifico che la voce appartenga a un conto dell'utente loggato
				if ($account->user_id != $user->id){
					err("la voce non appartiene all'utente loggato.");
					break;
				}

				if (isset($_GET['confirm'])){
					$transaction->delete();
					conf('confermata cancellazione voce');
				}
				else{
					$mess = 'Procedo a eliminare la voce "'.$transaction->description.'"<br>';
					$mess .= '<a href="account.php?action=deletetransaction&transactionid='.$transaction->id.'&confirm">';
					$mess .= 'clicca per confermare';
					$mess .= '</a>';
					notice($mess);	
					break;
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
				if (!isset($_SESSION['accountid'])){
					err('Nessun conto selezionato');
					break;
				}
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
				
			// NEWACCOUNT - crea nuovo conto
			case "newaccount":
			
				//parametri passati da POST
				//importante: i nomi devono essere uguali a quelli passati da
				//form, i quali a loro volta devono essere uguali ai nomi dei
				//record della tabella interessata!
				$parametri = array(
					'description'
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
				$_SESSION['temp']['newaccount'] = $newValue;
				
				//impostazione parametri mancanti (non passati da POST)
				if (!isset($_SESSION['userid'])){
					error('Parametro user id non impostato');
					break;
				}
				$newValue['user_id'] = $_SESSION['userid'];
				
				//creazione nuovo oggetto e salvataggio
				$account = new Account($newValue);
				$result = $account->save();
				
				//verifica salvataggio
				if ($result == false){
					$errors = '<ul class="error" style="padding:10px 10px 10px 20px;">';
					foreach ($account->errors as $msg)
						$errors .= '<li>'.$msg;
					$errors .= '</ul>';
					echo $errors;
				}
				else {
					conf('Nuovo conto "'.$account->description.'" creata correttamente');

					//individua l'ultimo conto creato e lo seleziona
					$account = Account::last( 
						array(
							'conditions' => array(
								'user_id = ?', $_SESSION['userid']
							)
						)
					);
					if ($account != null) $_SESSION['accountid'] = $account->id;
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
			<a href="#" onclick="mostraDivSlow('addTransactionForm')" class="toolbarButton" id="addTransaction">Nuova voce</a>
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
		
		//individuo conto
		//per sicurezza verifico che sia collegato all'utente
		//se nessun conto selezionato attivo il primo disponibile per l'utente
		if (!isset($_SESSION['accountid'])){
			$conto = Account::first(
				array(
					'conditions' => array(
						'user_id = ?', $_SESSION['userid']
					)
				)				
			);
		}
		else{
			$conto = Account::first(
				array(
					'conditions' => array(
						'user_id = ? AND id = ?', $_SESSION['userid'], $_SESSION['accountid']
					)
				)				
			);
		}
		
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
				<input type=button id="closeTransactionForm" onclick="mostraDivSlow('addTransactionForm')" value="Annulla">

			</fieldset>
			</form>
		</div>
		
		<script>
		$(function() {
			$( "#datepicker" ).datepicker();
			$( "#datepicker" ).datepicker( "option", "dateFormat", "d-m-yy" );
		});			
		</script>

		<?php
		
		// Anno e mese attuali
		$year = date('Y');
		$month = date('m');
		
		// Anno e mese se passati da POST
		if (isset($_GET['year']) && isset($_GET['month'])){
			if (is_numeric($_GET['year']) && is_numeric($_GET['month'])){
				$year = $_GET['year'];
				$month = $_GET['month'];
				if ($month > 12) $month = 12;
				if ($month <= 0) $month = 1;
			}
		}

		// composizione delle date del filtro del mese attuale
		$datemin = date("Y-m-d", mktime(0, 0, 0, $month, 1, $year));
		$datemax = date("Y-m-d", mktime(0, 0, 0, $month+1, 0, $year));

		// ricerca transazioni precedenti al mese selezionato
		$prevTransactions = Transaction::find(
			'all',
			array(
				'conditions' => array('account_id = ? AND date < ?', $conto->id, $datemin),
				'order' => 'date asc'
			)
		);
		
		// calcolo saldo a fine mese precedente
		$prevTotale = 0;
		foreach ($prevTransactions as $prevTransaction){
			$prevTotale += $prevTransaction->import;
		}
		
		// ricerca transazioni mese corrente
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
		
		echo '<fieldset><legend>'.$conto->description.' - '.$month.'/'.$year.'</legend>';
		
		//barra di cambio mese
		echo '<div class="toolbar">';
		echo '<div align=center>';
		echo '<a href="account.php?year='.$prevyear.'&month='.$prevmonth.'" class="toolbarButton">&lt;&lt;&lt;</a>';
		echo '<a href="account.php" class="toolbarButton">Oggi</a>';
		echo '<a href="#" class="toolbarButton" onclick="mostraDiv(\'selectPeriod\')">Scegli...</a>';
		echo '<a href="account.php?year='.$nextyear.'&month='.$nextmonth.'" class="toolbarButton">&gt;&gt;&gt;</a>';
		echo '</div>';
		echo '</div>';
		?>
		<div align="center" id="selectPeriod" style="display:none;">
		<form action="account.php" method="GET" class="inline">
		Anno: 
		<select name="year">
			<?php
			for ($i = 2000; $i < 2020; $i++){
				echo '<option value="'.$i.'"';
				if (date('Y') == $i) echo ' selected';
				echo '>'.$i.'</option>';
			}
			?>
		</select>
		Mese: 
		<select name="month">
			<option value="01" <?php if (date('m') == 1) echo ' selected ' ?> >Gennaio</option>
			<option value="02" <?php if (date('m') == 2) echo ' selected ' ?> >Febbraio</option>
			<option value="03" <?php if (date('m') == 3) echo ' selected ' ?> >Marzo</option>
			<option value="04" <?php if (date('m') == 4) echo ' selected ' ?> >Aprile</option>
			<option value="05" <?php if (date('m') == 5) echo ' selected ' ?> >Maggio</option>
			<option value="06" <?php if (date('m') == 6) echo ' selected ' ?> >Giugno</option>
			<option value="07" <?php if (date('m') == 7) echo ' selected ' ?> >Luglio</option>
			<option value="08" <?php if (date('m') == 8) echo ' selected ' ?> >Agosto</option>
			<option value="09" <?php if (date('m') == 9) echo ' selected ' ?> >Settembre</option>
			<option value="10" <?php if (date('m') == 10) echo ' selected ' ?> >Ottobre</option>
			<option value="11" <?php if (date('m') == 11) echo ' selected ' ?> >Novembre</option>
			<option value="12" <?php if (date('m') == 12) echo ' selected ' ?> >Dicembre</option>
		</select>
		<input type=submit value="Vai">
		</form>	
		</div>
		<?php
		
		echo '<hr>';
	
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