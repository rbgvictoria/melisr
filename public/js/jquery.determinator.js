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

$(function() {
    
    autocomplete();
    
    $(document).on('click', 'button.addDetslip, button.cloneDetslip', 
            function(event) {
        var detslip = $(this).parents('.detslip').eq(0);
        var newDetslip = detslip.clone();
        var remove = $('<button/>', {
            "type": "button",
            "class": "btn btn-primary removeDetslip",
            "html": '<i class="fa fa-trash fa-lg"></i>'
        });
        if (newDetslip.find('.removeDetslip').length === 0) {
            remove.insertBefore(newDetslip.find('.cloneDetslip').eq(0));
            remove.after(' ');
        }
        if ($(this).hasClass('addDetslip')) {
            newDetslip.find('[name=taxonName]').val('');
            newDetslip.find('[name=taxon]').val('');
            newDetslip.find('[name=note]').val('');
            newDetslip.find('[name=identifierRole]').val('Det.');
            newDetslip.find('[name=identifiedBy]').val('');
            newDetslip.find('[name=agent]').val('');
            
            var d = new Date();
            newDetslip.find('[name=day]').val(d.getDate());
            newDetslip.find('[name=month]').val(d.getMonth() + 1);
            newDetslip.find('[name=year]').val(d.getFullYear());
            
            newDetslip.find('[name=number]').val("1");
        }
        newDetslip.insertAfter(detslip);
        autocomplete();
    });
    
    $(document).on('click', 'button.removeDetslip', function(event) {
        var detslip = $(this).parents('.detslip').eq(0);
        detslip.remove();
    });
    
    $(document).on('click', 'button.printDetslips', function(event) {
        event.preventDefault();
        var data = {};
        data.start = $('[name=start]').val();
        var detslips = [];
        $('.detslip').each(function() {
            var detslip = {};
            detslip.taxonID = $(this).find('[name=taxon]').val();
            detslip.blankTaxonNameAllowed = $(this).find('[name=allowBlankTaxonName]').prop('checked');
            detslip.identifierRole = $(this).find('[name=identifierRole]').val();
            detslip.identifiedByID = $(this).find('[name=agent]').val();
            detslip.day = $(this).find('[name=day]').val();
            detslip.month = $(this).find('[name=month]').val();
            detslip.year = $(this).find('[name=year]').val();
            detslip.note = $(this).find('[name=note]').val();
            detslip.number = $(this).find('[name=number]').val();
            detslips.push(detslip);
            console.log(detslip);
        });
        data.detslips = detslips;
        $('[name=data]').eq(0).val(JSON.stringify(data));
        $('form').submit();
    });
});

var autocomplete = function() {
    $('[name=taxonName]').autocomplete({
        source: location.href + '/taxon_name_autocomplete',
        minLength: 2,
        focus: function( event, ui ) {
            $(this).val( ui.item.label );
            return false;
        },
        select: function( event, ui ) {
            $(this).val( ui.item.label );
            $(this).next('[type=hidden]').val( ui.item.value );
            return false;
        }
    })
    .autocomplete().data("uiAutocomplete")._renderItem = function(ul, item) {
        ul.addClass('taxon-name-autocomplete-list');
        return $( "<li>" )
            .append( "<a>" + item.label + "</a>" )
            .appendTo( ul );
    };

    $('[name=identifiedBy]').autocomplete({
        source: location.href + '/agent_autocomplete',
        minLength: 2,
        focus: function( event, ui ) {
            $(this).val( ui.item.label );
            return false;
        },
        select: function( event, ui ) {
            $(this).val( ui.item.label );
            $(this).next('[type=hidden]').val( ui.item.value );
            return false;
        }
    })
    .autocomplete().data("uiAutocomplete")._renderItem = function(ul, item) {
        ul.addClass('agent-autocomplete-list');
        return $( "<li>" )
            .append( "<a>" + item.label + "</a>" )
            .appendTo( ul );
    };
};




