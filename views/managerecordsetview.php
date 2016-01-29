<?php require_once('header.php'); ?>

<h2>Record set users</h2>

<table>
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
            <td><?=anchor(site_url() . '/recordset/delete_users_recordsets/' . $row['SpecifyUserID'], 'delete');?></td>
        </tr>
        <?php endforeach; ?>
    </tbody>
</table>



<?php require_once('footer.php'); ?>

