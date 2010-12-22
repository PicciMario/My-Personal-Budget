	<br>
	</div>
	
	<script>
		$(function() {
			$( ".toolbarButton" ).button();
			
			$( ".toolbarButtonLeft" ).button({
				text:false,
				icons: {
					primary: "ui-icon-seek-prev"
				}
			});
			
			$( ".toolbarButtonRight" ).button({
				text:false,
				icons: {
					primary: "ui-icon-seek-next"
				}
			});
			
			$( ".toolbarButtonNew" ).button({
				icons: {
					primary: "ui-icon-circle-plus"
				}
			});
			
			$( ".toolbarButtonDelete" ).button({
				icons: {
					primary: "ui-icon-circle-close"
				}
			});
			
			$( ".toolbar" ).addClass("ui-widget-header ui-corner-all");
			
   			$("a#logoutlink").button({
				icons: {
					primary: "ui-icon-power"
				}	
   			});

		});
	</script>
	
	<div class="column span-3 last">
	spazio in affitto :-)
	</div>
	
	<div class="span-24 last">
	&nbsp;
	</div>
	
	<hr>
	
	<!-- Footer -->
	<div class="span-16">
		&nbsp;
	</div>
	<div class="span-8 last" style="font-style:italic;">
		<p>
		Copyright (c) 2010 <a href="mailto:mario.piccinelli@gmail.com">mario.piccinelli@gmail.com</a><br>
		Rilasciato sotto <a href="http://it.wikipedia.org/wiki/Licenza_MIT">licenza MIT</a><br>
		Scarica l'ultima versione da <a href="https://github.com/PicciMario/My-Personal-Budget">GitHub</a>
		</p>
	</div>
	
	<hr>
	
</div>
</body>
</html>