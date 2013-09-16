<?php require_once('header.php'); ?>

<h2>MEL numbers</h2>
<?=form_open('numbers/melnumber', array('style' => 'display: inline-block')); ?>
    <input type="submit" name="submit_melnumber" value="MEL numbers" style="width: 125px"/>
    <?php
            if(!isset($howmany)) $howmany = 100;
            $data3 = array(
              'name'        => 'howmany',
              'id'          => 'howmany',
              'value'       => $howmany,
              'maxlength'   => '3',
              'size'        => '10'
            );
            echo form_input($data3);
    ?>
    <p><?=anchor('numbers/melnumbers/', 'Overview of assigned MEL numbers', 'title="Overview of assigned MEL numbers"');?></p>
       
<?=form_close(); ?>
    <?php
        if(isset($startnumber)) {
            echo form_open('numbers/melnumber_insert', array('style' => 'display: inline-block'));
            echo '<span style="display:inline-block; width: 50px; text-align: right">MEL&nbsp;</span>';
            $data = array(
              'name'        => 'startnumber',
              'id'          => 'startnumber',
              'value'       => $startnumber,
              'maxlength'   => '7',
              'size'        => '10',
              'readonly'       => 'readonly'
            );
            echo form_input($data);
            if(isset($endnumber)) {
                echo '&nbsp;&ndash;&nbsp;';
                $data2 = array(
                  'name'        => 'endnumber',
                  'id'          => 'endnumber',
                  'value'       => $endnumber,
                  'maxlength'   => '7',
                  'size'        => '10',
                  'readonly'    => 'readonly'
                );
                echo form_input($data2);
            }
            if (!isset($print)) {echo form_label('Name: ', 'username', array('style' => 'text-align: right'));
                if(!isset($username)) $username = FALSE;
                $usern = array(
                  'name'        => 'username',
                  'id'          => 'username',
                  'value'       => $username,
                  'maxlength'   => '32',
                  'size'        => '12'
                );
                echo form_input($usern);
                echo '<input type="submit" id="accept" name="accept" value="Accept" style="margin-left: 3px; position: relative; top:-2px"/>';
            } else {
                $siteurl = site_url();
                echo "<span style=\"display: inline-block; margin-left: 5px;\"><a href=\"$siteurl/numbers/printcsv/start/$startnumber/end/$endnumber\">print list</a></span>";
            }
            echo form_close();
        }
    ?>
<div>&nbsp;</div>
<h2>Spirit jar, microscope slide and silica gel sample numbers</h2>

<?=form_open('numbers/spirit'); ?>
    <div style="float: right; 
         color: #ff0000; 
         width: 400px; 
         margin: 15px 300px 10px 10px; 
         padding: 10px; 
         border: solid 1px #ff0000; 
         font-weight: bold">Sample numbers for spirit collections, microscope slides and silicagel samples
    are now automatically created in Specify, so you don't need to look them up anymore.</div>
    <input type="submit" name="submit_spirit" disabled="disabled" value="Spirit jars" style="width: 125px"/>
    <?php
        if(isset($spiritnumber)) {
            $data = array(
              'name'        => 'spiritnumber',
              'id'          => 'spiritnumber',
              'value'       => $spiritnumber,
              'maxlength'   => '7',
              'size'        => '10',
              'readonly'    => 'readonly'
            );
            echo form_input($data);
        }
    ?>
<?=form_close(); ?>
<?=form_open('numbers/slide'); ?>
    <input type="submit" name="submit_slide"  disabled="disabled" value="Microscope slides" style="width: 125px; margin-top: 10px;"/>
    <?php
        if(isset($slidenumber)) {
            $data = array(
              'name'        => 'slidenumber',
              'id'          => 'slidenumber',
              'value'       => $slidenumber,
              'maxlength'   => '7',
              'size'        => '10',
              'readonly'    => 'readonly'
            );
            echo form_input($data);
        }
    ?>
<?=form_close(); ?>
<?=form_open('numbers/silicagel'); ?>
    <input type="submit" name="submit_silicagel"  disabled="disabled" value="Silica gel samples" style="width: 125px; margin-top: 10px;"/>
    <?php
        if(isset($silicagelnumber)) {
            $data = array(
              'name'        => 'silicagelnumber',
              'id'          => 'silicagelnumber',
              'value'       => $silicagelnumber,
              'maxlength'   => '7',
              'size'        => '10',
              'readonly'    => 'readonly'
            );
            echo form_input($data);
        }
    ?>
<?=form_close(); ?>
<div>&nbsp;</div>
<h2>Loan and exchange numbers</h2>
<?=form_open('numbers/loan'); ?>
    <input type="submit" name="submit_loan" value="Loans" style="width: 125px; margin-top: 10px;"/>
    <?php
        if(isset($loannumber)) {
            $data = array(
              'name'        => 'loannumber',
              'id'          => 'loannumber',
              'value'       => $loannumber,
              'maxlength'   => '9',
              'size'        => '10',
              'readonly'    => 'readonly'
            );
            echo form_input($data);
        }
    ?>
<?=form_close(); ?>
<?=form_open('numbers/exchange'); ?>
    <input type="submit" name="submit_exchange" value="Exchange" style="width: 125px; margin-top: 10px;"/>
    <?php
        if(isset($exchangenumber)) {
            $data = array(
              'name'        => 'exchangenumber',
              'id'          => 'exchangenumber',
              'value'       => $exchangenumber,
              'maxlength'   => '7',
              'size'        => '10',
              'readonly'    => 'readonly'
            );
            echo form_input($data);
        }
    ?>
<?=form_close(); ?>

<?php require_once('footer.php'); ?>

