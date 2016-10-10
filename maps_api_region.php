<?php
$db_username        = 'vantur'; //database username
$db_password        = 'Sa6%Wa*bwH'; //dataabse password
$db_name            = 'otherdb'; //database name
$db_host            = '139.162.193.209'; //hostname or IP

$mysqli = new mysqli($db_host, $db_username, $db_password, $db_name);
mysqli_set_charset($mysqli, 'utf8');
//Output any connection error
if ($mysqli->connect_error) {
	die('Error : ('. $mysqli->connect_errno .') '. $mysqli->connect_error);
}

$country = isset($_GET['country']) ? $_GET['country']: "";
$territory = isset($_GET['territory']) ? $_GET['territory']: "";
$budget = isset($_GET['budget']) ? $_GET['budget']: "all";
$month = isset($_GET['month']) ? $_GET['month']: "all";
$genre = isset($_GET['genre']) ? $_GET['genre']: "all";

$query="select dj_Master.artistID,dj_Artists.artistName,dj_Master.eventID,dj_Events.eventName,dj_Events.date,
dj_Events.startTime,dj_Events.endTime,dj_Master.venueID,dj_Venues.venueName,dj_Venues.venueAddress,
dj_Venues.venueLatitude,dj_Venues.venueLongitude,dj_Venues.city,dj_Venues.country
from dj_Master,dj_Artists,dj_Events,dj_Venues,countries, dj_ArtistGenre
where dj_Master.artistID=dj_Artists.artistID and dj_Master.eventID=dj_Events.eventID
and dj_Master.venueID=dj_Venues.venueID and dj_Artists.artistID = dj_ArtistGenre.artistID";

// Special Check For Country
if(isset($country) && !empty($country)) {
    $query .= " and dj_Venues.country = countries.name";
    $query .= " and countries.code = '$country'";
}

// Special Check For Territory
if(isset($territory) && !empty($territory)) {
    $query .= " and dj_Venues.country = countries.name";
    $query .= " and countries.territory_code = '$territory'";
}

// Special Check For Budget
if(isset($budget) && !empty($budget) && $budget != 'all') {
    $query .= " and dj_Artists.artistBudget = '$budget'";
}

// Special Check For Genre
if(isset($genre) && !empty($genre) && $genre != 'all') {
    $query .= " and dj_ArtistGenre.genreID = '$genre'";
}

// Special Check For Month
if(isset($month) && !empty($month) && $month != 'all') {
    $query .= " and MONTH(dj_Events.date) = '$month'";
}

$query .= " order by dj_Events.date";

if($aResult = $mysqli->query($query)) {
    $events=array();
    $aData = array();
    while ($aRow = $aResult->fetch_assoc()) {
        $aData[] = $aRow;
    }
    $now = date('Y-m-d h:i:s');
    foreach($aData as $thisData) {
        $thisEvent = [];
        $eventTime = $thisData['date'] . " " . $thisData['startTime'];
        $thisEvent['artistDetail'] = ["id" => $thisData['artistID'], "artist" => $thisData['artistName']];
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
        $events['events'][] = $thisEvent;
        }
    echo json_encode($events);
}
$mysqli->close();

?>