<?php require_once('header_1.php'); ?>

<div class="container">
    <div class="row">
        <div class="col-md-12">
            <?php if ($BatchNo): ?>
            <h2>Batch <?=$BatchNo?></h2>
            <?php else: ?>
            <h2>All batches</h2>
            <?php endif; ?>

            <?php //form_open(site_url() . "/gpi/create_error_record_set/batch/$BatchNo", array('style' => 'display: inline-block'))?>
            <form action="" 
                  method="post" class="form-horizontal">
            
            <?php $errortypes = array('NotAType', 'TypeStatusEqualsCurrent', 'NotABasionym', 'NoSpecies', 'NoAuthor', 'NoProtologue'); ?>
            <?php foreach ($errortypes as $type): ?>
            <?php  
                $err = array_filter($Errors, function ($arr) use ($type) {
                    if ($arr['Type'] == $type) {
                        return true;
                    }
                    return false;
                });
            ?>
            <?php if ($err): ?>
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
                    <?php if (!$BatchNo): ?>
                    <th>Batch #</th>
                    <?php endif; ?>
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
                <?php foreach ($err as $error): ?>
                <tr>
                    <td>
                        <?php
                            $value = substr($error['CollectionObjectID'], 4);
                            $opts = array(
                                'name' => 'recsetitems[]',
                                'value' => $value,
                                'checked' => ($this->input->post('recsetitems') && in_array($value, $this->input->post('recsetitems'))) ? TRUE : FALSE
                            );
                        ?>
                        <?=form_checkbox($opts)?>
                    </td>
                    <?php if (!$BatchNo): ?>
                    <td><?=$error['BatchNo']?></td>
                    <?php endif; ?>
                    <td><a href="https://specify.rbg.vic.gov.au/specify/view/collectionobject/<?=$error['CollectionObjectID']?>/" target="_blank"><?=$error['CatalogNumber']?></a></td>
                    <td>
                        <?=$error['FullName']?>
                        <?php if ($type == 'NotABasionym' || $type == 'NoAuthor' || $type == 'NoProtologue'): ?>
                        <a href="https://biodiversity.org.au/nsl/services/search/names?product=APNI&tree.id=&name=<?= urlencode($error['FullName'])?>" target="_blank"
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
                <label class="col-md-2 control-label" for="recsetname">Record set name:</label>
                <div class="col-md-4">
                    <input type="text" name="recsetname" id="recsetname"
                          value="<?=($this->input->post('recsetname')) ? 
                            $this->input->post('recsetname') : 'GPI batch' . 
                            $BatchNo .'errors'?>" class="form-control" />
                </div>
            </div> <!-- /.form-group -->
            
            <div>
                <?php 
                    $formaction = site_url() . 'gpi/create_error_record_set';
                    if ($BatchNo) {
                        $formaction .= '/batch/' . $BatchNo;
                    }
                ?>
                <input type="submit" name="submit" value="Create Specify record set"
                       formaction="<?=$formaction?>"
                       class="btn btn-primary" />
                
            </div> <!-- /.form-group -->

        </div> <!-- /.col-md-12 -->
    </div> <!-- /.row -->
</div> <!-- /.container -->

<?php require_once('footer.php'); ?>

