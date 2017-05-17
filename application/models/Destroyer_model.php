<?php

class Destroyer_model extends CI_Model {
    
    public function __construct() {
        parent::__construct();
    }
    
    public function getAgents() {
        $ret = array();
        $ret[''] = '(Choose agent)';
        $this->db->distinct();
        $this->db->select('CreatedByAgentID');
        $this->db->from('conservdescription');
        $query = $this->db->get();
        $agents = array();
        if ($query->num_rows()) {
            foreach ($query->result() as $row)
                $agents[] = $row->CreatedByAgentID;
        
        
            $this->db->select('AgentID, MiddleInitial');
            $this->db->from('agent');
            $this->db->where_in('AgentID', $agents);
            $this->db->order_by('MiddleInitial');
            $query = $this->db->get();
            foreach ($query->result() as $row) {
                $ret[$row->AgentID] = $row->MiddleInitial;
            }
            return $ret;
        }
        else
            return FALSE;
    }
    
    public function getCollectionObjectIDs($barcode) {
        $catalognumber = str_pad(substr($barcode, 4), 7, '0', STR_PAD_LEFT);
        $ret = array();
        $this->db->select('CollectionObjectID');
        $this->db->from('collectionobject');
        $this->db->where('SUBSTRING(CatalogNumber, 1, 7)=' . $catalognumber, FALSE, FALSE);
        $query = $this->db->get();
        if ($query->num_rows()) {
            foreach ($query->result() as $row) {
                $ret[] = $row->CollectionObjectID;
            }
        }
        return $ret;
    }
    
    public function getPickListItemValue($picklistid, $title) {
        $this->db->select('Value');
        $this->db->from('picklistitem');
        $this->db->where('PickListID', $picklistid);
        $this->db->where('Title', $title);
        $query = $this->db->get();
        if ($query->num_rows()) {
            $row = $query->row();
            return $row->Value;
        }
        else
            return FALSE;
    }
    
    public function getNewConservDescriptionID() {
        $this->db->select('MAX(ConservDescriptionID) AS max', FALSE);
        $this->db->from('conservdescription');
        $query = $this->db->get();
        $row = $query->row();
        return ($row->max) ? $row->max + 1 : 1;
    }
    
    public function findAgentID($fullname) {
        $this->db->select('AgentID');
        $this->db->from('agent');
        $this->db->where("CONCAT(LastName, ', ', FirstName)='$fullname'", FALSE, FALSE);
        $this->db->or_where('LastName', $fullname);
        $query = $this->db->get();
        if ($query->num_rows()) {
            $row = $query->row();
            return $row->AgentID;
        }
        else
            return FALSE;
    }
    
    public function insertConservDescription($insertArray) {
        $this->db->insert('conservdescription', $insertArray);
    }
    
    public function insertConservEvent($insertArray) {
        $this->db->insert('conservevent', $insertArray);
    }
    
    public function getRecordSetID() {
        $this->db->select('MAX(RecordSetID) as max', FALSE);
        $this->db->from('recordset');
        $query = $this->db->get();
        $row = $query->row();
        return ($row->max) ? $row->max + 1 : 1;
    }
    
    public function getSpecifyUserID($agent) {
        $this->db->select('SpecifyUserID');
        $this->db->from('agent');
        $this->db->where('AgentID', $agent);
        $query = $this->db->get();
        $row = $query->row();
        return $row->SpecifyUserID;
    }
    
    public function insertRecordSet($insertArray) {
        $this->db->insert('recordset', $insertArray);
    }
    
    public function insertRecordSetItem($insertArray) {
        $this->db->insert('recordsetitem', $insertArray);
    }
    
    public function getPickListItems($picklistid) {
        $ret = array();
        $this->db->select('Title');
        $this->db->from('picklistitem');
        $this->db->where('PickListID', $picklistid);
        $this->db->order_by('Title');
        $query = $this->db->get();
        if ($query->num_rows()) {
            foreach ($query->result() as $row) {
                $ret[] = $row->Title;
            }
        }
        return $ret;
    }
}


?>
