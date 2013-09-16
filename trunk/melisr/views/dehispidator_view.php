<?php require_once('header.php'); ?>

<h2>Dehispidator</h2>
<h3>Convert HISPID3 to CSV</h3>
<?=form_open_multipart(site_url() . '/dehispidator/hispid3_convert'); ?>
    <?php
        echo form_label('Load HISPID file', 'upload',
                array('style' => 'position: relative; top: -4px; width: 150px'));
    
        $inputdata = array(
              'name'        => 'upload',
              'id'          => 'upload',
        );
        echo form_upload($inputdata);
        echo '&nbsp;&nbsp;';
        
        echo form_submit('submit', 'Load');
        
        echo '<br/>';
        $data = array(
            'name'        => 'cleanup',
            'id'          => 'cleanup',
            'value'       => 'cleanup',
            'checked'     => 'checked',
            );
        echo form_checkbox($data);
        echo form_label('Make it sing', 'cleanup', array('style' => 'width: 140px;'));
        
        $options = array(
            '' => '',
            'allfields.csv'  => 'All fields (HISPID + extras)',
            'hispidonly.csv'    => 'Just the HISPID fields thanks',
            'specifywb.csv'   => 'Specify WorkBench fields',
            'alison.csv' => "Alison's custom output",
        );
        echo form_label('Output fields:', 'outputfields', array('style' => 'width: 100px;'));
        echo form_dropdown('outputfields', $options, 'allfields.csv');
        
    ?>
<p><sup>*</sup>Please make sure name of uploaded file has an extension</p>

<?=form_close(); ?>

<h3>Convert HISPID5 to CSV</h3>
<?=form_open_multipart(site_url() . '/dehispidator/hispid5_convert'); ?>
    <?php
        echo form_label('Load HISPID file', 'upload2',
            array('style' => 'position: relative; top: -4px; width: 150px'));
    
        $inputdata = array(
              'name'        => 'upload2',
              'id'          => 'upload2',
        );
        echo form_upload($inputdata);
        echo '&nbsp;&nbsp;';
        echo form_submit('submit', 'Load');
    ?>
    <br/>
<div style="margin-top: 5px;"><?=form_checkbox(array('name'=>'friendlycolumnnames', 'id'=>'friendlycolumnnames', 'value'=>1, 'checked'=>'checked'));?>
<?=form_label('Use friendly column names', 'friendlycolumnnames', array('style'=>'width: 200px'));?>
<?=form_checkbox(array('name'=>'ad', 'id'=>'ad', 'value'=>'ad'));?><?=form_label('AD data', 'ad');?>
</div>
<?=form_close(); ?>

<?php require_once('footer.php'); ?>

