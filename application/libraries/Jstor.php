<?php defined('BASEPATH') OR exit('No direct script access allowed');
/* 
 * Copyright 2021 Royal Botanic Gardens Victoria.
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 *      http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 */

class Jstor 
{
    protected $ci;
    protected $doc;
    protected $dataset;
    protected $units;
    protected $identifications;
    
    protected $datasetElem;
    protected $unitElem;
    
    public function __construct($params)
    {
        $this->ci =& get_instance();
        $this->ci->load->helper('xml');
        $this->dataset = $params['dataset'];
        $this->units = $params['units'];
        $this->identifications = $params['identifications'];
        $this->doc = new DOMDocument();
    }
    
    public function createXml()
    {
        $this->createDatasetElement();
        
        foreach ($this->units as $unit) {
            $this->createUnitElement($unit);
        }
        
        echo xml_output($this->doc->saveXML());
    }
    
    protected function createDatasetElement()
    {
        $this->datasetElem = $this->doc->createElement('DataSet');
        $this->datasetElem->setAttributeNS(
            'http://www.w3.org/2000/xmlns/', 
            'xmlns:xsi', 
            'http://www.w3.org/2001/XMLSchema-instance'
        );
        $this->datasetElem->setAttributeNS(
            'http://www.w3.org/2001/XMLSchema-instance', 
            'xsi:noNamespaceSchemaLocation', 
            'http://plants.jstor.org/XSD/AfricanTypesv2.xsd'
        );
        
        $institutionCode = $this->doc->createElement('InstitutionCode', 'MEL');
        $this->datasetElem->appendChild($institutionCode);
        $institutionName = $this->doc->createElement('InstitutionName', 'National Herbarium of Victoria');
        $this->datasetElem->appendChild($institutionName);
        $dateSupplied = $this->doc->createElement('DateSupplied', $this->dataset->DateSupplied);
        $this->datasetElem->appendChild($dateSupplied);
        $personName = $this->doc->createElement('PersonName', $this->dataset->PersonName);
        $this->datasetElem->appendChild($personName);
        
        $this->doc->appendChild($this->datasetElem);
    }
    
    protected function createUnitElement($unit)
    {
        $unitElem = $this->doc->createElement('Unit');
        $unitElem->appendChild($this->doc->createElement('UnitID', $unit->UnitID));
        $unitElem->appendChild($this->doc->createElement('DateLastModified', $unit->DateLastModified));
        $identifications = array_filter($this->identifications, function ($arr) use ($unit) {
            if ($arr->CollectionObjectID == $unit->CollectionObjectID) {
                return true;
            }
            return false;
        });
        foreach ($identifications as $identification) {
            $identificationElem = $this->createIdentificationElem($identification);
            $unitElem->appendChild($identificationElem);
        }
        $unitElem->appendChild($this->doc->createElement('Collectors', htmlspecialchars($unit->Collectors)));
        $unitElem->appendChild($this->doc->createElement('CollectorNumber', htmlspecialchars($unit->CollectorNumber)));
        $unitElem->appendChild($this->createCollectionDateElement($unit));
        if ($unit->CountryName) {
            $unitElem->appendChild($this->doc->createElement('CountryName', htmlspecialchars($unit->CountryName)));
        }
        $unitElem->appendChild($this->doc->createElement('ISO2Letter', htmlspecialchars($unit->ISO2Letter ?: 'ZZ')));
        $unitElem->appendChild($this->doc->createElement('Locality', htmlspecialchars($unit->Locality)));
        $this->datasetElem->appendChild($unitElem);
    }
    
    protected function createCollectionDateElement($unit)
    {
        $collectionDateElem = $this->doc->createElement('CollectionDate');
        if ($unit->CollectionDateStartDay) {
            $collectionDateElem->appendChild($this->doc->createElement('StartDay', $unit->CollectionDateStartDay));
        }
        if ($unit->CollectionDateStartMonth) {
            $collectionDateElem->appendChild($this->doc->createElement('StartMonth', $unit->CollectionDateStartMonth));
        }
        if ($unit->CollectionDateStartYear) {
            $collectionDateElem->appendChild($this->doc->createElement('StartYear', $unit->CollectionDateStartYear));
        }
        if ($unit->CollectionDateEndDay) {
            $collectionDateElem->appendChild($this->doc->createElement('EndDay', $unit->CollectionDateEndDay));
        }
        if ($unit->CollectionDateEndMonth) {
            $collectionDateElem->appendChild($this->doc->createElement('EndMonth', $unit->CollectionDateEndMonth));
        }
        if ($unit->CollectionDateEndYear) {
            $collectionDateElem->appendChild($this->doc->createElement('EndYear', $unit->CollectionDateEndYear));
        }
        if ($unit->CollectionDateOtherText) {
            $collectionDateElem->appendChild($this->doc->createElement('OtherText', htmlspecialchars($unit->CollectionDateOtherText)));
        }
        return $collectionDateElem;
    }
    
    protected function createIdentificationElem($identification)
    {
        $identificationElem = $this->doc->createElement('Identification');
        $identificationElem->setAttribute('StoredUnderName', $this->getStoredUnder($identification));
        $identificationElem->appendChild($this->doc->createElement('Family', $identification->Family));
        $identificationElem->appendChild($this->doc->createElement('Genus', $identification->Genus));
        $identificationElem->appendChild($this->doc->createElement('Species', $identification->SpecificEpithet));
        $identificationElem->appendChild($this->doc->createElement('Author', htmlspecialchars($identification->Author)));
        if ($identification->InfraspecificRank) {
            $identificationElem->appendChild($this->doc->createElement('Infra-specificRank', $identification->InfraspecificRank));
        }
        if ($identification->InfraspecificEpithet) {
            $identificationElem->appendChild($this->doc->createElement('Infra-specificEpithet', $identification->InfraspecificEpithet));
        }
        $identificationElem->appendChild($this->doc->createElement('Identifier', $identification->Identifier));
        $identificationElem->appendChild($this->createIdentificationDateElement($identification));
        $identificationElem->appendChild($this->doc->createElement('TypeStatus', $identification->TypeStatus ?: '-'));
        
        return $identificationElem;
    }
    
    protected function createIdentificationDateElement($identification)
    {
        $identificationDateElem = $this->doc->createElement('IdentificationDate');
        if ($identification->IdentificationDateStartDay) {
            $identificationDateElem->appendChild($this->doc->createElement('StartDay', $identification->IdentificationDateStartDay));
        }
        if ($identification->IdentificationDateStartMonth) {
            $identificationDateElem->appendChild($this->doc->createElement('StartMonth', $identification->IdentificationDateStartMonth));
        }
        if ($identification->IdentificationDateStartYear) {
            $identificationDateElem->appendChild($this->doc->createElement('StartYear', $identification->IdentificationDateStartYear));
        }
        if ($identification->IdentificationDateOtherText) {
            $identificationDateElem->appendChild($this->doc->createElement('OtherText', htmlspecialchars($identification->IdentificationDateOtherText)));
        }
        return $identificationDateElem;
    }
    
    protected function getStoredUnder($identification)
    {
        if ($identification->StoredUnder) {
            return 'true';
        }
        elseif($identification->CurrentDetermination) {
            $identifications = array_filter($this->identifications, function($arr) use($identification) {
                return $arr->CollectionObjectID == $identification->CollectionObjectID && $arr->StoredUnder == 1 ? true : false;
            });
            return $identifications ? 'false' : 'true';
        }
        return 'false';
    }
}

/*
 * /application/libraries/Jstor.php
 */