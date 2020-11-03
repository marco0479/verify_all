<?php

/**
 * Description of DataValue
 *
 * @author lveeckha
 */
class DataResolutionDAO {
        /**
     * @var resource
     */
    private $conn;

    /**
     *
     * @param resource $conn
     */
    public function __construct($conn)
    {
        if ($conn === null) throw new Exception("Connection cannot be null");
        $this->conn = $conn;
    }
    
    public function fieldHasComments($projectid, $record, $field, $instanceid, $eventid)
    {
        $sql = "SELECT count(*) as count FROM redcap_data_quality_status WHERE project_id=? AND field_name=? AND instance = ? AND record = ? AND event_id = ?";
        $prepared = mysqli_prepare($this->conn, $sql);
        mysqli_stmt_bind_param($prepared, "dsdsd", $projectid, $field, $instanceid, $record, $eventid);
        mysqli_stmt_execute($prepared);
        mysqli_stmt_bind_result($prepared, $count);
        mysqli_stmt_fetch($prepared);
        if (mysqli_stmt_error($prepared) != "")
        {
            throw new Exception("Unable to execute query " . mysqli_stmt_error($prepared) . " $sql");
        }
        return $count > 0;

    }

    public function fieldIsVerifiable($projectid, $record, $field, $instanceid, $eventid)
    {
        
        // field status complete is not verifiable
        if (strpos($field,'_complete') !== false) {
            return false;
        }

        // fields empty are not verifiable
        if($this->fieldEmpty($projectid, $record, $field, $instanceid, $eventid)){
            return false;
        }

        // fields with comment are not verifiable
        if($this->fieldHasComments($projectid, $record, $field, $instanceid, $eventid)){
            return false;
        }

        return true;
    }
    
    public function fieldEmpty($projectid, $record, $field, $instanceid, $eventid){

        $ret = false;

        if($instanceid > 1){
            $whereInst = "AND instance = {$instanceid}";
        }else{
            $whereInst = "AND (instance IS NULL OR instance = {$instanceid})";
        }

        $sql = "SELECT * FROM redcap_data 
        WHERE project_id = {$projectid} 
        AND event_id = {$eventid} 
        AND record = {$record} 
        AND field_name = '{$field}' 
        {$whereInst}";

        $startTreatmentResult = mysqli_query($this->conn, $sql);
        $queryResult = mysqli_fetch_assoc($startTreatmentResult);

        if(empty($queryResult) || $queryResult['value'] == ""){
            $ret = true;
        }

        return $ret;

    }

    /**
     * 
     * @param int $projectid
     * @param string $record
     * @param string $field
     * @param int $instanceid
     * @param string $userid
     */
    public function markFieldAsVerified($projectid, $record, $field, $instanceid, $userid, $eventid)
    {
        $dr_status = "VERIFIED";
        $sql = "insert into redcap_data_quality_status (non_rule, project_id, record, event_id, field_name, query_status, instance)
                        values (1, ?, ?, ?, ?, ?, ?)
                        on duplicate key update query_status = ?, status_id = LAST_INSERT_ID(status_id)";
        $prepared_status = mysqli_prepare($this->conn, $sql);
        mysqli_stmt_bind_param($prepared_status, "dsdssds",      $projectid, $record, $eventid, $field,    $dr_status,   $instanceid, 
                                                            $dr_status);
        mysqli_stmt_execute($prepared_status);
        if (mysqli_stmt_error($prepared_status) != "")
        {
            throw new Exception("Unable to execute query " . mysqli_stmt_error($prepared_status) . " $sql");
        }
        $status_id = mysqli_insert_id($this->conn);

        $sql = "insert into redcap_data_quality_resolutions (status_id, ts, user_id, response_requested,
                                        response, comment, current_query_status, upload_doc_id)
                                        VALUES (?, ?, ?,
                                        0, NULL, 'Field verified by Verify All tool (simultaneous verification of multiple fields)', ?, NULL)"; //UPLOADDOC_ID
        $prepared_resolution = mysqli_prepare($this->conn, $sql);
        $now = NOW;
        mysqli_stmt_bind_param($prepared_resolution, "dsds", $status_id, $now, $userid, $dr_status);
        mysqli_stmt_execute($prepared_resolution);
        if (mysqli_stmt_error($prepared_resolution) != "")
        {
            throw new Exception("Unable to execute query " . mysqli_stmt_error($prepared_resolution) . " $sql");
        }

        ## Logging (modeled after 'DataQuality/data_resolution_popup.php')
        $logDataValues = json_encode_rc(array('record'=>$record,'event_id'=>$eventid,'field'=>$field));

        // Set event_id in query string for logging purposes only
        $_GET['event_id'] = $eventid;
        // Log it
        Logging::logEvent($sql,"redcap_data_quality_resolutions","MANAGE",$record, $logDataValues, "Verified data value");
    }
    
}
