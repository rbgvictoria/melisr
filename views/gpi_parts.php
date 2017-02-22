<?php require_once('header.php'); ?>

<h2>Batch <?=$BatchNo?>: Parts</h2>
<table>
    <tr>
        <th>MEL Number</th>
    </tr>
    <?php foreach($parts as $part): ?>
    <tr>
        <td><?=$part?></td>
    </tr>
    <?php endforeach; ?>
</table>

<?php require_once('footer.php'); ?>

