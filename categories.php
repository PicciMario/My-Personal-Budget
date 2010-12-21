<?php
	$pagedata['pagetitle'] = "Gestione categorie";
	$pagedata['onlyadmin'] = 0;
	$pagedata['onlylogged'] = 1;
	include('header.inc.php');
?>

<?php
	
	$showlist = 1;

	//**** parametri GET ******************************************************************

	if (isset($_GET['action'])){
		
		$action = $_GET['action'];
		
		switch ($action){
			
			// SHOWCATEGORY - dettagli categoria
			case "showcategory":
			
				$showlist = 0;
			
				if (!isset($_SESSION['userid'])){
					err("Utente non valido");
					break;
				}
				
				if (!isset($_GET['categoryid'])){
					err("Categoria non impostata");
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
				
				//individuo categoria
				$category = Category::first( 
					array(
						'conditions' => array('id = ? AND user_id = ?', $_GET['categoryid'], $user->id)
					)
				);
				
				//se categoria non valida interrompo
				if ($category == null){
					err('Errore: passato ID di categoria inesistente.');
					break;
				}
				
				// ricerca transazioni nella categoria selezionata
				$transactions = Transaction::find(
					'all',
					array(
						'conditions' => array('category_id = ?', $category->id),
						'order' => 'date desc'
					)
				);
				
				//calcola totale
				$totale = 0;
				foreach ($transactions as $transaction){
					$totale += $transaction->import;
				}
				
				//stampa elementi
				echo '<fieldset><legend>Elenco voci categoria: '.$category->name.'</legend>';
				printTotal("Totale categoria", $totale);
				foreach ($transactions as $transaction){
					printTransaction($transaction, 1);
				}
				echo '</fieldset>';
				
				break;

			// DELETECATEGORY - elimina categoria
			case "deletecategory":
			
				if (!isset($_SESSION['userid'])){
					err("Utente non valido");
					break;
				}
				
				if (!isset($_GET['categoryid'])){
					err("Categoria non impostata");
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
				
				//individuo categoria
				$category = Category::first( 
					array(
						'conditions' => array('id = ? AND user_id = ?', $_GET['categoryid'], $user->id)
					)
				);
				
				//se categoria non valida interrompo
				if ($category == null){
					err('Errore: passato ID di categoria inesistente.');
					break;
				}
				
				//voci nella categoria
				$transactions = Transaction::find( 
					'all',
					array(
						'conditions' => array('category_id = ?', $category->id)
					)
				);
				
				if (isset($_GET['confirm'])){
					$category->delete();
					foreach ($transactions as $transaction){
						$transaction->delete();
					}
					conf('Confermata cancellazione categoria');
				}
				else{
					$mess = 'Procedo a eliminare la categoria "'.$category->name;
					$mess .= '" e le sue '.count($transactions).' voci.<br>';
					$mess .= '<a href="categories.php?action=deletecategory&categoryid='.$category->id.'&confirm">';
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
		
		switch ($action){
			
			// NEWCATEGORY - crea nuova categoria
			case "newcategory":
			
				//parametri passati da POST
				//importante: i nomi devono essere uguali a quelli passati da
				//form, i quali a loro volta devono essere uguali ai nomi dei
				//record della tabella interessata!
				$parametri = array(
					'name',
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
				
				//impostazione parametri mancanti (non passati da POST)
				if (!isset($_SESSION['userid'])){
					error('Parametro user id non impostato');
					break;
				}
				$newValue['user_id'] = $_SESSION['userid'];
				
				//creazione nuovo oggetto e salvataggio
				$category = new Category($newValue);
				$result = $category->save();
				
				//verifica salvataggio
				if ($result == false){
					$errors = '<ul class="error" style="padding:10px 10px 10px 20px;">';
					foreach ($category->errors as $msg)
						$errors .= '<li>'.$msg;
					$errors .= '</ul>';
					echo $errors;
				}
				else {
					conf('Nuova categoria "'.$category->description.'" creata correttamente');
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
			
			?>
				
			<div class="toolbar">
				<a href="#" onclick="mostraDivSlow('addCategoryForm')" class="toolbarButton" id="addCategory">Nuova categoria</a>
			</div>
				
			<div id="addCategoryForm" style="display:none">
				<form action="categories.php" method="post">
				<fieldset>
					<legend>Aggiungi Categoria</legend>
					
					Nome:<br>
					<input type="text" name="name"><br>
					Descrizione:<br>
					<input type="text" size=60 name="description"><br>
					
					<input type=hidden name="action" value="newcategory">
					
					<input type=submit value="Salva">
					<input type=button id="closeTransactionForm" onclick="mostraDivSlow('addCategoryForm')" value="Annulla">
	
				</fieldset>
				</form>
			</div>
		
			<?php
			
			//elenco categorie per l'utente
			$categories = Category::find(
				'all',
				array(
					'conditions' => array(
						'user_id = ?', $_SESSION['userid']
					)
				)				
			);
			
			echo '<fieldset><legend>Elenco categorie</legend>';
			foreach ($categories as $category){
				printCategory($category);
			}
			echo '</fieldset>';

			break;
	}

?>

<?php
	include('footer.inc.php');
?>