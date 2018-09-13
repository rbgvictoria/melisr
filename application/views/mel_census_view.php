<?php

/* 
 * Copyright 2017 Niels Klazenga, Royal Botanic Gardens Victoria.
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 *      http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 */

require_once 'header_1.php';

?>

<div class="container">
    <div class="row">
        <div class="col-lg-12">
            <?php require_once 'includes/melcensus_links.php'; ?>
            <h1>MEL Census</h1>
            
            <div class="form-horizontal">
                <div class="form-group" id="major-group-div">
                    <label class="control-label col-md-2">Major group</label>
                    <div class="col-md-10">
                    <select class="form-control" id="major-group">
                        <?php foreach ($majorGroups as $group): ?>
                        <option value="<?=$group['StorageID']?>"><?=$group['Name']?></option>
                        <?php endforeach; ?>
                    </select>
                    </div>
                </div>
                <div class="form-group" id="subgroup-div">
                    <label class="control-label col-md-2">Major group</label>
                    <div class="col-md-10">
                    <select class="form-control" id="subgroup"></select>
                    </div>
                </div>
            </div>
            
            <div class="well">
                <div class="row">
                    <div class="col-md-6">
                        <h4>Data from</h4>
                        <div class="checkbox">
                            <label>
                                <input type="checkbox" name="source[]" value="melisr" checked="true"> MELISR
                            </label>
                        </div>
                        <div class="checkbox">
                            <label>
                                <input type="checkbox" name="source[]"  value="census" checked="true"> MEL Census
                            </label>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <h4>Australian or foreign</h4>
                        <div class="checkbox">
                            <label>
                                <input type="checkbox" name="area[]" value="au" checked="true"> Australian records
                            </label>
                        </div>
                        <div class="checkbox">
                            <label>
                                <input type="checkbox" name="area[]" value="f" checked="true"> Foreign records
                            </label>
                        </div>
                    </div>
                </div>
            </div> <!-- /.well -->
            
            <table id="storageTaxa" class="table table-condensed table-bordered table-responsive">
                <thead></thead>
                <tbody></tbody>
            </table>
        </div> <!-- /.col-lg-12 -->
    </div> <!-- /.row -->
</div> <!-- /.container -->


<?php require_once 'footer.php' ?>;
