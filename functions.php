<?php
if(!class_exists('cf7ic_invitation_code_functions')){
    class cf7ic_invitation_code_functions{

        public function __construct() {
            add_action( 'admin_enqueue_scripts', array('cf7ic_invitation_code_functions','cf7ci_enqueue_scripts'),30 );
            add_filter( 'manage_cf7ic_invite_codes_posts_columns', array('cf7ic_invitation_code_functions','insert_posts_columns'));
            add_action( 'manage_cf7ic_invite_codes_posts_custom_column' , array('cf7ic_invitation_code_functions','insert_post_type_custom_columns'));
            add_filter( 'wpcf7_validate_text', array('cf7ic_invitation_code_functions','cf7ic_validate_field'), 50, 3);
            add_filter( 'wpcf7_validate_text*', array('cf7ic_invitation_code_functions','cf7ic_validate_field'), 50, 3);
            add_action( 'wpcf7_mail_sent', array('cf7ic_invitation_code_functions','cf7ic_after_mail_sent'));
            
            // add_action( 'wp_ajax_cf7ic_invitation_code_validation',array($this,'cf7ic_invitation_code_validation_callback'));
            // add_action( 'wp_ajax_nopriv_cf7ic_invitation_code_validation', array($this,'cf7ic_invitation_code_validation_callback'));

            add_action( 'wp_ajax_cf7ic_invitation_post_validation',array($this,'cf7ic_invitation_post_validation_callback'));
            add_action( 'wp_ajax_nopriv_cf7ic_invitation_post_validation', array($this,'cf7ic_invitation_post_validation_callback'));
        }

        static function insert_posts_columns($columns){
            $columns['cf7ic-invitation-code'] = __( 'Invitation Code', 'invitation-code-for-contact-form-7' );
            $columns['cf7ic-expiration-date'] = __( 'Expiration', 'invitation-code-for-contact-form-7' );
            $columns['cf7ic-remaining-amt'] = __( 'Remaining Amount', 'invitation-code-for-contact-form-7' );
            return $columns;
        }

        static function insert_post_type_custom_columns($column){
            global $post;
            if('cf7ic-invitation-code' == $column){
                $invitation_code = get_post_meta($post->ID, 'cf7ic_invitation_code', true);
                if(isset($invitation_code) && !empty($invitation_code)){
                    printf('<span>%1$s</span>',$invitation_code);
                }
            }
            if('cf7ic-expiration-date' == $column){
                $expiration_timestamp = get_post_meta($post->ID, 'cf7ic_expiration_date', true);
                if(isset($expiration_timestamp) && !empty($expiration_timestamp)) $expiration_date = date('m/d/Y H:i', $expiration_timestamp);
                if(isset($expiration_date) && !empty($expiration_date)){
                    printf('<span>%1$s</span>',$expiration_date);
                }elseif(empty($expiration_date)){
                    printf('<span class="cf7ic-red">%1$s</span>',__('Not Set','invitation-code-for-contact-form-7'));
                }
            }
            if('cf7ic-remaining-amt' == $column){
                $remaining_amt = get_post_meta($post->ID, 'cf7ic_number_times_used', true);
                if(isset($remaining_amt) && ($remaining_amt>=0)){
                    printf('<span>%1$s</span>',$remaining_amt);
                }else{
                    printf('<span class="cf7ic-green">%1$s</span>',__('Unlimited Usage','invitation-code-for-contact-form-7'));
                }
            }
        }

        // matches inserted code with all codes post meta
        static function cf7ci_post_count($form_id, $invitation_code){
            $cur_time = time();
            
            $cf7ic_args = array(
                'post_type'     => 'cf7ic_invite_codes',
                'post_status'   => 'publish',
                'posts_per_page'   => -1,
                'meta_query'    => array(
                    'relation'  => 'AND',
                    array(
                        'key'     => 'cf7ic_plugin_status',
                        'value'   => 'on',
                        'compare' => '='
                    ),
                    array(
                        'key'     => 'cf7ic_invitation_code',
                        'value'   => $invitation_code,
                        'compare' => '='
                    ),
                    array(
                        'relation' => 'OR',
                        array(
                            'key'     => 'cf7ic_expiration_date',
                            'value'   =>  $cur_time,
                            'compare' => '>='
                        ),
                        array(
                            'key'     => 'cf7ic_expiration_date',
                            'value'   => '',
                            'compare' => '='
                        )
                    ),
                    array(
                        'key'     => 'cf7ic_contact_forms',
                        'value'   => $form_id,
                        'compare' => 'LIKE'
                    ),
                    array(
                        'relation' => 'OR',
                        array(
                            'key'     => 'cf7ic_number_times_used',
                            'value'   => 0,
                            'compare' => '>'
                        ),
                        array(
                            'key'     => 'cf7ic_number_times_used',
                            'value'   => '',
                            'compare' => '='
                        )
                    )
                )
            );
            $cf7ic_posts = get_posts($cf7ic_args);

            return $cf7ic_posts;
        }

        // checks code status and selected any form or not 
        static function cf7ci_validation_check($form_id, $invitation_code){
            $cur_time = time();
            
            $cf7ic_validation_args = array(
                'post_type'     => 'cf7ic_invite_codes',
                'post_status'   => 'publish',
                'posts_per_page'   => -1,
                'meta_query'    => array(
                    'relation'  => 'AND',
                    array(
                        'relation' => 'OR',
                        array(
                            'key'     => 'cf7ic_plugin_status',
                            'value'   => '',
                            'compare' => '='
                        ),
                        array(
                            'key'     => 'cf7ic_contact_forms',
                            'value'   => $form_id,
                            'compare' => 'NOT LIKE'
                        )
                    ),
                    array(
                        'relation' => 'OR',
                        array(
                            'key'     => 'cf7ic_invitation_code',
                            'value'   => '',
                            'compare' => '!='
                        ),
                        array(
                            'key'     => 'cf7ic_invitation_code',
                            'value'   => $invitation_code,
                            'compare' => '='
                        )
                    ),
                    array(
                        'relation' => 'OR',
                        array(
                            'key'     => 'cf7ic_expiration_date',
                            'value'   =>  $cur_time,
                            'compare' => '>='
                        ),
                        array(
                            'key'     => 'cf7ic_expiration_date',
                            'value'   => '',
                            'compare' => '='
                        )
                    ),
                    array(
                        'relation' => 'OR',
                        array(
                            'key'     => 'cf7ic_number_times_used',
                            'value'   => 0,
                            'compare' => '>'
                        ),
                        array(
                            'key'     => 'cf7ic_number_times_used',
                            'value'   => '',
                            'compare' => '='
                        )
                    )
                )
            );
            $cf7ic_validation_posts = get_posts($cf7ic_validation_args);

            return $cf7ic_validation_posts;
        }

        static function cf7ic_after_mail_sent($contact_form){
            $form_id = $contact_form->id();
            $submission = WPCF7_Submission::get_instance();

            if ( $submission ) {
                $posted_data = $submission->get_posted_data();
                $invitation_code = sanitize_text_field($_POST['invitation-code']);

                if(isset($invitation_code) && !empty($invitation_code) && isset($form_id) && !empty($form_id)){
                    $cf7ci_posts = cf7ic_invitation_code_functions::cf7ci_post_count($form_id, $invitation_code);

                    if(isset($cf7ci_posts) && !empty($cf7ci_posts)){
                        $post_id = $cf7ci_posts[0]->ID;
                        $remaining_amt = get_post_meta($post_id, 'cf7ic_number_times_used', true);
                        
                        if($remaining_amt != ''){
                            update_post_meta($post_id, 'cf7ic_number_times_used', --$remaining_amt);
                        }
                    }
                }
            }       
        }

        static function cf7ic_validate_field($result, $tag){
            $form_id = intval($_POST['_wpcf7']);
            $invitation_code = isset( $_POST['invitation-code'] ) ? trim(sanitize_text_field($_POST['invitation-code'])) : '';

            if(isset( $_POST['invitation-code'] ) && 'invitation-code' == $tag->name ){
                $cf7ci_posts = cf7ic_invitation_code_functions::cf7ci_post_count($form_id, $invitation_code);
                $cf7ic_validation_posts = cf7ic_invitation_code_functions::cf7ci_validation_check($form_id, $invitation_code);
                
                $count_posts = wp_count_posts('cf7ic_invite_codes')->publish;   // count total posts
                if(!empty($count_posts) && isset($cf7ci_posts) && empty($cf7ci_posts)){
                    
                    
                    if(isset($cf7ic_validation_posts) && empty($cf7ic_validation_posts)){
                        $result->invalidate( $tag, "Enter valid invitation code." );
                    }
                }
            }
            return $result;
        }

        static function cf7ci_enqueue_scripts(){
            wp_enqueue_style( 'cf7ic-datetimepicker-css', CF7IC_PLUGIN_URL . '/assets/css/jquery.datetimepicker.min.css', array(), CF7IC_BUILD );
            wp_enqueue_style( 'cf7ic-admin-style', CF7IC_PLUGIN_URL . '/assets/css/admin-style.css', array(), time() );
            wp_enqueue_script('cf7ic-datetimepicker-js', CF7IC_PLUGIN_URL . '/assets/js/jquery.datetimepicker.min.js', array(), CF7IC_BUILD );
            wp_enqueue_script('cf7ic-admin-js', CF7IC_PLUGIN_URL . '/assets/js/admin-script.js', array(), time() );
            wp_localize_script('cf7ic-admin-js', 'cf7ic_custom_call', ['cf7ic_ajaxurl' => admin_url('admin-ajax.php')]);
        }


        /**
         * Callback function for handling CF7IC invitation code validation via AJAX.
         */
        public function cf7ic_invitation_post_validation_callback() {
            // Initialize variables
            $cf7ic_common = $cf7ic_invitation_Code_data = $cf7ic_response = array();
            $cf7ic_response_message = 'Success';
            $cf7ic_response_error_status = false;

            // Retrieve form data from AJAX POST request
            $cf7ic_posts_data = $_POST['cf7ic_posts_data'];

            // Extract relevant information from form data
            foreach ($cf7ic_posts_data as $cf7ic_post_data) {
                if ($cf7ic_post_data['name'] ===  'post_ID') {
                    $cf7ic_invitation_Code_data['cf7ic_current_post_id'] = $cf7ic_post_data['value'];
                }
                if ($cf7ic_post_data['name'] === 'cf7ic_invitation_code') {
                    $cf7ic_invitation_Code_data['cf7ic_invitation_code'] = trim($cf7ic_post_data['value']);
                }
                if ($cf7ic_post_data['name'] === 'cf7ic_contact_forms[]') {
                    $cf7ic_invitation_Code_data['cf7ic_contact_forms'][] = $cf7ic_post_data['value'];
                }
            }

            // Check if required fields are not empty
            if (isset($cf7ic_invitation_Code_data['cf7ic_invitation_code']) && !empty($cf7ic_invitation_Code_data['cf7ic_invitation_code'])
                && isset($cf7ic_invitation_Code_data['cf7ic_contact_forms']) && !empty($cf7ic_invitation_Code_data['cf7ic_contact_forms'])) {

                // Query to retrieve invitation posts with the provided code
                $cf7ic_argc = array(
                    'post_type'         => 'cf7ic_invite_codes',
                    'post_status'       => 'publish',
                    'posts_per_page'    => -1,
                    'meta_query' => array(
                        array(
                            'key'       => 'cf7ic_invitation_code',
                            'value'     => $cf7ic_invitation_Code_data['cf7ic_invitation_code'],
                            'compare'   => '=',
                        )
                    )
                );

                // Execute the query
                $cf7ic_invitation_posts = new WP_Query($cf7ic_argc);

                // Check if there are invitation posts
                if ($cf7ic_invitation_posts->have_posts()) {
                    while ($cf7ic_invitation_posts->have_posts()) {
                        $cf7ic_invitation_posts->the_post();

                        // Skip the current post being edited
                        if ((int)$cf7ic_invitation_Code_data['cf7ic_current_post_id'] == get_the_ID()) continue;

                        // Get contact form IDs associated with the invitation post
                        $cf7ic_contact_from_ids = get_post_meta(get_the_ID(), 'cf7ic_contact_forms', true);

                        // Check for common contact forms
                        if (!empty($cf7ic_contact_from_ids) && !empty($cf7ic_invitation_Code_data['cf7ic_contact_forms'])) {
                            
                            $cf7ic_common = array_intersect($cf7ic_invitation_Code_data['cf7ic_contact_forms'], $cf7ic_contact_from_ids);

                            // If common contact forms are found, set error status and message
                            if (!empty($cf7ic_common)) {
                                $cf7ic_response_error_status = true;

                                $cf7ic_common_contact_forms_titles = array_map(function ($cf7ic_id){ return get_the_title($cf7ic_id). ' (#'. $cf7ic_id .')'; }, $cf7ic_common);
                                $cf7ic_common_contact_forms_title = implode(", ", $cf7ic_common_contact_forms_titles);

                                $cf7ic_response_message         = 'Invitation Code <strong>'. $cf7ic_invitation_Code_data['cf7ic_invitation_code'] . '</strong> is already set in the contact form <strong>' . $cf7ic_common_contact_forms_title .'</strong>';

                                break;
                            }
                        }
                    }
                }

                // Reset post data
                wp_reset_postdata();
            } else {
                // Set error status and message for empty required fields
                $cf7ic_response_error_status    = true;
                $cf7ic_response_message         = 'One or more fields have an error. Please check and try again.';
            }

            // Prepare response array
            $cf7ic_response = array(
                'status'    => $cf7ic_response_error_status,
                'message'   => $cf7ic_response_message,
            );

            // Send JSON-encoded response and terminate execution
            echo json_encode($cf7ic_response);
            wp_die();
        }

    }
    new cf7ic_invitation_code_functions();
}
