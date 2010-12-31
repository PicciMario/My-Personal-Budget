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
			
			$( ".toolbarButtonLeftText" ).button({
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
			
			$( ".toolbarButtonRightText" ).button({
				icons: {
					secondary: "ui-icon-seek-next"
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
			
			$( ".toolbarButtonAlert" ).button({
				icons: {
					primary: "ui-icon-alert"
				}
			});
			
			$( ".toolbar" ).addClass("ui-widget-header ui-corner-all");
			
   			$("a#logoutlink").button({
				icons: {
					primary: "ui-icon-power"
				}	
   			});
   			
   			$("p.info").click(function () {
		      $(this).hide("slow");
		      return true;
		    });
   			$("p.notice").click(function () {
		      $(this).hide("slow");
		      return true;
		    });
   			$("p.success").click(function () {
		      $(this).hide("slow");
		      return true;
		    });
   			$("p.error").click(function () {
		      $(this).hide("slow");
		      return true;
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