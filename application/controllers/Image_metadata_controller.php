<?php

class Image_metadata_controller extends CI_Controller {
    var $data;

    public function __construct() {
        parent::__construct();
        $this->output->enable_profiler(false);
        $this->data = [];
        //$this->data['js'][] = 'jquery.melisr.htmltableoptions.js';
        $this->data['title'] = 'MELISR | Attachment metadata';
        $this->load->model('Image_metadata_model', 'imagemetadatamodel');
        $this->session->unset_userdata(['error', 'warning', 'success']);

    }
    
    public function index() {
        $this->data['Users'] = $this->imagemetadatamodel->getUsers();
        
        if ($this->input->post('submit')) {
            if (!$this->input->post('startdate')) {
                $this->session->set_flashdata('warning', 'Please set a start date.');
            }
            else {
                $this->data['imagerecords'] = $this->imagemetadatamodel->getImageRecords($this->input->post('startdate'), 
                        $this->input->post('enddate'), 
                        $this->input->post('user'),
                        $this->input->post('missing'),
                        $this->input->post('extrafields'),
                        $this->input->post('insufficient_metadata')
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
                else {
                    $this->session->set_flashdata('warning', 'There are no records that match the criteria');
                }
            }
        }
        
        $this->load->view('imagemetadata_view', $this->data);
    }
    
    public function upload() {
        $this->data['js'][] = 'jquery.fileupload.js';
        $this->data['Users'] = $this->imagemetadatamodel->getUsers();
        
        if ($this->input->post('submit')) {
            if ($this->input->post('user') && isset($_FILES['image_metadata_upload']) && $_FILES['image_metadata_upload']['tmp_name']) {
                $file = file_get_contents($_FILES['image_metadata_upload']['tmp_name']);
                $filename = $_FILES['image_metadata_upload']['name'];
                $this->data['filename'] = $filename;
                $recordset = $this->imagemetadatamodel->uploadImageMetadata($file, $this->input->post('user'));
                $this->session->set_flashdata('success', "The file <b>$filename</b> has been uploaded. A record set &apos;<b>$recordset</b>&apos; has been created. 
                You have to close and open Specify in order to see it.");
            }
            else {
                $this->session->set_flashdata('error', 'Please select a Specify user from the 
                    drop-down list and select a file to upload');
            }
        }
        $this->load->view('imagemetadata_upload_view', $this->data);
    }
    
    private function createCSV($data, $delimiter = ',', $contenttype = 'text/csv', $extension = 'csv') {
        $filename = 'attachments_' . date('Ymd_Hi');
        
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
}

?>
