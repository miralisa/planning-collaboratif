<?php

$dbh = new PDO('mysql:host=fdb15.biz.nf;dbname=2252095_planning', "2252095_planning", "planningIHM2016");

$requete = $dbh->prepare("SELECT title, startdate, enddate
							FROM calendar
							WHERE planning=1");
$requete->execute();

$resultat = $requete->fetchAll();

echo json_encode($resultat);

$dbh = null;

?>