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

                <h1><?php the_title(); ?></h1>

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
