<?php
/**
 * @property CI_Loader $load
 * @property CI_Form_validation $form_validation
 * @property CI_Input $input
 * @property CI_Email $email
 * @property CI_DB_active_record $db
 * @property CI_DB_forge $dbforge
 */

/**
 * MELISR GpiModel Class
 * 
 * Model for GPI application. 
 * 
 * @package     GPI
 * @author      Niels Klazenga
 * @copyright   copyright (c) 2010-2012, Royal Botanic Gardens, Melbourne
 * @license     http://creativecommons.org/licenses/by/3.0/ CC BY 3.0
 */
class Gpi_model extends CI_Model {

    public function  __construct() {
        parent::__construct();
        $this->load->helper('xml');
    }
    
    /**
     * showDataSets Function
     * 
     * Returns data set information. Used for index page of application
     * @return array|FALSE 
     */
    public function showDataSets() {
        $this->db->select('p.ProjectNumber as BatchNo, DATE(p.EndDate) AS DateUploaded,
            count(*) AS NumRecords, count(co.YesNo5) AS NumMarked', FALSE);
        $this->db->select('sum(e.hasError) as NumErrors', false);
        $this->db->select("count(if(co.Modifier!='A', 1, null)) as NumParts");
        $this->db->from('project p');
        $this->db->join('project_colobj pco', 'p.ProjectID=pco.ProjectID');
        $this->db->join('collectionobject co', 'pco.CollectionObjectID=co.CollectionObjectID');
        $this->db->join('gpi_error e', 'pco.CollectionObjectID=e.CollectionObjectID');
        $this->db->like('p.ProjectName', 'GPI ', 'after');
        $this->db->group_by('p.ProjectID');
        $query = $this->db->get();
        return $query->result_array();
    }

    public function getCollectionObjects($units) {
        $catnos = array();
        foreach ($units as $unit) {
            if (preg_match('/[A-Z]$/', $unit)) {
                $catnos[] = str_pad(substr($unit, 0, strlen($unit)-1), 7, '0', STR_PAD_LEFT) . substr($unit, -1);
            }
            else {
                $catnos[] = str_pad($unit, 7, '0', STR_PAD_LEFT) . 'A';
            }
        }
        $this->db->select('co.CollectionObjectID');
        $this->db->from('collectionobject co');
        $this->db->where_in('co.CatalogNumber', $catnos);
        $this->db->order_by('co.CatalogNumber');
        $query = $this->db->get();
        $colobjs = array();
        if ($query->num_rows()) {
            foreach ($query->result() as $row) {
                $colobjs[] = $row->CollectionObjectID;
            }
        }
        return $colobjs;
    }
    
    /**
     * insertDataSet Function
     * 
     * Inserts the data set record, one record for each batch. The data set number
     * is the same as the batch number.
     * @param integer $batchno 
     */
    public function createOrUpdateProject($batchno) {
        $this->db->select('ProjectID, Version');
        $this->db->from('project');
        $this->db->where('ProjectName', "GPI – batch $batchno");
        $query = $this->db->get();
        if ($query->num_rows()) {
            $row = $query->row();
            
            $this->db->where('ProjectID', $row->ProjectID);
            $this->db->update('project', array(
                'EndDate' => data('Y-m-d'),
                'Version' => $row->Version + 1,
                'TimestampModified' => date('Y-m-d H:i:s')
            ));
            
            $this->db->where('ProjectID', $row->ProjectID);
            $this->db->delete('project_colobj');
        }
        else {
            $this->db->insert('project', array(
                'TimestampCreated' => date('Y-m-d H:i:s'),
                'TimestampModified' => date('Y-m-d H:i:s'),
                'Version' => 1,
                'CollectionMemberID' => 4,
                'ProjectName' => "GPI – batch $batchno",
                'ProjectNumber' => "$batchno",
                'ProjectAgentID' => 2,
                'CreatedByAgentID' => 1
            )); 
        }
    }
    
    /**
     * Inserts associations between GPI data set and collection objects
     * @param type $batchno
     * @param type $colobjs
     */
    public function insertCollectionObjects($batchno, $colobjs)
    {
        $this->db->select('ProjectID');
        $this->db->from('project');
        $this->db->like('ProjectMame', 'GPI ', 'after');
        $this->db->where('ProjectNumber', $batchno);
        $query = $this->db->get();
        
        if ($query->num_rows()) {
            $row = $query->row();
            foreach ($colobjs as $colobj) {
                $data = array(
                    'ProjectID' => $row->ProjectID,
                    'CollectionObjectID' => $colobj
                );
                $this->db->insert('project_colobj', $data);
            }
        }
    }
    
    public function getDarwinCoreData($batch) 
    {
        $this->db->select('dwc.*', false);
        $this->db->from('project p');
        $this->db->join('project_colobj pco', 'p.ProjectID=pco.ProjectID');
        $this->db->join('mel_avh_occurrence_core dwc', 'pco.CollectionObjectID=dwc.id');
        $this->db->like('p.ProjectName', 'GPI ', 'after');
        $this->db->where('p.ProjectNumber', $batch);
        $query = $this->db->get();
        return $query->result_array();
    }
    
    public function getDataset($batch)
    {
        $this->db->select("p.ProjectNumber, p.EndDate as DateSupplied, concat(pa.MiddleInitial, ' ', pa.LastName) as PersonName", false);
        $this->db->from('project p');
        $this->db->join('agent pa', 'p.ProjectAgentID=pa.AgentID');
        $this->db->like('p.ProjectName', 'GPI ', 'after');
        $this->db->where('p.ProjectNumber', $batch);
        $query = $this->db->get();
        if ($query->num_rows()) {
            return $query->row();
        }
        return false;
    }
    
    public function getUnits($batch)
    {
        $this->db->select('u.*', false);
        $this->db->from('project p');
        $this->db->join('project_colobj pco', 'p.ProjectID=pco.ProjectID');
        $this->db->join('gpi_unit u', 'pco.CollectionObjectID=u.CollectionObjectID');
        $this->db->like('p.ProjectName', 'GPI ', 'after');
        $this->db->where('p.ProjectNumber', $batch);
        $query = $this->db->get();
        return $query->result();
    }
    
    public function getIdentifications($batch)
    {
        $this->db->select('i.*', false);
        $this->db->from('project p');
        $this->db->join('project_colobj pco', 'p.ProjectID=pco.ProjectID');
        $this->db->join('gpi_identification i', 'pco.CollectionObjectID=i.CollectionObjectID');
        $this->db->like('p.ProjectName', 'GPI ', 'after');
        $this->db->where('p.ProjectNumber', $batch);
        $query = $this->db->get();
        return $query->result();
    }

    public function createErrorRecordSet($batchno, $recsetname, $spuser, $recsetitems) {
        $this->db->select('co.CollectionObjectID');
        $this->db->from('collectionobject co');
        $this->db->where_in('co.CollectionObjectID', $recsetitems);
        $query = $this->db->get();
        $items = array();
        if ($query->num_rows()) {
            foreach ($query->result() as $row)
                $items[] = $row->CollectionObjectID;
        }
        if ($items) {
            $this->db->select('MAX(RecordSetID) AS max', FALSE);
            $this->db->from('recordset');
            $query = $this->db->get();
            $row = $query->row();
            $recordsetid = $row->max + 1;

            $this->db->select('AgentID');
            $this->db->from('agent');
            $this->db->where('SpecifyUserID', $spuser);
            $query = $this->db->get();
            $row = $query->row();
            $agentid = $row->AgentID;

            $recordSetArray = array(
                'RecordSetID' => $recordsetid,
                'TimestampCreated' => date('Y-m-d H-i-s'),
                'TimestampModified' => date('Y-m-d H-i-s'),
                'Version' => 0,
                'CollectionMemberID' => 4,
                'TableID' => 1,
                'Name' => $recsetname,
                'Type' => 0,
                'SpecifyUserID' => $spuser,
                'CreatedByAgentID' => $agentid,
                'ModifiedByAgentID' => $agentid
            );

            $this->db->insert('recordset', $recordSetArray);

            foreach ($items as $item) {
                $recordsetItemArray = array(
                    'RecordID' => $item,
                    'RecordSetID' => $recordsetid
                );
                $this->db->insert('recordsetitem', $recordsetItemArray);
            }
            
            return $recordsetid;
        }
    }

    public function getParts($batchno) {
        $parts = array();
        $this->db->select('MelNumber');
        $this->db->from('gpi_unit u');
        $this->db->join('project_colobj pco', 'u.UnitID=pco.CollectionObjectID');
        $this->db->join('project p', 'pco.ProjectID=p.ProjectID');
        $this->db->where('p.ProjectNumber', $batchno);
        $this->db->where("u.MelNumber REGEXP '[A-Z]{1}$'", FALSE, FALSE);
        $query = $this->db->get();
        if ($query->num_rows()) {
            foreach ($query->result() as $row) {
                $parts[] = $row->MelNumber;
            }
        }
        return $parts;
    }

    public function showErrors($batchno) {
        $notAType = <<<SQL
select 'NotAType' as `Type`, p.ProjectNumber as BatchNo, co.CollectionObjectID, co.CatalogNumber, t.FullName, t.Author, t.CommonName AS Protologue, t.Number2 AS Year, ty.TypeStatusName
from project p
join project_colobj pco on p.ProjectID=pco.ProjectID
join collectionobject co on pco.CollectionObjectID=co.CollectionObjectID
left join determination ty on co.CollectionObjectID=ty.CollectionObjectID and ty.YesNo1=true
left join taxon t on ty.TaxonID=t.TaxonID
where p.ProjectName like 'GPI %' and ty.DeterminationID is null
SQL;
        $typeStatusEqualsCurrent = <<<SQL
select 'TypeStatusEqualsCurrent' as `Type`, p.ProjectNumber as BatchNo, co.CollectionObjectID, co.CatalogNumber, t.FullName, t.Author, t.CommonName AS Protologue, t.Number2 AS Year, ty.TypeStatusName
from project p
join project_colobj pco on p.ProjectID=pco.ProjectID
join collectionobject co on pco.CollectionObjectID=co.CollectionObjectID
join determination cd on co.CollectionObjectID=cd.CollectionObjectID and cd.IsCurrent=true
join determination ty on co.CollectionObjectID=ty.CollectionObjectID and ty.YesNo1=true
left join taxon t on ty.TaxonID=t.TaxonID
where p.ProjectName like 'GPI %' and ty.DeterminationID=cd.DeterminationID
SQL;
        $notABasionym = <<<SQL
select 'NotABasionym' as `Type`, p.ProjectNumber as BatchNo, co.CollectionObjectID, co.CatalogNumber, t.FullName, t.Author, t.CommonName AS Protologue, t.Number2 AS Year, ty.TypeStatusName
from project p
join project_colobj pco on p.ProjectID=pco.ProjectID
join collectionobject co on pco.CollectionObjectID=co.CollectionObjectID
join determination ty on co.CollectionObjectID=ty.CollectionObjectID and ty.YesNo1=true
left join taxon t on ty.TaxonID=t.TaxonID
where p.ProjectName like 'GPI %' and t.Author like '(%'
SQL;
        $noSpecies = <<<SQL
select 'NoSpecies' as `Type`, p.ProjectNumber as BatchNo, co.CollectionObjectID, co.CatalogNumber, t0.FullName, t0.Author, t0.CommonName AS Protologue, t0.Number2 AS Year, ty.TypeStatusName
from project p
join project_colobj pco on p.ProjectID=pco.ProjectID
join collectionobject co on pco.CollectionObjectID=co.CollectionObjectID
join determination ty on co.CollectionObjectID=ty.CollectionObjectID and ty.YesNo1=true
left join taxon t0 on ty.TaxonID=t0.TaxonID
left join taxontreedefitem tdi0 
  on t0.TaxonTreeDefItemID=tdi0.TaxonTreeDefItemID
left join taxon t1 on t0.ParentID=t1.TaxonID
left join taxontreedefitem tdi1 
  on t1.TaxonTreeDefItemID=tdi1.TaxonTreeDefItemID
left join taxon t2 on t1.ParentID=t2.TaxonID
left join taxontreedefitem tdi2 
  on t2.TaxonTreeDefItemID=tdi2.TaxonTreeDefItemID  
where p.ProjectName like 'GPI %' and case when tdi0.Name='species' then t0.Name 
  when tdi1.Name='species' then t1.Name when tdi2.Name='species' then t2.Name 
  else null end is null
SQL;
        $noAuthor = <<<SQL
select 'NoAuthor' as `Type`, p.ProjectNumber as BatchNo, co.CollectionObjectID, co.CatalogNumber, t.FullName, t.Author, t.CommonName AS Protologue, t.Number2 AS Year, ty.TypeStatusName
from project p
join project_colobj pco on p.ProjectID=pco.ProjectID
join collectionobject co on pco.CollectionObjectID=co.CollectionObjectID
join determination ty on co.CollectionObjectID=ty.CollectionObjectID and ty.YesNo1=true
left join taxon t on ty.TaxonID=t.TaxonID
where p.ProjectName like 'GPI %' and t.Author is null
SQL;
        $noProtologue = <<<SQL
select 'NoProtologue' as `Type`, p.ProjectNumber as BatchNo, co.CollectionObjectID, co.CatalogNumber, t.FullName, t.Author, t.CommonName AS Protologue, t.Number2 AS Year, ty.TypeStatusName
from project p
join project_colobj pco on p.ProjectID=pco.ProjectID
join collectionobject co on pco.CollectionObjectID=co.CollectionObjectID
join determination ty on co.CollectionObjectID=ty.CollectionObjectID and ty.YesNo1=true
left join taxon t on ty.TaxonID=t.TaxonID
where p.ProjectName like 'GPI %' and (t.Author IS NULL OR t.CommonName IS NULL OR t.Number2 IS NULL)
SQL;
        
        $stmts = array(
            $notAType,
            $typeStatusEqualsCurrent,
            $notABasionym,
            $noSpecies,
            $noAuthor,
            $noProtologue
        );
        
        $ret = array();
        foreach ($stmts as $stmt) {
            if ($batchno) {
                $query = $this->db->query($stmt . ' and p.ProjectNumber=?', array($batchno));
            }
            else {
                $query = $this->db->query($stmt);
            }
            $ret = array_merge($ret, $query->result_array());
       }
        return $ret;
    }
    
    public function getSpecifyUsers() {
        $ret = array();
        $this->db->select('SpecifyUserID, Name');
        $this->db->from('specifyuser');
        $this->db->where_in('UserType', array('Manager', 'FullAccess'));
        $this->db->order_by('Name');
        $query = $this->db->get();
        if ($query->num_rows()) {
            $ret['0'] = '(Specify user)';
            foreach ($query->result() as $row)
                $ret[$row->SpecifyUserID] = $row->Name;
        }
        return $ret;
    }
    
    public function markInMelisr($batch) {
        $this->db->select('co.CollectionObjectID');
        $this->db->from('collectionobject co');
        $this->db->join('project_colobj pco', 'co.CollectionObjectID=pco.CollectionObjectID');
        $this->db->join('project p', 'pco.ProjectID=p.ProjectID');
        $this->db->where('p.ProjectNumber', $batch);
        $query = $this->db->get();
        foreach ($query->result() as $row) {
            $this->db->where('CollectionObjectID', $row->CollectionObjectID);
            $this->db->update('collectionobject', array('YesNo5'=>1));
        }
    }

}

/*
 * /application/models/Gpi_model.php
 */