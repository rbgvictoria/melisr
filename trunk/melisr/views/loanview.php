<?php require_once('header_1.php'); ?>

<h2>Loans</h2>
<?=form_open('loanreturn/loans', array('id'=>'loans')); ?>
<p>
    <input type="radio" name="discipline" id="discipline_1" value="3"<?=($discipline == 3) ? 'checked="checked"' : ''; ?>/><label for="discipline_1">MEL loans</label>
    <input type="radio" name="discipline" id="discipline_2" value="32768"<?=($discipline == 32768) ? 'checked="checked"' : ''; ?>/><label for="discipline_2" style="width: auto">non-MEL loans</label>
</p>
<div>
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
<?=form_dropdown('institution', $institutions, $this->input->post('institution'), 'style="width: 200px;"'); ?>
<select name="filter" style="width: 200px;">
    <option value="0">All loans</option>
<?php if ($discipline == 32768): ?>
    <option value="1"<?=($this->input->post('filter') == 1) ? ' selected="selected"' : FALSE; ?>>Current loans</option>
    <option value="2"<?=($this->input->post('filter') == 2) ? ' selected="selected"' : FALSE; ?>>Complete loans</option>
    <option value="3"<?=($this->input->post('filter') == 3) ? ' selected="selected"' : FALSE; ?>>Returning loans</option>
<?php else: ?>
    <option value="1"<?=($this->input->post('filter') == 1) ? ' selected="selected"' : FALSE; ?>>Open loans</option>
    <option value="2"<?=($this->input->post('filter') == 2) ? ' selected="selected"' : FALSE; ?>>Closed loans</option>
    <option value="3"<?=($this->input->post('filter') == 3) ? ' selected="selected"' : FALSE; ?>>Partially returned loans</option>
    <option value="4"<?=($this->input->post('filter') == 4) ? ' selected="selected"' : FALSE; ?>>Loans with preparations</option>
    <option value="5"<?=($this->input->post('filter') == 5) ? ' selected="selected"' : FALSE; ?>>Loans without preparations</option>
<?php endif; ?>
</select>
<?=form_submit('submit', 'Submit'); ?>
<?=form_submit('reset', 'Reset'); ?>
</div>
<div>&nbsp;</div>
<?=form_close(); ?>
<?php if (isset($loans) && $loans): ?>
<?php
    echo '<p>' . count($loans);
    echo (count($loans) > 1) ? ' loans' : ' loan';
    echo '</p>';
?>
<table id="loantable">
    <thead>
        <tr>
            <?php if ($discipline == 32768): ?>
            <th>MEL ref. no.</th>
            <?php endif; ?>
            <th style="width: 150px;">Loan number</th>
            <th>Botanist</th>
            <th style="width: 80px;">Quantity</th>
            <th style="width: 80px;">Outstanding</th>
            <th>Status</th>
            <th>Due&nbsp;date</th>
            <th>&nbsp;</th>
        </tr>
    </thead>
    <tbody>
        <?php foreach ($loans as $loan): ?>
        <tr>
            <?php if ($discipline == 32768): ?>
            <td><?=$loan['MELRefNo']?></td>
            <?php endif; ?>
            <td><?=$loan['LoanNumber']?></td>
            <td><?=$loan['Botanist']?></td>
            <td><?=$loan['Quantity']?></td>
            <td><?=($loan['QuantityResolved']) ? $loan['Quantity'] - $loan['QuantityResolved'] : $loan['Quantity']; ?></td>
            <td><?=$loan['LoanStatus']?></td>
            <td><?=($loan['LoanStatus']!='Complete') ? $loan['CurrentDueDate'] : '&nbsp;'?></td>
            <td>
                <?php 
                    if ($discipline == 32768) {
                        $form = 'borrower';
                        $submit = 'Borrower';
                        $hidden = array(
                            'melrefno' => $loan['LoanID']
                        );
                    }
                    else {
                        $form = 'loanreturn/prepare';
                        $submit = 'Loan returner';
                        $hidden = array(
                            'loannumber' => $loan['LoanID'],
                            'allpreps' => 1,
                        );
                    }
                    echo form_open($form, array('target' => '_blank'));
                    echo form_hidden($hidden);
                    echo form_submit('submit', $submit);
                    echo form_close();
                ?>
            </td>
        </tr>
        <?php endforeach; ?>
    </tbody>
</table>

<?php endif; ?>


<?php require_once('footer.php'); ?>

