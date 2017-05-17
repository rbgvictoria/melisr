<?php require_once('header.php'); ?>

<div class="container">
    <div class="row">
        <div class="col-md-6">
            <h2>Assigned MEL numbers</h2>
        </div>
        <div class="col-md-6 text-right">
            <a href="<?=site_url()?>numbers" class="btn btn-primary btn-sm">Back to <b>Numbers</b></a>
        </div>
        <div class="col-md-12">
            <table class="table table-bordered table-condensed">
                <tr>
                    <th>Date</th>
                    <th>Used by</th>
                    <th>Start number</th>
                    <th>End number</th>
                    <th>&nbsp;</th>
                </tr>
                <?php foreach ($melnumbers as $row): ?>
                <tr>
                    <td><?=$row['Date']?></td>
                    <td><?=$row['UsedBy']?></td>
                    <td><?=$row['StartNumber']?></td>
                    <td><?=$row['EndNumber']?></td>
                    <td><?=anchor('numbers/melnumbersusage/' . $row['MelNumbersID'], 'usage');?></td>
                </tr>
                <?php endforeach; ?>
            </table>
        </div> <!-- /.col- -->
    </div> <!-- /.row -->
</div> <!-- /.container -->
<?php require_once('footer.php'); ?>

