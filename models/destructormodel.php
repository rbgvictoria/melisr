<?php
/**
 * @property CI_Loader $load
 * @property CI_Form_validation $form_validation
 * @property CI_Input $input
 * @property CI_Email $email
 * @property CI_DB_active_record $db
 * @property CI_DB_forge $dbforge
 */

class DestructorModel extends Model {

    function  __construct() {
        parent::Model();

        // connect to database
        $this->load->database();
    }
    
    public function getAgents() {
        $this->db->select('a.AgentID, spu.Name');
        $this->db->from('specifyuser spu');
        $this->db->join('agent a', 'spu.SpecifyUserID=a.SpecifyUserID');
        $this->db->order_by('Name');
        $query = $this->db->get();
        $agents = array();
        foreach($query->result() as $row)
            $agents[] = array('id' => $row->AgentID, 'username' => $row->Name);
        return $agents;
    }
    
    
    public function batchDestruct($recordsetid, $remark, $agentid, $override=FALSE) {
        $ret = array();
        $this->db->select('RecordID');
        $this->db->from('recordsetitem');
        $this->db->where('RecordSetID', $recordsetid);
        $this->db->group_by('RecordID');
        $query = $this->db->get();
        if ($query->num_rows()) {
            foreach ($query->result() as $row) {
                $destruct = $this->singleDestruct($row->RecordID, $remark, $agentid, $override);
                if ($destruct)
                    $ret[] = $destruct;
            }
        }
        return $ret;
    }
    
    private function singleDestruct($collectionobjectid, $remark, $agentid, $override=FALSE) {
        $this->db->select('co.CollectionObjectID, co.CollectionObjectAttributeID, 
            coa.Text6, coa.TimestampModified, coa.TimestampCreated');
        $this->db->from('collectionobject co');
        $this->db->join('collectionobjectattribute coa', 
                'co.CollectionObjectAttributeID=coa.CollectionObjectAttributeID', 'left');
        $this->db->where('co.CollectionObjectID', $collectionobjectid);
        $query = $this->db->get();
        
        if ($query->num_rows()) {
            $row = $query->row();
            $date = date('Y-m-d H:i:s');
            if ($row->CollectionObjectAttributeID) {
                if ($row->Text6 && !$override) {
                    $this->db->select('CatalogNumber');
                    $this->db->from('collectionobject');
                    $this->db->where('CollectionObjectID', $row->CollectionObjectID);
                    $query = $this->db->get();
                    $r = $query->row();
                    $catalogNumber = $r->CatalogNumber;
                    return "MEL $catalogNumber: '$row->Text6'";
                }
                else {
                    $updateArray = array(
                        'TimestampModified' => $date,
                        'Text6' => $remark,
                        'ModifiedByAgentID' => $agentid
                    );
                    $this->db->where('CollectionObjectAttributeID', $row->CollectionObjectAttributeID);
                    $this->db->update('collectionobjectattribute', $updateArray);

                    $updateArray = array(
                        'TimestampModified' => $date,
                        'ModifiedByAgentID' => $agentid
                    );
                    $this->db->where('CollectionObjectID', $row->CollectionObjectID);
                    $this->db->update('collectionobject', $updateArray);
                }
                
            }
            else {
                $this->db->select('MAX(CollectionObjectAttributeID) as MaxID');
                $this->db->from('collectionobjectattribute');
                $query = $this->db->get();
                $max = $query->row();
                $newcollectionobjectattributeid = $max->MaxID+1;
                
                $insertArray = array(
                    'CollectionObjectAttributeID' => $row->CollectionObjectAttributeID,
                    'TimestampCreated' => $date,
                    'Version' => 1,
                    'CollectionMemberID' => 4,
                    'Text6' => $remark,
                    'CreatedByAgentID' => $agentid
                );
                
                $this->db->insert('collectionobjectattribute', $insertArray);
                
                $updateArray = array(
                    'CollectionObjectAttributeID' => $row->CollectionObjectAttributeID
                );
                
                $this->db->where('CollectionObjectID', $row->CollectionObjectID);
                $this->db->update('collectionobject', $updateArray);
            }
        }
        return FALSE;
        
    }

}

?>
