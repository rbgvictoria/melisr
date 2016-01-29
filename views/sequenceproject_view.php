<?php require_once 'header.php'; ?>

<h2>DNA sequencing projects</h2>
<?php if ($projects): ?>
<table>
    <thead>
        <tr>
            <th>Project name</th>
            <th>Number of sequences</th>
        </tr>
    </thead>
    <tbody>
        <?php foreach ($projects as $project): ?>
        <tr>
            <td><?=$project['ProjectName']?></td>
            <td class="td-right"><?=$project['cntSequences']?></td>
        </tr>
        <?php endforeach; ?>
    </tbody>
</table>
<?php endif; ?>

<?=form_open();?>
<h3>Add new project</h3>
<p>
    <?=form_label('Specify user', 'specify_user', array('class' => 'required')); ?>
    <?=form_dropdown('specify_user', $specify_user, $this->input->post('specify_user'), 'id="specify_user"'); ?>
</p>
<p>
    <?=form_label('Project name', 'new_project', array('class' => 'required')); ?>
    <?=form_input(array('name' => 'new_project', 'id' => 'new_project')); ?>
    <button type="submit" formaction="new_project">Add</button>
</p>
<?=form_close();?>

<?php require_once 'footer.php'; ?>

