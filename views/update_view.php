<?php require_once('header.php'); ?>

<h2>Update</h2>

<?=form_open('update'); ?>
<?=form_fieldset('IBRA regions'); ?>
<div class="update">Update IBRA regions and subregions 
<?php 
    
    $options = array(
                  'hourly'  => 'last hour',
                  'daily'    => 'last 24 hours'
                );
    echo form_dropdown('interval1', $options);
    echo form_submit('submit1', 'Update');
    
    if (isset($message1)) {
        echo ' <span style="color: red">' . $message1 . '</span>';
    }
?>
</div>
<?=form_fieldset_close(); ?>

<?=form_fieldset('BioCASe tables'); ?>
<div class="update">Update BioCASe tables 
<?php 
    
    $options = array(
                  'hourly'  => 'last hour',
                  'daily'    => 'last 24 hours'
                );
    echo form_dropdown('interval2', $options);
    echo form_submit('submit2', 'Update');
    
    if (isset($message2)) {
        echo ' <span style="color: red">' . $message2 . '</span>';
    }
?>
</div>
<div class="update">Reindex loans 
<?=form_submit('submit3', 'Reindex'); ?>
</div>
<div class="update">Reindex exchange 
<?=form_submit('submit4', 'Reindex'); ?>
</div>
<?=form_fieldset_close(); ?>
<?=form_close(); ?>

<?php require_once('footer.php'); ?>

