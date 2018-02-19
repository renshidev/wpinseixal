<?php

if(!function_exists('renshi_get_categories')) {
	/**
	 * Get Post Categories
	 * @return array
	 */
	function renshi_get_categories() {
		$post_categories = array();
		
		$cats = get_terms(array(
			'taxonomy' => 'post-category'
		));
		
		foreach($cats as $cat) {
			$post_categories[$cat->slug] = $cat->name;
		}

		return $post_categories;
	}
}

if(!function_exists('renshi_get_categories_vc')) {
	/**
	 * Function that returns array of posts categories formatted for Visual Composer
	 *
	 * @return array array of posts categories where key is term title and value is term slug
	 *
	 * @see renshi_get_categories
	 */

	function renshi_get_categories_vc() {

		return array_flip(renshi_get_categories());
	}
}

if(!function_exists('renshi_get_shortcode_module_template_part')) {
	/**
	 * Loads module template part.
	 *
	 * @param $template
	 * @param $module
	 * @param $part
	 * @param string $slug
	 * @param array $params
	 *
	 * @return string
	 */
	function renshi_get_post_module_template_part($template, $module, $part, $slug = '', $params = array()) {

		//HTML Content from template
		$html          = '';
		$template_path = get_template_directory().'/'.$module.'/'.$part;

		$temp = $template_path.'/'.$template;
		if(is_array($params) && count($params)) {
			extract($params);
		}

		$template = '';
		
		if (!empty($temp)) {
			if (!empty($slug)) {
				$template = "{$temp}-{$slug}.php";
				
				if(!file_exists($template)) {
					$template = $temp.'.php';
				}
			} else {
				$template = $temp.'.php';
			}
		}
		
		if($template) {
			ob_start();
			include($template);
			$html = ob_get_clean();
		}

		return $html;
	}
}

if(!function_exists('renshi_get_post_attributes')) {
	/**
	 * Return Post Attribute Custom Post Type associative array where key is post id and value is post title.
	 *
	 * return array
	 */
	function renshi_get_post_attributes() {
		$post_attributes = array();
		
		if(renshi_is_wpml_installed()) {
			global $wpdb;
			
			$lang = ICL_LANGUAGE_CODE;
			
			$sql = "SELECT p.*
					FROM {$wpdb->prefix}posts p
					LEFT JOIN {$wpdb->prefix}icl_translations icl_t ON icl_t.element_id = p.ID 
					WHERE p.post_type = 'post-attributes'
					AND p.post_status = 'publish'
					AND icl_t.language_code='{$lang}'";
			
			$query_results = $wpdb->get_results($sql);
			
			if($query_results) {
				global $post;
				
				foreach ($query_results as $post) {
					setup_postdata($post);
					$post_attributes[get_the_ID()] = get_the_title();
				}
			}
		} else {
			$args = array(
				'post_type'   => 'post-attributes',
				'post_status' => 'publish',
				'posts_per_page' => -1
			);
			
			$query_results = new WP_Query($args);
			
			if($query_results->have_posts()) {
				
				while($query_results->have_posts()) {
					
					$query_results->the_post();
					
					$post_attributes[get_the_ID()] = get_the_title();
				}
			}
		}

		wp_reset_postdata();

		return $post_attributes;
	}
}

if(!function_exists('renshi_get_single_post_item')) {
	/**
	 * Loads single post-item template
	 *
	 */
	function renshi_get_single_post_item() {
		$params = array(
			'holder_class'  => 'mkdf-post-item-single-holder',
			'post_sections' => renshi_check_post_sections()
		);

		echo renshi_get_post_module_template_part('single/holder', 'posts', 'templates', '', $params);
	}
}

if(!function_exists('renshi_get_post_info_part')) {
	/**
	 * @param $part
	 *
	 * @return bool
	 */
	function renshi_get_post_info_part($part) {
		if(empty($part)) {
			return false;
		}

		echo renshi_get_post_module_template_part($part, 'posts', 'templates', '', array());
	}
}

if(!function_exists('renshi_check_post_sections')) {
	/**
	 * check if post item sections are enabled
	 *
	 */
	function renshi_check_post_sections() {

		$sections_array = array(
			'renshi_show_info_section',
			'renshi_show_post_plan_section',
			'renshi_show_location_section',
			'renshi_show_gallery_section',
			'renshi_show_review_section',
			'renshi_show_custom_section_1',
			'renshi_show_custom_section_2',
		);
		$return_array   = array();

		foreach($sections_array as $section) {
			$section_key                         = str_replace('renshi_', '', $section);
			$return_array[$section_key]['value'] = get_post_meta(get_the_ID(), $section, true);

			switch($section_key) {
				case 'show_info_section' :
					$return_array[$section_key]['icon']  = 'icon_documents';
					$return_array[$section_key]['title'] = esc_html__('INFORMATION','mikado-posts');
					$return_array[$section_key]['id']    = 'post-item-info-id';
					break;
				case 'show_post_plan_section' :
					$return_array[$section_key]['icon']  = 'icon_map';
					$return_array[$section_key]['title'] = esc_html__('TOUR PLAN','mikado-posts');
					$return_array[$section_key]['id']    = 'post-item-plan-id';
					break;
				case 'show_location_section' :
					$return_array[$section_key]['icon']  = 'icon_pin';
					$return_array[$section_key]['title'] = esc_html__('LOCATION','mikado-posts');
					$return_array[$section_key]['id']    = 'post-item-location-id';
					break;
				case 'show_gallery_section' :
					$return_array[$section_key]['icon']  = 'icon_camera';
					$return_array[$section_key]['title'] = esc_html__('GALLERY','mikado-posts');
					$return_array[$section_key]['id']    = 'post-item-gallery-id';
					break;
				case 'show_review_section' :
					$return_array[$section_key]['icon']  = 'icon_group';
					$return_array[$section_key]['title'] = esc_html__('REVIEWS','mikado-posts');
					$return_array[$section_key]['id']    = 'post-item-review-id';
					break;
				case 'show_custom_section_1' :

					$custom_section1_title = (get_post_meta(get_the_ID(), 'renshi_custom_section1_title', true) != '') ? get_post_meta(get_the_ID(), 'renshi_custom_section1_title', true) : esc_html__('Custom Section 1', 'mikado-posts');
					$return_array[$section_key]['icon']  = 'icon_book';
					$return_array[$section_key]['title'] = $custom_section1_title;
					$return_array[$section_key]['id']    = 'post-item-custom1-id';
					break;
				case 'show_custom_section_2' :
					$custom_section2_title = (get_post_meta(get_the_ID(), 'renshi_custom_section2_title', true) != '') ? get_post_meta(get_the_ID(), 'renshi_custom_section2_title', true) : esc_html__('Custom Section 2', 'mikado-posts');
					$return_array[$section_key]['icon']  = 'icon_book';
					$return_array[$section_key]['title'] =  $custom_section2_title;
					$return_array[$section_key]['id']    = 'post-item-custom2-id';
					break;
			}
		}

		return $return_array;
	}
}

if(!function_exists('renshi_get_attachment_meta')) {
	/**
	 * Function that returns attachment meta data from attachment id
	 *
	 * @param $attachment_id
	 * @param array $keys sub array of attachment meta
	 *
	 * @return array|mixed
	 */
	function renshi_get_attachment_meta($attachment_id, $keys = array()) {
		$meta_data = array();

		//is attachment id set?
		if(!empty($attachment_id)) {
			//get all post meta for given attachment id
			$meta_data = get_post_meta($attachment_id, '_wp_attachment_metadata', true);

			//is subarray of meta array keys set?
			if(is_array($keys) && count($keys)) {
				$sub_array = array();

				//for each defined key
				foreach($keys as $key) {
					//check if that key exists in all meta array
					if(array_key_exists($key, $meta_data)) {
						//assign key from meta array for current key to meta subarray
						$sub_array[$key] = $meta_data[$key];
					}
				}

				//we want meta array to be subarray because that is what used whants to get
				$meta_data = $sub_array;
			}
		}

		//return meta array
		return $meta_data;
	}
}

if(!function_exists('renshi_get_attachment_id_from_url')) {
	/**
	 * Function that retrieves attachment id for passed attachment url
	 *
	 * @param $attachment_url
	 *
	 * @return null|string
	 */
	function renshi_get_attachment_id_from_url($attachment_url) {
		global $wpdb;
		$attachment_id = '';

		//is attachment url set?
		if($attachment_url !== '') {
			//prepare query

			$query = $wpdb->prepare("SELECT ID FROM {$wpdb->posts} WHERE guid=%s", $attachment_url);

			//get attachment id
			$attachment_id = $wpdb->get_var($query);
		}

		//return id
		return $attachment_id;
	}
}

if(!function_exists('renshi_get_attachment_meta_from_url')) {
	/**
	 * Function that returns meta array for give attachment url
	 *
	 * @param $attachment_url
	 * @param array $keys sub array of attachment meta
	 *
	 * @return array|mixed
	 *
	 * @see renshi_get_attachment_id_from_url()
	 * @see renshi_get_attachment_meta()
	 *
	 * @version 0.1
	 */
	function renshi_get_attachment_meta_from_url($attachment_url, $keys = array()) {
		$attachment_meta = array();

		//get attachment id for attachment url
		$attachment_id = renshi_get_attachment_id_from_url($attachment_url);

		//is attachment id set?
		if(!empty($attachment_id)) {
			//get post meta
			$attachment_meta = renshi_get_attachment_meta($attachment_id, $keys);
		}

		//return post meta
		return $attachment_meta;
	}
}

if(!function_exists('renshi_resize_image')) {
	/**
	 * Functin that generates custom thumbnail for given attachment
	 *
	 * @param null $attach_id id of attachment
	 * @param null $attach_url URL of attachment
	 * @param int $width desired height of custom thumbnail
	 * @param int $height desired width of custom thumbnail
	 * @param bool $crop whether to crop image or not
	 *
	 * @return array returns array containing img_url, width and height
	 *
	 * @see renshi_get_attachment_id_from_url()
	 * @see get_attached_file()
	 * @see wp_get_attachment_url()
	 * @see wp_get_image_editor()
	 */
	function renshi_resize_image($attach_id = null, $attach_url = null, $width = null, $height = null, $crop = true) {
		$return_array = array();

		//is attachment id empty?
		if(empty($attach_id) && $attach_url !== '') {
			//get attachment id from url
			$attach_id = renshi_get_attachment_id_from_url($attach_url);
		}

		if(!empty($attach_id) && (isset($width) && isset($height))) {

			//get file path of the attachment
			$img_path = get_attached_file($attach_id);

			//get attachment url
			$img_url = wp_get_attachment_url($attach_id);

			//break down img path to array so we can use it's components in building thumbnail path
			$img_path_array = pathinfo($img_path);

			//build thumbnail path
			$new_img_path = $img_path_array['dirname'].'/'.$img_path_array['filename'].'-'.$width.'x'.$height.'.'.$img_path_array['extension'];

			//build thumbnail url
			$new_img_url = str_replace($img_path_array['filename'], $img_path_array['filename'].'-'.$width.'x'.$height, $img_url);

			//check if thumbnail exists by it's path
			if(!file_exists($new_img_path)) {
				//get image manipulation object
				$image_object = wp_get_image_editor($img_path);

				if(!is_wp_error($image_object)) {
					//resize image and save it new to path
					$image_object->resize($width, $height, $crop);
					$image_object->save($new_img_path);

					//get sizes of newly created thumbnail.
					///we don't use $width and $height because those might differ from end result based on $crop parameter
					$image_sizes = $image_object->get_size();

					$width  = $image_sizes['width'];
					$height = $image_sizes['height'];
				}
			}

			//generate data to be returned
			$return_array = array(
				'img_url'    => $new_img_url,
				'img_width'  => $width,
				'img_height' => $height
			);
		} //attachment wasn't found, probably because it comes from external source
		elseif($attach_url !== '' && (isset($width) && isset($height))) {
			//generate data to be returned
			$return_array = array(
				'img_url'    => $attach_url,
				'img_width'  => $width,
				'img_height' => $height
			);
		}

		return $return_array;
	}
}

if(!function_exists('renshi_generate_thumbnail')) {
	/**
	 * Generates thumbnail img tag. It calls renshi_resize_image function which resizes img on the fly
	 *
	 * @param null $attach_id attachment id
	 * @param null $attach_url attachment URL
	 * @param  int $width width of thumbnail
	 * @param int $height height of thumbnail
	 * @param bool $crop whether to crop thumbnail or not
	 *
	 * @return string generated img tag
	 *
	 * @see renshi_resize_image()
	 * @see renshi_get_attachment_id_from_url()
	 */
	function renshi_generate_thumbnail($attach_id = null, $attach_url = null, $width = null, $height = null, $crop = true) {
		//is attachment id empty?
		if(empty($attach_id)) {
			//get attachment id from attachment url
			$attach_id = renshi_get_attachment_id_from_url($attach_url);
		}

		if(!empty($attach_id) || !empty($attach_url)) {
			$img_info = renshi_resize_image($attach_id, $attach_url, $width, $height, $crop);
			$img_alt  = !empty($attach_id) ? get_post_meta($attach_id, '_wp_attachment_image_alt', true) : '';

			if(is_array($img_info) && count($img_info)) {
				return '<img src="'.$img_info['img_url'].'" alt="'.$img_alt.'" width="'.$img_info['img_width'].'" height="'.$img_info['img_height'].'" />';
			}
		}

		return '';
	}
}

if(!function_exists('renshi_inline_style')) {
	/**
	 * Function that echoes generated style attribute
	 *
	 * @param $value string | array attribute value
	 *
	 * @see renshi_get_inline_style()
	 */
	function renshi_inline_style($value) {
		echo renshi_get_inline_style($value);
	}
}

if(!function_exists('renshi_get_inline_style')) {
	/**
	 * Function that generates style attribute and returns generated string
	 *
	 * @param $value string | array value of style attribute
	 *
	 * @return string generated style attribute
	 *
	 * @see renshi_get_inline_style()
	 */
	function renshi_get_inline_style($value) {
		return renshi_get_inline_attr($value, 'style', ';');
	}
}

if(!function_exists('renshi_class_attribute')) {
	/**
	 * Function that echoes class attribute
	 *
	 * @param $value string value of class attribute
	 *
	 * @see renshi_get_class_attribute()
	 */
	function renshi_class_attribute($value) {
		echo renshi_get_class_attribute($value);
	}
}

if(!function_exists('renshi_get_class_attribute')) {
	/**
	 * Function that returns generated class attribute
	 *
	 * @param $value string value of class attribute
	 *
	 * @return string generated class attribute
	 *
	 * @see renshi_get_inline_attr()
	 */
	function renshi_get_class_attribute($value) {
		return renshi_get_inline_attr($value, 'class', ' ');
	}
}

if(!function_exists('renshi_get_inline_attr')) {
	/**
	 * Function that generates html attribute
	 *
	 * @param $value string | array value of html attribute
	 * @param $attr string name of html attribute to generate
	 * @param $glue string glue with which to implode $attr. Used only when $attr is array
	 *
	 * @return string generated html attribute
	 */
	function renshi_get_inline_attr($value, $attr, $glue = '') {
		if(!empty($value)) {

			if(is_array($value) && count($value)) {
				$properties = implode($glue, $value);
			} elseif($value !== '') {
				$properties = $value;
			}

			return $attr.'="'.esc_attr($properties).'"';
		}

		return '';
	}
}

if(!function_exists('renshi_inline_attr')) {
	/**
	 * Function that generates html attribute
	 *
	 * @param $value string | array value of html attribute
	 * @param $attr string name of html attribute to generate
	 * @param $glue string glue with which to implode $attr. Used only when $attr is array
	 *
	 * @return string generated html attribute
	 */
	function renshi_inline_attr($value, $attr, $glue = '') {
		echo renshi_get_inline_attr($value, $attr, $glue);
	}
}

if(!function_exists('renshi_get_inline_attrs')) {
	/**
	 * Generate multiple inline attributes
	 *
	 * @param $attrs
	 *
	 * @return string
	 */
	function renshi_get_inline_attrs($attrs) {
		$output = '';

		if(is_array($attrs) && count($attrs)) {
			foreach($attrs as $attr => $value) {
				$output .= ' '.renshi_get_inline_attr($value, $attr);
			}
		}

		$output = ltrim($output);

		return $output;
	}
}

if(!function_exists('renshi_visual_composer_installed')) {
	/**
	 * Function that checks if visual composer installed
	 * @return bool
	 */
	function renshi_visual_composer_installed() {
		//is Visual Composer installed?
		if(class_exists('WPBakeryVisualComposerAbstract')) {
			return true;
		}

		return false;
	}
}

if(!function_exists('renshi_is_wpml_installed')) {
	/**
	 * Function that checks if WPML plugin is installed
	 * @return bool
	 *
	 * @version 0.1
	 */
	function renshi_is_wpml_installed() {
		return defined('ICL_SITEPRESS_VERSION');
	}
}

if(!function_exists('renshi_execute_shortcode')) {
	/**
	 * @param $shortcode_tag - shortcode base
	 * @param $atts - shortcode attributes
	 * @param null $content - shortcode content
	 *
	 * @return mixed|string
	 */
	function renshi_execute_shortcode($shortcode_tag, $atts, $content = null) {
		global $shortcode_tags;

		if(!isset($shortcode_tags[$shortcode_tag])) {
			return;
		}

		if(is_array($shortcode_tags[$shortcode_tag])) {
			$shortcode_array = $shortcode_tags[$shortcode_tag];

			return call_user_func(array(
				$shortcode_array[0],
				$shortcode_array[1]
			), $atts, $content, $shortcode_tag);
		}

		return call_user_func($shortcode_tags[$shortcode_tag], $atts, $content, $shortcode_tag);
	}
}

if(!function_exists('renshi_get_search_pages')) {
	/**
	 * @param bool $first_empty
	 *
	 * @return array
	 */
	function renshi_get_search_pages($first_empty = false) {
		$posts_args = array(
			'post_type'   => array('page'),
			'post_status' => 'publish',
			'meta_key'    => '_wp_page_template',
			'meta_value'  => 'post-types/posts/templates/search-post-item-template.php'
		);

		$posts_query = new WP_Query($posts_args);

		$search_pages = array();

		if($first_empty) {
			$search_pages[''] = '';
		}

		if($posts_query->have_posts()) {
			while($posts_query->have_posts()) {
				$posts_query->the_post();
				$search_pages[get_the_ID()] = get_the_title();
			}
		}

		return $search_pages;
	}
}

if(!function_exists('renshi_get_checkout_pages')) {
	/**
	 * @param bool $first_empty
	 *
	 * @return array
	 */
	function renshi_get_checkout_pages($first_empty = false) {
		$posts_args = array(
			'post_type'   => array('page'),
			'post_status' => 'publish',
			'meta_key'    => '_wp_page_template',
			'meta_value'  => 'post-types/posts/templates/checkout/post-checkout.php'
		);

		$booking_pages = new WP_Query($posts_args);

		$search_pages = array();

		if($first_empty) {
			$search_pages[''] = esc_html__('No Selected', 'mikado-posts');
		}

		if($booking_pages->have_posts()) {
			while($booking_pages->have_posts()) {
				$booking_pages->the_post();
				$search_pages[get_the_ID()] = get_the_title();
			}
		}

		return $search_pages;
	}
}

if(!function_exists('renshi_get_search_page_url')) {
	/**
	 * @return false|string
	 */
	function renshi_get_search_page_url() {
	    $default_url = get_post_type_archive_link('post-item');
        if(!renshi_theme_installed()) {
	        return $default_url;
        }

	    $option = gotravel_mikado_options()->getOptionValue('posts_search_main_page');

	    if(empty($option)) {
		    return $default_url;
	    }

	    return get_permalink($option);
    }
}

if(!function_exists('renshi_paypal_enabled')) {
	/**
	 * @return bool|mixed|void
	 */
	function renshi_paypal_enabled() {
        $default_enabled = apply_filters('renshi_enable_paypal', true);

	    if(renshi_theme_installed()) {
		    $option = gotravel_mikado_options()->getOptionValue('posts_enable_paypal');
	    }

	    $option = empty($option) ? $default_enabled : $option === 'yes';

	    return $option;
    }
}

if(!function_exists('renshi_get_paypal_facilitator_id')) {
	/**
	 * @return bool|mixed|void
	 */
	function renshi_get_paypal_facilitator_id() {
	    $default_facilitator = apply_filters('renshi_facilitator', '');

	    if(renshi_theme_installed()) {
		    $option = gotravel_mikado_options()->getOptionValue('paypal_facilitator_id');
	    }

	    $option = empty($option) ? $default_facilitator : $option;

	    return $option;
    }
}

if(!function_exists('renshi_get_paypal_currency')) {
	/**
	 * @return bool|mixed|void
	 */
	function renshi_get_paypal_currency() {
	    $default_currency = apply_filters('renshi_paypal_currency', 'USD');

	    if(renshi_theme_installed()) {
		    $option = gotravel_mikado_options()->getOptionValue('paypal_currency');
	    }

	    $option = empty($option) ? $default_currency : $option;

	    return $option;
    }
}
