<?php require_once('views/header.php'); ?>

<?php if (isset($gift) && $gift): ?>
<?=form_open("gift/edit/giftid/$gift->GiftID")?>
<div id="gift">
    <h3>Gift</h3>
    <p>
        <?php
            $inputdata = array(
                'id' => 'gift_giftid',
                'name' => 'gift_giftid',
                'value' => $gift->GiftNumber,
                'style' => 'width: 50px; background-color: #C6E6F2;',
                'maxlength' => 4,
            );
            echo form_label('Gift number:', 'gift_giftid', array('style' => 'width: 100px;'));
            echo form_input($inputdata);
        ?>
        <?=form_label('Category:', 'gift_type', array('style' => 'width: 65px; margin-left: 20px;'));?>
        <select id="gift_type" name="gift_type" style="width: 200px;">
            <option value="">(select gift type)</option>
            <?php foreach ($gifttypes as $gifttype): ?>
            <?php
                if ($gifttype->Value == $gift->SrcGeography)
                    $selected = ' selected="selected"';
                else
                    $selected = FALSE;
            ?>
            <option value="<?=$gifttype->Value?>"<?=$selected?>><?=$gifttype->Title?></option>
            <?php endforeach; ?>
        </select>
        <?php
            $inputdata = array(
                'id' => 'gift_quantity',
                'name' => 'gift_quantity',
                'value' => floor($gift->Number1),
                'style' => 'width: 30px; text-align: right',
                'disabled' => 'disabled',
            );
            echo form_label('Quantity:', 'gift_quantity', array('style' => 'width: 65px; margin-left: 20px;'));
            echo form_input($inputdata);

            $inputdata = array(
                'id' => 'gift_filename',
                'name' => 'gift_filename',
                'value' => $gift->SrcTaxonomy,
                'style' => 'width: 120px;',
            );
            echo form_label('File name:', 'gift_filename', array('style' => 'width: 65px; margin-left: 20px;'));
            echo form_input($inputdata);

        ?>
    </p>
    <p class="align-top">
        <?php
            $inputdata = array(
                'id' => 'gift_description',
                'name' => 'gift_description',
                'value' => $gift->Remarks,
                'rows' => 2,
                'cols' => 86,
                'style' => 'font-family: arial; font-size: 10pt;',
            );
            echo form_label('Description:', 'gift_description', array('style' => 'width: 100px;'));
            echo form_textarea($inputdata);
        ?>
    </p>
    <p>
        <?php
            $inputdata = array(
                'id' => 'gift_acknowledged',
                'name' => 'gift_acknowledged',
                'value' => $gift->DateReceived,
                'style' => 'width: 120px;',
            );
            echo form_label('Acknowledged:', 'gift_acknowledged', array('style' => 'width: 100px;'));
            echo form_input($inputdata);

            $inputdata = array(
                'id' => 'gift_receivedcomments',
                'name' => 'gift_receivedcomments',
                'value' => $gift->ReceivedComments,
                'style' => 'width: 120px;',
            );
            echo form_label('Received comments:', 'gift_receivedcomments', array('style' => 'width: 130px; margin-left: 20px;'));
            echo form_input($inputdata);
        ?>
    </p>
    
    <?php if (isset($giftagents) && $giftagents): ?>
    <div id="giftagents">
        <h3>Gift agents</h3>
        <?php foreach ($giftagents as $key => $giftagent): ?>
        <p>
            <?php
                $hidden = array(
                    "giftagent_agentid[$key]" => $giftagent->AgentID,
                    "giftagent_giftagentid[$key]" => $giftagent->GiftAgentID,
                );
                
                echo form_hidden($hidden);
                
                $inputdata = array(
                    'id' => "giftagent_giftagent_$key",
                    'name' => "giftagent_giftagent[$key]",
                    'value' => $giftagent->GiftAgent,
                    'style' => 'width: 200px;',
                );
                echo form_label('Name:', "giftagent_giftagent_$key", array('style' => 'width: 100px;'));
                echo form_input($inputdata);
                
            ?>
            <?=form_label('Role:', "giftagent_role_$key", array('style' => 'width: 40px; margin-left: 20px;'));?>
            <select id="giftagent_role_<?=$key?>" name="giftagent_role[<?=$key?>]" style="width: 200px;">
                <option value="">(select gift type)</option>
                <?php foreach ($giftagentroles as $role): ?>
                <?php
                    if ($role->Value == $giftagent->Role)
                        $selected = ' selected="selected"';
                    else
                        $selected = FALSE;
                ?>
                <option value="<?=$role->Value?>"<?=$selected?>><?=$role->Title?></option>
                <?php endforeach; ?>
            </select>
        </p>
        <?php endforeach; ?>
    </div>
    <?php endif; ?>
    <?php if (isset($shipments) && $shipments): ?>
    <div id="shipments">
        <h3>Shipments</h3>
        <?php foreach ($shipments as $key => $shipment): ?>
        <div>
            <p>
                <?php
                    $hidden = array(
                        "shipment_shipmentid[$key]" => $shipment->ShipmentID,
                        "shipment_shippedbyid[$key]" => $shipment->ShippedByID,
                        "shipment_shippedtoid[$key]" => $shipment->ShippedToID,
                    );

                    echo form_hidden($hidden);

                    $inputdata = array(
                        'id' => "shipment_shippedto_$key",
                        'name' => "shipment_shippedto[$key]",
                        'value' => $shipment->ShippedTo,
                        'style' => 'width: 250px;',
                    );
                    echo form_label('Sent to:', "shipment_shippedto_$key", array('style' => 'width: 100px;'));
                    echo form_input($inputdata);

                    $inputdata = array(
                        'id' => "shipment_number_$key",
                        'name' => "shipment_number[$key]",
                        'value' => $shipment->ShipmentNumber,
                        'maxlength' => '5',
                        'style' => 'width: 50px; text-align: right;',
                    );
                    echo form_label('Shipment number:', "shipment_number_$key", array('style' => 'width: 120px; margin-left: 20px;'));
                    echo form_input($inputdata);

                ?>
            </p>
            <p>
                <?php
                    $inputdata = array(
                        'id' => "shipment_date_$key",
                        'name' => "shipment_date[$key]",
                        'value' => $shipment->ShipmentDate,
                        'style' => 'width: 120px;',
                    );
                    echo form_label('Date sent:', "shipment_date_$key", array('style' => 'width: 100px;'));
                    echo form_input($inputdata);

                    $inputdata = array(
                        'id' => "shipment_shippedby_$key",
                        'name' => "shipment_shippedby[$key]",
                        'value' => $shipment->ShippedBy,
                        'style' => 'width: 100px;',
                    );
                    echo form_label('Prepared by:', "shipment_shippedby_$key", array('style' => 'width: 85px; margin-left: 20px;'));
                    echo form_input($inputdata);

                ?>
            </p>
            <p>
                <?=form_label('Method:', "shipment_method_$key", array('style' => 'width: 100px;'));?>
                <select id="shipment_method_<?=$key?>" name="shipment_method[<?=$key?>]" style="width: 200px;">
                    <option value="">(select shipment method)</option>
                    <?php foreach ($shipmentmethods as $method): ?>
                    <?php
                        if ($method->Value == $shipment->ShipmentMethod)
                            $selected = ' selected="selected"';
                        else
                            $selected = FALSE;
                    ?>
                    <option value="<?=$method->Value?>"<?=$selected?>><?=$method->Title?></option>
                    <?php endforeach; ?>
                </select>
                <?php
                    $inputdata = array(
                        'id' => "shipment_referencenumber_$key",
                        'name' => "shipment_referencenumber[$key]",
                        'value' => $shipment->ReferenceNumber,
                        'style' => 'width: 200px;',
                    );
                    echo form_label('Reference number(s):', "shipment_referencenumber_$key", array('style' => 'width: 140px; margin-left: 20px;'));
                    echo form_input($inputdata);
                ?>
            </p>
            <p>
                <?php
                    $inputdata = array(
                        'id' => "shipment_numberofpackages_$key",
                        'name' => "shipment_numberofpackages[$key]",
                        'value' => $shipment->NumberOfPackages,
                        'maxlength' => 2,
                        'style' => 'width: 30px;',
                    );
                    echo form_label('Number of parcels:', "shipment_numberofpackages_$key", array('style' => 'width: 100px;'));
                    echo form_input($inputdata);

                    $inputdata = array(
                        'id' => "shipment_weight_$key",
                        'name' => "shipment_weight[$key]",
                        'value' => $shipment->Weight,
                        'style' => 'width: 60px;',
                    );
                    echo form_label('Weight:', "shipment_weight_$key", array('style' => 'width: 65px; margin-left: 20px;'));
                    echo form_input($inputdata);

                    $inputdata = array(
                        'id' => "shipment_postage_$key",
                        'name' => "shipment_postage[$key]",
                        'value' => $shipment->Postage,
                        'style' => 'width: 60px;',
                    );
                    echo form_label('Postage:', "shipment_postage_$key", array('style' => 'width: 65px; margin-left: 20px;'));
                    echo form_input($inputdata);

                ?>
            </p>
            <p>
                <?php

                    $inputdata = array(
                        'id' => "shipment_remarks_$key",
                        'name' => "shipment_remarks[$key]",
                        'value' => $shipment->Remarks,
                        'rows' => 2,
                        'cols' => 90,
                        'style' => 'font-family: arial; font-size: 10pt;',
                    );
                    echo form_label('Comments:', "shipment_remarks_$key", array('style' => 'width: 100px;'));
                    echo form_textarea($inputdata);

                ?>
            </p>
        </div>
        <?php endforeach; ?>
    </div>    
    <?php endif; ?>
    <?php if (isset($giftpreparations) && $giftpreparations): ?>
        <div id="giftpreparations">
            <h3>Gift preparations</h3>
            <div>
                <table>
                    <tr>
                        <th>Catalogue number</th>
                        <th>Prep. type</th>
                        <th>Taxon name</th>
                        <th>Quantity</th>
                        <th>Duplicates string</th>
                        <th>Quantity sent</th>
                        <th>Duplicates sent to</th>
                    </tr>
                    <?php foreach ($giftpreparations as $giftpreparation): ?>
                    <tr>
                        <td><?=$giftpreparation->CatalogNumber?></td>
                        <td><?=$giftpreparation->PrepType?></td>
                        <td><?=$giftpreparation->TaxonName?></td>
                        <td><?=$giftpreparation->Quantity?></td>
                        <td><?=$giftpreparation->DuplicateString?></td>
                        <td><?=$giftpreparation->QuantitySent?></td>
                        <td><?=$giftpreparation->DuplicatesSentTo?></td>
                    </tr>
                    <?php endforeach;?>
                </table>
            </div>
        </div>
    <?php endif; ?>
    <p style="text-align: right;"><?=form_submit('save', 'Save');?></p>
</div>
<?php endif; ?>
<?=form_close()?>


<?php require_once('views/footer.php'); ?>

