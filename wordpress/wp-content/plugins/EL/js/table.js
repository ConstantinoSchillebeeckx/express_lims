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
*/


function getData(table, columns, pk, filter) {


    // html for Action button column
    var buttonHTML = '<div class="btn-group" role="group">';
    buttonHTML += '<button onclick="historyModal(this)" type="button" class="btn btn-info btn-xs" title="History"><i class="fa fa-history" aria-hidden="true"></i></button>'
    buttonHTML += '<button onclick="editModal(this)" type="button" class="btn-xs btn btn-warning" title="Edit"><i class="fa fa-pencil-square-o" aria-hidden="true"></i></button>'
    buttonHTML += '<button onclick="deleteModal(this)" type="button" class="btn-xs btn btn-danger" title="Delete"><i class="fa fa-times" aria-hidden="true"></i></button>'
    buttonHTML += '</div>';


    jQuery('#datatable').DataTable( {
        "processing": true,
        "serverSide": true,
        "ajax": {
            "url": ajax_object.ajax_url,
            "data": {
                "action": "viewTable", 
                "table": table, 
                "cols": columns,
                "pk": pk,
                "filter": filter,
                },
            },
        "columnDefs": [
            // set Action column data to empty since we are automatically adding buttons here
            { // https://datatables.net/examples/ajax/null_data_source.html
                "targets": -1,
                "data": null,
                "defaultContent": buttonHTML,
                "width": "20px",
            }
        ]
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

    // send via AJAX to process with PHP
    jQuery.ajax({
            url: ajax_object.ajax_url, 
            type: "GET",
            data: data, 
            dataType: 'json',
            success: function(response) {
                jQuery('#datatable').DataTable().draw('page');
                showMsg(response);
            },
            error: function(xhr, status, error) {
                console.log(xhr.responseText);
                showMsg({"msg":"There ws an error, please try again.", "status": false});
            }
    });

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
    var val = jQuery(sel).parents("tr").find(">:first-child").html()
    jQuery("#editID").html( "<code>" + val + "</code>" ); // set PK message
    jQuery('#editModal').modal('toggle'); // show modal
    jQuery("#confirmEdit").attr("onclick", "editItem('" + val + "')");
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
    // cancels the form submission
    event.preventDefault();

    jQuery('#addItemModal').modal('toggle'); // hide modal

    var formData = jQuery('#addItemForm').serialize(); // form data
    // TODO reformat formData var to be better useable (currently str)


    // TODO when should we do error checking (e.g. foreign key, unique)
    // best place would be before AJAX call, within the modal ...

    return;

    var data = {
            "action": "addItem", 
            "id": id, 
            "table": table, // var set by build_table() in EL.php
            "pk": pk, // var set by build_table() in EL.php
    }
    
    // send via AJAX to process with PHP
    jQuery.ajax({
            url: ajax_object.ajax_url, 
            type: "GET",
            data: data, 
            dataType: 'json',
            success: function(response) {
                jQuery('#datatable').DataTable().draw('page');
                showMsg(response);
            },
            error: function(xhr, status, error) {
                console.log(xhr.responseText);
                showMsg({"msg":"There ws an error, please try again.", "status": false});
            }
    });

}




/* Function called when use confirms to delete an item

Function will make an AJAX call to the server to delete
the selected item.

Parameters:
- id : name displayed in the first column of the row that
       the user is requesting to delete.

*/
function editItem(id) {
    console.log("edit",id);
    jQuery('#editModal').modal('toggle'); // hide modal
}


// activate modals
jQuery(document).ready(function($) {
    jQuery('#historyModal').modal({ show: false })
    jQuery('#editModal').modal({ show: false})
    jQuery('#deleteModal').modal({ show: false})
    jQuery('#addItemModal').modal({ show: false})
})







/* Catch AJAX response and show message if needed

Parameters:
===========
- dat : object
        -> msg : msg to display)
        -> status : bool - true if success msg, false if error msg

*/
function showMsg(dat) {

    var type = dat.status ? 'success' : 'danger';
    var msg = dat.msg;
    var alertDiv = '<div id="alertDiv" class="alert alert-' + type + ' alert-dismissible" role="alert">' + msg + '</div>';

    jQuery( alertDiv ).prependTo( "main" );

    // automatically hide msg after 3s
    setTimeout(function () {
        jQuery("#alertDiv").fadeTo(2000, 500).slideUp(500, function ($) {
            jQuery("#alert_div").remove();
        });
    }, 3000);
}


