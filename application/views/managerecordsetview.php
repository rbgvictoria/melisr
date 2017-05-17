<?php require_once('header_1.php'); ?>
<div class="container">
    <div class="row">
        <div class="col-md-12">
            <h2>Record set users</h2>
            <table class="table table-bordered table-condensed table-responsive">
                <thead>
                    <tr>
                        <th>Username</th>
                        <th>Name</th>
                        <th>Number of record sets</th>
                        <th>Delete</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($recordSetUsers as $row): ?>
                    <tr>
                        <td><?=$row['Username']?></td>
                        <td><?=$row['AgentName']?></td>
                        <td><?=$row['NumberOfRecordSets']?></td>
                        <td><?=anchor(site_url() . 'recordset/delete_users_recordsets/' . $row['SpecifyUserID'], 'Delete all <b>' . $row['AgentName'] . '</b>&apos;s record sets');?></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
            
        </div>
    </div>
</div>


<?php require_once('footer.php'); ?>

