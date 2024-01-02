<?php
if(!class_exists('cf7ic_invitation_codes_settings')){
    class cf7ic_invitation_codes_settings
    {
        public function __construct() {
            add_action( 'init', array( $this,'cf7ic_custom_post_type' ));
            add_action( 'add_meta_boxes', array( $this,'cf7ic_add_meta_box' ));
            add_action( 'save_post_cf7ic_invite_codes', array( $this, 'cf7ic_save_meta' ));
            add_action( 'wpcf7_init', array( $this,'cf7ic_add_form_tag' ), 36, 0 );
        }

        static function cf7ic_add_meta_box() {
            add_meta_box(
                'cf7ic_meta_box',
                __('Invitation Code','invitation-code-for-contact-form-7'),
                array('cf7ic_invitation_codes_settings','cf7ic_metabox_html'),
                'cf7ic_invite_codes',
                'normal',
                'high'
            );
        }

        static function cf7ic_metabox_html($post){

            $cf7ic_plugin_status = get_post_meta($post->ID, 'cf7ic_plugin_status', true);
            $invitation_code = get_post_meta($post->ID, 'cf7ic_invitation_code', true);
            $expiration_timestamp = get_post_meta($post->ID, 'cf7ic_expiration_date', true);
            $number_times_used = get_post_meta($post->ID, 'cf7ic_number_times_used', true);
            $contact_forms = get_post_meta($post->ID, 'cf7ic_contact_forms', true);
            if(isset($expiration_timestamp) && !empty($expiration_timestamp)) $expiration_date = date('m/d/Y H:i', $expiration_timestamp);
            $cf7ic_contact_forms = (isset($contact_forms) && !empty($contact_forms)) ? $contact_forms : array();
            ?>
            <div id="message" class="notice notice-error" style="display: none;">
                <p id="cf7ic-error-notice"></p>
            </div>

            <table>
                <tr>
                    <th scope="row">
                        <?php wp_nonce_field('cf7ic_metadata_nonce_action', 'cf7ic_metadata_nonce'); ?>
                        <label><?php esc_html_e('Status', 'invitation-code-for-contact-form-7'); ?></label>

                    </th>
                    <td>
                        <label class="cf7ic-switch">
                            <input type="checkbox" class="cf7ic-checkbox" name="cf7ic_plugin_status" value="on" <?php if(isset($cf7ic_plugin_status) && $cf7ic_plugin_status=='on') esc_attr_e('checked', 'invitation-code-for-contact-form-7'); ?>>
                            <span class="cf7ic-slider cf7ic-round"></span>
                        </label>
                        <p class="note"><i><?php esc_html_e('Enable invitation code.','invitation-code-for-contact-form-7'); ?></i></p>
                    </td>
                </tr>
                <tr>
                    <th><?php esc_html_e('Select Contact Form 7*','invitation-code-for-contact-form-7'); ?></th>
                    <td>
                        <div class="cf7ic-field-box">
                            <?php
                            $args = array('post_type' => 'wpcf7_contact_form', 'posts_per_page' => -1);
                            $cf7Forms = get_posts( $args );
                            foreach($cf7Forms as $key => $value){ ?>
                                <input type="checkbox" id="<?php esc_attr_e($value->post_name); ?>" class="cf7ic_contact_forms" name="cf7ic_contact_forms[]" value="<?php esc_attr_e($value->ID);?>" <?php if(in_array($value->ID,$cf7ic_contact_forms)) esc_attr_e('checked','invitation-code-for-contact-form-7'); ?>>
                                
                                <label for="<?php esc_attr_e($value->post_name); ?>"><?php esc_html_e($value->post_title . ' (#' . $value->ID . ')'); ?></label><br>
                                <?php
                            }
                            ?>
                            <p class="cf7ic-contact-forms-notice cf7ic-notice" style="display: none;"></p>
                            <p class="note"><i><?php esc_html_e('Select contact form 7 for this code.','invitation-code-for-contact-form-7'); ?></i></p>
                        </div>
                    </td>
                </tr>
                <tr>
                    <th><?php esc_html_e('Invitation Code*','invitation-code-for-contact-form-7'); ?></th>
                    <td>
                        <div class="cf7ic-field-box">
                            <div class="cf7ic-form-element">
                                <input type="text" name="cf7ic_invitation_code" value="<?php if(isset($invitation_code) && !empty($invitation_code)) esc_html_e($invitation_code); ?>" autocomplete="off" >
                                <span class="cf7ic-copy-to-clipboard"> <span class="cf7ic-copy-text">Copy Text</span> <svg xmlns="http://www.w3.org/2000/svg" version="1.1" xmlns:xlink="http://www.w3.org/1999/xlink" xmlns:svgjs="http://svgjs.com/svgjs" width="20" height="20" x="0" y="0" viewBox="0 0 512 512" style="enable-background:new 0 0 512 512" xml:space="preserve" class=""><g><path xmlns="http://www.w3.org/2000/svg" d="m186.667969 416c-49.984375 0-90.667969-40.683594-90.667969-90.667969v-218.664062h-37.332031c-32.363281 0-58.667969 26.300781-58.667969 58.664062v288c0 32.363281 26.304688 58.667969 58.667969 58.667969h266.664062c32.363281 0 58.667969-26.304688 58.667969-58.667969v-37.332031zm0 0" fill="#1976d2" data-original="#1976d2" class=""></path><path xmlns="http://www.w3.org/2000/svg" d="m469.332031 58.667969c0-32.40625-26.261719-58.667969-58.664062-58.667969h-224c-32.40625 0-58.667969 26.261719-58.667969 58.667969v266.664062c0 32.40625 26.261719 58.667969 58.667969 58.667969h224c32.402343 0 58.664062-26.261719 58.664062-58.667969zm0 0" fill="#2196f3" data-original="#2196f3" class=""></path></g></svg></span>
                            </div>
                            <p class="cf7ic-invitation-code-notice cf7ic-notice" style="display: none;"></p>
                            <p class="note"><i><?php esc_html_e('Enter invitation code which you want to apply.','invitation-code-for-contact-form-7'); ?></i></p>
                            
                        </div>
                    </td>
                </tr>
                <tr>
                    <th><?php esc_html_e('Expiration Date','invitation-code-for-contact-form-7'); ?></th>
                    <td>
                        <div class="cf7ic-field-box">
                            <div class="cf7ic-form-element cf7ic-calender">
                                <input type="text" class="cf7ic-datepicker" name="cf7ic_expiration_date" placeholder="mm/dd/yyyy HH:mm" value="<?php if(isset($expiration_date) && !empty($expiration_date)) esc_html_e($expiration_date); ?>" autocomplete="off">
                                <img class="fas fa-calendar-alt" src="<?php echo CF7IC_PLUGIN_URL  . '/assets/images/calendar.png'; ?>" alt="calender icon"></img>
                            </div>
                            <p class="note"><b><?php esc_html_e('Note:','invitation-code-for-contact-form-7'); ?></b> <i><?php esc_html_e('Expiration date be on GMT. ','invitation-code-for-contact-form-7'); ?><strong><?php esc_html_e('Current GMT time: '); ?><?php esc_html_e(gmdate('m/d/Y @ G:i')); ?></strong></i></p>
                        </div>
                    </td>
                </tr>
                <tr>
                    <th><?php esc_html_e('Usage limit per user','invitation-code-for-contact-form-7'); ?></th>
                    <td>
                        <div class="cf7ic-field-box">
                            <input type="number" name="cf7ic_number_times_used" min="0" value="<?php if(isset($number_times_used)) esc_html_e($number_times_used); ?>" placeholder="Unlimited usage" autocomplete="off">
                            <p class="note"><i><?php esc_html_e('Enter how many times this code can be used by an individual user.','invitation-code-for-contact-form-7'); ?></i></p>
                        </div>
                    </td>
                </tr>
                
            </table>
            <?php
        }

        static function cf7ic_save_meta($post_id){
            // Check if this is an autosave
            if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) return;
            
            // Check if the user has permission to edit the post
            if (!current_user_can('edit_post', $post_id)) return;
            
            // Verify the nonce if using one
            if (isset($_POST['cf7ic_metadata_nonce']) && !wp_verify_nonce($_POST['cf7ic_metadata_nonce'], 'cf7ic_metadata_nonce_action')) return;
            
            // Check if it's the correct post type
            if ('cf7ic_invite_codes' !== get_post_type($post_id)) return;
            
            if((isset($_POST['cf7ic_invitation_code']) && !empty($_POST['cf7ic_invitation_code']))){
                $cf7ic_invitation_code = sanitize_text_field($_POST['cf7ic_invitation_code']);
                update_post_meta($post_id, 'cf7ic_invitation_code', $cf7ic_invitation_code);
            }
            
            if((isset($_POST['cf7ic_contact_forms']) && !empty($_POST['cf7ic_contact_forms']))){
                $cf7ic_contact_forms = $_POST['cf7ic_contact_forms'];
                update_post_meta($post_id, 'cf7ic_contact_forms', $cf7ic_contact_forms);
            }
            
            $cf7ic_plugin_status = (isset($_POST['cf7ic_plugin_status'])) ? sanitize_text_field($_POST['cf7ic_plugin_status']) : '';
            $expiration_date = (isset($_POST['cf7ic_expiration_date'])) ? sanitize_text_field($_POST['cf7ic_expiration_date']) : '';
            $number_times_used = (isset($_POST['cf7ic_number_times_used']) && $_POST['cf7ic_number_times_used'] >= 0) ? intval($_POST['cf7ic_number_times_used']) : '';
            
            update_post_meta($post_id, 'cf7ic_plugin_status', $cf7ic_plugin_status);
            update_post_meta($post_id, 'cf7ic_expiration_date', strtotime($expiration_date));
            update_post_meta($post_id, 'cf7ic_number_times_used', $number_times_used);
           
        }

        static function cf7ic_add_form_tag() {
            if(class_exists('WPCF7_TagGenerator')){
                $tag_generator = WPCF7_TagGenerator::get_instance();
                $tag_generator->add( 'invitation-code', __( 'invitation code', 'invitation-code-for-contact-form-7' ), array('cf7ic_invitation_codes_settings','cf7ic_form_tag_html') );
            }
        }

        static function cf7ic_form_tag_html( $contact_form, $args = '' ){
            $args = wp_parse_args( $args, array() );
            $type = 'text';
            ?>
                <div class="control-box">
                    <fieldset>
                        <table class="form-table">
                            <tbody>
                                <tr>
                                    <th scope="row"><?php echo esc_html( __( 'Field type', 'invitation-code-for-contact-form-7' ) ); ?></th>
                                    <td>
                                        <fieldset>
                                            <legend class="screen-reader-text">
                                                <?php echo esc_html( __( 'Field type', 'invitation-code-for-contact-form-7' ) ); ?></legend>
                                            <label><input type="checkbox" name="required" checked disabled readonly />
                                                <?php echo esc_html( __( 'Required field', 'invitation-code-for-contact-form-7' ) ); ?></label>
                                        </fieldset>
                                    </td>
                                </tr>

                                <tr>
                                    <th scope="row"><label
                                            for="<?php echo esc_attr( $args['content'] . '-name' ); ?>"><?php echo esc_html( __( 'Name', 'invitation-code-for-contact-form-7' ) ); ?></label>
                                    </th>
                                    <td><input type="text" name="name" class="tg-name oneline"
                                            id="<?php echo esc_attr( $args['content'] . '-name' ); ?>" value="invitation-code"
                                            disabled /></td>
                                </tr>

                                <tr>
                                    <th scope="row"><label
                                            for="<?php echo esc_attr( $args['content'] . '-values' ); ?>"><?php echo esc_html( __( 'Default value', 'invitation-code-for-contact-form-7' ) ); ?></label>
                                    </th>
                                    <td><input type="text" name="values" class="oneline"
                                            id="<?php echo esc_attr( $args['content'] . '-values' ); ?>" /><br />
                                        <label><input type="checkbox" name="placeholder" class="option" />
                                            <?php echo esc_html( __( 'Use this text as the placeholder of the field', 'invitation-code-for-contact-form-7' ) ); ?></label>
                                    </td>
                                </tr>

                                <?php if ( in_array( $type, array( 'text' ) ) ) : ?>
                                <tr>
                                    <th scope="row"><?php echo esc_html( __( 'Akismet', 'invitation-code-for-contact-form-7' ) ); ?></th>
                                    <td>
                                        <fieldset>
                                            <legend class="screen-reader-text">
                                                <?php echo esc_html( __( 'Akismet', 'invitation-code-for-contact-form-7' ) ); ?></legend>

                                            <?php if ( 'text' == $type ) : ?>
                                            <label>
                                                <input type="checkbox" name="akismet:author" class="option" />
                                                <?php echo esc_html( __( "This field requires author's name", 'invitation-code-for-contact-form-7' ) ); ?>
                                            </label>                                            
                                            <?php endif; ?>

                                        </fieldset>
                                    </td>
                                </tr>
                                <?php endif; ?>

                                <tr>
                                    <th scope="row"><label
                                            for="<?php echo esc_attr( $args['content'] . '-id' ); ?>"><?php echo esc_html( __( 'Id attribute', 'invitation-code-for-contact-form-7' ) ); ?></label>
                                    </th>
                                    <td><input type="text" name="id" class="idvalue oneline option"
                                            id="<?php echo esc_attr( $args['content'] . '-id' ); ?>" /></td>
                                </tr>

                                <tr>
                                    <th scope="row"><label
                                            for="<?php echo esc_attr( $args['content'] . '-class' ); ?>"><?php echo esc_html( __( 'Class attribute', 'invitation-code-for-contact-form-7' ) ); ?></label>
                                    </th>
                                    <td><input type="text" name="class" class="classvalue oneline option"
                                            id="<?php echo esc_attr( $args['content'] . '-class' ); ?>" /></td>
                                </tr>

                            </tbody>
                        </table>
                    </fieldset>
                </div>

                <div class="insert-box">
                    <input type="text" name="<?php echo $type; ?>" class="tag code" readonly="readonly" onfocus="this.select()" />
                    <div class="submitbox">
                        <input type="button" class="button button-primary insert-tag"
                            value="<?php echo esc_attr( __( 'Insert Tag', 'invitation-code-for-contact-form-7' ) ); ?>" />
                    </div>
                    <br class="clear" />
                    <p class="description mail-tag">
                        <label for="<?php echo esc_attr( $args['content'] . '-mailtag' ); ?>">
                            <?php echo sprintf( esc_html( __( "To use the value input through this field in a mail field, you need to insert the corresponding mail-tag (%s) into the field on the Mail tab.", 'invitation-code-for-contact-form-7' ) ), '<strong><span class="mail-tag"></span></strong>' ); ?>
                            <input type="text" class="mail-tag code hidden" readonly="readonly" id="<?php echo esc_attr( $args['content'] . '-mailtag' ); ?>" />
                        </label>
                    </p>
                </div>
            <?php
        }

        static function cf7ic_custom_post_type() {
            register_post_type( 'cf7ic_invite_codes', array(
                'labels' => array(
                    'name' => __('Invitation Codes','invitation-code-for-contact-form-7'),
                    'singular_name' => __('Invitation Codes','invitation-code-for-contact-form-7'),
                    'add_new' => __('Add  Invitation Code','invitation-code-for-contact-form-7'),
                    'edit_item' => __('Edit Invitation Code','invitation-code-for-contact-form-7'),
                    'new_item' => __('New Code','invitation-code-for-contact-form-7'),
                ),
                'public' => false,
                'publicly_queryable' => false,
                'show_ui' => true,
                'show_in_menu' => 'wpcf7',
                'has_archive' => false,
                'rewrite' => false,
                'exclude_from_search' => true,
                'supports' => array('title')
            ) );
        }
    }
    new cf7ic_invitation_codes_settings();
}