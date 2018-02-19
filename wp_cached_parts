<?php
/**
 * @param  $slug (string) (required) The slug name for the generic template.
 * @param  $name (string) (optional) The name of the specialized template.
 * @return (string) HTML output of the template part. 
 */ 
if (!function_exists('renshi_cached_template_part')) {
	function renshi_cached_template_part($slug, $name = '', $ttl = 3600, $sitewide = false) {
		if (strlen($name) > 0) {
			$transient_id = $slug.'-'.$name;
		} else {
			$transient_id = $slug;
		}
		$cached_template_part = ($sitewide) ? get_site_transient( $transient_id ) : get_transient( $transient_id ); 
		if ( false === $cached_template_part ) {
			ob_start();
			get_template_part($slug,$name);
			$cached_template_part = ob_get_contents();
			ob_end_clean();
			if ($sitewide) {
				set_site_transient( $transient_id, $cached_template_part, $ttl );
			} else {
				set_transient( $transient_id, $cached_template_part, $ttl );
			}
		}
		echo $cached_template_part;
		return true;
	}
}
?>
