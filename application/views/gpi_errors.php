<?php require_once('header_1.php'); ?>

<div class="container">
    <div class="row">
        <div class="col-md-12">

            <h2>Batch <?=$BatchNo?></h2>

            <?php //form_open(site_url() . "/gpi/create_error_record_set/batch/$BatchNo", array('style' => 'display: inline-block'))?>
            <form action="" 
                  method="post" class="form-horizontal">
            
            <?php $errortypes = array('NotAType', 'TypeStatusEqualsCurrent', 'NotABasionym', 'NoSpecies', 'NoAuthor', 'NoProtologue'); ?>

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
                        case 'NoSpecies':
                            echo '<h3>Typified name without specific epithet</h3>';
                            break;
                        case 'NoAuthor':
                            echo '<h3>Name without author</h3>';
                            break;
                        case 'NoProtologue':
                            echo '<h3>Typified name with missing or incomplete protologue info.</h3>';
                            break;
                    }
                ?>

            <table class="table table-bordered table-condensed table-responsive" style="width: 100%">
                <tr>
                    <th>&nbsp;</th>
                    <th>MEL number</th>
                    <?php if($type == 'NoProtologue') :?>
                    <th>Taxon name</th>
                    <th>Author</th>
                    <th>Protologue</th>
                    <th>Year</th>
                    <?php else: ?>
                    <th>Taxon name</th>
                    <th>Author</th>
                    <th>Type status</th>
                    <?php endif; ?>
                </tr>
                <?php foreach ($Errors[$type] as $error): ?>
                <tr>
                    <td>
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
                    <td>
                        <?=$error['TaxonName']?>
                        <?php if ($type == 'NotABasionym' || $type == 'NoAuthor' || $type == 'NoProtologue'): ?>
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
                    <?php if ($type == 'NoProtologue'): ?>
                    <td><?=$error['Protologue']?></td>
                    <td><?=$error['Year']?></td>
                    <?php else:?>
                    <td><?=$error['TypeStatusName']?></td>
                    <?php endif;?>
                </tr>
                <?php endforeach; ?>
            </table>
            <?php endif; ?>
            <?php endforeach; ?>
                
            <div class="form-group">
                <label class="col-md-2 control-label" for="spuser">Specify user:</label>
                <div class="col-md-4">
                    <?=form_dropdown('spuser', $SpecifyUsers, 
                            $this->input->post('spuser'), 'id="spuser" class="form-control"')?>
                </div>
                <label class="col-md-2 control-label" for="recsetname">Specify user:</label>
                <div class="col-md-4">
                    <input type="text" name="recsetname" id="recsetname"
                          value="<?=($this->input->post('recsetname')) ? 
                            $this->input->post('recsetname') : 'GPI batch' . 
                            $BatchNo .'errors'?>" class="form-control" />
                </div>
            </div> <!-- /.form-group -->
            
            <div>
                <input type="submit" name="submit" value="Create Specify record set"
                       formaction="<?=site_url()?>gpi/create_error_record_set/batch/<?=$BatchNo?>"
                       class="btn btn-primary" />
                
                <button type="submit" 
                        formaction="<?=site_url()?>gpi/create_error_csv/batch/<?=$BatchNo?>"
                        class="btn btn-primary">
                    Create CSV file
                </button>

                <button type="submit" 
                        formaction="<?=site_url()?>gpi/fix_errors/batch/<?=$BatchNo?>"
                        class="btn btn-primary">
                    Fix errors
                </button>

                <button type="submit" 
                        formaction="<?=site_url()?>gpi/delete_hybrid_dets/batch/<?=$BatchNo?>"
                        class="btn btn-primary">
                    Delete hybrid determinations
                </button>

                <button type="submit" 
                        formaction="<?=site_url()?>gpi/delete_non_types/batch/<?=$BatchNo?>"
                        class="btn btn-primary">
                    Delete non-types
                </button>
            </div> <!-- /.form-group -->

        </div> <!-- /.col-md-12 -->
    </div> <!-- /.row -->
</div> <!-- /.container -->

<?php require_once('footer.php'); ?>

