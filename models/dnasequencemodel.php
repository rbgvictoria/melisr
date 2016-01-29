<?php
/**
 * @property CI_Loader $load
 * @property CI_Form_validation $form_validation
 * @property CI_Input $input
 * @property CI_Email $email
 * @property CI_DB_active_record $db
 * @property CI_DB_forge $dbforge
 */

class DnaSequenceModel extends Model {

    public function  __construct() {
        parent::Model();

    }
    
    public function getSpecifyUsers() {
        $ret = array();
        $ret[0] = '';
        $this->db->select('u.Name AS Username, a.AgentID');
        $this->db->from('specifyuser u');
        $this->db->join('agent a', 'u.SpecifyUserID=a.SpecifyUserID');
        $this->db->order_by('Username');
        $query = $this->db->get();
        foreach ($query->result() as $row) {
            $ret[$row->AgentID] = $row->Username;
        }
        return $ret;
    }
    
    public function getCollectionObjectIdMel($melno) {
        $catno = str_pad($melno, 7, '0', STR_PAD_LEFT) . 'A';
        $this->db->select('CollectionObjectID');
        $this->db->from('collectionobject');
        $this->db->where('CollectionID', 4);
        $this->db->where('CatalogNumber', $catno);
        $query = $this->db->get();
        if ($query->num_rows()) {
            $row = $query->row();
            return $row->CollectionObjectID;
        }
    }
    
    public function getCollectionObjectIdNonMel($catno) {
        $catnor = str_replace(' ', '', $catno);
        $this->db->select('CollectionObjectID');
        $this->db->from('collectionobject');
        $this->db->where('CollectionID', 32769);
        $this->db->where("REPLACE(AltCatalogNumber, ' ', '')='$catnor'", FALSE, FALSE);
        $query = $this->db->get();
        if ($query->num_rows()) {
            $row = $query->row();
            return $row->CollectionObjectID;
        }
    }
    
    public function createCollectionObjectNonMel($catno) {
        $this->db->select('max(CollectionObjectID) AS maxid');
        $this->db->from('collectionobject');
        $query = $this->db->get();
        $row = $query->row();
        $colobjid = $row->maxid + 1;

        $insertArray = array(
            'CollectionObjectID' => $colobjid,
            'TimestampCreated' => date('Y-m-d H:i:s'),
            'TimestampModified' => date('Y-m-d H:i:s'),
            'Version' => 1,
            'CollectionMemberID' => 32769,
            'AltCatalogNumber' => $catno,
            'CatalogedDate' => date('Y-m-d'),
            'CatalogedDatePrecision' => 1,
            'CollectionID' => 32769,
            'CreatedByAgentID' => $this->input->post('specify_user')
        );

        $this->db->insert('collectionobject', $insertArray);
        return $colobjid;
    }
    
    public function getPreparedByID($preparedby) {
        $this->db->select('AgentID');
        $this->db->from('agent');
        $this->db->where("CONCAT(LastName, ', ', FirstName)='$preparedby'", FALSE, FALSE);
        $query = $this->db->get();
        if ($query->num_rows()) {
            $row = $query->row();
            return $row->AgentID;
        }
        else {
            return NULL;
        }
    }
    
    public function findPreparation($colobjid, $prep_type_id, $sample_number) {
        $this->db->select('PreparationID, PreparedByID, PreparedDate, Version');
        $this->db->from('preparation');
        $this->db->where('CollectionObjectID', $colobjid);
        $this->db->where('PrepTypeID', $prep_type_id);
        $this->db->where('SampleNumber', $sample_number);
        $query = $this->db->get();
        if ($query->num_rows()) {
            return $query->row();
        }
        else {
            return FALSE;
        }
    }
    
    public function updatePreparation($preparationid, $prepared_by_id, $prepared_date, $prepared_date_precision, $version) {
        $updateArray = array(
            'TimestampModified' => date('Y-m-d H:i:s'),
            'ModifiedByAgentID' => $this->input->post('specify_user'),
            'Version' => $version,
            'PreparedByID' => $prepared_by_id,
            'PreparedDate' => $prepared_date,
            'PreparedDatePrecision' => $prepared_date_precision
        );
        $this->db->where('PreparationID', $preparationid);
        $this->db->update('preparation', $updateArray);
    }
    
    public function insertPreparation($colobjid, $collection_id, $prep_type_id, $sample_number, $prepared_by_id, $prepared_date, $prepared_date_precision) {
        $this->db->select('PreparationID');
        $this->db->from('preparation');
        $this->db->where('CollectionObjectID', $colobjid);
        $this->db->where('PrepTypeID', $prep_type_id);
        $this->db->where('SampleNumber', $sample_number);
        $query = $this->db->get();
        if (!$query->num_rows()) {
            $insertArray = array(
                'TimestampCreated' => date('Y-m-d H:i:s'),
                'TimestampModified' => date('Y-m-d H:i:s'),
                'Version' => 1,
                'CollectionMemberID' => $collection_id,
                'CountAmt' => 1,
                'SampleNumber' => $sample_number,
                'CreatedByAgentID' => $this->input->post('specify_user'),
                'CollectionObjectID' => $colobjid,
                'PreparedDate' => $prepared_date,
                'PreparedDatePrecision' => $prepared_date_precision,
                'PreparedByID' => $prepared_by_id,
                'PrepTypeID' => $prep_type_id,
            );
            $this->db->insert('preparation', $insertArray);
            return "A new Molecular isolate Preparation record was created: $sample_number.";
        }
        else {
            return "Molecular isolate Preparation record $sample_number already exists.";
        }
    }
    
    public function insertSequence($colobjid, $collection_id, $sample_number, $accession_no, $marker, $sequence, $sequencer, $bold_barcode_id, $bold_sample_id) {
        $this->db->select('DNASequenceID');
        $this->db->from('dnasequence');
        $this->db->where('CollectionObjectID', $colobjid);
        $this->db->where('GenBankAccessionNumber', $accession_no);
        $query = $this->db->get();
        if (!$query->num_rows()) {
            $insertArray = array(
                'TimestampCreated' => date('Y-m-d H:i:s'),
                'TimestampModified' => date('Y-m-d H:i:s'),
                'Version' => 1,
                'CollectionMemberID' => $collection_id,
                'AmbiguousResidues' => $sequence['ambiguous'],
                'BoldBarcodeID' => $bold_barcode_id,
                'BoldSampleID' => $bold_sample_id,
                'CompA' => $sequence['compA'],
                'CompC' => $sequence['compC'],
                'CompG' => $sequence['compG'],
                'CompT' => $sequence['compT'],
                'GenBankAccessionNumber' => $accession_no,
                'GeneSequence' => $sequence['Sequence'],
                'MoleculeType' => 'DNA',
                'TargetMarker' => $marker,
                'Text2' => $sample_number,
                'TotalResidues' => $sequence['total'],
                'CollectionObjectID' => $colobjid,
                'CreatedByAgentID' => $this->input->post('specify_user'),
                'AgentID' => ($sequencer) ? $sequencer : NULL
            );
            
            $this->db->insert('dnasequence', $insertArray);
        }
    }
    
    public function getProjectID($project, $collection_id) {
        $this->db->select('ProjectID');
        $this->db->from('project');
        $this->db->where('ProjectName', $project);
        $this->db->where('CollectionMemberID', $collection_id);
        $query = $this->db->get();
        if ($query->num_rows()) {
            $row = $query->row();
            return $row->ProjectID;
        }
        else
            return FALSE;
    }
    
    public function insertProjectColObj($colobjid, $projectid, $collection_id) {
        $this->db->select('CollectionObjectID');
        $this->db->from('project_colobj');
        $this->db->where('ProjectID', $projectid);
        $this->db->where('CollectionObjectID', $colobjid);
        $query = $this->db->get();
        if (!$query->num_rows()) {
            $insertArray = array(
                'CollectionObjectID' => $colobjid,
                'ProjectID' => $projectid
            );
            $this->db->insert('project_colobj', $insertArray);
        }
    }
    
    public function findGenBankAccessionNumber($colobjid, $accessionno) {
        $this->db->select('DnaSequenceID, BOLDBarcodeID, BOLDSampleID, AgentID, Version');
        $this->db->from('dnasequence');
        $this->db->where('CollectionObjectID', $colobjid);
        $this->db->where('GenBankAccessionNumber', $accessionno);
        $query = $this->db->get();
        if ($query->num_rows()) {
            return $query->row();
        }
        else
            return FALSE;
    }
    
    public function findBOLDBarcodeID($colobjid, $barcode) {
        $this->db->select('DnaSequenceID, BOLDBarcodeID, BOLDSampleID, Text2 AS SampleNumber, AgentID, Version');
        $this->db->from('dnasequence');
        $this->db->where('CollectionObjectID', $colobjid);
        $this->db->where('BOLDBarcodeID', $accessionno);
        $query = $this->db->get();
        if ($query->num_rows()) {
            return $query->row();
        }
        else
            return FALSE;
    }
    
    public function updateBOLD($id, $bold_barcode_id, $bold_sample_id, $version) {
        $update = array(
            'BOLDBarcodeID' => $bold_barcode_id,
            'BOLDSampleID' => $bold_sample_id,
            'TimestampModified' => date('Y-m-d H:i:s'),
            'ModifiedByAgentID' => $this->input->post('specify_user'),
            'Version' => $version
        );
        $this->db->where('DnaSequenceID', $id);
        $this->db->update('dnasequence', $update);
    }
    
    public function insertBOLD($collobjid, $barcode, $sampleId, $sampleNumber, $sequencer) {
        $insArray = array(
            'TimestampCreated' => date('Y-m-d H:i:s'),
            'TimestampModified' => date('Y-m-d H:i:s'),
            'Version' => 1,
            'BOLDBarcodeID' => $barcode,
            'BOLDSampleID' => $sampleId,
            'Text2' => $sampleNumber,
            'AgentID' => $sequencer,
            'CreatedByAgentID' => $this->input->post('specify_user'),
            'ModifiedByAgentID' => $this->input->post('specify_user')
        );
        $this->db->insert('dnasequence', $insArray);
    }
    
    public function updateBOLD2($dnasequenceid, $sampleNumber, $sequencer, $bold_sample_id, $version) {
        $updArray = array(
            'TimestampModified' => date('Y-m-d H:i:s'),
            'Version' => $version,
            'Text2' => $sampleNumber,
            'BOLDSampleID' => $bold_sample_id,
            'AgentID' => $sequencer,
            'ModifiedByAgentID' => $this->input->post('specify_user')
        );
        $this->db->where('DNASequenceID', $dnasequenceid);
        $this->db->update('dnasequence', $updArray);
    }
    
    public function updateSequencer($id, $sequencer, $version) {
        $update = array(
            'AgentID' => $sequencer,
            'TimestampModified' => date('Y-m-d H:i:s'),
            'ModifiedByAgentID' => $this->input->post('specify_user'),
            'Version' => $version
        );
        $this->db->where('DnaSequenceID', $id);
        $this->db->update('dnasequence', $update);
    }
    
    public function getMarkers() {
        $ret = array();
        $this->db->select('Title');
        $this->db->from('picklistitem');
        $this->db->where('PickListID', 251);
        $this->db->order_by('Title');
        $query = $this->db->get();
        if ($query->num_rows()) {
            foreach ($query->result() as $row) {
                $ret[] = $row->Title;
            }
        }
        return $ret;
    }
    
    public function getMarkerDetails() {
        $sql = "SELECT d.targetMarker, if(pi.PickListItemID IS NOT NULL, 1, 0) AS isInPickList, count(*) AS cntSequences
            FROM dnasequence d
            LEFT JOIN picklistitem pi ON d.TargetMarker=pi.`Value` AND pi.PickListID=251
            GROUP BY d.TargetMarker
            UNION
            SELECT `Value`, 1, NULL
            FROM picklistitem pi
            LEFT JOIN dnasequence d ON pi.`Value`=d.TargetMarker
            WHERE pi.PickListID=251 AND d.DnaSequenceID IS NULL";
        $query = $this->db->query($sql);
        return $query->result_array();
    }
    
    public function addNewMarker($marker) {
        $this->db->select('PickListItemID');
        $this->db->from('picklistitem');
        $this->db->where('Value', $marker);
        $this->db->where('PickListID', 251);
        $query = $this->db->get();
        if ($query->num_rows()) {
            return FALSE;
        }
        
        $this->db->select('max(Ordinal) AS Ordinal', FALSE);
        $this->db->from('picklistitem');
        $this->db->where('PickListID', 251);
        $query = $this->db->get();
        $row = $query->row();
        $ordinal = $row->Ordinal;
        
        $insArray = array(
            'TimestampCreated' => date('Y-m-d H:i:s'),
            'TimestampModified' => date('Y-m-d H:i:s'),
            'Version' => 0,
            'Ordinal' => $ordinal + 1,
            'Title' => $marker,
            'Value' => $marker,
            'CreatedByAgentID' => $this->input->post('specify_user'),
            'PickListID' => 251
        );
        $this->db->insert('picklistitem', $insArray);
        $insArray['PickListID'] = 252;
        $this->db->insert('picklistitem', $insArray);
    }
    
    public function getProjects() {
        $sql = "SELECT p.ProjectID, p.ProjectName, count(d.DnaSequenceID) AS cntSequences
            FROM project p
            LEFT JOIN project_colobj pco ON p.ProjectID=pco.ProjectID
            LEFT JOIN collectionobject co ON pco.CollectionObjectID=co.CollectionObjectID
            LEFT JOIN dnasequence d ON co.CollectionObjectID=d.CollectionObjectID
            GROUP BY p.ProjectName";
        $query = $this->db->query($sql);
        return $query->result_array();
    }
    
    public function addNewProject($project, $collectionid) {
        $this->db->select('ProjectID');
        $this->db->from('project');
        $this->db->where('CollectionMemberID', $collectionid);
        $this->db->where('ProjectName', $project);
        $query = $this->db->get();
        if ($query->num_rows()) { // Project already exists
            return FALSE;
        }
        
        $insArray = array(
            'TimestampCreated' => date('Y-m-d H:i:s'),
            'TimestampModified' => date('Y-m-d H:i:s'),
            'Version' => 0,
            'CollectionMemberID' => $collectionid,
            'ProjectName' => $project,
            'CreatedByAgentID' => $this->input->post('specify_user'),
        );
        
        $this->db->insert('project', $insArray);
    }


}

