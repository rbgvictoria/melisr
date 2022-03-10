<?php require_once 'header_1.php'; ?>


<div class="container">
    <div class="row">
        <div class="col-lg-12">
            <?php require_once 'includes/melcensus_links.php'; ?>
            <h1>Taxon name slips</h1>

            <div class="form-horizontal">
                <div class="form-group">
                    <label class="control-label col-md-1">Offset</label>
                    <div class="col-md-1">
                        <input class="form-control" type="number" min="0" max="29" value="0" name="offset" />
                    </div>
                </div>
            </div>
            
            <table class="table table-condensed">
                <thead>
                    <tr>
                        <th width="10%">#</th>
                        <th width="30%">Storage group</th>
                        <th>Taxon name</th>
                    </tr>
                </thead>
                <tbody>
                    <tr class="cupboardLabel">
                        <td>
                            <input class="form-control" type="number" min="1" value="1" name="num_slips" />
                        </td>
                        <td>
                            <input class="form-control" type="text" name="storage_group" placeholder="Storage group"/>
                            <input type="hidden" name="storage_id" />
                        </td>
                        <td>
                            <input class="form-control" type="text" name="taxon_name" data-with-author="1" placeholder="Taxon name"/>
                        </td>
                    </tr>
                </tbody>
                <tfoot>
                    <tr>
                        <td colspan="5" class="text-right">
                            <button type="button" class="btn btn-primary" id="cloneLabel"><i class="fa fa-copy fa-lg"></i></button>
                            <button type="button" class="btn btn-primary" id="addLabel"><i class="fa fa-plus fa-lg"></i></button>
                        </td>
                    </tr>
                </tfoot>
            </table>
            
            <div>&nbsp;</div>
            <form action="<?=site_url()?>melcensus/label/taxon-name" method="post"
                  target="_blank">
                <input type="hidden" name="data" value=""/>
                <button type="submit" name="print" class="btn btn-primary btn-block printTaxonNameLabels">Print taxon name slips</button>
            </form>
        </div> <!-- /.col-lg-12 -->
    </div> <!-- /.col-lg-12 -->
</div> <!-- /.col-lg-12 -->


<?php require_once 'footer.php'; ?>