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

var base_url = 'http://melisr.rbg.vic.gov.au';
var taxa;

$(function() {
    getSubgroups();
    
    $('body').on('change', 'select#major-group', function(e) {
        getSubgroups();
    });
    
    $('body').on('change', 'select#subgroup', function(e) {
        getStorageTaxa();
    });
    
    $('body').on('click', 'table [type=checkbox]', function() {
        return false;
    }).on('keydown', 'table [type=checkbox]', function() {
        return false;
    });
    
    $('body').on('click', '[name="source[]"]', function() {
        showTaxa(taxa, getSource(), getArea());
    }).on('click', '[name="area[]"]', function() {
        showTaxa(taxa, getSource(), getArea());
    });
    
});

var getSubgroups = function() {
    var majorGroupId = $('#major-group').val();
    $.ajax({
        "url": base_url + '/melcensus/subgroups/' + majorGroupId,
        "success": function(data) {
            $('#subgroup option').remove();
            var select = $('#subgroup');
            
            $.each(data, function(index, item) {
                $('<option/>', {
                    value: item.StorageID,
                    html: item.Name
                }).appendTo(select);
            });
            
            getStorageTaxa();

        }
    });
};

var getSource = function() {
    if ($('[name="source[]"]:checked').length === 1) {
        return $('[name="source[]"]:checked').val();
    }
    else {
        return false;
    }
};

var getArea = function() {
    if ($('[name="area[]"]:checked').length === 1) {
        return $('[name="area[]"]:checked').val();
    }
    else {
        return false;
    }
};

var getStorageTaxa = function() {
    $('#storageTaxa tbody tr').remove();
    var storageId = $('#subgroup').val();
    $.ajax({
        url: base_url + '/melcensus/taxa/' + storageId,
        success: function(resp) {
            taxa = resp.data;
            showTaxa(taxa, getSource(), getArea());
        }
    });
};

var tableHeader = function(area) {
    var header = $('#storageTaxa thead');
    header.children('tr').remove();
    var firstRow = $('<tr/>').appendTo(header);
    $('<th/>', {
        "rowspan": "2",
        "html": "Taxon name"
    }).appendTo(firstRow);
    $('<th/>', {
        "rowspan": "2",
        "html": "Source"
    }).appendTo(firstRow);
    if (!area || area === "au") {
        $('<th/>', {
            "colspan": "7",
            "html": "Australian"
        }).appendTo(firstRow);
    }
    if (!area || area === "f") {
        $('<th/>', {
            "colspan": "7",
            "html": "Foreign"
        }).appendTo(firstRow);
    }
    
    var secondRow = $('<tr/>').appendTo(header);
    if (!area || area === "au") {
        $('<th/>', {"html": "Type"}).appendTo(secondRow);
        $('<th/>', {"html": "Sh."}).appendTo(secondRow);
        $('<th/>', {"html": "Pckt"}).appendTo(secondRow);
        $('<th/>', {"html": "Carp."}).appendTo(secondRow);
        $('<th/>', {"html": "Sp."}).appendTo(secondRow);
        $('<th/>', {"html": "Unm."}).appendTo(secondRow);
        $('<th/>', {"html": "Cult."}).appendTo(secondRow);
    }
    if (!area || area === "f") {
        $('<th/>', {"html": "Type"}).appendTo(secondRow);
        $('<th/>', {"html": "Sh."}).appendTo(secondRow);
        $('<th/>', {"html": "Pckt"}).appendTo(secondRow);
        $('<th/>', {"html": "Carp."}).appendTo(secondRow);
        $('<th/>', {"html": "Sp."}).appendTo(secondRow);
        $('<th/>', {"html": "Unm."}).appendTo(secondRow);
        $('<th/>', {"html": "Cult."}).appendTo(secondRow);
    }
};

var showTaxa = function(taxa, source, area) {
    var data = taxa;
    if (source) {
        var path = '.{.' + source + '}';
        data = JSPath.apply(path, taxa);
    }
    if (area) {
        var areaUpper = area.toUpperCase();
        path = '.{..' + areaUpper + '.total > 0}';
        data = JSPath.apply(path, taxa);
    }
    tableHeader(area);
    var table = $('#storageTaxa tbody');
    table.children('tr').remove();
    $.each(data, function(index, item) {
        if (typeof item.melisr !== "undefined" && (!source || source === "melisr")) {
            var tr = $('<tr/>').appendTo(table);
            var name = "<i>" + item.FullName + "</i>";
            if (item.Author) {
                name += ' ' + item.Author;
            }
            if (item.acceptedName) {
                name += '<br/>= ' + item.acceptedName;
            }

            var td = $('<td/>', {
                html: name
            }).appendTo(tr);
            if (typeof item.census !== "undefined" && (!source || source === "census")) {
                td.prop("rowspan", "2");
            }

            $('<td/>', {
                html: "MELISR"
            }).appendTo(tr);
            
            if (area === false || area === "au") {
                var prepTypes = ['AT', 'AM', 'APkt', 'AC', 'ASp', '', 'ACult'];
                $.each(prepTypes, function(index, type) {
                    var td = $('<td/>').appendTo(tr);
                    if (type.length > 0) {
                        if (item.melisr.AU[type] > 0) {
                            td.html(item.melisr.AU[type]);
                        }
                        else {
                            td.html('-');
                        }
                    }
                });
            }
            if (area === false || area === "f") {
                var prepTypes = ['FT', 'FM', 'FPkt', 'FC', 'FSp', '', 'FCult'];
                $.each(prepTypes, function(index, type) {
                    var td = $('<td/>').appendTo(tr);
                    if (type.length > 0) {
                        if (item.melisr.F[type] > 0) {
                            td.html(item.melisr.F[type]);
                        }
                        else {
                            td.html('-');
                        }
                    }
                });
            }
        }
        if (typeof item.census !== "undefined" && (!source || source === "census")) {
            var tr = $('<tr/>').appendTo(table);

            if (typeof item.melisr === "undefined" || source === "census") {
                var name = "<i>" + item.FullName + "</i>";
                if (item.Author) {
                    name += ' ' + item.Author;
                }
                if (item.acceptedName) {
                    name += '<br/>= ' + item.acceptedName;
                }

                var td = $('<td/>', {
                    html: name
                }).appendTo(tr);
            }

            $('<td/>', {
                html: "census"
            }).appendTo(tr);

            if (area === false || area === "au") {
                var prepTypes = ['AT', 'AM', 'APkt', 'AC', '', 'AUnm', 'ACult'];
                $.each(prepTypes, function(index, type) {
                    var td = $('<td/>').appendTo(tr);
                    if (type.length > 0) {
                        var checkbox = $('<input/>', {type: "checkbox"}).appendTo(td);
                        if (item.census.AU[type] > 0) {
                            checkbox.prop("checked", true);
                        }
                    }
                });
            }
            if (area === false || area === "f") {
                var prepTypes = ['FT', 'FM', 'FPkt', 'FC', '', 'FUnm', 'FCult'];
                $.each(prepTypes, function(index, type) {
                    var td = $('<td/>').appendTo(tr);
                    if (type.length > 0) {
                        var checkbox = $('<input/>', {type: "checkbox"}).appendTo(td);
                        if (item.census.F[type] > 0) {
                            checkbox.prop("checked", true);
                        }
                    }
                });
            }
        }

    });
};


