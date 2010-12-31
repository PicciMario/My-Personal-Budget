<?php
	$pagedata['pagetitle'] = "Graphs";
	$pagedata['onlyadmin'] = 0;
	$pagedata['onlylogged'] = 1;
	include('header.inc.php');
?>

<script language="javascript" type="text/javascript" src="flot/jquery.flot.min.js"></script>
<!--[if IE]><script language="javascript" type="text/javascript" src="flot/excanvas.min.js"></script><![endif]-->

<?php
	
	$showDesc = 1;
	
	?>
		<div class="toolbar">
			<a href="graphs.php?action=mostrasaldi" class="toolbarButton" id="addCategory">Saldi mensili</a>
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
				$accounts = Account::find(
					'all',
					array(
						'conditions' => array(
							'user_id = ?', $_SESSION['userid']
						)
					)				
				);
				
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
				<!-- spazio per costruzione legenda grafico -->
				<div id="legenda" style="width:400px;margin-left:10px;margin-right:10px;"></div>
				
				<!-- spazio per costruzione canvas grafico -->
				<div id="placeholder" style="width:600px;height:300px;"></div>

				<hr>

				<!-- Barra di cambio anno -->
				<div align=center>
				<div>
					<a href="graphs.php?action=mostrasaldi&year=<?php echo $year-1 ?>" class="toolbarButtonLeftText" id="addCategory"><?php echo $year-1 ?></a>
					<a href="graphs.php?action=mostrasaldi&year=<?php echo date("Y") ?>" class="toolbarButton" id="addCategory">Anno corrente <?php echo date("Y") ?></a>
					<a href="graphs.php?action=mostrasaldi&year=<?php echo $year+1 ?>" class="toolbarButtonRightText" id="addCategory"><?php echo $year+1 ?></a>
				</div>
				</div>

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