<?php
require_once __DIR__ . "/../../redcap_connect.php";
require_once __DIR__ . "/DataResolutionDAO.php";

//$HtmlPage = new HtmlPage();
//$HtmlPage->PrintHeaderExt();

$projectid = $_POST["projectid"];
$record = $_POST["recordid"];
$instrument = $_POST["instrument"];
$eventid = $_POST["eventid"];
$instanceid = $_POST["instanceid"];
$verifyList = $_POST["verifyList"];

$resolutionDao = new DataResolutionDAO($conn);

$userInitiator = User::getUserInfo(USERID);
$skipped = [];
$verified = [];

foreach ($verifyList as $field) {

    if ($resolutionDao->fieldIsVerifiable($projectid, $record, $field, $instanceid, $eventid)) {
        $verified[] = $field;
        $resolutionDao->markFieldAsVerified($projectid, $record, $field, $instanceid, $userInitiator['ui_id'], $eventid);
    }
}

$f = count($verified) > 1 ? 'fields' : 'field';
setrawcookie('verify-all-message', rawurlencode(count($verified) . " {$f} have been marked as verified!"), time() + 120, "/");

header('Location: /redcap_v' . $redcap_version . '/DataEntry/index.php?pid=' . $projectid . '&id=' . $record . '&page=' . $instrument . '&event_id=' . $eventid . '&instance=' . $instanceid);

?>
