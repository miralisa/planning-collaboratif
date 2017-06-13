<?php

if(!isset($_SESSION["connecte"])){
        include('/home/www/planning-collaboratif.co.nf/html/accesInterdit.html');
        exit();
}

function listeAgendas($mail){
        $dbh = new PDO('mysql:host=fdb15.biz.nf;dbname=2252095_planning', '2252095_planning', 'planningIHM2016');
        
        $requete = $dbh->prepare("
                SELECT titre,id 
                FROM planning p, utilisateur_planning u 
                WHERE p.id = u.planning 
                AND utilisateur='".$mail."'");
        $requete->execute();
        
        $resultats = $requete->fetchAll();
        
        $contenu = "";
        if(isset($resultats)){ // si l'utilisateur a des agendas
        
        foreach($resultats as $agenda){
                $contenu .= "<form style='text-align: center;' method='get'>";
                $contenu .= "<input type='hidden' value='".$agenda[1]."' name='idPS'>";
                $contenu .= "<li><input type='submit' class='transpBouton' value='".$agenda[0]."' > </li>";
                $contenu .= "</form>";
        }
        
}else{
        $contenu = "<li> Vous n'avez encore aucun agenda ! </li>";
}

        $dbh = null; // ferme la connexion à la BD
        
        return $contenu;
}

function collaborateurs($mail){
        $dbh = new PDO('mysql:host=fdb15.biz.nf;dbname=2252095_planning', '2252095_planning', 'planningIHM2016');

        if(isset($_GET["idPS"])){
        
        $idPlanningSelected = $_GET["idPS"];
        $requete = $dbh->prepare("
                SELECT utilisateur, couleur
                FROM utilisateur_planning
                WHERE planning = ".$idPlanningSelected."");
        

        $requete->execute();

        $collaborateurs = $requete->fetchAll();
        $contenu = "";
        foreach($collaborateurs as $collaborateur){
                //var_dump($collaborateur);
                $contenu .= "<p style='color:".$collaborateur["couleur"].";'>".$collaborateur["utilisateur"]."&nbsp &nbsp</p>";
        }
        
        return $contenu;
        }
}



if (isset($_POST["supprimer"])) {
       
       
                $dbh = new PDO('mysql:host=fdb15.biz.nf;dbname=2252095_planning', '2252095_planning', 'planningIHM2016');
                $requete = $dbh->prepare("
                        DELETE 
                        FROM planning 
                        WHERE id='".$_GET["idPS"]."'");
                $requete->execute();
                unset($_SERVER["REQUEST_URI"]);
                header('Refresh:0;url=../index.php');
//                var_dump($_SERVER);
        
}

function calendrier($mail){
        $contenu = "<br><br><br>";
        if(isset($_GET["idPS"])){
                $dbh = new PDO('mysql:host=fdb15.biz.nf;dbname=2252095_planning', '2252095_planning', 'planningIHM2016');
                $idPS = $_GET['idPS'];
                $requete = $dbh->prepare("SELECT utilisateur FROM chef_planning WHERE utilisateur='".$mail."' AND planning='".$idPS."'");
                $requete->execute();
                $resultat = $requete->fetch();
                if($resultat[0] == $mail){
                        $contenu .= "<button id='cmode'>Mode création de créneaux</button>";
                        $contenu .= "<button id='smode'>Mode suppression de créneaux</button>";
                }
                
                $contenu .= "<button id='emode'>Mode création d'évènements</button>";
                $contenu .= "<button id='refresh'>Rafraichir</button>";
                $contenu .= "<div id='calendar'></div>";
                $contenu .= "<div id='eventContent' title='Details d évènement' style='display:none;'>";
                $contenu .= "Commence à <span id='startTime'></span><br>";
                $contenu .= "Fini à <span id='endTime'></span><br><br>";
	        $contenu .= "<p id='eventInfo'></p>";
	        $contenu .= "<form id='edit'  style='display: none;'>";
		$contenu .= "Title : <input type='text' id='title'><br>";
		$contenu .= "<input type='button' id='btn' value='Valider'>";
		$contenu .= "</form>";
	 	$contenu .= "</div>";
                $contenu .= "<div id='Discussion' title='Discussion' style='display:none;'>";
                $contenu .= "<div id='messages'>";
                $contenu .= "</div>";
                $contenu .= "Message:<textarea name='message' id='message'></textarea>";
                $contenu .= "<br> <button id='envoi'>Envoyer</button>";
                $contenu .= "</div>";
                
        }
        return $contenu;
}

if(strlen($_SESSION["nom"]) > 0 and strlen($_SESSION["prenom"]) > 0){$utilisateur = $_SESSION["nom"]." ".$_SESSION["prenom"];}
else{$utilisateur = $_SESSION["mail"];}
$role = "";
if(strlen($_SESSION["role"] > 0)){$role = $_SESSION["role"];}

$agendas = listeAgendas($_SESSION["mail"]);

$collaborateurs = collaborateurs($_SESSION["mail"]);

$calendrier = calendrier($_SESSION["mail"]);

/* Stockage de la vue à charger dans un buffer */
ob_start();
include "/home/www/planning-collaboratif.co.nf/html/profil.html";
$html = ob_get_contents();
ob_end_clean();

/* Initialisation du tableau pour le remplacement */
$remplacement = array(
        '%utilisateur%'         => $utilisateur,
        '%role%'                => $role,
        '%agendas%'             => $agendas,
        '%collaborateurs%'      => $collaborateurs,
        '%calendrier%'          => $calendrier
        );

/* Remplacement des variables de la vue par les données de la page */ 
$html = str_replace(array_keys($remplacement), array_values($remplacement), $html);

echo $html;
?>                              