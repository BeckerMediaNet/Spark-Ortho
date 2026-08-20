<?php
/**
 * Filter the term clauses using the arguments, specifically for the wp_dropdown_categories.
 *
 * @see http://wordpress.stackexchange.com/questions/207655/restrict-taxonomy-dropdown-to-post-type
 * @see https://www.dfactory.eu/get_terms-post-type/
 * 
 * @param array $clauses 
 * @param string $taxonomy
 * @param array $args
 * 
 * @return array
 */
add_filter( 'terms_clauses', 'lvl_post_type_terms_clauses', 10, 3 );
function lvl_post_type_terms_clauses( $clauses, $taxonomy, $args ) {
	// Make sure we have a post_type argument to run with.
	if( empty( $args['post_type'] ) )
		return $clauses;
    
	global $wpdb;

	// Set up the post types in an array
	$post_types = array();
	
	// If the argument is an array, check each one and cycle through the post types
	if( is_array( $args['post_type'] ) ) {
		
		// All possible, public post types
		$possible_post_types = get_post_types( array( 'public' => true ) );
		
		// Cycle through the post types, add them to our array if they are public
		foreach( $args['post_type'] as $post_type ) {
			if( in_array( $post_type, $possible_post_types ) )
				$post_types[] = "'" . esc_attr( $post_type ) . "\'";
		}
		
	// If the post type argument is a string, not an array
	} elseif( is_string( $args['post_type'] ) ) {
		$post_types[] = "'" . esc_attr( $args['post_type'] ) . "'";
	}

	// If we have valid post types, build the new sql
	if( !empty( $post_types ) ) {
		$post_types_string = implode( ',', $post_types );
		$fields = str_replace( 'tt.*', 'tt.term_taxonomy_id, tt.term_id, tt.taxonomy, tt.description, tt.parent', $clauses['fields'] );
		
		$clauses['fields'] = 'DISTINCT ' . esc_sql( $fields ) . ', COUNT(t.term_id) AS count';
		$clauses['join'] .= ' INNER JOIN ' . $wpdb->term_relationships . ' AS r ON r.term_taxonomy_id = tt.term_taxonomy_id INNER JOIN ' . $wpdb->posts . ' AS p ON p.ID = r.object_id';
		$clauses['where'] .= ' AND p.post_type IN (' . $post_types_string . ')';
		$clauses['orderby'] = 'GROUP BY t.term_id ' . $clauses['orderby'];
	}

    return $clauses;
}