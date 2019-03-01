<?php
/**
 * @property CI_Loader $load
 * @property CI_Form_validation $form_validation
 * @property CI_Input $input
 * @property CI_Email $email
 * @property CI_DB_active_record $db
 * @property CI_DB_forge $dbforge
 */

class Audit_model extends CI_Model {

    public function  __construct() {
        parent::__construct();
        $this->load->helper('xml');
    }
    
    public function getNumberOfChanges($searchparams=FALSE) {
        $searchparams = unserialize($searchparams);
        $this->db->select('COUNT(*) AS NumberOfChanges', FALSE);
        $this->db->from('spauditlog');
        if (isset($searchparams['startdate'])) {
            $startdate = $searchparams['startdate'];
            $this->db->where("DATE(TimestampCreated)>='$startdate'", FALSE, FALSE);
        }
        if (isset($searchparams['enddate'])) {
            $enddate = $searchparams['enddate'];
            $this->db->where("DATE(TimestampCreated)<='$enddate'", FALSE, FALSE);
        }
        if (isset($searchparams['table'])) {
            if ($searchparams['table'] == 132) {
                $this->db->where('TableNum', 54);
                $this->db->where('ParentTableNum', 131);
            }
            elseif ($searchparams['table'] == 54) {
                $this->db->where('TableNum', 54);
                $this->db->where('ParentTableNum', 52);
            }
            else
                $this->db->where('TableNum', $searchparams['table']);
        }
        if (isset($searchparams['action'])) 
            $this->db->where('Action', $searchparams['action']-1);
        if (isset($searchparams['user']))
            $this->db->where('CreatedByAgentID', $searchparams['user']);
        $query = $this->db->get();
        if ($query->num_rows()) {
            $row = $query->row();
            return $row->NumberOfChanges;
        }
    }

    public function getChanges($searchparams=FALSE, $limit=FALSE, $offset=FALSE) {
        $searchparams = unserialize($searchparams);
        $this->db->select('TimestampCreated, Action, ParentTableNum, ParentRecordID, TableNum, RecordID, CreatedByAgentID');
        $this->db->from('spauditlog');
        if (isset($searchparams['startdate'])) {
            $startdate = $searchparams['startdate'];
            $this->db->where("DATE(TimestampCreated)>='$startdate'", FALSE, FALSE);
        }
        if (isset($searchparams['enddate'])) {
            $enddate = $searchparams['enddate'];
            $this->db->where("DATE(TimestampCreated)<='$enddate'", FALSE, FALSE);
        }
        if (isset($searchparams['table'])) {
            if ($searchparams['table'] == 132) {
                $this->db->where('TableNum', 54);
                $this->db->where('ParentTableNum', 131);
            }
            elseif ($searchparams['table'] == 54) {
                $this->db->where('TableNum', 54);
                $this->db->where('ParentTableNum', 52);
            }
            else
                $this->db->where('TableNum', $searchparams['table']);
        }
        if (isset($searchparams['action'])) 
            $this->db->where('Action', $searchparams['action']-1);
        if (isset($searchparams['user']))
            $this->db->where('CreatedByAgentID', $searchparams['user']);
        $this->db->order_by('TimestampCreated DESC, SpAuditLogID DESC');
        if ($limit)
            $this->db->limit($limit, $offset);
        
        $query = $this->db->get();
        if ($query->num_rows()) {
            $ret = array();
            foreach ($query->result() as $row) {
                if ($row->TableNum == 54 && $row->ParentTableNum == 131)
                    $row->TableNum = 132;
                switch ($row->ParentTableNum) {
                    case 1:
                        $select = "SELECT CatalogNumber FROM collectionobject WHERE CollectionObjectID=$row->ParentRecordID";
                        $qry = $this->db->query($select);
                        if ($qry->num_rows()) {
                            $r = $qry->row();
                            $row->ParentRecordID = $r->CatalogNumber;
                        }
                        break;
                    
                    case 52:
                        $select = "SELECT LoanNumber FROM loan WHERE LoanID=$row->ParentRecordID";
                        $qry = $this->db->query($select);
                        if ($qry->result()) {
                            $r = $qry->row();
                            $row->ParentRecordID = trim($r->LoanNumber);
                        }
                        break;

                    case 131:
                        $select = "SELECT GiftNumber FROM gift WHERE GiftID=$row->ParentRecordID";
                        $qry = $this->db->query($select);
                        if ($qry->result()) {
                            $r = $qry->row();
                            $row->ParentRecordID = trim($r->GiftNumber);
                        }
                        break;

                    default:
                        break;
                }
                
                $ret[] = $row;
            }
            
            return $ret;
        }
    }
} 

?>
