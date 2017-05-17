<?php require_once 'header.php'; ?>

<div class="container">
    <div class="row">
        <div class="col-md-12">

            <h2>DNA sequencing projects</h2>
            <?php if ($projects): ?>
            <table class="table table-bordered table-condensed table-responsive">
                <thead>
                    <tr>
                        <th>Project name</th>
                        <th>Collection</th>
                        <th>Number of sequences</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($projects as $project): ?>
                    <tr>
                        <td><?=$project['ProjectName']?></td>
                        <td><?=$project['CollectionName']?></td>
                        <td class="td-right"><?=$project['cntSequences']?></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
            <?php endif; ?>

            <form action="" method="post" class="form-horizontal">
                <h3>Add new project</h3>
                <div class="form-group">
                    <label class="control-label col-md-2" for="specify_user">
                        Specify user:
                    </label>
                    <div class="col-md-10">
                        <?=form_dropdown('specify_user', $specify_user, 
                                $this->input->post('specify_user'), 
                                'id="specify_user" class="form-control"'); ?>
                    </div>
                </div> <!-- /.form-group -->

                <div class="form-group">
                    <label class="control-label col-md-2" for="new_project">
                        Project name:
                    </label>
                    <div class="col-md-10">
                        <input type="text" name="new_project" id="new_project"
                               class="form-control" />
                    </div>
                </div> <!-- /.form-group -->

                <p>
                    <button type="submit" formaction="new_project" 
                            class="btn btn-primary btn-block">
                        Add project
                    </button>
                </p>
            </form>

        </div> <!-- /.col-md-12 -->
    </div> <!-- /.row -->
</div> <!-- /.container -->

<?php require_once 'footer.php'; ?>

