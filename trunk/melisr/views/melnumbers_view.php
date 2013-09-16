<?php require_once('header.php'); ?>

<h2>Assigned MEL numbers</h2>
<table>
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
<?php require_once('footer.php'); ?>

