<?php require_once('header.php'); ?>

<h2>Download XML with GPI data for individual records</h2>
<?=form_open('/gpi/recordsxml')?>
<p>Download XML for the following records:</p>
<div>
<?php
    $data = array(
        'name'        => 'melnos',
        'id'          => 'melnos',
        'value'       => $this->input->post('melnos'),
        'cols'        => '80',
        'rows'        => '10',
    );

    echo form_textarea($data);
    echo '<div>';
    echo form_submit('update', 'Update records\' metadata');
    echo '</div>';
?>
</div>
<p>&nbsp;</p>
<p><b>Output format</b></p>
<div>
<?php
    $data = array(
        'name' => 'format',
        'id' => 'format_biocase',
        'value' => 'biocase',
        'checked' => TRUE,
    );
    echo form_radio($data);
    echo form_label('GPI with BioCASe wrapper', 'format_biocase', array('style' => 'width: 300px;'));
?>
</div>
<div>
<?php
    $data = array(
        'name' => 'format',
        'id' => 'format_jstor',
        'value' => 'jstor',
        'checked' => FALSE,
    );
    echo form_radio($data);
    echo form_label('GPI', 'format_jstor', array('style' => 'width: 300px;'));
?>
</div>
<div>
<?php
    $data = array(
        'name' => 'format',
        'id' => 'format_csv',
        'value' => 'csv',
        'checked' => FALSE,
    );
    echo form_radio($data);
    echo form_label('CSV', 'format_csv', array('style' => 'width: 300px;'));
?>
</div>
<p>&nbsp;</p>
<p><?=form_submit('submit', 'Submit')?></p>
<?=form_close()?>
<?php require_once('footer.php'); ?>
