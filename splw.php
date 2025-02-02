<?php
/*
Plugin Name: Smart Post Lists Light
Plugin URI: http://otwthemes.com
Description: Makes Smart Post Lists Light Widget available. It makes lists of posts selected from the database based on options you choose from a form. No coding required! You can build different types of blog, portfolio, services pages. Select category/s and/or tag/s. Choose post count and offset. Order the list by date, author, date modified. Order to display posts: ascending, descending. Define which fields you need to show: title, date, excerpt, comments. Choose to show image on the first post only, all post, none. Choose which Wordpress image to show: thumbnail, medium, large. Choose image float.  <a href="http://codecanyon.net/item/smart-post-lists-widget-for-wordpress/935289?ref=OTWthemes">Want more options + support</a>? 
Version: 1.8
Author: OTWthemes
Author URI: https://codecanyon.net/user/otwthemes/portfolio?ref=OTWthemes
*/


define('SMART_POST_LIST_PLUGIN_URL', plugin_dir_url( __FILE__ ));

load_plugin_textdomain('smart-post-list',false,dirname(plugin_basename(__FILE__)) . '/languages/');

$otw_spll_plugin_url = plugin_dir_url( __FILE__);
$otw_spll_plugin_id = 'a57a5f15234273fe7d74649b8ad5a77b';

//components
$otw_spll_factory_component = false;
$otw_spll_factory_object = false;

//load core component functions
@include_once( 'include/otw_components/otw_functions/otw_functions.php' );

//register factory component
otw_register_component( 'otw_factory', dirname( __FILE__ ).'/include/otw_components/otw_factory/', $otw_spll_plugin_url.'include/otw_components/otw_factory/' );

if( !function_exists( 'otw_register_component' ) ){
	wp_die( 'Please include otw components' );
}

class PostInCategory extends WP_Widget
{
    /**
     *
     * The Widget.
     */
  	public function __construct()
  	{
		$widget_ops = array( 
			'classname' => 'widget smart-post-list',
			'show_instance_in_rest' => true,
			'description' => esc_html__('A widget that displays a list of posts from a specific category/tag. Want more options? - OTWthemes.com', 'smart-post-list') 
		);
		$control_ops = array( 
			'width' => 300,
			'height' => 350,
			'id_base' => 'smart-post-list-widget'
		);
		
		parent::__construct( 'smart-post-list-widget', esc_html__('Smart Post Lists Light', 'smart-post-list'), $widget_ops, $control_ops );
  	}

  	/**
  	 * Creates the Widget's Admin HTML.
  	 */
	function form($instance)
	{
		$defaults = array('title' => esc_html__('Category Name', 'smart-post-list'),
						  'show_title' => 1,
						  'show_date' => 1,
                          'show_excerpt' => 1,
                          'excerpt_words_count' => 150,
                          'show_number_of_comments' => 1,
                          'posts_count' => 5,
                          'posts_offset' => 0,
                          'post_orderby' => 'date',
                          'post_order_direction' => 'DESC',
						  'categories' => array(),
                          'tags' => '',
						  'thumb_on_post' => 'none',
                          'thumb_image_size' => 'thumbnail',
						  'post_image_float' => 'top_of_excerpt');

		$instance = wp_parse_args((array) $instance, $defaults);
        ?>
            <script type="text/javascript">
                jQuery(document).ready(function($) {
                    setSuggest("<?php echo otw_esc_text( $this->get_field_id('tags') );?>");
                });
                if (document.getElementById("<?php echo esc_html( $this->get_field_id('thumb_on_post') );?>") != null && document.getElementById("<?php echo esc_attr( $this->get_field_id('thumb_on_post') );?>").selectedIndex == 0) {
                    document.getElementById("<?php echo esc_html( $this->get_field_id('thumb_image_size') );?>").disabled = true;
					document.getElementById("<?php echo esc_html( $this->get_field_id('post_image_float') );?>").disabled = true;
                }
            </script>
			<p>
				<label for="<?php echo esc_attr( $this->get_field_id( 'title' ) ); ?>"><?php esc_html_e('Title', 'smart-post-list'); ?></label>
				<input id="<?php echo esc_attr( $this->get_field_id( 'title' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'title' ) ); ?>" value="<?php echo esc_attr( $instance['title'] ); ?>" type='text' style="width:100%;" />
			</p>
			<p>
				<label for="<?php esc_attr( $this->get_field_id( 'categories' ) )?>"><?php esc_html_e('Categories', 'smart-post-list');?></label>
				<ul class="checklist">
				<?php
					$selectedCategories = array();
					if (is_array($instance['categories']))
					{
						$selectedCategories = $instance['categories'];
					}

					$cattree = array();
					$categories = get_categories();

					$catinfo = array();
					foreach ($categories as $category)
					{
						$id = $category->term_id;
						$data = array('name' => $category->name, 'count' => $category->category_count, 'parent' => $category->parent, 'childs' => array());
						$catinfo[$id] = $data;
					}

					foreach ($catinfo as $id => $category)
					{
						$parent_id = $category['parent'];
						if ($parent_id > 0)
						{
							$catinfo[$parent_id]['childs'][$id] = $category;
						}
					}

					foreach ($catinfo as $id => $category)
					{
						if ($category['parent'] == 0)
						{
							$this->add_category_node($selectedCategories, $id, $category); // recursive
						}
					}
				?>
				</ul>
			</p>
			<p>
				<label for="<?php echo $this->get_field_id( 'tags' ); ?>"><?php esc_html_e('Tags', 'smart-post-list'); ?></label>
				<input id="<?php echo $this->get_field_id( 'tags' ); ?>" name="<?php echo $this->get_field_name( 'tags' ); ?>" value="<?php echo esc_attr( $instance['tags'] ); ?>" type='text' style="width:100%;"/>
                <label>(<?php esc_html_e('Separate tags with commas', 'smart-post-list'); ?>)</label>
			</p>
			<p>
				<label for="<?php echo esc_attr( $this->get_field_id('posts_count') ); ?>"><?php esc_html_e('Posts count', 'smart-post-list'); ?></label>
				<input id="<?php echo esc_attr( $this->get_field_id('posts_count') ); ?>" onkeypress="return isNumberKey(event)" name="<?php echo esc_attr( $this->get_field_name('posts_count') ); ?>" value="<?php echo esc_attr( $instance['posts_count'] ); ?>" type='text' style="width: 100%;" />
			</p>
			<p>
				<label for="<?php echo esc_attr( $this->get_field_id('posts_offset') ); ?>"><?php esc_html_e('Posts offset (number of posts to skip)', 'smart-post-list'); ?></label>
				<input id="<?php echo esc_attr( $this->get_field_id('posts_offset') ); ?>" onkeypress="return isNumberKey(event)" name="<?php echo esc_attr( $this->get_field_name('posts_offset') ); ?>" value="<?php echo esc_attr( $instance['posts_offset'] ); ?>" type='text' style="width: 100%;" />
			</p>
			<p>
				<label for="<?php echo esc_attr( $this->get_field_id('post_orderby') ); ?>"><?php esc_html_e('Order by:', 'smart-post-list'); ?></label>
				<select id="<?php echo esc_attr( $this->get_field_id('post_orderby') ); ?>" name="<?php echo esc_attr( $this->get_field_name('post_orderby') ); ?>" class="widefat" style="width:100%;">
					<option <?php if ( 'author' == $instance['post_orderby'] ) echo 'selected="selected"'; ?>>author</option>
					<option <?php if ( 'date' == $instance['post_orderby'] ) echo 'selected="selected"'; ?>>date</option>
					<option <?php if ( 'title' == $instance['post_orderby'] ) echo 'selected="selected"'; ?>>title</option>
					<option <?php if ( 'modified' == $instance['post_orderby'] ) echo 'selected="selected"'; ?>>modified</option>
				</select>
			</p>
			<p>
				<label for="<?php echo esc_attr( $this->get_field_id('post_order_direction') ); ?>"><?php esc_html_e('Order to display posts:', 'smart-post-list'); ?></label>
				<select id="<?php echo esc_attr( $this->get_field_id('post_order_direction') ); ?>" name="<?php echo esc_attr( $this->get_field_name('post_order_direction') ); ?>" class="widefat" style="width:100%;">
					<option <?php if ( 'DESC' == $instance['post_order_direction'] ) echo 'selected="selected"'; ?>>DESC</option>
					<option <?php if ( 'ASC' == $instance['post_order_direction'] ) echo 'selected="selected"'; ?>>ASC</option>
				</select>
			</p>
			<p>
				<label for="<?php echo esc_attr( $this->get_field_id('show_title') ); ?>"><input type="checkbox" <?php checked($instance['show_title'], true) ?> id="<?php echo $this->get_field_id( 'show_title' ); ?>" name="<?php echo $this->get_field_name( 'show_title' ); ?>" value="1" /><?php esc_html_e('Show title', 'smart-post-list'); ?></label>

			</p>
			<p>
				<label for="<?php echo esc_attr( $this->get_field_id('show_date') ); ?>"><input type="checkbox" <?php checked($instance['show_date'], true) ?> id="<?php echo $this->get_field_id( 'show_date' ); ?>" name="<?php echo $this->get_field_name( 'show_date' ); ?>" value="1" /><?php esc_html_e('Show date', 'smart-post-list'); ?></label>

			</p>
            <p>
				<label for="<?php echo esc_attr( $this->get_field_id('show_excerpt') ); ?>"><input type="checkbox" <?php checked($instance['show_excerpt'], true) ?> id="<?php echo $this->get_field_id( 'show_excerpt' ); ?>" name="<?php echo $this->get_field_name( 'show_excerpt' ); ?>" value="1" /><?php esc_html_e('Show excerpt', 'smart-post-list'); ?></label>
			</p>
			<p>
				<label for="<?php echo esc_attr( $this->get_field_id('excerpt_words_count') ); ?>"><?php esc_html_e('Excerpt length in words', 'smart-post-list'); ?></label>
				<input id="<?php echo esc_attr( $this->get_field_id('excerpt_words_count') ); ?>" onkeypress="return isNumberKey(event)" name="<?php echo esc_attr( $this->get_field_name('excerpt_words_count') ); ?>" value="<?php echo esc_attr( $instance['excerpt_words_count'] ); ?>" type='text' style="width: 100%;" />
			</p>
            <p>
				<label for="<?php echo esc_attr( $this->get_field_id('show_number_of_comments') ); ?>"><input type="checkbox" <?php checked($instance['show_number_of_comments'], true) ?> id="<?php echo $this->get_field_id( 'show_number_of_comments' ); ?>" name="<?php echo $this->get_field_name( 'show_number_of_comments' ); ?>" value="1" /><?php esc_html_e('Show number of comments', 'smart-post-list'); ?></label>
			</p>

            <p>
    			<label for="<?php echo esc_attr( $this->get_field_id('thumb_on_post') ); ?>"><?php esc_html_e('Show thumbnail on post', 'smart-post-list'); ?></label>
				<select id="<?php echo esc_attr( $this->get_field_id('thumb_on_post') ); ?>" name="<?php echo esc_attr( $this->get_field_name('thumb_on_post') ); ?>" onchange="onThumbOnPostSelect(this, '<?php echo $this->get_field_id('thumb_image_size');?>'); onThumbOnPostSelect(this, '<?php echo $this->get_field_id('post_image_float');?>');" class="widefat" style="width:100%;">
					<option <?php if ( 'none' == $instance['thumb_on_post'] ) echo 'selected="selected"'; ?>>none</option>
					<option <?php if ( 'all' == $instance['thumb_on_post'] ) echo 'selected="selected"'; ?>>all</option>
					<option <?php if ( 'first' == $instance['thumb_on_post'] ) echo 'selected="selected"'; ?>>first</option>
				</select>
            </p>
            <p>
                <label for="<?php echo esc_attr( $this->get_field_id('thumb_image_size') ); ?>"><?php esc_html_e('Image size', 'smart-post-list'); ?></label>
                <select id="<?php echo esc_attr( $this->get_field_id('thumb_image_size') ); ?>" name="<?php echo esc_attr( $this->get_field_name('thumb_image_size') ); ?>" class="widefat" style="width:100%;">
                    <option <?php if ( 'thumbnail' == $instance['thumb_image_size'] ) echo 'selected="selected"'; ?>>thumbnail</option>
                    <option <?php if ( 'medium' == $instance['thumb_image_size'] ) echo 'selected="selected"'; ?>>medium</option>
                    <option <?php if ( 'large' == $instance['thumb_image_size'] ) echo 'selected="selected"'; ?>>large</option>
                </select>
            </p>
			<p>
				<label for="<?php echo esc_attr( $this->get_field_id('post_image_float') ); ?>"><?php esc_html_e('Image float:', 'smart-post-list'); ?></label>
				<select id="<?php echo esc_attr( $this->get_field_id('post_image_float') ); ?>" name="<?php echo esc_attr( $this->get_field_name('post_image_float') ); ?>" class="widefat" style="width:100%;">
					<option <?php if ( 'top_of_excerpt' == $instance['post_image_float'] ) echo 'selected="selected"'; ?> value="top_of_excerpt"><?php esc_html_e('top of excerpt'); ?></option>
					<option <?php if ( 'right_of_excerpt' == $instance['post_image_float'] ) echo 'selected="selected"'; ?> value="right_of_excerpt"><?php esc_html_e('right of excerpt'); ?></option>
					<option <?php if ( 'left_of_excerpt' == $instance['post_image_float'] ) echo 'selected="selected"'; ?> value="left_of_excerpt"><?php esc_html_e('left of excerpt'); ?></option>
				</select>
			</p>
		<?php
	}

	/**
	 * Updates the Widget's data.
	 */
  function update($new_instance, $old_instance)
  {
	$instance = $old_instance;
	
	$instance['title'] = strip_tags($new_instance['title']);
	
	if( isset( $new_instance['show_title'] ) && ( $new_instance['show_title'] == 1 ) ){
		$instance['show_title'] = 1;
	}else{
		$instance['show_title'] = 0;
	}
	
	if( isset( $new_instance['show_date'] ) && ( $new_instance['show_date'] == 1 ) ){
		$instance['show_date'] = 1;
	}else{
		$instance['show_date'] = 0;
	}
	
	if( isset( $new_instance['show_excerpt'] ) && ( $new_instance['show_excerpt'] == 1 ) ){
		$instance['show_excerpt'] = 1;
	}else{
		$instance['show_excerpt'] = 0;
	}
	
	
	$instance['excerpt_words_count'] = $new_instance['excerpt_words_count'];
	
	if( isset( $new_instance['show_number_of_comments'] ) && ( $new_instance['show_number_of_comments'] == 1 ) ){
		$instance['show_number_of_comments'] = 1;
	}else{
		$instance['show_number_of_comments'] = 0;
	}
	
	$instance['posts_count'] = $new_instance['posts_count'];
	$instance['posts_offset'] = $new_instance['posts_offset'];
	$instance['post_orderby'] = $new_instance['post_orderby'];
	$instance['post_order_direction'] = $new_instance['post_order_direction'];
	$instance['categories'] = $new_instance['categories'];
	$instance['tags'] = strip_tags($new_instance['tags']);
	$instance['thumb_on_post'] = $new_instance['thumb_on_post'];
	$instance['thumb_image_size'] = $new_instance['thumb_image_size'];
	$instance['post_image_float'] = $new_instance['post_image_float'];
	
	return $instance;
  }

  /**
   * Get's an instance.
   */
  function widget($args, $instance)
  {
  
	if( otw_is_admin() ){
		echo 'Smart Post Lists Light';
		return;
	}
  
	extract($args);
	
	$init_props = array( 'title', 'show_title', 'show_date', 'show_excerpt', 'excerpt_words_count', 'show_number_of_comments', 'posts_count', 'post_orderby', 'post_order_direction', 'posts_offset', 'categories', 'tags', 'thumb_on_post', 'thumb_image_size', 'post_image_float' );
	
	foreach( $init_props as $prop ){
		if( !isset( $instance[ $prop ] ) ){
			$instance[ $prop ] = '';
		}
	}
	
	$title = apply_filters('widget_title', $instance['title'] );
	$show_title = $instance['show_title'];
	$show_date = $instance['show_date'];
	$show_excerpt = $instance['show_excerpt'];
	$excerpt_words_count = $instance['excerpt_words_count'];
	$show_number_of_comments = $instance['show_number_of_comments'];
	$posts_count = $instance['posts_count'];
	$post_orderby = $instance['post_orderby'];
	$post_order_direction = $instance['post_order_direction'];
	$posts_offset = $instance['posts_offset'];
	$categories = $instance['categories'];
	$tags = $instance['tags'];
	$thumb_on_post = $instance['thumb_on_post'];
	$thumb_image_size = $instance['thumb_image_size'];
	$post_image_float = $instance['post_image_float'];
	
	echo $before_widget;

	if ($title)
	{
        echo $before_title . $title . $after_title;
	}

    // main div
    echo '<ul class="smart-post-list-main">';

	$category_posts_result = '';
	$categories_requested = '';
    $tags_requested = $tags;

	if( is_array( $categories ) && ( count($categories) > 0 ) )
	{
		foreach ($categories as $category_id)
		{
			$categories_requested .= $category_id . ',';
		}

		$categories_requested = substr($categories_requested, 0, -1);
	}

	$category_posts = array();
	// If no filters were set we return empty posts array.
	if (!empty($categories_requested) || !empty($tags_requested)) {
        $category_posts = get_posts( array( 'category' => $categories_requested,
                                            'tag' => $tags_requested,
                                            'numberposts' => $posts_count,
                                            'orderby' => $post_orderby,
                                            'order' => $post_order_direction,
                                            'offset' => (int) $posts_offset));
	}

	if ($category_posts)
	{
        $first = true;

        // Looping all posts for the front end.
		$i = 1;
        foreach($category_posts as $post)
        {
            $category_posts_result .= '<li class="smart-post-list-single-container">';
            $thumb_info = array();

       		// Create the HTML for the post Image Start
       		$category_posts_img = '';
       		switch ($thumb_on_post)
            {
                case 'all':
                    $thumb_info = $this->add_img($post, $thumb_image_size);
                    $category_posts_img = $thumb_info[0];
   		        break;
			    case 'first':
                    if ($first)
                    {
                        $thumb_info = $this->add_img($post, $thumb_image_size);
                        $category_posts_img = $thumb_info[0];
                        $first = false;
                    }
                    break;
            }
            // Create the HTML for the post Image End

    		$open_div_image = '';
            $close_div_image = '';
            $open_div_text = '';
            $close_div_text = '';

            if ($thumb_on_post != 'none') {
                switch ($post_image_float)
                {
                    case 'top_of_excerpt':
                        $open_div_text = '<div class="text-top-of-excerpt">';
                        $close_div_text = '</div>';
                        $open_div_image = '<div class="image-top-of-excerpt img-container">';
                        $close_div_image = '</div>' . $open_div_text;
                        break;

                    case 'right_of_excerpt':
                        $open_div_text = '<div class="text-right-of-excerpt">';
                        $close_div_text = '</div>';
                        $open_div_image = '<div class="image-right-of-excerpt img-container">';
                        $close_div_image = '</div>' . $open_div_text;
                        break;

                    case 'left_of_excerpt':
                        $open_div_text = '<div class="text-left-of-excerpt">';
                        $close_div_text = '</div>';
                        $open_div_image = '<div class="image-left-of-excerpt img-container">';
                        $close_div_image = '</div>' . $open_div_text;
                        break;
                }

           		$category_posts_result .= $open_div_image;
           		$category_posts_result .= $category_posts_img;
                $category_posts_result .= $close_div_image;
            }

            // Create the HTML for the post Text Start
            if ($show_title)
            {
                $category_posts_result .= $this->add_title($post);
            }

            if ($show_date)
            {
                $category_posts_result .= $this->add_date($post);
            }

            if ($show_number_of_comments)
            {
                $category_posts_result .= $this->add_comments_num($post);
            }

            if ($show_excerpt)
            {
                if (has_excerpt($post->ID))
                {
                    $category_posts_result .= $this->add_excerpt($post, $excerpt_words_count);
                }
                else
                {
                    $category_posts_result .= $this->add_content($post, $excerpt_words_count);
                }
            }
            $category_posts_result .= $close_div_text;
            // Create the HTML for the post Text End

            $category_posts_result .= '</li>';

            $i++;
        }
	} else {
		$category_posts_result .= "No posts found in this category";
	}

	echo $category_posts_result;
	echo '</ul>';
	echo $after_widget;
  }

    /**
     * Adds Post Title.
     */
    function add_title($post)
    {
        $result = '';
		$result .= '<a href="' . get_permalink($post->ID) . '" class="smart-post-list-title">' . $post->post_title . '</a>';

		return $result;
    }

    /**
     * Adds Post Date.
     */
    function add_date($post)
    {
        $result = '';
        $result .= '<p class="smart-post-list-date">' . esc_html__('Date', 'post-in-category') . ': ' . date(get_option('date_format'), strtotime($post->post_date)) . '</p>';

		return $result;
    }

    /**
     * Adds Post Comments.
     */
    function add_comments_num($post)
    {
        $result = '';
        $result .= '<p class="smart-post-list-comments"><a href="' . get_permalink($post->ID) . '#comments">' . esc_html__('Comments', 'post-in-category') . ': ' . (string)$post->comment_count . '</a></p>';

        return $result;
    }

    /**
     * Adds Post Content.
     */
	function add_content($post, $words_count)
	{
		$content = $this->prepare_content($post->post_content, $words_count);

        $result = '';
		$result .= '<p class="smart-post-list-excerpt">' . $content . '</p>';

		return $result;
	}

	/**
	 * Prepares the content for the posts list.
	 */
    function prepare_content($content, $words_count)
    {
        $content = strip_tags($content);
        $content = str_replace('&nbsp;', ' ', $content);
        $content = explode(" ", $content);

		if ($words_count < count($content))
		{
			$content = array_slice($content, 0, $words_count);
			array_push($content, "...");
		}
		$content = join(" ", $content);

        return $content;
    }

    /**
     * Getting the Excerpt parameter for post content.
     */
	function add_excerpt($post, $words_count)
	{
		$content = $this->prepare_content($post->post_excerpt, $words_count);

		$result = '';
		$result .= '<p class="smart-post-list-excerpt">' . $content . '</p>';

		return $result;
	}

	/**
	 * Add an Image to the post in the list.
	 */
	function add_img($post, $thumb_image_size)
	{
 	    $size = 'full';
        switch ($thumb_image_size)
        {
            case 'thumbnail':
                $size = 'thumbnail';
                break;
            case 'medium':
                $size = 'medium';
                break;
        }

        $result = '';
        // Get the Post's Featured Image ID
        $attachment_id = get_post_thumbnail_id($post->ID);
        if ($attachment_id) {
            // Get the Post's Featured Image data
            $image_url = wp_get_attachment_image_src($attachment_id, $size);
            $result = '<a href="' . get_permalink($post->ID) . '"><img src="' . $image_url[0] . '" class="smart-post-list-image"></a>';
        }

        $result_array = array($result);

		return $result_array;
	}

	/**
	 * HTML for the Categories list in the Admin part of the Widget.
	 */
    function add_category_node($selectedCategories, $cat_id, $category, $indent = '')
	{
		$option = '<li>' . $indent;
		$option .= '<input type="checkbox" id="'. $this->get_field_id( 'categories' ) .'[]" name="'. $this->get_field_name( 'categories' ) .'[]"';

		foreach ($selectedCategories as $selectedCategory)
		{
			if ($selectedCategory == $cat_id)
			{
				$option .= ' checked="checked"';
			}
		}

		$option .= ' value="' . $cat_id . '" />';
		$option .= $category['name'];
		$option .= ' (' . (string)$category['count'] . ')';
		$option .= '</li>';
		echo $option;

		foreach ($category['childs'] as $child_id => $child)
		{
			$this->add_category_node($selectedCategories, $child_id, $child, $indent . '&nbsp;&nbsp;&nbsp;&nbsp;');
		}
	}
}

/**
 * Loading CSS and JS for the Widget.
 */
function post_in_category_init()
{
    wp_register_style('splw.css', SMART_POST_LIST_PLUGIN_URL . 'splw.css');
	wp_enqueue_style('splw.css');

    if (!is_admin())
    {
        if (wp_script_is('jquery') === false) {
            wp_enqueue_script('jquery');
        }

        wp_register_script('splw.js', SMART_POST_LIST_PLUGIN_URL . 'splw.js');
        wp_enqueue_script('splw.js');
    }

  	return register_widget("PostInCategory");
}

// Register hooks
add_action('admin_print_scripts', 'add_script');
add_action('admin_head', 'add_script_config');

/**
 * Add script to admin page
 */
function add_script() {
    // Build in tag auto complete script
    wp_enqueue_script( 'suggest' );
}

/**
 * Add script to admin page
 */
function add_script_config()
{
?>
    <script type="text/javascript">
    // Function to add auto suggest
    function setSuggest(id) {
        jQuery('#' + id).suggest("<?php echo get_bloginfo('wpurl'); ?>/wp-admin/admin-ajax.php?action=ajax-tag-search&tax=post_tag", {multiple:true, multipleSep: ","});
    }

    function isNumberKey(event) {
        var charCode = (event.which) ? event.which : event.keyCode;
        if (charCode < 32) return true;
        if (charCode > 47 && charCode < 58) return true;
        return false;
    }
    function onThumbOnPostSelect(list, inputId)
    {
        var input = document.getElementById(inputId)
        if (list.selectedIndex > 0)
        {
            input.disabled = false
        }
        else
        {
            input.blur()
            input.disabled = true
        }
    }
    </script>
<?php
}


/**
 * Registger admin menu
 */
function otw_spl_admin_menu(){
	
	add_menu_page( esc_html__( 'Smart Post Lists', 'smart-post-list' ), esc_html__( 'Smart Post Lists', 'smart-post-list' ), 'manage_options', 'otw-spl', 'otw_spl_info' );
	add_submenu_page( 'otw-spl', esc_html__( 'Plugin Options', 'smart-post-list'), esc_html__('Plugin Options', 'smart-post-list'), 'manage_options', 'otw-spl', 'otw_spl_info' );
}

/**
 * Info page
 */
function otw_spl_info(){
	
	require_once( 'include/otw_spl_plugin_options.php' );
}

/**
 * Init function
 */
if( !function_exists( 'otw_spll_init' ) ){
	
	function otw_spll_init(){
		
		global $otw_spll_plugin_url, $otw_spll_factory_component, $otw_spll_factory_object, $otw_spll_plugin_id;
		
		if( is_admin() ){
			
			add_action('admin_menu', 'otw_spl_admin_menu');
			
			add_filter('otwfcr_notice', 'otw_spll_factory_message' );
		}
		
		$otw_spll_factory_component = otw_load_component( 'otw_factory' );
		$otw_spll_factory_object = otw_get_component( $otw_spll_factory_component );
		$otw_spll_factory_object->add_plugin( $otw_spll_plugin_id, dirname( __FILE__ ).'/splw.php', array( 'menu_parent' => 'otw-spl', 'lc_name' => esc_html__( 'License Manager', 'smart-post-list' ), 'menu_key' => 'otw-spl' ) );
		
		include_once( plugin_dir_path( __FILE__ ).'include/otw_labels/otw_spll_factory_object.labels.php' );
		$otw_spll_factory_object->init();
		
		include_once( 'include/otw_spll_process_actions.php' );
	}
}

/**
 * factory messages
 */
if( !function_exists( 'otw_spll_factory_message' ) ){
	function otw_spll_factory_message( $params ){
		
		global $otw_spll_plugin_id;
		
		if( isset( $params['plugin'] ) && $otw_spll_plugin_id == $params['plugin'] ){
			
			//filter out some messages if need it
		}
		if( isset( $params['message'] ) )
		{
			return $params['message'];
		}
		return $params;
	}
}
add_action( 'widgets_init', 'post_in_category_init' );
add_action('init', 'otw_spll_init' );
?>