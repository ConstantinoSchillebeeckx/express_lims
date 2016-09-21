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
                            echo '<div class="pull-right btn-group"><button type="button" class="btn btn-success" onclick="editTable()">Save changed</button>';
                          } else {
                            echo '<button type="button" class="btn pull-right btn-success" onclick="addTable()">Create table</button>';
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
