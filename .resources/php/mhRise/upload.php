<!DOCTYPE html>
<html>
<head>
	<title>MH Stats - Per-Server</title>
	<meta charset="utf-8">
	<link rel="stylesheet" href="/.resources/css/mh_style.css">
	<link rel="shortcut icon" type="image/x-icon" href=".resources/img/media/favicon.webp">
	<meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1" />
	<script>
			function _(el){
				return document.getElementById(el);
			}
			function uploadFile(){
				var file = _("fileToUpload").files[0];
				var name = _("name").value;
				var formdata = new FormData();
				formdata.append("fileToUpload", file);
				formdata.append("name", name);
				var ajax = new XMLHttpRequest();
				ajax.upload.addEventListener("progress", progressHandler, false);
				ajax.addEventListener("load", completeHandler, false);
				ajax.addEventListener("error", errorHandler, false);
				ajax.addEventListener("abort", abortHandler, false);
				ajax.open("POST", "/.resources/php/mhRise/file_upload_parser.php");
				ajax.send(formdata);
			}
			function progressHandler(event){
				_("loaded_n_total").innerHTML = "Subidos "+event.loaded+" bytes de "+event.total;
				var percent = (event.loaded / event.total) * 100;
				_("progressBar").value = Math.round(percent);
				_("status").innerHTML = Math.round(percent)+"% subido... espera pls";
			}
			function completeHandler(event){
				_("status").innerHTML = event.target.responseText;
				_("progressBar").value = 0;
			}
			function errorHandler(event){
				_("status").innerHTML = "Archivo probablemente no subido";
			}
			function abortHandler(event){
				_("status").innerHTML = "Subida cancelada";
			}
		</script>
</head>
<body>
<div class="image-hero-area" style="background: url('/.resources/img/mhRise/begimo.webp') no-repeat center center; background-size: cover;"></div>
<div id="container">
<div id="menu">
	<?php include("/mnt/disk/.resources/php/mhRise/menu.html"); ?>
</div>
<div id="content">
	<form method="post" enctype="multipart/form-data" class="dropzone">
		<div>
			<label>Nombre de Jugador: </label>
			<input type="text" name="name" id="name" required> 
		</div>
		
		<div class="fileInput">
			<input type="file" name="fileToUpload" id="fileToUpload" required>
		</div>
		<input type="button" value="Enviar" class="enviar" onclick="uploadFile()">
		<progress id="progressBar" value="0" max="100"></progress>
		<h3 id="status"></h3>
		<p id="loaded_n_total"></p>
		<div class="filetype">Solo se permite .webp</div>
	</form>

	<script>
			var uploadField = document.getElementById("fileToUpload");

			uploadField.onchange = function() {
    			if(this.files[0].size > 10485760){
       				alert("Archivo demasiado grande");
       				this.value = "";
    			};
    			var re = /(?:\.([^.]+))?$/;
    			var ext = re.exec(this.files[0].name)[1];
    			let whitelist = ['webp'];
    			if(!(whitelist.includes(ext))){
       				alert("Tipo de archivo no permitido");
       				this.value = "";
    			};
			};
		</script>
</div>
</div>
</body>
</html>