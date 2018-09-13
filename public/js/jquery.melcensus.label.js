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

var base_url = 'http://melisr.rbg.vic.gov.au/dev/';

$(function() {
    autocomplete(0);
    
    $(document).on('click', 'button#addLabel, button#cloneLabel', function() {
        addLabel($(this).attr('id'));
    });
    
    $(document).on('click', 'button.removeLabel.btn-primary', function() {
        $(this).parents('.cupboardLabel').eq(0).remove();
        if ($('.removeLabel').length === 1) {
            $('.removeLabel').each(function() {
                $(this).removeClass('btn-primary').addClass('btn-disabled');
            });
        }
    });
    
    $(document).on('click', 'button.printCupboardLabels, button.printStrawboardLabels', function( event ) {
        event.preventDefault();
        if ($(this).hasClass('printCupboardLabels')) {
            printLabels('cupboard');
        }
        else {
            printLabels('strawboard');
        }
    });
});

var autocomplete = function( index ) {
    $('[name=storage_group]:eq(' + index + ')').autocomplete({
        source: function( request, response ) {
            $.ajax({
                url: base_url + 'melcensus/autocomplete_storage_group',
                data: {
                    "term": request.term
                },
                success: function( data ) {
                    response(data);
                }
            });
        },
        minLength: 2,
        focus: function( event, ui ) {
            $('[name=storage_group]:eq(' + index + ')').val(ui.item.Name);
            return false;
        },
        select: function( event, ui ) {
            $('[name=storage_group]:eq(' + index + ')').val(ui.item.Name);
            $('[name=storage_id]:eq(' + index + ')').val(ui.item.StorageID);
            return false;
        },
        classes: {
            "ui-autocomplete": "melcensus-autocomplete"
        }
    })
    .autocomplete('instance')._renderItem = function( ul, item ) {
        return $('<li/>', {
            html: item.Name
        }).appendTo('ul');
    };
    
    $('tbody tr:eq(' + index + ') [name$=taxon_name]').autocomplete({
        source: function( request, response ) {
            $.ajax({
                url: base_url + 'melcensus/autocomplete_taxon_name',
                data: {
                    "storageId": $('[name=storage_id]:eq(' + index + ')').val(),
                    "term": request.term
                },
                success: function( data ) {
                    response(data);
                }
            });
        },
        classes: {
            "ui-autocomplete": "melcensus-autocomplete" 
        }
    });
    
};

var addLabel = function( action ) {
    var index = $('.cupboardLabel').length - 1;
    if ($('[name=storage_group]').eq(index).val() && $('[name$=taxon_name]').eq(index).val()) {
        var currentLabel = $('.cupboardLabel').eq(index);
        var newLabel = currentLabel.clone();
        if (action === 'addLabel' ) {
            newLabel.find('[name=storage_group]').val('');
            newLabel.find('[name=storage_id]').val('');
            newLabel.find('[name$=taxon_name]').val('');
            newLabel.find('[name=extra_info]').val('');
        }
        else {
            if (action === 'cloneLabel') {
                var selectedOption = currentLabel.find('[name=prep_type] option').index(currentLabel.find('[name=prep_type] option:selected'));
                newLabel.find('[name=prep_type] option').eq(selectedOption).prop('selected', true);
            }
        }
        newLabel.insertAfter(currentLabel);
        if ($('.removeLabel').length > 1) {
            $('.removeLabel').each(function() {
                $(this).removeClass('btn-disabled').addClass('btn-primary');
            });
        }
        autocomplete(index+1);
    }    
};

var printLabels = function(labelType) {
    var data = {};
    var labels = [];
    $('.cupboardLabel').each(function() {
        var label = {};
        label.storageGroup = $(this).find('[name=storage_group]').val();
        label.prepType = $(this).find('[name=prep_type] option:selected').val();
        if (labelType === 'cupboard') {
            label.taxonName = $(this).find('[name=taxon_name]').val();
            label.extraInfo = $(this).find('[name=extra_info]').val();
        }
        if (labelType === 'strawboard') {
            label.fromTaxonName = $(this).find('[name=from_taxon_name]').val();
            label.toTaxonName = $(this).find('[name=to_taxon_name]').val();
        }
        labels.push(label);
    });
    data.labels = labels;
    $('[name=data]').eq(0).val(JSON.stringify(data));
    $('form').submit();
};