<?php require_once('header_1.php'); ?>

<div class="container">
    <div class="row">
        <div class="col-md-12">

            <h2>Loans</h2>
            <form action="<?=site_url()?>loanreturn/loans" method="post"
                  class="form-horizontal">
                <div class="radio-inline">
                    <label>
                        <input type="radio" name="discipline" id="discipline_1" value="3"<?=($discipline == 3) ? 'checked="checked"' : ''; ?>/>
                        MEL loans
                    </label>
                </div>
                <div class="radio-inline">
                    <label>
                        <input type="radio" name="discipline" id="discipline_2" value="32768"<?=($discipline == 32768) ? 'checked="checked"' : ''; ?>/>
                        non-MEL loans
                    </label>
                </div>
                
                <div class="form-group">
                    <div class="col-lg-2">
                        <select id="year" name="year" class="form-control">
                            <option value="">(select year)</option>
                            <?php foreach ($years as $year): ?>
                            <?php
                                if ($year['Year'] == $this->input->post('year'))
                                    $selected = ' selected="selected"';
                                else
                                    $selected = FALSE;
                            ?>
                            <option value="<?=$year['Year']?>"<?=$selected?>><?=$year['Year']?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="col-lg-5">
                        <?=form_dropdown('institution', $institutions, $this->input->post('institution'), 'class="form-control"'); ?>
                    </div>
                    
                    <div class="col-lg-3">
                        <select name="filter" class="form-control">
                            <option value="0">All loans</option>
                        <?php if ($discipline == 32768): ?>
                            <option value="1"<?=($this->input->post('filter') == 1) ? ' selected="selected"' : FALSE; ?>>Current loans</option>
                            <option value="2"<?=($this->input->post('filter') == 2) ? ' selected="selected"' : FALSE; ?>>Complete loans</option>
                            <option value="3"<?=($this->input->post('filter') == 3) ? ' selected="selected"' : FALSE; ?>>Returning loans</option>
                        <?php else: ?>
                            <option value="1"<?=($this->input->post('filter') == 1) ? ' selected="selected"' : FALSE; ?>>Open loans</option>
                            <option value="2"<?=($this->input->post('filter') == 2) ? ' selected="selected"' : FALSE; ?>>Closed loans</option>
                            <option value="3"<?=($this->input->post('filter') == 3) ? ' selected="selected"' : FALSE; ?>>Partially returned loans</option>
                            <option value="4"<?=($this->input->post('filter') == 4) ? ' selected="selected"' : FALSE; ?>>Loans with preparations</option>
                            <option value="5"<?=($this->input->post('filter') == 5) ? ' selected="selected"' : FALSE; ?>>Loans without preparations</option>
                        <?php endif; ?>
                        </select>
                    </div>

                    <div class="col-lg-2">
                        <button class="btn btn-primary btn-block" type="submit" name="submit">
                            Submit
                        </button>
                    </div>
                </div>

                <div>&nbsp;</div>
            </form>
            
            
            <?php if (isset($loans) && $loans): ?>
            <?php
                echo '<p>' . count($loans);
                echo (count($loans) > 1) ? ' loans' : ' loan';
                echo '</p>';
            ?>
            <table id="loantable" class="table table-bordered table-condensed table-responsive">
                <thead>
                    <tr>
                        <?php if ($discipline == 32768): ?>
                        <th>MEL ref. no.</th>
                        <?php endif; ?>
                        <th style="width: 150px;">Loan number</th>
                        <th>Botanist</th>
                        <th style="width: 80px;">Quantity</th>
                        <th style="width: 80px;">Outstanding</th>
                        <th>Status</th>
                        <th>Due&nbsp;date</th>
                        <th>&nbsp;</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($loans as $loan): ?>
                    <tr>
                        <?php if ($discipline == 32768): ?>
                        <td><?=$loan['MELRefNo']?></td>
                        <?php endif; ?>
                        <td><?=$loan['LoanNumber']?></td>
                        <td><?=$loan['Botanist']?></td>
                        <td><?=$loan['Quantity']?></td>
                        <td><?=($loan['QuantityResolved']) ? $loan['Quantity'] - $loan['QuantityResolved'] : $loan['Quantity']; ?></td>
                        <td><?=$loan['LoanStatus']?></td>
                        <td><?=($loan['LoanStatus']!='Complete') ? $loan['CurrentDueDate'] : '&nbsp;'?></td>
                        <td>
                            <?php if ($discipline == 32768): ?>
                            <form class="form-inline" 
                                  action="<?=site_url()?>borrower"
                                  method="post">
                                <input type="hidden" name="melrefno" 
                                       value="<?=$loan['LoanID']?>" />
                                <button type="submit" name="submit" 
                                        class="btn btn-primary btn-sm">
                                    Borrower
                                </button>
                            </form>
                            <?php else: ?>
                            <form class="form-inline" 
                                  action="<?=site_url()?>loanreturn/prepare"
                                  method="post">
                                <input type="hidden" name="loannumber" 
                                       value="<?=$loan['LoanID']?>" />
                                <input type="hidden" name="allpreps" value="1" />
                                <button type="submit" name="submit" 
                                        class="btn btn-primary btn-sm">
                                    Loan return
                                </button>
                            </form>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>

            <?php endif; ?>

            
        </div> <!-- /.col-md-12 -->
    </div> <!-- /.row -->
</div> <!-- /.container> -->


<?php require_once('footer.php'); ?>

