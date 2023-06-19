<?php
// if uninstall.php is not called by WordPress, die
if (!defined('WP_UNINSTALL_PLUGIN')) {
    die;
}

//delete all posts
$allcodes= get_posts( array('post_type'=>'cf7ic_invite_codes','numberposts'=>-1) );
foreach ($allcodes as $eachcode) {
  wp_delete_post( $eachcode->ID, true );
}