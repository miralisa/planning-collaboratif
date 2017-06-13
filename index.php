<?php

// Récupération des données de session si elles existent
// Crée une session sinon
session_start();

if (isset($_POST["supprimer"])) {
	unset($_SERVER["REQUEST_URI"]);
}
// Si l'utilisateur a cliqué sur le bouton de déconnexion, détruit sa session
if(isset($_POST["deconnecte"])){
	$_SESSION = array();
	session_destroy(); 
	$page="accueil";
}

/**
* Ouverture de connexion à la base de données
**/
function openBDD(){
	try{
		$dbh = new PDO('mysql:host=fdb15.biz.nf;dbname=2252095_planning', "2252095_planning", "planningIHM2016");
	}
	catch (PDOException $e) {
		print "Erreur !: " . $e->getMessage() . "<br/>";
		die();
	}
	// S'il y a retour c'est que la connexion s'est bien passée
	return $dbh;
}

/**
* Fermeture de connexion à la base de données
* A affecter à la variable contenant la connexion
**/
function closeBDD(){
	return null;
}

/**
* Tente de connecter l'utilisateur avec le $mail et le $mdp fournis
* Renvoie vrai si les identifiants correspondent
* faux sinon
* Aucune vérification n'est faite dans la fonction sur le formatage du $mail car inutile sur le bon fonctionnement de la fonction
*
* $mail : l'adresse mail fournie par l'utilisateur
* $mdp : le mot de passe fourni par l'utilisateur
**/
function connexionUtilisateur($mail, $mdp){
	$dbh = openBDD();
	$requete = $dbh->prepare("SELECT mail FROM utilisateur WHERE mail=:utilmail AND mdp=:utilmdp");
	$requete->bindValue(':utilmail', $mail, PDO::PARAM_STR);
	$requete->bindValue(':utilmdp', $mdp, PDO::PARAM_STR);
	$requete->execute();
	
	
	if($requete){
		$_POST["erreurCo"] = "";
	}else{
		$_POST["erreurCo"] = true;
	}
	var_dump($_POST["erreurCo"]);
	
	$dbh = closeBDD();
	
	return $requete->fetchAll();
}


/**
* Crée un compte sur la $dbh avec les $données fournies
*
* $dbh : BD dans laquelle on ajoute le compte
* $donnees : ensemble des données utilisées pour créer le compte
*
* Retourne 0 si l'adresse mail est déjà utilisée
* Retourne 1 si le compte a été créé correctement
* Retourne 2 si la création du compte a échouée
**/
function creationCompte($donnees){
	// Ouverture de la connexion à la base de données
	$dbh = openBDD();
	
	// Vérifie que l'adresse mail n'est pas déjà utilisée
	$requete = $dbh->prepare("SELECT mail FROM utilisateur WHERE mail=:utilmail");
	$requete->bindValue(":utilmail", $donnees["mail"], PDO::PARAM_STR);
	$requete->execute();
	$doublon = $requete->fetchAll();
	// Si l'adresse est déjà présente, un nouveau compte n'est pas créé avec cette adresse
	if(isset($doublon[0])){
		return 0;
	}
	
	// Ajoute le compte dans la BD
	$requete = $dbh->prepare("INSERT INTO utilisateur VALUES ('".$donnees["mail"]."', '".$donnees["mdp"]."', '".$donnees["nom"]."', '".$donnees["prenom"]."', '".$donnees["role"]."', '".$donnees["img"]."')");
	$requete->execute();
	
	// Vérifie que la création a bien été effectuée
	$requete = $dbh->prepare("SELECT mail FROM utilisateur WHERE mail=:mail");
	$requete->bindValue(":mail", $donnes["mail"], PDO::PARAM_STR);
	$requete->execute();
	$inscrit = $requete->fetchAll();
	
	$dbh = closeBDD();
	
	if(isset($inscrit[0])){return 1;}else{return 2;}
	
	
}

/**
* Charge le .php correspondant à la page à afficher
*
* $page : nom du fichier .php, 404 not found par défaut
* Il faut rajouter des "case" si on rajoute des pages
*/
function afficher($page){
	switch($page){
		case "accueil":
		include("./php/accueil.php");
		break;
		case "profil";
		include("./php/profil.php");
		break;
		
		default :
		include("./php/notfound.php");
	}
}

extract($_POST); // Récupération des données envoyées

// Si l'utilisateur souhaite s'inscrire, on essaye de créer son compte
if(isset($inscription)){
	$resultat = creationCompte($_POST);
	if($resultat == 0){
		echo '<script>alert("Cette adresse mail est déjà utilisée !");</script>';
	}
}

// Ajout d'un planning par un utilisateur
if(strlen($_POST["creationPlanning"]) > 1){
	// Réinitialisation de la connexion si besoin et création
	$dbh = openBDD();
	
	// Récupération du plus grand id pour l'incrémentation
	$requete = $dbh->prepare("SELECT MAX(id) FROM planning");
	$requete->execute();
	
	$nouvelID = $requete->fetchAll();
	$nouvelID = $nouvelID[0][0] + 1;
	
	// Création du nouveau planning
	$requete = $dbh->prepare("INSERT INTO planning VALUES ('".$nouvelID."', '".$_POST["nom"]."')");
	$requete->execute();
	
	// Création du chef pour ce planning
	$requete = $dbh->prepare("INSERT INTO chef_planning VALUES ('".$_SESSION["mail"]."', '".$nouvelID."')");
	$requete->execute();
	
	// Récupération de la couleur à associer à l'utilisateur
	$requete = $dbh->prepare("SELECT code FROM codeCouleur ORDER BY code LIMIT 1");
	$requete->execute();
	
	$couleur = $requete->fetchAll();
	$couleur = $couleur[0][0];
	
	// Ajout du créateur du planning dans les utilisateurs de celui-ci
	$requete = $dbh->prepare("INSERT INTO utilisateur_planning VALUES ('".$_SESSION["mail"]."', '".$nouvelID."', '".$couleur."')");
	$insertion = $requete->execute();
	
	$dbh = closeBDD();
	
	// si insertion
	if($insertion){
		$_SESSION["ajoutBDD"] = "#feedbackPositif";
	}else{
		$_SESSION["ajoutBDD"] = "#feedbackNegatif";
	}
}

// Envoi d'une invitation à un collaborateur
if(isset($_POST["invitation"])){
	
        if(isset($_GET["idPS"])){
	$dbh = openBDD();
	$req = $dbh->prepare("SELECT COUNT(mail) FROM utilisateur WHERE mail = '".$_POST["mail"]."'");
	$req->execute();
	$num_rows = $req->fetchColumn();
	

	if($num_rows != 0){
		
		$req = $dbh->prepare("SELECT code FROM codeCouleur WHERE code NOT IN (SELECT couleur FROM utilisateur_planning WHERE planning ='".$_GET["idPS"]."') ORDER BY code LIMIT 1");
		
		$req->execute();
		
		$couleur = $req->fetchAll();
		$couleur = $couleur[0][0];
		
		var_dump($couleur);
		$req = $dbh->prepare("INSERT INTO utilisateur_planning VALUES ('".$_POST['mail']."','".$_GET['idPS']."','".$couleur."')");
		$req->execute();
		
	}
	}else{ echo '<script> alert("Vous n\'avez selectionné aucun agenda. En cas de difficulté, utilisez la F.A.Q. ."); </script>'; }
	
	$to = $_POST["mail"];
	
	$object = 'Rejoins mon calendrier collaboratif !';
	
	$message = "Bonjour ! \n";
	$message .= "Ceci est une invitation pour mon planning collaboratif, rejoins moi vite !\n";
	$message .= "\n\n";
	$message .= "Cette invitation vous a été envoyée par ".$_SESSION["mail"].".";
	
	$headers  = 'From: adresse de l expediteur'."\r\n";
	$headers .= 'Reply-To: adresse destinee a la reponse'."\r\n";
	$headers .= 'MIME-Version: 1.0' . "\r\n";
	$headers .= 'Content-type: text/html; charset=utf-8' . "\r\n";
	
	// La fonction mail n'est pas opérationnelle sur ce serveur
	// La version gratuite utilisée ne permet pas de modifier le php.ini
	mail($to, $object, $message, $headers);
	
	// si insertion
	// on affiche succès
	// sinon
	// on affiche échec
}

// Tentative de connexion de l'utilisateur
if(isset($_POST["mail"]) and isset($_POST["mdp"]) and !isset($_POST["connecte"])){
	
	$dbh = openBDD();
	
	$requete = $dbh->prepare("SELECT * FROM utilisateur WHERE mail='".$_POST["mail"]."' AND mdp='".$_POST["mdp"]."'");
	$requete->execute();
	
	$resultat = $requete->fetchAll();
	$resultat = $resultat[0];
	
	
	if(isset($resultat["mail"])){
		$_SESSION["connecte"] = "connecte";
		$_SESSION["mail"] = $resultat["mail"];
		$_SESSION["nom"] = $resultat["nom"];
		$_SESSION["prenom"] = $resultat["prenom"];
		$_SESSION["role"] = $resultat["role"];
		
		$page = "profil";
	}else{
		
		$_SESSION["erreurCo"] = true;
	}
	
	$dbh = closeBDD();
}

if(isset($_GET["idPS"])){
	$page = "profil";
}

if(isset($_SESSION["mail"])){$page = "profil";}


// Si aucune page n'est précisée, on redirige de base vers l'accueil
if(!isset($page)){$page = "accueil";} 

// On affiche le contenu correspondant à la page
afficher($page);

?>