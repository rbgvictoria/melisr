<?php
/**
 * @property CI_Loader $load
 * @property CI_Form_validation $form_validation
 * @property CI_Input $input
 * @property CI_Email $email
 * @property CI_DB_active_record $db
 * @property CI_DB_forge $dbforge
 */

class VrsModel extends Model {

    function  __construct() {
        parent::Model();

        // connect to database
        $this->load->database();
    }
    
    public function getRecords() {
        $query = $this->db->query("SELECT p.PreparationID, co.CollectionObjectID, co.CatalogNumber, p.SampleNumber, p.ModifiedByAgentID, mba.MiddleInitial, p.TimestampModified,
              coa.Number1 AS Flowers, coa.Number2 AS Fruit, coa.Number3 AS Buds,
              coa.Number4 AS Leafless, coa.Number5 AS Fertile, coa.Number6 AS Sterile
            FROM collectionobject co
            LEFT JOIN collectionobjectattribute coa ON co.CollectionObjectAttributeID=coa.CollectionObjectAttributeID
            JOIN preparation p ON co.CollectionObjectID=p.CollectionObjectID
            JOIN agent mba ON p.ModifiedByAgentID=mba.AgentID
            LEFT JOIN collectionobject vrs ON p.SampleNumber=vrs.CatalogNumber AND vrs.CollectionID=65536
            WHERE co.CollectionID=4 AND p.PrepTypeID=18 AND vrs.CollectionObjectID IS NULL
            ORDER BY SampleNumber");
        if ($query->num_rows()) {
            return $query->result_array();
        }
        else {
            return FALSE;
        }
    }
    
    public function createVRSRecords($data) {
        $n = count($data['collectionobjectid']);
        $collectionid = 65536;
        $preptypeid = 25;
        
        $query = $this->db->query("SELECT MAX(CollectionObjectID)+1 AS newcollectionobjectid
            FROM collectionobject");
        $row = $query->row();
        $collectionobjectid = $row->newcollectionobjectid;
        
        $query = $this->db->query("SELECT MAX(CollectionObjectAttributeID)+1 AS newcollectionobjectattributeid
            FROM collectionobjectattribute;");
        $row = $query->row();
        $collectionobjectattributeid = $row->newcollectionobjectattributeid;
        
        for ($i = 0; $i < $n; $i++) {
            $this->db->trans_start();
            
            $flowers = (isset($data['flowers'][$i])) ? 1 : NULL; 
            $fruit = (isset($data['fruit'][$i])) ? 1 : NULL; 
            $buds = (isset($data['buds'][$i])) ? 1 : NULL; 
            $leafless = (isset($data['leafless'][$i])) ? 1 : NULL; 
            $fertile = (isset($data['fertile'][$i])) ? 1 : NULL; 
            $sterile = (isset($data['sterile'][$i])) ? 1 : NULL; 
            $curationnotes = (isset($data['curationnotes'][$i])) ? $data['curationnotes'][$i] : NULL;
            
            $attr = ($flowers || $fruit || $buds || $leafless || $fertile || $sterile || $curationnotes) ? TRUE : FALSE;
            if ($attr) {
                // Collection Object Attribute
                $insertArray = array(
                    'CollectionObjectAttributeID' => $collectionobjectattributeid, 
                    'TimestampCreated' => date('Y-m-d H:i:s'), 
                    'TimestampModified' => date('Y-m-d H:i:s'),
                    'Version' => 0, 
                    'CollectionMemberID' => $collectionid, 
                    'Number1' => $flowers, 
                    'Number2' => $fruit, 
                    'Number3' => $buds, 
                    'Number4' => $leafless, 
                    'Number5' => $fertile, 
                    'Number6' => $sterile,
                    'Text1' => $curationnotes,
                    'CreatedByAgentID' => $data['agentid'][$i], 
                    'ModifiedByAgentID' => $data['agentid'][$i]
                );
                $this->db->insert('collectionobjectattribute', $insertArray);
            }
            
            // Collection Object
            $query = $this->db->query("SELECT CatalogNumber, Remarks, Text1, CollectingEventID
                FROM collectionobject
                WHERE CollectionObjectID=" . $data['collectionobjectid'][$i]);
            $row = $query->row_array();
            
            $insertArray = array(
                'CollectionObjectID' => $collectionobjectid,
                'TimestampCreated' => date('Y-m-d H:i:s'),
                'TimestampModified' => date('Y-m-d H:i:s'),
                'Version' => 0,
                'CollectionMemberID' => $collectionid,
                'CatalogNumber' => $data['vrsnumber'][$i],
                'Remarks' => $row['Remarks'],
                'Text1' => $row['Text1'],
                'CollectingEventID' => $row['CollectingEventID'],
                'GUID' => $this->uuid(),
                'CollectionID' => $collectionid,
                'CollectionObjectAttributeID' => ($attr) ? $collectionobjectattributeid : NULL,
                'ModifiedByAgentID' => $data['agentid'][$i],
                'CreatedByAgentID' => $data['agentid'][$i]
            );
            $this->db->insert('collectionobject', $insertArray);
            
            // Determination
            $query = $this->db->query("SELECT Addendum, AlternateName, DeterminedDate, DeterminedDatePrecision,
                FeatureOrBasis, `Method`, NameUsage, Qualifier, Remarks, VarQualifier,
                DeterminerID, PreferredTaxonID, TaxonID
              FROM determination
              WHERE CollectionObjectID=" . $data['collectionobjectid'][$i]. " AND IsCurrent=1");
            $row = $query->row_array();
            
            $insertArray = array(
                'TimestampCreated' => date('Y-m-d H:i:s'),
                'TimestampModified' => date('Y-m-d H:i:s'),
                'Version' => 0,
                'CollectionMemberID' => $collectionid,
                'Addendum' => $row['Addendum'],
                'AlternateName' => $row['AlternateName'],
                'DeterminedDate' => $row['DeterminedDate'],
                'DeterminedDatePrecision' => $row['DeterminedDatePrecision'],
                'FeatureOrBasis' => $row['FeatureOrBasis'],
                'Method' => $row['Method'],
                'NameUsage' => $row['NameUsage'],
                'Qualifier' => $row['Qualifier'],
                'Remarks' => $row['Remarks'],
                'VarQualifier' => $row['VarQualifier'],
                'DeterminerID' => $row['DeterminerID'],
                'PreferredTaxonID' => $row['PreferredTaxonID'],
                'TaxonID' => $row['TaxonID'],
                'GUID' => $this->uuid(),
                'CollectionObjectID' => $collectionobjectid,
                'ModifiedByAgentID' => $data['agentid'][$i],
                'CreatedByAgentID' => $data['agentid'][$i],
                'IsCurrent' => 1                
            );
            $this->db->insert('determination', $insertArray);
            
            // Preparation
            $insertArray = array(
                'TimestampCreated' => date('Y-m-d H:i:s'),
                'TimestampModified' => date('Y-m-d H:i:s'),
                'Version' => 0,
                'CollectionMemberID' => $collectionid,
                'CountAmt' => 1,
                'CreatedByAgentID' => $data['agentid'][$i],
                'CollectionObjectID' => $collectionobjectid,
                'PrepTypeID' => $preptypeid,
                'ModifiedByAgentID' => $data['agentid'][$i]
            );
            $this->db->insert('preparation', $insertArray);
            
            // Other Identifier
            $insertArray = array(
                'TimestampCreated' => date('Y-m-d H:i:s'),
                'TimestampModified' => date('Y-m-d H:i:s'),
                'Version' => 0,
                'CollectionMemberID' => $collectionid,
                'Identifier' => 'MEL',
                'Institution' => $data['catalognumber'][$i],
                'Remarks' => 'MEL catalogue number',
                'CreatedByAgentID' => $data['agentid'][$i],
                'CollectionObjectID' => $collectionobjectid,
                'ModifiedByAgentID' => $data['agentid'][$i],
            );
            $this->db->insert('otheridentifier', $insertArray);
            
            $collectionobjectid++;
            if ($attr) $collectionobjectattributeid++;
            
            $this->db->trans_complete();
        }
    }
    
    private function uuid() {
        return sprintf('%04x%04x-%04x-%04x-%04x-%04x%04x%04x',

          // 32 bits for "time_low"
          mt_rand(0, 0xffff), mt_rand(0, 0xffff),

          // 16 bits for "time_mid"
          mt_rand(0, 0xffff),

          // 16 bits for "time_hi_and_version",
          // four most significant bits holds version number 4
          mt_rand(0, 0x0fff) | 0x4000,

          // 16 bits, 8 bits for "clk_seq_hi_res",
          // 8 bits for "clk_seq_low",
          // two most significant bits holds zero and one for variant DCE1.1
          mt_rand(0, 0x3fff) | 0x8000,

          // 48 bits for "node"
          mt_rand(0, 0xffff), mt_rand(0, 0xffff), mt_rand(0, 0xffff)
        );
    }


}


?>
