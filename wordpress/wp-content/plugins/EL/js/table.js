/* 

AJAX call for retreiving DB data

When script is called on a page with a table that
has the ID 'datatable', AJAX will be used to query
the DB.

The function build_table() [in EL.php] is used to
build the HTML table needed to display the data
queried by AJAX.  Both must have the same columns.

Parameters (set by build_table()):
- table : table name to query
- columns : columns in table being queried
- pk : primary key of table
- filter : (optional) filter for table in format {col: val}
- hidden : (optional) array of column names that should be hidden (e.g. UID)
*/


function getData(table, columns, pk, filter, hidden) {


    // html for Action button column
    var buttonHTML = '<div class="btn-group" role="group">';
    buttonHTML += '<button onclick="historyModal(this)" type="button" class="btn btn-info btn-xs" title="History"><i class="fa fa-history" aria-hidden="true"></i></button>'
    buttonHTML += '<button onclick="editModal(this)" type="button" class="btn-xs btn btn-warning" title="Edit"><i class="fa fa-pencil-square-o" aria-hidden="true"></i></button>'
    buttonHTML += '<button onclick="deleteModal(this)" type="button" class="btn-xs btn btn-danger" title="Delete"><i class="fa fa-times" aria-hidden="true"></i></button>'
    buttonHTML += '</div>';

    jQuery.fn.dataTable.ext.errMode = 'throw'; // Have DataTables throw errors rather than alert() them

    console.log(table, columns, pk, filter, hidden);

    // variables set with build_table() defined in EL.php
    var data =  {
                "action": "viewTable", 
                "table": table, 
                "cols": columns,
                "pk": pk,
                "filter": filter,
                }

    var colDefs = [ { // https://datatables.net/examples/ajax/null_data_source.html
            // set Action column data to empty since we are automatically adding buttons here
                "targets": -1,
                "data": null,
                "defaultContent": buttonHTML,
                "width": "70px",
                "orderable": false,
            }]

    // hide any columns listed in hidden
    // also make them non-searchable
    if (hidden.length) {
        for (var i = 0; i < hidden.length; i++) {
            var idx = columns.indexOf(hidden[i]);
            colDefs.push({"targets": [ idx ], "visible": false, "searchable": false })
        }
    }

    jQuery('#datatable').DataTable( {
        "processing": true,
        "serverSide": true,
        "responsive": true,
        "ajax": {
            "url": ajax_object.ajax_url,
            "data": data,
            },
        "columnDefs": colDefs
    } );

};


/* Function called when action button delete clicked

Because none of the buttons have a specified ID, we
need to use some jQuery to figure out which button
was clicked and thus which row the user is trying
to act on.  This function will figure out the ID
of the first column item and update the modal with
its value.  It will then display the modal as well as
set the proper onclick event for the confirm delete button

Parameters:
- sel : will be the 'a' selection of the button that was clicked

*/
function deleteModal(sel) {

    // find first col value (PK) of row from button press
    var val = jQuery(sel).parents("tr").find(">:first-child").html()
    jQuery("#deleteID").html( "<code>" + val + "</code>" ); // set PK message
    jQuery('#deleteModal').modal('toggle'); // show modal
    jQuery("#confirmDelete").attr("onclick", "deleteItem('" + val + "')");
}


/* Function called when user confirms to delete an item

Function will make an AJAX call to the server to delete
the selected item.

Parameters:
- id : name displayed in the first column of the row that
       the user is requesting to delete.

*/
function deleteItem(id) {

    jQuery('#deleteModal').modal('toggle'); // hide modal
        
    var data = {
            "action": "deleteItem", 
            "id": id, 
            "table": table, // var set by build_table() in EL.php
            "pk": pk, // var set by build_table() in EL.php
    }

    // send data to server
    doAJAX(data);

}


/* Function called when action button history clicked

Because none of the buttons have a specified ID, we
need to use some jQuery to figure out which button
was clicked and thus which row the user is trying
to act on.  This function will figure out the ID
of the first column item and update the modal with
its value.  It will then display the modal.

Parameters:
- sel : will be the 'a' selection of the button that was clicked

*/
function historyModal(sel) {

    // find first col value (PK) of row from button press
    var val = jQuery(sel).parents("tr").find(">:first-child").html()
    jQuery("#historyID").html( "<code>" + val + "</code>" ); // set PK message
    jQuery('#historyModal').modal('toggle'); // show modal
}



/* Function called when action button 'edit' clicked

Because none of the buttons have a specified ID, we
need to use some jQuery to figure out which button
was clicked and thus which row the user is trying
to act on.  This function will figure out the ID
of the first column item and update the modal with
its value.  It will then display the modal as well as
set the proper onclick event for the confirm edit button

Parameters:
- sel : will be the 'a' selection of the button that was clicked

*/
function editModal(sel) {


    // find first col value (PK) of row from button press
    var tr = jQuery(sel).parents("tr");
    var rowIX = tr.index()
    var cellVal = tr.find('td:first').text();

    // get values from row and fill modal
    var dat = parseTable(rowIX);
    originalRowVals = dat; // set to global for comparison to edited values
    for (var col in dat) {
        var cell = dat[col];
        jQuery('#' + col).val(cell);
    }


    jQuery("#editID").html( "<code>" + cellVal + "</code>" ); // set PK message
    jQuery('#editModal').modal('toggle'); // show modal
    jQuery('#confirmEdit').focus();

    jQuery("#confirmEdit").attr("onclick", "editItem('" + cellVal + "')");
}


/* parse table into array object

Instead of querying the DB again, we parse the viewed
datatable in cases when the edit modal needs to be
filled in

Format : [{colname: value}, {colname: value}]

Paramters:
==========
- rowIX : int
          represents index (0-based) for row requested,
          otherwise returns all rows

Returns:
========
- obj with column names as keys and row values as value


*/
function parseTable(rowIX) {

    var table = jQuery('#datatable').DataTable();
    var colData = table.columns().nodes();
    //need to use this so that we can grab UID (hidden field)

    var dat = {};
    table.columns().every(function(i) { 
        var col = this.header().textContent;
        var cellVal = colData[i][rowIX].textContent;
        if (cellVal) {
            dat[col] = cellVal;
        }
    })

    return dat;

}





/* Function called when add row button history clicked

Parameters:
- sel : will be the 'a' selection of the button that was clicked

*/
function addItemModal(sel) {
    jQuery('#addItemModal').modal('toggle'); // show modal
}





/* Function handles form submission from add item modal

When the 'Add item' button is clicked from the modal,
this function makes an AJAX call to the server to add
the item to the DB.

*/
function addItem() {

    event.preventDefault(); // cancel form submission

    var data = {
            "action": "addItem", 
            "table": table, // var set by build_table() in EL.php
            "pk": pk, // var set by build_table() in EL.php
            "dat": getFormData('#addItemForm'), // form values
    }

    console.log(data)
 
    // send data to server
    doAJAX(data);
    
    jQuery('#addItemModal').modal('toggle'); // hide modal

}




/* Function called when use confirms to edit an item

Function will make an AJAX call to the server to delete
the selected item.

Parameters:
- id : name displayed in the first column of the row that
       the user is requesting to delete.

*/
function editItem(id) {

    event.preventDefault(); // cancel form submission

    var data = {
            "action": "editItem", 
            "table": table, // var set by build_table() in EL.php
            "pk": pk, // var set by build_table() in EL.php
            "original_row": originalRowVals, // var set in editModal()
            "dat": {}, // form values
    }

    var formData = jQuery('#editItemForm').serializeArray(); // form data
    jQuery.each(formData, function() {
        data.dat[this.name] = this.value;
    })

    // send data to server
    doAJAX(data);

    jQuery('#editModal').modal('toggle'); // hide modal
}




/* Send AJAX request to sever

Will send an AJAX request to the server and properly show/log
the response as either a message to the user or an error
message in the console.

Paramters:
----------
- data : obj
         data object to send to the server

Returns:
--------
- will return true on success, false otherwise

*/
function doAJAX(data) {

    var ajaxStatus;

    // send via AJAX to process with PHP
    jQuery.ajax({
            url: ajax_object.ajax_url, 
            type: "GET",
            data: data, 
            dataType: 'json',
            success: function(response) {
                if (jQuery('#datatable').length) {
                    jQuery('#datatable').DataTable().draw('page'); // refresh table
                }
                console.log(response);

                // disable autohide of message if certain type of error
                if (response.msg.indexOf('that you are trying to delete is referenced as a foreign key') > -1) {
                    showMsg(response, true);
                } else {
                    showMsg(response);
                }

                ajaxStatus = response['status'];
            },
            error: function(xhr, status, error) {
                console.log(xhr.responseText);
                showMsg({"msg":"There ws an error, please try again.", "status": false});
                ajaxStatus = false;
            }
    });

    return ajaxStatus;
}




// activate modals
jQuery(document).ready(function($) {
    jQuery('#historyModal').modal({ show: false })
    jQuery('#editModal').modal({ show: false})
    jQuery('#deleteModal').modal({ show: false})
    jQuery('#addItemModal').modal({ show: false})
})







/* Catch AJAX response and show message if needed

Will generate a dismissable alert div at the top 
of the page which will hide after 3 seconds

Parameters:
===========
- dat : object
        -> msg : msg to display
        -> status : bool - true if success msg, false if error msg
- disableTimer : bool (optional)
            if true, message will not automatically be removed, defaults to false (3s timer)
*/
function showMsg(dat, disableTimer=false) {

    var type = dat.status ? 'success' : 'danger';
    var msg = dat.msg;
    var alertDiv = '<div id="alertDiv" class="alert alert-' + type + ' alert-dismissible" role="alert"><button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>' + msg + '</div>';

    jQuery( alertDiv ).prependTo( "main" );

    // automatically hide msg after 3s
    var timeout = setTimeout(function () {
        jQuery(".alert").fadeTo(2000, 500).slideUp(500, function ($) {
            jQuery(".alert").remove();
        });
    }, 3000);

    if (disableTimer) {
        clearTimeout(timeout);
    }

}

var fieldNum = 0;
/* Function called by "Add field" button on add table template page
Will add all the necessarry GUI fields for defining a given field
*/
function addField() {
    fieldNum += 1;
    var dom = ['<div class="panel panel-default" style="margin-bottom:40px;" id="field-' + fieldNum + '">',
            '<div class="panel-heading">',
            '<h3 class="panel-title">Field #' + fieldNum + '</h3>',
            '</div>',
            '<div class="panel-body">',
            '<div class="form-group">',
            '<label class="col-sm-2 control-label" id="fieldLabel">Field name*</label>',
            '<div class="col-sm-3">',
            '<input type="text" class="form-control" name="name-' + fieldNum + '" required pattern="[a-zA-Z0-9 ]+" title="Letters, numbers and spaces only">',
            '</div>',
            '<label class="col-sm-1 control-label">Type</label>',
            '<div class="col-sm-2">',
            '<select class="form-control" onChange="selectChange(' + fieldNum + ')" id="type-' + fieldNum + '" name="type-' + fieldNum + '" required>',
            '<option value="" disabled selected style="display:none;">Choose</option>',
            '<option value="varchar">String</option>',
            '<option value="int">Integer</option>',
            '<option value="float">Float</option>',
            '<option value="date">Date</option>',
            '<option value="timestamp">Date & Time</option>',
            '<option value="fk">Foreign</option>',
            '</select>',
            '</div>',
            '</div>',
            '<div class="form-group">',
            '<label class="col-sm-2 control-label" id="fieldLabel">Default value</label>',
            '<div class="col-sm-3">',
            '<input type="text" class="form-control" name="default-' + fieldNum + '" pattern="[a-zA-Z0-9 ]+" title="Letters, numbers and spaces only">',
            '</div>',
            '<div class="col-sm-offset-1 col-sm-6" id="hiddenType-' + fieldNum + '">',
            '</div>',
            '</div>',
            '<div class="form-group">',
            '<label class="col-sm-2 control-label">Required</label>',
            '<div class="col-sm-3">',
            '<label class="checkbox-inline">',
            '<input type="checkbox" name="required-' + fieldNum + '"> check if field is required',
            '</label>',
            '</div>',
            '<label class="col-sm-1 control-label">Unique</label>',
            '<div class="col-sm-3">',
            '<label class="checkbox-inline">',
            '<input type="checkbox" name="unique-' + fieldNum + '"> check if field is unique',
            '</label>',
            '</div>',
            '</div>',
            '</div>',
            '</div>']
    jQuery("form").append(dom.join('\n'));
}

// hide/show divs based on what user selects for field type
function selectChange(id){
    var val = jQuery("#type-" + id).val()

    var hidden = jQuery("#hiddenType-" + id);
    if (val == 'fk') {
        var html = '<p>Text for foreign key</p>';
        html += getFKchoices(id);
        hidden.html(html);
    } else if (val == 'date') {
        html = '<span>Text for date field</span><br>';
        html +='<input type="checkbox" clas="form-control" name="currentDate-' + id + '"> check if you want this field automatically filled with the date.';
        hidden.html(html);
    } else if (val == 'varchar') {
        hidden.html('<p>Text for string field</p>');
    } else if (val == 'int') {
        hidden.html('<p>Text for integer field</p>');
    } else if (val == 'float') {
        hidden.html('<p>Text for float field</p>');
    } else if (val == 'timestamp') {
        html = '<span>Text for timestamp field</span><br>';
        html +='<input type="checkbox" clas="form-control" name="currentDate-' + id + '"> check if you want this field automatically filled with the date & time.';
        hidden.html(html);
    }
}


/* Function called when "Create table" button is clicked
*/
function addTable() {
    
    event.preventDefault(); // cancel form submission

    var data = {
            "action": "addTable", 
            "dat": getFormData('form'), // form values
            "field_num": jQuery('[id^=field-]').length // number of fields
    }
    console.log(data);



    // ensure table doesn't exist
    // global var db is set in the add_table WP template
    // NOTE: protected attributes will have a prepended '*' in the key, see:
    // https://ocramius.github.io/blog/fast-php-object-to-array-conversion/
    var tables = db['\0*\0tables'];
    for (var i = 0; i < tables.length; i++) {
        var table = tables[i];
        var table_safe = table.split('_')[1];
        if (table_safe.toLowerCase() == data.dat.table_name.toLowerCase()) {
            alert('need an error message here about table existing'); // TODO
        }
    }

    var names = [];
    for (var i = 1; i <= data.field_num; i++ ) {

        // sensure field names are unique
        var field = 'name-' + i;
        var name = data.dat[field];
        if (names.indexOf(name) > -1) { // name not unique
            alert('need an error message here about field names not being unique'); // TODO
        }        
        names.push(name);

        // check that default value matches with field type
        var defaultVal = data.dat['default-' + i];
        if (defaultVal) {
            var type = data.dat['type-' + i];
            if ( type == 'float' && !(isFloat(defaultVal) || isInt(defaultVal)) ) {
                alert("need an error message here about default value not being a float");
            } else if ( type == 'int' && !isInt(defaultVal) ) {
                alert("need an error message here about default value not being an int");
            }
        }
    }

 
    // send data to server
    var ret = doAJAX(data);

    // TODO add table to dropdown 'view' list manually
    // ret still not returning proper value due to synchronous processing
    console.log('ret',ret);

}

// http://stackoverflow.com/a/3886106/1153897
// NOTE: will return true when checking '1'
function isInt(n){
    return Number(n) === parseInt(n) && parseInt(n) % 1 === 0;
}

// http://stackoverflow.com/a/3886106/1153897
// NOTE: will return true when checking '1.1'
function isFloat(n){
    return Number(n) === parseFloat(n) && parseFloat(n) % 1 !== 0;
}



/* Will parse form on page into obj for use with AJAX

Parameters:
===========
- sel : str
        selector for form (e.g. form)

Returns:
========
- obj : will with form input field values

*/
function getFormData(sel) {

    var data = {};

    var formData = jQuery(sel).serializeArray(); // form data

    jQuery.each(formData, function() {
        var val = this.value;
        if (val != '') { // don't capture empty fields
            if (val == 'on') {
                val = true;
            }
            data[this.name] = val;
        }
    })

    return data;

}






/* Generate a dropdown of possible tables/fields for FK

When setting up a table, a field can be chosen to be
a foreign key; this will generate a drop down select
filled with table name and field name from which to
choose as a reference for the FK

Parameters:
-----------
- id : int (optional)
       if specified, select will get the name 'foreignKey-#',
       otherwise name will be 'foreignKey'

Returns:
--------
- full html for select


*/
function getFKchoices(id=null) {

    // global var db is set in the add_table WP template
    // NOTE: protected attributes will have a prepended '*' in the key, see:
    // https://ocramius.github.io/blog/fast-php-object-to-array-conversion/
    var tables = db['\0*\0tables'];
    var struct = db['\0*\0struct'];

    var name = 'foreignKey';
    if (id) {
        name += '-' + id;
    }

    var html = '<select class="form-control" name="' + name + '" required>';
    for (var i = 0; i < tables.length; i++) {
        var table = tables[i];
        var tableSafe = table.split('_')[1]; // remove company name
        var tableStruct = struct[table];
        var fields = tableStruct['\0*\0fields'];

        for (var j = 0; j < fields.length; j++) {
            var val = tableSafe + '.' + fields[j];
            var label = 'Table: ' + tableSafe + ' | Field: ' + fields[j];
        
            html += '<option value="' + val + '">' + label + '</option>';
        }

    }
    html += '</select>';

    return html;

}

















