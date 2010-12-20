<?php
	$pagedata['pagetitle'] = "My Personal Budget";
	$pagdata['onlyadmin'] = 0;
	include('header.inc.php');
?>

<strong>My Personal Budget</strong> è un progettino software che mira a realizzare un sistema completo per la gestione della contabilità personale.
<p>

<?php
	Notice("Account di prova<br>username: mario - password: mille<br>username: admin - password: mille");
?>

Tra le features implementate abbiamo:
<ul>
<li>Gestione di più conti separati.
<li>Transazioni categorizzate e con la possibilità di inserire un numero arbitrario di tag.
<li>Vista delle transazioni per mese, con saldo a inizio mese, a fine mese e alla data corrente.
<li>Vista delle transazioni per categoria.
</ul> 

Numerose altre features sono inoltre in fase di implementazione:
<ul>
<li>Vista con filtro selettivo per tag.
<li>Produzione di grafici.
<li>Produzione di resoconti in PDF.
</ul>

..e tutto quello che mi verrà in mente. Ovviamente sono ben accetti suggerimenti :-)
<ul>
<li>email: <a href="mailto:mario.piccinelli@gmail.com">mario.piccinelli@gmail.com</a>
<li>issue tracking: <a href="https://github.com/PicciMario/My-Personal-Budget/issues">https://github.com/PicciMario/My-Personal-Budget/issues</a>
</ul>

<?php
	include('footer.inc.php');
?>