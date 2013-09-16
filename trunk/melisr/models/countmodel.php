<?php
/**
 * @property CI_Loader $load
 * @property CI_Form_validation $form_validation
 * @property CI_Input $input
 * @property CI_Email $email
 * @property CI_DB_active_record $db
 * @property CI_DB_forge $dbforge
 */

class CountModel extends Model {

    function  __construct() {
        parent::Model();

        // connect to database
        $this->load->database();
    }

    function getSpiritNumber() {
        $select = "SELECT MAX(CAST(SampleNumber AS UNSIGNED))+1 AS Next FROM preparation WHERE PrepTypeID=2";
        $query = $this->db->query($select);
        $row = $query->row();
        return $row->Next;
    }
    
    function getSlideNumber() {
        $select = "SELECT MAX(CAST(SampleNumber AS UNSIGNED))+1 AS Next FROM preparation WHERE PrepTypeID=6";
        $query = $this->db->query($select);
        $row = $query->row();
        return $row->Next;
    }
    
    function getSilicagelNumber() {
        $select = "SELECT MAX(CAST(SampleNumber AS UNSIGNED))+1 AS Next FROM preparation WHERE PrepTypeID=7";
        $query = $this->db->query($select);
        $row = $query->row();
        return $row->Next;
    }
    
    function getLoanNumber() {
        $select = "SELECT MAX(SUBSTRING(LoanNumber, 1, 4)) AS `Year` FROM loan";
        $query = $this->db->query($select);
        $row = $query->row();
        $year = $row->Year;
        
        $select = "SELECT MAX(SUBSTRING(LoanNumber, 6, 4))+1 AS Next FROM loan WHERE SUBSTRING(LoanNumber, 1, 4)=$year";
        $query = $this->db->query($select);
        $row = $query->row();
        return $year . '/' . str_pad($row->Next, 4, '0', STR_PAD_LEFT);
    }

    function getExchangeNumber() {
        $select = "SELECT MAX(CAST(GiftNumber AS unsigned)) AS Gift FROM gift";
        $query = $this->db->query($select);
        $row = $query->row();
        $gift = $row->Gift;

        $select = "SELECT MAX(CAST(Text1 AS unsigned)) AS Exchange FROM exchangein";
        $query = $this->db->query($select);
        $row = $query->row();
        $exchange = $row->Exchange;

        if ($gift > $exchange)
            return str_pad ($gift+1, 4, '0', STR_PAD_LEFT);
        else
            return str_pad ($exchange+1, 4, '0', STR_PAD_LEFT);
    }

    function getMelNumber() {
        $select = "SELECT MAX(EndNumber) as LastUsed FROM melnumbers";
        $query = $this->db->query($select);
        $row = $query->row();
        return $row->LastUsed;
    }

    function insertMELNumbers($username, $startnumber, $endnumber) {
        if ($startnumber == $this->getMelNumber()+1) {
            $insert = "INSERT INTO melnumbers (`Date`, UsedBy, StartNumber, EndNumber)
                VALUES (NOW(), '$username', $startnumber, $endnumber)";
            $this->db->query($insert);
            return TRUE;
        } else return FALSE;
    }
    
    public function MelNumbers() {
        $this->db->select("MelNumbersID, DATE(`Date`) AS `Date`, UsedBy, StartNumber, EndNumber", FALSE);
        $this->db->from('melnumbers');
        $this->db->order_by('StartNumber', 'desc');
        $query = $this->db->get();
        return $query->result_array();
    }
    
    public function checkUsage($id) {
        $this->db->select('StartNumber, EndNumber');
        $this->db->from('melnumbers');
        $this->db->where('MelNumbersID', $id);
        $query = $this->db->get();
        if ($query->num_rows()) {
            $ret = array();
            
            $row = $query->row();
            
            for ($i = $row->StartNumber; $i <= $row->EndNumber; $i++) {
                
                $this->db->select("co.CatalogNumber, co.TimestampCreated, 
                    IF(!isnull(a.FirstName), CONCAT(a.FirstName, ' ', a.LastName), a.LastName) AS CreatedBy", FALSE);
                $this->db->from('collectionobject co');
                $this->db->join('agent a', 'co.CreatedByAgentID=a.AgentID');
                $this->db->where("CatalogNumber LIKE '$i%'", FALSE, FALSE);
                $query = $this->db->get();
                if ($query->num_rows()) {
                    foreach ($query->result() as $r) {
                        $ret[] = array(
                            'AssignedNumber' => $i,
                            'CatalogNumber' => $r->CatalogNumber,
                            'TimestampCreated' => $r->TimestampCreated,
                            'CreatedBy' => $r->CreatedBy
                        );
                    }
                }
                else 
                     $ret[] = array(
                        'AssignedNumber' => $i,
                        'CatalogNumber' => '&nbsp;',
                        'TimestampCreated' => '&nbsp;',
                        'CreatedBy' => '&nbsp;'
                    );
            }
            return $ret;
        }
    }
}

?>
