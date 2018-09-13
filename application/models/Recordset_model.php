<?php
/**
 * @property CI_Loader $load
 * @property CI_Form_validation $form_validation
 * @property CI_Input $input
 * @property CI_Email $email
 * @property CI_DB_active_record $db
 * @property CI_DB_forge $dbforge
 */

class Recordset_model extends CI_Model {

    function  __construct() {
        parent::__construct();

        // connect to database
        $this->load->database();
    }

    function getCollectionObjectRecordSets($collectionId=4) {
        $this->db->select('r.RecordSetID, r.Name, u.Name AS SpecifyUser');
        $this->db->from('recordset r');
        $this->db->join('specifyuser u', 'r.SpecifyUserID=u.SpecifyUserID');
        $this->db->where('CollectionMemberID', $collectionId);
        $this->db->where('Type', 0);
        $this->db->order_by('r.Name');
        $this->db->order_by('SpecifyUser');
        $query = $this->db->get();
        return $query->result_array();
    }

    function getRecordSetFromRange($start, $count=FALSE, $end=FALSE) {
        if (!$count && !$end) return FALSE;
        $items = array();
        if (!$end) $end = $start+$count-1;
        for ($i = $start; $i <= $end; $i++) {
            $select = "SELECT CollectionObjectID
                FROM collectionobject
                WHERE CAST(substring(CatalogNumber, 1, 7) AS unsigned)=$i";
            $query = $this->db->query($select);
            if ($query->num_rows() > 0) {
                foreach ($query->result() as $row) {
                    $items[] = $row->CollectionObjectID;
                }
            }
        }
        return $items;
    }

    function getRecordsFromVrsNos($start, $count=FALSE, $end=FALSE) {
        if (!$count && !$end) return FALSE;
        $start = (int) $start;
        $items = array();
        if (!$end) $end = $start+$count-1;
        else $end = (int) $end;
        for ($i = $start; $i <= $end; $i++) {
            /*$select = "SELECT CollectionObjectID
                FROM collectionobject
                WHERE CAST(substring(CatalogNumber, 1, 7) AS unsigned)=$i";*/
            $select = "SELECT co.CollectionObjectID
                FROM collectionobject co
                JOIN preparation p ON co.CollectionObjectID=p.CollectionObjectID
                WHERE p.PrepTypeID=18 AND CAST(p.SampleNumber AS unsigned)=$i";           
            $query = $this->db->query($select);
            if ($query->num_rows() > 0) {
                foreach ($query->result() as $row) {
                    $items[] = $row->CollectionObjectID;
                }
            }
        }
        return $items;
    }

    function getRecordSetItems($recordsetid) {
        $this->db->select('RecordID');
        $this->db->from('recordsetitem');
        $this->db->where('RecordSetID', $recordsetid);
        $query=$this->db->get();
        $recordsetitems = array();
        foreach($query->result() as $row) {
            $recordsetitems[] = $row->RecordID;
        }
        return $recordsetitems;
    }

    function getSpecifyUsers() {
        $this->db->select('SpecifyUserID, Name');
        $this->db->from('specifyuser');
        $this->db->order_by('Name');
        $query = $this->db->get();
        $specifyusers = array();
        foreach($query->result() as $row)
            $specifyusers[] = array('id' => $row->SpecifyUserID, 'username' => $row->Name);
        return $specifyusers;
    }

    function findRecordSetName($specifyuser, $recordsetname) {
        $this->db->select('RecordSetID');
        $this->db->from('recordset');
        $this->db->where('SpecifyUserID', $specifyuser);
        $this->db->where('Name', $recordsetname);
        $query = $this->db->get();
        if ($query->num_rows() > 0) {
            $row = $query->row();
            return $row->RecordSetID;
        } else return FALSE;
    }

    function createRecordset($specifyuser, $recordsetname, $agentid=FALSE) {
        if (!$agentid)
            $agentid = $this->findAgentID($specifyuser);

        $insert = "INSERT INTO recordset (TimeStampCreated, TimestampModified, Version, CollectionMemberID, TableID, Name,
                `Type`, ModifiedByAgentID, SpecifyUserID, CreatedByAgentID)
            VALUES (NOW(), NOW(), 0, 4, 1, '$recordsetname', 0, $agentid, $specifyuser, $agentid)";
        $this->db->query($insert);
    }
    
    function findAgentID ($specifyuser) {
        $this->db->select('AgentID');
        $this->db->from('agent');
        $this->db->where('SpecifyUserID', $specifyuser);
        $query = $this->db->get();
        if ($query->num_rows() > 0) {
            $row = $query->row();
            return $row->AgentID;
        } else return FALSE;
    }
    
    function findSpecifyUserID ($agentid) {
        $this->db->select('SpecifyUserID');
        $this->db->from('agent');
        $this->db->where('AgentID', $agentid);
        $query = $this->db->get();
        if ($query->num_rows() > 0) {
            $row = $query->row();
            return $row->SpecifyUserID;
        }
        else
            return FALSE;
    }

    function createRecordSetItems($recordsetid, $melnumbers, $parts = FALSE) {
        $melnumbers = explode(',', $melnumbers);
        //print_r($melnumbers);
        foreach ($melnumbers as $key=>$value)
            $melnumbers[$key] = str_replace ('MEL ', '', $value);
        
        $this->db->select('CollectionObjectID');
        $this->db->from('collectionobject');
        $this->db->where_in('AltCatalogNumber', $melnumbers);
        if (!$parts)
            $this->db->where('Modifier', 'A');
        $query = $this->db->get();
        //print_r($query->result());
        if ($query->num_rows()) {
            foreach ($query->result() as $row) {
                $insertarray = array(
                    'RecordSetID' => $recordsetid,
                    'RecordID' => $row->CollectionObjectID
                );
                $this->db->insert('recordsetitem', $insertarray);
            }
        }
    }

    function getCollectionObjectID ($melno, $parts = FALSE) {
        $select = "SELECT CollectionObjectID, CatalogNumber
            FROM collectionobject
            WHERE substring(CatalogNumber, 1, 7) = LPAD('$melno', 7, '0') AND substring(CatalogNumber, 8)='A'";
        $query = $this->db->query($select);
        if ($query->num_rows() > 0) {
            $row = $query->row();
            return $row->CollectionObjectID;
        } else return FALSE;
    }
    
    function getTaxa($melnumbers, $parts = false) 
    {
        $this->db->select('co.CatalogNumber, co.CollectionObjectID, t.FullName, t.Author');
        $this->db->from('collectionobject co');
        $this->db->join('determination d', 'co.CollectionObjectID=d.CollectionObjectID');
        $this->db->join('taxon t', 'd.TaxonID=t.TaxonID');
        $this->db->where_in('CAST(SUBSTRING(co.CatalogNumber, 1, 7) AS unsigned)', 
                $melnumbers, false);
        $this->db->where('d.IsCurrent', true);
        $this->db->where('co.CollectionID', 4);
        if (!$parts) {
            $this->db->where("SUBSTRING(co.CatalogNumber, 8)= 'A'", false, false);
        }
        $this->db->order_by('CatalogNumber');
        $query = $this->db->get();
        if ($query->num_rows()) {
            return $query->result_array();   
        }
        else {
            return false;
        }
        
    }
    
    function getFormattedNameString($colobj, $style='i', $bas=FALSE) { // borrowed from labeldatamodel.php (slightly modified)
        $start = FALSE;
        $end = FALSE;
        if ($style) {
            $start = "<$style>";
            $end = "</$style>";
        }
        
        if (!$bas)
            $select = "SELECT TaxonID, Qualifier, VarQualifier AS QualifierRank, Addendum
                FROM determination
                WHERE IsCurrent=1 AND CollectionObjectID=$colobj";
        else
            $select = "SELECT TaxonID, Qualifier, VarQualifier AS QualifierRank
                FROM determination
                WHERE YesNo1=1 AND CollectionObjectID=$colobj";
        $query = $this->db->query($select);
        if ($query->num_rows() > 0) {
            $row = $query->row();
            $namearray = $this->getNameArray($row->TaxonID);
            $qualifier = $row->Qualifier;
            if($qualifier && $qualifier!='?') $qualifier .= ' ';
            $qualifierrank = ($row->QualifierRank) ? $row->QualifierRank : $namearray['Rank'];
            $formattednamestring = '';
            if(isset($namearray['Species'])) {
                if($qualifier && $qualifierrank=='Genus')
                    $formattednamestring .= $qualifier;
                
                $formattednamestring .= $start;
                if ($namearray['GenusHybrid'] == 'x')
                    $formattednamestring .= '×';
                $formattednamestring .= $namearray['Genus'] . $end;
                if($qualifier && $qualifierrank=='Species')
                    $formattednamestring .= ' ' . $qualifier;
                $formattednamestring .= " $start";
                if ($namearray['SpeciesHybrid'] == 'x')
                    $formattednamestring .= '×';
                elseif ($namearray['SpeciesHybrid'] == 'H')
                    $namearray['Species'] = str_replace (' x ', ' × ', $namearray['Species']);
                $formattednamestring .= $namearray['Species'] . $end;
                if(isset($namearray['Subspecies']) || isset($namearray['variety']) || isset($namearray['forma'])) {
                    if(isset($namearray['forma'])) {
                        if($namearray['forma']!=$namearray['Species']) {
                            if($qualifier && $qualifierrank=='forma')
                                $formattednamestring .= ' ' . $qualifier;
                            if ($namearray['formaHybrid'] == 'x')
                                $formattednamestring .= " nothof. $start" . $namearray['forma'] . $end;
                            else {
                                if ($namearray['formaHybrid'] == 'H')
                                    $namearray['forma'] = str_replace (' x ', ' × ', $namearray['Forma']);
                                $formattednamestring .= " f. $start" . $namearray['forma'] . $end;
                            }
                            $formattednamestring .= ' ' . $namearray['formaAuthor'];
                        } else {
                            $formattednamestring .= ' ' . $namearray['SpeciesAuthor'];
                            if($qualifier && $qualifierrank=='forma')
                                $formattednamestring .= ' ' . $qualifier;
                            $formattednamestring .= " f. $start" . $namearray['forma'] . $end;
                        }
                    } elseif(isset($namearray['variety'])) {
                        if($namearray['variety']!=$namearray['Species']) {
                            if($qualifier && $qualifierrank=='variety')
                                $formattednamestring .= ' ' . $qualifier;
                            if ($namearray['varietyHybrid'] == 'x')
                                $formattednamestring .= " nothovar. $start" . $namearray['variety'] . $end;
                            else {
                                if ($namearray['varietyHybrid'] == 'H')
                                    $namearray['variety'] = str_replace (' x ', ' × ', $namearray['variety']);
                                $formattednamestring .= " var. $start" . $namearray['variety'] . $end;
                            }
                            $formattednamestring .= ' ' . $namearray['varietyAuthor'];
                        } else {
                            $formattednamestring .= ' ' . $namearray['SpeciesAuthor'];
                            if($qualifier && $qualifierrank=='variety')
                                $formattednamestring .= ' ' . $qualifier;
                            $formattednamestring .= " var. $start" . $namearray['variety'] . $end;
                        }
                    } elseif(isset($namearray['Subspecies'])) {
                        if($namearray['Subspecies']!=$namearray['Species']) {
                            if($qualifier && $qualifierrank=='subspecies')
                                $formattednamestring .= " nothosubsp. $start" . $namearray['Subspecies'] . $end;
                            if ($namearray['SubspeciesHybrid'] == 'x')
                                $namearray['Subspecies'] = '×' . $namearray['Subspecies'];
                            else {
                                if ($namearray['SubspeciesHybrid'] == 'H')
                                    $namearray['Subspecies'] = str_replace (' x ', ' × ', $namearray['Subspecies']);
                                $formattednamestring .= " subsp. $start" . $namearray['Subspecies'] . $end;
                            }
                            $formattednamestring .= ' ' . $namearray['SubspeciesAuthor'];
                        } else {
                            $formattednamestring .= ' ' . $namearray['SpeciesAuthor'];
                            if($qualifier && $qualifierrank=='subspecies')
                                $formattednamestring .= $qualifier;
                            $formattednamestring .= " subsp. $start" . $namearray['Subspecies'] . $end;
                        }
                    }
                } else $formattednamestring .= ' ' . $namearray['SpeciesAuthor'];
            } else {
                $rankarray = array('Genus', 'Tribe', 'Subfamily', 'Family', 'Suborder', 'Order', 'Superorder',
                    'Subclass', 'Class', 'Subdivision', 'Division', 'Subkingdom', 'Kingdom');
                foreach($rankarray as $rank) {
                    if(isset($namearray[$rank])) {
                        if($qualifier && $qualifierrank==$rank)
                            $formattednamestring .= ' ' . $qualifier;
                            if (isset($namearray['Genus']) && $namearray['GenusHybrid'] == 'x')
                                $namearray['Genus'] = '×' . $namearray['Genus'];
                            elseif (isset($namearray['Genus']) && $namearray['GenusHybrid'] == 'H')
                                $namearray['Genus'] = str_replace (' x ', ' × ', $namearray['Genus']);
                        $formattednamestring .= $start . $namearray[$rank] . $end;
                        $formattednamestring .= ($namearray[$rank.'Author']) ? ' ' . $namearray[$rank.'Author'] : '';
                        break;
                    }
                }
            }
            $formattednamestring = str_replace("$end $start", ' ', $formattednamestring);
            if (isset($row->Addendum) && $row->Addendum) $formattednamestring .= ' ' . $row->Addendum;
            return $formattednamestring;
        } else return FALSE;
    }
    
    function getNameArray($taxonid) { // borrowed from labeldatamodel
        if($taxonid) {
            $namearray = array();
            $select = "SELECT t.Name, t.Author, d.Name AS Rank, t.UsfwsCode AS HybridFlag, d.RankID, t.ParentID
                FROM taxon t
                JOIN taxontreedefitem d ON t.TaxonTreeDefItemID=d.TaxonTreeDefItemID
                WHERE t.TaxonID=$taxonid";
            $query = $this->db->query($select);
            $row = $query->row();

            $namearray['Rank'] = $row->Rank;
            $namearray[$row->Rank] = $row->Name;
            $namearray[$row->Rank.'Author'] = $row->Author;
            $namearray[$row->Rank.'Hybrid'] = $row->HybridFlag;
            $rankid = $row->RankID;
            $parentid = $row->ParentID;
            while($rankid>160) {
                $select = "SELECT t.Name, t.Author, d.Name AS Rank, t.UsfwsCode AS HybridFlag, d.RankID, t.ParentID
                    FROM taxon t
                    JOIN taxontreedefitem d ON t.TaxonTreeDefItemID=d.TaxonTreeDefItemID
                    WHERE t.TaxonID=$parentid";
                $query = $this->db->query($select);
                $row = $query->row();
                $namearray[$row->Rank] = $row->Name;
                $namearray[$row->Rank.'Author'] = $row->Author;
                $namearray[$row->Rank.'Hybrid'] = $row->HybridFlag;
                $rankid = $row->RankID;
                $parentid = $row->ParentID;
            }
            return $namearray;
        } else return false;
    }
    
    function xml_convert($string) {
        return str_replace('&', '&amp;', $string);
    }
    
    function getRecordSetUsers() {
        $this->db->select("u.SpecifyUserID, u.Name AS Username, IF(a.MiddleInitial IS NOT NULL, 
            CONCAT_WS(' ', a.MiddleInitial, a.LastName), a.LastName) AS AgentName, 
            count(*) AS NumberOfRecordSets", FALSE);
        $this->db->from('specifyuser u');
        $this->db->join('agent a', 'u.SpecifyUserID=a.SpecifyUserID', 'left');
        $this->db->join('recordset r', 'u.SpecifyUserID=r.SpecifyUserID');
        $this->db->where('r.Type', 0);
        $this->db->group_by('u.SpecifyUserID, a.AgentID');
        $query = $this->db->get();
        return $query->result_array();
    }
    
    function getRecordSetsForUser($specifyUserID) {
        $this->db->select('RecordSetID');
        $this->db->from('recordset');
        $this->db->where('SpecifyUserID', $specifyUserID);
        $this->db->where('Type', 0);
        $query = $this->db->get();
        return $query->result();
    }
    
    function deleteRecordSet($recordSetID) {
        $this->deleteRecordSetItems($recordSetID);
        $this->db->where('RecordSetID', $recordSetID);
        $this->db->delete('recordset');
    }
    
    function deleteRecordSetItems($recordSetID) {
        $this->db->where('RecordSetID', $recordSetID);
        $this->db->delete('recordsetitem');
    }
}

?>
