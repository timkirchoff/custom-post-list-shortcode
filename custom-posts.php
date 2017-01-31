<?php
   /*
   Plugin Name: Custom Post List Shortcode
   Description: Configurable custom post list shortcode
   Version: 1.0
   Author: Tim Kirchoff
   */

/**
 * Post List Shortcode
 */

function program_list_function($atts) {
    extract(shortcode_atts(array(
      'type' => 'some_custom_post_type', // sets default type 
      'count' => -1, // sets default count to show all
      'online' => '',
      'state' => '',
      'degrees' => '',
      'programs' => '',
      'orderby' => 'name', // sets defatul orderby to name
      'template' => 'all', // sets default template to all 
    ), $atts));


    $page_id = get_the_ID();


    // setting up the state variable
    if($state == '') { // if the shortcode state identifier is blank
		if(get_field('program_state')) {  // search if the content page has a state set
			$state = get_field('program_state');
			if ($state == 'none') {
				$state = '';
			}
		} else {
			$state = ''; // if the content page has no state then set it the variable to null
		}
	}


	// setting up the program type
	if(($programs == '') || ($programs == 'none')) { // if the shortcode programs identifier is blank 
		$program_terms = get_the_terms( $page_id, 'tax_program' ); // get custom taxonomy values
		if ($program_terms != '') { // if there are program categories selected
			$program_term_names = wp_list_pluck( $program_terms, 'slug' );
		} else {
			$program_term_names = '';
		}
	} else {
		$program_term_names = explode(',', $programs);
	}


	// setting up the degree type
	if(($degrees == '') || ($degrees == 'none')) { // if the shortcode degrees identifier is blank 
		$degree_terms = get_the_terms( $page_id, 'tax_degree' ); // get custom taxonomy values
		if ($degree_terms != '') { // if there are degree categories selected
			$degree_term_names = wp_list_pluck( $degree_terms, 'slug' );
		} else {
			$degree_term_ids = '';
		}
	} else {
		$degree_term_names = explode(',', $degrees);
	}


	// setup the main query for schools
	$school_search = array(
	    'post_type' => $type,
      	'posts_per_page' => $count,
      	'order' => 'ASC',
        'orderby' => $orderby,
	);


	// if program and degree categories are set, update the query with both values
	if (( $program_term_names != '' ) && ( $degree_term_names != '' )) {
	  	$school_search['tax_query'] =array(
	        'relation' => 'AND',
      		array(
			    'taxonomy' => 'tax_program',
			    'terms' => $program_term_names,
			    'field' => 'slug',
			    'operator' => 'IN'
			),
			array(
			    'taxonomy' => 'tax_degree',
			    'terms' => $degree_term_names,
			    'field' => 'slug',
			    'operator' => 'IN'
			)
	    );
	// if the program category only is set, update the query with both values
	} elseif ( $program_term_names != '' ) {
	  	$school_search['meta_query'] =array(
	        array(
			    'taxonomy' => 'tax_program',
			    'terms' => $program_term_names,
			    'field' => 'slug',
			    'operator' => 'IN'
			)
	    );
	// if the degree category only is set, update the query with both values
	} elseif ( $degree_term_names != '' ) {
	  	$school_search['meta_query'] =array(
	        array(
			    'taxonomy' => 'tax_degree',
			    'terms' => $degree_term_names,
			    'field' => 'slug',
			    'operator' => 'IN'
			)
	    );
	}


	//if state and online are set, update the query with both values
	if (( $state != '' ) && ( $online != '' )) {
	  	$school_search['meta_query'] =array(
	        'relation' => 'AND',
            array(
                'key' => 'program_state', // name of custom field
                'value' => $state, 
                'compare' => 'LIKE'
            ),
            array(
	            'key' => 'online', // name of custom field
	            'value' => $online, 
	            'compare' => 'LIKE' 
	        )
	    );
	// if only state is set update the query
	} elseif ( $state != '' ) {
		$school_search['meta_query'] =array(
	        array(
                'key' => 'program_state', // name of custom field
                'value' => $state, 
                'compare' => 'LIKE'
            )
	    );
	// if only online is set update the query
	} elseif ( $online != '' ) {
		$school_search['meta_query'] =array(
	        array(
	            'key' => 'online', // name of custom field
	            'value' => $online, 
	            'compare' => 'LIKE'
	        )
	    );
	}


	// set the return string variable
	$return_string = '';


	$query = new WP_Query($school_search);


	// query for schools
	if ( $query->have_posts() ) { 
		
		$return_string .= '<div class="school-list all-programs ' . $template . '-list">';
		$post_num = 1;

		while ( $query->have_posts() ) : $query->the_post();
		
			$program_title = get_the_title();						
			$specific_program_name = get_field('specific_program_name');
			$program_state = get_field('program_state');						
			$program_city = get_field('program_city');
			$program_url = get_field('deg_url');						
			$program_writeup = get_field('program_writeup');						
			$program_accred = get_field('program_accred');
			$univ_enroll = get_field('univ_enroll');
			$univ_acceptance = get_field('univ_acceptance');	
			$degree_url = get_field('deg_url');
			$school_name = get_field('school_name');			
			$featured_image_url = get_field('featured_image_url');


			// setting up the all template
			if($template == 'all') {
	    		 $return_string .= '<div class="school-list-box">
	    								<div class="featured-school-program">
	    									<a href="' . $program_url . '" target="_blank">'. $program_title .'</a>
	    								</div>				
										<div class="univ-stats">
											<div class="school-facts">
												<strong>School Facts:</strong>
											</div>
											<div class="univ-enrollment">
												<strong>Students:</strong> '. $univ_enroll .'
											</div>												
											<div class="univ-acceptance">
												<strong>Acceptance:</strong> '. $univ_acceptance .'
											</div>
										</div>								
										<div class="program-accreditation">'. $program_accred .'</div>
										<div class="featured-school-logo">';
									    	if(get_field('featured_image_url')) { 
									    		$featured_img_url = get_field('featured_image_url');
												$return_string .= '<img class="listing-image" src="' . $featured_img_url . '">';
											}
					 $return_string .= '</div>
										<div class="inner-wrapper">
											<div class="school-location">'. $program_city .' | '. $program_state .'</div>
											<div class="program-writeup">'. $program_writeup .'</div>
											<div class="program-text">Programs:</div>
											<ul>
			        							<li>' . $specific_program_name . '</li>
			        						</ul>
			        					</div>
			        				</div>';
			}


			// setting up the featured template
			if($template == 'featured') {
			     $return_string .= '<div class="school-list-box">
			     						<div class="featured-school-program">
	    									<a href="' . $program_url . '" target="_blank">'. $program_title .'</a>
	    								</div>
	    								<div class="featured-program-accreditation">'. $program_accred .'</div>
	    								<div class="featured-school-logo">
	    									<a href="' . $program_url . '" target="_blank">';
										    	if(get_field('featured_image_url')) { 
										    		$featured_img_url = get_field('featured_image_url');
													$return_string .= '<img class="listing-image" src="' . $featured_img_url . '">';
												}
						 $return_string .= '</a>
						 				</div>
						 				<div class="featured-school-title">'. $school_name .'</div>
						 				<div class="featured-inner-wrapper">
						 					<div class="featured-program-writeup">'. $program_writeup .'</div>
						 					<div class="featured-program-text">Online Programs:</div>
						 					<ul>
						 						<li><a href="' . $program_url . '" target="_blank">' . $program_title . '</a></li>
						 					</ul>
						 				</div>
						 			<div>';
			}


			// setting up the native template
			if($template == 'native') {
			     $return_string .= '<div class="school-nat">
			     						<div class="featured-program-nat">'. $program_writeup_native .'</div>
						 			<div>';
			}


			// setting up the simple template
			if($template == 'simple') {
	    		 $return_string .= '<div class="school-list-box">
	    								<div class="featured-school-program">
	    									<a href="' . $program_url . '" target="_blank">'. $program_title .'</a>
	    								</div>
	    								<div class="featured-program-accreditation">'. $program_accred .'</div>
										<div class="featured-school-title">'. $school_reference_title .'</div>							
										<div class="program-accreditation">'. $program_accred .'</div>
										<div class="featured-school-logo">';
									    	if(get_field('featured_image_url')) { 
									    		$featured_img_url = get_field('featured_image_url');
												$return_string .= '<img class="listing-image" src="' . $featured_img_url . '">';
											}
					 $return_string .= '</div>
										<div class="inner-wrapper">
											<div class="school-location">'. $program_city .' | '. $program_state .'</div>
											<div class="program-writeup">'. $program_writeup .'</div>
											<div class="featured-inner-wrapper">
												<div class="featured-program-writeup">'. $program_writeup .'</div>
												<div class="featured-program-text">Online Programs:</div>
												<ul>
				        							<li><a href="' . $program_url . '" target="_blank">' . $program_title . '</a></li>
				        						</ul>
				        					</div>
			        					</div>
			        				</div>';
			}


		    $post_num++; // give each program a unique id

		endwhile; wp_reset_query();
		$return_string .= '</div>';
	} // end if

	return $return_string;
} // end function all_programs_function()

add_shortcode('programs', 'program_list_function');

?>