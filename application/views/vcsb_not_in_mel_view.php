 <?php require_once('header_1.php'); ?>
<div class="container">
    <div class="row">
        <div class="col-md-12">
            <?php require_once APPPATH . 'views/includes/vcsb_links.php'; ?>

            <h2>VCSB records no longer in MEL collection</h2>

            <?php if ($records): ?>
            <form action="" method="post" class="form-horizontal">
                <table class="table table-condensed table-bordered table-responsive">
                    <tr>
                        <th>VRS number</th>
                        <th>MEL number</th>
                        <th>Perp</th>
                        <th>Created</th>
                        <th>Delete?</th>
                    </tr>
                    <?php foreach ($records as $row): ?>
                    <tr>
                        <td><?=$row['VRSNumber']?></td>
                        <td><?=$row['MELNumber']?></td>
                        <td><?=$row['Perp']?></td>
                        <td><?=$row['TimestampCreated']?></td>
                        <td><?=form_checkbox(array('name' => 'colobj[]', 'value' => $row['CollectionObjectID']))?></td>
                    </tr>
                    <?php endforeach; ?>
                </table>
                
                <p>
                    <input type="submit" name="delete" value="Delete"
                           class="btn btn-primary" />
                </p>

            </form>

            <?php else: ?>
            <div class="alert alert-success alert-dismissible" role="alert">
                There are no records without MEL vouchers in the VCSB collection.
            </div>
            <?php endif; ?>

        </div> <!-- /.col-md-12 -->
    </div> <!-- /.row -->
</div> <!-- /.container -->

<?php require_once('footer.php'); ?>
