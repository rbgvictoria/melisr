<?php require_once('header.php'); ?>

<h2>MELISR admin</h2>

<p><?=anchor('melisradmin/loggedin', "Who's logged in?")?></p>
<?php if (isset($ActiveLogins)): ?>
<?php if ($ActiveLogins): ?>
<?=form_open('melisradmin/logoff'); ?>
<table>
    <tr><th>&nbsp;</th><th>Username</th><th>Last logged out</th></tr>
    <?php foreach ($ActiveLogins as $login): ?>
    <tr>
        <td><?=form_checkbox('spusers[]', $login['SpecifyUserID'], FALSE)?></td>
        <td><?=$login['Name']?></td>
        <td><?=$login['LastLoggedOut']?></td>
    </tr>

    <?php endforeach; ?>
</table>
    <p><?=form_submit('submit_logoff', 'Log out')?></p>
    <p>&nbsp;</p>
<?=form_close()?>
<?php else: ?>
    <p>Nobody is logged in</p>
    <p>&nbsp;</p>
<?php endif;?>
<?php endif;?>

<p><?=anchor('melisradmin/locks', "Who's got the lock?")?></p>
<?php if (isset($Locks)): ?>
<?php if ($Locks): ?>
<?=form_open('melisradmin/releaselocks'); ?>
<table>
    <tr><th>&nbsp;</th><th>Task</th><th>Specify user</th><th>Locked since</th></tr>
    <?php foreach ($Locks as $lock): ?>
    <tr>
        <td><?=form_checkbox('tasks[]', $lock['TaskSemaphoreID'])?></td>
        <td><?=$lock['TaskName']?></td>
        <td><?=$lock['SpecifyUser']?></td>
        <td><?=$lock['LockedTime']?></td>
    </tr>
    <?php endforeach; ?>
</table>
    <p><?=form_submit('submit_release', 'Release locks')?></p>
    <p>&nbsp;</p>
<?=form_close()?>
<?php else: ?>
    <p>There are no active locks.</p>
<?php endif; ?>
<?php endif;?>


<p><?=anchor('melisradmin/version', "Specify version")?></p>
<?php if (isset($SpVersion) && $SpVersion): ?>
<?=form_open('melisradmin/version');?>
<?php 
    $options = array(
        '6.4.13' => '6.4.13',
        '6.5.03' => '6.5.03',
    );
    echo form_dropdown('version', $options, $SpVersion['AppVersion']);
    echo form_submit('change', 'Change');
?>
<?=form_close()?>
<?php endif;?>
    
<p><?=anchor('melisradmin/biocase', "Biocase tables")?></p>
<?php if (isset($lastUpdated) && $lastUpdated): ?>
<p>Biocase tables last updated: <?=$lastUpdated?></p>
<?=form_open('melisradmin/biocase')?>
<?=form_hidden('lastupdated', $lastUpdated); ?>
<?=form_submit('update', 'Update biocase');?>
<?=form_close()?>

<?php endif; ?>    
<?php require_once('footer.php'); ?>

