<?php require_once 'header_1.php'; ?>


<div class="container">
    <div class="row">
        <div class="col-lg-12">
            <?php require_once 'includes/melcensus_links.php'; ?>
            <h1>Strawboard labels</h1>
            <table class="table table-condensed">
                <thead>
                    <tr>
                        <th>Storage group</th>
                        <th>Austr./foreign</th>
                        <th>From taxon name</th>
                        <th>To taxon name</th>
                        <th>&nbsp;</th>
                    </tr>
                </thead>
                <tbody>
                    <tr class="cupboardLabel">
                        <td>
                            <input class="form-control" type="text" name="storage_group" placeholder="Storage group"/>
                            <input type="hidden" name="storage_id" />
                        </td>
                        <td>
                            <select class="form-control" type="text" name="prep_type">
                                <option></option>
                                <option value="Austr.">Austr.</option>
                                <option value="Foreign">Foreign</option>
                            </select>
                        </td>
                        <td>
                            <input class="form-control" type="text" name="from_taxon_name" placeholder="Taxon name"/>
                        </td>
                        <td>
                            <input class="form-control" type="text" name="to_taxon_name" placeholder="Taxon name"/>
                        </td>
                        <td class="text-right">
                            <button class="removeLabel btn btn-disabled"><i class="fa fa-minus fa-lg"></i></button>
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
            <form action="<?=site_url()?>melcensus/label/strawboard" method="post"
                  target="_blank">
                <input type="hidden" name="data" value=""/>
                <button type="submit" name="print" class="btn btn-primary btn-block printStrawboardLabels">Print strawboard labels</button>
            </form>
        </div> <!-- /.col-lg-12 -->
    </div> <!-- /.col-lg-12 -->
</div> <!-- /.col-lg-12 -->


<?php require_once 'footer.php'; ?>

