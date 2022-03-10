<?php require_once('header_1.php'); ?>

<div class="container">
    <div class="row">
        <div class="col-md-12">
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
        </div>
    </div>
</div>
<?php require_once('footer.php'); ?>

