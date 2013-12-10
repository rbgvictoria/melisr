<?php require_once('header_1.php'); ?>

<h2>Messy loan sorter</h2>
<?php if (isset($message)): ?>
<h3 style="color: red"><?=$message?></h3>
<?php endif; ?>
<p>You can enter barcodes of both MEL and non MEL specimens in the text area below.</p>
<?=form_open('loansorter/sort'); ?>
<div style="margin-top: 20px"><?=form_label('Barcodes: ', 'melnumbers', array('style' => 'width: 200px'));?></div>
<?php
if (!isset($melnumbers)) $melnumbers = '';
$data = array(
              'name'        => 'melnumbers',
              'id'          => 'melnumbers',
              'value'       => $melnumbers,
              'rows'        => '100',
              'cols'        => '80',
              'style'       => 'width:50%; height: 120px'
            );

echo form_textarea($data);

?>
<div style="width: 50%; text-align: right;">
    <?=form_submit('submit', 'Sort');?>
    <?=form_reset('reset', 'Reset');?>
</div>

<?=form_close(); ?>


<?php if (isset($loans) && $loans): ?>
<h2>MEL loans</h2>
<?php foreach ($loans as $key => $loan): ?>
    <?php
        $attributes = array(
            'target' => '_blank',
        );
    ?>
<?=form_open('loanreturn/prepare', $attributes)?>
<h3><?=$loan['LoanNumber']?></h3>
<?php
$data = array(
              'name'        => 'melnumbers',
              'id'          => 'melnumbers_' . $key,
              'value'       => implode("\n", $loan['MelNumber']),
              'rows'        => '100',
              'cols'        => '80',
              'style'       => 'width:50%; height: 120px'
            );

echo form_textarea($data);

?>
<div style="width: 50%; text-align: right;">
    <?=form_submit('loan_return', 'Send to loan return');?>
    <?=form_submit('record_set', 'Send to record set creator');?>
</div>
<?=form_close()?>
<div>&nbsp;</div>
<?php endforeach; ?>
<?php endif; ?>
<?php if (isset($nonmelloans) && $nonmelloans): ?>
<h2>Non-MEL loans</h2>
<?php foreach ($nonmelloans as $key => $loan): ?>
    <?php
        $attributes = array(
            'target' => '_blank',
        );
    ?>
<?=form_open('borrower', $attributes)?>
<?=form_hidden(array('melrefno' => $loan['LoanID']))?>
<h3><?=$loan['LoanNumber']?></h3>
<?php
$data = array(
              'name'        => 'stickybarcodes',
              'id'          => 'melnumbers_' . $key,
              'value'       => implode("\n", $loan['MelNumber']),
              'rows'        => '100',
              'cols'        => '80',
              'style'       => 'width:50%; height: 120px'
            );

echo form_textarea($data);

?>
<div style="width: 50%; text-align: right;">
    <?=form_submit('loan_return', 'Send to non-MEL loan return');?>
</div>
<?=form_close()?>
<div>&nbsp;</div>
<?php endforeach; ?>

<?php endif; ?>



<?php require_once('footer.php'); ?>

