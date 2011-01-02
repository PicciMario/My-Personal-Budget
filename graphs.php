<?php
	$pagedata['pagetitle'] = "Graphs";
	$pagedata['onlyadmin'] = 0;
	$pagedata['onlylogged'] = 1;
	include('header.inc.php');
?>

<script language="javascript" type="text/javascript" src="flot/jquery.flot.min.js"></script>
<!--[if IE]><script language="javascript" type="text/javascript" src="flot/excanvas.min.js"></script><![endif]-->
<script language="javascript" type="text/javascript" src="flot/jquery.flot.pie.js"></script>

<?php
	
	$showDesc = 1;
	
	?>
		<div class="toolbar">
			<a href="graphs.php?action=mostrasaldi" class="toolbarButton">Saldi mensili</a>
			<a href="graphs.php?action=mostracat" class="toolbarButton">Movimenti per categoria</a>
		</div>
	<?php

	//**** parametri GET ******************************************************************

	if (isset($_GET['action'])){
		
		$action = $_GET['action'];
		
		switch ($action){
			
			// MOSTRASALDI: mostra un grafico con i saldi di fine mese di tutti i conti
			case "mostrasaldi":
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
				
				//elenco conti per l'utente
				$userAccounts = Account::find(
					'all',
					array(
						'conditions' => array(
							'user_id = ?', 
							$_SESSION['userid']
						)
					)				
				);			
				if (isset($_GET['accountid'])){
					if ($_GET['accountid'] == 0) unset($_GET['accountid']);
				}
				if (isset($_GET['accountid'])){
					$accounts = Account::find(
						'all',
						array(
							'conditions' => array(
								'user_id = ? AND id = ?', 
								$_SESSION['userid'],
								$_GET['accountid']
							)
						)				
					);					
				} 
				else{
					$accounts = $userAccounts;
				}
				
				//saldi anno corrente (o anno passato da get)
				$year = date("Y");
				if (isset($_GET['year'])){
					if (is_numeric($_GET['year'])){
						$year = $_GET['year'];
					}
				}
				
				//calcolo date inizio e fine anno
				$beginYear = date("Y-m-d", mktime(0, 0, 0, 1, 1, $year));
				$endYear = date("Y-m-d", mktime(0, 0, 0, 1, 1, $year+1));
				
				//costruzione di array 2-dimensionale
				//array ---> account ---> chiusura
				//      |             |-> chiusura
				//      |
				//      |--> account ---> chiusura
				//                    |-> chiusura
				
				$saldi = array();
				$nomi = array();
				for($i = 0; $i < count($accounts); $i++){
					$account = $accounts[$i];
					$nomi[$i] = $account->description;
					$saldi[$i] = array();
					$saldi[$i] = Transaction::find(
						'all',
						array(
							'conditions' => array(
								'account_id = ? AND auto = ? AND category_id = ? and date >= ? AND date < ?',
								$account->id,
								1,
								0,
								$beginYear, 
								$endYear				
							),
							'order' => 'date asc'
						)
					);
				}
				
				echo '<fieldset><legend>Saldi mensili '.$year.'</legend>';
				
				?>
				
					<!-- Barra di cambio anno -->
					<div align=center>
					<div>
						<a href="graphs.php?action=mostrasaldi&year=<?php echo $year-1 ?>" class="toolbarButtonLeftText" id="addCategory"><?php echo $year-1 ?></a>
						<a href="graphs.php?action=mostrasaldi&year=<?php echo date("Y") ?>" class="toolbarButton" id="addCategory">Oggi: <?php echo date("Y") ?></a>
						
						<span id="scegliConto">
						<button id="scegliContoNome">
							<?php
								if (count($saldi) > 1){
									echo "Tutti i conti";
								}
								else if (count($saldi == 1)){
									if (isset($nomi[0]))
										echo $nomi[0];
									else
										echo "Nessun conto selezionato";
								}

							?>
						</button>
						<button id="select">Scegli un conto</button>
						</span>
						
						<a href="graphs.php?action=mostrasaldi&year=<?php echo $year+1 ?>" class="toolbarButtonRightText" id="addCategory"><?php echo $year+1 ?></a>
						
					</div>
					</div>
					
					<!-- form per scelta conto -->
					<div id="scegliContoForm" style="display:none;">
						<form action="graphs.php" method="get">
							
							Conto da mostrare:
							<select name="accountid">
								<?php
								echo '<option value=0>Tutti i conti</option>';
								foreach($userAccounts as $account){
									echo '<option value="'.$account->id.'">'.$account->description.'</option>';
									echo $account->description;
								}
								?>
							</select>
							
							<input type=hidden name="action" value="mostrasaldi">
							<input type=hidden name="year" value="<?php echo $year; ?>">
							<input type=submit value="Mostra">
						</form>
					</div>
		
					<br>
					<hr>

					<!-- spazio per costruzione legenda grafico -->
					<div id="legenda" style="width:400px;margin-left:10px;margin-right:10px;"></div>
					
					<!-- spazio per costruzione canvas grafico -->
					<div id="placeholder" style="width:600px;height:300px;"></div>

					<hr>
					
					<!-- javascript per il pulsante di selezione conto -->
					<script>
					$(function() {
						$( "#scegliContoNome" )
							.button()
							.next()
								.button( {
									text: false,
									icons: {
										primary: "ui-icon-triangle-1-s"
									}
								})
								.click(function() {
									mostraDiv('scegliContoForm');
								})
								
								$("#scegliConto").buttonset();
					});
					</script>
	
					<script>				
					$(function () {
					    				    
						<?php 
							//crea le variabili con i dati
							for($i = 0; $i < count($saldi); $i++){
								echo 'var d'.$i.' = [];';
								$elementi = $saldi[$i];
								foreach ($elementi as $elemento) {
									echo 'd'.$i.'.push(['.$elemento->date->format("m").','.$elemento->import.']);';
								}
							}
						?>    
					
					    $.plot($("#placeholder"), [
					    
					    <?php
					    	//inizializza le serie
							for($i = 0; $i < count($saldi); $i++){
								echo '{';
								echo 'data: d'.$i.',';
								echo 'label: "'.$nomi[$i].'",';
								echo 'lines: { show: true },';
								echo 'points: { show: true}';
								echo '},';
							}				    
					    ?>
	
					    ],{
							xaxis: {
		            			ticks: [
									[1, "GEN"],
									[2, "FEB"],
		            				[3, "MAR"],
		            				[4, "APR"],
		            				[5, "MAG"],
		            				[6, "GIU"],
		            				[7, "LUG"],
		            				[8, "AGO"],
		            				[9, "SET"], 
		           					[10, "OTT"],
		           					[11, "NOV"],
									[12, "DIC"], 
								]
	        				},
	        				grid: {
	            				backgroundColor: { 
            						colors: ["#fff", "#eee"] 
            					}
	       		 			},
							legend: {
								container: legenda
							},	
							grid: { 
								hoverable: true, 
								clickable: true 
							}		
	       		 
					    });
					});	
	
				    function showTooltip(x, y, contents) {
				        $('<div id="tooltip">' + contents + '</div>').css( {
				            position: 'absolute',
				            display: 'none',
				            top: y + 5,
				            left: x + 5,
				            border: '1px solid #fdd',
				            padding: '2px',
				            'background-color': '#fee',
				            opacity: 0.80
				        }).appendTo("body").fadeIn(200);
				    }
				
								
				    var previousPoint = null;
				    $("#placeholder").bind("plothover", function (event, pos, item) {
				        $("#x").text(pos.x.toFixed(2));
				        $("#y").text(pos.y.toFixed(2));
				        
						if (item) {
						    if (previousPoint != item.datapoint) {
						        previousPoint = item.datapoint;
						        
						        $("#tooltip").remove();
						        var x = item.datapoint[0].toFixed(0),
						            y = item.datapoint[1].toFixed(2);
						        
						        showTooltip(item.pageX, item.pageY,
						                    item.series.label + " (mese " + x + "): " + y);
						    }
						}
						else {
						    $("#tooltip").remove();
						    previousPoint = null;            
						}
						
				    });
				    </script>			
						
				<?php
	
				echo '</fieldset>';
				
				$showDesc = 0;
			
				break;


			// MOSTRACAT: mostra un grafico con i movimenti delle categorie nel mese
			case "mostracat":
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
				
				//individua il conto richiesto per l'utente
				if (isset($_GET['accountid'])){
					if ($_GET['accountid'] == 0) unset($_GET['accountid']);
				}
				if (isset($_GET['accountid'])){
					$account = Account::first(
						array(
							'conditions' => array(
								'user_id = ? AND id = ?', 
								$_SESSION['userid'],
								$_GET['accountid']
							)
						)				
					);					
				} 
				else{
					$account = Account::first(
						array(
							'conditions' => array(
								'user_id = ?', 
								$_SESSION['userid']
							)
						)				
					);
					debug('Non passato conto, scelto il primo');	
				}
				
				if (!isset($account)){
					err('Nessun conto selezionato o conto non valido.');
					break;
				}
				
				//imposta anno e mese per analisi
				$month = date('m');
				$year = date('Y');
				
				if (isset($_GET['month'])){
					if (is_numeric($_GET['month']) && $_GET['month'] <= 12){
						$month = $_GET['month'];
					}
				}
				if (isset($_GET['year'])){
					if (is_numeric($_GET['year'])){
						$year = $_GET['year'];
					}
				}
				
				$beginMonth = date("Y-m-d", mktime(0, 0, 0, $month, 1, $year));
				$endMonth = date("Y-m-d", mktime(0, 0, 0, $month+1, 1, $year));
				
				//inizializzo array vuoto con indici pari a tutte le categorie
				//dell'utente
				$userCategories = Category::find(
					'all',
					array(
						'conditions' => array(
							'user_id = ?',
							$_SESSION['userid']
						)
					)
				);
				
				$categories = array();
				foreach ($userCategories as $userCategory){
					$categories[$userCategory->id] = 0;
					$catNames[$userCategory->id] = $userCategory->name;
				}
				
				//individuo tutte le transazioni nel periodo desiderato
				$transactions = Transaction::find(
					'all',
					array(
						'conditions' => array(
							'account_id = ? AND date >= ? AND date < ? AND auto = ?', 
							$account->id,
							$beginMonth,
							$endMonth,
							0
						)
					)				
				);			
				
				foreach($transactions as $transaction){
					if (isset($categories[$transaction->category_id])){
						$categories[$transaction->category_id] += $transaction->import;
					}
				}
				
				//stampa grafico con categorie
				


					?>
					
					<!-- spazio per costruzione legenda grafico -->
					<div id="legenda" style="width:400px;margin-left:10px;margin-right:10px;"></div>
					
					<!-- spazio per costruzione canvas grafico -->
					<div id="placeholder" style="width:600px;height:300px;"></div>
					
					
					<script>				
					$(function () {
					    				    
						<?php 
							//crea le variabili con i dati
							$i = 0;
							foreach($categories as $category){
								echo 'var d'.$i.' = [];';
								echo 'd'.$i.'.push(['.$i.','.$category.']);';
								$i++;
							}
						?>    
					
					    $.plot($("#placeholder"), [
					    
					    <?php
					    	//inizializza le serie
					    	$i = 0;
							foreach($catNames as $catName){
								echo '{';
								echo 'data: d'.$i.',';
								echo 'label: "'.$catName.'",';
								echo 'bars: { show: true },';
								echo '},';
								$i++;
							}				    
					    ?>
	
					    ],{
							xaxis: {
		            			ticks: [
		            			
		            			<?php
		            				$i = 0.5;
		            				foreach($catNames as $catName){
		            					echo '['.$i.',"'.$catName.'"],';
		            					$i++;
		            				}
		            			?>

								]
	        				},
	        				grid: {
	            				backgroundColor: { 
            						colors: ["#fff", "#eee"] 
            					}
	       		 			},
							legend: {
								container: legenda
							},	
							grid: { 
								hoverable: true, 
								clickable: true 
							}		
	       		 
					    });
					});	
					</script>
					<?php




				
				
				$showDesc = 0;

				break;

			
			
			default:
				err("Passato in GET parametro action sconosciuto: ".$_GET['action']);
				break;
			
		}//fine switch $action
			
	}//fine isset($_GET['action'])
	
	//**** parametri POST ******************************************************************

	if (isset($_POST['action'])){
		
		$action = $_POST['action'];
		
		switch ($action){
			

			default:
				err("Passato in POST parametro action sconosciuto: ".$_POST['action']);
				break;

		}//fine switch $action
			
	}//fine isset($_POST['action'])
	
	
	//**** corpo della pagina ***************************************************************
	
	switch ($showDesc){
		case 1:
		
			?>
				La sezione "grafici" contiene un sacco di bei grafici colorati. Usa la toolbar qui sopra per sceglierli, e nel frattempo sentiti libero di suggerirmi un contenuto più interessante per questa pagina di presentazione...
			<?php
		
			break;
		}

?>

<?php
	include('footer.inc.php');
?>