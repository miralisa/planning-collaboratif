<?
	//include_once("utils.php"); // $_SESSION, connexion BDD & requetes associées
	
	/* Déclaration des variables pour cette page */
	$header 			= "Planning collaboratif";
	$logo		 		= "<a href='./index.php' alt='accueil'><img id='lg' src='/img/logo.png'></a>";
	$menuG 				= "Mon profil";	
        $calendar                       ="";
     	$menuD				= "<a href='#'><img class='icon' src='/img/profile.png'></a>
                                           <a href='./calendar.php'><img class='icon' src='/img/calendar.png'></a>
                                           <a href='#'><img class='icon' src='/img/contact.png'></a>
                                           ";
	$footer 			= "";
	
	/* Stockage de la vue à charger dans un buffer */
	ob_start();
	include "./html/calendar.html";
	$html = ob_get_contents();
	ob_end_clean();
	 
	/* Initialisation du tableau pour le remplacement */
	$remplacement = array(
	  '%header%' 			=> $header,
	  '%logo%'			=> $logo,
	  '%menuG%' 			=> $menuG,
	  '%calendar%' 			=> $calendar,
	  '%menuD%' 			=> $menuD,
	  '%footer%' 			=> $footer
	);
	
	/* Remplacement des variables de la vue par les données de la page */ 
	$html = str_replace(array_keys($remplacement), array_values($remplacement), $html);
	 
	echo $html;
?>				
