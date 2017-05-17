<?php require_once 'header.php'; ?>

<div class="container">
    <div class="row">
        <div class="col-md-12">
            <h2>Markers</h2>
            <?php if ($markers): ?>
            <table class="table table-bordered table-condensed table-responsive">
                <thead>
                    <tr>
                        <th>Marker</th>
                        <th>In pick list</th>
                        <th>Number of sequences</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($markers as $marker): ?>
                    <tr>
                        <td><?=$marker['targetMarker']?></td>
                        <td class="td-center"><?=($marker['isInPickList']) ? '<i class="fa fa-check green"></i>':'<i class="fa fa-remove red"></i>';?></td>
                        <td class="td-right"><?=$marker['cntSequences']?></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
            <?php endif; ?>

            <form action="" method="post" class="form-horizontal">
                <h3>Add new marker</h3>
                <div class="form-group">
                    <label class="control-label col-md-2" for="specify_user">
                        Specify user:
                    </label>
                    <div class="col-md-10">
                        <?=form_dropdown('specify_user', 
                                $specify_user, $this->input->post('specify_user'), 
                                'id="specify_user" class="form-control"'); ?>
                    </div>
                </div> <!-- /.form-group -->
                
                <div class="form-group">
                    <label class="control-label col-md-2" for="new_marker">
                        Marker:
                    </label>
                    <div class="col-md-10">
                        <input type="text" name="new_marker" id="new_marker"
                               class="form-control" />
                    </div>
                </div> <!-- /.form-group -->
                
                <p>
                    <button type="submit" value="1" formaction="new_marker" 
                            class="btn btn-primary btn-block">
                        Add marker
                    </button>
                </p>
            </form>

        </div> <!-- /.col-md-12 -->
    </div> <!-- /.row -->
</div> <!-- /.container -->

<?php require_once 'footer.php'; ?>

