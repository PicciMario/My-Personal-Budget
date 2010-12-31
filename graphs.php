<?php
	$pagedata['pagetitle'] = "Graphs";
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
			
			?>
				
			<div class="toolbar">
				<a href="graphs.php" class="toolbarButton" id="addCategory">Saldi mensili</a>
			</div>
		
			<?php
			
			//elenco conti per l'utente
			$accounts = Account::find(
				'all',
				array(
					'conditions' => array(
						'user_id = ?', $_SESSION['userid']
					)
				)				
			);
			
			//saldi 2010
			$year = 2010;
			$beginYear = date("Y-m-d", mktime(0, 0, 0, 1, 1, $year));
			$endYear = date("Y-m-d", mktime(0, 0, 0, 1, 1, $year+1));
			
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
			
				<script language="javascript" type="text/javascript" src="flot/jquery.flot.min.js"></script>
				<!--[if IE]><script language="javascript" type="text/javascript" src="flot/excanvas.min.js"></script><![endif]-->
				<div id="legenda" style="width:400px;margin-left:10px;margin-right:10px;"></div>
				<div id="placeholder" style="width:600px;height:300px;"></div>

				<script>				
				$(function () {
				    				    
					<?php 
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
            				backgroundColor: { colors: ["#fff", "#eee"] }
       		 			},
						legend: {
							container: legenda
						}			
       		 
				    });
				});
				</script>
				
			<?php

			echo '</fieldset>';
		
			break;
		}

?>

<?php
	include('footer.inc.php');
?>