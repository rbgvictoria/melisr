<?php

/**
 * Hispid5ToCsv class
 * 
 * Converts HISPID5 and ABCD2.06 to CSV.
 * 
 * @package Dehispidator
 * @author Niels Klazenga
 * @copyright Copyright (c) 2012, Council of Heads of Australian Herbaria (CHAH)
 * @license http://creativecommons.org/licenses/by/3.0/au/ CC BY 3.0 
 */
class Hispid5ToCsv {

    /**
     * removeWrapper function
     * 
     * Removes the BioCASe wrapper. The function was intended to also remove the 
     * namespace aliases, but that proved to be done more easily in the XPATHs than 
     * in the document. As the wrapper is not in the way, the function is not used 
     * anymore.
     * 
     * @param DOMDocument $doc
     * @return DOMDocument 
     */
    public function removeWrapper($doc) {
        $node = $doc->getElementsByTagName('DataSets')->item(0);
        $newdoc = new DOMDocument('1.0', 'UTF-8');
        $node = $newdoc->importNode($node, TRUE);
        $newdoc->appendChild($node);
        return $newdoc;
    }

    /**
     * parseHISPID5 function
     * 
     * Parses the XML and turns it into a two-dimensional array.
     * 
     * The function first creates a node list of all Units. For each Unit a node list
     * of all descendant elements is created. Each node is then checked for attributes and
     * whether they have a single child node (which means that is the text node). For 
     * elements with attributes the attribute nodes are retrieved and then for both 
     * elements and attributes the node path and node values are stored in a column-value 
     * array.
     * 
     * Special functions are called in each row to parse higher taxon names, named areas,
     * measurements or facts and collectors, which need a different structure in the output 
     * than they are stored in in the XML.
     * 
     * @param DOMDocument $doc
     * @return array 
     */
    public function parseHISPID5($doc) {
        $data = array();
        /*
         * Get the Units (individual records).
         */
        foreach ($doc->getElementsByTagName('Unit') as $unit){
            $row = array();
            
            /*
             * Get all the decendant elements of the Unit.
             */
            foreach ($unit->getElementsbyTagName('*') as $node) {
                /*
                 * The node path for the first in a set of repeatable elements is 
                 * different when there is only one element than when there are more
                 * than one. The search and replace arrays and the string replace
                 * functions later on take care of that.
                 */
                $search = array(
                    'GatheringAgent/',
                    'Identification/',
                    'HigherTaxon/',
                    'MeasurementOrFact/'
                );
                $replace = array(
                    'GatheringAgent[1]/',
                    'Identification[1]/',
                    'HigherTaxon[1]/',
                    'MeasurementOrFact[1]/'
                );

                /*
                 * Check whether the element node has attributes and, if so, retrieve
                 * them and store the node path and value. Some regular expresssions
                 * are used to get the right XPATH for the node path.
                 */
                if ($node->hasAttributes()) {
                    foreach ($node->attributes as $attribute){
                        $nodepath = $attribute->getNodePath();
                        preg_match('/Unit\[[\d]+\]/', $nodepath, $matches);
                        $nodepath = 'Unit' . substr($nodepath, strpos($nodepath, $matches[0])+strlen($matches[0]));
                        $nodepath = preg_replace(array('/\/[\w]+:/', '/@[\w]+:/'), array('/', '@'), $nodepath);
                        $nodepath = str_replace($search, $replace, $nodepath);
                            $row[] = array(
                                'column' => $nodepath,
                                'value' => $attribute->nodeValue
                            );
                    }
                }
                
                /*
                 * For nodes that have only a single child node, store the node path
                 * and value. Some regular expresssions are used to get the right 
                 * XPATH for the node path.
                 */
                if ($node->childNodes->length == 1) {
                    $nodepath = $node->getNodePath();
                    preg_match('/Unit\[[\d]+\]/', $nodepath, $matches);
                    $nodepath = 'Unit' . substr($nodepath, strpos($nodepath, $matches[0])+strlen($matches[0]));
                    $nodepath = preg_replace(array('/\/[\w]+:/', '/@[\w]+:/'), array('/', '@'), $nodepath);
                    $nodepath = str_replace($search, $replace, $nodepath);
                    $row[] = array(
                        'column' => $nodepath,
                        'value' => $node->nodeValue
                    );
                }
            }
            
            /*
             * Functions for elements that need to be transposed, the results of
             * which are merged onto the row array.
             */

            // higher taxa
            $row = array_merge($row, $this->HigherTaxa($unit));

            // named areas
            $row = array_merge($row, $this->NamedAreas($unit));

            // measurement or facts
            $row = array_merge($row, $this->MeasurementsOrFacts($unit));
            
            // collectors
            $row = array_merge($row, $this->GatheringAgents($unit));
            
            /*
             * Parse collectors strings for data sets without individual collectors 
             * and merge results onto the row array.
             */
            $row = array_merge($row, $this->parseGatheringAgentsText($unit));
            
            $data[] = $row;
        }
        return $data;
    }

    /**
     * HigherTaxa function
     * 
     * Creates cell arrays with the Higher Taxon Rank (or the XPATh expression with
     * the Higher Taxon Rank in the filter) as the column name and Higher Taxon Name
     * as the value. 
     * 
     * @param DOMElement $unit
     * @return array 
     */
    private function HigherTaxa($unit) {
        $row = array();
        /*
         * Pretty straightforward DOM traversal
         */
        $identifications = $unit->getElementsByTagName('Identification');
        if ($identifications->length > 0) {
            foreach ($identifications as $i => $identification) {
                $highertaxa = $identification->getElementsByTagName('HigherTaxon');
                if ($highertaxa->length) {
                    foreach ($highertaxa as $highertaxon) {
                        $highertaxonrank = $highertaxon->getElementsByTagName('HigherTaxonRank');
                        if ($highertaxonrank->length) {
                            $rank = $highertaxonrank->item(0)->nodeValue;
                            $k = $i + 1;
                            $row[] = array(
                                'column' => "Unit/Identifications/Identification[$k]/Result/TaxonIdentified/HigherTaxa/HigherTaxon[HigherTaxonRank=\"$rank\"]/HigherTaxonName",
                                'value' => $highertaxon->getElementsByTagName('HigherTaxonName')->item(0)->nodeValue
                            );
                        }
                    }
                }
            }
        }
        return $row;
    }

    /**
     * NamedAreas function
     * 
     * Creates cell arrays with the Area Class (or the XPATH with the Area Class in
     * the filter) as the column name and the Area Name as the value.
     * 
     * @param DOMElement $unit
     * @return array 
     */
    private function NamedAreas($unit) {
        $row = array();
        $namedareas = $unit->getElementsByTagName('NamedArea');
        if ($namedareas->length) {
            foreach ($namedareas as $namedarea) {
                $class = $namedarea->getElementsByTagName('AreaClass');
                $class = ($class->length) ? ucfirst($class->item(0)->nodeValue) : FALSE;
                $name = $namedarea->getElementsByTagName('AreaName');
                $name = ($name->length) ? $name->item(0)->nodeValue : FALSE;
                if ($class && $name && strtolower($class))
                    $row[] = array(
                        'column' => "Unit/Gathering/NamedAreas/NamedArea[AreaClass=\"$class\"]/AreaName",
                        'value' => $name
                    );
            }
        }
        return $row;
    }

    /**
     * MeasurementsOrFacts function
     * 
     * Creates cell areas with the Parameter added as a filter in the XPATHs that
     * make the column names, hence creating different names for each Measurement
     * or Fact.
     * 
     * Also creates valid ABCD XPATHs for Cultivated Occurrence and Natural Occurrence,
     * which are incorrect in the HISPID5 schema. These will be treated as Measurement 
     * or Facts with parameters CultivatedOccurrence and NaturalOccurrence respectively.
     * 
     * Node values of HISPID Unit Phenology elements are concatenated and given an
     * ABCD2.06 Measurement or Fact XPATH with parameter Phenology.
     * 
     * @param DOMElement $unit
     * @return array 
     */
    private function MeasurementsOrFacts($unit) {
        $row = array();
        $measurementsorfacts = $unit->getElementsByTagName('MeasurementOrFact');
        if ($measurementsorfacts->length) {
            foreach ($measurementsorfacts as $measurementorfact) {
                $parameter = $measurementorfact->getElementsByTagName('Parameter');
                $parameter = ($parameter->length) ? $parameter->item(0)->nodeValue : FALSE;
                $lowervalue = $measurementorfact->getElementsByTagName('LowerValue');
                $lowervalue = ($lowervalue->length) ? $lowervalue->item(0)->nodeValue : FALSE;
                if ($parameter && $lowervalue)
                    $row[] = array(
                        'column' => "Unit/MeasurementsOrFacts/MeasurementOrFact/MeasurementOrFactAtomised[Parameter=\"$parameter\"]/LowerValue",
                        'value' => $lowervalue
                    );
            }
        }
        
        /*
         * Get incorrectly mapped HISPID Cultivated Occurrence and Natural Occurrence.
         */
        $cultivated = $unit->getElementsByTagName('CultivatedOccurrence');
        if ($cultivated->length) {
            $row[] = array(
                'column' => 'Unit/MeasurementsOrFacts/MeasurementOrFact/MeasurementOrFactAtomised[Parameter="CultivatedOccurrence"]/LowerValue',
                'value' => $cultivated->item(0)->nodeValue
            );
        }
        
        $natural = $unit->getElementsByTagName('NaturalOccurrence');
        if ($natural->length) {
            $row[] = array(
                'column' => 'Unit/MeasurementsOrFacts/MeasurementOrFact/MeasurementOrFactAtomised[Parameter="NaturalOccurrence"]/LowerValue',
                'value' => $natural->item(0)->nodeValue
            );
        }
        
        /*
         * Concatenate values of HISPID Unit Phenology elements and stores the resulting
         * value with an ABCD Measurement or Fact XPATH. That is, provided that they are
         * mapped to the right concept (//Gathering/Agents/GatheringAgentsText), which is
         * not always the case.
         */
        $list = $unit->getElementsByTagName('Phenology');
        if ($list->length) {
            $phenology = array();
            foreach ($list as $phen) {
                $phenology[] = $phen->nodeValue;
            }
            $phenology = implode('; ', $phenology);
            $row[] = array(
                'column' => 'Unit/MeasurementsOrFacts/MeasurementOrFact/MeasurementOrFactAtomised[Parameter="Phenology"]/LowerValue',
                'value' => $phenology
            );
            
        }
        
        return $row;
    }
    
    /**
     * GatheringAgents function
     * 
     * Creates cell arrays with the Agent Text sequence attribute in the XPATH, in order
     * for the collector columns to be correctly ordered in the CSV. If no sequence attribute
     * is given the order in which the Agent Text elements come out of the XML, which is not
     * necessarily the same as the order they go in, will be used.
     * 
     * @param DOMElement $unit 
     * @return array
     */
    private function GatheringAgents($unit) {
        $row = array();
        $list = $unit->getElementsByTagName('GatheringAgent');
        if ($list->length) {
            foreach ($list as $i => $gatheringagent) {
                $primary = $gatheringagent->getAttribute('abcd:primarycollector');
                $agenttext = $gatheringagent->getElementsByTagName('AgentText');
                if ($agenttext->length)
                    $agenttext = $agenttext->item(0)->nodeValue;

                if ($gatheringagent->hasAttribute('abcd:sequence')) {
                    $sequence = $gatheringagent->getAttribute('abcd:sequence')+1;
                    $row[] = array(
                        'column' => "Unit/Gathering/Agents/GatheringAgent[@sequence=\"$sequence\"]/@primarycollector",
                        'value' => $primary
                    );
                    $row[] = array(
                        'column' => "Unit/Gathering/Agents/GatheringAgent[@sequence=\"$sequence\"]/AgentText",
                        'value' => $agenttext
                    );
                }
                else {
                    $k = $i + 1;
                    $row[] = array(
                        'column' => "Unit/Gathering/Agents/GatheringAgent[@sequence=\"$k\"]/@primarycollector",
                        'value' => "1"
                    );
                    $row[] = array(
                        'column' => "Unit/Gathering/Agents/GatheringAgent[@sequence=\"$k\"]/AgentText",
                        'value' => $agenttext
                    );
                }
            }
        }
        return $row;
    }
    
    /**
     * parseGateringAgentsText function
     * 
     * For data sets that do not provide individual collectors, but provide a concatenated 
     * string of collectors, this function parses the string and creates cell arrays
     * with individual collectors and the appropriate XPATHs. 
     * 
     * @param DOMElement $unit 
     * @return array
     */
    private function parseGatheringAgentsText($unit) {
        $row = array();
        /*
         * First check if parsed collectors are not already in the XML.
         */
        $list = $unit->getElementsByTagName('GatheringAgent');
        if (!$list->length) {
            /*
             * Get the collectors string, parse it and create individual collector
             * arrays.
             */
            $gatheringagents = $unit->getElementsByTagName('GatheringAgentsText');
            if ($gatheringagents->length) {
                $text = $gatheringagents->item(0)->nodeValue;
                $gatheringagents = explode(';', $text);
                foreach ($gatheringagents as $i => $agent) {
                    $k = $i + 1;
                    $row[] = array(
                        'column' => "Unit/Gathering/Agents/GatheringAgent[@sequence=\"$k\"]/@primarycollector",
                        'value' => "1"
                    );
                    $row[] = array(
                        'column' => "Unit/Gathering/Agents/GatheringAgent[@sequence=\"$k\"]/AgentText",
                        'value' => trim($agent)
                    );
                }
            }
        }
        return $row;
    }
    
    
    
    /**
     * outputToCsv function
     * 
     * Outputs parsed data to CSV. It does so by first creating an array of column
     * names from the parsed data, which forms the header row and then for each 
     * input find each column and store the value in the output row array. The output
     * row array is imploded and added to the CSV array, which in the end is imploded
     * and returned as a string.
     * 
     * Optionally a CSV file ('friendlynames.csv') with XPATHs in the first column 
     * and friendly column names in the second can be used to create a header row
     * with friendly column names. This file is also used to set the columns that
     * will be in the output and the order of the columns.
     * 
     * Currently only columns for which there is data will be in the output, but it
     * will be quite easy to add an option to output all columns in the configuration
     * file. This will be handy if we want to store data from multiple XML files in
     * the same CSV file. In this case the header row should be made optional as well.
     * 
     * @param array $data
     * @param boolean $friendlynames
     * @return string 
     */
    public function outputToCsv($data, $friendlynames=FALSE) {
        $csv = array();
        
        /*
         * Get the column names from the data.
         */
        $xpathcols = array();
        foreach ($data as $unit) {
            foreach ($unit as $item)
                $xpathcols[] = $item['column'];
        }
        $xpathcols = array_unique($xpathcols);
        sort($xpathcols);
        
        if ($friendlynames) {
            /*
             * Get the XPATHs and the friendly column names from the configuration file.
             */
            
            $handle = fopen('libraries/friendlynames.csv', 'r');
            $friendlynames = array();
            while (!feof($handle))
                $friendlynames[] = fgetcsv($handle);
            $xp_cols = array();
            $friendlycolnames = array();
            foreach ($friendlynames as $name) {
                /*
                 * Check whether the columns from the configuration file are present.
                 * in the data
                 */
                //if (in_array($name[0], $xpathcols)){
                    $friendlycolnames[] = $name[1];
                    $xp_cols[] = $name[0];
                //}
            }
            
            /*
             * This fixes the Canberra Source Institution and Unit IDs. Shifting the
             * first two items of both the XPATH column name and friendly column name
             * arrays, Accession Catalogue and Accession Number will be used instead
             * of Source Institution ID and Unit ID.
             */
            /*if (in_array('Unit/SpecimenUnit/Accessions/AccessionNumber', $xp_cols)) {
                array_shift($xp_cols);
                array_shift($xp_cols);
                array_shift($friendlycolnames);
                array_shift($friendlycolnames);
                $xp_cols[] = 'Unit/UnitID';
                //$friendlycolnames[] = '[CANB OccurrenceID]';
            }*/
            
            
            /*
             * Create the columns array and add the header row to the CSV array.
             */
            $cols = $xp_cols;
            $csv[] = implode(',', $friendlycolnames);
        }
        else {
            /*
             * Create the columns array and add the header row to the CSV array.
             */
            $cols = $xpathcols;
            $csv[] = implode(',', array_values($cols));
        }
        
        foreach ($data as $unit) {
            $row = array();
            
            /*
             * Create arrays with column name and values for each row.
             */
            $rowcols = array();
            $values = array();
            foreach ($unit as $item) {
                $rowcols[] = $item['column'];
                $values[] = $item['value'];
            }
            
            /*
             * For each column in the output CSV, find the array key in the column name
             * array for the input row, then store the value for the item with that
             * key in the input values array in the output row array. If the column
             * is not found an empty string is stored.
             */
            foreach ($cols as $col) {
                $key = array_search($col, $rowcols);
                if ($key !== FALSE) {
                    $value = $values[$key];
                    @$row[] = (is_numeric($value)) ? $value : '"' . $this->escapeQuotes($value) . '"';
                }
                else
                    $row[] = '""';
            }
            
            /*
             * Implode the row array and add to the CSV array.
             */
            $csv[] = implode(',', $row);
        }
        
        /*
         * Implode the CSV array and return as a string.
         */
        $csv = implode("\n", $csv);
        return $csv;
    }
    
    /**
     * getNamedAreaClasses function
     * 
     * Gets the Named Area Classes from the data array
     * 
     * @param array $data
     * @return array 
     */
    public function getNamedAreaClasses($data) {
        $classes = array();
        foreach ($data as $record) {
            foreach ($record as $item) {
                if (strpos($item['column'], 'AreaClass='))
                    $classes[] = $item['column'];
            }
        }
        $classes = array_unique($classes);
        return $classes;
    }

    /**
    * escapeQuotes function
    * 
    * In CSV double quotes must be escaped by replacing a single double quote with two double quotes.
    * 
    * @param string $string
    * @return string 
    */
    private function escapeQuotes($string) {
        if(is_string($string))
            return str_replace('"', '""', $string);
        else
            return $string;
    }
}
?>
