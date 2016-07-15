<?php
/**
 * Wordpress Bootstrap navbar walker
 * 
 * For WordPress version 3.0.0+ and Bootstrap 3+
 * 
 * Set Title Attribute to "divider" for submenu divider.
 * Set Title Attribute to "header" for submenu header.
 *
 * Code example:
 * <code>
 * <nav class="navbar navbar-inverse" role="navigation">
 *     <div class="navbar-header">
 *         <button type="button" class="navbar-toggle" data-toggle="collapse" data-target="#navbar-collapse">
 *             <span class="sr-only">Toggle navigation</span>
 *             <span class="icon-bar"></span>
 *             <span class="icon-bar"></span>
 *             <span class="icon-bar"></span>
 *         </button>
 *         <a class="navbar-brand" href="<?php echo home_url(); ?>">
 *             <?php bloginfo('name'); ?>
 *         </a>
 *     </div>
 * 
 *     <div class="collapse navbar-collapse" id="navbar-collapse">
 *         <?php wp_nav_menu(array(
 *             'menu'       => 'main',           // Name of menu in WordPress.
 *             'depth'      => 2,                // Bootstrap only supports two levels.
 *             'menu_class' => 'nav navbar-nav', // 'nav navbar-nav' is required by Bootstrap.
 *             'walker'     => new bootstrap_navbar_walker()
 *         )); ?>
 *     </div>
 * </nav>
 * </code>
 *
 * @link http://codex.wordpress.org/Function_Reference/wp_nav_menu
 * 
 * Note:
 * When using this walker, only these arguments to wp_nav_menu are supported:
 * $menu, $walker, $link_befrore, $link_after and $menu_class.
 *
 * @author Ola Waljefors
 * https://gist.github.com/saurid/8146709
 */
class bootstrap_navbar_walker extends Walker_Nav_Menu
{
    /**
     * start_lvl
     *
     * @param  string  $output HTML output.
     * @param  integer $depth  Menus total depth.
     * @param  array   $args   Parameters.
     */
    public function start_lvl(&$output, $depth = 0, $args = array())
    {
        // Class name dropdown-menu is Bootstrap, sub-menu is WordPress.
        $output .= "\n<ul class=\"dropdown-menu sub-menu\">\n";
    }
    /**
     * start_el
     *
     * @param  string  $output  HTML output.
     * @param  object  $item    Current menu item.
     * @param  integer $depth   Current items depth.
     * @param  array   $args    Parameters.
     */
    public function start_el(&$output, $item, $depth = 0, $args = array())
    {
        // WP classes.
        $classes   = empty($item->classes) ? array() : (array) $item->classes;
        $classes[] = 'menu-item-' . $item->ID;

        // Join and escape class names.
        $class_names = join(' ', apply_filters('nav_menu_css_class', array_filter($classes), $item, $args));
        $class_names = $class_names ? esc_attr($class_names) : '';
        // Apply and escape id
        $id = apply_filters('nav_menu_item_id', 'menu-item-'. $item->ID, $item, $args );
        $id = $id ? 'id="' . esc_attr($id) . '"' : '';
        // Anchor attributes
        $atts = array(
            'title'  => !empty($item->attr_title) ? $item->attr_title : '',
            'target' => !empty($item->target)     ? $item->target     : '',
            'rel'    => !empty($item->xfn)        ? $item->xfn        : '',
            'href'   => !empty($item->url)        ? $item->url        : '',
        );

        $atts = apply_filters('nav_menu_link_attributes', $atts, $item, $args);
        $attributes = '';
        foreach ($atts as $attr => $value) {
            if (!empty($value)) {
                $value = ('href' === $attr) ? esc_url($value) : esc_attr($value);
                $attributes .= "$attr=\"$value\" ";
            }
        }
        // Check if title is divider, header or other menu item.
        if ($item->attr_title == 'divider') {
            $output .= "<li $id class=\"divider\">";
        } elseif ($item->attr_title == 'header') {
            $output .= "<li $id class=\"dropdown-header\">{$args->link_before}{$item->title}{$args->link_after}";
        } else {
            // If menu item has children add Bootstrap dropdown functions.
            if(in_array('menu-item-has-children', $classes)) {
                $output .= "<li $id class=\"dropdown $class_names\">"
                        .  "<a href=\"#\" class=\"dropdown-toggle\" data-toggle=\"dropdown\">{$args->link_before}{$item->title}{$args->link_after} <b class=\"caret\"></b></a>";
            } else {
                $output .= "<li $id class=\"$class_names\">"
                        .  "<a $attributes>{$args->link_before}{$item->title}{$args->link_after}</a>";
            }
        }
    }
}
?>
