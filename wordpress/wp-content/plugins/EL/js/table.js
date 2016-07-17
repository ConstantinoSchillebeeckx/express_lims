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
    buttonHTML += '<button onclick="viewHistory()" type="button" class="btn btn-info btn-xs" id="history" title="History"><i class="fa fa-history" aria-hidden="true"></i></button>'
    buttonHTML += '<button onclick="editRow()" type="button" class="btn-xs btn btn-warning" id="edit" title="Edit"><i class="fa fa-pencil-square-o" aria-hidden="true"></i></button>'
    buttonHTML += '<button onclick="deleteRow()" type="button" class="btn-xs btn btn-danger" id="delete" title="Delete"><i class="fa fa-times" aria-hidden="true"></i></button>'
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
            },{ 
                "targets": columns, 
                "render": function (data, type, row ) { return data; }
            }
        ]
    } );
};



function deleteRow() {
    console.log('delete row');
}


function editRow() {
    console.log('edit row');
}


function viewHistory() {
    console.log('view history');
}
