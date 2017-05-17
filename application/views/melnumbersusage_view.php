<?php require_once('header.php'); ?>

<div class="container">
    <div class="row">
        <div class="col-md-8">
            <h2>What happened with my MEL numbers?</h2>
        </div>
        <div class="col-md-4 text-right">
            <a href="<?=site_url()?>numbers/melnumbers" class="btn btn-primary btn-sm">Back to <b>Assigned MEL numbers</b></a>
        </div>
        
        <div class="col-md-12">
            
            <table class="table table-bordered table-condensed">
                <tr>
                    <th>Assigned number</th>
                    <th>Catalogue number</th>
                    <th>Created</th>
                    <th>Created by</th>
                </tr>
                <?php foreach ($usage as $row): ?>
                <tr>
                    <td><?=$row['AssignedNumber']?></td>
                    <td><?=$row['CatalogNumber']?></td>
                    <td><?=$row['TimestampCreated']?></td>
                    <td><?=$row['CreatedBy']?></td>
                </tr>
                <?php endforeach; ?>
            </table>
        </div> <!-- /.col- -->
    </div> <!-- /.row -->
</div> <!-- /.container -->
<?php require_once('footer.php'); ?>

