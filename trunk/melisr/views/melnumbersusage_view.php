<?php require_once('header.php'); ?>

<h2>What happened with my MEL numbers?</h2>
<table>
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
<?php require_once('footer.php'); ?>

