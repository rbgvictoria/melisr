<?php require_once('header.php'); ?>

<h2>Upload attachment metadata</h2>
<?=form_open_multipart('imagemetadata/upload')?>

<p>
<?=form_label('User name:', 'user', array('style' => 'width: auto')); ?>
<select id="user" name="user">
    <option value="">(select a user)</option>
    <?php foreach($Users as $user): ?>
    <?php
        $selected = false;
        if ($this->input->post('user') && $user['AgentID'] == $this->input->post('user'))
            $selected = ' selected="selected"'
    ?>
    <option value="<?=$user['AgentID']?>"<?=$selected?>><?=$user['Name']?></option>
    <?php endforeach;?>
</select>
</p>
    <?=form_label('Load file:', 'image_metadata_upload'); ?>
    <?=form_upload('image_metadata_upload', ''); ?>
</p>
<p><?=form_submit('submit', 'Submit')?></p>

<?=form_close(); ?>

<?php if(isset($message)): ?>
<div class="message"><?=$message?></div>
<?php endif; ?>

<?php if(isset($recordset)): ?>
<div class="success">The file <b><?=$filename?></b> has been uploaded. A record set &apos;<b><?=$recordset?></b>&apos; has been created. 
    You have to close and open Specify in order to see it.</div>
<?php endif; ?>

<?php require_once('footer.php'); ?>
