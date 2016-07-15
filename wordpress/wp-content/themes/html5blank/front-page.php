<?php get_header(); ?>


    <main role="main">
    <!-- section -->
    <section>


    <!-- article -->
    <article id="search">
        <?php // content here 
            global $current_user;
            $db = new Database( get_user_meta( $current_user->ID, 'company', true) );
            $db->show();
        ?>

    </article>
    <!-- /article -->

    </section>
    <!-- /section -->
    </main>

<?php //get_sidebar(); ?>

<?php get_footer(); ?>

