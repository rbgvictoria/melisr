<?php
/**
 * @property CI_Loader $load
 * @property CI_Form_validation $form_validation
 * @property CI_Input $input
 * @property CI_Email $email
 * @property CI_DB_active_record $db
 * @property CI_DB_forge $dbforge
 */

class LapiModel extends Model {

    function  __construct() {
        parent::Model();

        // connect to database
        $this->load->database();
        $this->load->helper('xml');
    }

    function getMetadata($units) {
        $source = <<<XML
<?xml version="1.0" encoding="UTF-8"?>
<DataSet xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xsi:noNamespaceSchemaLocation="http://aluka.ithaka.org/api/docs/AfricanTypesv2.xsd">
  <InstitutionCode>MEL</InstitutionCode>
  <InstitutionName>National Herbarium of Victoria</InstitutionName>
</DataSet>
XML;
        $dom = new DOMDocument('1.0', 'UTF-8');
        $dom->preserveWhiteSpace = true;

        $dataset = $dom->createElement('DataSet');
        $dom->appendChild($dataset);

        $dataset->setAttribute('xmlns:xsi', 'http://www.w3.org/2001/XMLSchema-instance');
        $dataset->setAttribute('xsi:noNamespaceSchemaLocation', 'http://aluka.ithaka.org/api/docs/AfricanTypesv2.xsd');

        $institutioncode = $dom->createElement('InstitutionCode', 'MEL');
        $dataset->appendChild($institutioncode);

        $institutionname = $dom->createElement('InstitutionName', 'National Herbarium of Victoria');
        $dataset->appendChild($institutionname);

        $datesupplied = $dom->createElement('DateSupplied', date('Y-m-d'));
        $dataset->appendChild($datesupplied);

        $personname = $dom->createElement('PersonName', 'MEL GPI type scanning project');
        $dataset->appendChild($personname);
        
        $objects = array();
        foreach ($units as $unit)
            $objects[] = "'$unit'";
        $objects = implode(',', $objects);
        
        $select = "SELECT co.CollectionObjectID, CAST(SUBSTRING(co.CatalogNumber, 1, 7) AS unsigned) AS UnitID, DATE(co.TimestampModified) AS DateLastModified, 
              collectorstring(ce.CollectingEventID, 1) AS Collectors, ce.StationFieldNumber AS CollectorNumber,
              IF(ce.StartDatePrecision=1, DAYOFMONTH(ce.StartDate), NULL) AS CollectionDateStartDay, IF(ce.StartDatePrecision<3, MONTH(ce.StartDate), NULL) AS CollectionDateStartMonth,
              YEAR(ce.StartDate) AS CollectionDateStartYear,
              IF(ce.EndDatePrecision=1, DAYOFMONTH(ce.EndDate), NULL) AS CollectionDateEndDay, IF(ce.EndDatePrecision<3, MONTH(ce.EndDate), NULL) AS CollectionDateEndMonth,
              YEAR(ce.EndDate) AS CollectionDateEndYear, cea.Text6 AS CollectionDateOtherText,
              country(l.GeographyID) AS Country, state(l.GeographyID) AS State, l.LocalityName,
              IF(l.MaxElevation, CONCAT(l.MinElevation, '–', l.MaxElevation), l.MinElevation) AS Altitude
            FROM collectionobject co
            JOIN collectingevent ce ON co.CollectingEventID=ce.CollectingEventID
            LEFT JOIN collectingeventattribute cea ON ce.CollectingEventAttributeID=cea.CollectingEventAttributeID
            JOIN locality l ON ce.LocalityID=l.LocalityID
            WHERE co.CatalogNumber IN ($objects)
            ORDER BY co.CatalogNumber";
        
        $query = $this->db->query($select);
        if ($query->num_rows()) {
            foreach ($query->result() as $item) {
                $unit = $dom->createElement('Unit');
                $dataset->appendChild($unit);
                
                $unitid = $dom->createElement('UnitID', 'MEL' . $item->UnitID);
                $unit->appendChild($unitid);
                
                $datelastmodified = $dom->createElement('DateLastModified', $item->DateLastModified);
                $unit->appendChild($datelastmodified);
                
                $select = "SELECT d.YesNo1=1 AS StoredUnderName, d.IsCurrent=1 AS CurrentName,
                      family(d.TaxonID) AS Family, genus(d.TaxonID) AS Genus, taxongeneral(d.TaxonID, 'species') AS Species,
                      taxongeneral(d.TaxonID, 'subspecies') AS Subspecies, taxongeneral(d.TaxonID, 'variety') AS Variety, taxongeneral(d.TaxonID, 'forma') AS Forma,
                      t.Author, IF(!isnull(a.Firstname), CONCAT_WS(', ', a.LastName, a.FirstName), a.LastName) AS Identifier,
                      IF(d.DeterminedDatePrecision=1, DAYOFMONTH(d.DeterminedDate), NULL) AS IdentificationDateDay, IF(d.DeterminedDatePrecision<3, MONTH(d.DeterminedDate), NULL) AS IdentificationDateMonth,
                      YEAR(d.DeterminedDate) AS IdentificationDateYear, d.TypeStatusName
                    FROM determination d
                    JOIN taxon t ON d.TaxonID=t.TaxonID
                    LEFT JOIN agent a ON d.DeterminerID=a.AgentID
                    WHERE (d.IsCurrent=1 OR d.YesNo1=1) AND d.CollectionObjectID=$item->CollectionObjectID";
                $query2 = $this->db->query($select);
                if ($query2->num_rows()) {
                    foreach ($query2->result() as $det) {
                        $identification = $dom->createElement('Identification');
                        $unit->appendChild($identification);
                        
                        if ($det->StoredUnderName)
                            $identification->setAttribute ('StoredUnderName', 'true');
                        else
                            $identification->setAttribute ('StoredUnderName', 'false');
                        
                        $family = $dom->createElement('Family', $det->Family);
                        $identification->appendChild($family);
                        
                        $genus = $dom->createElement('Genus', $det->Genus);
                        $identification->appendChild($genus);
                        
                        if ($det->Species) 
                            $species = $dom->createElement('Species', $det->Species);
                        else 
                            $species = $dom->createElement('Species', 'Not on sheet');
                        $identification->appendChild($species);
                        
                        if ($det->Author) {
                            $author = $dom->createElement('Author', xml_convert($det->Author));
                        }
                        else
                            $author = $dom->createElement('Author', 'Not on sheet');
                        $identification->appendChild($author);
                        
                        if ($det->Forma) {
                            $infraspecificrank = $dom->createElement('Infra-specificRank', 'f.');
                            $identification->appendChild($infraspecificrank);
                            
                            $infraspecificname = $dom->createElement('Infra-specificEpithet', $det->Forma);
                            $identification->appendChild($infraspecificname);
                        }
                        
                        elseif ($det->Variety) {
                            $infraspecificrank = $dom->createElement('Infra-specificRank', 'var.');
                            $identification->appendChild($infraspecificrank);
                            
                            $infraspecificname = $dom->createElement('Infra-specificEpithet', $det->Variety);
                            $identification->appendChild($infraspecificname);
                        }
                        
                        elseif ($det->Subspecies) {
                            $infraspecificrank = $dom->createElement('Infra-specificRank', 'subsp.');
                            $identification->appendChild($infraspecificrank);
                            
                            $infraspecificname = $dom->createElement('Infra-specificEpithet', $det->Subspecies);
                            $identification->appendChild($infraspecificname);
                        }
                        
                        if ($det->Identifier) 
                            $identifier = $dom->createElement ('Identifier',$det->Identifier);
                        else 
                            $identifier = $dom->createElement ('Identifier', 'Not on sheet');
                        $identification->appendChild($identifier);
                        
                        $identificationdate = $dom->createElement('IdentificationDate');
                        $identification->appendChild($identificationdate);
                        
                        if ($det->IdentificationDateYear) {
                            if ($det->IdentificationDateDay) {
                                $startday = $dom->createElement('StartDay', $det->IdentificationDateDay);
                                $identificationdate->appendChild($startday);
                            }

                            if ($det->IdentificationDateMonth) {
                                $startmonth = $dom->createElement('StartMonth', $det->IdentificationDateMonth);
                                $identificationdate->appendChild($startmonth);
                            }

                            $startyear = $dom->createElement('StartYear', $det->IdentificationDateYear);
                            $identificationdate->appendChild($startyear);
                        }
                        else {
                            $othertext = $dom->createElement('OtherText', 'Not on sheet');
                            $identificationdate->appendChild($othertext);
                        }

                        if ($det->TypeStatusName)
                            $typestatus = $dom->createElement('TypeStatus', ucfirst($det->TypeStatusName));
                        else
                            $typestatus = $dom->createElement ('TypeStatus', '-');
                        $identification->appendChild($typestatus);
                    }
                }
                else
                    return FALSE;
                
                if ($item->Collectors)
                    $collectors = $dom->createElement('Collectors', $item->Collectors);
                else
                    $collectors = $dom->createElement('Collectors', 'Not on sheet');
                $unit->appendChild($collectors);
                
                $collectornumber = $dom->createElement('CollectorNumber', $item->CollectorNumber);
                $unit->appendChild($collectornumber);

                $collectiondate = $dom->createElement('CollectionDate');
                $unit->appendChild($collectiondate);

                if ($item->CollectionDateStartDay) {
                    $startday = $dom->createElement('StartDay', $item->CollectionDateStartDay);
                    $collectiondate->appendChild($startday);
                }
               
                if ($item->CollectionDateStartMonth) {
                    $startmonth = $dom->createElement('StartMonth', $item->CollectionDateStartMonth);
                    $collectiondate->appendChild($startmonth);
                }

                if ($item->CollectionDateStartYear) {
                    $startyear = $dom->createElement('StartYear', $item->CollectionDateStartYear);
                    $collectiondate->appendChild($startyear);
                }

                if ($item->CollectionDateEndDay) {
                    $endday = $dom->createElement('EndDay', $item->CollectionDateEndDay);
                    $collectiondate->appendChild($startday);
                }

                if ($item->CollectionDateEndMonth) {
                    $endmonth = $dom->createElement('EndMonth', $item->CollectionDateEndMonth);
                    $collectiondate->appendChild($endmonth);
                }

                if ($item->CollectionDateEndYear) {
                    $endyear = $dom->createElement('EndYear', $item->CollectionDateEndYear);
                    $collectiondate->appendChild($endyear);
                }

                if ($item->CollectionDateOtherText) {
                    $othertext = $dom->createElement('OtherText', $item->CollectionDateOtherText);
                    $collectiondate->appendChild($othertext);
                }
                
                if (!$item->CollectionDateStartYear && !$item->CollectionDateOtherText) {
                    $othertext = $dom->createElement('OtherText', 'Not on sheet');
                    $collectiondate->appendChild($othertext);
                }

                if ($item->Country) {
                    $countryname = $dom->createElement('CountryName', $item->Country);
                    $unit->appendChild($countryname);
                }

                $isotwoletter = $dom->createElement('ISO2Letter', 'AU');
                $unit->appendChild($isotwoletter);

                $locality = '';
                if ($item->State && $item->LocalityName)
                    $locality = $item->State . '. ' . $item->LocalityName;
                elseif ($item->State)
                    $locality = $item->State;
                elseif ($item->LocalityName)
                    $locality = $item->LocalityName;
                if ($locality) {
                    $locality = $dom->createElement('Locality', $locality);
                    $unit->appendChild($locality);
                }

                if ($item->Altitude) {
                    $altitude = $dom->createElement('Altitude', $item->Altitude);
                    $unit->appendChild($altitude);
                }

            }
        }
        else
            return FALSE;

        return $dom->saveXML();

    }

}

?>
