<?php

class ImageMetadata extends Controller {
    var $data;

    public function __construct() {
        parent::Controller();
        $this->load->database();
        $this->load->helper('form');
        $this->load->helper('file');
        $this->load->helper('url');
        $this->output->enable_profiler(TRUE);
        $this->data = array();
        $this->data['css'][] = 'datatables.css';
        $this->data['js'][] = 'jquery.dataTables.min.js';
        $this->data['js'][] = 'jquery.melisr.htmltableoptions.js';
        $this->data['bannerimage'] = $this->banner();
        $this->data['title'] = 'MELISR | Image metadata';
        $this->load->model('imagemetadatamodel');
    }
    
    public function index() {
        $this->data['Users'] = $this->imagemetadatamodel->getUsers();
        
        if ($this->input->post('submit')) {
            if (!$this->input->post('startdate')) {
                $this->data['message'] = 'Please set a start date.';
            }
            else {
                $this->data['imagerecords'] = $this->imagemetadatamodel->getImageRecords($this->input->post('startdate'), 
                        $this->input->post('enddate'), 
                        $this->input->post('user'),
                        $this->input->post('missing'),
                        $this->input->post('extrafields')
                    );
                
                if ($this->data['imagerecords']) {
                    if ($this->input->post('format') == 'txt') {
                        $this->output->enable_profiler(FALSE);
                        $this->createCSV($this->data['imagerecords'], "\t", 'text/plain', 'txt');
                        return TRUE;
                    }
                    elseif ($this->input->post('format') == 'csv') {
                        $this->output->enable_profiler(FALSE);
                        $this->createCSV($this->data['imagerecords']);
                        return TRUE;
                    }
                }
                else 
                    $this->data['message'] = 'There are no records that match the criteria';
            }
        }
        
        $this->load->view('imagemetadata_view', $this->data);
    }
    
    public function upload() {
        $this->data['Users'] = $this->imagemetadatamodel->getUsers();
        
        if ($this->input->post('submit')) {
            if ($this->input->post('user') && isset($_FILES['image_metadata_upload'])) {
                $file = file_get_contents($_FILES['image_metadata_upload']['tmp_name']);
                $this->data['filename'] = $_FILES['image_metadata_upload']['name'];
                $this->data['recordset'] = $this->imagemetadatamodel->uploadImageMetadata($file, $this->input->post('user'));
            }
            else 
                $this->data['message'] = 'Please select a Specify user from the drop-down list and select a file to upload';
        }
        $this->load->view('imagemetadata_upload_view', $this->data);
    }
    
    private function createCSV($data, $delimiter = ',', $contenttype = 'text/csv', $extension = 'csv') {
        $filename = 'images_' . date('Ymd_Hi');
        
        $csv = array();
        
        $firstrow = array_keys($data[0]);
        array_shift($firstrow);
        foreach ($firstrow as $index=>$value) {
            if(is_numeric($value))
                $firstrow[$index] = $value;
            else
                $firstrow[$index] = '"' . $value . '"';
        }
        $csv[] = implode($delimiter, $firstrow);

        foreach ($data as $row) {
            $row = (array) $row;
            array_shift($row);
            foreach ($row as $key => $value) {
                if ($value) {
                    if (is_numeric($value))
                        $row[$key] = $value;
                    else {
                        $value = trim($value, "\\");
                        $row[$key] = '"' . $value . '"';
                    }
                }
            }
            $csv[] = implode($delimiter, $row);
        }

        $csv = implode("\r\n", $csv);

        header("Content-Disposition: attachment; filename=\"$filename.$extension\"");
        header("Content-type: $contenttype");
        echo $csv;
        
    }

    private function banner() {
        $banners = get_dir_file_info('./images/banners', TRUE);
        $banners = array_values($banners);
        $count = count($banners);
        $i = rand(0, $count-1);
        return $banners[$i]['name'];
    }
}

?>
