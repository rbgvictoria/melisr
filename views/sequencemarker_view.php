<?php require_once 'header.php'; ?>

<h2>Markers</h2>
<?php if ($markers): ?>
<table>
    <thead>
        <tr>
            <th>Marker</th>
            <th>In pick list</th>
            <th>Number of sequences</th>
        </tr>
    </thead>
    <tbody>
        <?php foreach ($markers as $marker): ?>
        <tr>
            <td><?=$marker['targetMarker']?></td>
            <td class="td-center"><?=($marker['isInPickList']) ? '<i class="fa fa-check green"></i>':'<i class="fa fa-remove red"></i>';?></td>
            <td class="td-right"><?=$marker['cntSequences']?></td>
        </tr>
        <?php endforeach; ?>
    </tbody>
</table>
<?php endif; ?>

<?=form_open();?>
<h3>Add new marker</h3>
<p>
    <?=form_label('Specify user', 'specify_user', array('class' => 'required')); ?>
    <?=form_dropdown('specify_user', $specify_user, $this->input->post('specify_user'), 'id="specify_user"'); ?>
</p>
<p>
    <?=form_label('Marker', 'new_marker', array('class' => 'required')); ?>
    <?=form_input(array('name' => 'new_marker', 'id' => 'new_marker')); ?>
    <button type="submit" formaction="new_marker">Add</button>
</p>
<?=form_close();?>

<?php require_once 'footer.php'; ?>

