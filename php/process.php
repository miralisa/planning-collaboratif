<?php

session_start();
$con = mysqli_connect('fdb15.biz.nf','2252095_planning','planningIHM2016','2252095_planning');

$type = $_POST['type'];
$idPS = $_POST['idPS'];

if($type == 'newe')
{
	$startdate = $_POST['startdate'];
	$title = $_POST['title'];
	$enddate = $_POST['end'];
        $auteur = $_SESSION["mail"];
        
        
        //Recherche d'un creneau englobant l'evenement
        $qcreneau = mysqli_query($con, "SELECT id FROM creneau WHERE debut <= '$startdate' AND fin >= '$enddate'");
        $creneau_result = mysqli_fetch_array($qcreneau, MYSQLI_ASSOC);
        $creneauid = $creneau_result['id'];
        
        if($creneauid != null){
        
                $qcolor = mysqli_query($con, "SELECT couleur FROM utilisateur_planning WHERE utilisateur = '$auteur' AND planning = '$idPS' LIMIT 1");
                $color_result = mysqli_fetch_array($qcolor, MYSQLI_ASSOC);
                $color = $color_result['couleur'];
                $insert = mysqli_query($con,"INSERT INTO calendar(`title`, `startdate`, `enddate`, `color`, `creneau`,`auteur`) VALUES('$title','$startdate','$enddate','$color', '$creneauid', '$auteur' )");
                $lastid = mysqli_insert_id($con);
                echo json_encode(array('status'=>'success','eventid'=>$lastid));
        }/*else{
                echo json_encode(array('status'=>'error'));
        }*/
}

if($type == 'newc')
{
	$startdate = $_POST['startdate'];
	$enddate = $_POST['end'];
	$insert = mysqli_query($con,"INSERT INTO creneau(`planning`, `debut`, `fin`) VALUES('$idPS','$startdate','$enddate')");
	$lastid = mysqli_insert_id($con);
	echo json_encode(array('status'=>'success','creneauid'=>$lastid, 'bonjour'=>$idPS));
}

if($type == 'suppc')
{
	$startdate = $_POST['startdate'];
	$enddate = $_POST['end'];
        $qsuppc = mysqli_query($con,"SELECT id FROM creneau WHERE debut BETWEEN '$startdate' AND '$enddate' OR fin BETWEEN '$startdate' AND '$enddate'");
	while($qsuppc_res = mysqli_fetch_array($qsuppc, MYSQLI_ASSOC)){
                $creneauid = $qsuppc_res['id'];
                $delete = mysqli_query($con,"DELETE FROM creneau where id='$creneauid'");
        }
              
}


if($type == 'changetitle')
{
	$eventid = $_POST['eventid'];
	$title = $_POST['title'];
	$update = mysqli_query($con,"UPDATE calendar SET title='$title' where id='$eventid'");
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
	$update = mysqli_query($con,"UPDATE calendar SET title='$title', startdate = '$startdate', enddate = '$enddate' where id='$eventid'");
	if($update)
		echo json_encode(array('status'=>'success'));
	else
		echo json_encode(array('status'=>'failed'));
}

if($type == 'remove')
{
	$eventid = $_POST['eventid'];
	$delete = mysqli_query($con,"DELETE FROM calendar where id='$eventid'");
	if($delete)
		echo json_encode(array('status'=>'success'));
	else
		echo json_encode(array('status'=>'failed'));
}

if($type == 'fetch')
{
	$events = array();
	$query1 = mysqli_query($con, "SELECT ca.id, title, startdate, enddate, color FROM calendar ca, creneau cr WHERE ca.creneau = cr.id AND cr.planning = '$idPS'");
	while($fetch1 = mysqli_fetch_array($query1,MYSQLI_ASSOC))
	{
            $e = array();
            $e['id'] = $fetch1['id'];
            $e['title'] = $fetch1['title'];
            $e['start'] = $fetch1['startdate'];
            $e['end'] = $fetch1['enddate'];
            $e['color'] = $fetch1['color'];
        
            array_push($events, $e);
	}
        
        
        $query2 = mysqli_query($con, "SELECT * FROM creneau WHERE planning = '$idPS'");
	while($fetch2 = mysqli_fetch_array($query2,MYSQLI_ASSOC))
	{
            $e = array();
            $e['id'] = $fetch2['id'];
            $e['planning'] = $fetch2['planning'];
            $e['start'] = $fetch2['debut'];
            $e['end'] = $fetch2['fin'];
            $e['color'] = 'rgba(137, 255, 184, 0.8)';
            $e['rendering'] = 'background';
        
            array_push($events, $e);
	}
	echo json_encode($events);
}

if($type == 'envoi'){
        $auteur = $_SESSION['mail'];
        $contenu = $_POST['contenu'];
        $eventid = $_POST['eventid'];
        
        $qcreneau = mysqli_query($con, "SELECT creneau FROM calendar WHERE id = '$eventid'");
        $cresult = mysqli_fetch_array($qcreneau,MYSQLI_ASSOC);
        $creneauid = $cresult['creneau'];
        
        $date = $_POST['date'];
        
        $insert = mysqli_query($con,"INSERT INTO message(`auteur`, `contenu`, `creneau`, `date`) VALUES('$auteur','$contenu','$creneauid','$date')");
	$lastid = mysqli_insert_id($con);
	echo json_encode(array('status'=>'success','creneauid'=>$lastid, 'bonjour'=>$idPS));
}

if($type == 'fetchMessage'){
        $eventid = $_POST['eventid'];
        
        $qcreneau = mysqli_query($con, "SELECT creneau FROM calendar WHERE id = '$eventid'");
        $cresult = mysqli_fetch_array($qcreneau,MYSQLI_ASSOC);
        $creneauid = $cresult['creneau'];
        
        $messages = "";
        $qmessage = mysqli_query($con, "SELECT * FROM message WHERE creneau = '$creneauid'");
        while($mresult = mysqli_fetch_array($qmessage,MYSQLI_ASSOC)){
                $messages .= "<p id=\"" . $mresult['id'] . "\">" . $mresult['auteur'] . " dit : " . $mresult['contenu'] . "</p>";
        }
        echo json_encode(array('status'=>'success', 'messages'=>$messages));
}

if($type == 'charger'){
        
    $id = $_POST['lastmessage'];
    $eventid = $_POST['eventid'];
        
    $qcreneau = mysqli_query($con, "SELECT creneau FROM calendar WHERE id = '$eventid'");
    $cresult = mysqli_fetch_array($qcreneau,MYSQLI_ASSOC);
    $creneauid = $cresult['creneau'];

    // on récupère les messages ayant un id plus grand que celui donné
    $requete = mysqli_query($con, "SELECT * FROM message WHERE id > '$id' AND creneau = '$creneauid' ORDER BY id ASC");
    $messages = "";


    // on inscrit tous les nouveaux messages dans une variable
    while($result = mysqli_fetch_array($requete, MYSQLI_ASSOC)){

        $messages .= "<p id=\"" . $result['id'] . "\">" . $result['auteur'] . " dit : " . $result['contenu'] . "</p>";

    }


    echo json_encode(array('status'=>'success', 'messages'=>$messages));
}
?>
