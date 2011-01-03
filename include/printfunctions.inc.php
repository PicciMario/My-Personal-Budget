<?php

	function printCategory($category){
		
		if ($category == null) return;
		
		//div principale
		echo '<div class="categoryList">';
		
		//prima icona
		echo '<div class="categoryListIcon1">';
			echo '<a href="categories.php?action=showcategory&categoryid='.$category->id.'">';
			echo '<img src="images/select.jpg"/>';
			echo '</a>';
		echo '</div>';
		
		//seconda icona
		echo '<div class="categoryListIcon2">';
			echo '<img src="images/downTriangle.png" onclick="mostraDiv(\'accNote'.$category->id.'\')"/>';
		echo '</div>';		
		
		//descrizione
		echo '<div class="categoryListDescr">';
			echo $category->name;
		echo '</div>';
		
		//descrizione 2
		echo '<div class="categoryListDescr2">';
			echo stripslashes($category->description);
		echo '</div>';
		
		echo '</div>';
		
		//div a scomparsa
		echo '<div id="accNote'.$category->id.'" style="display:none;" class="accountNote aNote">';
		
			//toolbar nel div a scomparsa
			echo '<div>';
				echo '<a href="categories.php?action=deletecategory&categoryid='.$category->id.'" class="toolbarButton">Elimina categoria</a>';
			echo '</div>';
		
		echo '</div>';		
		
	}

	//************************************************************************************************

	function printTransactionNoDelete($transaction, $descr = 0){
		printTransaction($transaction, $descr, 1);
	}

	function printTransaction($transaction, $descr = 0, $noDelete = 0){
		
		//$descr indica cosa stampare nel secondo campo descrizione
		//0 = nome categoria di appartenenza
		//1 = nome conto di appartenenza
		
		if ($transaction == null) return;
		
		$import = $transaction->import;
		
		//div principale transazione
		echo '<div class="transaction ';
		
		//sceglie colore transazione
		if ($import >= 0) echo 'positive';
		if ($import < 0) echo 'negative';
		
		echo '">';
		
		//id transazione
		echo '<div class="transactionId">';
			echo '<img src="images/downTriangle.png" onclick="mostraDiv(\'transNote'.$transaction->id.'\')"/>';
		echo '</div>';
		
		//data transazione
		echo '<div class="transactionDate">';
			echo $transaction->date->format("d/m/y");
		echo '</div>';
		
		//descrizione transazione
		echo '<div class="transactionDescr">';
			echo $transaction->description;
		echo '</div>';
		
		//categoria (o account) transazione
		echo '<div class="transactionCat">';
			if ($descr == 0)
				echo $transaction->category->name;
			elseif ($descr == 1)
				echo $transaction->account->description;
		echo '</div>';
		
		//importo transazione
		if ($import >= 0) {
			echo '<div class="transactionValue">';
				echo formattaImporto($import);
				echo '</div>';
		}
		else{
			echo '<div class="transactionValue2">';
				echo formattaImporto($import);
			echo '</div>';
		}
		
		echo '</div>';
		
		//note transazione
		echo '<div id="transNote'.$transaction->id.'" style="display:none;" class="transactionNote note">';
		
			//toolbar note transazione
			echo '<div class="simpleToolbar">';
			if ($noDelete == 0){
				echo '<a href="account.php?action=deletetransaction&transactionid='.$transaction->id.'"';
				echo ' class=toolbarButtonDelete>';
				echo 'Cancella</a>';
			}
			echo '</div>';
			
			//testo note alla transazione
			echo '<div>';
			echo nl2br(htmlspecialchars($transaction->note));
			echo '</div>';
			
			//elenco tags
			echo '<div class="tagbar">';
			//ricerca tags
			$transactiontags = Transactiontag::find(
				'all',
				array(
					'conditions' => array('transaction_id = ?', $transaction->id),
					'include' => array('tag')
				)
			);
			//stampa elenco tags
			if (count($transactiontags) > 0) echo 'TAGS: ';
			foreach ($transactiontags as $transactiontag) 
				echo '<div class="toolbarButton">'.$transactiontag->tag->name.'</div>';
			echo '</div>';
		
		echo '</div>';
		
	}

	//************************************************************************************************

	function printAccount($account, $saldo = 999999){
		
		if ($account == null) return;
		
		//div principale
		echo '<div class="accountList">';
		
		//prima icona
		echo '<div class="accountListIcon1">';
			echo '<a href="account.php?action=selectaccount&id='.$account->id.'">';
			echo '<img src="images/select.jpg"/>';
			echo '</a>';
		echo '</div>';
		
		//seconda icona
		echo '<div class="accountListIcon2">';
			echo '<img src="images/downTriangle.png" onclick="mostraDiv(\'accNote'.$account->id.'\')"/>';
		echo '</div>';	
		
		//descrizione conto
		echo '<div class="accountListDescr">';
			echo $account->description;
		echo '</div>';
		
		if ($saldo >= 0){
			//$saldo positivo
			echo '<div class="accountListValue">';
				echo formattaImporto($saldo);
			echo '</div>';
		}
		else {
			//$saldo negativo
			echo '<div class="accountListValue2">';
				echo formattaImporto($saldo);
			echo '</div>';
		}
		
		echo '</div>';
		
		echo '<div id="accNote'.$account->id.'" style="display:none;" class="accountNote aNote">';
		echo '<div>';
		echo '<a href="account.php?action=deleteaccount&accountid='.$account->id.'" class="toolbarButton">Elimina conto</a>';
		echo '</div>';
		
		echo '</div>';		
		
	}

	//************************************************************************************************

	function printTotal($text, $import, $color=0, $id=""){
		
		echo '<div class="transaction transactionTotal ';
		if ($color == 0) echo 'yellowTot';
		if ($color == 1) echo 'orangeTot';
		if ($color == 2) echo 'blueTot';
		echo '">';
		
		//id totale
		echo '<div class="transactionId">';
			echo $id;
		echo '</div>';
		
		//descrizione totale
		echo '<div class="transactionDescr">';
			echo $text;
		echo '</div>';
		
		//importo totale
		if ($import >= 0){
			echo '<div class="transactionTotValue">';
				echo formattaImporto($import);
			echo '</div>';
		}
		else{
			echo '<div class="transactionTotValue2">';
				echo formattaImporto($import);
			echo '</div>';
		}
		
		echo '</div>';
	}
	
	//************************************************************************************************
	
	function decodificaMese($mese){
		$mesi = array(
			1 => 'Gennaio',
			2 => 'Febbraio',
			3 => 'Marzo',
			4 => 'Aprile',
			5 => 'Maggio',
			6 => 'Giugno',
			7 => 'Luglio',
			8 => 'Agosto',
			9 => 'Settembre',
			10 => 'Ottobre',
			11 => 'Novembre',
			12 => 'Dicembre'
		);

		if (isset($mesi[(int)$mese]))
			return $mesi[(int)$mese];
		else
			return "";
	}
	
	//************************************************************************************************
	
	function formattaImporto($valore){
		return number_format($valore, 2, ',', '\'').' €';
	}

	//************************************************************************************************
	
	function inizioMese($month, $year){
		return date("Y-m-d", mktime(0, 0, 0, $month, 1, $year));
	}
	function fineMese($month, $year){
		return date("Y-m-d", mktime(0, 0, 0, $month+1, 0, $year));
	}
	
	//************************************************************************************************

	//stampa errore
	function err($text){
		echo '<p class="error"><strong>AVVISO:</strong> '.$text.'<br>';
		echo '<font style="font-style:italic;font-size:10px;">click to hide</font>';
		echo '</p>';
	}

	//stampa errore con pulsante back
	function errback($text){
		echo '<p class="error">'.$text;
		echo '<br><a href="#" onClick="history.go(-1)">Torna indietro</a> ';
		echo '</p>';
	}
	
	//stampa conferma
	function conf($text){
		echo '<p class="success">'.$text.'<br>';
		echo '<font style="font-style:italic;font-size:10px;">click to hide</font>';
		echo '</p>';
	}
	
	//stampa notice
	function notice($text){
		echo '<p class="notice"><strong>AVVISO:</strong> '.$text.'<br>';
		echo '<font style="font-style:italic;font-size:10px;">click to hide</font>';
		echo '</p>';
	}
	
	//stampa debug
	function debug($text){
		$debug = $_SESSION['debug'];
		if ($debug != 0){
			echo '<p class="info"><strong>DEBUG:</strong> '.$text.'<br>';
			echo '<font style="font-style:italic;font-size:10px;">click to hide</font>';
			echo '</p>';
		}
	}

?>
