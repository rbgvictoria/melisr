<?php
/**
 * @property CI_Loader $load
 * @property CI_Form_validation $form_validation
 * @property CI_Input $input
 * @property CI_Email $email
 * @property CI_DB_active_record $db
 * @property CI_DB_forge $dbforge
 */

class DehispidatorModel extends Model {

    function  __construct() {
        parent::Model();

        // connect to database
        $this->load->database();
    }
    
    /*public function addCurationOfficer($data) {
        foreach ($data as $index=>$row) {
            $genus = FALSE;
            $unit = array();
            foreach ($row as $colind=>$field) {
                if ($field['column'] == 'gen')
                    $genus = $field['value'];
            }
            
            if ($genus) {
                /*
                SELECT s.Text1
FROM taxon t
JOIN genusstorage gs ON t.TaxonID=gs.TaxonID
JOIN `storage` s ON gs.StorageID=s.StorageID
WHERE t.Name='Diuris';
                */
               /* $this->db->select('s.Text1');
                $this->db->from('taxon t');
                $this->db->join('genusstorage gs', 't.TaxonID=gs.TaxonID');
                $this->db->join('storage s', 'gs.StorageID=s.StorageID');
                $this->db->where('t.Name', $genus);
                $query = $this->db->get();
                if ($query->num_rows()) {
                    $row = $query->row();
                    $data[$index][] = array(
                        'column' => 'CurationOfficer',
                        'value' => $row->Text1,
                    );
                }
            }
            
        }
        return $data;
    }*/

}

?>
