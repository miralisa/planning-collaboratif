<?php

// Connexion à la base de données
$dbh = new PDO('mysql:host=fdb15.biz.nf;dbname=2252095_planning', '2252095_planning', 'planningIHM2016');

// Action à faire
$type = $_POST['type'];

if($type == 'new')
{
	$startdate = $_POST['startdate'].'+'.$_POST['zone'];
	$title = $_POST['title'];
	$color = $_POST['color'];
	$enddate = $_POST['end'];

        $insert = mysqli_query($dbh,"INSERT INTO evenement VALUES('$title','$startdate','$enddate','$color')");

	//$requete = $dbh->prepare("SELECT MAX(id) FROM evenement LIMIT 1");
	//$requete->execute();
	//$nouvelID = $requete->fetchAll();
	//$nouvelID = ($nouvelID[0] != null ? $nouvelID[0] + 1 : 1);

	//$requete = $dbh->prepare("INSERT INTO evenement VALUES('".$nouvelID."', 1, '".$title."', '".$startdate."', '".$enddate."', '".$color."'");
	//$requete->execute();
$lastid = mysqli_insert_id($dbh);
	
	echo json_encode(array('status'=>'success','eventid'=>$lastid));
}

if($type == 'changetitle')
{
	$eventid = $_POST['eventid'];
	$title = $_POST['title'];

	$requete = $dbh->prepare("UPDATE evenement SET titre='".$title."' WHERE id='".$eventid."'");
	$update = $requete->execute();

	if($update)
		echo json_encode(array('status'=>'success'));
	else
		echo json_encode(array('status'=>'failed'));
}

if($type == 'resetdate')
{
	$title = $_POST['title'];
	$startdate = $_POST['start'];
	$enddate = $_POST['end'];
	$eventid = $_POST['eventid'];

	$requete = $dbh->prepare("UPDATE evenement SET titre='".$title."', debut='".$startdate."', fin='".$enddate."' WHERE id='".$eventid."'");
	$update = $requete->execute();

	if($update)
		echo json_encode(array('status'=>'success'));
	else
		echo json_encode(array('status'=>'failed'));
}

if($type == 'remove')
{
	$eventid = $_POST['eventid'];

	$requete = $dbh->prepare("DELETE FROM evenement WHERE id='".$eventid."'");
	$delete = $requete->execute();

	if($delete)
		echo json_encode(array('status'=>'success'));
	else
		echo json_encode(array('status'=>'failed'));
}

if($type == 'fetch')
{
	$evenement = array();

	$requete = $dbh->prepare("SELECT * FROM evenement");
	$requete->execute();

	$events = $requete->fetchAll();
	foreach($events as $event){
		$e = array();
		$e['id'] = $fetch['id'];
		$e['title'] = $fetch['title'];
		$e['start'] = $fetch['startdate'];
		$e['end'] = $fetch['enddate'];
		$e['color'] = $fetch['color'];

		array_push($evenement, $e);
	}
	echo json_encode($events);
}

?>