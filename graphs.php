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
				<a href="#" onclick="mostraDivSlow('addCategoryForm')" class="toolbarButtonNew" id="addCategory">Nuova categoria</a>
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
			
			//elenco conti per l'utente
			$account = Account::first(
				array(
					'conditions' => array(
						'user_id = ?', $_SESSION['userid']
					)
				)				
			);
			
			//saldi 2010
			$saldi = Transaction::find(
				'all',
				array(
					'conditions' => array(
						'account_id = ? AND auto = ? AND category_id = ?',
						$account->id,
						1,
						0						
					),
					'order' => 'date asc'
				)
			);
			
			echo '<fieldset><legend>'.$account->description.' - saldi a consuntivo</legend>';
			
			print "Numero elementi: ".count($saldi)."<br>";
			
			?>
				<script language="javascript" type="text/javascript" src="flot/jquery.flot.min.js"></script>
				<!--[if IE]><script language="javascript" type="text/javascript" src="flot/excanvas.min.js"></script><![endif]-->
				<div id="placeholder" style="width:600px;height:300px"></div>

				<script>				
				$(function () {
				    var d1 = [];
				    
				<?php 
					foreach ($saldi as $saldo) {
						echo 'd1.push(['.$saldo->date->format("m").','.$saldo->import.']);';
					}
				?>     
				             
				    $.plot($("#placeholder"), [
				        {
				            data: d1,
				            lines: { show: true },
				            points: { show: true}
				        }
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