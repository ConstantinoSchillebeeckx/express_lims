/* 

AJAX call for retreiving DB data

When script is called on a page with a table that
has the ID 'datatable', AJAX will be used to query
the DB.

The function build_table() [in EL.php] is used to
build the HTML table needed to display the data
queried by AJAX.  Both must have the same columns.

Parameters:
- table : table name to query
- columns : columns in table being queried
- pk : primary key of table
*/


function getData(table, columns, pk) {

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


/* Function called when use confirms to delete an item

Function will make an AJAX call to the server to delete
the selected item.

Parameters:
- id : name displayed in the first column of the row that
       the user is requesting to delete.

*/
function deleteItem(id) {
    console.log("delete",id);
    jQuery('#deleteModal').modal('toggle'); // hide modal
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
})






