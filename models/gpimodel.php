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
class GpiModel extends Model {

    public function  __construct() {
        parent::Model();

        // connect to database
        $this->load->database();
        $this->load->helper('xml');
    }

    /**
     * insertDataSet Function
     * 
     * Inserts the data set record, one record for each batch. The data set number
     * is the same as the batch number.
     * @param integer $batchno 
     */
    public function insertDataSet($batchno) {
        $object = new DataSet;
        $object->DataSetID = $batchno;
        $object->BatchNo = $batchno;
        $object->DateSupplied = date('Y-m-d');
        $this->db->insert('gpi.dataset', $object);
    }

    /**
     * insertUnits Function
     * 
     * Inserts records into the unit table, one for each MEL number.
     * @param array $units
     * @param integer $datasetid 
     */
    public function insertUnits($units, $datasetid) {
        $this->db->select("$datasetid AS DataSetID");
        $this->db->select('co.CollectionObjectID as SpCollectionObjectID', FALSE);
        $this->db->select("CONCAT('MEL', CAST(SUBSTRING(co.CatalogNumber, 1, 7) AS unsigned)) AS MelNumber", FALSE);
        $this->db->select('DATE(co.TimestampModified) AS DateLastModified', FALSE);
        $this->db->select('collectorstring(ce.CollectingEventID, 1) AS Collectors', FALSE);
        $this->db->select('ce.StationFieldNumber AS CollectorNumber', FALSE);
        $this->db->select('IF(ce.StartDatePrecision=1, DAYOFMONTH(ce.StartDate), NULL) AS CollectionDateStartDay', FALSE);
        $this->db->select('IF(ce.StartDatePrecision<3, MONTH(ce.StartDate), NULL) AS CollectionDateStartMonth', FALSE);
        $this->db->select('YEAR(ce.StartDate) AS CollectionDateStartYear', FALSE);
        $this->db->select('IF(ce.EndDatePrecision=1, DAYOFMONTH(ce.EndDate), NULL) AS CollectionDateEndDay', FALSE);
        $this->db->select('IF(ce.EndDatePrecision<3, MONTH(ce.EndDate), NULL) AS CollectionDateEndMonth', FALSE);
        $this->db->select('YEAR(ce.EndDate) AS CollectionDateEndYear', FALSE);
        $this->db->select('cea.Text6 AS CollectionDateOtherText', FALSE);
        //$this->db->select('country(l.GeographyID) AS CountryName', FALSE);
        $this->db->select('l.LocalityName AS Locality, l.GeographyID', FALSE);
        $this->db->select("IF(l.MaxElevation, CONCAT(l.MinElevation, '–', l.MaxElevation), l.MinElevation) AS Altitude", FALSE);
        
        $this->db->from('collectionobject co');
        $this->db->join('collectingevent ce', 'co.CollectingEventID=ce.CollectingEventID');
        $this->db->join('collectingeventattribute cea', 'ce.CollectingEventAttributeID=cea.CollectingEventAttributeID', 'left');
        $this->db->join('locality l', 'ce.LocalityID=l.LocalityID');

        $this->db->where_in('co.CatalogNumber', $units);
        $this->db->order_by('co.CatalogNumber');

        $query = $this->db->get();
        if ($query->num_rows()) {
            foreach ($query->result() as $row) {
                $unit = new Unit();
                $unit->DataSetID = $row->DataSetID;
                $unit->SpCollectionObjectID = $row->SpCollectionObjectID;
                $unit->MelNumber = $row->MelNumber;
                $unit->DateLastModified = $row->DateLastModified;
                $unit->Collectors = $row->Collectors;
                    if (!$unit->Collectors) $unit->Collectors = 'Not on sheet';
                $unit->CollectorNumber = $row->CollectorNumber;
                $unit->CollectionDateStartDay = $row->CollectionDateStartDay;
                $unit->CollectionDateStartMonth = $row->CollectionDateStartMonth;
                $unit->CollectionDateStartYear = $row->CollectionDateStartYear;
                $unit->CollectionDateEndDay = $row->CollectionDateEndDay;
                $unit->CollectionDateEndMonth = $row->CollectionDateEndMonth;
                $unit->CollectionDateEndYear = $row->CollectionDateEndYear;
                $unit->CollectionDateOtherText = $row->CollectionDateOtherText;
                if (!$unit->CollectionDateStartYear && !$unit->CollectionDateOtherText)
                    $unit->CollectionDateOtherText = 'Not on sheet';
                $unit->CountryName = $this->getCountryOrState($row->GeographyID, 'country');
                if ($unit->CountryName == 'Australia') 
                    $unit->ISO2Letter = 'AU';
                $state = $this->getCountryOrState($row->GeographyID, 'state');
                if ($state)
                    $unit->Locality = $state . '. ';
                $unit->Locality .= $row->Locality;
                $unit->Altitude = $row->Altitude;
                $this->db->insert('gpi.unit', $unit);
            }
        }
    }
    
    public function updateUnits($units) {
        $this->db->select('UnitID, SpCollectionObjectID');
        $this->db->from('gpi.unit');
        $this->db->where_in('MelNumber', $units);
        $query = $this->db->get();
        
        if ($query->num_rows()) {
            $unitids = array();
            $collectionobjectids = array();
            foreach ($query->result() as $row) {
                $unitids[] = $row->UnitID;
                $collectionobjectids[] = $row->SpCollectionObjectID;
            }
            
            foreach ($unitids as $unitid) {
                // delete old identifications
                $this->db->delete('gpi.identification', array('UnitID' => $unitid));
                
                
                // update unit
                
            
            
                // insert new identifications
                $this->insertIdentificationsForUnit($unitid);
            }
            
            
            
            
            
        }
        
        
        
    }
    
    private function insertIdentificationsForUnit($unitid) {
        $this->db->select('d.DeterminationID AS SpDeterminationID, u.UnitID, t.NodeNumber, t.TaxonTreeDefItemID, 
            t.FullName, t.Author, d.TypeStatusName, d.VarQualifier');
        $this->db->select('d.Qualifier, d.VarQualifier AS QualifierRank');
        $this->db->select("IF(!isnull(a.Firstname), CONCAT_WS(', ', a.LastName, a.FirstName), a.LastName) AS Identifier", FALSE);
        $this->db->select('IF(d.DeterminedDatePrecision=1, DAYOFMONTH(d.DeterminedDate), NULL) AS IdentificationDateStartDay,
            IF(d.DeterminedDatePrecision<3, MONTH(d.DeterminedDate), NULL) AS IdentificationDateStartMonth,
            YEAR(d.DeterminedDate) AS IdentificationDateStartYear', FALSE);
        $this->db->select("IF(d.YesNo1=1, 'TRUE', 'FALSE') AS StoredUnderName", FALSE);
        $this->db->select("IF(d.IsCurrent=1, 'TRUE', 'FALSE') AS CurrentName", FALSE);
        
        $this->db->from('determination d');
        $this->db->join('taxon t', 'd.TaxonID=t.TaxonID');
        $this->db->join('agent a', 'd.DeterminerID=a.AgentID', 'left');
        $this->db->join('collectionobject co', 'd.CollectionObjectID=co.CollectionObjectID');
        $this->db->join('gpi.unit u', 'co.CollectionObjectID=u.SpCollectionObjectID');
        
        $this->db->where('(d.IsCurrent=1 OR d.YesNo1=1)', FALSE, FALSE);
        $this->db->where('u.UnitID', $unitid);

        $query = $this->db->get();
        
        if ($query->num_rows()) {
            foreach ($query->result() as $row) {
                $identification = new Identification;
                $identification->SpDeterminationID = $row->SpDeterminationID;
                //$name = $this->getTaxonNameArray($row->TaxonID);
                $name = explode(' ', $row->FullName);

                $identification->Family = $this->getFamily($row->NodeNumber);
                if ($row->Qualifier && ($row->QualifierRank=='Genus' || (!$row->QualifierRank && $row->TaxonTreeDefItemID == 12)))
                    $identification->GenusQualifier = $row->Qualifier;
                else
                    $identification->GenusQualifier = NULL;
                $identification->Genus = (isset($name[0])) ? $name[0] : NULL;
                $identification->InfraspecificRank = NULL;
                $identification->InfraspecificEpithet = NULL;
                $identification->Author = str_replace(' & ', ' &amp; ', $row->Author);

                if ($row->Qualifier && ($row->QualifierRank=='Species' || (!$row->QualifierRank && $row->TaxonTreeDefItemID == 13)))
                    $identification->SpeciesQualifier = $row->Qualifier;
                else
                    $identification->SpeciesQualifier = NULL;
                $identification->Species = (isset($name[1])) ? $name[1] : NULL;

                if (count($name) == 4 && in_array($name[2], array('subsp.', 'var.', 'subvar.', 'f.', 'subf.'))) {
                    $identification->InfraspecificRank = $name[2];
                    $identification->InfraspecificEpithet = $name[3];
                    if ($name[3] == $name[1])
                        $identification->Author = $this->getSpeciesAuthor ($row->NodeNumber);
                }
                 $identification->PlantNameCode = NULL;
                $identification->Identifier = ($row->Identifier) ? $row->Identifier : 'Not on sheet';
                $identification->IdentificationDateStartDay = $row->IdentificationDateStartDay;
                $identification->IdentificationDateStartMonth = $row->IdentificationDateStartMonth;
                $identification->IdentificationDateStartYear = $row->IdentificationDateStartYear;
                $identification->IdentificationDateEndDay = NULL;
                $identification->IdentificationDateEndMonth = NULL;
                $identification->IdentificationDateEndYear = NULL;
                if (!$identification->IdentificationDateStartYear)
                    $identification->IdentificationDateOtherText = 'Not on sheet';
                if ($row->TypeStatusName) {
                    switch ($row->TypeStatusName) {
                        case 'Paralectotype':
                            $identification->TypeStatus = 'Syntype';
                            break;
                        case 'Paraneotype':
                            $identification->TypeStatus = 'Syntype';
                            break;
                        case 'Authentic specimen':
                            $identification->TypeStatus = 'Original material';
                            break;
                        default:
                            $identification->TypeStatus = $row->TypeStatusName;
                    }
                    if ($row->VarQualifier) {
                        $identification->TypeStatus = ($identification->TypeStatus == 'Original material') ? 'Original material ?' : 'Type ?';
                    }
                }
                else 
                    $identification->TypeStatus = '-';
                $identification->UnitID = $row->UnitID;
                $identification->StoredUnderName = $row->StoredUnderName;
                $identification->CurrentName = $row->CurrentName;

                $this->db->insert('gpi.identification', $identification);
            }
        }
    }
    
    /**
     * getCountryorState Function
     * 
     * Finds the country or state, as set in the second parameter, for a given 
     * GeographyID. It does this by first retrieving the Node number and then
     * looking for either the country or state in the lineage leading to the node.
     * @param integer $geographyid
     * @param string $what
     * @return string 
     */
    private function getCountryOrState($geographyid, $what='country') {
        $this->db->select('NodeNumber');
        $this->db->from('geography');
        $this->db->where('GeographyID', $geographyid);
        $query = $this->db->get();
        if ($query->num_rows()) {
            $row = $query->row();
            $nodenumber = $row->NodeNumber;
        }
        else
            return FALSE;
        if ($what == 'country')
            $geographytreedefitemid = 3;
        elseif ($what == 'state')
            $geographytreedefitemid = 4;
        else
            return FALSE;
        $this->db->select('Name');
        $this->db->from('geography');
        $this->db->where('GeographyTreeDefItemID', $geographytreedefitemid);
        $this->db->where('NodeNumber <=', $nodenumber);
        $this->db->where('HighestChildNodeNumber >=', $nodenumber);
        $query = $this->db->get();
        if ($query->num_rows()) {
            $row = $query->row();
            return $row->Name;
        }
        else
            return NULL;

    }
    
    /**
     * insertIdentifications Function
     * 
     * Inserts identification information in identification table.
     * @param array $units 
     */
    public function insertIdentifications($units) {
        $this->db->select('d.DeterminationID AS SpDeterminationID, u.UnitID, t.NodeNumber, t.TaxonTreeDefItemID, 
            t.FullName, t.Author, d.TypeStatusName, d.VarQualifier');
        $this->db->select('d.Qualifier, d.VarQualifier AS QualifierRank');
        $this->db->select("IF(!isnull(a.Firstname), CONCAT_WS(', ', a.LastName, a.FirstName), a.LastName) AS Identifier", FALSE);
        $this->db->select('IF(d.DeterminedDatePrecision=1, DAYOFMONTH(d.DeterminedDate), NULL) AS IdentificationDateStartDay,
            IF(d.DeterminedDatePrecision<3, MONTH(d.DeterminedDate), NULL) AS IdentificationDateStartMonth,
            YEAR(d.DeterminedDate) AS IdentificationDateStartYear', FALSE);
        $this->db->select("IF(d.YesNo1=1, 'TRUE', 'FALSE') AS StoredUnderName", FALSE);
        $this->db->select("IF(d.IsCurrent=1, 'TRUE', 'FALSE') AS CurrentName", FALSE);
        
        $this->db->from('determination d');
        $this->db->join('taxon t', 'd.TaxonID=t.TaxonID');
        $this->db->join('agent a', 'd.DeterminerID=a.AgentID', 'left');
        $this->db->join('collectionobject co', 'd.CollectionObjectID=co.CollectionObjectID');
        $this->db->join('gpi.unit u', 'co.CollectionObjectID=u.SpCollectionObjectID');
        
        $this->db->where('(d.IsCurrent=1 OR d.YesNo1=1)', FALSE, FALSE);
        $this->db->where_in('co.CatalogNumber', $units);

        $query = $this->db->get();

        if ($query->num_rows()) {
            foreach ($query->result() as $row) {
                $identification = new Identification;
                $identification->SpDeterminationID = $row->SpDeterminationID;
                //$name = $this->getTaxonNameArray($row->TaxonID);
                $name = explode(' ', $row->FullName);

                $identification->Family = $this->getFamily($row->NodeNumber);
                if ($row->Qualifier && ($row->QualifierRank=='Genus' || (!$row->QualifierRank && $row->TaxonTreeDefItemID == 12)))
                    $identification->GenusQualifier = $row->Qualifier;
                else
                    $identification->GenusQualifier = NULL;
                $identification->Genus = (isset($name[0])) ? $name[0] : NULL;
                $identification->InfraspecificRank = NULL;
                $identification->InfraspecificEpithet = NULL;
                $identification->Author = str_replace(' & ', ' &amp; ', $row->Author);

                if ($row->Qualifier && ($row->QualifierRank=='Species' || (!$row->QualifierRank && $row->TaxonTreeDefItemID == 13)))
                    $identification->SpeciesQualifier = $row->Qualifier;
                else
                    $identification->SpeciesQualifier = NULL;
                $identification->Species = (isset($name[1])) ? $name[1] : NULL;

                if (count($name) == 4 && in_array($name[2], array('subsp.', 'var.', 'subvar.', 'f.', 'subf.'))) {
                    $identification->InfraspecificRank = $name[2];
                    $identification->InfraspecificEpithet = $name[3];
                    if ($name[3] == $name[1])
                        $identification->Author = $this->getSpeciesAuthor ($row->NodeNumber);
                }
                /*switch ($name['Rank']) {
                    case 'Subspecies':
                        $identification->InfraspecificRank = 'subsp.';
                        $identification->InfraspecificEpithet = $name['Subspecies'];
                        $identification->Author = ($name['Subspecies'] != $name['Species']) ? $name['SubspeciesAuthor'] : NULL;
                        break;
                    case 'Variety':
                        $identification->InfraspecificRank = 'var.';
                        $identification->InfraspecificEpithet = $name['Variety'];
                        $identification->Author = ($name['Variety'] != $name['Species']) ? $name['VarietyAuthor'] : NULL;
                        break;
                    case 'Forma':
                        $identification->InfraspecificRank = 'f.';
                        $identification->InfraspecificEpithet = $name['Forma'];
                        $identification->Author = ($name['Forma'] != $name['Species']) ? $name['FormaAuthor'] : NULL;
                        break;
                    default:
                        $identification->InfraspecificRank = NULL;
                        $identification->InfraspecificEpithet = NULL;
                        $identification->Author = isset($name['SpeciesAuthor']) ? $name['SpeciesAuthor'] : NULL;
                }*/
                $identification->PlantNameCode = NULL;
                $identification->Identifier = ($row->Identifier) ? $row->Identifier : 'Not on sheet';
                $identification->IdentificationDateStartDay = $row->IdentificationDateStartDay;
                $identification->IdentificationDateStartMonth = $row->IdentificationDateStartMonth;
                $identification->IdentificationDateStartYear = $row->IdentificationDateStartYear;
                $identification->IdentificationDateEndDay = NULL;
                $identification->IdentificationDateEndMonth = NULL;
                $identification->IdentificationDateEndYear = NULL;
                if (!$identification->IdentificationDateStartYear)
                    $identification->IdentificationDateOtherText = 'Not on sheet';
                if ($row->TypeStatusName) {
                    switch ($row->TypeStatusName) {
                        case 'Paralectotype':
                            $identification->TypeStatus = 'Syntype';
                            break;
                        case 'Paraneotype':
                            $identification->TypeStatus = 'Syntype';
                            break;
                        case 'Authentic specimen':
                            $identification->TypeStatus = 'Original material';
                            break;
                        default:
                            $identification->TypeStatus = $row->TypeStatusName;
                    }
                    if ($row->VarQualifier) {
                        $identification->TypeStatus = ($identification->TypeStatus == 'Original material') ? 'Original material ?' : 'Type ?';
                    }
                }
                else 
                    $identification->TypeStatus = '-';
                $identification->UnitID = $row->UnitID;
                $identification->StoredUnderName = $row->StoredUnderName;
                $identification->CurrentName = $row->CurrentName;

                $this->db->insert('gpi.identification', $identification);
            }
        }
    }
    
    /**
     * getFamily Function
     * 
     * Finds the family from a node's lineage, given the node number
     * @param integer $nodenumber
     * @return string|NULL
     */
    private function getFamily($nodenumber) {
        $this->db->select('Name');
        $this->db->from('taxon');
        $this->db->where('NodeNumber <', $nodenumber);
        $this->db->where('HighestChildNodeNumber >=', $nodenumber);
        $this->db->where('TaxonTreeDefItemID', 11);
        $query = $this->db->get();
        if ($query->num_rows()) {
            $row = $query->row();
            return $row->Name;
        }
        else
            return NULL;
    }

    /**
     * getSpeciesAuthor
     * 
     * Gets author of the species name from a node's lineage. Used for autonyms.
     * @param integer $nodenumber
     * @return string|NULL
     */
    private function getSpeciesAuthor($nodenumber) {
        $this->db->select('Author');
        $this->db->from('taxon');
        $this->db->where('NodeNumber <=', $nodenumber);
        $this->db->where('HighestChildNodeNumber >=', $nodenumber);
        $this->db->where('TaxonTreeDefItemID', 13);
        $query = $this->db->get();
        if ($query->num_rows()) {
            $row = $query->row();
            return $row->Author;
        }
        else
            return NULL;
    }

    /**
     * getTaxonNameArray Function
     * 
     * Returns the lineage of a taxon record as an array, given the TaxonID. Not sure if this function
     * is still used in the application
     * @param integer $taxonid
     * @return array|FALSE 
     */
    private function getTaxonNameArray($taxonid) {
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
    
    /**
     * showDataSets Function
     * 
     * Returns data set information. Used for index page of application
     * @return array|FALSE 
     */
    public function showDataSets() {
        $this->db->select('d.BatchNo, DATE(d.TimestampCreated) AS DateUploaded,
            count(*) AS NumRecords, count(co.YesNo5) AS NumMarked', FALSE);
        $this->db->from('gpi.dataset d');
        $this->db->join('gpi.unit u', 'd.DataSetID=u.DataSetID');
        $this->db->join('collectionobject co', 'u.SpCollectionObjectID=co.CollectionObjectID');
        $this->db->group_by('d.DataSetID');
        $query = $this->db->get();
        if ($query->num_rows()) {
            $datasets = array();
            foreach ($query->result() as $row) {
                $errors = $this->findErrors($row->BatchNo);
                $numerrors = count($errors['NotAType'])
                        + count($errors['TypeStatusEqualsCurrent'])
                        + count($errors['NotABasionym'])
                        + count($errors['NoAuthor'])
                        + count($errors['NoProtologue']);

                $datasets[] = array(
                    'BatchNo' => $row->BatchNo,
                    'DateUploaded' => $row->DateUploaded,
                    'NumRecords' => $row->NumRecords,
                    'NumErrors' => $numerrors,
                    'NumMarked' => $row->NumMarked
                );
            }
            return $datasets;
        }
        else
            return FALSE;
    }

    public function fixErrors($batchno) {
        $errors = $this->findErrors($batchno);
        $dets = array();
        foreach ($errors as $group) {
            foreach ($group as $error) {
                $dets[] = $error;
            }
        }
        $this->db->select('co.CollectionObjectID, co.CatalogNumber, d.DeterminationID');
        $this->db->from('determination d');
        $this->db->join('collectionobject co', 'd.CollectionObjectID=co.CollectionObjectID');
        $this->db->where_in('determinationID', $dets);
        $this->db->order_by('co.CatalogNumber');
        $query = $this->db->get();
        $units = array();
        $objects = array();
        if ($query->num_rows()) {
            foreach ($query->result() as $row) {
                $objects[] = $row->CollectionObjectID;
                $units[] = $row->CatalogNumber;
            }
        }
        if ($objects && $units) {
            $this->deleteIdentifications($objects);
            $this->insertIdentifications($units);
        }
        else
            return FALSE;
    }

    private function deleteIdentifications($objects) {
        $identifications = array();
        $this->db->select('i.IdentificationID');
        $this->db->from('gpi.identification i');
        $this->db->join('gpi.unit u', 'i.unitID=u.UnitID');
        $this->db->where_in('u.SpCollectionObjectID', $objects);
        $query = $this->db->get();
        if ($query->num_rows()) {
            foreach ($query->result() as $row)
                $identifications[] = $row->IdentificationID;
        }
        else
            return FALSE;


        if ($identifications) {
            $this->db->where_in('IdentificationID', $identifications);
            $this->db->delete('gpi.identification');
        }
    }

    public function deleteHybridDets($batchno) {
        $identifications = array();
        $this->db->select('i.IdentificationID');
        $this->db->from('gpi.dataset g');
        $this->db->join('gpi.unit u', 'g.DataSetID=u.DataSetID');
        $this->db->join('gpi.identification i', 'u.unitID=i.UnitID');
        $this->db->join('determination d', 'i.SpDeterminationID=d.DeterminationID');
        $this->db->join('taxon t', 'd.TaxonID=t.TaxonID');
        $this->db->where('g.BatchNo', $batchno);
        $this->db->where("t.UsfwsCode='H' OR t.USfwsCode='-'", FALSE, FALSE);
        $query = $this->db->get();

        if ($query->num_rows()) {
            foreach ($query->result() as $row)
                $identifications[] = $row->IdentificationID;
        }

        if ($identifications) {
            $this->db->where_in('IdentificationID', $identifications);
            $this->db->delete('gpi.identification');
        }
    }

    public function createErrorRecordSet($batchno, $recsetname, $spuser, $recsetitems) {
        $this->db->select('co.CollectionObjectID');
        $this->db->from('collectionobject co');
        $this->db->where_in('co.CatalogNumber', $recsetitems);
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
        }
    }

    
    public function findErrors($batchno) {
        $errors = array();
        $errors['NotAType'] = array();
        $errors['TypeStatusEqualsCurrent'] = array();
        $this->db->select('u.UnitID');
        $this->db->from('gpi.unit u');
        $this->db->join('gpi.identification i', 'u.UnitID=i.UnitID');
        $this->db->where('u.DataSetID', $batchno);
        $this->db->group_by('u.UnitID');
        $this->db->having('count(*)=1');
        $query = $this->db->get();
        if ($query->num_rows()) {
             $units = array();
             foreach ($query->result() as $row)
                 $units[] = $row->UnitID;
             $errors['NotAType'] = $this->notAType($units);
             $errors['TypeStatusEqualsCurrent'] = $this->typeStatusEqualsCurrent($units);
        }
        $errors['NotABasionym'] = $this->notABasionym($batchno);
        $errors['NoAuthor'] = $this->noAuthor($batchno);
        $errors['NoProtologue'] = $this->noProtologue($batchno);
        return $errors;
    }

    public function showErrors($batchno) {
        $ret = array();
        $errors = $this->findErrors($batchno);
        foreach ($errors as $key=>$array) {
            if ($array){
                $this->db->select('co.CatalogNumber, t.FullName, t.Author, t.CommonName AS Protologue, t.Number2 AS Year, d.TypeStatusName');
                $this->db->from('collectionobject co');
                $this->db->join('determination d', 'co.CollectionObjectID=d.CollectionObjectID');
                $this->db->join('taxon t', 'd.TaxonID=t.TaxonID');
                $this->db->where_in('d.DeterminationID', $array);
                $query = $this->db->get();
                if ($query->num_rows()) {
                    $group = array();
                    $melnos = array();
                    foreach ($query->result() as $row) {
                        $melnos[] = $row->CatalogNumber;
                        $group[] = array(
                            'MELNumber' => 'MEL ' . $row->CatalogNumber,
                            'TaxonName' => $row->FullName,
                            'Author' => xml_convert($row->Author),
                            'Protologue' => xml_convert($row->Protologue),
                            'Year' => $row->Year,
                            'TypeStatusName' => $row->TypeStatusName
                        );
                    }
                    array_multisort($melnos, $group);
                    $ret[$key] = $group;
                }
            }
        }
        return $ret;
    }
    
    private function notAType($units) {
        $ret = array();
        $this->db->select('u.SpCollectionObjectID, i.SpDeterminationID');
        $this->db->from('gpi.unit u');
        $this->db->join('gpi.identification i', 'u.UnitID=i.UnitID');
        $this->db->where_in('u.UnitID', $units);
        $this->db->where('i.StoredUnderName', 'FALSE');
        $query = $this->db->get();
        if ($query->num_rows()) {
            foreach($query->result() as $row)
                $ret[] = $row->SpDeterminationID;
       }
        return $ret;
    }

    private function typeStatusEqualsCurrent($units) {
        $ret = array();
        $this->db->select('u.SpCollectionObjectID, i.SpDeterminationID');
        $this->db->from('gpi.unit u');
        $this->db->join('gpi.identification i', 'u.UnitID=i.UnitID');
        $this->db->where_in('u.UnitID', $units);
        $this->db->where('i.StoredUnderName', 'TRUE');
        $this->db->where('i.CurrentName', 'TRUE');
        $query = $this->db->get();
        if ($query->num_rows()) {
            foreach($query->result() as $row)
                $ret[] = $row->SpDeterminationID;
        }
        return $ret;
    }

    private function notABasionym($batchno) {
        $ret = array();
        $this->db->select('u.SpCollectionObjectID, i.SpDeterminationID');
        $this->db->from('gpi.unit u');
        $this->db->join('gpi.identification i', 'u.UnitID=i.UnitID');
        $this->db->where('u.DataSetID', $batchno);
        $this->db->where('i.TypeStatus !=', '-');
        $this->db->like('i.Author', '(');
        $query = $this->db->get();
        if ($query->num_rows()) {
            foreach($query->result() as $row)
                $ret[] = $row->SpDeterminationID;
        }
        return $ret;
    }

    private function noAuthor($batchno) {
        $ret = array();
        $this->db->select('u.SpCollectionObjectID, i.SpDeterminationID');
        $this->db->from('gpi.unit u');
        $this->db->join('gpi.identification i', 'u.UnitID=i.UnitID');
        $this->db->where("u.DataSetID=$batchno AND (i.Author IS NULL OR i.Author='') AND i.TypeStatus!='-'", FALSE, FALSE);
        $query = $this->db->get();
        if ($query->num_rows()) {
            foreach($query->result() as $row)
                $ret[] = $row->SpDeterminationID;
        }
        return $ret;
    }

    private function noProtologue($batchno) {
        /*
        SELECT u.SpCollectionObjectID, i.SpDeterminationID, t.FullName, t.Author, t.CommonName, t.number2
        FROM (gpi.unit u)
        JOIN gpi.identification i ON u.UnitID=i.UnitID
        JOIN determination d ON i.SpDeterminationID=d.DeterminationID
        JOIN taxon t ON d.TaxonID=t.TaxonID
        WHERE d.YesNo1=1 AND (t.Author IS NULL OR t.CommonName IS NULL OR t.Number2 IS NULL);
        */
        $ret = array();
        $this->db->select('u.SpCollectionObjectID, i.SpDeterminationID');
        $this->db->from('gpi.unit u');
        $this->db->join('gpi.identification i', 'u.UnitID=i.UnitID');
        $this->db->join('determination d', 'i.spDeterminationID=d.DeterminationID');
        $this->db->join('taxon t', 'd.TaxonID=t.TaxonID');
        $this->db->where("u.DataSetID=$batchno AND (t.Author IS NULL OR t.CommonName IS NULL OR t.Number2 IS NULL) AND d.YesNo1=1", FALSE, FALSE);
        $query = $this->db->get();
        if ($query->num_rows()) {
            foreach($query->result() as $row)
                $ret[] = $row->SpDeterminationID;
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
    
    public function getUnitIds($batchno) {
        $this->db->select('MelNumber');
        $this->db->from('gpi.unit');
        $this->db->where('DataSetID', $batchno);
        $query = $this->db->get();
        if ($query->num_rows()) {
            $ret = array();
            foreach ($query->result() as $row) 
                $ret[] = '<value>' . $row->MelNumber . '</value>';
            return $ret;
        }
        else
            return FALSE;
    }
    
    public function deleteBatch($batch) {
        $this->db->trans_start();
        $this->db->query("DELETE FROM gpi.`identification`
            WHERE `UnitID` IN (SELECT `UnitID` FROM gpi.`unit` WHERE `DataSetID`=$batch)");
        $this->db->query("DELETE FROM gpi.`unit`
            WHERE `DataSetID`=$batch");
        $this->db->query("UPDATE collectionobject co
            JOIN gpi.unit u ON co.CollectionObjectID=u.SpCollectionObjectID
            SET co.YesNo5=NULL
            WHERE u.DataSetID=$batch");
        $this->db->query("DELETE FROM gpi.dataset WHERE DataSetID=$batch");
        $this->db->trans_complete();
    }
    
    public function updateMetadata($datesupplied) {
        $this->db->query("UPDATE gpi.metadata SET DateSupplied='$datesupplied'");
    }
    
    public function markInMelisr($batch) {
        $this->db->select('co.CollectionObjectID');
        $this->db->from('collectionobject co');
        $this->db->join('gpi.unit u', 'co.CollectionObjectID=u.SpCollectionObjectID');
        $this->db->where('u.DataSetID', $batch);
        $query = $this->db->get();
        foreach ($query->result() as $row) {
            $this->db->where('CollectionObjectID', $row->CollectionObjectID);
            $this->db->update('collectionobject', array('YesNo5'=>1));
        }
    }

}

class DataSet {
    var $DataSetID;
    var $InstitutionCode = 'MEL';
    var $InstitutionName = 'National Herbarium of Victoria';
    var $PersonName = 'Alison Vaughan';
    var $BatchNo;
    var $DateSupplied;
}

class Unit {
    var $DataSetID = NULL;
    var $SpCollectionObjectID = NULL;
    var $MelNumber = NULL;
    var $DateLastModified = NULL;
    var $Collectors = NULL;
    var $CollectorNumber = NULL;
    var $CollectionDateStartDay = NULL;
    var $CollectionDateStartMonth = NULL;
    var $CollectionDateStartYear = NULL;
    var $CollectionDateEndDay = NULL;
    var $CollectionDateEndMonth = NULL;
    var $CollectionDateEndYear = NULL;
    var $CollectionDateOtherText = NULL;
    var $CountryName = NULL;
    var $ISO2Letter = NULL;
    var $Locality = NULL;
    var $Altitude = NULL;
}

class Identification {
  var $SpDeterminationID = NULL;
  var $Family = NULL;
  var $GenusQualifier = NULL;
  var $Genus = NULL;
  var $SpeciesQualifier = NULL;
  var $Species = NULL;
  var $InfraspecificRank = NULL;
  var $InfraspecificEpithet = NULL;
  var $Author = NULL;
  var $PlantNameCode = NULL;
  var $Identifier = NULL;
  var $IdentificationDateStartDay = NULL;
  var $IdentificationDateStartMonth = NULL;
  var $IdentificationDateStartYear = NULL;
  var $IdentificationDateEndDay = NULL;
  var $IdentificationDateEndMonth = NULL;
  var $IdentificationDateEndYear = NULL;
  var $IdentificationDateOtherText = NULL;
  var $TypeStatus = NULL;
    /*
     * Holotype
     * Epitype
     * Isoepitype
     * Lectotype
     * Isolectotype
     * Neotype
     * Isoneotype
     * Paratype
     * Isoparatype
     * Syntype
     * Isosyntype
     * Isotype
     * Type
     * Original material
     * Original material ?
     * - 
     */
  var $UnitID = NULL;
  var $StoredUnderName = NULL;
  var $CurrentName = NULL;
};


?>
