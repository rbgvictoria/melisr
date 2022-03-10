<?php

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

/**
 * Description of Dwca
 *
 * @author Niels.Klazenga <Niels.Klazenga at rbg.vic.gov.au>
 */
class Dwca {
    protected $CI;
    
    public function __construct() {
        $this->CI =& get_instance();
        $this->CI->load->helper('csv');
        $this->CI->load->library('zip');
    }
    
    public function createDarwinCoreArchive($records, $filename) {
        $meta = file_get_contents(APPPATH . '/xml/exchange_data_meta.xml');
        $this->CI->zip->add_data('meta.xml', $meta);
        
        foreach ($records as $index => $rec) {
            $records[$index]['id'] = $rec['occurrenceID'];
        }
        
        $data = str_putcsv($records);
        $this->CI->zip->add_data('occurrences.csv', $data);
        $this->CI->zip->download($filename . '.zip');
    }
}
