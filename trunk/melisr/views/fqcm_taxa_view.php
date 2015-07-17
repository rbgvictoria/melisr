<?php require_once('header.php'); ?>

<h2>Fancy Quality Control Machine – Taxon names</h2>


<?=form_open('fqcm/doqc',array('enctype'=>'multipart/form-data'))?>

<?php
    $data = array(
        'id' => 'startdate',
        'name' => 'startdate',
        'style' => 'width: 100px;',
        'value' => (isset($startdate) && $startdate) ? $startdate : FALSE
    );
    echo form_label('Start date (yyyy-mm-dd):', 'startdate', array('style' => 'width: auto;'));
    echo form_input($data);
    
    echo '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;';
    
?>

<?=form_submit('submit', 'Check')?>&nbsp;&nbsp;
<?=form_close()?>


<?php require_once('footer.php'); ?>
