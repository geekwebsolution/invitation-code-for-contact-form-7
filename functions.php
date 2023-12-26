<?php
if(!class_exists('cf7ic_invitation_code_functions')){
    class cf7ic_invitation_code_functions{

        /**
         * define defult vairables values 
         */
        
         //selected form values
        public $cf7ic_selected_form_id = [];

        // invitationcode for ajax validation
        public $cf7ic_code             = '';

        public function __construct() {
            add_action( 'admin_enqueue_scripts', array('cf7ic_invitation_code_functions','cf7ci_enqueue_scripts'),30 );
            add_filter( 'manage_cf7ic_invite_codes_posts_columns', array('cf7ic_invitation_code_functions','insert_posts_columns'));
            add_action( 'manage_cf7ic_invite_codes_posts_custom_column' , array('cf7ic_invitation_code_functions','insert_post_type_custom_columns'));
            add_filter( 'wpcf7_validate_text', array('cf7ic_invitation_code_functions','cf7ic_validate_field'), 50, 3);
            add_filter( 'wpcf7_validate_text*', array('cf7ic_invitation_code_functions','cf7ic_validate_field'), 50, 3);
            add_action( 'wpcf7_mail_sent', array('cf7ic_invitation_code_functions','cf7ic_after_mail_sent'));
            
            add_action( 'wp_ajax_cf7ic_invitation_code_validation',array($this,'cf7ic_invitation_code_validation_callback'));
            add_action( 'wp_ajax_nopriv_cf7ic_invitation_code_validation', array($this,'cf7ic_invitation_code_validation_callback'));
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
            wp_enqueue_style( 'cf7ic-admin-style', CF7IC_PLUGIN_URL . '/assets/css/admin-style.css', array(), CF7IC_BUILD );
            wp_enqueue_script('cf7ic-datetimepicker-js', CF7IC_PLUGIN_URL . '/assets/js/jquery.datetimepicker.min.js', array(), CF7IC_BUILD );
            wp_enqueue_script('cf7ic-admin-js', CF7IC_PLUGIN_URL . '/assets/js/admin-script.js', array(), CF7IC_BUILD );
            wp_localize_script('cf7ic-admin-js', 'cf7ic_custom_call', ['cf7ic_ajaxurl' => admin_url('admin-ajax.php')]);
        }




        public function cf7ic_invitation_code_validation_callback(){
            
            echo '<pre>'; print_r(  get_the_ID()); echo '</pre>';
            $this->cf7ic_selected_form_id = $_POST['cf7ic_selected_form_id'];
            $this->cf7ic_code             = $_POST['cf7ic_code'];
            
            echo '<pre>'; print_r( $this->cf7ic_selected_form_id  ); echo '</pre>';
            echo '<pre>'; print_r( $this->cf7ic_code ); echo '</pre>';

            wp_die();
        }




    }
    new cf7ic_invitation_code_functions();
}
