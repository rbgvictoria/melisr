<?php
/**
 * @property CI_Loader $load
 * @property CI_Form_validation $form_validation
 * @property CI_Input $input
 * @property CI_Email $email
 * @property CI_DB_active_record $db
 * @property CI_DB_forge $dbforge
 */

class Fqcm extends Controller {
    var $data;

    function __construct() {
        parent::Controller();
        $this->load->helper('form');
        $this->load->helper('file');
        $this->load->helper('url');
        $this->output->enable_profiler(TRUE);
        $this->data['bannerimage'] = $this->banner();
        $this->data['title'] = 'MELISR | FQCM';
        $this->load->model('fqcmmodel');
        $this->load->model('recordsetmodel');
        $this->data['Users'] = $this->fqcmmodel->getUsers();
        $this->data['js'][] = 'jquery.fqcm.js';
    }

    function index() {
        $this->load->view('fqcmview', $this->data);
    }
    
    function doqc() {
        $request = array();
        $request = $this->uri->uri_to_assoc();
        if ($request && !isset($request['user']))
            $request['user'] = FALSE;
        if (!$request && ($this->input->post('submit') || $this->input->post('createrecordset'))){
            $request['startdate'] = $this->input->post('startdate');
            $request['user'] = $this->input->post('user');
            $request['createrecordset'] = $this->input->post('createrecordset');
        }    
        $this->data['request'] = $request;
        
        if ($request['startdate']) {
            $startdate = $request['startdate'];
            if ($startdate < '2011-02-04' ) $startdate = '2011-02-04';
            $this->data['startdate'] = $startdate;
            if ($this->input->post('createrecordset')) {
                $this->createRecordSet();
            }
            if ($request || $this->input->post('createrecordset')) {
                if (!isset($request['fqcr']) || $request['fqcr'] == 'HighCatalogueNumbers')
                    $this->data['HighCatalogueNumbers'] = $this->fqcmmodel->HighCatalogueNumbers($startdate, FALSE, $request['user']);
                if (!isset($request['fqcr']) || $request['fqcr'] == 'MissingPreparation')
                    $this->data['MissingPreparation'] = $this->fqcmmodel->missingPreparation($startdate, FALSE, $request['user']);
                if (!isset($request['fqcr']) || $request['fqcr'] == 'MissingAltitudeUnit')
                    $this->data['MissingAltitudeUnit'] = $this->fqcmmodel->missingAltitudeUnit($startdate, FALSE, $request['user']);
                if (!isset($request['fqcr']) || $request['fqcr'] == 'MissingLocality')
                    $this->data['MissingLocality'] = $this->fqcmmodel->missingLocality($startdate, FALSE, $request['user']);
                if (!isset($request['fqcr']) || $request['fqcr'] == 'MissingSourceOrPrecision')
                    $this->data['MissingSourceOrPrecision'] = $this->fqcmmodel->missingSourceOrPrecision($startdate, FALSE, $request['user']);
                if (!isset($request['fqcr']) || $request['fqcr'] == 'MissingDetermination')
                    $this->data['MissingDetermination'] = $this->fqcmmodel->missingDetermination($startdate, FALSE, $request['user']);
                if (!isset($request['fqcr']) || $request['fqcr'] == 'TypeMismatch')
                    $this->data['TypeMismatch'] = $this->fqcmmodel->typeMismatch($startdate, FALSE, $request['user']);
                if (!isset($request['fqcr']) || $request['fqcr'] == 'MissingTaxonName')
                    $this->data['MissingTaxonName'] = $this->fqcmmodel->missingTaxonName($startdate, FALSE, $request['user']);
                if (!isset($request['fqcr']) || $request['fqcr'] == 'TypeDetIsCurrent')
                    $this->data['TypeDetIsCurrent'] = $this->fqcmmodel->typeDetIsCurrent($startdate, FALSE, $request['user']);
                if (!isset($request['fqcr']) || $request['fqcr'] == 'TypeDetOverriddenByIndet')
                    $this->data['TypeDetOverriddenByIndet'] = $this->fqcmmodel->typeDetOverriddenByIndet($startdate, FALSE, $request['user']);
                if (!isset($request['fqcr']) || $request['fqcr'] == 'DetDateEarlierThanCollDate')
                    $this->data['DetDateEarlierThanCollDate'] = $this->fqcmmodel->detDateEarlierThanCollDate($startdate, FALSE, $request['user']);
                if (!isset($request['fqcr']) || $request['fqcr'] == 'MissingProtologue')
                    $this->data['MissingProtologue'] = $this->fqcmmodel->missingProtologue($startdate, FALSE, $request['user']);
                if (!isset($request['fqcr']) || $request['fqcr'] == 'MissingPrimaryCollectors')
                    $this->data['MissingPrimaryCollectors'] = $this->fqcmmodel->missingPrimaryCollectors($startdate, FALSE, $request['user']);
                if (!isset($request['fqcr']) || $request['fqcr'] == 'MissingCollectors')
                    $this->data['MissingCollectors'] = $this->fqcmmodel->missingCollectors($startdate, FALSE, $request['user']);
                if (!isset($request['fqcr']) || $request['fqcr'] == 'DuplicateHerbariaInWrongPreparation')
                    $this->data['DuplicateHerbariaInWrongPreparation'] = $this->fqcmmodel->duplicateHerbariaInWrongPreparation($startdate, FALSE, $request['user']);
                if (!isset($request['fqcr']) || $request['fqcr'] == 'DuplicateCountMismatch')
                    $this->data['DuplicateCountMismatch'] = $this->fqcmmodel->DuplicateCountMismatch($startdate, FALSE, $request['user']);
                if (!isset($request['fqcr']) || $request['fqcr'] == 'SomethingInNumberThatShouldntBeThere')
                    $this->data['SomethingInNumberThatShouldntBeThere'] = $this->fqcmmodel->somethingInNumberThatShouldntBeThere($startdate, FALSE, $request['user']);
                if (!isset($request['fqcr']) || $request['fqcr'] == 'SomethingMissingFromNumberField')
                    $this->data['SomethingMissingFromNumberField'] = $this->fqcmmodel->somethingMissingFromNumberField($startdate, FALSE, $request['user']);
                if (!isset($request['fqcr']) || $request['fqcr'] == 'TooEarlyForGPS')
                    $this->data['TooEarlyForGPS'] = $this->fqcmmodel->tooEarlyForGPS($startdate, FALSE, $request['user']);
                if (!isset($request['fqcr']) || $request['fqcr'] == 'GroupAgentsWithoutIndividuals')
                    $this->data['GroupAgentsWithoutIndividuals'] = $this->fqcmmodel->groupAgentsWithoutIndividuals($startdate, FALSE, $request['user']);
                if (!isset($request['fqcr']) || $request['fqcr'] == 'PartMissingFromMultisheetMessage')
                    $this->data['PartMissingFromMultisheetMessage'] = $this->fqcmmodel->partMissingFromMultisheetMessage($startdate, FALSE, $request['user']);
                if (!isset($request['fqcr']) || $request['fqcr'] == 'NewSubgenus')
                    $this->data['NewSubgenus'] = $this->fqcmmodel->newSubgenus($startdate, FALSE, $request['user']);
                if (!isset($request['fqcr']) || $request['fqcr'] == 'MissingAuthor')
                    $this->data['MissingAuthor'] = $this->fqcmmodel->missingAuthor($startdate, FALSE, $request['user']);
                if (!isset($request['fqcr']) || $request['fqcr'] == 'EndDateWithNoStartDate')
                    $this->data['EndDateWithNoStartDate'] = $this->fqcmmodel->endDateWithNoStartDate($startdate, FALSE, $request['user']);
                if (!isset($request['fqcr']) || $request['fqcr'] == 'AgentsWithNoLastName')
                    $this->data['AgentsWithNoLastName'] = $this->fqcmmodel->agentsWithNoLastName($startdate, FALSE, $request['user']);
                if (!isset($request['fqcr']) || $request['fqcr'] == 'DodgyPart')
                    $this->data['DodgyPart'] = $this->fqcmmodel->dodgyPart($startdate, FALSE, $request['user']);
                if (!isset($request['fqcr']) || $request['fqcr'] == 'PossiblyDodgyPart')
                    $this->data['PossiblyDodgyPart'] = $this->fqcmmodel->possiblyDodgyPart($startdate, FALSE, $request['user']);
                if (!isset($request['fqcr']) || $request['fqcr'] == 'AlternativeNameInCurrentDetermination')
                    $this->data['AlternativeNameInCurrentDetermination'] = $this->fqcmmodel->alternativeNameInCurrentDetermination($startdate, FALSE, $request['user']);
                if (!isset($request['fqcr']) || $request['fqcr'] == 'PrimaryCollectorNotFirst')
                    $this->data['PrimaryCollectorNotFirst'] = $this->fqcmmodel->primaryCollectorNotFirst($startdate, FALSE, $request['user']);
                if (!isset($request['fqcr']) || $request['fqcr'] == 'JarSizeMissing')
                    $this->data['JarSizeMissing'] = $this->fqcmmodel->jarSizeMissing($startdate, FALSE, $request['user']);
                if (!isset($request['fqcr']) || $request['fqcr'] == 'InappropriateQuantityInPreparation')
                    $this->data['InappropriateQuantityInPreparation'] = $this->fqcmmodel->inappropriateQuantityInPreparation($startdate, FALSE, $request['user']);
                if (!isset($request['fqcr']) || $request['fqcr'] == 'MissingDatum')
                    $this->data['MissingDatum'] = $this->fqcmmodel->missingDatum($startdate, FALSE, $request['user']);
                if (!isset($request['fqcr']) || $request['fqcr'] == 'StoredUnderMultipleNames')
                    $this->data['StoredUnderMultipleNames'] = $this->fqcmmodel->storedUnderMultipleNames($startdate, FALSE, $request['user']);
                if (!isset($request['fqcr']) || $request['fqcr'] == 'TooManyPrimaryPreparations')
                    $this->data['TooManyPrimaryPreparations'] = $this->fqcmmodel->tooManyPrimaryPreparations($startdate, FALSE, $request['user']);
                if (!isset($request['fqcr']) || $request['fqcr'] == 'NoPrimaryPreparations')
                    $this->data['NoPrimaryPreparations'] = $this->fqcmmodel->noPrimaryPreparations($startdate, FALSE, $request['user']);
                if (!isset($request['fqcr']) || $request['fqcr'] == 'NoCollectingDate')
                    $this->data['NoCollectingDate'] = $this->fqcmmodel->noCollectingDate($startdate, FALSE, $request['user']);
                if (!isset($request['fqcr']) || $request['fqcr'] == 'MissingGeography')
                    $this->data['MissingGeography'] = $this->fqcmmodel->missingGeography($startdate, FALSE, $request['user']);
 //               if (!isset($request['fqcr']) || $request['fqcr'] == 'PartlyAtomisedHabitat')
 //                   $this->data['PartlyAtomisedHabitat'] = $this->fqcmmodel->partlyAtomisedHabitat($startdate, FALSE, $request['user']);
                if (!isset($request['fqcr']) || $request['fqcr'] == 'MissingCultSource')
                    $this->data['MissingCultSource'] = $this->fqcmmodel->missingCultSource($startdate, FALSE, $request['user']);                
                if (!isset($request['fqcr']) || $request['fqcr'] == 'MissingIntroSource')
                    $this->data['MissingIntroSource'] = $this->fqcmmodel->missingIntroSource($startdate, FALSE, $request['user']);    
                }
            elseif ($this->input->post('submit_localities')) {
                $this->data['SharedLocalities'] = $this->fqcmmodel->sharedLocalities($startdate, FALSE, $this->input->post('user'));               
            }
            
            $this->load->view('fqcmview', $this->data);
        }
        else {
            $this->index();
        }
    }

    function banner() {
        $banners = get_dir_file_info('./images/banners', TRUE);
        $banners = array_values($banners);
        $count = count($banners);
        $i = rand(0, $count-1);
        return $banners[$i]['name'];
    }
    
    function createRecordSet() {
        $this->data['specifyusers'] = $this->recordsetmodel->getSpecifyUsers();
        if (!$this->input->post('user') || !$this->input->post('recordset') || !$this->input->post('recsetitems')) {
            $this->data['message'] = 'Not all fields have been filled in<br/>Record set cannot be created';
            $this->load->view('message', $this->data);
        } else {
            if ($this->recordsetmodel->findRecordSetName($this->input->post('user'), $this->input->post('recordset')) != FALSE) {
                $this->data['message'] = 'A record set of this name already exists';
                $this->load->view('message', $this->data);
            } else {
                $agentid = trim($this->input->post('user'));
                $specifyuser = $this->recordsetmodel->findSpecifyUserID($agentid);
                $recordsetname = trim($this->input->post('recordset'));
                
                $this->recordsetmodel->createRecordSet($specifyuser, $recordsetname, $agentid);
                $recordsetid = $this->recordsetmodel->findRecordSetName($specifyuser, $recordsetname);
                $this->fqcmmodel->createRecordSetItems($recordsetid, $this->input->post('recsetitems'));
            }
        }
        
    }

}
?>
