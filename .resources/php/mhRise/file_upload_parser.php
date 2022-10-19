<?php
$fileName = $_FILES["fileToUpload"]["name"]; // The file name
$fileTmpLoc = $_FILES["fileToUpload"]["tmp_name"]; // File in the PHP tmp folder
$fileMimeType = $_FILES["fileToUpload"]["type"]; // The type of file it is
$fileType = strtolower(pathinfo($fileName,PATHINFO_EXTENSION)); // The extension of the file
$fileSize = $_FILES["fileToUpload"]["size"]; // File size in bytes
$fileErrorMsg = $_FILES["fileToUpload"]["error"]; // 0 for false... and 1 for true
$name = strip_tags(strtolower($_REQUEST['name']));
$name = trim($name);

if($name == "default"){
    echo "Algo ha fallado.";
    exit();
}

if (!$fileTmpLoc) { // if file not chosen
    echo "ERROR: Por favor, selecciona un archivo.";
    exit();
}
$badAgents = array('curl/7.64.0'); //Block curl user agent
if(in_array($_SERVER['HTTP_USER_AGENT'],$badAgents)) {
    die('Go away');
}
$whitelist = array('png');
if(in_array($fileType, $whitelist) == false){
    echo "Tipo de archivo no permitido.";
    exit();
}
if(move_uploaded_file($fileTmpLoc, "/mnt/disk/.resources/img/mhRise/players/$name.png")){
	chmod("/mnt/disk/.resources/img/mhRise/players/$name.png", 0666);
    echo "Subido correctamente.";
} else {
    echo "Algo ha fallado.";
}
?>