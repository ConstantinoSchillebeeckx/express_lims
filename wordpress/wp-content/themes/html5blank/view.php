<?php 
/**
 * Template Name: View template
 *
 * @package WordPress
 */
get_header(); ?>

	<main role="main">
		<!-- section -->
		<section>

            <?php if ( is_user_logged_in() ) { ?>

                <div class="row">
                    <div class="col-sm-8">
                        <h1><?php echo isset($_GET['table']) ? "Viewing table <code>" . $_GET['table'] . "</code>" : the_title(); ?></h1>
                    </div>
                    <div class="col-sm-4" style="margin-top:20px">
                        <div class="btn-group pull-right" role="group" aria-label="...">
                            <button type="button" title="Add item to table" class="btn btn-info" onclick="addItemModal()"><i class="fa fa-plus fa-2x" aria-hidden="true"></i></button>
                            <button type="button" title="Download table as CSV" class="btn btn-info" onclick="downloadCSV(<?php echo "'" . $_GET['table'] . "'" ?>)"><i class="fa fa-cloud-download fa-2x" aria-hidden="true"></i></button>
                            <button type="button" title="Delete table" class="btn btn-danger" onclick="deleteTableModal(<?php echo "'" . $_GET['table'] . "'" ?>)"><i class="fa fa-times fa-2x" aria-hidden="true"></i></button>
                            <a href="<?php echo ADD_TABLE_URL_PATH . '?table=' . $_GET['table']; ?>" title="Modify table setup" class="btn btn-warning"><i class="fa fa-cog fa-2x" aria-hidden="true"></i></a>
                        </div>
                    </div>
                </div>

                <!-- article -->
                <article id="post-<?php the_ID(); ?>" <?php post_class(); ?>>

                    <?php 
                        build_table(); // funtion is defined in plugin EL.php 
                    ?>

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
