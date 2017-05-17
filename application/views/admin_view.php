<?php require_once 'header_1.php'; ?>

<div class="container">
    <div class="row">
        <div class="col-md-12">

            <h2>MELISR admin</h2>

            <p><?=anchor('admin/loggedin', "Who's logged in?")?></p>
            <?php if (isset($ActiveLogins)): ?>
            <?php if ($ActiveLogins): ?>
            <?=form_open('melisradmin/logoff'); ?>
            <form action="<?=site_url()?>admin/logoff" method="post">
                <table class="table table-bordered table-condensed">
                    <tr><th>&nbsp;</th><th>Username</th><th>Last logged out</th></tr>
                    <?php foreach ($ActiveLogins as $login): ?>
                    <tr>
                        <td><?=form_checkbox('spusers[]', $login['SpecifyUserID'], FALSE)?></td>
                        <td><?=$login['Name']?></td>
                        <td><?=$login['LastLoggedOut']?></td>
                    </tr>

                    <?php endforeach; ?>
                </table>
                <p>
                    <button type="submit" name="submit_logoff" value="1"
                            class="btn btn-primary">Log out</button>
                </p>
            </form>
            <?php else: ?>
                <p>Nobody is logged in</p>
            <?php endif;?>
            <?php endif;?>

            <p><?=anchor('admin/locks', "Who's got the lock?")?></p>
            <?php if (isset($Locks)): ?>
            <?php if ($Locks): ?>
            <?=form_open('melisradmin/releaselocks'); ?>
            <form action="<?=site_url()?>admin/releaslocks">
            <table class="table table-bordered table-condensed">
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
            <p>
                <button type="submit" name="submit_release" value="1"
                        class="btn btn-primary">
                    Release locks
                </button>
            </form>
            <?php else: ?>
                <p>There are no active locks.</p>
            <?php endif; ?>
            <?php endif;?>


            <p><?=anchor('admin/version', "Specify version")?></p>
            <?php if (isset($SpVersion) && $SpVersion): ?>
            <form action="<?=site_url()?>admin/version" method="post"
                  class="form-horizontal">
                <div class="form-group">
                    <div class="col-md-2">
                        <?php 
                            $options = [
                                '6.4.13' => '6.4.13',
                                '6.5.03' => '6.5.03',
                                '6.6.00' => '6.6.00',
                                '6.6.02' => '6.6.02',
                                '6.6.05' => '6.6.05'
                            ];
                        ?>
                        <?=form_dropdown('version', $options, $SpVersion['AppVersion'], 
                                'class="form-control"')?>
                    </div>
                    <div class="col-md-10">
                        <button type="submit" name="change" value="1" class="btn btn-primary">
                            Change
                        </button>
                    </div>
                </div>
            </form>
            <?php endif;?>

            <p><?=anchor('admin/biocase', "Biocase tables")?></p>
            <?php if (isset($lastUpdated) && $lastUpdated): ?>
            <p>Biocase tables last updated: <?=$lastUpdated?></p>
            <form action="<?=site_url()?>admin/biocase" method="post">
                <input type="hidden" name="biocase" value="<?=$lastUpdated?>" />
                <button type="submit" name="update" value="1" 
                        class="btn btn-primary">
                    Update BioCASe
                </button>
            </form>

            <?php endif; ?>

        </div> <!-- /.col-md-12 -->
    </div> <!-- /.row -->
</div> <!-- /.container -->

<?php require_once('footer.php'); ?>

