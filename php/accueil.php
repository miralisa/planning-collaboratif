<?php

ob_start();


include "/home/www/planning-collaboratif.co.nf/html/accueil.html";
$html .= ob_get_contents();
ob_end_clean();

$messageCo = "";
if(isset($_SESSION["erreurCo"])){
$messageCo = "Désolé, nous n'avons pas pu vous connecter avec l'adresse ".$_POST["mail"];
unset($_SESSION["erreurCo"]);
}

$html = str_replace('%messageCo%',$messageCo, $html);

echo $html;

?>