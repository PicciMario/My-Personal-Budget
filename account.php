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
					<a href="#" class="toolbarButtonNew" id="addTransaction" onclick="mostraDivSlow('addAccountForm')">Nuovo conto</a>
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
				$saldoComplessivo = 0;
				
				echo '<fieldset><legend>Selezione conto</legend>';
				foreach ($user->accounts as $account) {
					
					//calcolo il saldo corrente
					
					//data e mese corrente
					$month = date('m');
					$year = date('Y');

					//saldo mese precedente
					$saldo = 0;
					$prevMonthStart = date("Y-m-d", mktime(0, 0, 0, $month-1, 1, $year));
					$prevMonthEnd = date("Y-m-d", mktime(0, 0, 0, $month, 1, $year));
					$saldoMesePrec = Transaction::first(
						array(
							'conditions' => array('account_id = ? AND date >= ? AND date < ? AND auto = ? AND category_id = ?', 
								$account->id, 
								$prevMonthStart,
								$prevMonthEnd, 
								1,
								0
							),
							'order' => 'date desc'
						)
					);
					if ($saldoMesePrec != null){
						$saldo = $saldoMesePrec->import;
						debug('Recuperato saldo mese precedente da chiusura: '.$saldo);
					} else{
						notice('Il mese precedente risulta aperto, rigenerato lo storico');
						//totale movimenti precedenti al mese
						$precTrans = Transaction::find(
							'all',
							array(
								'select' => 'sum(import) as sum_imports',
								'conditions' => array('account_id = ? AND date < ? AND auto = ?', 
									$account->id, 
									$prevMonthEnd, 
									0
								),
							)
						);
						if ($precTrans != null){
							$saldo = $precTrans[0]->sum_imports;
							debug('Recuperato saldo mese precedente mediante ricostruzione: '.$saldo);
						}else{
							debug('Nulla da ricostruire');
						}
					}

					//al saldo MP aggiungo i movimenti MC fino a oggi
					$actualTrans = Transaction::find(
						'all',
						array(
							'select' => 'sum(import) as sum_imports',
							'conditions' => array('account_id = ? AND date >= ? AND date < ? AND auto = ?', 
								$account->id,
								date("Y-m-d", mktime(0, 0, 0, $month, 1, $year)),
								date("Y-m-d", mktime(0, 0, 0, date('m'), date('d')+1, date('Y'))),
								0
							),
						)
					);
					if ($actualTrans != null){
						$saldo += $actualTrans[0]->sum_imports;
						debug("recuperati movimenti MC fino a oggi: ".$actualTrans[0]->sum_imports);
					}
					
					//stampo il conto
					echo printAccount($account, $saldo);
					$saldoComplessivo += $saldo;
				}
				printTotal("Saldo complessivo a oggi:", $saldoComplessivo, 2);
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
					foreach($account->transactions as $transaction){
						//elimina collegamenti con tags
						$transactiontags = Transactiontag::find(
							'all',
							array(
								'conditions' => array('transaction_id = ?', $transaction->id)
							)
						);
						foreach ($transactiontags as $transactiontag){
							$transactiontag->delete();
						}
						//elimina la transazione
						$transaction->delete();
					}
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
				
				//analizzo il mese per vedere se è stato chiuso
				$year = $transaction->date->format('Y');
				$month = $transaction->date->format('m');
				debug("New transaction date - Year: $year - Month: $month");
				$datemin = date("Y-m-d", mktime(0, 0, 0, $month, 1, $year));
				$datemax = date("Y-m-d", mktime(0, 0, 0, $month+1, 1, $year));				
				$cercaChiusura = Transaction::first(
					array(
						'conditions' => array(
							'account_id = ? AND date >= ? AND date < ? AND auto = ? and category_id = ?', 
							$account->id, 
							$datemin, 
							$datemax,
							1, 	//chiusura mese
							0	//no categoria
						),
						'order' => 'date asc'
					)
				);
				if ($cercaChiusura != null){
					notice("Impossibile eliminare nuova voce: mese chiuso a consuntivo!");
					break;
				}
				
				//procedo alla cancellazione
				if (isset($_GET['confirm'])){
					
					//elimina collegamenti con tags
					$transactiontags = Transactiontag::find(
						'all',
						array(
							'conditions' => array('transaction_id = ?', $transaction->id)
						)
					);
					foreach ($transactiontags as $transactiontag) 
						$transactiontag->delete();
					
					$transaction->delete();
					conf('confermata cancellazione voce');
				}
				else{
					$mess = 'Procedo a eliminare la voce "'.$transaction->description.'"<br>';
					$mess .= '<a href="account.php?action=deletetransaction&transactionid='.$transaction->id.'&confirm"';
					$mess .= ' class="toolbarButtonDelete">';
					$mess .= 'clicca per confermare';
					$mess .= '</a>';
					notice($mess);	
					break;
				}
	
				break;
				
			// CLOSEMONTH - chiude il mese
			case "closemonth":

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

				//lettura conto da variabile sessione
				if (!isset($_SESSION['accountid'])){
					err('Nessun conto selezionato');
					break;
				}
				$accountid = $_SESSION['accountid'];

				//individua il conto richiesto
				$account = Account::first( 
					array(
						'conditions' => array('id = ? AND user_id = ?', $accountid, $user->id)
					)
				);

				//se conto non valido interrompo
				if ($account == null){
					err('Errore: passato ID di conto inesistente.');
					break;
				}
				
				//verifica presenza e acquisizione parametri da GET
				$parametri = array(
					'year',
					'month'
				);
				foreach ($parametri as $parametro){
					if (!isset($_GET[$parametro])){
						err('Non sono stati passati tutti i parametri necessari (manca '.$parametro.').');
						break;
					}				
				}			
				$month = $_GET['month'];
				$year = $_GET['year'];
				
				//calcolo date massima e minima per ricerca
				$datemin = date("Y-m-d", mktime(0, 0, 0, $month, 1, $year));
				$datemax = date("Y-m-d", mktime(0, 0, 0, $month+1, 1, $year));
				debug('Date min: '.$datemin.' - Date max: '.$datemax);
				
				//elimino precedenti voci auto=1 nel mese
				$precChiusure = Transaction::find(
					'all',
					array(
						'conditions' => array('account_id = ? AND date >= ? AND date < ? AND auto = ?', 
						//'conditions' => array('auto = ?',
							$account->id, 
							$datemin, 
							$datemax,
							1
						),
					)
				);
				debug('Eliminate '.count($precChiusure).' chiusure');
				foreach($precChiusure as $transaction){
					$transaction->delete();
				}
				
				//saldo mese precedente
				$saldo = 0;
				$prevMonthStart = date("Y-m-d", mktime(0, 0, 0, $month-1, 1, $year));
				$prevMonthEnd = date("Y-m-d", mktime(0, 0, 0, $month, 1, $year));
				$saldoMesePrec = Transaction::first(
					array(
						'conditions' => array('account_id = ? AND date >= ? AND date < ? AND auto = ? AND category_id = ?', 
							$account->id, 
							$prevMonthStart,
							$prevMonthEnd, 
							1,
							0
						),
						'order' => 'date desc'
					)
				);
				if ($saldoMesePrec != null){
					$saldo = $saldoMesePrec->import;
					debug('Recuperato saldo mese precedente da chiusura: '.$saldo);
				} else{
					notice('Il mese precedente risulta aperto, rigenerato lo storico');
					//totale movimenti precedenti al mese
					$precTrans = Transaction::find(
						'all',
						array(
							'select' => 'sum(import) as sum_imports',
							'conditions' => array('account_id = ? AND date < ? AND auto = ?', 
								$account->id, 
								$datemin, 
								0
							),
						)
					);
					if ($precTrans != null){
						$saldo = $precTrans[0]->sum_imports;
						debug('Recuperato saldo mese precedente mediante ricostruzione: '.$saldo);
					}else{
						debug('Nulla da ricostruire');
					}
				}

				//totale movimenti nel mese
				$monthTrans = Transaction::find(
					'all',
					array(
						'select' => 'sum(import) as sum_imports',
						'conditions' => array('account_id = ? AND date >= ? AND date < ? AND auto = ?', 
							$account->id, 
							$datemin, 
							$datemax,
							0
						),
					)
				);
				
				if ($monthTrans != null){
					debug('Movimenti mese corrente: '.$monthTrans[0]->sum_imports);
					$saldo += $monthTrans[0]->sum_imports;
				}

				//creazione nuovo oggetto e salvataggio
				$transaction = new Transaction(
					array(
						'description' => 'chiusura mese',
						'account_id' => $account->id,
						'category_id' => 0,
						'date' => fineMese($month, $year),
						'import' => $saldo,
						'auto' => 1
					)
				);
				$result = $transaction->save();
				
				//verifica salvataggio
				if ($result == false){
					$errors = '<ul class="error" style="padding:10px 10px 10px 20px;">';
					foreach ($transaction->errors as $msg)
						$errors .= '<li>'.$msg;
					$errors .= '</ul>';
					echo $errors;
					break;
				}
				else {
					conf('Chiusura mese corretta, saldo a consuntivo: '.formattaImporto($saldo));
					unset($_SESSION['temp']['newtransaction']);
				}	
				
				$showlist = 1;
				break;
				
			//REOPENMONTH: riapre un mese chiuso a consuntivo
			case "reopenmonth":
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

				//lettura conto da variabile sessione
				if (!isset($_SESSION['accountid'])){
					err('Nessun conto selezionato');
					break;
				}
				$accountid = $_SESSION['accountid'];

				//individua il conto richiesto
				$account = Account::first( 
					array(
						'conditions' => array('id = ? AND user_id = ?', $accountid, $user->id)
					)
				);

				//se conto non valido interrompo
				if ($account == null){
					err('Errore: passato ID di conto inesistente.');
					break;
				}
				
				//verifica presenza e acquisizione parametri da GET
				$parametri = array(
					'year',
					'month'
				);
				foreach ($parametri as $parametro){
					if (!isset($_GET[$parametro])){
						err('Non sono stati passati tutti i parametri necessari (manca '.$parametro.').');
						break;
					}				
				}			
				$month = $_GET['month'];
				$year = $_GET['year'];
				
				//calcolo date massima e minima per ricerca
				$datemin = date("Y-m-d", mktime(0, 0, 0, $month, 1, $year));
				debug('Elimino chiusure da: '.$datemin);
				
				//elimino precedenti voci auto=1 nel mese
				$precChiusure = Transaction::find(
					'all',
					array(
						'conditions' => array('account_id = ? AND date >= ? AND auto = ?', 
							$account->id, 
							$datemin, 
							1
						),
					)
				);
				
				if (isset($_GET['confirm'])){
					conf('Riaperto il mese "'.$month.'/'.$year.'"<br>');
					foreach($precChiusure as $transaction){
						$transaction->delete();
					}
				}
				else{
					$mess = 'Procedo a riaprire il mese "'.$month.'/'.$year.'"<br>';
					$mess .= 'Attenzione: la procedura riapre anche tutti i mesi successivi!<br>';
					$mess .= '<a href="account.php?action=reopenmonth&year='.$year.'&month='.$month.'&confirm"';
					$mess .= ' class="toolbarButtonDelete">';
					$mess .= 'clicca per confermare';
					$mess .= '</a>';
					notice($mess);						
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
			
				//parametri passati da POST
				//importante: i nomi devono essere uguali a quelli passati da
				//form, i quali a loro volta devono essere uguali ai nomi dei
				//record della tabella interessata!
				$parametri = array(
					'description',
					'import',
					'category_id',
					'second_account_id',
					'date',
					'note',
					'tags'
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
				
				//voce manuale
				$newValue['auto'] = 0;
				
				//estrazione lista tags da parametri
				$tagstring = $newValue['tags'];
				unset($newValue['tags']);
				$taglist = explode(",", $tagstring);
				
				//estrazione secondo conto da parametri
				$second_account_id = $newValue['second_account_id'];
				unset($newValue['second_account_id']);
				
				//analizzo il mese per vedere se è stato chiuso
				$dateElems = explode("-",$newValue['date']);
				if (!isset($dateElems[1]) || !isset($dateElems[2])){
					err('Errore nel calcolo della data.');
					break;
				}
				$year = $dateElems[2];
				$month = $dateElems[1];
				debug("New transaction date: ".$newValue['date']." - Year: $year - Month: $month");
				$datemin = date("Y-m-d", mktime(0, 0, 0, $month, 1, $year));
				$datemax = date("Y-m-d", mktime(0, 0, 0, $month+1, 1, $year));				
				$cercaChiusura = Transaction::first(
					array(
						'conditions' => array(
							'account_id = ? AND date >= ? AND date < ? AND auto = ? and category_id = ?', 
							$newValue['account_id'], 
							$datemin, 
							$datemax,
							1, 	//chiusura mese
							0	//no categoria
						),
						'order' => 'date asc'
					)
				);
				if ($cercaChiusura != null){
					notice("Impossibile creare nuova voce: mese chiuso a consuntivo!");
					break;
				}
				$cercaChiusura2 = Transaction::first(
					array(
						'conditions' => array(
							'account_id = ? AND date >= ? AND date < ? AND auto = ? and category_id = ?', 
							$second_account_id, 
							$datemin, 
							$datemax,
							1, 	//chiusura mese
							0	//no categoria
						),
						'order' => 'date asc'
					)
				);
				if ($cercaChiusura2 != null){
					notice("Impossibile creare nuova voce: mese chiuso a consuntivo per il conto complementare!");
					break;
				}

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
					break;
				}
				else {
					conf('Nuova transazione "'.$transaction->description.'" creata correttamente');
					unset($_SESSION['temp']['newtransaction']);
				}
				
				//recupero la transizione (per import e description)
				$transaction = Transaction::last(
					array(
						'conditions' => array(
							'import = ? AND description = ?', 
							$newValue['import'], 
							$newValue['description'])
					)
				);
				
				//salva la lista tags
				foreach ($taglist as $tagname){
					$tagname = trim($tagname);
					if (strlen($tagname) == 0) continue;
					
					//verifico se il tag esiste già per l'utente
					$tag = Tag::first(
						array(
							'conditions' => 
								array('user_id = ? AND name = ?', $user->id, $tagname)
						)
					);
					
					//se non esiste lo creo
					if ($tag == null){
						$tag = new Tag( 
							array(
								'name' => $tagname,
								'user_id' => $user->id
							)
						);
						$result = $tag->save();
						
						//gestione errori nella creazione nuovo tag
						if ($result == false){
							$errors = '<ul class="error" style="padding:10px 10px 10px 20px;">';
							$errors .= '<li>Impossibile creare il tag: '.$tag->name;
							foreach ($tag->errors as $msg)
								$errors .= '<li>-- '.$msg;
							$errors .= '</ul>';
							echo $errors;
							break;
						}
						
						//recupero il tag salvato
						$tag = Tag::first(
							array(
								'conditions' => 
									array('user_id = ? AND name = ?', $user->id, $tagname)
							)
						);
						
						if ($tag == null) {
							err('Errore nella creazione di un nuovo tag');
							break;
						}
					
					}
					
					//associo il tag alla transazione
					$transactiontag = new Transactiontag(
						array(
							'transaction_id' => $transaction->id,
							'tag_id' => $tag->id
						)
					);
					$result = $transactiontag->save();
					
					//gestione errori
					if ($result == false){
						$errors = '<ul class="error" style="padding:10px 10px 10px 20px;">';
						$errors .= '<li>Impossibile associare il tag: '.$tag->name;
						foreach ($transactiontag->errors as $msg)
							$errors .= '<li>-- '.$msg;
						$errors .= '</ul>';
						echo $errors;
						break;
					}
				
				}
				
				////////////////////////////////////////////////////////////////////
					
				//creazione nuovo oggetto (conto opposto) e salvataggio
				
				if ($second_account_id == 0) break;
				
				$newValue['account_id'] = $second_account_id;
				$newValue['import'] = -$newValue['import'];
				$transaction = new Transaction($newValue);
				$result = $transaction->save();
				
				//verifica salvataggio
				if ($result == false){
					$errors = '<ul class="error" style="padding:10px 10px 10px 20px;">';
					foreach ($transaction->errors as $msg)
						$errors .= '<li>'.$msg;
					$errors .= '</ul>';
					echo $errors;
					break;
				}
				else {
					conf('Transazione opposta creata correttamente.');
					unset($_SESSION['temp']['newtransaction']);
				}
				
				//recupero la transizione (per import e description)
				$transaction = Transaction::last(
					array(
						'conditions' => array(
							'import = ? AND description = ?', 
							$newValue['import'], 
							$newValue['description'])
					)
				);
				
				//salva la lista tags
				foreach ($taglist as $tagname){
					$tagname = trim($tagname);
					if (strlen($tagname) == 0) continue;
					
					//verifico se il tag esiste già per l'utente
					$tag = Tag::first(
						array(
							'conditions' => 
								array('user_id = ? AND name = ?', $user->id, $tagname)
						)
					);
					
					//se non esiste lo creo
					if ($tag == null){
						$tag = new Tag( 
							array(
								'name' => $tagname,
								'user_id' => $user->id
							)
						);
						$result = $tag->save();
						
						//gestione errori nella creazione nuovo tag
						if ($result == false){
							$errors = '<ul class="error" style="padding:10px 10px 10px 20px;">';
							$errors .= '<li>Impossibile creare il tag: '.$tag->name;
							foreach ($tag->errors as $msg)
								$errors .= '<li>-- '.$msg;
							$errors .= '</ul>';
							echo $errors;
							break;
						}
						
						//recupero il tag salvato
						$tag = Tag::first(
							array(
								'conditions' => 
									array('user_id = ? AND name = ?', $user->id, $tagname)
							)
						);
						
						if ($tag == null) {
							err('Errore nella creazione di un nuovo tag');
							break;
						}
					}
					
					//associo il tag alla transazione
					$transactiontag = new Transactiontag(
						array(
							'transaction_id' => $transaction->id,
							'tag_id' => $tag->id
						)
					);
					$result = $transactiontag->save();
					
					//gestione errori
					if ($result == false){
						$errors = '<ul class="error" style="padding:10px 10px 10px 20px;">';
						$errors .= '<li>Impossibile associare il tag: '.$tag->name;
						foreach ($transactiontag->errors as $msg)
							$errors .= '<li>-- '.$msg;
						$errors .= '</ul>';
						echo $errors;
						break;
					}

					
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
		$tags = "";
		if (isset($_SESSION['temp']['newtransaction'])){
			$description = $_SESSION['temp']['newtransaction']['description'];
			$import = $_SESSION['temp']['newtransaction']['import'];
			$category = $_SESSION['temp']['newtransaction']['category_id'];
			$date = $_SESSION['temp']['newtransaction']['date'];
			$note = $_SESSION['temp']['newtransaction']['note'];
			$tags = "aa";//$_SESSION['temp']['newtransaction']['tags'];
			unset($_SESSION['temp']['newtransaction']);
			echo '<script>$(document).ready(function() {$(\'div#addTransactionForm\').show();})</script>';
		}
		
		//recupero elenco categorie per l'utente				
		$categories = Category::find(
			'all',
			array('conditions' => array('user_id = ?', $user->id))				
		);	

		//recupero elenco account per l'utente				
		$useraccounts = Account::find(
			'all',
			array(
				'conditions' => array(
					'user_id = ?', 
					$user->id
				)
			)				
		);		
	
		// composizione delle date del filtro del mese attuale
		$date1stJan = date("Y-m-d", mktime(0, 0, 0, 1, 1, $year));
		$datemin = date("Y-m-d", mktime(0, 0, 0, $month, 1, $year));
		$datemax = date("Y-m-d", mktime(0, 0, 0, $month+1, 1, $year));
		$datetomorrow = date("Y-m-d", mktime(0, 0, 0, date('m'), date('d')+1, date('Y')));
		debug("min: $datemin - max: $datemax - tomorrow: $datetomorrow - 1stJan: $date1stJan ");
		
		//analizzo il mese per vedere se è stato chiuso
		$cercaChiusura = Transaction::first(
			array(
				'conditions' => array(
					'account_id = ? AND date >= ? AND date < ? AND auto = ? and category_id = ?', 
					$conto->id, 
					$datemin, 
					$datemax,
					1, 	//chiusura mese
					0	//no categoria
				),
				'order' => 'date asc'
			)
		);
		$meseChiuso = 0;
		if ($cercaChiusura != null)
			$meseChiuso = 1;
		?>
		
		<div class="toolbar">
			<a href="account.php?action=listaccount" class="toolbarButton">Lista conti</a>
			
			<?php
			if ($meseChiuso == 0){
				echo '<a href="#" onclick="mostraDivSlow(\'addTransactionForm\')"'
					.' class="toolbarButtonNew" id="addTransaction">Nuova voce</a>';
			}
			?>
			
			<?php
			if ($meseChiuso == 0) {
				echo '<a href="account.php?action=closemonth&year='
					.$year.'&month='.$month.'" class="toolbarButton">Chiudi mese</a>';
			}
			else {
				echo '<a href="account.php?action=reopenmonth&year='
					.$year.'&month='.$month.'" class="toolbarButtonAlert">Mese chiuso a consuntivo</a>';
			}
			?>
		</div>	
		
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
				
				Eventuale voce complementare:<br>
				<select name="second_account_id">
					<option value="0" selected>----</option>
					<?php
					foreach($useraccounts as $useraccount){
						if ($useraccount->id != $_SESSION['accountid'])
							echo '<option value="'.$useraccount->id.'">'.$useraccount->description.'</option>';
					}
					?>
				</select><br>
				
				Importo:<br>
				<input type="text" name="import" value="<?php echo $import; ?>"><br>
				
				Data:<br>
				<input type="text" id="datepicker" name="date" value="<?php echo $date; ?>"><br>
				
				Tags:<br>
				<input id="tags" name="tags" value="<?php echo $tags; ?>"><br>

				<?php
					//include codice per gestione autocompletamento
					include_once('include/autocomptag.php');
				?>

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

		//-----------------------------------------------------------------------------------------------------

		// ricerca saldo a inizio anno
		$prevYears = 0;
		
		//recupero dalla chiusura di dicembre MP
		$dateDicMPmin = date("Y-m-d", mktime(0, 0, 0, 12, 1, $year-1));
		$dateDicMPmax = date("Y-m-d", mktime(0, 0, 0, 1, 1, $year));
		$prevYearsTransactions = Transaction::first(
			array(
				'conditions' => array('account_id = ? AND date >= ? AND date < ? AND auto = ? AND category_id = ?', 
					$conto->id, 
					$dateDicMPmin,
					$dateDicMPmax, 
					1,
					0
				),
				'order' => 'date desc'
			)
		);
		
		if ($prevYearsTransactions != null){
			$prevYears = $prevYearsTransactions->import;
			debug('Saldo anno precedente da chiusura dicembre MP ('.$dateDicMPmax.' - '.$dateDicMPmax.'): '.$prevYears);
		}
		else{
			//ricostruisco storico
			$prevYearsTransactions = Transaction::find(
				'all',
				array(
					'select' => 'sum(import) as sum_imports',
					'conditions' => array(
						'account_id = ? AND date < ? AND auto = ?', 
						$conto->id, 
						$date1stJan,
						0
					),
				)
			);
			if ($prevYearsTransactions != null){
				$prevYears = $prevYearsTransactions[0]->sum_imports;
				debug('Saldo anno precedente da ricostruzione (prima di '.$date1stJan.'): '.$prevYears);
				notice('Anno precedente non chiuso correttamente, ricostruzione saldo');
			}
		}
		
		//ricerca saldi storici di tutti i mesi dell'anno in corso
		$prevMonthsTransactions = array();
		for ($i = 1; $i < $month; $i++){
			$beginMonth = date("Y-m-d", mktime(0, 0, 0, $i, 1, $year));
			$endMonth = date("Y-m-d", mktime(0, 0, 0, $i+1, 1, $year));
			
			$valore = 0;
			$incremento = 0;
			
			$prevMonth = Transaction::first(
				array(
					'conditions' => array(
						'account_id = ? AND date >= ? AND date < ? AND auto = ? AND category_id = ?', 
						$conto->id, 
						$beginMonth, 
						$endMonth,
						1,
						0
					),
				)
			);
			
			if ($prevMonth != null){
				$valore = $prevMonth->import;
			}
			
			$prevMonth = Transaction::find(
				'all',
				array(
					'select' => 'sum(import) as sum_imports',
					'conditions' => array(
						'account_id = ? AND date >= ? AND date < ? AND auto = ?', 
						$conto->id, 
						$beginMonth, 
						$endMonth,
						0
					),
				)
			);
			
			if ($prevMonth != null) 
				$incremento = $prevMonth[0]->sum_imports;
			
			$newElement = array(
				'anno' => $year,
				'mese' => $i,
				'valore' => $valore,
				'incremento' => $incremento
			);
			array_push($prevMonthsTransactions, $newElement);
			
		}
		
		if ($year == date('Y') && $month == date('m')){
			
			debug('mese corrente');
		
			// ricerca transazioni mese corrente (fino al giorno corrente)
			$transactionsBefore = Transaction::find(
				'all',
				array(
					'conditions' => array(
						'account_id = ? AND date >= ? AND date < ? AND auto = ?', 
						$conto->id, 
						$datemin, 
						$datetomorrow,
						0
					),
					'order' => 'date asc'
				)
			);
	
			// ricerca transazioni mese corrente (dopo il giorno corrente)
			$transactionsAfter = Transaction::find(
				'all',
				array(
					'conditions' => array(
						'account_id = ? AND date >= ? AND date < ? AND auto = ?', 
						$conto->id, 
						$datetomorrow, 
						$datemax,
						0
					),
					'order' => 'date asc'
				)
			);
		
		}
		else{
			
			debug('non mese corrente');
			
			// annulla transazioni prima
			$transactionsBefore = array();
				
			// ricerca transazioni
			$transactionsAfter = Transaction::find(
				'all',
				array(
					'conditions' => array(
						'account_id = ? AND date >= ? and date < ? AND auto = ?', 
						$conto->id, 
						$datemin, 
						$datemax,
						0
					),
					'order' => 'date asc'
				)
			);
	
		}
		
		//-----------------------------------------------------------------------------------------------------
		
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
		echo '<div>';
		echo '<div align=center>';
		echo '<a href="account.php?year='.$prevyear.'&month='.$prevmonth.'" class="toolbarButtonLeft">&lt;&lt;&lt;</a>';
		echo '<a href="account.php" class="toolbarButton">Oggi</a>';
		echo '<a href="#" class="toolbarButton" onclick="mostraDiv(\'selectPeriod\')">Scegli...</a>';
		echo '<a href="account.php?year='.$nextyear.'&month='.$nextmonth.'" class="toolbarButtonRight">&gt;&gt;&gt;</a>';
		echo '</div>';
		echo '<p>';
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
		
		//-----------------------------------------------------------------------------------------------------
		
		//saldo inizio esercizio
		$saldoProgressivo = $prevYears;
			
		echo '<div style="display:none" id="saldiStorici">';

			printTotal(
				'Saldo inizio anno', 
				$saldoProgressivo,
				1,
				'<img src="images/downTriangle.png" onclick="mostraDiv(\'saldiStorici\')"/>'
			);
			
			//saldo alla fine di ogni mese precedente al mese selezionato
			foreach ($prevMonthsTransactions as $prevMonthsTransaction){
				$chiuso = "";
				if ($prevMonthsTransaction['valore'] != 0){
					$saldoProgressivo = $prevMonthsTransaction['valore'];
					$chiuso = " (consuntivato)";
				}
				else{
					$saldoProgressivo += $prevMonthsTransaction['incremento'];
				}
				printTotal('Saldo di '.
					'<a href="account.php?year='.$prevMonthsTransaction['anno'].'&month='.$prevMonthsTransaction['mese'].'">'.
					decodificaMese($prevMonthsTransaction['mese']).' '.
					$prevMonthsTransaction['anno'].'</a>'.$chiuso, $saldoProgressivo);
			}
			
		echo '</div>';
		
		//-----------------------------------------------------------------------------------------------------
		
		$saldoInizioMese = $saldoProgressivo;
		
		// Stampa storico
		printTotal(
			'Saldo inizio mese', 
			$saldoInizioMese,
			1,
			'<img src="images/downTriangle.png" onclick="mostraDiv(\'saldiStorici\')"/>'
		);

		$totale = $saldoInizioMese;
		foreach ($transactionsBefore as $transaction){
			if ($meseChiuso == 0)
				printTransaction($transaction);
			else
				printTransactionNoDelete($transaction);
			$totale += $transaction->import;
		}
	
		if ($year == date('Y') && $month == date('m')) 
			printTotal("Saldo attuale", $totale, 1);	

		foreach ($transactionsAfter as $transaction){
			if ($meseChiuso == 0)
				printTransaction($transaction);
			else
				printTransactionNoDelete($transaction);
			$totale += $transaction->import;
		}
		
		//-----------------------------------------------------------------------------------------------------
		
		// Stampa totale conto attuale
		printTotal("Saldo previsto a fine mese", $totale, 1);
		
		//-----------------------------------------------------------------------------------------------------
		
		echo '<hr>';

		// Stampa consuntivi
		$consuntivo = Transaction::first(
			array(
				'conditions' => array(
					'account_id = ? AND date >= ? AND date < ? AND auto = ? and category_id = ?', 
					$conto->id, 
					$datemin, 
					$datemax,
					1, //chiusure mese
					0 //no categoria
				),
				'order' => 'auto asc'
			)
		);
		if ($consuntivo != null){
			printTotal("Chiusura mese", $consuntivo->import, 2);
			printTotal("Variazione saldo mensile", ($consuntivo->import - $saldoInizioMese), 2);
		}
		
		echo '</fieldset>';

	}

?>

<?php
	include('footer.inc.php');
?>