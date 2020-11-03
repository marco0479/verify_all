<?php

/**
 * Description of DataValue
 *
 * @author lveeckha
 */
class MetaDataDAO {
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
    
    /**
     * 
     * @param int $projectid
     * @param string $instrument_name
     * @return string[]
     */
    public function getFieldNames($projectid, $instrument)
    {
        $sql = "SELECT field_name FROM redcap_metadata WHERE project_id=" . $projectid . " AND form_name='" . $instrument . "' ORDER BY field_order ASC";
        $startTreatmentResult = mysqli_query($this->conn, $sql);
        $queryResult = mysqli_fetch_assoc($startTreatmentResult);
        $names = [];
        while ($queryResult !== null) {
            $names[] = $queryResult["field_name"];
            $queryResult = mysqli_fetch_assoc($startTreatmentResult);
        }
        mysqli_free_result($startTreatmentResult);
        return $names;
    }

    /**
     * 
     * @param int $projectid
     * @param string $instrument_name
     * @return string[]
     */
    public function getFields($projectid, $instrument)
    {
        $sql = "SELECT * FROM redcap_metadata 
            WHERE project_id = " . $projectid . " 
            AND form_name = '" . $instrument . "' 
            AND field_name != 'record_id' 
            AND ((misc NOT LIKE '%@HIDDEN%' AND misc NOT LIKE '%@NOVERIFYALL%') OR misc IS NULL)
            AND element_type != 'calc'
            AND element_type != 'descriptive'
            ORDER BY field_order ASC";
        
        $startTreatmentResult = mysqli_query($this->conn, $sql);
        $queryResult = mysqli_fetch_assoc($startTreatmentResult);
        $names = [];
        while ($queryResult !== null) {
            $names[] = $queryResult;
            $queryResult = mysqli_fetch_assoc($startTreatmentResult);
        }
        mysqli_free_result($startTreatmentResult);
        return $names;
    }

    public function getHeaderFields($projectid, $instrument){

        $sql = "SELECT * FROM redcap_metadata 
            WHERE project_id = " . $projectid . " 
            AND form_name = '" . $instrument . "' 
            ORDER BY field_order ASC";

        $startTreatmentResult = mysqli_query($this->conn, $sql);

        $headers = [];
        $headerToUse = "";
        while ($row = mysqli_fetch_assoc($startTreatmentResult)) {
            if($row['element_preceding_header'] != ""){
                $headerToUse = strip_tags($row['element_preceding_header']);
            }
            $headers[$row['field_name']] = $headerToUse;
        }

        return $headers;

    }
    
}
