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

require_once 'header.php'; ?>

<div class="container">
    <div class="row">
        <div class="col-lg-12">
            <h1>The Determinator</h1>
            <div class="form-horizontal">
                <div class="form-group">
                    <div class="col-lg-3">
                        <div class="input-group">
                            <span class="input-group-addon">Start</span>
                            <input type="number" name="start" min="1" max="30" class="form-control" value="1" required="true"/>
                        </div>
                    </div>
                </div>
                <div class="detslip">
                    <div class="well">
                        <div class="form-group">
                        </div>
                        <h3 class="text-center">National Herbarium of Victoria (MEL)</h3>
                        <br/>
                        <div class="form-group">
                            <div class="col-lg-12">
                                <input type="text" class="form-control" name="taxonName" 
                                       placeholder="Enter taxon name" required="true"/>
                                <input type="hidden" name="taxon" value="" />
                            </div>
                        </div>
                        <div class="form-group">
                            <div class="col-lg-12">
                                <input type="text" name="note" class="form-control" placeholder="Optionally enter a (very) short note" maxlength="40" />
                            </div>
                        </div>
                        <div class="form-group">
                            <div class="col-lg-3">
                                <select name="identifierRole" class="form-control">
                                    <option value="Det.">Det.</option>
                                    <option value="Conf.">Conf.</option>
                                </select>
                            </div>
                            <div class="col-lg-6">
                                <input type="text" class="form-control" name="identifiedBy" 
                                       placeholder="Enter determiner name" required="true"/>
                                <input type="hidden" name="agent" value="" />
                            </div>
                            <div class="col-lg-1" style="padding-right: 2px">
                                <input type="number" min="1" max="31" name="day" 
                                       value="<?=date('j')?>"
                                       class="form-control" placeholder="day"/>
                            </div>
                            <div class="col-lg-1" style="padding-right: 2px; padding-left: 2px;">
                                <select class="form-control" name="month" required="true">
                                    <option value=""></option>
                                    <option value="1" <?=(date('n') == "1") ? 'selected="true"' : ''?>>Jan.</option>
                                    <option value="2" <?=(date('n') == "2") ? 'selected="true"' : ''?>>Feb.</option>
                                    <option value="3" <?=(date('n') == "3") ? 'selected="true"' : ''?>>Mar.</option>
                                    <option value="4" <?=(date('n') == "4") ? 'selected="true"' : ''?>>Apr.</option>
                                    <option value="5" <?=(date('n') == "5") ? 'selected="true"' : ''?>>May</option>
                                    <option value="6" <?=(date('n') == "6") ? 'selected="true"' : ''?>>Jun.</option>
                                    <option value="7" <?=(date('n') == "7") ? 'selected="true"' : ''?>>Jul.</option>
                                    <option value="8" <?=(date('n') == "8") ? 'selected="true"' : ''?>>Aug.</option>
                                    <option value="9" <?=(date('n') == "9") ? 'selected="true"' : ''?>>Sep.</option>
                                    <option value="10" <?=(date('n') == "10") ? 'selected="true"' : ''?>>Oct.</option>
                                    <option value="11" <?=(date('n') == "11") ? 'selected="true"' : ''?>>Nov.</option>
                                    <option value="12" <?=(date('n') == "12") ? 'selected="true"' : ''?>>Dec.</option>
                                </select>
                            </div>
                            <div class="col-lg-1" style="padding-left: 2px">
                                <input type="number" name="year" min="2010" max="2017" value="<?=date('Y')?>"
                                       name="year" class="form-control" required="true" />
                            </div>
                        </div> <!-- /.form-group -->
                        <div class="form-group">
                            <div class="col-lg-3">
                                <div class="input-group">
                                    <span class="input-group-addon">Number</span>
                                    <input type="number" name="number" min="1" class="form-control" value="1" required="true"/>
                                </div>
                            </div>
                            <div class="col-lg-9 text-right">
                                <button type="button" class="btn btn-primary cloneDetslip"><i class="fa fa-copy fa-lg"></i></button>
                                <button type="button" class="btn btn-primary addDetslip"><i class="fa fa-plus fa-lg"></i></button>
                            </div>
                        </div>
                    </div> <!-- /.well -->
                </div> <!-- /.detslip -->
                <form action="<?=site_url()?>determinator/printDetslips" method="post"
                      target="_blank">
                    <input type="hidden" name="data" value=""/>
                    <button type="submit" name="print" class="btn btn-primary btn-block printDetslips">Print det. slips</button>
                </form>
                <br/>
            </div>
        </div> <!-- /.col-lg-12 -->
    </div> <!-- /.row -->
</div> <!-- /.container -->

<?php require_once 'footer.php'; ?>

