<?php require_once('header_1.php'); ?>

<div class="container">
    <div class="row">
        <div class="col-md-12">
            <?php require_once APPPATH . 'views/includes/vrs_links.php'; ?>
            <h2>Victorian Reference Set</h2>
            
            <?php if($records): ?>
            <p><?=count($records)?> new records to be added to Vic. Ref. Set collection</p>
            <?=form_open('vrs'); ?>
            <form action="" method="post" class="form-horizontal">
                <table class="table table-bordered table-condensed table-responsive">
                    <tr>
                        <th>Catalogue number</th>
                        <th>VRS number</th>
                        <th>Modified by</th>
                        <th>Modified</th>
                        <th>Flowers</th>
                        <th>Fruit</th>
                        <th>Buds</th>
                        <th>Leafless</th>
                        <th>Fertile</th>
                        <th>Sterile</th>
                        <th>Curation notes</th>
                        <th><i class="fa fa-wrench" title="Create VRS record"></i></th>
                    </tr>
                    <?php foreach ($records as $index=>$row): ?>
                    <?php if ($index%2==0): ?>
                    <tr class="odd">
                    <?php else: ?>
                    <tr class="even"?>
                    <?php endif; ?>
                        <td><?=$row['CatalogNumber']?><?=form_hidden("collectionobjectid[$index]", $row['CollectionObjectID']);?><?=form_hidden("catalognumber[$index]", $row['CatalogNumber']);?></td>
                        <td><?=$row['SampleNumber']?><?=form_hidden("vrsnumber[$index]", $row['SampleNumber']);?></td>
                        <td><?=$row['MiddleInitial']?><?=form_hidden("agentid[$index]", $row['ModifiedByAgentID']);?></td>
                        <td><?=$row['TimestampModified']?></td>
                        <td><?=form_checkbox("flowers[$index]", 1, ($row['Flowers']) ? TRUE : FALSE);?></td>
                        <td><?=form_checkbox("fruit[$index]", 1, ($row['Fruit']) ? TRUE : FALSE);?></td>
                        <td><?=form_checkbox("buds[$index]", 1, ($row['Buds']) ? TRUE : FALSE);?></td>
                        <td><?=form_checkbox("leafless[$index]", 1, ($row['Leafless']) ? TRUE : FALSE);?></td>
                        <td><?=form_checkbox("fertile[$index]", 1, ($row['Fertile']) ? TRUE : FALSE);?></td>
                        <td><?=form_checkbox("sterile[$index]", 1, ($row['Sterile']) ? TRUE : FALSE);?></td>
                        <td><?=form_textarea(['name'=>"curationnotes[$index]", 
                            'value' => '','rows' => '0', 'class' => 'form-control']);?>
                        </td>
                        <td><?=form_checkbox("createVrsRec[$index]", $row['CollectionObjectID'], 0);?></td>
                    </tr>
                    <?php endforeach; ?>
                </table>
                <div>
                    <input type="submit" name="updatevrs" value="Update VRS collection"
                           class="btn btn-primary" />
                </div>
            </form>
            <br/>
            <?php endif; ?>
        </div> <!-- /.col-md-12 -->
    </div> <!-- /.row -->
</div> <!-- /.container -->

<?php require_once('footer.php'); ?>

