<?php
require_once APP_PATH_DOCROOT . 'ProjectGeneral/header.php';
require_once __DIR__ . "/../../redcap_connect.php";
require_once __DIR__ . "/MetaDataDAO.php";
require_once __DIR__ . "/DataResolutionDAO.php";
require_once __DIR__ . "/VerifyClass.php";

$cssVersion = filemtime(__DIR__ . "/assets/css/verify_all.css");
$jsVersion = filemtime(__DIR__ . "/assets/js/verify_all.js");

$projectid = $_POST["projectid"];
$record = $_POST["recordid"];
$instrument = $_POST["instrument"];
$eventid = $_POST["eventid"];
$instanceid = $_POST["instanceid"];

$metadataDao = new MetaDataDAO($conn);
$resolutionDao = new DataResolutionDAO($conn);

$userInitiator = User::getUserInfo(USERID);
$skipped = [];
$verified = [];

$headerFields = $metadataDao->getHeaderFields($projectid, $instrument);

foreach ($metadataDao->getFields($projectid, $instrument) as $field) {

    if ($resolutionDao->fieldIsVerifiable($projectid, $record, $field['field_name'], $instanceid, $eventid)) {
        $verifiable[] = [
            'label' => strip_tags($field["element_label"]),
            'field_name' => $field['field_name']
        ];
    } else {
        // Quelli saltati non ci interessano....cmq io li metto in un elenco
        $skipped[] = [
            'label' => strip_tags($field["element_label"]),
            'field_name' => $field['field_name']
        ];
    }
}


?>
<div id="container-verify-all">
    <link rel="stylesheet" href="/modules/<?= $prefix ?>_<?= $version ?>/assets/css/verify_all.css?v=<?= $cssVersion ?>">
    <script src="/modules/<?= $prefix ?>_<?= $version ?>/assets/js/verify_all.js?v=<?= $jsVersion ?>"></script>

    <div id="dataEntryTopOptions">
        <div style="color:#800000;font-size:16px;font-weight:bold;padding:20px 0 5px;">
            <i class="far fa-comment"></i> Verify All Fields
        </div>
        <div class="explain-text">
            <b><i class="fas fa-exclamation-triangle"></i> ATTENTION:</b><br />
            <p>
                Some fields will not be present in the following list since the Verified All tool skips fields with the following characteristics:
                <ul>
                    <li>Already verified</li>
                    <li>Auto calculated</li>
                    <li>With open queries</li>
                    <li>Empty</li>
                    <li>Not visible</li>
                </ul>
                If you want to verify every field click on "All Fields" button, otherwise select the section or specific field that you are verifying.<br/>
                When you are done, click on the Verify All Fields" button.
            </p>
        </div>
    </div>
    <table id="verify-all-tbl">
        <form id="list-fields-verify-form" action="/redcap_v<?= $redcap_version ?>/ExternalModules/?prefix=<?= $prefix ?>&page=verify&pid=<?= $projectid ?>" method="POST">
            <input type="hidden" name="projectid" value="<?php echo $project_id; ?>">
            <input type="hidden" name="recordid" value="<?php echo $record; ?>">
            <input type="hidden" name="eventid" value="<?php echo $eventid; ?>">
            <input type="hidden" name="instrument" value="<?php echo $instrument; ?>">
            <input type="hidden" name="instanceid" value="<?php echo $instanceid; ?>">
            <tbody>
                <tr>
                    <td class="record-id" colspan="3">
                        <i class="fas fa-edit"></i> Verify fields of Record ID <b><?= $record ?></b>
                    </td>
                </tr>
                <tr>
                    <td class="header-top" colspan="3">
                        Fields automatically verifiable (<?= count($verifiable) ?> fields):
                    </td>
                </tr>

                <?php if (count($verifiable) > 0) { ?>
                    <tr class="row-all">
                        <td class="detail detail-header detail-ck" width="10%">
                            <input id="check-all" class="check-field" type="checkbox" title="Check/Uncheck all fields for all section" />
                        </td>
                        <td class="detail detail-header" width="90%" colspan="2">All Fields</td>
                    </tr>
                    <?php
                    $titleUsed = null;
                    $section = 0;
                    ?>
                    <?php foreach ($verifiable as $verifiableField) { ?>
                        <?php if ($headerFields[$verifiableField['field_name']] !== $titleUsed) { ?>
                            <?php
                            $titleUsed = $headerFields[$verifiableField['field_name']];
                            $section++;
                            $sectionShow = $headerFields[$verifiableField['field_name']] ? '' : 'display:none';
                            ?>
                            <tr class="row-section" style="<?= $sectionShow ?>;">
                                <td class="header header-ck" width="10%">
                                    <input class="check-all-section check-field" type="checkbox" title="Check/Uncheck all fields for section" data-section-ref="<?= $section ?>" />
                                </td>
                                <td class="header" width="90%" colspan="2">
                                    <?= $headerFields[$verifiableField['field_name']] ?>
                                </td>
                            </tr>
                        <?php } ?>
                        <tr class="row-detail">
                            <td class="detail detail-list detail-ck" width="10%">
                                <input class="check-field" name="verifyList[]" type="checkbox" value="<?= $verifiableField['field_name'] ?>" data-section="<?= $section ?>" />
                            </td>
                            <td class="detail detail-list" width="80%">
                                <span class="field-label"><?= $verifiableField['label'] ?></span>
                                <!--<span class="field-name">(<?= $verifiableField['field_name'] ?>)</span>-->
                            </td>
                            <td class="detail detail-list field-name" width="10%">
                                <?= $verifiableField['field_name'] ?>
                            </td>
                        </tr>
                    <?php } ?>
                    <tr>
                        <td colspan="3" class="detail detail-list"></td>
                    </tr>
                <?php } else { ?>
                    <tr>
                        <td class="detail detail-no-data" colspan="3">
                            <b><i class="fas fa-exclamation-triangle"></i> No fields verifiable found!</b>
                        </td>
                    </tr>
                <?php } ?>
                <tr>
                    <td class="detail detail-btn" colspan="3">
                        <button class="btn btn-defaultrc btn-sm" onclick="window.history.go(-1); return false;">-- Cancel --</button>
                        <?php if (count($verifiable) > 0) { ?>
                            <button class="btn btn-primaryrc btn-sm btn-verify-all-submit">Verify All Fields</button>
                        <?php } ?>
                    </td>
                </tr>
            </tbody>
        </form>
    </table>
    <div id="fixed-box-btn">
        <?php if (count($verifiable) > 0) { ?>
            <button class="btn btn-primaryrc btn-sm btn-verify-all-submit">Verify All Fields</button>
        <?php } ?>
        <br />
        <button class="btn btn-defaultrc btn-sm" onclick="window.history.go(-1); return false;">-- Cancel --</button>
    </div>
</div>