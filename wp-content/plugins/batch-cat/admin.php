<?php
/**
 * Add the menu for Batch Cat
 **/
function bcat_add_admin_page()
{
    add_management_page('Batch Cat Management', 'Batch Cat', 8, __FILE__, 'bcat_create_admin_page');
}

/**
 * Generate the admin page for Batch Cat
 **/
function bcat_create_admin_page()
{
    $title_bar = "<h2>Batch Cat</h2>";
    $search_bar = "
        <div>
            <span>
                ".wp_dropdown_categories('id=slct_category&hide_empty=0&hierarchical=1&echo=0&selected='.(isset($_GET['cat']) ? intval($_GET['cat']) : -1 ))."
            </span>
            <span>
                <label for=\"input_keyword\">Keyword:</label>
                <input type=\"text\" id=\"input_keyword\" value=\"".$_GET['s']."\">
            </span>
            <span>
                <label for=\"slct_sortby\">Sort by:</label>
                <select id=\"slct_sortby\">
                    <option value=\"post_date\">Post Date</option>
                    <option value=\"modify_date\">Modify Date</option>
                </select>
            </span>
            <span>
                <label for=\"rdo_order_1\">ASC:</label>
                <input type=\"radio\" name=\"rdo_order\" id=\"rdo_order_1\" value=\"asc\">
                <label for=\"rdo_order_2\">DESC:</label>
                <input type=\"radio\" name=\"rdo_order\" id=\"rdo_order_2\" value=\"desc\">
            </span>
            <span><input type=\"button\" id=\"btn_bcat_search\" value=\"Search ...\" class=\"button-secondary\"></span>
        </div>
    ";

    $post_list = bcat_create_post_list();

    $category_list = bcat_create_category_list();

    echo '<div class="wrap">'.$title_bar.$search_bar.$post_list.$category_list.'</div>';
}

/**
 * Generate the post table
 **/
function bcat_create_post_list()
{
    $query = new WP_Query;
    $q = bcat_build_query_string();
    $posts = $query->query($q);

    if ( $post_type_object->hierarchical )
        $num_pages = ceil($wp_query->post_count / $per_page);
    else
        $num_pages = $wp_query->max_num_pages;

    $page_links = paginate_links( array(
        'base'          => add_query_arg('paged', '%#%'),
        'format'        => '',
        'prev_text'     => __('&laquo;'),
        'next_text'     => __('&raquo;'),
        'total'         => $query->max_num_pages,
        'current'       => isset($_GET['paged']) ? $_GET['paged'] : 1
    ));

    $pager = "
        <div class=\"tablenav\">
            <div class=\"tablenav-pages\">
                $page_links
            </div>
        </div>
    ";

    $str_table = "
        <table class=\"widefat\">
            <thead>
                <tr>
                    <th><input type=\"checkbox\" id=\"toggle_posts\" title=\"Select all posts\"></th>
                    <th>Title</th>
                    <th>Categories</th>
                </tr>
            </thead>
            <tbody id=\"the-list\">
                ".bcat_create_post_list_body($posts)."
            </tbody>
        </table>
    ";

    return "<div>".$pager.$str_table.$pager."</div>";
}

/**
 * Generate body of the post table
 **/
function bcat_create_post_list_body($posts)
{
    $body = "";
    $siteurl = get_option('siteurl');

    foreach ($posts as $post) {
        $cats = wp_get_post_categories($post->ID);
        $category_list = bcat_generate_category_list($cats);
        $body .= "
            <tr class=\"alternate\">
                <td><input type=\"checkbox\" name=\"postid[]\" value=\"".$post->ID."\"></td>
                <td><a href=\"$siteurl/wp-admin/post.php?post=$post->ID&action=edit\">".$post->post_title."</a></td>
                <td>".$category_list."</td>
            </tr>
        ";
    }

    return $body;
}

/**
 * Build query string
 **/
function bcat_build_query_string()
{
    $options = get_option('bcat_options');

    $querystr = 'post_type=post';
    $querystr .= '&posts_per_page='.$options['bcat_posts_per_page'];
    $querystr .= '&paged='.(isset($_GET['paged']) ? $_GET['paged'] : 1);
    $querystr .= '&orderby='.(isset($_GET['sort']) ? $_GET['sort'] : 'post_date');
    $querystr .= '&order='.(isset($_GET['order']) ? $_GET['order'] : 'desc');
    $querystr .= '&cat='.(isset($_GET['cat']) ? $_GET['cat'] : '');
    $querystr .= '&s='.(isset($_GET['s']) ? $_GET['s'] : '');

    return $querystr;
}

/**
 * Include js
 **/
function bcat_script_action()
{
    global $bcat_plugin_url;

    // Only include the js file in the plugin's admin page
    if ('batch-cat/admin.php' != $_GET['page']) {
        return '';
    }

    wp_enqueue_script('jquery');
    wp_enqueue_script('phpjs', $bcat_plugin_url.'php.transport.min.js');
    wp_enqueue_script('batch_cat_script', $bcat_plugin_url.'batch-cat.js', array('jquery'));
}

/**
 * Create category list
 **/
function bcat_create_category_list()
{
    $categories = get_categories('type=post&hide_empty=0');
    $siteurl = get_option('siteurl');

    $category_list = '';
    foreach ($categories as $cat) {
        $category_list .= "
            <tr>
                <td><input type=\"checkbox\" name=\"catid[]\" value=\"$cat->cat_ID\"></td>
                <td><a href=\"$siteurl/wp-admin/edit-tags.php?action=edit&taxonomy=category&post_type=post&tag_ID=$cat->cat_ID\">$cat->cat_name</a></td>
                <td><a href=\"$siteurl/wp-admin/edit.php?cat=$cat->cat_ID\">$cat->count</a></td>
            </tr>
        ";
    }

    $category_list = "
        <table class=\"widefat\">
            <thead>
                <tr>
                    <th><input type=\"checkbox\" id=\"toggle_categories\"></th>
                    <th>Category</th>
                    <th>Posts Count</th>
                </tr>
            </thead>
            <tbody>
                $category_list
            </tbody>
        </table>
    ";

    $button_list = <<<HTML
<div style="margin-top:5px">
    <input type="button" id="btn_set_cats" value="Set categories to posts" class="button-secondary">
    <input type="button" id="btn_add_cats" value="Add categories to posts" class="button-secondary">
    <input type="button" id="btn_del_cats" value="Drop categories from posts" class="button-secondary">
</div>
HTML;

    return $category_list.$button_list;
}

/**
 * AJAX server function: handles a change category request
 **/
function bcat_ajax_set_category()
{
    $raw_post_ids = $_POST['post_ids'];
    $raw_cat_ids = $_POST['cat_ids'];

    if (!isset($raw_post_ids) || strlen(trim($raw_post_ids))==0 || !preg_match('/([0-9]+,?)+/', $raw_post_ids)) {
        die('No posts found !');
    }
    if (!isset($raw_cat_ids) || strlen(trim($raw_cat_ids))==0 || !preg_match('/([0-9]+,?)+/', $raw_cat_ids)) {
        die('No categories found !');
    }

    $post_ids = explode(',', $raw_post_ids);
    $cat_ids = explode(',', $raw_cat_ids);

    foreach ($post_ids as $post_id) {
        wp_set_post_categories($post_id, $cat_ids);
    }

    return 0;
}

/**
 * AJAX server function: handles a change category request
 **/
function bcat_ajax_add_category()
{
    $raw_post_ids = $_POST['post_ids'];
    $raw_cat_ids = $_POST['cat_ids'];

    if (!isset($raw_post_ids) || strlen(trim($raw_post_ids))==0 || !preg_match('/([0-9]+,?)+/', $raw_post_ids)) {
        die('No posts found !');
    }
    if (!isset($raw_cat_ids) || strlen(trim($raw_cat_ids))==0 || !preg_match('/([0-9]+,?)+/', $raw_cat_ids)) {
        die('No categories found !');
    }

    $post_ids = explode(',', $raw_post_ids);
    $cat_ids = explode(',', $raw_cat_ids);

    foreach ($post_ids as $post_id) {
        $cats = wp_get_post_categories($post_id);
        wp_set_post_categories($post_id, array_merge($cat_ids, $cats));
    }

    return 0;
}

/**
 * AJAX server function: handles a change category request
 **/
function bcat_ajax_del_category()
{
    $raw_post_ids = $_POST['post_ids'];
    $raw_cat_ids = $_POST['cat_ids'];

    if (!isset($raw_post_ids) || strlen(trim($raw_post_ids))==0 || !preg_match('/([0-9]+,?)+/', $raw_post_ids)) {
        die('No posts found !');
    }
    if (!isset($raw_cat_ids) || strlen(trim($raw_cat_ids))==0 || !preg_match('/([0-9]+,?)+/', $raw_cat_ids)) {
        die('No categories found !');
    }

    $post_ids = explode(',', $raw_post_ids);
    $cat_ids = explode(',', $raw_cat_ids);

    foreach ($post_ids as $post_id) {
        $cats = wp_get_post_categories($post_id);
        wp_set_post_categories($post_id, array_diff($cats, $cat_ids));
    }

    return 0;
}

/**
 * Add hook to configuration page
 **/
function bcat_add_config_page()
{
    add_options_page('Batch Cat Configuration', 'Batch Cat Settings', 'manage_options', __FILE__, 'bcat_output_config_page');
}

/**
 * Register settings options
 **/
function bcat_register_settings()
{
    register_setting('bcat_options_group', 'bcat_options', 'bcat_validate_options');
}

/**
 * Configuration page
 **/
function bcat_output_config_page()
{
    $options = get_option('bcat_options');
?>
<div class="wrap">
    <h2>Batch Cat Settings</h2>
    <form name="bcat_form" method="post" action="options.php">
    <?php settings_fields('bcat_options_group'); ?>
    <table class="form-table">
        <tr valign="top">
            <th scope="row">Posts per page:</th>
            <td><input type="text" name="bcat_options[bcat_posts_per_page]" value="<?php echo $options['bcat_posts_per_page']; ?>" /></td>
        </tr>
    </table>
    <p class="submit">
        <input type="submit" class="button-secondary" value="<?php _e('Save Changes'); ?>" />
    </p>
    </form>
</div>
<?php
}

/**
 * Reset options' values to default ones
 **/
function bcat_reset_options()
{
    $options = array();

    $options['bcat_posts_per_page'] = 15;

    update_option('bcat_options', $options);
}

/**
 * Validate user input for options
 **/
function bcat_validate_options($options)
{
    $org = get_option('bcat_options');

    if (is_array($options)) {
        // Validate bcat_posts_per_page
        if (isset($options['bcat_posts_per_page'])) {
            $val = $options['bcat_posts_per_page'];
            $val = trim($val);
            $options['bcat_posts_per_page'] = is_numeric($val) && is_int($val+0) && $val>0 ? $val : $org['bcat_posts_per_page'];
        }
    }

    return $options;
}

/**
 * Generate a string for given categories
 **/
function bcat_generate_category_list($cat_ids)
{
    $category_list = array();

    foreach ($cat_ids as $cat_id){
        $cat = get_category($cat_id);
        $category_list[] = $cat->name;
    }

    return implode(' , ', $category_list);
}
?>
