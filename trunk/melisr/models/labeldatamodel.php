<?php
/**
 * @property CI_Loader $load
 * @property CI_Form_validation $form_validation
 * @property CI_Input $input
 * @property CI_Email $email
 * @property CI_DB_active_record $db
 * @property CI_DB_forge $dbforge
 */

class LabelDataModel extends Model {

    function  __construct() {
        parent::Model();

        // connect to database
        $this->load->database();
    }

    function getAnnotationSlipData($colobjects, $part=FALSE, $multiple=FALSE, $type=FALSE) {
        $this->db->select('co.CollectionObjectID, co.CatalogNumber, d.FeatureOrBasis AS DetType, d.DeterminerID, d.DeterminedDate, d.DeterminedDatePrecision, d.Remarks');
        $this->db->from('collectionobject co');
        $this->db->join('determination d', 'co.CollectionObjectID=d.CollectionObjectID');
        $this->db->where_in('co.CollectionObjectID', $colobjects);
        $this->db->where('d.IsCurrent', 1);
        //$this->db->where('d.Remarks IS NOT NULL', FALSE, FALSE);
        $this->db->where('d.DeterminerID IS NOT NULL', FALSE, FALSE);
        if (!$part)
            $this->db->where("substring(CatalogNumber, 8, 1)='A'", FALSE, FALSE);
        $query = $this->db->get();

        if ($query->num_rows()) {
            $labelarray = array();
            foreach ($query->result() as $row) {
                $labeldata = array();
                $labeldata['CatalogNumber'] = $row->CatalogNumber;
                $labeldata['Role'] = $row->DetType;
                if ($labeldata['Role']!='Conf.') $labeldata['Role'] = 'Det.'; 
                $labeldata['Determiner'] = ($row->DeterminerID) ? $this->getAgentName($row->DeterminerID) : NULL;
                if (substr($labeldata['Determiner'], 0, 6) == 'MEL --') $labeldata['Determiner'] = 'MEL';
                $labeldata['DeterminedDate'] = ($row->DeterminedDate) ? $this->getProperDate($row->DeterminedDate, $row->DeterminedDatePrecision) : FALSE;
                $labeldata['FormattedName'] = $this->getFormattedNameString($row->CollectionObjectID, 'i');
                //$labeldata['DeterminationNotes'] = $row->Remarks;
                $labeldata['DeterminationNotes'] = FALSE;
                if ($row->DetType == 'Acc. name change')
                    $labeldata['DeterminationNotes'] = 'Accepted name change only';
                $labelarray[] = $labeldata;
            }
            return $labelarray;
        }
        else
            return FALSE;

    }

    function getLabelData($colobjects, $part=FALSE, $multiple=FALSE, $type=FALSE) {
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
              l.Lat1Text AS Latitude,
              l.Long1Text AS Longitude,
              l.MinElevation,
              l.MaxElevation,
              l.Text1 AS AltitudeUnit,
              l.LocalityID,
              ce.CollectingEventID,
              ce.Remarks AS Habitat,
              cea.Text2 AS AssociatedTaxa,
              ce.CollectingTripID,
              cea.Text5 AS Host,
              cea.Text4 AS Substrate,
              cea.Text3 AS Provenance,
              cea.Text6 AS VerbatimCollectingDate,
              cea.Text13 AS Cultivated,
              co.Text1 AS Habit,
              co.Text2 AS HabitCtd,
              co.Remarks AS CollectingNotes,
              cea.Text11 AS Introduced,
              coa.Remarks AS EthnobotanyInfo,
              coa.Text3 AS ToxicityInfo,
              co.Number1');
        $this->db->from('collectionobject co');
        $this->db->join('collectionobjectattribute coa', 'co.CollectionObjectAttributeID=coa.CollectionObjectAttributeID', 'left');
        $this->db->join('collectingevent ce', 'co.CollectingEventID=ce.CollectingEventID', 'left');
        $this->db->join('collectingeventattribute cea', 'ce.CollectingEventAttributeID=cea.CollectingEventAttributeID', 'left');
        $this->db->join('locality l', 'ce.LocalityID=l.LocalityID', 'left');
        $this->db->join('determination d', 'co.CollectionObjectID=d.CollectionObjectID', 'left');
        $this->db->where_in('co.CollectionObjectID', $colobjects);
        $this->db->where('d.isCurrent', 1);
        if (!$part)
            $this->db->where("substring(co.CatalogNumber, 8)='A'");
        if ($type == 12) {
            $this->db->join('preparation p', 'co.CollectionObjectID=p.CollectionObjectID');
            $this->db->where('p.PrepTypeID', 2);
        }

        $query = $this->db->get();

        if($query->num_rows()>0) {
            $labelarray = array();
            foreach($query->result() as $row) {
                $labeldata = array();
                $labeldata['CatalogNumber'] = $row->CatalogNumber;
                $labeldata['MelNumber'] = (int) substr($row->CatalogNumber, 0, 7);
                $colobj = $row->CollectionObjectID;
                $labeldata['FormattedName'] = $this->getFormattedNameString($colobj, 'b');
                $labeldata['ExtraInfo'] = $row->ExtraInfo;
                $labeldata['Collector'] = $this->getFormattedCollectorString($row->CollectingEventID, 1);
                $labeldata['AdditionalCollectors'] = $this->getFormattedCollectorString($row->CollectingEventID, 0);
                $labeldata['CollectingNumber'] = $row->CollectingNumber;
                $labeldata['CollectingDate'] = FALSE;
                if ($row->CollectingDate) {
                    if ($row->CollectingEndDate) $labeldata['CollectingDate'] = $this->getDateRange($row->CollectingDate, $row->CollectingEndDate,
                            $row->CollectingDatePrecision, $row->CollectingEndDatePrecision);
                    else $labeldata['CollectingDate'] = $this->getProperDate ($row->CollectingDate, $row->CollectingDatePrecision);
                } elseif ($row->VerbatimCollectingDate) $labeldata['CollectingDate'] = $row->VerbatimCollectingDate;
                $labeldata['Locality'] = $this->xml_convert($row->LocalityName);
                $labeldata['Geography'] = $this->getGeographyString($row->GeographyID);
                $labeldata['Latitude'] = $row->Latitude;
                $labeldata['Longitude'] = $row->Longitude;
                $labeldata['Altitude'] = ($row->MinElevation) ? $this->altitude($row->MinElevation, $row->MaxElevation, $row->AltitudeUnit) : FALSE;
                $labeldata['Depth'] = ($row->LocalityID) ? $this->depth($row->LocalityID) : FALSE;

                $labeldata['Habitat'] = array();
                if($row->Habitat) $labeldata['Habitat'][] = 'Habitat: ' . $row->Habitat;
                if($row->Substrate) $labeldata['Habitat'][] = 'Substrate: ' . $row->Substrate;
                if($row->Host) $labeldata['Habitat'][] = 'Host: ' . $row->Host;
                if($row->AssociatedTaxa) $labeldata['Habitat'][] = 'Associated taxa: ' . trim($row->AssociatedTaxa);

                for ($i = 0; $i < count($labeldata['Habitat']); $i++) {
                    $labeldata['Habitat'][$i] = trim($labeldata['Habitat'][$i]);
                    //echo $labeldata['Habitat'][$i] . '|' . substr($labeldata['Habitat'][$i], strlen($labeldata['Habitat'][$i])-1, 1) . "|<br/>\n";
                    if (substr($labeldata['Habitat'][$i], strlen($labeldata['Habitat'][$i])-1, 1) != '.')
                        $labeldata['Habitat'][$i] .= '.';

                    //echo $labeldata['Habitat'][$i] . '|' . substr($labeldata['Habitat'][$i], strlen($labeldata['Habitat'][$i])-1, 1) . "|<br/>\n";

                }

                $labeldata['Habitat'] = implode(' ', $labeldata['Habitat']);
                $labeldata['Provenance'] = ($row->Provenance) ? 'Provenance: ' . $row->Provenance : FALSE;

                if ($row->Habit) {
                    $labeldata['DescriptiveNotes'] = 'Descriptive notes: ' . $this->xml_convert($row->Habit);
                    if($row->HabitCtd) $labeldata['DescriptiveNotes'] .= ' ' . $this->xml_convert($row->HabitCtd);
                }
                else $labeldata['DescriptiveNotes'] = FALSE;
                $labeldata['CollectingNotes'] = $this->xml_convert($row->CollectingNotes);
                $labeldata['Introduced'] = $row->Introduced;

                $cultivatedArray = array('Cultivated', 'Presumably cultivated', 'Possibly cultivated');

                $labeldata['Cultivated'] = (in_array($row->Cultivated, $cultivatedArray)) ? $row->Cultivated : FALSE;
                $labeldata['Ethnobotany'] = ($row->EthnobotanyInfo) ? ($row->EthnobotanyInfo) : FALSE;
                $labeldata['Toxicity'] = ($row->ToxicityInfo) ? ($row->ToxicityInfo) : FALSE;

                $notesfields = array('Provenance', 'DescriptiveNotes', 'CollectingNotes', 'Ethnobotany', 'Toxicity');
                foreach ($notesfields as $field) {
                    $labeldata[$field] = trim($labeldata[$field]);
                    if($labeldata[$field] && substr($labeldata[$field], strlen($labeldata[$field])-1) != '.')
                        $labeldata[$field] .= '.';
                }

                if ($row->CollectingTripID)
                    $labeldata['CollectingTrip'] = $this->getCollectingTripDetails($row->CollectingTripID);
                else $labeldata['CollectingTrip'] = FALSE;
                $labeldata['StoredUnder'] = $this->getStorage($row->CollectionObjectID);
                $labeldata['Continent'] = $this->getContinent($row->GeographyID);
                $labeldata['TypeInfo'] = $this->getTypeInfo($row->CollectionObjectID);
                $labeldata['MixedInfo'] = $this->getMixedInfo($row->CatalogNumber);
                $labeldata['Multisheet'] = $this->getMultisheetInfo($row->CollectionObjectID);
                $labeldata['DetType'] = $row->FeatureOrBasis;
                $labeldata['DeterminedBy'] = ($row->DeterminerID) ? $this->getAgentName($row->DeterminerID) : FALSE;
                $labeldata['DeterminedDate'] = ($row->DeterminedDate) ? $this->getProperDate($row->DeterminedDate, $row->DeterminedDatePrecision) : FALSE;

                if ($type == 12)
                    $labeldata['SpiritInfo'] = $this->getSpiritInfo ($row->CollectionObjectID);

                $numdups = $this->getNumberOfDuplicates($row->CollectionObjectID, $type);
                $labeldata['numdups'] = $numdups;
                $labeldata['DuplicateInfo'] = $this->getDuplicateInfo($row->CollectionObjectID);


                if ($type == 6 || $type == 7 || $type == 13 || $type == 14) {
                    if ($numdups > 0) {
                        for ($i = 0; $i < $numdups; $i++)
                            $labelarray[] = $labeldata;
                    }
                }
                else {
                    $labelarray[] = $labeldata;
                }
           }
            return $labelarray;
        } else return 'no records selected';
    }

    function getLabelDataNew($colobjects, $part=FALSE, $multiple=FALSE, $type=FALSE) {
        $this->db->select('co.CollectionObjectID,
              co.CatalogNumber,
              co.AltCatalogNumber,
              d.NameUsage AS ExtraInfo,
              d.FeatureOrBasis,
              d.DeterminerID,
              d.DeterminedDate,
              d.DeterminedDatePrecision,
              d.Remarks AS DeterminationNotes,
              ce.StationFieldNumber AS CollectingNumber,
              ce.StartDate AS CollectingDate,
              ce.StartDatePrecision AS CollectingDatePrecision,
              ce.EndDate AS CollectingEndDate,
              ce.EndDatePrecision AS CollectingEndDatePrecision,
              l.LocalityName,
              l.GeographyID,
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
              co.Text2 AS HabitCtd,
              co.Remarks AS MiscellaneousNotes,
              cea.Text11 AS Introduced,
              coa.Remarks AS EthnobotanyInfo,
              coa.Text3 AS ToxicityInfo,
              co.Number1');
        $this->db->from('collectionobject co');
        $this->db->join('collectionobjectattribute coa', 'co.CollectionObjectAttributeID=coa.CollectionObjectAttributeID', 'left');
        $this->db->join('collectingevent ce', 'co.CollectingEventID=ce.CollectingEventID', 'left');
        $this->db->join('collectingeventattribute cea', 'ce.CollectingEventAttributeID=cea.CollectingEventAttributeID', 'left');
        $this->db->join('locality l', 'ce.LocalityID=l.LocalityID', 'left');
        $this->db->join('determination d', 'co.CollectionObjectID=d.CollectionObjectID', 'left');
        $this->db->where_in('co.CollectionObjectID', $colobjects);
        $this->db->where('d.isCurrent', 1);
        if (!$part)
            $this->db->where("substring(co.CatalogNumber, 8)='A'");
        if ($type == 12) {
            $this->db->join('preparation p', 'co.CollectionObjectID=p.CollectionObjectID');
            $this->db->where('p.PrepTypeID', 2);
        }

        $query = $this->db->get();

        if($query->num_rows()>0) {
            $labelarray = array();
            foreach($query->result() as $row) {
                $labeldata = array();
                $labeldata['CatalogNumber'] = $row->CatalogNumber;
                $labeldata['MelNumber'] = (int) substr($row->CatalogNumber, 0, 7);
                $colobj = $row->CollectionObjectID;
                $labeldata['Family'] = $this->getFamily($colobj);
                $labeldata['FormattedName'] = $this->getFormattedNameString($colobj, 'b');
                $labeldata['ExtraInfo'] = $row->ExtraInfo;
                $labeldata['Collector'] = $this->getFormattedCollectorString($row->CollectingEventID, 1);
                $labeldata['AdditionalCollectors'] = $this->getFormattedCollectorString($row->CollectingEventID, 0);
                $labeldata['CollectingNumber'] = $row->CollectingNumber;
                $labeldata['CollectingDate'] = FALSE;
                if ($row->CollectingDate) {
                    if ($row->CollectingEndDate) $labeldata['CollectingDate'] = $this->getDateRange($row->CollectingDate, $row->CollectingEndDate,
                            $row->CollectingDatePrecision, $row->CollectingEndDatePrecision);
                    else $labeldata['CollectingDate'] = $this->getProperDate ($row->CollectingDate, $row->CollectingDatePrecision);
                } elseif ($row->VerbatimCollectingDate) $labeldata['CollectingDate'] = $row->VerbatimCollectingDate;
                $labeldata['Locality'] = $this->xml_convert($row->LocalityName);
                $labeldata['Geography'] = $this->getGeographyString($row->GeographyID);
                $labeldata['Latitude'] = $row->Latitude;
                $labeldata['Longitude'] = $row->Longitude;
                $labeldata['Altitude'] = ($row->MinElevation) ? $this->altitude($row->MinElevation, $row->MaxElevation, $row->AltitudeUnit) : FALSE;
                $labeldata['Depth'] = ($row->LocalityID) ? $this->depth($row->LocalityID) : FALSE;

                $labeldata['Habitat'] = $row->Habitat;
                $labeldata['Substrate'] = $row->Substrate;
                $labeldata['Host'] = $row->Host;
                $labeldata['AssociatedTaxa'] = $row->AssociatedTaxa;

                /*
                $labeldata['Habitat'] = array();
                if($row->Habitat) $labeldata['Habitat'][] = 'Habitat: ' . $row->Habitat;
                if($row->Substrate) $labeldata['Habitat'][] = 'Substrate: ' . $row->Substrate;
                if($row->Host) $labeldata['Habitat'][] = 'Host: ' . $row->Host;
                if($row->AssociatedTaxa) $labeldata['Habitat'][] = 'Associated taxa: ' . trim($row->AssociatedTaxa);

                for ($i = 0; $i < count($labeldata['Habitat']); $i++) {
                    $labeldata['Habitat'][$i] = trim($labeldata['Habitat'][$i]);
                    //echo $labeldata['Habitat'][$i] . '|' . substr($labeldata['Habitat'][$i], strlen($labeldata['Habitat'][$i])-1, 1) . "|<br/>\n";
                    if (substr($labeldata['Habitat'][$i], strlen($labeldata['Habitat'][$i])-1, 1) != '.')
                        $labeldata['Habitat'][$i] .= '.';
                    
                    //echo $labeldata['Habitat'][$i] . '|' . substr($labeldata['Habitat'][$i], strlen($labeldata['Habitat'][$i])-1, 1) . "|<br/>\n";
                    
                }

                $labeldata['Habitat'] = implode(' ', $labeldata['Habitat']);
                
                 */
                $labeldata['Provenance'] = $row->Provenance;

                if ($row->Habit) {
                    $labeldata['DescriptiveNotes'] = $this->xml_convert($row->Habit);
                    if($row->HabitCtd) $labeldata['DescriptiveNotes'] .= ' ' . $this->xml_convert($row->HabitCtd);
                }
                else $labeldata['DescriptiveNotes'] = FALSE;
                $labeldata['CollectingNotes'] = $this->xml_convert($row->CollectingNotes);
                $labeldata['Introduced'] = $row->Introduced;

                $cultivatedArray = array('Cultivated', 'Presumably cultivated', 'Possibly cultivated');

                $labeldata['Cultivated'] = (in_array($row->Cultivated, $cultivatedArray)) ? $row->Cultivated : FALSE;
                $labeldata['Ethnobotany'] = ($row->EthnobotanyInfo) ? ($row->EthnobotanyInfo) : FALSE;
                $labeldata['Toxicity'] = ($row->ToxicityInfo) ? ($row->ToxicityInfo) : FALSE;
                /*
                $notesfields = array('Provenance', 'DescriptiveNotes', 'CollectingNotes', 'Ethnobotany', 'Toxicity');
                foreach ($notesfields as $field) {
                    $labeldata[$field] = trim($labeldata[$field]);
                    if($labeldata[$field] && substr($labeldata[$field], strlen($labeldata[$field])-1) != '.')
                        $labeldata[$field] .= '.';
                }
                 * 
                 */
                
                if ($row->CollectingTripID)
                    $labeldata['CollectingTrip'] = $this->getCollectingTripDetails($row->CollectingTripID);
                else $labeldata['CollectingTrip'] = FALSE;
                $labeldata['StoredUnder'] = $this->getStorage($row->CollectionObjectID);
                $labeldata['Continent'] = $this->getContinent($row->GeographyID);
                $labeldata['TypeInfo'] = $this->getTypeInfo($row->CollectionObjectID);
                $labeldata['MixedInfo'] = $this->getMixedInfo($row->CatalogNumber);
                $labeldata['Multisheet'] = $this->getMultisheetInfo($row->CollectionObjectID);
                $labeldata['DetType'] = $row->FeatureOrBasis;
                $labeldata['DeterminedBy'] = ($row->DeterminerID) ? $this->getAgentName($row->DeterminerID) : FALSE;
                $labeldata['DeterminedDate'] = ($row->DeterminedDate) ? $this->getProperDate($row->DeterminedDate, $row->DeterminedDatePrecision) : FALSE;
                $labeldata['DeterminationNotes'] = $row->DeterminationNotes;

                if ($type == 12)
                    $labeldata['SpiritInfo'] = $this->getSpiritInfo ($row->CollectionObjectID);
                
                $numdups = $this->getNumberOfDuplicates($row->CollectionObjectID, $type);
                $labeldata['numdups'] = $numdups;
                $labeldata['DuplicateInfo'] = $this->getDuplicateInfo($row->CollectionObjectID);
                $vrsnumbers = $this->vrsNumber($row->CollectionObjectID);
                
                
                if ($type == 6 || $type == 7 || $type == 13 || $type == 14) {
                    if ($numdups > 0) {
                        for ($i = 0; $i < $numdups; $i++)
                            $labelarray[] = $labeldata;
                    }
                }
                elseif ($type == 19 || $type == 21) {
                    $vrsnumbers = $this->vrsNumber($row->CollectionObjectID);
                    if ($vrsnumbers) {
                        foreach($vrsnumbers as $number) {
                            $labeldata['VRSNumber'] = $number;
                            $labelarray[] = $labeldata;
                        }
                    }
                }
                else {
                    $labelarray[] = $labeldata;
                }
           }
           return $labelarray;
        } else return 'no records selected';
    }
    
    function vrsNumber($colobj) {
        $this->db->select('SampleNumber');
        $this->db->from('preparation');
        $this->db->where('CollectionObjectID', $colobj);
        $this->db->where('PrepTypeID', 18);
        $query = $this->db->get();
        if ($query->num_rows()) {
            $numbers = array();
            foreach ($query->result() as $row)
                $numbers[] = $row->SampleNumber;
            return $numbers;
        }
        else {
            return FALSE;
        }
    }

    function altitude($min, $max, $unit) {
        $alt = $min;
        if ($max) $alt .= '–' . $max;
        $alt .= ' ' . $unit;
        return $alt;
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
    
    function getSpiritInfo($colobj) {
        $this->db->select('SampleNumber, Status, CountAmt');
        $this->db->from('preparation');
        $this->db->where('PrepTypeID', 2);
        $this->db->where('CollectionObjectID', $colobj);
        $query = $this->db->get();
        if ($query->num_rows()) {
            $row = $query->row();
            $ret = array();
            $ret['Number'] = $row->SampleNumber;
            $ret['JarSize'] = $row->Status;
            $ret['Quantity'] = $row->CountAmt;
            return $ret;
        }
    }


    function getSpiritJarLabelData($colobjects, $preptype=2) {
        $colobjects = implode(', ', $colobjects);
        $select = "SELECT co.CollectionObjectID, substring(CatalogNumber, 1, 7) AS MelNumber,
                p.Status AS JarSize, p.SampleNumber, l.GeographyID, co.CollectingEventID, ce.StationFieldNumber
            FROM collectionobject co
            JOIN preparation p ON co.CollectionObjectID=p.CollectionObjectID
            JOIN collectingevent ce ON co.CollectingEventID=ce.CollectingEventID
            JOIN locality l ON ce.LocalityID=l.LocalityID
            WHERE co.CollectionObjectID IN ($colobjects) AND p.PrepTypeID=$preptype";
        $query = $this->db->query($select);
        if ($query->num_rows() > 0) {
            $labelarray = array();
            foreach($query->result() as $row) {
                $labeldata = array();
                $colobj = $row->CollectionObjectID;
                $labeldata['MelNumber'] = $row->MelNumber;
                $labeldata['FormattedName'] = $this->getFormattedNameString($colobj, 'i');
                $labeldata['JarSize'] = $row->JarSize;
                $labeldata['SpiritNumber'] = $row->SampleNumber;
                $labeldata['Collector'] = $this->getFormattedCollectorString($row->CollectingEventID, 1);
                $labeldata['CollectingNumber'] = $row->StationFieldNumber;
                $labeldata['State'] = $this->getAustralianStateOrOtherwiseCountry($row->GeographyID);
                $labelarray[] = $labeldata;
            }
            return $labelarray;
        }
    }

    function getBarcodeLabelData($colobjects) {
        $colobjects = implode(', ', $colobjects);
        $select = "SELECT co.CatalogNumber
            FROM collectionobject co
            WHERE co.CollectionObjectID IN ($colobjects)";
        $query = $this->db->query($select);
        if ($query->num_rows() > 0) {
            $labelarray = array();
            foreach($query->result() as $row) {
                $labeldata = array();
                $labeldata['Barcode'] = (int) substr($row->CatalogNumber, 0, 7);
                $labelarray[] = $labeldata;
            }
            return $labelarray;
        }
    }
    
    function getVrsBarcodeLabelData($collobjects) {
        $this->db->select('co.CatalogNumber, p.SampleNumber');
        $this->db->from('collectionobject co');
        $this->db->join('preparation p', 'co.CollectionObjectID=p.CollectionObjectID');
        $this->db->where('p.PrepTypeID', 18);
        $this->db->where_in('co.CollectionObjectID', $collobjects);
        $query = $this->db->get();
        if ($query->num_rows()) {
            $labelarray = array();
            foreach ($query->result() as $row) {
                $labeldata = array();
                $labeldata['Barcode'] = 'VRS ' . $row->SampleNumber;
                $labeldata['MELNumber'] = 'MEL ' . (int) substr($row->CatalogNumber, 0, 7);
                $labelarray[] = $labeldata;
            }
            return $labelarray;
        }
    }

    function getMultisheetLabelData($colobjects) {
        $colobjects = implode(', ', $colobjects);
        $select = "SELECT p.Remarks
            FROM preparation p
            JOIN collectionobject co ON p.CollectionObjectID=co.CollectionObjectID
            WHERE co.CollectionObjectID IN ($colobjects) AND p.PrepTypeID IN (1, 8, 10, 13) AND !isnull(p.Remarks)";
        $query = $this->db->query($select);
        if ($query->num_rows() > 0) {
            $labelarray = array();
            foreach($query->result() as $row) {
                $labeldata = array();
                $labeldata['Multisheet'] = $row->Remarks;
                $labelarray[] = $labeldata;
            }
            return $labelarray;
        }
    }

    function getAustralianStateOrOtherwiseCountry($geoid) {
        $select = "SELECT NodeNumber, HighestChildNodeNumber
            FROM geography WHERE Name='Australia'";
        $query = $this->db->query($select);
        $au = $query->row();

        $select = "SELECT NodeNumber, HighestChildNodeNumber
            FROM geography WHERE GeographyID=$geoid";
        $query = $this->db->query($select);
        $col = $query->row();

        if($au->NodeNumber < $col->NodeNumber && $col->NodeNumber <= $au->HighestChildNodeNumber) {
            $select = "SELECT g.Name
                FROM geography g
                JOIN geographytreedefitem d ON g.GeographyTreeDefItemID=d.GeographyTreeDefItemID
                WHERE NodeNumber<=$col->NodeNumber AND HighestChildNodeNumber>=$col->NodeNumber AND d.Name='State'";
            $query = $this->db->query($select);
            $row = $query->row();
            return $row->Name;
        } else {
            $select = "SELECT g.Name, d.Name AS Rank, d.RankID
                FROM geography g
                JOIN geographytreedefitem d ON g.GeographyTreeDefItemID=d.GeographyTreeDefItemID
                WHERE NodeNumber<=$col->NodeNumber AND HighestChildNodeNumber>=$col->NodeNumber AND d.RankID<300 AND d.RankID>0
                ORDER BY d.RankID DESC;";
            $query = $this->db->query($select);
            $row = $query->row();
            return $row->Name;
        }
    }

    /*
    function getLabelData($colobjects, $part=FALSE, $multiple=FALSE) {
        $colobjects = implode(', ', $colobjects);
        $addwhere = ($part) ? '' : " AND substring(co.CatalogNumber, 8)='A'";
        $select = "SELECT co.CollectionObjectID,
              co.CatalogNumber,
              co.AltCatalogNumber,
              d.Text1 AS ExtraInfo,
              d.Method,
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
              l.Lat1Text AS Latitude,
              l.Long1Text AS Longitude,
              ce.CollectingEventID,
              ce.Remarks AS Habitat,
              ce.VerbatimLocality AS AssociatedTaxa,
              ce.CollectingTripID,
              cea.Text5 AS Host,
              cea.Text4 AS Substrate,
              cea.Text3 AS Provenance,
              cea.Text6 AS VerbatimCollectingDate,
              co.Description AS DescriptiveNotes,
              co.Remarks AS CollectingNotes,
              coa.Text10 AS Introduced,
              coa.Text11 AS Cultivated,
              coa.Remarks AS EthnobotanyInfo,
              coa.Text3 AS ToxicityInfo,
              co.Number1
            FROM collectionobject co
            LEFT JOIN collectionobjectattribute coa ON co.CollectionObjectAttributeID=coa.CollectionObjectAttributeID
            JOIN collectingevent ce ON co.CollectingEventID=ce.CollectingEventID
            LEFT JOIN collectingeventattribute cea ON ce.CollectingEventAttributeID=cea.CollectingEventAttributeID
            JOIN locality l ON ce.LocalityID=l.LocalityID
            JOIN determination d ON co.CollectionObjectID=d.CollectionObjectID
            WHERE co.CollectionObjectID IN ($colobjects) AND d.IsCurrent=1$addwhere";
        
        $query = $this->db->query($select);
        
        if($query->num_rows()>0) {
            $labelarray = array();
            foreach($query->result() as $row) {
                $labeldata = array();
                $labeldata['CatalogNumber'] = $row->CatalogNumber;
                $labeldata['MelNumber'] = (int) substr($row->CatalogNumber, 0, 7);
                $colobj = $row->CollectionObjectID;
                $labeldata['FormattedName'] = $this->getFormattedNameString($colobj, 'b');
                $labeldata['ExtraInfo'] = $row->ExtraInfo;
                $labeldata['Collector'] = $this->getFormattedCollectorString($row->CollectingEventID, 1);
                $labeldata['AdditionalCollectors'] = $this->getFormattedCollectorString($row->CollectingEventID, 0);
                $labeldata['CollectingNumber'] = $row->CollectingNumber;
                $labeldata['CollectingDate'] = FALSE;
                if ($row->CollectingDate) {
                    if ($row->CollectingEndDate) $labeldata['CollectingDate'] = $this->getDateRange($row->CollectingDate, $row->CollectingEndDate,
                            $row->CollectingDatePrecision, $row->CollectingEndDatePrecision);
                    else $labeldata['CollectingDate'] = $this->getProperDate ($row->CollectingDate, $row->CollectingDatePrecision);
                } elseif ($row->VerbatimCollectingDate) $labeldata['CollectingDate'] = $row->VerbatimCollectingDate;
                $labeldata['Locality'] = $this->xml_convert($row->LocalityName);
                $labeldata['Geography'] = $this->getGeographyString($row->GeographyID);
                $labeldata['Latitude'] = $row->Latitude;
                $labeldata['Longitude'] = $row->Longitude;
                $labeldata['Habitat'] = array();
                if($row->Habitat) $labeldata['Habitat'][] = 'Habitat: ' . $row->Habitat;
                if($row->Substrate) $labeldata['Habitat'][] = 'Substrate: ' . $row->Substrate . '.';
                if($row->Host) $labeldata['Habitat'][] = 'Host: ' . $row->Host . '.';
                if($row->AssociatedTaxa) $labeldata['Habitat'][] = 'Associated taxa: ' . trim($row->AssociatedTaxa) . '.';
                $labeldata['Habitat'] = implode(' ', $labeldata['Habitat']);
                $labeldata['Provenance'] = ($row->Provenance) ? 'Provenance: ' . $row->Provenance : FALSE;
                $labeldata['DescriptiveNotes'] = $this->xml_convert($row->DescriptiveNotes);
                $labeldata['CollectingNotes'] = $this->xml_convert($row->CollectingNotes);
                $labeldata['Introduced'] = $row->Introduced;
                $labeldata['Cultivated'] = $row->Cultivated;
                $labeldata['Ethnobotany'] = ($row->EthnobotanyInfo) ? ($row->EthnobotanyInfo) : FALSE;
                $labeldata['Toxicity'] = ($row->ToxicityInfo) ? ($row->ToxicityInfo) : FALSE;
                if ($row->CollectingTripID)
                    $labeldata['CollectingTrip'] = $this->getCollectingTripDetails($row->CollectingTripID);
                else $labeldata['CollectingTrip'] = FALSE;
                $labeldata['StoredUnder'] = $this->getStorage($row->CollectionObjectID);
                $labeldata['Continent'] = $this->getContinent($row->GeographyID);
                $labeldata['TypeInfo'] = $this->getTypeInfo($row->CollectionObjectID);
                $labeldata['MixedInfo'] = $this->getMixedInfo($row->AltCatalogNumber);
                $labeldata['Multisheet'] = $this->getMultisheetInfo($row->CollectionObjectID);
                $labeldata['DetType'] = $row->Method;
                $labeldata['DeterminedBy'] = ($row->DeterminerID) ? $this->getAgentName($row->DeterminerID) : FALSE;
                $labeldata['DeterminedDate'] = ($row->DeterminedDate) ? $this->getProperDate($row->DeterminedDate, $row->DeterminedDatePrecision) : FALSE;
                $count = ($multiple && $row->Number1) ? $row->Number1 : 1;
                for ($i = 0; $i < $count; $i++) 
                    $labelarray[] = $labeldata;
            }
            return $labelarray;
        } else return 'no records selected';
    }
    */
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

    function getContinent($geographyid) {
        if ($geographyid) {
            $select = "SELECT NodeNumber FROM geography WHERE GeographyID=$geographyid";
            $query = $this->db->query($select);
            $row = $query->row();
            $node = $row->NodeNumber;
            $select = "SELECT Name FROM geography
                WHERE GeographyTreeDefItemID=2 AND NodeNumber<=$node AND HighestChildNodeNumber>=$node";
            $query = $this->db->query($select);
            $row = $query->row();
            return $row->Name;
        }
    }

    function getAgentName($agentid) {
        $select = "SELECT LastName, FirstName FROM agent WHERE AgentID=$agentid";
        $query = $this->db->query($select);
        if ($query->num_rows()) {
            $row = $query->row();
            $determiner = $row->LastName;
            if ($row->FirstName) $determiner .= ', ' . $row->FirstName;
            return $determiner;
        } else return FALSE;
    }

    function getProperDate($date, $precision) {
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

    function getDateRange($startdate, $enddate, $startdateprecision, $enddateprecision) {
        $startdate = $this->getProperDate($startdate, $startdateprecision);
        $startDateArray = explode(' ', $startdate);
        //print_r($startDateArray);
        $enddate = $this->getProperDate($enddate, $enddateprecision);
        $endDateArray = explode(' ', $enddate);
        //print_r($endDateArray);
        if ($startdateprecision != $enddateprecision) // start and end date precisions are unequal
            $daterange = "$startdate–$enddate"; // 4 June 1984–1985
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
            $qualifierrank = ($row->QualifierRank) ? $row->QualifierRank : $namearray['Rank'];
            $formattednamestring = '';
            if(isset($namearray['Species'])) {
                if($qualifier && $qualifierrank=='Genus')
                    $formattednamestring .= $qualifier;
                
                $formattednamestring .= "<$style>";
                if ($namearray['GenusHybrid'] == 'x')
                    $formattednamestring .= '×';
                $formattednamestring .= $namearray['Genus'] . "</$style>";
                if($qualifier && $qualifierrank=='Species')
                    $formattednamestring .= ' ' . $qualifier;
                $formattednamestring .= " <$style>";
                if ($namearray['SpeciesHybrid'] == 'x')
                    $formattednamestring .= '×';
                elseif ($namearray['SpeciesHybrid'] == 'H')
                    $namearray['Species'] = str_replace (' x ', ' × ', $namearray['Species']);
                $formattednamestring .= $namearray['Species'] . "</$style>";
                if(isset($namearray['Subspecies']) || isset($namearray['variety']) || isset($namearray['forma'])) {
                    if(isset($namearray['forma'])) {
                        if($namearray['forma']!=$namearray['Species']) {
                            if($qualifier && $qualifierrank=='forma')
                                $formattednamestring .= ' ' . $qualifier;
                            if ($namearray['formaHybrid'] == 'x')
                                $formattednamestring .= " nothof. <$style>" . $namearray['forma'] . "</$style>";
                            else {
                                if ($namearray['formaHybrid'] == 'H')
                                    $namearray['forma'] = str_replace (' x ', ' × ', $namearray['Forma']);
                                $formattednamestring .= " f. <$style>" . $namearray['forma'] . "</$style>";
                            }
                            $formattednamestring .= ' ' . $namearray['formaAuthor'];
                        } else {
                            $formattednamestring .= ' ' . $namearray['SpeciesAuthor'];
                            if($qualifier && $qualifierrank=='forma')
                                $formattednamestring .= ' ' . $qualifier;
                            if ($namearray['formaHybrid'] == 'x')
                                $formattednamestring .= " nothof. <$style>" . $namearray['forma'] . "</$style>";
                            else {
                                if ($namearray['formaHybrid'] == 'H')
                                    $namearray['forma'] = str_replace (' x ', ' × ', $namearray['Forma']);
                                $formattednamestring .= " f. <$style>" . $namearray['forma'] . "</$style>";
                            }
                        }
                    } elseif(isset($namearray['variety'])) {
                        if($namearray['variety']!=$namearray['Species']) {
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
                            $formattednamestring .= ' ' . $namearray['SpeciesAuthor'];
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
                    } elseif(isset($namearray['Subspecies'])) {
                        if($namearray['Subspecies']!=$namearray['Species']) {
                            if($qualifier && $qualifierrank=='subspecies')
                                $formattednamestring .= ' ' . $qualifier;
                            if ($namearray['SubspeciesHybrid'] == 'x')
                                $formattednamestring .= " nothosubsp. <$style>" . $namearray['Subspecies'] . "</$style>";
                            else {
                                if ($namearray['SubspeciesHybrid'] == 'H')
                                    $namearray['Subspecies'] = str_replace (' x ', ' × ', $namearray['Subspecies']);
                                $formattednamestring .= " subsp. <$style>" . $namearray['Subspecies'] . "</$style>";
                            }
                            $formattednamestring .= ' ' . $namearray['SubspeciesAuthor'];
                        } else {
                            $formattednamestring .= ' ' . $namearray['SpeciesAuthor'];
                            if($qualifier && $qualifierrank=='subspecies')
                                $formattednamestring .= $qualifier;
                            if ($namearray['SubspeciesHybrid'] == 'x')
                                $formattednamestring .= " nothosubsp. <$style>" . $namearray['Subspecies'] . "</$style>";
                            else {
                                if ($namearray['SubspeciesHybrid'] == 'H')
                                    $namearray['Subspecies'] = str_replace (' x ', ' × ', $namearray['Subspecies']);
                                $formattednamestring .= " subsp. <$style>" . $namearray['Subspecies'] . "</$style>";
                            }
                        }
                    }
                } else $formattednamestring .= ' ' . $namearray['SpeciesAuthor'];
            } 
            elseif (isset($namearray['Genus']) && (isset($namearray['Subgenus']) || isset($namearray['Section']))) {
                $formattednamestring = "<$style>";
                $formattednamestring .= $namearray['Genus'];
                $formattednamestring .= "</$style>";
                if (isset($namearray['Section'])) {
                    if ($qualifier && $qualifierrank == 'Section')
                        $formattednamestring .= ' ' . $qualifier;
                    $formattednamestring .= " sect. <$style>" . $namearray['Section'] . "</$style>";
                    if (isset($namearray['SectionAuthor']))
                        $formattednamestring .= ' ' . $namearray['SectionAuthor'];
                }
                elseif (isset($namearray['Subgenus'])) {
                    if ($qualifier && $qualifierrank == 'Subgenus')
                        $formattednamestring .= ' ' . $qualifier;
                    $formattednamestring .= " subgen. <$style>" . $namearray['Subgenus'] . "</$style>";
                    if (isset($namearray['SubgenusAuthor']))
                        $formattednamestring .= ' ' . $namearray['SubgenusAuthor'];
                }
            }
            elseif (isset($namearray['Family']) && (isset($namearray['Subfamily']) || isset($namearray['Tribe']))) {
                $formattednamestring = "<$style>";
                $formattednamestring .= $namearray['Family'];
                $formattednamestring .= "</$style>";
                if (isset($namearray['Tribe'])) {
                    if ($qualifier && $qualifierrank == 'Tribe')
                        $formattednamestring .= ' ' . $qualifier;
                    $formattednamestring .= " tr. <$style>" . $namearray['Tribe'] . "</$style>";
                    if (isset($namearray['TribeAuthor']))
                        $formattednamestring .= ' ' . $namearray['TribeAuthor'];
                }
                elseif (isset($namearray['Subfamily'])) {
                    if ($qualifier && $qualifierrank == 'Subfamily')
                        $formattednamestring .= ' ' . $qualifier;
                    $formattednamestring .= " subfam. <$style>" . $namearray['Subfamily'] . "</$style>";
                    if (isset($namearray['SubfamilyAuthor']))
                        $formattednamestring .= ' ' . $namearray['SubfamilyAuthor'];
                }
            }
            elseif (isset($namearray['Order']) && isset($namearray['Suborder'])) {
                $formattednamestring = "<$style>";
                $formattednamestring .= $namearray['Order'];
                $formattednamestring .= "</$style>";
                if ($qualifier && $qualifierrank == 'Suborder')
                    $formattednamestring .= ' ' . $qualifier;
                $formattednamestring .= " subord. <$style>" . $namearray['Suborder'];
                if (isset($namearray['SuborderAuthor']))
                    $formattednamestring .= ' ' . $namearray['SuborderAuthor'];
            }
            else {
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
        $collector = array();
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
        if (count($collector) > 0)
            return implode('; ', $collector);
        elseif ($isprimary  == 1) {
            $select = "SELECT ceo.Text1
                FROM collectingevent ce
                JOIN collectingeventattribute ceo ON ce.CollectingEventAttributeID=ceo.CollectingEventAttributeID
                WHERE ce.CollectingEventID=$collectingeventid";
            $query = $this->db->query($select);
            if ($query->num_rows() > 0) {
                $row = $query->row();
                return $row->Text1;
            } else return FALSE;
        }
    }
    
    function getGeographyString($geographyid) {
        if($geographyid) {
            $select = "SELECT g.`Name`, d.`Name` AS AreaType, d.`RankID`, g.`ParentID`
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
                $select = "SELECT g.`Name`, d.`Name` AS AreaType, d.`RankID`, g.`ParentID`
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

    function getStorage($colobj) {
        $select = "SELECT s.FullName AS StoredUnder
            FROM preparation p
            JOIN storage s ON p.StorageID=s.StorageID
            WHERE p.CollectionObjectID=$colobj
            LIMIT 1";
        $query = $this->db->query($select);
        if ($query->num_rows() > 0) {
            $row = $query->row();
            return $row->StoredUnder;
        } else {
            if ($storageid = $this->getStorageID($colobj)) {
                $update = "UPDATE preparation
                    SET StorageID=$storageid
                    WHERE CollectionObjectID=$colobj";
                $this->db->query($update);
                $this->getStorage($colobj);
            }
            else
                return FALSE;
        }
    }

    /*function getStorage($colobj) {
        if ($storageid = $this->getStorageID($colobj)) {
            $update = "UPDATE preparation
                SET StorageID=$storageid
                WHERE CollectionObjectID=$colobj";
            $this->db->query($update);
            $this->getStorage($colobj);
        }
        else
            return FALSE;
    }*/

    function getStorageID($colobj) {
        $select = "SELECT d.TaxonID, t.RankID, t.NodeNumber
            FROM determination d
            JOIN taxon t ON d.TaxonID=t.TaxonID
            WHERE d.CollectionObjectID=$colobj AND d.YesNo1=1";
        $query = $this->db->query($select);
        if ($query->num_rows() > 0) { // it is a type
            $row = $query->row();
            if ($row->RankID <= 180) { // it is a taxon of generic or higher rank
                $s1 = "SELECT StorageIDTypes
                    FROM `genusstorage`
                    WHERE TaxonID=$row[TaxonID]";
                $q1 = $this->db->query($s1);
                if ($q1->num_rows() > 0) {
                    $r1 = $q1->row();
                    if ($r1->StorageIDTypes)
                        return $r1->StorageIDTypes;
                    else
                        return FALSE;
                }
                else return FALSE;
            } else { // lower than generic rank
                $s1 = "SELECT TaxonID
                    FROM taxon
                    WHERE NodeNumber<$row->NodeNumber AND HighestChildNodeNumber>=$row->NodeNumber AND RankID=180";
                $q1 = $this->db->query($s1);
                $r1 = $q1->row();
                $s2 = "SELECT StorageIDTypes
                    FROM `genusstorage`
                    WHERE TaxonID=$r1->TaxonID";
                $q2 = $this->db->query($s2);
                if ($q2->num_rows()) {
                    $r2 = $q2->row();
                    if ($r2->StorageIDTypes)
                        return $r2->StorageIDTypes;
                    else
                        return FALSE;
                } 
                else
                    return FALSE;
            }
        } else {
            $select = "SELECT d.TaxonID, t.RankID, t.NodeNumber
                FROM determination d
                JOIN taxon t ON d.TaxonID=t.TaxonID
                WHERE d.CollectionObjectID=$colobj AND d.IsCurrent=1";
            $query = $this->db->query($select);
            if ($query->num_rows() > 0) {
                $row = $query->row();
                if ($row->RankID <= 180) { // it is a taxon of generic or higher rank
                    $s1 = "SELECT StorageID
                        FROM `genusstorage`
                        WHERE TaxonID=$row->TaxonID";
                    $q1 = $this->db->query($s1);
                    if ($q1->num_rows() > 0) {
                        $r1 = $q1->row();
                        if ($r1->StorageID)
                            return $r1->StorageID;
                        else
                            return false;
                    }
                } else { // lower than generic rank
                    $s1 = "SELECT TaxonID, FullName
                        FROM taxon
                        WHERE NodeNumber<$row->NodeNumber AND HighestChildNodeNumber>=$row->NodeNumber AND RankID=180";
                    $q1 = $this->db->query($s1);
                    $r1 = $q1->row();
                    //echo "$r1->TaxonID\t$r1->FullName\n";
                    $s2 = "SELECT StorageID
                        FROM `genusstorage`
                        WHERE TaxonID=$r1->TaxonID";
                    $q2 = $this->db->query($s2);
                    if ($q1->num_rows() && $q2->num_rows()) {
                        $r2 = $q2->row();
                        if ($r2->StorageID)
                            return $r2->StorageID;
                        else
                            return FALSE;
                    } 
                    else
                        return FALSE;
                }
            }
            return false;
        }
    }

    function getTypeInfo($colobj) {
        $select = "SELECT TaxonID, TypeStatusName, SubSpQualifier
            FROM determination
            WHERE CollectionObjectID=$colobj AND FeatureOrBasis='Type status' AND YesNo1=1";
        $query = $this->db->query($select);
            if($query->num_rows()) {
            $row = $query->row();
            $namearray = $this->getNameArray($row->TaxonID);

            $typeinfo = '';
            if ($row->SubSpQualifier) {
                if ($row->SubSpQualifier == '?') $typeinfo .= ucfirst($row->SubSpQualifier);
                else $typeinfo .= ucfirst($row->SubSpQualifier) . ' ';
            }
            $typestatus = $row->TypeStatusName;
            if ($typestatus == 'paralectotype') $typestatus = 'residual syntype';

            $typeinfo .= '<b>' . strtoupper($typestatus) . '</b> of ';
            
            if ($namearray['GenusHybrid'])
                $typeinfo .= '×';
            $sphybrid = ($namearray['SpeciesHybrid']) ? '×' : '';
            $typeinfo .= " <i>$namearray[Genus] {$sphybrid}$namearray[Species]</i>";
            if(isset($namearray['Subspecies']) || isset($namearray['variety']) || isset($namearray['forma'])) {
                if(isset($namearray['forma'])) {
                    $rank = ($namearray['formaHybrid']) ? 'nothof.' : 'f.';
                    $typeinfo .= " $rank <i>" . $namearray['forma'] . "</i>";
                    $typeinfo .= ' ' . $namearray['formaAuthor'];
                } elseif(isset($namearray['variety'])) {
                    $rank = ($namearray['varietyHybrid']) ? 'nothovar.' : 'var.';
                    $typeinfo .= " $rank <i>" . $namearray['variety'] . "</i>";
                    $typeinfo .= ' ' . $namearray['varietyAuthor'];
                } elseif(isset($namearray['Subspecies'])) {
                    $rank = ($namearray['SubspeciesHybrid']) ? 'nothosubsp.' : 'subsp.';
                    $typeinfo .= " $rank <i>" . $namearray['Subspecies'] . "</i>";
                    $typeinfo .= ' ' . $namearray['SubspeciesAuthor'];
                }
            } else $typeinfo .= ' ' . $namearray['SpeciesAuthor'];
            $typeinfo .= $this->getProtologue($row->TaxonID);
            return $typeinfo;
        } return null;
    }

    function getProtologue($taxonid) {
        $select = "SELECT CommonName, Number2, EsaStatus FROM taxon WHERE TaxonID=$taxonid AND CommonName IS NOT NULL";
        $query = $this->db->query($select);
        if ($query->num_rows()) {
            $row = $query->row();
            $protologue = ", $row->CommonName ($row->Number2)";
            if ($row->EsaStatus)
                $protologue .= ", $row->EsaStatus";
            $protologue .= '.';
            return $protologue;
        } else return FALSE;
    }

    function getMixedInfo($melnum) {
        $melnum = substr($melnum, 0, 7);
        $select = "SELECT CollectionObjectID, SUBSTRING(CatalogNumber, 8) AS Modifier, ObjectCondition AS MixedNote
            FROM collectionobject
            WHERE SUBSTRING(CatalogNumber, 1, 7)='$melnum'
            ORDER BY CatalogNumber";
        $query = $this->db->query($select);
        $mixed = array();
        if($query->num_rows()>1) {
            foreach($query->result() as $row) {
                $taxonname = $this->getFormattedNameString($row->CollectionObjectID);
                //$mixedinfo = 'MEL ' . $melnum;
                $mixedinfo = $row->Modifier . '. ' . $taxonname;
                if ($row->MixedNote) $mixedinfo .= ' – ' . $row->MixedNote;
                $mixed[] = $mixedinfo;
            }
            return implode('<br/>', $mixed);
        } else return null;
    }

    function getMultisheetInfo($collectionobjectid) {
        $select = "SELECT Remarks FROM preparation
            WHERE CollectionObjectID=$collectionobjectid
            AND PreptypeID IN (1, 2, 3, 4)";
        $query = $this->db->query($select);
        if ($query->num_rows() > 0) {
            $row = $query->row();
            return $row->Remarks;
        } else
            return FALSE;
    }

    function getTypeFolderLabelData($colobjects) {
        $colobjects = implode(', ', $colobjects);
        $select = "SELECT d.CollectionObjectID, d.SubSpQualifier AS DoubtfulFlag,
                d.TypeStatusName, d.TaxonID, d.YesNo1,
                co.CatalogNumber,
                s.Name AS Storage, p.Remarks AS Multisheet
            FROM determination d
            JOIN collectionobject co ON d.CollectionObjectID=co.CollectionObjectID
            JOIN preparation p ON co.CollectionObjectID=p.CollectionObjectID
            LEFT JOIN storage s ON p.StorageID=s.StorageID
            WHERE d.CollectionObjectID IN ($colobjects)
                AND p.PrepTypeID IN (1, 2, 3, 4, 8, 10)
                AND d.YesNo1=1";
        $query = $this->db->query($select);
        if ($query->num_rows() > 0) {
            $labelarray = array();
            foreach ($query->result() as $row) {
                if ($row->YesNo1) {
                    $labeldata = array();
                    $labeldata['MELNumber'] = 'MEL ' . ltrim(substr($row->CatalogNumber, 0, 7), '0');
                    if (strtoupper(substr($row->CatalogNumber, 7)) != 'A') $labeldata['MELNumber'] .= substr($row->CatalogNumber, 7);
                    $labeldata['Status'] = $row->TypeStatusName;
                    $labeldata['DoubtfulFlag'] = $row->DoubtfulFlag;
                    $basionym = $this->getFormattedNameString($row->CollectionObjectID, 'i', TRUE);
                    $basionym = str_replace('<i>', '<b><i>', $basionym);
                    $basionym = str_replace('</i>', '</i></b>', $basionym);
                    $labeldata['Basionym'] = $basionym;
                    $labeldata['Protologue'] = $this->getProtologue($row->TaxonID);
                    $labeldata['CurrentName'] = $this->getFormattedNameString($row->CollectionObjectID);
                    //$labeldata['Family'] = $row->Storage;
                    $labeldata['Family'] = str_replace('.', '', $row->Storage);
                    $labeldata['AuOrForeign'] = $this->inAustralia($row->CollectionObjectID);
                    $labeldata['Multisheet'] = $row->Multisheet;
                    $labelarray[] = $labeldata;
                }
            }
            return $labelarray;
        } else
            return FALSE;
    }

    function inAustralia($collectionobjectid) {
        $select = "SELECT g.NodeNumber
            FROM collectionobject co
            JOIN collectingevent ce ON co.CollectingEventID=ce.CollectingEventID
            JOIN locality l ON ce.LocalityID=l.LocalityID
            JOIN geography g ON l.GeographyID=g.GeographyID
            WHERE co.CollectionObjectID=$collectionobjectid";
        $query = $this->db->query($select);
        if ($query->num_rows()) {
            $row = $query->row();

            $sel = "SELECT NodeNumber, HighestChildNodeNumber
                FROM geography
                WHERE Name='Australia'";
            $q = $this->db->query($sel);
            $r = $q->row();
            return ($row->NodeNumber >= $r->NodeNumber && $row->NodeNumber <= $r->HighestChildNodeNumber) ? 'A' : 'F';
        } else
            return FALSE;
    }

    function xml_convert($string) {
        $string = str_replace(' & ', ' &amp; ', $string);
        $string = str_replace("\\'", '&apos;', $string);
        $string = str_replace('"', '&quot;', $string);
        $string = str_replace('<', '&lt;', $string);
        $string = str_replace('>', '&gt;', $string);
        return $string;
    }

    function getNumberOfDuplicates($colobj, $type) {
        if (!$type) return 0;
        $this->db->select('SUM(CountAmt) AS NumDups', FALSE);
        $this->db->from('preparation');
        if ($type == 6 || $type == 7)
            $this->db->where_in('PrepTypeID', array(15));
        elseif ($type == 13 || $type == 14)
            $this->db->where_in('PrepTypeID', array(16));
        $this->db->where('CollectionObjectID', $colobj);
        $query = $this->db->get();
        $row = $query->row();
        return $row->NumDups;
    }

    function getDuplicateInfo($colobj) {
        $this->db->select('p.PrepTypeID, p.Text1');
        $this->db->from('preparation p');
        $this->db->where('p.CollectionObjectID', $colobj);
        $this->db->where_in('p.PrepTypeID', array(15));
        //$this->db->where('p.Text1 IS NOT NULL', FALSE, FALSE);
        $query = $this->db->get();
        if ($query->num_rows() > 0) {
            $string = array();
            foreach ($query->result() as $row ) {
            if ($row->PrepTypeID == 15)
                $string[] = $row->Text1;
            }
            return implode(', ', $string);
        } else return FALSE;
    }


}

?>
