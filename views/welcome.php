<?php require_once('header.php'); ?>

<h2>MELISR labels</h2>

<?=form_open('melisrlabels/label', array('target' => '_blank')); ?>
    <?=form_fieldset('Records', array('id' => 'fieldset_records'));?>
    <div>
    <?=form_label('Select record set:', 'recordset', array('style' => 'width: 130px; text-align: left')); ?>
    <select name="recordset" id="recordset">
        <option value="">(select recordset)</option>
        <?php foreach($recordsets as $set): ?>
            <option value="<?=$set['RecordSetID'] ?>"><?=$set['Name'] ?> [<?=$set['SpecifyUser'] ?>]</option>
        <?php endforeach; ?>
    </select>
    <p style="margin:0px;padding:0px;">or</p>
    <?=form_label('MEL number (start):', 'melno_start', array('style' => 'width: 130px; text-align: left')); ?>
    <?=form_input(array(
        'name'        => 'melno_start',
        'id'          => 'melno_start',
        'value'       => '',
        'maxlength'   => '7',
        'size'        => '8'
    )); ?>
    <?=form_label('count:', 'melno_count', array('style' => 'width: 80px; text-align: right')); ?>
    <?=form_input(array(
        'name'        => 'melno_count',
        'id'          => 'melno_count',
        'value'       => '1',
        'maxlength'   => '3',
        'size'        => '5'
    )); ?>
    <span style="display:inline-block;margin-left:15px;margin-right:15px;">or</span>
    <?=form_label('MEL number (end):', 'melno_end', array('style' => 'width: 120px; text-align: left')); ?>
    <?=form_input(array(
        'name'        => 'melno_end',
        'id'          => 'melno_end',
        'value'       => '',
        'maxlength'   => '7',
        'size'        => '8'
    )); ?>
    </div>
    <?=form_fieldset_close()?>
    <?=form_fieldset('Type of label', array('id' => 'fieldset_type'));?>
    <?=form_radio(array(
        'name' => 'labeltype',
        'id' => 'lt3',
        'value' => '3',
        'checked' => true
    )); ?>&nbsp;<?=form_label('Standard herbarium sheet label, 4 per page', 'lt3', array('style' => 'width: 600px')); ?>
    <?=form_checkbox(array(
    'name'        => 'part',
    'id'          => 'part',
    'value'       => 'part',
    'checked'     => false,
    'style'       => 'margin-left:20px'
    )); ?>&nbsp;<?=form_label('print labels for parts', 'part', array('style' => 'width: 140px')); ?>
    <br/>
    <?=form_radio(array(
        'name' => 'labeltype',
        'id' => 'lt2',
        'value' => '2',
        'checked' => false
    )); ?>&nbsp;<?=form_label('Long label, 2 per page', 'lt2', array('style' => 'width: 200px')); ?>
    <br/>
    <?=form_radio(array(
        'name' => 'labeltype',
        'id' => 'lt1',
        'value' => '1',
        'checked' => FALSE
    )); ?>&nbsp;<?=form_label('Fungi and lichen packet label, 4 per page', 'lt1', array('style' => 'width: 620px')); ?>
    <br />
    <?=form_radio(array(
        'name' => 'labeltype',
        'id' => 'lt18',
        'value' => '5',
        'checked' => false
    )); ?>&nbsp;<?=form_label('Fungi and lichen packet label, 2 per page', 'lt18', array('style' => 'width: 620px')); ?>
    <br/>
    <?=form_radio(array(
        'name' => 'labeltype',
        'id' => 'lt4',
        'value' => '4',
        'checked' => false
    )); ?>&nbsp;<?=form_label('Bryophyte label, 3 per page', 'lt4', array('style' => 'width: 200px')); ?>
    <br/>
    <?=form_radio(array(
        'name' => 'labeltype',
        'id' => 'lt5',
        'value' => '5',
        'checked' => false
    )); ?>&nbsp;<?=form_label('Bryophyte label, 2 per page', 'lt5', array('style' => 'width: 200px')); ?>
    <br/>
    <?=form_radio(array(
        'name' => 'labeltype',
        'id' => 'lt6',
        'value' => '6',
        'checked' => false
    )); ?>&nbsp;<?=form_label('Duplicate label, 4 per page', 'lt6', array('style' => 'width: 200px')); ?>
    <br/>
    <?=form_radio(array(
        'name' => 'labeltype',
        'id' => 'lt7',
        'value' => '7',
        'checked' => false
    )); ?>&nbsp;<?=form_label('Duplicate label, 2 per page', 'lt7', array('style' => 'width: 200px')); ?>
    <br/>
    <?=form_radio(array(
        'name' => 'labeltype',
        'id' => 'lt13',
        'value' => '13',
        'checked' => false
    )); ?>&nbsp;<?=form_label('Duplicate label for seed collections, 4 per page', 'lt13', array('style' => 'width: 300px')); ?>
    <br/>
    <?=form_radio(array(
        'name' => 'labeltype',
        'id' => 'lt14',
        'value' => '14',
        'checked' => false
    )); ?>&nbsp;<?=form_label('Duplicate label for seed collections, 2 per page', 'lt14', array('style' => 'width: 300px')); ?>
    <br/>
    <?=form_radio(array(
        'name' => 'labeltype',
        'id' => 'lt8',
        'value' => '8',
        'checked' => false
    )); ?>&nbsp;<?=form_label('Spirit jar label', 'lt8', array('style' => 'width: 200px')); ?>
    <br/>
    <?=form_radio(array(
        'name' => 'labeltype',
        'id' => 'lt12',
        'value' => '12',
        'checked' => false
    )); ?>&nbsp;<?=form_label('Spirit collection card', 'lt12', array('style' => 'width: 200px')); ?>
    <br/>
    <?=form_radio(array(
        'name' => 'labeltype',
        'id' => 'lt17',
        'value' => '17',
        'checked' => false
    )); ?>&nbsp;<?=form_label('Carpological collection card', 'lt17', array('style' => 'width: 200px')); ?>
    <br/>
    <?=form_radio(array(
        'name' => 'labeltype',
        'id' => 'lt18',
        'value' => '18',
        'checked' => false
    )); ?>&nbsp;<?=form_label('Silica gel sample label', 'lt18', array('style' => 'width: 200px')); ?>
    <br/>
    <?=form_radio(array(
        'name' => 'labeltype',
        'id' => 'lt9',
        'value' => '9',
        'checked' => false
    )); ?>&nbsp;<?=form_label('Multisheet label', 'lt9', array('style' => 'width: 200px')); ?>
    <br/>
    <?=form_radio(array(
        'name' => 'labeltype',
        'id' => 'lt10',
        'value' => '10',
        'checked' => false
    )); ?>&nbsp;<?=form_label('Type folder label', 'lt10', array('style' => 'width: 200px')); ?>
    <br/>
    <?=form_radio(array(
        'name' => 'labeltype',
        'id' => 'lt11',
        'value' => '11',
        'checked' => false
    )); ?>&nbsp;<?=form_label('Barcode label', 'lt11', array('style' => 'width: 240px')); ?>
    <?=form_fieldset_close(); ?>
    <br />
    <?=form_fieldset('Annotation slips');?>
    <?=form_radio(array(
        'name' => 'labeltype',
        'id' => 'lt15',
        'value' => '15',
        'checked' => false,
        'style' => ''
    )); ?>&nbsp;<?=form_label('Sticky labels, 30 per sheet', 'lt15', 
            array('style' => 'width: 240px;')); ?>

    <?=form_fieldset_close();?>

    <br/>
    <?=form_label('Start printing at label:', 'start', array('style' => 'width: 150px')); ?>
    <?=form_input(array(
        'name'        => 'start',
        'id'          => 'start',
        'value'       => '1',
        'maxlength'   => '2',
        'size'        => '5'
    )); ?>

    <br/><br/>
    <input type="submit" name="submit" value="Submit"/>
<?=form_close(); ?>

<?php require_once('footer.php'); ?>

