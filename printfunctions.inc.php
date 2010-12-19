<?php

	function printCategory($category){
		if ($category == null) return;
		echo '<div class="categoryList">';
		echo '<div class="categoryListIcon1">';
			echo '<a href="categories.php?action=showcategory&categoryid='.$category->id.'">';
			echo '<img src="images/select.jpg"/>';
			echo '</a>';
		echo '</div>';
		echo '<div class="categoryListIcon2">';
			echo '<img src="images/downTriangle.png" onclick="mostraDiv(\'accNote'.$category->id.'\')"/>';
		echo '</div>';		
		echo '<div class="categoryListDescr">';
			echo $category->name;
		echo '</div>';
		echo '<div class="categoryListDescr2">';
			echo $category->description;
		echo '</div>';
		echo '</div>';
		
		echo '<div id="accNote'.$category->id.'" style="display:none;" class="accountNote aNote">';
		echo '<div>';
		echo '<a href="categories.php?action=deletecategory&categoryid='.$category->id.'" class="toolbarButton">Elimina categoria</a>';
		echo '</div>';
		echo '</div>';		
		
	}

	//************************************************************************************************

	function printTransaction($transaction, $descr = 0){
		
		//$descr indica cosa stampare nel secondo campo descrizione
		//0 = nome categoria di appartenenza
		//1 = nome conto di appartenenza
		
		if ($transaction == null) return;
		$import = $transaction->import;
		echo '<div class="transaction ';
		if ($import >= 0) echo 'positive';
		if ($import < 0) echo 'negative';
		echo '">';
		echo '<div class="transactionId">';
			echo '<img src="images/downTriangle.png" onclick="mostraDiv(\'transNote'.$transaction->id.'\')"/>';
		echo '</div>';
		echo '<div class="transactionDate">';
			echo $transaction->date->format("d/m/y");
		echo '</div>';
		echo '<div class="transactionDescr">';
			echo $transaction->description;
		echo '</div>';
		echo '<div class="transactionCat">';
			if ($descr == 0)
				echo $transaction->category->name;
			elseif ($descr == 1)
				echo $transaction->account->description;
		echo '</div>';
		echo '<div class="transactionValue">';
			printf("%01.2f €", $import);
		echo '</div>';
		echo '</div>';
		
		echo '<div id="transNote'.$transaction->id.'" style="display:none;" class="transactionNote note">';
		echo '<div class="toolbar">';
		echo '<a href="account.php?action=deletetransaction&transactionid='.$transaction->id.'" class=toolbarButton>';
		echo 'Elimina voce</a>';
		echo '</div>';
		echo nl2br(htmlspecialchars($transaction->note));
		echo '</div>';
		
	}

	//************************************************************************************************

	function printAccount($account){
		if ($account == null) return;
		echo '<div class="accountList">';
		echo '<div class="accountListIcon1">';
			echo '<a href="account.php?action=selectaccount&id='.$account->id.'">';
			echo '<img src="images/select.jpg"/>';
			echo '</a>';
		echo '</div>';
		echo '<div class="accountListIcon2">';
			echo '<img src="images/downTriangle.png" onclick="mostraDiv(\'accNote'.$account->id.'\')"/>';
		echo '</div>';		
		echo '<div class="accountListDescr">';
			echo $account->description;
		echo '</div>';
		echo '<div class="accountListDescr2">';
			echo count($account->transactions)." voci.";
		echo '</div>';
		echo '</div>';
		
		echo '<div id="accNote'.$account->id.'" style="display:none;" class="accountNote aNote">';
		echo '<div>';
		echo '<a href="account.php?action=deleteaccount&accountid='.$account->id.'" class="toolbarButton">Elimina conto</a>';
		echo '</div>';
		echo '</div>';		
		
	}

	//************************************************************************************************

	function printTotal($text, $import){
		echo '<div class="transaction transactionTotal ';
		if ($import >= 0) echo 'positiveTot';
		if ($import < 0) echo 'negativeTot';
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
