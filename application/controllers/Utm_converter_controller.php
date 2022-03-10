<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Utm_converter_controller extends CI_Controller {
    private $data;
    
    public function __construct() {
        parent::__construct();
        $this->data = array();
        
        $this->load->database();
        $this->load->helper('url');
        $this->load->helper('form');
        $this->load->model('Utm_converter_model', 'utmconvertermodel');
        
        $this->output->enable_profiler(false);
    }

    public function index() {
        
        if($this->input->post('submit')) {
            if ($this->input->post('grid') &&
                    $this->input->post('zone') &&
                    $this->input->post('easting') &&
                    $this->input->post('northing') &&
                    $this->input->post('outputdatum')) {
                $this->data['point'] = $this->utmconvertermodel->convertToLatlong($this->input->post('easting'), 
                        $this->input->post('northing'), $this->input->post('grid'), 
                        $this->input->post('zone'), $this->input->post('outputdatum'));
                
            }
        }
        elseif ($this->input->post('submit_2')) {
            if (isset($_FILES['upload'])) {
                $infile = fopen($_FILES['upload']['tmp_name'], 'r');
                if (strpos($_FILES['upload']['name'], '.')) {
                    $outfilename = substr($_FILES['upload']['name'], 0, strpos($_FILES['upload']['name'], '.'))
                            . '_out' . substr($_FILES['upload']['name'], strpos($_FILES['upload']['name'], '.'));
                }
                else
                    $outfilename = $_FILES['upload']['name'] . '_out.csv';
                
                $csv = array();
                
                $firstline = fgetcsv($infile);
                $grid_col = array_search('Grid', $firstline);
                $zone_col = array_search('Zone', $firstline);
                $easting_col = array_search('Easting', $firstline);
                $northing_col = array_search('Northing', $firstline);
                
                
                if ($grid_col === FALSE)
                    $firstline[] = 'Grid';
                if ($zone_col === FALSE)
                    $firstline[] = 'Zone';
                
                $firstline[] = 'GridSRS';
                $firstline[] = 'LatLongSRS';
                $firstline[] = 'Latitude';
                $firstline[] = 'Longitude';
                
                $outputdatum = ($this->input->post('outputdatum_2')) ? $this->input->post('outputdatum_2') : 'WGS84';
                
                $csv[] = $this->arrayToCsvRow($firstline, ',');
                
                while (!feof($infile)) {
                    $line = fgetcsv($infile);
                    if ($line) {
                        $grid = ($grid_col !== FALSE) ? $line[$grid_col] : 'MGA';
                        $zone = ($zone_col !== FALSE) ? $line[$zone_col] : '55';

                        //convertToLatlong($easting, $northing, $grid='MGA', $zone='55', $outputdatum='WGS84')
                        $point = $this->utmconvertermodel->convertToLatlong($line[$easting_col], $line[$northing_col], $grid, $zone, $outputdatum);

                        if ($grid_col === FALSE)
                            $line[] = 'MGA';
                        if ($zone_col === FALSE)
                            $line[] = '55';

                        $line[] = 'EPSG:' . $point['gridsrs'];
                        $line[] = 'EPSG:' . $point['srs'];
                        $line[] = $point['lat'];
                        $line[] = $point['lng'];

                        $csv[] = $this->arrayToCsvRow($line, ',');
                    }
                }
                
                $this->output->enable_profiler(false);
                header('Content-type: text/csv');
                header("Content-Disposition: attachment; filename=$outfilename");
                echo implode("\r\n", $csv);
                return false;
                
                
            }
        }
        
        $this->load->view('utm_converter_view', $this->data);
    }
    
    private function arrayToCsv($data) {
        $csv = array();
        $csv[] = $this->arrayToCsvRow(array_keys((array) $data[0]), ',');
        foreach ($data as $row) {
            $csv[] = $this->arrayToCsvRow(array_values((array) $row), ',');
        }
        return implode("\n", $csv);
    }
    
    private function arrayToCsvRow( array &$fields, $delimiter = ';', $enclosure = '"', $encloseAll = false, $nullToMysqlNull = false ) {
        $delimiter_esc = preg_quote($delimiter, '/');
        $enclosure_esc = preg_quote($enclosure, '/');

        $output = array();
        foreach ( $fields as $field ) {
            if ($field === null && $nullToMysqlNull) {
                $output[] = 'NULL';
                continue;
            }

            // Enclose fields containing $delimiter, $enclosure or whitespace
            if ( $encloseAll || preg_match( "/(?:${delimiter_esc}|${enclosure_esc}|\s)/", $field ) ) {
                $output[] = $enclosure . str_replace($enclosure, $enclosure . $enclosure, $field) . $enclosure;
            }
            else {
                $output[] = $field;
            }
        }

        return implode( $delimiter, $output );
    }
    
}

/* End of file welcome.php */
/* Location: ./utm_convert/controllers/converter.php */