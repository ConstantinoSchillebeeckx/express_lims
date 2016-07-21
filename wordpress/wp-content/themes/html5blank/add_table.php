<?php 
/**
 * Template Name: Add table template
 *
 * @package WordPress
 */
get_header(); ?>

	<main role="main">
		<!-- section -->
		<section>

            <?php if ( is_user_logged_in() ) { ?>

                <h1><?php the_title(); ?></h1>

                <!-- article -->
                <article id="post-<?php the_ID(); ?>" <?php post_class(); ?>>

                    <form class="form-horizontal" onsubmit="return false;">
                      <div class="form-group">
                        <label for="inputEmail3" class="col-sm-2 control-label">Table name</label>
                        <div class="col-sm-2">
                          <input type="text" class="form-control" id="table_name" placeholder="samples">
                        </div>
                      </div>
                      <hr>
                      <div class="form-group" id="addFieldBtnGrp">
                        <div class="col-sm-offset-2 col-sm-10">
                          <button type="submit" class="btn btn-default btn-info" onclick="addField()">Add field</button>
                          <button type="submit" class="btn btn-default pull-right btn-success" onclick="addTable()">Create table</button>
                        </div>
                      </div>
                    </form>


                </article>
                <!-- /article -->

            <?php } else { ?>

                <?php EL_not_logged_in(); ?>

            <?php } ?>

		</section>
		<!-- /section -->
	</main>

<?php //get_sidebar(); ?>

<?php get_footer(); ?>


<script>

var fieldNum = 0;



/* Function called by "Add field" button on add table template page

Will add all the necessarry GUI fields for defining a given field

*/
function addField() {

    fieldNum += 1;

    var dom = ['<div style="margin-bottom:40px;" id="field-"' + fieldNum + '"</div>',
            '<div class="form-group">',
            '<label class="col-sm-2 control-label" id="fieldLabel">Field ' + fieldNum + '</label>',
            '<div class="col-sm-2">',
            '<input type="text" class="form-control" name="name-' + fieldNum + '" placeholder="name">',
            '</div>',
            '</div>',
            '<div class="form-group">',
            '<label class="col-sm-2 control-label" id="fieldLabel">Default value</label>',
            '<div class="col-sm-2">',
            '<input type="text" class="form-control" name="default-' + fieldNum + '">',
            '</div>',
            '</div>',
            '<div class="form-group">',
            '<label class="col-sm-2 control-label">Unique</label>',
            '<div class="col-sm-2">',
            '<label class="checkbox-inline">',
            '<input type="checkbox" name="unique-' + fieldNum + '" value=true> check if field is unique',
            '</label>',
            '</div>',
            '</div>',
            '<div class="form-group">',
            '<label class="col-sm-2 control-label">Required</label>',
            '<div class="col-sm-6">',
            '<label class="checkbox-inline">',
            '<input type="checkbox" name="required-' + fieldNum + '" value=true> check if field is required',
            '</label>',
            '</div>',
            '</div>',
            '<div class="form-group">',
            '<label class="col-sm-2 control-label">Type</label>',
            '<div class="col-sm-2">',
            '<select class="form-control" onChange="selectChange(' + fieldNum + ')" id="type-' + fieldNum + '" name="type-' + fieldNum + '">',
            '<option value="" disabled selected style="display:none;">Choose</option>',
            '<option value="varchar">String</option>',
            '<option value="int">Integer</option>',
            '<option value="float">Float</option>',
            '<option value="date">Date</option>',
            '<option value="timestamp">Date & Time</option>',
            '<option value="fk">Foreign</option>',
            '</select>',
            '</div>',
            '<div class="col-sm-8" id="hiddenType-' + fieldNum + '">',
            '</div>',
            '</div>',
            '</div>']

    jQuery("form").append(dom.join('\n'));


}


// hide/show divs based on what user selects for field type
function selectChange(id){
    var val = jQuery("#type-" + id).val()
    console.log(id, val);

    var hidden = jQuery("#hiddenType-" + id);

    if (val == 'fk') {
        hidden.html('<select class="form-control" name="foreignKey-' + id + '"></select>');
    } else if (val == 'date') {
        hidden.html('<input type="checkbox" clas="form-control" name="currentDate-' + id + '" value=true> check if you want this field automatically filled with the current date.');
    } else {
        hidden.html('');
    }

}




/* Function called when "Create table" button is clicked


*/
function addTable() {
    
    // do some error checking
    // table must have at least one unique field (PK)
    console.log('add table')

}


</script>
