<?php
$db_username        = 'vantur'; //database username
$db_password        = 'Sa6%Wa*bwH'; //dataabse password
$db_name            = 'otherdb'; //database name
$db_host            = '139.162.193.209'; //hostname or IP

$artist_id = isset($_GET['artist']) ? $_GET['artist']: "";
$mysqli = new mysqli($db_host, $db_username, $db_password, $db_name);
mysqli_set_charset($mysqli, 'utf8');
//Output any connection error
if ($mysqli->connect_error) {
	die('Error : ('. $mysqli->connect_errno .') '. $mysqli->connect_error);
}
//header('Content-Type: application/json');
$query="select dj_Master.artistID,artistName,dj_Master.eventID,eventName,date,startTime,endTime,dj_Master.venueID,venueName,venueAddress,venueLatitude,venueLongitude,city,country from dj_Master,dj_Artists,dj_Events,dj_Venues where dj_Master.artistID=dj_Artists.artistID and dj_Master.eventID=dj_Events.eventID and dj_Master.venueID=dj_Venues.venueID";
// Special Check For Artist
if(isset($artist_id) && !empty($artist_id)) $query .= " and dj_Master.artistID = ".$artist_id;
if($aResult = $mysqli->query($query)) {
    $events=array();
    $aData = array();
    $artists = [];
    $artist_counter = 0;
    while ($aRow = $aResult->fetch_assoc()) {
        $aData[] = $aRow;
    }
    $now = date('Y-m-d h:i:s');
    foreach($aData as $thisData) {
        $thisEvent = [];
        $eventTime = $thisData['date'] . " " . $thisData['startTime'];
        if(!in_array($thisData['artistID'], $artists)) {
            $events[$artist_counter]['artistDetail'] = ["id" => $thisData['artistID'], "artist" => $thisData['artistName']];
            $artists[] = $thisData['artistID'];
        }
        $thisEvent['eventDetail'] = [
            "eventID"=>$thisData['eventID'],
            "eventName"=>$thisData['eventName'],
            "date"=>$thisData['date'],
            "startTime"=>$thisData['startTime'],
            "endTime"=>$thisData['endTime']
        ];
        $thisEvent['venueDetail'] = [
            "venueID"=>$thisData['venueID'],
            "venueName"=>$thisData['venueName'],
            "venueAddress"=>$thisData['venueAddress'],
            "venueLatitude"=>$thisData['venueLatitude'],
            "venueLongitude"=>$thisData['venueLongitude'],
            "city"=>trim($thisData['city']),
            "country"=>trim($thisData['country'])
        ];
        if(strtotime($now) > strtotime($eventTime)) {
            $thisEvent['eventDetail']['eventType'] = 'past';
        } else {
            $thisEvent['eventDetail']['eventType'] = 'future';
        }
        $events[$artist_counter]['events'][] = $thisEvent;
        }
    echo json_encode($events);
}
$mysqli->close();

?>