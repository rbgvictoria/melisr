<?php
/**
 * @property CI_Loader $load
 * @property CI_Form_validation $form_validation
 * @property CI_Input $input
 * @property CI_Email $email
 * @property CI_DB_active_record $db
 * @property CI_DB_forge $dbforge
 */

class Vcsb_model extends CI_Model {

    protected $vcsbCollectionId;
    
    function  __construct() {
        parent::__construct();
        $this->vcsbCollectionId = 294912;
    }
    
    public function getRecords() {
        $query = $this->db->query("SELECT p.PreparationID, co.CollectionObjectID, co.CatalogNumber, p.SampleNumber,
            IF(p.ModifiedByAgentID IS NOT NULL, p.ModifiedByAgentID, p.CreatedByAgentID) AS ModifiedByAgentID,
              IF(p.ModifiedByAgentID IS NOT NULL, mba.MiddleInitial, cba.MiddleInitial) AS MiddleInitial, p.TimestampModified,
              coa.Number1 AS Flowers, coa.Number2 AS Fruit, coa.Number3 AS Buds,
              coa.Number4 AS Leafless, coa.Number5 AS Fertile, coa.Number6 AS Sterile
            FROM collectionobject co
            LEFT JOIN collectionobjectattribute coa ON co.CollectionObjectAttributeID=coa.CollectionObjectAttributeID
            JOIN preparation p ON co.CollectionObjectID=p.CollectionObjectID
            LEFT JOIN agent mba ON p.ModifiedByAgentID=mba.AgentID
            LEFT JOIN agent cba ON p.CreatedByAgentID=cba.AgentID
            LEFT JOIN collectionobject vcsb ON p.SampleNumber=vcsb.CatalogNumber AND vcsb.CollectionID=$this->vcsbCollectionId
            WHERE co.CollectionID=4 AND p.PrepTypeID=14 AND vcsb.CollectionObjectID IS NULL
            ORDER BY SampleNumber");
        if ($query->num_rows()) {
            return $query->result_array();
        }
        else {
            return FALSE;
        }
    }
    
    public function createVcsbRecords($data) {
        $n = count($data['collectionobjectid']);
        $preptypeid = 153; // Seed sample
        $dupPreptypeId = 154; // Seed dup
        
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
                    'CollectionMemberID' => $this->vcsbCollectionId, 
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
                'CollectionMemberID' => $this->vcsbCollectionId,
                'CatalogNumber' => $data['vcsbnumber'][$i],
                'Remarks' => $row['Remarks'],
                'Text1' => $row['Text1'],
                'CollectingEventID' => $row['CollectingEventID'],
                'GUID' => $this->uuid(),
                'CollectionID' => $this->vcsbCollectionId,
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
                'CollectionMemberID' => $this->vcsbCollectionId,
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
                'CollectionMemberID' => $this->vcsbCollectionId,
                'CountAmt' => 1,
                'CreatedByAgentID' => $data['agentid'][$i],
                'CollectionObjectID' => $collectionobjectid,
                'PrepTypeID' => $preptypeid,
                'ModifiedByAgentID' => $data['agentid'][$i]
            );
            $this->db->insert('preparation', $insertArray);
            
            // Seed duplicate
            $updateArray = [
                'TimestampModified' => date('Y-m-d H:i:s'),
                'CollectionMemberID' => $this->vcsbCollectionId,
                'CollectionObjectID' => $collectionobjectid,
                'PrepTypeID' => $dupPreptypeId,
                'ModifiedByAgentID' => $data['agentid'][$i]
            ];
            $this->db->where('PrepTypeID', 16);
            $this->db->where('CollectionObjectID', $data['collectionobjectid'][$i]);
            $this->db->update('preparation', $updateArray);
            
            // Other Identifier
            $insertArray = array(
                'TimestampCreated' => date('Y-m-d H:i:s'),
                'TimestampModified' => date('Y-m-d H:i:s'),
                'Version' => 0,
                'CollectionMemberID' => $this->vcsbCollectionId,
                'Identifier' => 'MEL',
                'Institution' => $data['catalognumber'][$i],
                'Remarks' => 'MEL voucher',
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
    
    public function getNotInMelRecords() 
    {
        $query = $this->db->query("SELECT co.CollectionObjectID, co.CatalogNumber AS VRSNumber, oi.Institution AS MELNumber,
            CONCAT(a.LastName, ', ', a.FirstName) AS Perp, co.TimestampCreated
            FROM collectionobject co
            JOIN otheridentifier oi ON co.CollectionObjectID=oi.CollectionObjectID
            JOIN agent a ON co.CreatedByAgentID=a.AgentID
            WHERE co.CollectionMemberID=$this->vcsbCollectionId AND co.CatalogNumber NOT IN (
              SELECT p.SampleNumber
              FROM collectionobject co
              JOIN preparation p ON co.CollectionObjectID=p.CollectionObjectID
              WHERE co.CollectionMemberID=4 AND p.PrepTypeID=14
            )");
        if ($query->num_rows())
            return $query->result_array();
        else
            return FALSE;
    }
    
    public function deleteVcsbRecords($colobjs) 
    {
        foreach ($colobjs as $id) {
            $this->db->trans_start();
            
            $this->db->delete('otheridentifier', array('CollectionObjectID' => $id));
            $this->db->delete('preparation', array('CollectionObjectID' => $id));
            $this->db->delete('determination', array('CollectionObjectID' => $id));
            
            $this->db->select('CollectionObjectAttributeID');
            $this->db->from('collectionobject');
            $this->db->where('CollectionObjectID', $id);
            $query = $this->db->get();
            $row = $query->row();
            
            $this->db->delete('collectionobject', array('CollectionObjectID' => $id));
            $this->db->delete('collectionobjectattribute', array('CollectionObjectAttributeID' => $row->CollectionObjectAttributeID));
            $this->db->trans_complete();
        }
    }
    
    public function getDifferentDets() 
    {
        
        $this->db->select("vcsb.CatalogNumber AS vcsbCatalogNumber, vcsb_t.FullName AS vcsbFullName, IF(vcsb_a.FirstName IS NOT NULL, CONCAT(vcsb_a.LastName, ', ', vcsb_a.FirstName), vcsb_a.LastName) AS vcsbDeterminer,
            if(vcsb_d.DeterminedDatePrecision=3, SUBSTR(vcsb_d.DeterminedDate, 1, 4), IF(vcsb_d.DeterminedDatePrecision=2, SUBSTR(vcsb_d.DeterminedDate, 1, 7), vcsb_d.DeterminedDate)) AS vcsbDeterminationDate,
            mel.CatalogNumber AS melCatalogNumber, mel_t.FullName AS melFullName, IF(mel_a.FirstName IS NOT NULL, CONCAT(mel_a.LastName, ', ', mel_a.FirstName), mel_a.LastName) AS melDeterminer,
            if(mel_d.DeterminedDatePrecision=3, SUBSTR(mel_d.DeterminedDate, 1, 4), IF(mel_d.DeterminedDatePrecision=2, SUBSTR(mel_d.DeterminedDate, 1, 7), mel_d.DeterminedDate)) AS melDeterminationDate", FALSE);
        $this->db->from('collectionobject vcsb');
        $this->db->join('determination vcsb_d', 'vcsb.CollectionObjectID=vcsb_d.CollectionObjectID AND vcsb_d.IsCurrent=1');
        $this->db->join('agent vcsb_a', 'vcsb_d.DeterminerID=vcsb_a.AgentID', 'left');
        $this->db->join('taxon vcsb_t', 'vcsb_d.TaxonID=vcsb_t.TaxonID');
        $this->db->join('otheridentifier oi', 'vcsb.CollectionObjectID=oi.CollectionObjectID');
        $this->db->join('collectionobject mel', 'oi.Institution=mel.CatalogNumber AND mel.CollectionMemberID=4');
        $this->db->join('determination mel_d', 'mel.CollectionObjectID=mel_d.CollectionObjectID AND mel_d.IsCurrent=1');
        $this->db->join('agent mel_a', 'mel_d.DeterminerID=mel_a.AgentID', 'left');
        $this->db->join('taxon mel_t', 'mel_d.TaxonID=mel_t.TaxonID');
        $this->db->where('vcsb.CollectionMemberID', $this->vcsbCollectionId);
        $this->db->where('vcsb_d.TaxonID!=mel_d.TaxonID', FALSE, FALSE);
        
        $query = $this->db->get();
        
        return $query->result_array();
        
    }
    
    function getRecordSetFromRange($start, $count=false, $end=false) 
    {
        if (!$count && !$end) return FALSE;
        $items = [];
        if (!$end) $end = $start+$count-1;
        for ($i = $start; $i <= $end; $i++) {
            $select = "SELECT CollectionObjectID
                FROM collectionobject
                WHERE CAST(CatalogNumber AS unsigned)=$i
                    AND CollectionID=$this->vcsbCollectionId";
            $query = $this->db->query($select);
            if ($query->num_rows() > 0) {
                foreach ($query->result() as $row) {
                    $items[] = $row->CollectionObjectID;
                }
            }
        }
        return $items;
    }
    
    public function getVcsbLabelData($colobjects, $labelType='seed-sample', $collectionId=294912)
    {
        $labels = [];
        $this->db->select('co.CollectionObjectID,
              co.CatalogNumber,
              co.AltCatalogNumber,
              d.NameUsage AS ExtraInfo,
              d.FeatureOrBasis,
              d.DeterminerID,
              d.DeterminedDate,
              d.DeterminedDatePrecision,
              ce.StationFieldNumber AS CollectingNumber,
              ce.StartDate AS CollectingDate,
              ce.StartDatePrecision AS CollectingDatePrecision,
              ce.EndDate AS CollectingEndDate,
              ce.EndDatePrecision AS CollectingEndDatePrecision,
              l.LocalityName,
              l.GeographyID,
              l.Latitude1,
              l.Longitude1,
              l.Lat1Text AS Latitude,
              l.Long1Text AS Longitude,
              l.MinElevation,
              l.MaxElevation,
              l.Text1 AS AltitudeUnit,
              l.LocalityID,
              ce.CollectingEventID,
              ce.Remarks AS Habitat,
              ce.VerbatimLocality AS CollectingNotes,
              ce.CollectingTripID,
              cea.Text2 AS AssociatedTaxa,
              cea.Text5 AS Host,
              cea.Text4 AS Substrate,
              cea.Text3 AS Provenance,
              cea.Text6 AS VerbatimCollectingDate,
              cea.Text13 AS Cultivated,
              co.Text1 AS Habit,
              co.Remarks AS MiscellaneousNotes,
              cea.Text11 AS Introduced,
              coa.Remarks AS EthnobotanyInfo,
              coa.Text3 AS ToxicityInfo,
              coa.YesNo4=1 AS IsHortRefSet,
              co.Number1,
              pt.Name as PrepType,
              p.SampleNumber,
              p.PreparedDate, p.PreparedDatePrecision', FALSE);
        $this->db->from('collectionobject co');
        $this->db->join('collectionobjectattribute coa', 'co.CollectionObjectAttributeID=coa.CollectionObjectAttributeID', 'left');
        $this->db->join('collectingevent ce', 'co.CollectingEventID=ce.CollectingEventID', 'left');
        $this->db->join('collectingeventattribute cea', 'ce.CollectingEventAttributeID=cea.CollectingEventAttributeID', 'left');
        $this->db->join('locality l', 'ce.LocalityID=l.LocalityID', 'left');
        $this->db->join('determination d', 'co.CollectionObjectID=d.CollectionObjectID', 'left');
        $this->db->join('preparation p', 'co.CollectionObjectID=p.CollectionObjectID');
        $this->db->join('preptype pt', 'p.PrepTypeID=pt.PrepTypeID');
        $this->db->where('co.CollectionID', $collectionId);
        $this->db->where_in('co.CollectionObjectID', $colobjects);
        $this->db->where('d.isCurrent', 1);
        switch ($labelType) {
            case 'seed-sample':
                $this->db->where('p.PrepTypeID', 153);
                break;
            case 'seed-dup':
                $this->db->where('p.PrepTypeID', 154);
                break;
            case 'seedling':
                $this->db->where('p.PrepTypeID', 155);
                break;
            default:
                break;
        }
        
        $query = $this->db->get();
        if ($query->num_rows()) {
            foreach ($query->result() as $row) {
                $label = [];
                $label['CatalogNumber'] = $row->CatalogNumber;
                $colobj = $row->CollectionObjectID;
                $label['Family'] = $this->getFamily($colobj);
                $label['FormattedName'] = $this->getFormattedNameString($colobj, 'b');
                $label['Collector'] = $this->getFormattedCollectorString($row->CollectingEventID, 1);
                $label['AdditionalCollectors'] = $this->getFormattedCollectorString($row->CollectingEventID, 0);
                $label['CollectingNumber'] = $row->CollectingNumber;
                $label['CollectingDate'] = FALSE;
                if ($row->CollectingDate) {
                    if ($row->CollectingEndDate) {
                        $label['CollectingDate'] = $this->getDateRange($row->CollectingDate, $row->CollectingEndDate,
                            $row->CollectingDatePrecision, $row->CollectingEndDatePrecision);
                    }
                    else {
                        $label['CollectingDate'] = $this->getProperDate ($row->CollectingDate, $row->CollectingDatePrecision);
                    }
                } elseif ($row->VerbatimCollectingDate) {
                    $label['CollectingDate'] = $row->VerbatimCollectingDate;
                }
                if ($row->CollectingTripID) {
                    $label['CollectingTrip'] = $this->getCollectingTripDetails($row->CollectingTripID);
                }
                else {
                    $label['CollectingTrip'] = FALSE;
                            
                }
                $label['Locality'] = $this->xml_convert($row->LocalityName);
                $label['Geography'] = $this->getGeographyString($row->GeographyID);
                $label['Latitude'] = ($row->Latitude1) ? $row->Latitude : FALSE;
                $label['Longitude'] = ($row->Longitude1) ? $row->Longitude : FALSE;
                $label['Altitude'] = ($row->MinElevation) ? $this->altitude($row->MinElevation, $row->MaxElevation, $row->AltitudeUnit) : FALSE;
                $label['Depth'] = ($row->LocalityID) ? $this->depth($row->LocalityID) : FALSE;

                $label['Habitat'] = $row->Habitat;
                $label['Substrate'] = $row->Substrate;
                $label['Host'] = $row->Host;
                $label['AssociatedTaxa'] = $row->AssociatedTaxa;

                if ($row->Habit) {
                    $label['DescriptiveNotes'] = 'Descriptive notes: ' . $this->xml_convert($row->Habit);
                }
                else {
                    $label['DescriptiveNotes'] = FALSE;
                }
                $label['CollectingNotes'] = $this->xml_convert($row->CollectingNotes);
                $label['Introduced'] = $row->Introduced;

                $cultivatedArray = array('Cultivated', 'Presumably cultivated', 'Possibly cultivated');

                $label['Cultivated'] = (in_array($row->Cultivated, $cultivatedArray)) ? $row->Cultivated : FALSE;
                $label['Ethnobotany'] = ($row->EthnobotanyInfo) ? ($row->EthnobotanyInfo) : FALSE;
                $label['Toxicity'] = ($row->ToxicityInfo) ? ($row->ToxicityInfo) : FALSE;

                $label['Provenance'] = $row->Provenance;
                $notesfields = array('Provenance', 'DescriptiveNotes', 'CollectingNotes', 'Ethnobotany', 'Toxicity');
                foreach ($notesfields as $field) {
                    $label[$field] = trim($label[$field]);
                    if($label[$field] && substr($label[$field], strlen($label[$field])-1) != '.') {
                        $label[$field] .= '.';
                    }
                }
                $label['MiscellaneousNotes'] = $row->MiscellaneousNotes;
                $label['MelVoucher'] = $this->getMelVoucher($colobj);
                
                $label['PrepType'] = $row->PrepType;
                $label['SampleNumber'] = $row->SampleNumber;
                $label['PreparedDate'] = false;
                if ($row->PreparedDate) {
                    $label['PreparedDate'] = $this->getProperDate($row->PreparedDate, $row->PreparedDatePrecision);
                }
                
                $labels[] = $label;
            }
        }
        return $labels;
    }

    function xml_convert($string) {
        $string = str_replace(' & ', ' &amp; ', $string);
        $string = str_replace("\\'", '&apos;', $string);
        $string = str_replace('"', '&quot;', $string);
        $string = str_replace('<', '&lt;', $string);
        $string = str_replace('>', '&gt;', $string);
        return $string;
    }

    function getFamily($colobj) {
        $this->db->select('t.NodeNumber');
        $this->db->from('determination d');
        $this->db->join('taxon t', 'd.TaxonID=t.TaxonID');
        $this->db->where('d.CollectionObjectID', $colobj);
        $this->db->where('d.IsCurrent', 1);
        $query = $this->db->get();
        
        if ($query->num_rows()) {
            $row = $query->row();
            $this->db->select('FullName');
            $this->db->from('taxon');
            $this->db->where('NodeNumber <=', $row->NodeNumber);
            $this->db->where('HighestChildNodeNumber >=', $row->NodeNumber);
            $this->db->where('RankID', 140);
            $query = $this->db->get();
            if ($query->num_rows()) {
                $row = $query->row();
                return $row->FullName;
            }
            else
                return FALSE;
        }
        else
            return FALSE;
    }

    function getFormattedNameString($colobj, $style='i', $bas=FALSE) {
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
            $qualifierrank = ($row->QualifierRank) ? $row->QualifierRank : strtolower($namearray['Rank']);
            $formattednamestring = '';
            if(isset($namearray['species'])) {
                if($qualifier && $qualifierrank=='genus')
                    $formattednamestring .= $qualifier;
                
                $formattednamestring .= "<$style>";
                if ($namearray['genusHybrid'] == 'x')
                    $formattednamestring .= '×';
                $formattednamestring .= $namearray['genus'] . "</$style>";
                if($qualifier && $qualifierrank=='species')
                    $formattednamestring .= ' ' . $qualifier;
                $formattednamestring .= " <$style>";
                if ($namearray['speciesHybrid'] == 'x')
                    $formattednamestring .= '×';
                elseif ($namearray['speciesHybrid'] == 'H')
                    $namearray['species'] = str_replace (' x ', ' × ', $namearray['species']);
                $formattednamestring .= $namearray['species'] . "</$style>";
                if(isset($namearray['subspecies']) || isset($namearray['variety']) || isset($namearray['forma'])) {
                    if(isset($namearray['forma'])) {
                        if($namearray['forma']!=$namearray['species']) {
                            if($qualifier && $qualifierrank=='forma')
                                $formattednamestring .= ' ' . $qualifier;
                            if ($namearray['formaHybrid'] == 'x')
                                $formattednamestring .= " nothof. <$style>" . $namearray['forma'] . "</$style>";
                            else {
                                if ($namearray['formaHybrid'] == 'H')
                                    $namearray['forma'] = str_replace (' x ', ' × ', $namearray['forma']);
                                $formattednamestring .= " f. <$style>" . $namearray['forma'] . "</$style>";
                            }
                            $formattednamestring .= ' ' . $namearray['formaAuthor'];
                        } else {
                            $formattednamestring .= ' ' . $namearray['speciesAuthor'];
                            if($qualifier && $qualifierrank=='forma')
                                $formattednamestring .= ' ' . $qualifier;
                            if ($namearray['formaHybrid'] == 'x')
                                $formattednamestring .= " nothof. <$style>" . $namearray['forma'] . "</$style>";
                            else {
                                if ($namearray['formaHybrid'] == 'H')
                                    $namearray['forma'] = str_replace (' x ', ' × ', $namearray['forma']);
                                $formattednamestring .= " f. <$style>" . $namearray['forma'] . "</$style>";
                            }
                        }
                    } elseif(isset($namearray['variety'])) {
                        if($namearray['variety']!=$namearray['species']) {
                            if($qualifier && $qualifierrank=='variety')
                                $formattednamestring .= ' ' . $qualifier;
                            if ($namearray['varietyHybrid'] == 'x')
                                $formattednamestring .= " nothovar. <$style>" . $namearray['variety'] . "</$style>";
                            else {
                                if ($namearray['varietyHybrid'] == 'H')
                                    $namearray['variety'] = str_replace (' x ', ' × ', $namearray['variety']);
                                $formattednamestring .= " var. <$style>" . $namearray['variety'] . "</$style>";
                            }
                            $formattednamestring .= ' ' . $namearray['varietyAuthor'];
                        } else {
                            $formattednamestring .= ' ' . $namearray['speciesAuthor'];
                            if($qualifier && $qualifierrank=='variety')
                                $formattednamestring .= ' ' . $qualifier;
                            if ($namearray['varietyHybrid'] == 'x')
                                $formattednamestring .= " nothovar. <$style>" . $namearray['variety'] . "</$style>";
                            else {
                                if ($namearray['varietyHybrid'] == 'H')
                                    $namearray['variety'] = str_replace (' x ', ' × ', $namearray['variety']);
                                $formattednamestring .= " var. <$style>" . $namearray['variety'] . "</$style>";
                            }
                        }
                    } elseif(isset($namearray['subspecies'])) {
                        if($namearray['subspecies']!=$namearray['species']) {
                            if($qualifier && $qualifierrank=='subspecies')
                                $formattednamestring .= ' ' . $qualifier;
                            if ($namearray['subspeciesHybrid'] == 'x')
                                $formattednamestring .= " nothosubsp. <$style>" . $namearray['subspecies'] . "</$style>";
                            else {
                                if ($namearray['subspeciesHybrid'] == 'H')
                                    $namearray['subspecies'] = str_replace (' x ', ' × ', $namearray['subspecies']);
                                $formattednamestring .= " subsp. <$style>" . $namearray['subspecies'] . "</$style>";
                            }
                            $formattednamestring .= ' ' . $namearray['subspeciesAuthor'];
                        } else {
                            $formattednamestring .= ' ' . $namearray['speciesAuthor'];
                            if($qualifier && $qualifierrank=='subspecies')
                                $formattednamestring .= $qualifier;
                            if ($namearray['subspeciesHybrid'] == 'x')
                                $formattednamestring .= " nothosubsp. <$style>" . $namearray['subspecies'] . "</$style>";
                            else {
                                if ($namearray['subspeciesHybrid'] == 'H')
                                    $namearray['subspecies'] = str_replace (' x ', ' × ', $namearray['subspecies']);
                                $formattednamestring .= " subsp. <$style>" . $namearray['subspecies'] . "</$style>";
                            }
                        }
                    }
                } else $formattednamestring .= ' ' . $namearray['speciesAuthor'];
            } 
            elseif (isset($namearray['genus']) && (isset($namearray['subgenus']) || isset($namearray['section']))) {
                $formattednamestring = "<$style>";
                $formattednamestring .= $namearray['genus'];
                $formattednamestring .= "</$style>";
                if (isset($namearray['section'])) {
                    if ($qualifier && $qualifierrank == 'section')
                        $formattednamestring .= ' ' . $qualifier;
                    $formattednamestring .= " sect. <$style>" . $namearray['section'] . "</$style>";
                    if (isset($namearray['sectionAuthor']))
                        $formattednamestring .= ' ' . $namearray['sectionAuthor'];
                }
                elseif (isset($namearray['subgenus'])) {
                    if ($qualifier && $qualifierrank == 'subgenus')
                        $formattednamestring .= ' ' . $qualifier;
                    $formattednamestring .= " subgen. <$style>" . $namearray['subgenus'] . "</$style>";
                    if (isset($namearray['subgenusAuthor']))
                        $formattednamestring .= ' ' . $namearray['subgenusAuthor'];
                }
            }
            elseif (isset($namearray['family']) && (isset($namearray['subfamily']) || isset($namearray['tribe']))) {
                $formattednamestring = "<$style>";
                $formattednamestring .= $namearray['family'];
                $formattednamestring .= "</$style>";
                if (isset($namearray['tribe'])) {
                    if ($qualifier && $qualifierrank == 'tribe')
                        $formattednamestring .= ' ' . $qualifier;
                    $formattednamestring .= " tr. <$style>" . $namearray['tribe'] . "</$style>";
                    if (isset($namearray['tribeAuthor']))
                        $formattednamestring .= ' ' . $namearray['tribeAuthor'];
                }
                elseif (isset($namearray['subfamily'])) {
                    if ($qualifier && $qualifierrank == 'subfamily')
                        $formattednamestring .= ' ' . $qualifier;
                    $formattednamestring .= " subfam. <$style>" . $namearray['subfamily'] . "</$style>";
                    if (isset($namearray['subfamilyAuthor']))
                        $formattednamestring .= ' ' . $namearray['subfamilyAuthor'];
                }
            }
            elseif (isset($namearray['order']) && isset($namearray['suborder'])) {
                $formattednamestring = "<$style>";
                $formattednamestring .= $namearray['order'];
                $formattednamestring .= "</$style>";
                if ($qualifier && $qualifierrank == 'suborder')
                    $formattednamestring .= ' ' . $qualifier;
                $formattednamestring .= " subord. <$style>" . $namearray['suborder'];
                if (isset($namearray['suborderAuthor']))
                    $formattednamestring .= ' ' . $namearray['suborderAuthor'];
            }
            else {
                $rankarray = array('genus', 'tribe', 'subfamily', 'family', 'suborder', 'order', 'superorder',
                    'subclass', 'class', 'subdivision', 'division', 'subkingdom', 'kingdom');
                foreach($rankarray as $rank) {
                    if(isset($namearray[$rank])) {
                        if($qualifier && $qualifierrank == strtolower($rank))
                            $formattednamestring .= ' ' . $qualifier;
                            if (isset($namearray['genus']) && $namearray['genusHybrid'] == 'x')
                                $namearray['genus'] = '×' . $namearray['genus'];
                            elseif (isset($namearray['genus']) && $namearray['genusHybrid'] == 'H')
                                $namearray['genus'] = str_replace (' x ', ' × ', $namearray['genus']);
                        $formattednamestring .= "<$style>" . $namearray[$rank] . "</$style>";
                        $formattednamestring .= ($namearray[$rank.'Author']) ? ' ' . $namearray[$rank.'Author'] : '';
                        break;
                    }
                }
            }
            $formattednamestring = str_replace("</$style> <$style>", ' ', $formattednamestring);
            if (isset($row->Addendum) && $row->Addendum) $formattednamestring .= ' ' . $row->Addendum;
            return $formattednamestring;
        } else return FALSE;
    }
    
    function getNameArray($taxonid) {
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
            if ($rankid == 160 || $rankid == 150) {
                $select = "SELECT NodeNumber
                    FROM taxon WHERE TaxonID=$taxonid";
                $query = $this->db->query($select);
                $row = $query->row();
                $nodenumber = $row->NodeNumber;
                
                $select = "SELECT Name
                    FROM taxon
                    WHERE NodeNumber<$nodenumber AND HighestChildNodeNumber>=$nodenumber
                    AND RankID=140";
                $query = $this->db->query($select);
                $row = $query->row();
                $namearray['Family'] = $row->Name;
            }
            if ($rankid == 110) {
                $select = "SELECT NodeNumber
                    FROM taxon WHERE TaxonID=$taxonid";
                $query = $this->db->query($select);
                $row = $query->row();
                $nodenumber = $row->NodeNumber;
                
                $select = "SELECT Name
                    FROM taxon
                    WHERE NodeNumber<$nodenumber AND HighestChildNodeNumber>=$nodenumber
                    AND RankID=100";
                $query = $this->db->query($select);
                $row = $query->row();
                $namearray['Order'] = $row->Name;
            }
            return $namearray;
        } else return false;
    }

    function getFormattedCollectorString($collectingeventid, $isprimary=1) {
        $collector = [];
        $select = "SELECT a.LastName, a.FirstName
            FROM collector c
            JOIN agent a ON c.AgentID=a.AgentID
            WHERE c.CollectingEventID=$collectingeventid AND IsPrimary=$isprimary
            ORDER BY c.OrderNumber";
        $query = $this->db->query($select);
        foreach($query->result() as $row) {
            $coll = $row->LastName;
            $coll .= ($row->FirstName) ? ', ' . $row->FirstName : '';
            $collector[] = $coll;
        }
        if (count($collector) > 0) {
            return implode('; ', $collector);
        }
        elseif ($isprimary  == 1) {
            $select = "SELECT ceo.Text1
                FROM collectingevent ce
                JOIN collectingeventattribute ceo ON ce.CollectingEventAttributeID=ceo.CollectingEventAttributeID
                WHERE ce.CollectingEventID=$collectingeventid";
            $query = $this->db->query($select);
            if ($query->num_rows() > 0) {
                $row = $query->row();
                return $row->Text1 ?: '[Unknown]';
            } 
            else {
                return '[Unknown]';
            }
        }
    }
    
    function getGeographyString($geographyid) {
        if($geographyid) {
            $select = "SELECT coalesce(g.CommonName, g.`Name`) AS `Name`, d.`Name` AS AreaType, d.`RankID`, g.`ParentID`
                FROM geography g
                JOIN geographytreedefitem d ON g.`GeographyTreeDefItemID`=d.`GeographyTreeDefItemID`
                WHERE g.GeographyID=$geographyid";
            $query = $this->db->query($select);
            $row = $query->row();
            $geographyarray = array();
            $geographyarray[$row->AreaType] = $row->Name;
            $rankid = $row->RankID;
            $parentid = $row->ParentID;
            while($rankid>100) {
                $select = "SELECT coalesce(g.CommonName, g.`Name`) as `Name`, d.`Name` AS AreaType, d.`RankID`, g.`ParentID`
                    FROM geography g
                    JOIN geographytreedefitem d ON g.`GeographyTreeDefItemID`=d.`GeographyTreeDefItemID`
                    WHERE g.GeographyID=$parentid";
                $query = $this->db->query($select);
                $row = $query->row();
                $geographyarray[$row->AreaType] = $row->Name;
                $rankid = $row->RankID;
                $parentid = $row->ParentID;
            }
            $geographystring = '';
            if(!isset($geographyarray['Country']))
                $geographystring .= $geographyarray['Continent'];
            else {
                $geographystring .= $geographyarray['Country'];
                $geographystring .= (isset($geographyarray['State'])) ? ': ' . $geographyarray['State'] : '';
                if(isset($geographyarray['County']))
                   $geographystring .= ': ' . $geographyarray['County'];
            }
            return $geographystring;
        } else return false;
    }
    
    function getProperDate($date, $precision) {
        $properDate = FALSE;
        $dateArray = explode('-', $date);
        $year = $dateArray[0];
        switch ((int) $dateArray[1]) {
            case 1: $month = 'Jan.';
                break;
            case 2: $month = 'Feb.';
                break;
            case 3: $month = 'Mar.';
                break;
            case 4: $month = 'Apr.';
                break;
            case 5: $month = 'May';
                break;
            case 6: $month = 'June';
                break;
            case 7: $month = 'July';
                break;
            case 8: $month = 'Aug.';
                break;
            case 9: $month = 'Sept.';
                break;
            case 10: $month = 'Oct.';
                break;
            case 11: $month = 'Nov.';
                break;
            case 12: $month = 'Dec.';
                break;
        }
        $day = (int) $dateArray[2];
        if ($precision == 1) $properDate = "$day $month $year";
        elseif ($precision == 2) $properDate = "$month $year";
        elseif ($precision == 3) $properDate = $year;
        return $properDate;
    }

    function depth($localityid) {
        $this->db->select("StartDepth, EndDepth");
        $this->db->from("localitydetail");
        $this->db->where("LocalityID", $localityid);
        $query = $this->db->get();
        if ($query->num_rows()) {
            $row = $query->row();
            if ($row->StartDepth) {
                $depth = $row->StartDepth;
                if ($row->EndDepth) $depth .= '–' . $row->EndDepth;
                $depth .= ' m';
                return $depth;
            }
            else
                return FALSE;
        }
        else
            return FALSE;
    }
    
    function altitude($min, $max, $unit) {
        $alt = $min;
        if ($max) $alt .= '–' . $max;
        $alt .= ' ' . $unit;
        return $alt;
    }
    
    function getMelVoucher($collectionObjectId)
    {
        $this->db->select("concat(Identifier, ' ', Institution) AS mel_voucher", false);
        $this->db->from('otheridentifier');
        $this->db->where('Remarks', 'MEL Voucher');
        $this->db->where('CollectionObjectID', $collectionObjectId);
        $query = $this->db->get();
        if ($query->num_rows()) {
            $row = $query->row();
            return $row->mel_voucher;
        }
        return false;
    }
    
    function getCollectingTripDetails($collectingtripid) {
        $select = "SELECT CollectingTripName, StartDate, EndDate, StartDateVerbatim, EndDateVerbatim
            FROM collectingtrip
            WHERE CollectingTripID=$collectingtripid";
        $query = $this->db->query($select);
        $row = $query->row();
        $detail = $row->CollectingTripName;
        if ($row->StartDate || $row->StartDateVerbatim) {
            $detail .= ' (';
            if ($row->StartDate) {
                if ($row->EndDate) $detail .= $this->getDateRange ($row->StartDate, $row->EndDate, 1, 1);
                else $detail .= $this->getProperDate($row->StartDate, 1);
            } else {
                $detail .= $row->StartDateVerbatim;
                if ($row->EndDateVerbatim) $detail .= '&ndash;' . $row->EndDateVerbatim;
            }
            $detail .= ')';
        }
        $detail .= '.';
        return $detail;
    }

    function getDateRange($startdate, $enddate, $startdateprecision, $enddateprecision) {
        $startdate = $this->getProperDate($startdate, $startdateprecision);
        $startDateArray = explode(' ', $startdate);
        //print_r($startDateArray);
        $enddate = $this->getProperDate($enddate, $enddateprecision);
        $endDateArray = explode(' ', $enddate);
        //print_r($endDateArray);
        if ($startdateprecision != $enddateprecision) // start and end date precisions are unequal
            $daterange = "{$startdate}–{$enddate}"; // 4 June 1984–1985
        elseif ($startdateprecision == 1) { // full dates
            if ($startDateArray[2] == $endDateArray[2]) { // same year
                if ($startDateArray[1] == $endDateArray[1]) // same month
                    $daterange = $startDateArray[0] . '–' . $enddate; // 4–5 June 1984
                else // different months
                    $daterange = $startDateArray[0] . ' ' . $startDateArray[1] . '–' . $enddate; // 4 June–5 July 1984
            } else // different years
                $daterange = $startdate . '–' . $enddate; // 4 June 1984–5 July 1985
        }
        elseif ($startdateprecision == 2) { // month/year
            if ($startDateArray[1] == $endDateArray[1]) // same year
                $daterange = $startDateArray[0] . '–' . $enddate; // June–July 1984
            else // different years
                $daterange = $startdate . '-' . $enddate; // June 1984–June 1985
        }
        elseif ($startdateprecision == 3) // year
            $daterange = $startdate . '–' . $enddate;
        return $daterange;
    }
}

