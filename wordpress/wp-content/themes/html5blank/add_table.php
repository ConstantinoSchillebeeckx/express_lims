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
                          <input type="text" class="form-control" id="table_name" placeholder="samples" required>
                        </div>
                      </div>
                      <hr>
                      <div class="form-group" id="addFieldBtnGrp">
                        <div class="col-sm-offset-2 col-sm-10">
                          <button type="button" class="btn btn-default btn-info" onclick="addField()" id="add_field" >Add field</button>
                          <button type="submit" class="btn btn-default pull-right btn-success" onclick="addTable()">Create table</button>
                        </div>
                      </div>
                    </form>
                    
                    <script>
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
