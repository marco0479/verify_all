<?php

namespace uzgent\VerifyClass;

// Declare your module class, which must extend AbstractExternalModule
class VerifyClass extends \ExternalModules\AbstractExternalModule
{

    protected static $Tags = array(
        '@NOVERIFYALL' => array('comparison' => 'gt', 'description' => 'Verify All<br>For disable the field from "verify all" tool. The fields with this tag can be verified only manually.'),
    );

    public function redcap_every_page_before_render($project_id)
    {

        if (PAGE === 'Design/action_tag_explain.php') {
            global $lang;
            $lastActionTagDesc = end(\Form::getActionTags());

            // which $lang element is this?
            $langElement = array_search($lastActionTagDesc, $lang);

            foreach (static::$Tags as $tag => $tagAttr) {
                $lastActionTagDesc .= "</td></tr>";
                $lastActionTagDesc .= $this->makeTagTR($tag, $tagAttr['description']);
            }
            $lang[$langElement] = rtrim(rtrim(rtrim(trim($lastActionTagDesc), '</tr>')), '</td>');
        }
    }

    public function redcap_data_entry_form($project_id, $record, $instrument, $event_id, $group_id, $repeat_instance)
    {
        $debug = $this->getProjectSetting("debug");
        $project_users = \REDCap::getUserRights(USERID);
        $resolution = $project_users[USERID]['data_quality_resolution'];

        $cssVersion = filemtime(__DIR__ . "/assets/css/verify_all.css");
        $jsVersion = filemtime(__DIR__ . "/assets/js/verify_all.js");
        /**
         * resolution should be Open queries only OR Open and respond to queries OR Open, close, and respond to queries
         */
        if ($resolution < 3 || $resolution > 5) {
            if ($debug) {
                echo "You don't have the appropriate Data Resolution Workflow rights.";
            }
            return;
        }

        if ($record !== null) {
?>
            <script src="<?= $this->getUrl("assets/js/verify_all.js?v=" . $jsVersion) ?>"></script>
            <link rel="stylesheet" href="<?= $this->getUrl("assets/css/verify_all.css?v=" . $cssVersion) ?>">
            <table id="row-btn-verify-all-instrument">
                <tr>
                    <td class="labelrc col-7">&nbsp;</td>
                    <td class="data col-5">
                        <form action="<?= $this->getUrl("checkFields.php") ?>" method="post" id="verify-all-form">
                            <input type="hidden" name="projectid" value="<?php echo $project_id; ?>">
                            <input type="hidden" name="recordid" value="<?php echo $record; ?>">
                            <input type="hidden" name="eventid" value="<?php echo $event_id; ?>">
                            <input type="hidden" name="instrument" value="<?php echo $instrument; ?>">
                            <input type="hidden" name="instanceid" value="<?php echo $repeat_instance; ?>">
                            <button class="btn btn-warning btn-verify-all" title="Verify All Fields"><i class="far fa-comment"></i> Verify All Fields</button>
                        </form>
                        <div class="verify-all-note">
                            NOTE: In the following steps you can select which fields or sections you want to mark as verified.
                        </div>
                    </td>
                </tr>
            </table>
<?php

        } else {
            if ($debug === true) {
                echo "Record needs to be saved first";
            }
        }
    }

    protected function makeTagTR($tag, $description)
    {
        global $isAjax, $lang;
        return \RCView::tr(
            array(),
            \RCView::td(
                array('class' => 'nowrap', 'style' => 'text-align:center;background-color:#f5f5f5;color:#912B2B;padding:7px 15px 7px 12px;font-weight:bold;border:1px solid #ccc;border-bottom:0;border-right:0;'),
                ((!$isAjax || (isset($_POST['hideBtns']) && $_POST['hideBtns'] == '1')) ? '' :
                    \RCView::button(array('class' => 'btn btn-xs btn-rcred', 'style' => '', 'onclick' => "$('#field_annotation').val(trim('" . js_escape($tag) . " '+$('#field_annotation').val())); highlightTableRowOb($(this).parentsUntil('tr').parent(),2500);"), $lang['design_171']))
            ) .
                \RCView::td(
                    array('class' => 'nowrap', 'style' => 'background-color:#f5f5f5;color:#912B2B;padding:7px;font-weight:bold;border:1px solid #ccc;border-bottom:0;border-left:0;border-right:0;'),
                    $tag
                ) .
                \RCView::td(
                    array('style' => 'font-size:12px;background-color:#f5f5f5;padding:7px;border:1px solid #ccc;border-bottom:0;border-left:0;'),
                    '<i class="fas fa-cube mr-1"></i>' . $description
                )
        );
    }
}
