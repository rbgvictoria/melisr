<?php require_once('views/header.php'); ?>

<h2>Gifts</h2>

<?=form_open('gift'); ?>
<select id="year" name="year">
    <option value="">(select year)</option>
    <?php foreach ($years as $year): ?>
    <?php
        if ($year['Year'] == $this->input->post('year'))
            $selected = ' selected="selected"';
        else
            $selected = FALSE;
    ?>
    <option value="<?=$year['Year']?>"<?=$selected?>><?=$year['Year']?></option>
    <?php endforeach; ?>
</select>
<select name="institution" style="width: 200px;">
    <option value="">(select institution)</option>
    <?php foreach ($institutions as $institution): ?>
    <?php
        if ($institution->AgentID == $this->input->post('institution'))
            $selected = ' selected="selected"';
        else
            $selected = FALSE;
    ?>
    <option value="<?=$institution->AgentID?>"<?=$selected?>><?=$institution->LastName?></option>
    <?php endforeach; ?>
</select>
<select name="gifttype" style="width: 200px;">
    <option value="">(select gift type)</option>
    <?php foreach ($gifttypes as $gifttype): ?>
    <?php
        if ($gifttype->Value == $this->input->post('gifttype'))
            $selected = ' selected="selected"';
        else
            $selected = FALSE;
    ?>
    <option value="<?=$gifttype->Value?>"<?=$selected?>><?=$gifttype->Title?></option>
    <?php endforeach; ?>
</select>
<?=form_submit('submit', 'Submit'); ?>
<?=form_submit('reset', 'Reset'); ?>
<div>&nbsp;</div>
<?=form_close(); ?>


<?php if (isset($gifts) && $gifts): ?>
<table>
    <tr>
        <th style="width: 100px;">Gift number</th>
        <th style="width: 120px;">Institute</th>
        <th style="width: 280px;">Gift agent</th>
        <th style="width: 135px;">Gift type</th>
        <th style="width: 80px;">Quantity</th>
        <th style="width: 115px;">Shipment date</th>
    </tr>
    <?php foreach ($gifts as $gift): ?>
    <tr>
        <td><a href="<?=site_url()?>/gift/edit/giftid/<?=$gift['GiftID']?>"><?=$gift['GiftNumber']?></a></td>
        <td><?=$gift['Abbreviation']?></td>
        <td><?=$gift['GiftAgent']?></td>
        <td><?=$gift['GiftType']?></td>
        <td><?=$gift['Quantity']?></td>
        <td><?=$gift['ShipmentDate']?></td>
    </tr>
    <?php endforeach; ?>
</table>
<?php endif; ?>

<?php require_once('views/footer.php'); ?>

