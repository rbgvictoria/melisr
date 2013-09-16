<?php require_once('header.php'); ?>

<h2>Batch <?=$BatchNo?></h2>

<?=form_open(site_url() . "/gpi/create_error_record_set/batch/$BatchNo", array('style' => 'display: inline-block'))?>
<?php $errortypes = array('NotAType', 'TypeStatusEqualsCurrent', 'NotABasionym', 'NoAuthor'); ?>

<?php foreach ($errortypes as $type): ?>
    <?php if (isset($Errors[$type])): ?>
    <?php
        switch ($type) {
            case 'NotAType':
                echo '<h3>No type status determination in MELISR</h3>';
                break;
            case 'TypeStatusEqualsCurrent':
                echo '<h3>Type status determination and current determination in same determination</h3>';
                break;
            case 'NotABasionym':
                echo '<h3>Basionym with parenthetical authors (so not a basionym, or authorship incorrect)</h3>';
                break;
            case 'NoAuthor':
                echo '<h3>Name without author</h3>';
                break;
        }
    ?>

<table class="dberrors" style="width: 100%">
    <tr>
        <th style="width: 4%">&nbsp;</th>
        <th style="width: 18%">MEL number</th>
        <th style="width: 42%">Taxon name</th>
        <th style="width: 24%">Author</th>
        <th style="width: 12%">Type status</th>
    </tr>
    <?php foreach ($Errors[$type] as $error): ?>
    <tr>
        <td style="width: 4%">
            <?php
                $value = substr($error['MELNumber'], 4);
                $opts = array(
                    'name' => 'recsetitems[]',
                    'value' => $value,
                    'checked' => ($this->input->post('recsetitems') && in_array($value, $this->input->post('recsetitems'))) ? TRUE : FALSE
                );
            ?>
            <?=form_checkbox($opts)?>
        </td>
        <td><?=$error['MELNumber']?></td>
        <td style="padding-right: 3px;">
            <?=$error['TaxonName']?>
            <?php if ($type == 'NotABasionym' || $type == 'NoAuthor'): ?>
            <?php
                if ($type == $errortypes[2])
                    $name = str_replace (' ', '%20', $error['TaxonName']);
                else {
                    $tname = explode(' ', $error['TaxonName']);
                    $name = $tname[0];
                    $name .=  (isset($tname[1])) ? '%20' . $tname[1] : '';
                }
            ?>
            
            <a href="http://anbg.gov.au/cgi-bin/apni?taxon_name=<?=$name?>%25" target="_blank"
               style="float:right;clear:right;"
               ><img src="<?=base_url()?>images/apni.gif" alt="anbg logo" width="16" height="16" /></a>
            <?php endif; ?>
        </td>
        <td><?=$error['Author']?></td>
        <td><?=$error['TypeStatusName']?></td>
    </tr>
    <?php endforeach; ?>
</table>
<br />
    <?php endif; ?>
<?php endforeach; ?>
<?=form_submit('submit', 'Create Specify record set')?>
    <?=form_dropdown('spuser', $SpecifyUsers, $this->input->post('spuser'))?>&nbsp;
    <?=form_label('Record set name:', 'recsetname', array('style' => 'width: 120px'))?>
    <?=form_input(array(
              'name'        => 'recsetname',
              'id'          => 'recsetname',
              'value'       => ($this->input->post('recsetname')) ? $this->input->post('recsetname') : 'GPI batch' . $BatchNo .'errors',
              'maxlength'   => '100',
              'size'        => '30'
            ));?>
<?=form_close()?>
<div style="padding-top: 7px;">
<?=form_open(site_url() . "/gpi/create_error_csv/batch/$BatchNo", array('style' => 'display: inline-block'))?>
    <?=form_submit('submit', 'Create CSV file')?>
<?=form_close()?>
&nbsp;
<?=form_open(site_url() . "/gpi/fix_errors/batch/$BatchNo", array('style' => 'display: inline-block'))?>
    <?=form_submit('submit', 'Fix errors')?>
<?=form_close()?>
&nbsp;
<?=form_open(site_url() . "/gpi/delete_hybrid_dets/batch/$BatchNo", array('style' => 'display: inline-block'))?>
    <?=form_submit('submit', 'Delete hybrid determinations')?>
<?=form_close()?>
</div>
<?php if (isset($message)): ?>
<div style="color: #ff0000; font-weight: bold; display: inline-block; margin-left: 15px"><?=$message?></div>

<?php endif; ?>



<div id="console">&nbsp;</div>

<?php require_once('footer.php'); ?>

