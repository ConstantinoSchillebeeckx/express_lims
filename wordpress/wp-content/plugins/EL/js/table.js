/* 

AJAX call for retreiving DB data

When script is called on a page with a table that
has the ID 'datatable', AJAX will be used to query
the DB.

The function build_table() [in EL.php] is used to
build the HTML table needed to display the data
queried by AJAX.  Both must have the same columns.

*/


function getData(table, columns) {
    jQuery('#datatable').DataTable( {
        "processing": true,
        "serverSide": true,
        "ajax": {
            "url": ajax_object.ajax_url,
            "data": {
                "action": "viewTable", 
                "table": table, 
                "columns": columns,
                },
            }
    } );
};
