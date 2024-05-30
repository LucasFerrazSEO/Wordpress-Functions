/* Remove Rank Math sitemap cache */ 
add_filter( 'rank_math/sitemap/enable_caching', '__return_false');

/* Retrieve the number of words in a text */
function word_count(){
    ob_start();
    the_content();
    $content = ob_get_clean();
    return sizeof(explode(" ", $content));
}

/* Retrieve the last word of a string */
function last_word($string){
    $last_word_start = strrpos ($string , " ") + 1;
    $last_word_end = strlen($string) - 1;
    $last_word = substr($string, $last_word_start, $last_word_end);
    return $last_word;
}

/* add featured image to RSS feed */
function news_post_thumbnails_in_feeds($content){
    global $post;
    if (has_post_thumbnail($post->ID)) {
        $content = '<div style="margin-bottom:20px;">' . get_the_post_thumbnail($post->ID, 'full') . '</div>' . $content;
    }
    return $content;
}
add_filter('the_excerpt_rss', 'news_post_thumbnails_in_feeds');
add_filter('the_content_feed', 'news_post_thumbnails_in_feeds');

/* If current URI is AMP */
function is_amp(){
    if (strstr(strip_tags(trim(ltrim(urldecode($_SERVER["REQUEST_URI"]), '/'))), 'amp/')) {
        return 1;
    } else {
        return 0;
    }
}

/* Disable Wordpress Heartbeat */
add_action('init', 'stop_heartbeat', 1);
function stop_heartbeat(){
    wp_deregister_script('heartbeat');
}

/* Disable Wordpress XMLRPC */
add_filter('xmlrpc_enabled', '__return_false');
remove_action('wp_head', 'rsd_link');
add_filter('bloginfo_url', function ($output, $property) {
    return ($property == 'pingback_url') ? null : $output;
}, 11, 2);

/* Remove Wordpress Version */
function crunchify_remove_version(){
    return '';
}
add_filter('the_generator', 'crunchify_remove_version');

/* Remove frontend's Link API */
remove_action('wp_head', 'rest_output_link_wp_head', 10);
remove_action( 'wp_head', 'wp_oembed_add_discovery_links', 10 );
remove_action('template_redirect', 'rest_output_link_header', 11, 0);
remove_action('wp_head', 'wp_generator');
remove_action('wp_head', 'feed_links', 2);
remove_action('wp_head', 'index_rel_link');
remove_action('wp_head', 'wlwmanifest_link');
remove_action('wp_head', 'feed_links_extra', 3);
remove_action('wp_head', 'start_post_rel_link', 10, 0);
remove_action('wp_head', 'parent_post_rel_link', 10, 0);
remove_action('wp_head', 'adjacent_posts_rel_link', 10, 0);
remove_action('wp_head', 'wp_shortlink_wp_head', 10, 0);
remove_action('wp_head', 'adjacent_posts_rel_link_wp_head', 10, 0);

/* Remove DNS-PREFETCH */
add_action('init', 'remove_dns_prefetch');
function  remove_dns_prefetch(){
    remove_action('wp_head', 'wp_resource_hints', 2, 99);
}

/* Remove Wordpress Block Library */
function dm_remove_wp_block_library_css(){
    wp_dequeue_style('wp-block-library');
}
add_action('wp_enqueue_scripts', 'dm_remove_wp_block_library_css');
add_filter('wpseo_robots', '__return_false');
remove_action('wp_head', 'wp_robots', 1);

/* Disable Wordpress Emoji */
function disable_emojis(){
    remove_action('wp_head', 'print_emoji_detection_script', 7);
    remove_action('admin_print_scripts', 'print_emoji_detection_script');
    remove_action('wp_print_styles', 'print_emoji_styles');
    remove_action('admin_print_styles', 'print_emoji_styles');
    remove_filter('the_content_feed', 'wp_staticize_emoji');
    remove_filter('comment_text_rss', 'wp_staticize_emoji');
    remove_filter('wp_mail', 'wp_staticize_emoji_for_email');
    // Remove from TinyMCE
    add_filter('tiny_mce_plugins', 'disable_emojis_tinymce');
}
add_action('init', 'disable_emojis');

/* Disable TinyMCE's Emoji */
function disable_emojis_tinymce($plugins){
    if (is_array($plugins)) {
        return array_diff($plugins, array('wpemoji'));
    } else {
        return array();
    }
}

/* Remove Plugins' CSS from frontend */
function jeherve_remove_all_jp_css(){
    wp_deregister_style('AtD_style'); // After the Deadline
    wp_deregister_style('jetpack_likes'); // Likes
    wp_deregister_style('jetpack_related-posts'); //Related Posts
    wp_deregister_style('jetpack-carousel'); // Carousel
    wp_deregister_style('grunion.css'); // Grunion contact form
    wp_deregister_style('the-neverending-homepage'); // Infinite Scroll
    wp_deregister_style('infinity-twentyten'); // Infinite Scroll - Twentyten Theme
    wp_deregister_style('infinity-twentyeleven'); // Infinite Scroll - Twentyeleven Theme
    wp_deregister_style('infinity-twentytwelve'); // Infinite Scroll - Twentytwelve Theme
    wp_deregister_style('noticons'); // Notes
    wp_deregister_style('post-by-email'); // Post by Email
    wp_deregister_style('publicize'); // Publicize
    wp_deregister_style('sharedaddy'); // Sharedaddy
    wp_deregister_style('sharing'); // Sharedaddy Sharing
    wp_deregister_style('stats_reports_css'); // Stats
    wp_deregister_style('jetpack-widgets'); // Widgets
    wp_deregister_style('jetpack-slideshow'); // Slideshows
    wp_deregister_style('presentations'); // Presentation shortcode
    wp_deregister_style('jetpack-subscriptions'); // Subscriptions
    wp_deregister_style('tiled-gallery'); // Tiled Galleries
    wp_deregister_style('widget-conditions'); // Widget Visibility
    wp_deregister_style('jetpack_display_posts_widget'); // Display Posts Widget
    wp_deregister_style('gravatar-profile-widget'); // Gravatar Widget
    wp_deregister_style('widget-grid-and-list'); // Top Posts widget
    wp_deregister_style('jetpack-widgets'); // Widgets
    wp_deregister_script('wp-mediaelement');
    wp_deregister_style('wp-mediaelement');
    wp_deregister_style('servebolt-optimizer-public-styling');
}
add_action('wp_print_styles', 'jeherve_remove_all_jp_css');

/* Remove Wordpress' Admin Bar */
function my_function_admin_bar(){
    return false;
}
add_filter('show_admin_bar', 'my_function_admin_bar')

/* Remove Wordpress' Jquery */
add_action('wp_enqueue_scripts', 'no_more_jquery');
function no_more_jquery(){
    wp_deregister_script('jquery');
}

/* Add Support to Thumbnails */
function ed_support_thumbnails(){
    add_theme_support('post-thumbnails');
}
add_action('after_setup_theme', 'ed_support_thumbnails');

/* Filter Category/Archive titles */
add_filter('get_the_archive_title', function ($title) {
    if (is_category()) {
        $title = single_cat_title('', false);
    } elseif (is_tag()) {
        $title = single_tag_title('', false);
    } elseif (is_author()) {
        $title = '<span class="vcard">' . get_the_author() . '</span>';
    } elseif (is_tax()) { //for custom post types
        $title = sprintf(('%1$s'), single_term_title('', false));
    } elseif (is_post_type_archive()) {
        $title = post_type_archive_title('', false);
    }
    return $title;
});

/* Remove classic-theme-styles */
add_action('wp_enqueue_scripts', function() {
    wp_dequeue_style( 'classic-theme-styles');
    wp_dequeue_style('swcfpc_admin_css');
}, 20);

/* Remove Rank Math's upsell banner */
function remove_upsell_banner_rank_math() {
    wp_dequeue_style( 'rank-math-analytics-stats');
    wp_deregister_style( 'rank-math-analytics-stats');
    wp_dequeue_script( 'rank-math-analytics-stats');
    wp_deregister_script( 'rank-math-analytics-stats' );
}

add_action('wp_enqueue_scripts', 'remove_upsell_banner_rank_math', 99);
add_action('wp', function () {
    header_remove('X-Pingback');
},9999);

/* Unregister default wp widgets */
function unregister_default_wp_widgets() {
    unregister_widget( 'WP_Widget_Pages' );
    unregister_widget( 'WP_Widget_Calendar' );
    unregister_widget( 'WP_Widget_Archives' );
    unregister_widget( 'WP_Widget_Links' );
    unregister_widget( 'WP_Widget_Meta' );
    unregister_widget( 'WP_Widget_Recent_Comments' );
    unregister_widget( 'WP_Widget_RSS' );
    unregister_widget( 'WP_Widget_Tag_Cloud' );
}
add_action( 'widgets_init', 'unregister_default_wp_widgets', 1 );

/* Remove dashboard items */
add_action( 'wp_dashboard_setup', 'my_custom_dashboard_widgets' );
function my_custom_dashboard_widgets() {
    global $wp_meta_boxes;
    unset( $wp_meta_boxes[ 'dashboard' ][ 'normal' ][ 'core' ][ 'dashboard_right_now' ] );
    unset( $wp_meta_boxes[ 'dashboard' ][ 'normal' ][ 'core' ][ 'dashboard_recent_comments' ] );
    unset( $wp_meta_boxes[ 'dashboard' ][ 'normal' ][ 'core' ][ 'dashboard_incoming_links' ] );
    unset( $wp_meta_boxes[ 'dashboard' ][ 'normal' ][ 'core' ][ 'dashboard_activity' ] );
    unset( $wp_meta_boxes[ 'dashboard' ][ 'normal' ][ 'core' ][ 'dashboard_plugins' ] );
    unset( $wp_meta_boxes[ 'dashboard' ][ 'side' ][ 'core' ][ 'dashboard_primary' ] );
    unset( $wp_meta_boxes[ 'dashboard' ][ 'side' ][ 'core' ][ 'dashboard_secondary' ] );
    unset( $wp_meta_boxes[ 'dashboard' ][ 'side' ][ 'core' ][ 'dashboard_quick_press' ] );
    unset( $wp_meta_boxes[ 'dashboard' ][ 'side' ][ 'core' ][ 'dashboard_recent_drafts' ] );
}

/* Remove tags support from posts */
function myprefix_unregister_tags() {
    unregister_taxonomy_for_object_type('post_tag', 'post');
}
add_action('init', 'myprefix_unregister_tags');

/* Transform string into slug */
function slugify($text, $length = null){
    $replacements = [
        '<' => '', '>' => '', '-' => ' ', '&' => '', '"' => '', 'À' => 'A', 'Á' => 'A', 'Â' => 'A', 'Ã' => 'A', 'Ä' => 'Ae', 'Ä' => 'A', 'Å' => 'A', 'Ā' => 'A', 'Ą' => 'A', 'Ă' => 'A', 'Æ' => 'Ae', 'Ç' => 'C', "'" => '', 'Ć' => 'C', 'Č' => 'C', 'Ĉ' => 'C', 'Ċ' => 'C', 'Ď' => 'D', 'Đ' => 'D', 'Ð' => 'D', 'È' => 'E', 'É' => 'E', 'Ê' => 'E', 'Ë' => 'E', 'Ē' => 'E', 'Ę' => 'E', 'Ě' => 'E', 'Ĕ' => 'E', 'Ė' => 'E', 'Ĝ' => 'G', 'Ğ' => 'G', 'Ġ' => 'G', 'Ģ' => 'G', 'Ĥ' => 'H', 'Ħ' => 'H', 'Ì' => 'I', 'Í' => 'I', 'Î' => 'I', 'Ï' => 'I', 'Ī' => 'I', 'Ĩ' => 'I', 'Ĭ' => 'I', 'Į' => 'I', 'İ' => 'I', 'Ĳ' => 'IJ', 'Ĵ' => 'J', 'Ķ' => 'K', 'Ł' => 'L', 'Ľ' => 'L', 'Ĺ' => 'L', 'Ļ' => 'L', 'Ŀ' => 'L', 'Ñ' => 'N', 'Ń' => 'N', 'Ň' => 'N', 'Ņ' => 'N', 'Ŋ' => 'N', 'Ò' => 'O', 'Ó' => 'O', 'Ô' => 'O', 'Õ' => 'O', 'Ö' => 'Oe', 'Ö' => 'Oe', 'Ø' => 'O', 'Ō' => 'O', 'Ő' => 'O', 'Ŏ' => 'O', 'Œ' => 'OE', 'Ŕ' => 'R', 'Ř' => 'R', 'Ŗ' => 'R', 'Ś' => 'S', 'Š' => 'S', 'Ş' => 'S', 'Ŝ' => 'S', 'Ș' => 'S', 'Ť' => 'T', 'Ţ' => 'T', 'Ŧ' => 'T', 'Ț' => 'T', 'Ù' => 'U', 'Ú' => 'U', 'Û' => 'U', 'Ü' => 'Ue', 'Ū' => 'U', 'Ü' => 'Ue', 'Ů' => 'U', 'Ű' => 'U', 'Ŭ' => 'U', 'Ũ' => 'U', 'Ų' => 'U', 'Ŵ' => 'W', 'Ý' => 'Y', 'Ŷ' => 'Y', 'Ÿ' => 'Y', 'Ź' => 'Z', 'Ž' => 'Z', 'Ż' => 'Z', 'Þ' => 'T', 'à' => 'a', 'á' => 'a', 'â' => 'a', 'ã' => 'a', 'ä' => 'ae', 'ä' => 'ae', 'å' => 'a', 'ā' => 'a', 'ą' => 'a', 'ă' => 'a', 'æ' => 'ae', 'ç' => 'c', 'ć' => 'c', 'č' => 'c', 'ĉ' => 'c', 'ċ' => 'c', 'ď' => 'd', 'đ' => 'd', 'ð' => 'd', 'è' => 'e', 'é' => 'e', 'ê' => 'e', 'ë' => 'e', 'ē' => 'e', 'ę' => 'e', 'ě' => 'e', 'ĕ' => 'e', 'ė' => 'e', 'ƒ' => 'f', 'ĝ' => 'g', 'ğ' => 'g', 'ġ' => 'g', 'ģ' => 'g', 'ĥ' => 'h', 'ħ' => 'h', 'ì' => 'i', 'í' => 'i', 'î' => 'i', 'ï' => 'i', 'ī' => 'i', 'ĩ' => 'i', 'ĭ' => 'i', 'į' => 'i', 'ı' => 'i', 'ĳ' => 'ij', 'ĵ' => 'j', 'ķ' => 'k', 'ĸ' => 'k', 'ł' => 'l', 'ľ' => 'l', 'ĺ' => 'l', 'ļ' => 'l', 'ŀ' => 'l', 'ñ' => 'n', 'ń' => 'n', 'ň' => 'n', 'ņ' => 'n', 'ŉ' => 'n', 'ŋ' => 'n', 'ò' => 'o', 'ó' => 'o', 'ô' => 'o', 'õ' => 'o', 'ö' => 'oe', 'ö' => 'oe', 'ø' => 'o', 'ō' => 'o', 'ő' => 'o', 'ŏ' => 'o', 'œ' => 'oe', 'ŕ' => 'r', 'ř' => 'r', 'ŗ' => 'r', 'š' => 's', 'ś' => 's', 'ù' => 'u', 'ú' => 'u', 'û' => 'u', 'ü' => 'ue', 'ū' => 'u', 'ü' => 'ue', 'ů' => 'u', 'ű' => 'u', 'ŭ' => 'u', 'ũ' => 'u', 'ų' => 'u', 'ŵ' => 'w', 'ý' => 'y', 'ÿ' => 'y', 'ŷ' => 'y', 'ž' => 'z', 'ż' => 'z', 'ź' => 'z', 'þ' => 't', 'α' => 'a', 'ß' => 'ss', 'ẞ' => 'b', 'ſ' => 'ss', 'ый' => 'iy', 'А' => 'A', 'Б' => 'B', 'В' => 'V', 'Г' => 'G', 'Д' => 'D', 'Е' => 'E', 'Ё' => 'YO', 'Ж' => 'ZH', 'З' => 'Z', 'И' => 'I', 'Й' => 'Y', 'К' => 'K', 'Л' => 'L', 'М' => 'M', 'Н' => 'N', 'О' => 'O', 'П' => 'P', 'Р' => 'R', 'С' => 'S', 'Т' => 'T', 'У' => 'U', 'Ф' => 'F', 'Х' => 'H', 'Ц' => 'C', 'Ч' => 'CH', 'Ш' => 'SH', 'Щ' => 'SCH', 'Ъ' => '', 'Ы' => 'Y', 'Ь' => '', 'Э' => 'E', 'Ю' => 'YU', 'Я' => 'YA', 'а' => 'a', 'б' => 'b', 'в' => 'v', 'г' => 'g', 'д' => 'd', 'е' => 'e', 'ё' => 'yo', 'ж' => 'zh', 'з' => 'z', 'и' => 'i', 'й' => 'y', 'к' => 'k', 'л' => 'l', 'м' => 'm', 'н' => 'n', 'о' => 'o', 'п' => 'p', 'р' => 'r', 'с' => 's', 'т' => 't', 'у' => 'u', 'ф' => 'f', 'х' => 'h', 'ц' => 'c', 'ч' => 'ch', 'ш' => 'sh', 'щ' => 'sch', 'ъ' => '', 'ы' => 'y', 'ь' => '', 'э' => 'e', 'ю' => 'yu', 'я' => 'ya', '.' => '-', '€' => '-eur-', '$' => '-usd-'
    ];
    // Replace non-ascii characters
    $text = strtr($text, $replacements);
    // Replace non letter or digits with "-"
    $text = preg_replace('~[^\pL\d.]+~u', '-', $text);
    // Replace unwanted characters with "-"
    $text = preg_replace('[^-\w.]+', '-', $text);
    // Trim "-"
    $text = trim($text, '-');
    // Remove duplicate "-"
    $text = preg_replace('-+', '-', $text);
    // Convert to lowercase
    $text = strtolower($text);
    // Limit length
    if (isset($length) && $length < strlen($text))
        $text = rtrim(substr($text, 0, $length), '-');
    return $text;
}

/*  Disable Gutenberd's styles in Header */
function wps_deregister_styles(){
    wp_dequeue_style('global-styles');
}
add_action('wp_enqueue_scripts', 'wps_deregister_styles', 100);

/* Adjusts menu link attributes to be compatible with Bootstrap 5 */
add_filter('nav_menu_link_attributes', 'bootstrap5_dropdown_fix');
function bootstrap5_dropdown_fix($atts){
    if (array_key_exists('data-toggle', $atts)) {
        unset($atts['data-toggle']);
        $atts['data-bs-toggle'] = 'dropdown';
        $atts['id'] = $atts['id'] . '-' . rand(1, 1000);
    }
    return $atts;
}

/* Add attributes to get_avatar <img> */
add_filter('get_avatar', 'add_avatar_attributes', 10, 5);
function add_avatar_attributes($avatar, $id_or_email, $size, $default, $alt){
    $doc = new DOMDocument();
    $section = @mb_convert_encoding($avatar, 'HTML-ENTITIES', "UTF-8"); // need for displaying UTF chars correctly
    $doc->loadHTML($section);
    unset($section);
    $img = $doc->getElementsByTagName('img')->item(0);
    $img->setAttribute("title", get_the_author_meta('display_name', $id_or_email));
    return $doc->saveHTML();
}