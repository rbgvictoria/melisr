<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Hispid3ToCsv {
    function parseHispid3($hispid) {
        preg_match('/,[\s]+{/', $hispid, $matches);
        $hispid = trim(substr($hispid, strpos($hispid, $matches[0])+  strlen($matches[0])));
        
        preg_match('/}[\s]+endfile/', $hispid, $matches);
        $hispid = trim(substr($hispid, 0, strpos($hispid, $matches[0])));
        
        $units = preg_split("/}[\s]+{/", $hispid);
        
        foreach ($units as $key=>$unit) {
            $fields = preg_split('/,[\s]*[\n]+[\s]*/', $unit);
            
            $cols = array();
            foreach ($fields as $field) {
                $field = trim(preg_replace('/[\s]+/', ' ', $field));
                $col = preg_split('/[\s]+/', $field, 2);
                if (count($col) == 2)
                    $value = (substr($col[1], strlen($col[1])-1) == ',') ? trim(substr($col[1], 0, strlen($col[1])-1)): trim($col[1]);
                $value = trim($value, '"');
                    $cols[] = array (
                        'column' => $col[0],
                        'value' => $value,
                    );
            }
            $units[$key] = $cols;
        }
        return $units;
    }
    
    function massageData($data) {
        foreach ($data as $index=>$row) {
            $unit = array();
            foreach ($row as $colind=>$field) {
                //
                $unit[$field['column']] = $field['value'];
                
                // Change column values
                if ($field['column'] == 'cou' || $field['column'] == 'fam')
                    $data[$index][$colind]['value'] = ucwords(strtolower($field['value']));
                
                if ($field['column'] == 'pru')
                    $data[$index][$colind]['value'] = $this->properStates($field['value']);
                
                if ($field['column'] == 'hab' || $field['column'] == 'cnot')
                    $data[$index][$colind]['value'] = str_replace ('{', '', str_replace ('}', '', $field['value']));
                
                if ($field['column'] == 'spadat')
                    $data[$index][$colind]['value'] = str_replace(array(' ', '-', '/'), '', $field['value']);

                if (in_array($field['column'], array('loc', 'hab', 'cnot', 'misc'))) {
                    if (!in_array(substr($field['value'], strlen($field['value'])-1, 1), array('.', '?')))
                        $data[$index][$colind]['value'] .= '.';
                    
                  }
            
                // Add columns
                if (isset($unit['isprk']) && isset($unit['isp'])) {
                    $infra = $this->parseInfraspecies($unit['isprk'], $unit['isp']);
                    $data[$index][] = $infra;
                }

                if (isset($unit['cdat'])) {
                    $date = $this->mysqlDate($unit['cdat']);
                    $data[$index][] = array(
                        'column' => 'CollectingDate',
                        'value' => $date,
                    );
                }

                if (isset($unit['vdat'])) {
                    $date = $this->mysqlDate($unit['vdat']);
                    $data[$index][] = array(
                        'column' => 'DeterminationDate',
                        'value' => $date,
                    );
                }

                if (isset($unit['latdeg']) && isset($unit['latdir'])) {
                    $deg = $unit['latdeg'];
                    $min = (isset($unit['latmin'])) ? $unit['latmin'] : FALSE;
                    $sec = (isset($unit['latsec'])) ? $unit['latsec'] : FALSE;
                    $hem = $unit['latdir'];
                    $latitude = $this->latLngText($deg, $min, $sec, $hem);
                    $data[$index][] = array(
                        'column' => 'LatitudeText',
                        'value' => $latitude,
                    );
                }

                if (isset($unit['londeg']) && isset($unit['londir'])) {
                    $deg = $unit['londeg'];
                    $min = (isset($unit['lonmin'])) ? $unit['lonmin'] : FALSE;
                    $sec = (isset($unit['lonsec'])) ? $unit['lonsec'] : FALSE;
                    $hem = $unit['londir'];
                    $longitude = $this->latLngText($deg, $min, $sec, $hem);
                    $data[$index][] = array(
                        'column' => 'LongitudeText',
                        'value' => $longitude,
                    );
                }

                if (isset($unit['cnam'])) {
                    $primary = $unit['cnam'];
                    $addit = (isset($unit['cnam2'])) ? $unit['cnam2'] : FALSE;
                    $data[$index] = array_merge($data[$index], $this->dehispidateCollectors($primary, $addit));
                }

                if (isset($unit['vnam']))
                    $data[$index] = array_merge($data[$index], $this->dehispidateDeterminers($unit['vnam']));

                if (isset($unit['alt'])) {
                    $data[$index][] = array(
                        'column' => 'AltitudeUnit',
                        'value' => 'metres',
                    );
                }

                if (isset($unit['altacy'])) {
                    $data[$index][] = array(
                        'column' => 'AltitudeUncertainty',
                        'value' => $unit['altacy'] . ' m',
                    );
                }

                if (isset($unit['geoacy'])) {
                    $data[$index][] = array(
                        'column' => 'GeocodeUncertainty',
                        'value' => $this->GeocodeUncertainty($unit['geoacy']),
                    );
                }

                if (isset($unit['phe'])) {
                    $data[$index] = array_merge($data[$index], $this->parsePhenology($unit['phe']));
                }

                if (isset($unit['desrep'])) {
                    $data[$index][] = $this->parseDuplicateString($unit['desrep']);
                }

                if (isset($unit['geosou'])) 
                    $data[$index][] = $this->GeocodeSource($unit['geosou']);

                if (isset($unit['det'])) 
                    $data[$index][] = $this->DetType($unit['det']);

                if (isset($unit['rkql'])) 
                    $data[$index][] = $this->QualifierRank($unit['rkql']);

                if (isset($unit['idql'])) 
                    $data[$index][] = $this->Qualifier($unit['idql']);

                if (isset($unit['poscul'])) 
                    $data[$index][] = $this->CultivatedStatus($unit['poscul']);

                if (isset($unit['posnat'])) 
                    $data[$index][] = $this->IntroducedStatus($unit['posnat']);

                if ((isset($unit['misc']) || isset($unit['fre'])) && $unit['insid'] == 'PERTH') {
                    $misc = (isset($unit['misc'])) ? trim($unit['misc']) : NULL;
                    if ($misc && substr($misc, strlen($misc)-1) != '.')
                            $misc .= '.';
                    $fre = (isset($unit['fre'])) ? ucfirst(trim($unit['fre'])) : NULL;
                    if ($fre && substr($fre, strlen($fre)-1) != '.')
                            $fre .= '.';
                    $data[$index][] = array(
                        'column' => 'CollectingNotes',
                        'value' => trim($misc . ' ' . $fre)
                    );
                }
                elseif (isset($unit['fre'])) {
                    $fre = ucfirst(trim($unit['fre']));
                    if (substr($fre, strlen($fre)-1) != '.')
                            $fre .= '.';
                    $data[$index][] = array(
                        'column' => 'CollectingNotes',
                        'value' => $fre
                    );
                }

                if (isset($unit['hab']) || isset($unit['veg']))
                    $data[$index][] = $this->Habitat($unit);

                $data[$index][] = array(
                    'column' => 'WBUpload',
                    'value' => 1,
                );

                $data[$index][] = array(
                    'column' => 'PrepType',
                    'value' => 'Sheet',
                );

                $data[$index][] = array(
                    'column' => 'PrepQuantity',
                    'value' => 1,
                );

                $data[$index][] = array(
                    'column' => 'LocalityUniquefier',
                    'value' => $index + 1,
                );
            }
        
        }
        return $data;
    }
    
    function DetType($det) {
        if ($det == 'conf.')
            $det = 'Conf.';
        else 
            $det = 'Det.';
        return array(
            'column' => 'DetType',
            'value' => $det,
        );
    }
    
    function QualifierRank($rkql) {
        if ($rkql == 'F')
            $rkql = 'family';
        elseif ($rkql == 'G')
                $rkql = 'genus';
        elseif ($rkql == 'S')
                $rkql = 'species';
        else
            $rkql = NULL;
        return array(
            'column' => 'QualifierRank',
            'value' => $rkql,
        );
    }
    
    function Qualifier($idql) {
        if ($idql == 'aff.')
            $idql = 'aff.';
        elseif ($idql == 'aff')
                $idql = 'aff.';
        elseif ($idql == 'cf.')
                $idql = 'cf.';
        elseif ($idql == 'cf')
                $idql = 'cf.';
        else
            $idql = '?';
        return array(
            'column' => 'Qualifier',
            'value' => $idql,
        );
    }

    function CultivatedStatus($poscul) {
        if ($poscul == 'Wild')
            $poscul = 'Not cultivated';
        else
            $poscul = ($poscul);
        return array(
            'column' => 'CultivatedStatus',
            'value' => $poscul,
        );
    }
    
        function IntroducedStatus($posnat) {
        if ($posnat == 'Natural')
            $posnat = 'Native';
        elseif ($posnat == 'Naturalised')
                $posnat = 'Not native';
        elseif ($posnat == 'Unknown')
                $posnat = 'Unknown';
        else
            $posnat = NULL;
        return array(
            'column' => 'IntroducedStatus',
            'value' => $posnat,
        );
    }
    
    function Habitat($unit) {
        $hab = (isset($unit['hab'])) ? trim($unit['hab']) : NULL;
        if ($hab && substr($hab, strlen($hab)-1) != '.')
                $hab .= '.';
        $veg = (isset($unit['veg'])) ? trim($unit['veg']) : NULL;
        if ($veg && substr($veg, strlen($veg)-1) != '.')
                $veg .= '.';
        return array(
            'column' => 'Habitat',
            'value' => trim($hab . ' ' . $veg)
        );
    }

    function GeocodeSource($geosou) {
        if ($geosou == 'compiler')
            $geosou = 'Data entry person';
        elseif ($geosou == 'Automatically generated')
            $geosou = 'Exchange data';
        elseif ($geosou == 'automatically generated')
            $geosou = 'Exchange data';
        else 
            $geosou = ucfirst($geosou);
        return array(
            'column' => 'GeocodeSource',
            'value' => $geosou,
        );
    }
    
    function cleanDatum($spadat) {
        return str_replace(array(' ', '-', '/'), '', $spadat);
    }
    
    function parseDuplicateString($desrep) {
        $dups = array();
        $desrep = explode(',', $desrep);
        foreach ($desrep as $dup) {
            if (trim($dup) && trim($dup) != 'MEL')
                $dups[] = trim($dup);
        }
        sort($dups);
        return array(
            'column' => 'OtherDuplicates',
            'value' => implode(', ', $dups),
        );
    }
    
    function parsePhenology($phe) {
        $ret = array();
        $flowers = array(
            'bisexual flowers',
            'female cones',
            'female flowers',
            'flowers',
            'male/female cones',
            'male cones',
            'male flowers',
        );
        $fruit = array(
            'fruit',
            'fruiting cones',
        );
        $buds = array(
            'buds',
        );
        
        $phe = explode(',', $phe);
        foreach ($phe as $value) {
            if (in_array(strtolower(trim($value)), $flowers))
                $ret[] = array(
                    'column' => 'Flowers',
                    'value' => 1,
                );
            if (in_array(strtolower(trim($value)), $fruit))
                $ret[] = array(
                    'column' => 'Fruit',
                    'value' => 1,
                );
            if (strtolower(trim($value)) == 'buds')
                $ret[] = array(
                    'column' => 'Buds',
                    'value' => 1,
                );
            if (strtolower(trim($value)) == 'fertile')
                $ret[] = array(
                    'column' => 'Fertile',
                    'value' => 1,
                );
            if (strtolower(trim($value)) == 'Sterile')
                $ret[] = array(
                    'column' => 'Sterile',
                    'value' => 1,
                );
            if (strtolower(trim($value)) == 'Leafless')
                $ret[] = array(
                    'column' => 'Leafless',
                    'value' => 1,
                );
        }
        return $ret;
    }
    
    function GeocodeUncertainty($geoacy) {
        switch ($geoacy) {
            case $geoacy <= 50:
                $code = '1. 0 - 50 m';
                break;
            case $geoacy <= 1000:
                $code = '2. 50 m - 1 km';
                break;
            case $geoacy <= 10000:
                $code = '3. 1 - 10 km';
                break;
            case $geoacy <= 25000:
                $code = '10 - 25 km';
                break;
            case $geoacy > 25000:
                $code = '> 25 km';
                break;
            default:
                break;
        }
        return $code;
    }
    
    function dehispidateDeterminers($vnam) {
        $ret = array();
        $determiners = array();
        $vnamArray = explode(',', $vnam);
        if (count($vnamArray) < 3) {
            $ret[] = array(
                'column' => 'DeterminerLastName',
                'value' => trim($vnamArray[0]),
            );
            if (isset($vnamArray[1]))
                $ret[] = array(
                    'column' => 'DeterminerFirstName',
                    'value' => trim($vnamArray[1]),
                );
        }
        else {
            for ($i=0; $i<count($vnamArray); $i+=2) {
                $det = array();
                $det[] = trim($vnamArray[$i]);
                if (isset($vnamArray[$i+1])) $det[] = trim($vnamArray[$i+1]);
                $determiners[] = implode(', ', $det);
            }
            $determiners = implode('; ', $determiners);
            $ret[] = array(
                'column' => 'DeterminerLastName',
                'value' => $determiners,
            );
        }
        return $ret;
    }
    
    function dehispidateCollectors($primary, $addit=FALSE){
        $ret = array();
        $lastname = array();
        $firstname = array();
        $isprimary = array();
        
        $collArray = explode(',', $primary);
        for ($i=0; $i<count($collArray); $i+=2) {
            $lastname[] = trim($collArray[$i]);
            $firstname[] = (isset($collArray[$i+1])) ? trim($collArray[$i+1]) : FALSE;
            $isprimary[] = 1;
        }
        
        if ($addit) {
            $collArray = explode(',', $addit);
            for ($i=0; $i<count($collArray); $i+=2) {
                $lastname[] = trim($collArray[$i]);
                $firstname[] = (isset($collArray[$i+1])) ? trim($collArray[$i+1]) : FALSE;
                $isprimary[] = 0;
            }
        }
        
        for ($i=0; $i<count($lastname); $i++) {
            $j = $i+1;
            $ret[] = array(
                'column' => 'Collector' . $j . 'LastName',
                'value' => $lastname[$i],
            );
            
            $ret[] = array(
                'column' => 'Collector' . $j . 'FirstName',
                'value' => $firstname[$i],
            );

            $ret[] = array(
                'column' => 'Collector' . $j . 'IsPrimary',
                'value' => $isprimary[$i],
            );
        }
       
        return $ret;
    }
    
    function latLngText($deg, $min, $sec, $hem) {
        $text = array();
        $text[] = $deg;
        if ($min !== FALSE) $text[] = str_pad ($min, 2, '0', STR_PAD_LEFT);
        if ($sec !== FALSE) $text[] = str_pad ($sec, 2, '0', STR_PAD_LEFT);
        $text[] = $hem;
        return implode(' ', $text);
    }
    
    function properStates($pru) {
        $stateArray = array(
            'WA' => 'Western Australia',
            'NT' => 'Northern Territory',
            'SA' => 'South Australia',
            'QLD' => 'Queensland',
            'NSW' => 'New South Wales',
            'VIC' => 'Victoria',
            'TAS' => 'Tasmania',
        );
        if (in_array($pru, array_keys($stateArray)))
            return ($stateArray[$pru]);
        else
            return ucwords(strtolower($pru));
    }
    
    function parseInfraspecies($rank, $epithet) {
        $rankArray = array(
            'subsp.' => 'Subspecies',
            'var.' => 'Variety',
            'f.' => 'Forma',
        );
        if (in_array($rank, array_keys($rankArray))) {
            return array(
                'column' => $rankArray[$rank],
                'value' => $epithet,
            );
        }
        else return FALSE;
    }
    
    function mysqlDate($hispiddate) {
        $date = array();
        $date[] = (strlen($hispiddate) > 6) ? substr($hispiddate, 6, 2) : '00';
        $date[] = (strlen($hispiddate) > 4) ? substr($hispiddate, 4, 2) : '00';
        $date[] = substr($hispiddate, 0, 4);
        return implode('/', $date);
    }

    function outputToCsv($data, $outputfields=FALSE) {
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
        
        if ($outputfields) {
            /*
             * Get the XPATHs and the friendly column names from the configuration file.
             */
            
            $handle = fopen('libraries/dehispidatorconfig/' . $outputfields, 'r');
            $friendlynames = array();
            while (!feof($handle))
                $friendlynames[] = fgetcsv($handle);
            $xp_cols = array();
            $friendlycolnames = array();
            foreach ($friendlynames as $name) {
                $friendlycolnames[] = $name[1];
                $xp_cols[] = $name[0];
            }

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
                    $row[] = (is_numeric($value)) ? $value : '"' . $this->escapeQuotes($value) . '"';
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
        
        
        
        /*
        $cols = array();
        foreach ($data as $unit) {
            foreach ($unit as $item) {
                $cols[] = $item['field'];
            }
        }
        $cols = array_unique($cols);

        $csv[] = implode(',', array_values($cols));

        foreach ($data as $unit) {
            $row = array();
            $rowcols = array();
            $values = array();
            foreach ($unit as $item) {
                $rowcols[] = $item['field'];
                $values[] = $item['value'];
            }

            foreach ($cols as $col) {
                $key = array_search($col, $rowcols);
                if ($key !== FALSE) 
                    $row[] = $values[$key];
                else
                    $row[] = '""';
            }

            $csv[] = implode(',', $row);
        }
        $csv = implode("\n", $csv);
        return $csv;*/
    }

    private function escapeQuotes($string) {
        return str_replace('"', '""', $string);
    }
    
}
?>
