<?php get_header(); ?>

    <main role="main">
    <!-- section -->
    <section>


    <!-- article -->
    <article id="search">
        <h1>Express Lims</h1>
        <p class="lead">Simple, light-weight lab inventory management system</p>

        <div class="row">
            <div class="col-sm-3 col-sm-offset-2">
                <h1 class="text-center" style="color: #77B3D4">View demo</h1>
                <a href="<?php echo home_url(); ?>" title="Home"><img style="margin:5px;" src="<?php echo get_template_directory_uri(); ?>/img/browser.svg"  alt="Logo" class="logo-img"></img></a>
            </div>
            <div class="col-sm-3 col-sm-offset-2">
                <h1 class="text-center" style="color:#76C2AF">Sign-up</h1>
                <a href="<?php echo home_url(); ?>" title="Home"><img style="margin:5px;" src="<?php echo get_template_directory_uri(); ?>/img/compose.svg"  alt="Logo" class="logo-img"></img></a>
            </div>
        </div>

    </article>
    <!-- /article -->

    </section>
    <!-- /section -->
    </main>

<?php //get_sidebar(); ?>

<?php get_footer(); ?>

