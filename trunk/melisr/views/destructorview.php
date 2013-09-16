<?php require_once('header.php'); ?>

<h2>Batch destructor</h2>
<?=form_open('destructor')?>
<p>
    <?=form_label('Select record set:', 'recordset', array('style' => 'width: 130px; text-align: left')); ?>
    <select name="recordset" id="recordset">
        <option value="">(select recordset)</option>
        <?php foreach($recordsets as $set): ?>
            <?php
                if ($this->input->post('recordset') && $this->input->post('recordset') == $set['RecordSetID'])
                    $selected = ' selected="selected"';
                else
                    $selected = '';
            ?>
            <option value="<?=$set['RecordSetID']?>"<?=$selected?>><?=$set['Name'] ?> [<?=$set['SpecifyUser'] ?>]</option>
        <?php endforeach; ?>
    </select>
    <?=form_checkbox(array('name'=>'override', 'id'=>'override', 'value'=>'override')); ?>
    <?=form_label('Override', 'override')?>
</p>
<p>
    <?=form_label('Specify user:', 'agent', array('style' => 'width: 130px; text-align: left')); ?>
    <select name="agent" id="agent">
        <option value="0">(select username)</option>
        <?php foreach ($agents as $user): ?>
            <?php if($this->input->post('agent') && $user['id']==$this->input->post('agent')) $selected = ' selected="selected"';
                else $selected = '';
            ?>
            <option value="<?=$user['id']?>"<?=$selected?>><?=$user['username']?></option>
        <?php endforeach; ?>
    </select>
</p>

<p>
    <?=form_label('Destructive sampling:', 'destructive_sampling', array('style' => 'width: 130px; text-align: left')); ?>
    <?=form_input(array(
        'name'        => 'destructive_sampling',
        'id'          => 'destructive_sampling',
        'value'       => $this->input->post('destructive_sampling'),
        'style'       => 'width: 400px;'
    )); ?>
    
    <?=form_submit('submit_destr', 'Destruct'); ?>
</p>

<?=form_close()?>


<?php if (isset($already_destructed) && $already_destructed): ?>
<p><b>The following records already have destructive sampling notes.</b> Select 'override' to replace the existing notes.</p>
<ul style="color:#ff0000">
<?php foreach ($already_destructed as $row): ?>
    <li><?=$row?></li>
<?php endforeach; ?>
</ul>
<?php endif; ?>
<?php require_once('footer.php'); ?>

