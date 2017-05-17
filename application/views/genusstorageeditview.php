<?php require_once('header.php'); ?>

<div class="container">
    <div class="row">
        <div class="col-md-12">
            <h4>Edit genus and higher taxon storage</h4>
            <h2><?=$name?></h2>
            <?php if ($classification): ?>
                <?php foreach ($classification as $item): ?>
                    <p><?=$item['Rank']?>: <b><?=$item['Name']?></b></p>
                <?php endforeach; ?>
            <?php endif; ?>
                    <br/><br/>
            <form action="<?=site_url()?>genusstorage/insert/" method="post" class="form-horizontal">
                <?=form_hidden('taxonid', $taxonid)?>
                <?=form_hidden('name', $name)?>
                <div class="form-group">
                    <label class="col-md-3 control-label" for="storedunder">Stored under:</label>
                    <div class="col-md-9 form-inline">
                        <select name="storedunder" id="storedunder" class="form-control">
                            <?=$options?>
                        </select>
                        <?=form_submit(['name' => 'insert', 'value' => 'Insert', 'class' => 'btn btn-primary'])?>
                    </div>
                    
                </div>

            <?php if($colobjects): ?>
                <h3>Specimen records</h3>
                <p>Storage of the following records will be updated when the storage family is set.</p>
                <table class="table table-condensed table-bordered">
                    <tr>
                        <th>Catalogue number</th>
                        <th>Taxon name</th>
                        <th>Storage type</th>
                        <th>Modification date</th>
                        <th>Modified by</th>
                    </tr>
                    <?php foreach ($colobjects as $colobj): ?>
                    <tr>
                        <td><?=$colobj['CatalogNumber']?><?=form_hidden('colobj[]', $colobj['CollectionObjectID'])?></td>
                        <td><?=$colobj['FullName']?></td>
                        <td><?=$colobj['StorageType']?><?=form_hidden('storagetype[]', $colobj['StorageType'])?></td>
                        <td><?=$colobj['DateModified']?></td>
                        <td><?=$colobj['ModifiedBy']?></td>
                    </tr>
                    <?php endforeach; ?>
                </table>
            <?php endif; ?>
            </form>
        </div> <!-- /.col-md-12 -->
    </div> <!-- /.row -->
</div> <!-- /.container -->

<?php require_once('footer.php'); ?>

