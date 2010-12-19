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