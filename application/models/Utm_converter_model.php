<?php

class Utm_converter_model extends CI_Model {
    protected $pgdb;
    
    public function __construct() {
        parent::__construct();
        $this->pgdb = $this->load->database('postgis', TRUE);
    }
    
    public function convertToLatlong($easting, $northing, $grid='MGA', $zone='55', $outputdatum='WGS84') {
        $inputSRS = $this->getInputSRS($grid, $zone);
        $outputSRS = $this->getOutputSRS($outputdatum);
        
        $sql = "SELECT ST_X(ST_Transform(ST_GeomFromText('POINT($easting $northing)',$inputSRS),$outputSRS)) As lng,
            ST_Y(ST_Transform(ST_GeomFromText('POINT($easting $northing)',$inputSRS),$outputSRS)) As lat,
            '$outputSRS' AS srs,
            '$inputSRS' AS gridsrs";
        
        $query = $this->pgdb->query($sql);
        
        return $query->row_array();
    }
    
    private function getInputSRS ($grid,$zone) {
        $prefix = array(
            'AMG' => '202',
            'AMG66' => '202',
            'AMG84' => '203',
            'MGA' => '283',
        );
        return $prefix[$grid] . $zone;
    }
    
    private function getOutputSRS ($outputdatum) {
        $srs = array(
            'WGS84' => '4326',
            'GDA94' => '4283'
        );
        return $srs[$outputdatum];
    }
    
}

?>
