<?php
/**
 * @property CI_Loader $load
 * @property CI_Form_validation $form_validation
 * @property CI_Input $input
 * @property CI_Email $email
 * @property CI_DB_active_record $db
 * @property CI_DB_forge $dbforge
 */

class Audit extends Controller {
    var $data;

    function __construct() {
        parent::Controller();
        $this->load->helper('form');
        $this->load->helper('file');
        $this->load->helper('url');
        $this->output->enable_profiler(TRUE);
        $this->data['bannerimage'] = $this->banner();
        $this->load->model('auditmodel');
        $this->load->model('fqcmmodel');
        $this->load->library('session');
        $this->data['Users'] = $this->fqcmmodel->getUsers();
    }

    function index($limit=FALSE, $page=FALSE) {
        $this->getTableList();
        
        if (!$limit) {
            $unset = array(
                'startdate' => '',
                'enddate' => '',
                'table' => '',
                'action' => '',
                'user' => '',
            );
            $this->session->unset_userdata($unset);
        }
        
        $limit = ($limit) ? $limit : 100;
        
        $offset = 0;
        if ($page)
            $offset = ($page-1)*$limit;
        
        
        
        $searchparams = array();
        if ($this->input->post('submit')) {
            if ($this->input->post('startdate'))
                $searchparams['startdate'] = $this->input->post('startdate');
            if ($this->input->post('enddate'))
                $searchparams['enddate'] = $this->input->post('enddate');
            if ($this->input->post('table'))
                $searchparams['table'] = $this->input->post('table');
            if ($this->input->post('action'))
                $searchparams['action'] = $this->input->post('action');
            if ($this->input->post('user'))
                $searchparams['user'] = $this->input->post('user');
        }
        elseif ($this->session->userdata('startdate') || $this->session->userdata('enddate') || 
            $this->session->userdata('table') || $this->session->userdata('action') || $this->session->userdata('user')) {
            if ($this->session->userdata('startdate'))
                $searchparams['startdate'] = $this->session->userdata('startdate');
            if ($this->session->userdata('enddate'))
                $searchparams['enddate'] = $this->session->userdata('enddate');
            if ($this->session->userdata('table'))
                $searchparams['table'] = $this->session->userdata('table');
            if ($this->session->userdata('action'))
                $searchparams['action'] = $this->session->userdata('action');
            if ($this->session->userdata('user'))
                $searchparams['user'] = $this->session->userdata('user');
        }
        
        if ($searchparams) $this->session->set_userdata($searchparams);
        $this->load->library('pagination');

        $totalrows = $this->auditmodel->getNumberOfChanges(serialize($searchparams));
        $firstrecord = (floor($offset/$limit)*$limit)+1;
        $lastrecord = (floor($offset/$limit)*$limit)+$limit;
        if ($lastrecord > $totalrows) $lastrecord = $totalrows;
        $this->data['NumberOfChanges'] = "Records <b>$firstrecord</b> to <b>$lastrecord</b>
                of <b>$totalrows</b>";

        $this->data['pagelinks'] = $this->paginationLinks($totalrows, $limit, $offset);      
        
        $this->data['changes'] = $this->auditmodel->getChanges(serialize($searchparams), $limit, $offset);
        $this->load->view('auditview', $this->data);
    }
    
    private function paginationLinks($totalrows, $limit, $offset) {
        $config = array();
        $config['num_links'] = 10;
        $config['total_rows'] = $totalrows;
        $config['per_page'] = $limit;
        $config['page'] = floor($offset/$limit)+1;
        $config['full_tag_open'] = '<div class="links">';
        $config['full_tag_close'] = '</div>';
        $config['first_link'] = '&laquo; First';
        $config['first_tag_open'] = '<span class="first">';
        $config['first_tag_close'] = '</span>';
        $config['last_link'] = 'Last &raquo;';
        $config['last_tag_open'] = '<span class="last">';
        $config['last_tag_close'] = '</span>';
        $config['next_link'] = '&gt;';
        $config['next_tag_open'] = '<span class="next">';
        $config['next_tag_close'] = '</span>';
        $config['prev_link'] = '&lt;';
        $config['prev_tag_open'] = '<span class="prev">';
        $config['prev_tag_close'] = '</span>';
        $config['cur_tag_open'] = '<span class="current">';
        $config['cur_tag_close'] = '</span>';
        $config['num_tag_open'] = '<span>';
        $config['num_tag_close'] = '</span>';
        
        $url = site_url() . "/audit/index/$limit/";
        $perpage = $limit;
        $numpages = ceil($totalrows/$perpage);
        $currentpage = floor($offset/$perpage)+1;
        
        $numlinks = ($numpages > $config['num_links']) ? $config['num_links'] : $numpages;
        
        $firstpage = 1;
        if ($currentpage-(floor($numlinks/2)) > $firstpage) $firstpage = $currentpage-(floor($numlinks/2));
        if ($numpages-$numlinks+1 < $firstpage) $firstpage = $numpages-$numlinks+1;
        
        $lastpage = $currentpage+(floor($numlinks/2));
        if ($lastpage < $firstpage+$numlinks-1) $lastpage = $firstpage+$numlinks-1;
        if ($numpages < $lastpage) $lastpage = $numpages;
        
        $links = array();
        $links[] = $config['full_tag_open'];

        // first page link
        if ($currentpage != 1)
            $links[] = $config['first_tag_open'] 
                . anchor($url . '1/', $config['first_link'])
                . $config['first_tag_close'];
        else
            $links[] = $config['first_tag_open'] . '<span class="nolink">' . $config['first_link'] . '</span>' . $config['first_tag_close'];
        
        // previous page link
        if ($currentpage-1 != 0) {
            $p = $currentpage-1;
            $links[] = $config['prev_tag_open']
                . anchor($url . $p . '/', $config['prev_link'])
                . $config['prev_tag_close'];
        }
        else 
            $links[] = $config['prev_tag_open'] . '<span class="nolink">' . $config['prev_link'] . '</span>' . $config['prev_tag_close'];
        
        // numbered page links
        for ($i = $firstpage; $i <= $lastpage; $i++) {
            if ($i != $currentpage) {
                $links[] = $config['num_tag_open']
                    . anchor($url . $i . '/', $i)
                    . $config['num_tag_close'];
            }
            else
                $links[] = $config['cur_tag_open'] . '<span class="nolink">' . $i . '</span>' . $config['cur_tag_close'];
        }
        
        // next page link
        if ($currentpage+1 <= $numpages){
            $p = $currentpage+1;
            $links[] = $config['next_tag_open']
                . anchor($url . $p . '/', $config['next_link'])
                . $config['next_tag_close'];
        }
        else
            $links[] = $config['next_tag_open'] . '<span class="nolink">' . $config['next_link'] . '</span>' . $config['next_tag_close'];
        
        // last page link
        if ($currentpage != $numpages) {
            $p = ($numpages*$perpage)-$perpage;
            $links[] = $config['last_tag_open']
                . anchor($url . $numpages . '/', $config['last_link'])
                . $config['last_tag_close'];
        }
        else
            $links[] = $config['last_tag_open'] . '<span class="nolink">' . $config['last_link'] . '</span>' . $config['last_tag_close'];
        $links[] = $config['full_tag_close'];
        
        return implode('', $links);
        
        
    } 
    
    function banner() {
        $banners = get_dir_file_info('./images/banners', TRUE);
        $banners = array_values($banners);
        $count = count($banners);
        $i = rand(0, $count-1);
        return $banners[$i]['name'];
    }
    
    function table_list() {
        $this->getTableList();
        $this->load->view('tablelistview', $this->data);
    }
    
    private function getTableList() {
        $doc = new DOMDocument('1.0', 'UTF-8');
        $doc->load('xml/specify_tableid_listing.xml');
        
        $tables = $doc->getElementsByTagName('table');
        if ($tables->length) {
            foreach ($tables as $table) {
                $id = $table->getAttribute('id');
                $system = ($id > 502) ? 'true' : '';
                $this->data['tables'][] = array(
                    'name' => substr($table->getAttribute('name'), strlen('edu.ku.brc.specify.datamodel.')),
                    'abbreviation' => $table->getAttribute('abbrev'),
                    'id' => $id,
                    'workbench' => $table->getAttribute('workbench'),
                    'searchable' => $table->getAttribute('searchable'),
                    'system' => $system
                );
            }
        }
    }
}
?>
