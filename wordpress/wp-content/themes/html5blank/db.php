<?php 
/**
 * Template Name: DB template
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
                        init_db(); // use this instead of get_db() for dev purposes

                        $_SESSION['db']->show();
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
