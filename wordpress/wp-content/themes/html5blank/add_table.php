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

                <?php if (isset($_GET['table'])) {
                    echo "<h1>Edit table <code>" . $_GET['table'] . "</code></h1>"; ?>

                    <!-- delete table modal -->
                    <div class="modal fade" id="deleteTableModal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel">
                      <div class="modal-dialog" role="document">
                        <div class="modal-content panel-danger">
                          <div class="modal-header panel-heading">
                            <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                            <h4 class="modal-title"><i class="fa fa-times" aria-hidden="true"></i> Delete table</h4>
                          </div>
                          <div class="modal-body">
                            Are you sure you want to delete the table <code><?php echo $_GET['table']; ?></code>? <br><br><strong>Note:</strong> you cannot undo this.
                          </div>
                          <div class="modal-footer">
                            <a href="#" class="btn" data-dismiss="modal">Cancel</a>
                            <button type="button" class="btn btn-danger" id="confirmDeleteTable">Delete table</button>
                          </div>
                        </div>
                      </div>
                    </div>
                <?php } else {
                    echo '<h1>Add table</h1>';
                }?>

                <!-- article -->
                <article id="post-<?php the_ID(); ?>" <?php post_class(); ?>>

                    <form class="form-horizontal" onsubmit="return false;">
                      <div class="form-group">
                        <label for="inputEmail3" class="col-sm-2 control-label">Table name</label>
                        <div class="col-sm-2">
                          <input type="text" class="form-control" name="table_name" placeholder="samples" required pattern="[A-Za-z0-9-_]+" title="Only letters, numbers, underscores and dashes allowed (no spaces).">
                        </div>
                        <div class="col-sm-2">
                          <button type="button" class="btn btn-default btn-info" onclick="addField()" id="add_field" >Add field</button>
                        </div>
                        <div class="col-sm-offset-4 col-sm-2">
                          <?php if (isset($_GET['table'])) {
                            echo '<div class="pull-right btn-group"><button class="btn btn-success" onclick="editTable()">Save table</button>';
                            echo '<button class="btn btn-danger"><i class="fa fa-times" aria-hidden="true" onclick="deleteTableModal(\'' . $_GET['table'] . '\')"></i></button></div>';
                          } else {
                            echo '<button class="btn pull-right btn-success" onclick="addTable()">Create table</button>';
                          }?>
                        </div>
                      </div>
                      <input id="submit_handle" type="submit" style="display: none"> <!-- needed for validating form -->
                    </form>


                    <script  type="text/javascript">
                        var tmp = <?php echo get_db()->asJSON(); // send DB to javascript var ?>;
                        db = cleanDB(tmp);

                        jQuery(function() { // automatically add the first field
                            jQuery('#add_field').click();
                        });


                    </script>

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


</script>
