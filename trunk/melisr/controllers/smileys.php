<?php

class Smileys extends Controller {

	function __construct()
	{
		parent::__construct();
	}

	function index()
	{
		$this->load->helper('url');
		$this->load->helper('smiley');
		$this->load->library('table');

		$image_array = get_clickable_smileys(base_url() . 'images/smileys/', 'comments');

		$col_array = $this->table->make_columns($image_array, 8);

		$data['smiley_table'] = $this->table->generate($col_array);

		$this->load->view('smiley_view', $data);
	}

}
?>
