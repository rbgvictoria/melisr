 <?php require_once('header_1.php'); ?>
<div class="container">
    <div class="row">
        <div class="col-md-12">
            <?php require_once APPPATH . 'views/includes/vcsb_links.php'; ?>

            <h2>VCSB records for which the determination of the MEL voucher has changed</h2>

            <?php if ($records): ?>
            <table class="table table-bordered table-condensed table-responsive">
                <tr>
                    <th>MEL number</th>
                    <th>MEL full name</th>
                    <th>MEL determiner</th>
                    <th>MEL determination date</th>
                    <th>VRS number</th>
                    <th>VRS full name</th>
                    <th>VRS determiner</th>
                    <th>VRS determination date</th>
                </tr>
                <?php foreach ($records as $row): ?>
                <tr>
                    <td><?=$row['melCatalogNumber']?></td>
                    <td><?=$row['melFullName']?></td>
                    <td><?=$row['melDeterminer']?></td>
                    <td><?=$row['melDeterminationDate']?></td>
                    <td><?=$row['vcsbCatalogNumber']?></td>
                    <td><?=$row['vcsbFullName']?></td>
                    <td><?=$row['vcsbDeterminer']?></td>
                    <td><?=$row['vcsbDeterminationDate']?></td>
                </tr>
                <?php endforeach; ?>
            </table>


            <?php else: ?>
            <div class="alert alert-success alert-dismissible" role="alert">
                There are no records with outdated determinations in the VCSB collection.
            </div>
            <?php endif; ?>

        </div> <!-- /.col-md-12 -->
    </div> <!-- /.row -->
</div> <!-- /.container -->
<?php require_once('footer.php'); ?>
