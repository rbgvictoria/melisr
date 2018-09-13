<?php require_once('header_1.php'); ?>

<div class="container">
    <div class="row">
        <div class="col-md-12">
            <?php require_once APPPATH . 'views/includes/vcsb_links.php'; ?>
            <h2>Victorian Conservation Seed Bank</h2>
            
            <?php if($records): ?>
            <p><?=count($records)?> new records to be added to Victorian Conservation Seed Bank collection</p>
            <?=form_open('vcsb'); ?>
            <form action="" method="post" class="form-horizontal">
                <div>
                    <input type="submit" name="updatevcsb" value="Update VCSB collection"
                           class="btn btn-primary" />
                </div>
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
                    </tr>
                    <?php foreach ($records as $index=>$row): ?>
                    <?php if ($index%2==0): ?>
                    <tr class="odd">
                    <?php else: ?>
                    <tr class="even"?>
                    <?php endif; ?>
                        <td><?=$row['CatalogNumber']?><?=form_hidden("collectionobjectid[$index]", $row['CollectionObjectID']);?><?=form_hidden("catalognumber[$index]", $row['CatalogNumber']);?></td>
                        <td><?=$row['SampleNumber']?><?=form_hidden("vcsbnumber[$index]", $row['SampleNumber']);?></td>
                        <td><?=$row['MiddleInitial']?><?=form_hidden("agentid[$index]", $row['ModifiedByAgentID']);?></td>
                        <td><?=$row['TimestampModified']?></td>
                        <td><?=form_checkbox("flowers[$index]", 1, ($row['Flowers']) ? TRUE : FALSE);?></td>
                        <td><?=form_checkbox("fruit[$index]", 1, ($row['Fruit']) ? TRUE : FALSE);?></td>
                        <td><?=form_checkbox("buds[$index]", 1, ($row['Buds']) ? TRUE : FALSE);?></td>
                        <td><?=form_checkbox("leafless[$index]", 1, ($row['Leafless']) ? TRUE : FALSE);?></td>
                        <td><?=form_checkbox("fertile[$index]", 1, ($row['Fertile']) ? TRUE : FALSE);?></td>
                        <td><?=form_checkbox("sterile[$index]", 1, ($row['Sterile']) ? TRUE : FALSE);?></td>
                        <td><?=form_textarea(['name'=>"curationnotes[$index]", 
                            'value' => '','rows' => '1', 'class' => 'form-control']);?>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </table>
            </form>
            <br/>
            <?php elseif ($message): ?>
                <div class="alert alert-success alert-dismissible" role="alert">
                    <?=$message?>
                </div>
            <?php endif; ?>
        </div> <!-- /.col-md-12 -->
    </div> <!-- /.row -->
</div> <!-- /.container -->

<?php require_once('footer.php'); ?>

