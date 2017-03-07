<?php
/*
   Plugin Name: Typewriter Parsedown
   Plugin URI: https://github.com/pablinos/typewriter-parsedown
   Description: Swap out PHP Markdown for Parsedown in the Typewriter plugin
   Version: 1.0
   Author: Paul Bunkham
   Author URI: https://bunkham.com
   License: GPL2
*/

require_once 'GeshiParsedown.php';

function remove_do_markdown(){
    global $wp_filter;
    $filters=$wp_filter['the_content']->callbacks[10];
    foreach ($filters as $filterid => $value){
        if(strpos($filterid, 'do_markdown') !== false && is_array($value['function']) && is_a($value['function'][0],'Dev7Typewriter')){
            remove_filter('the_content',array($value['function'][0],'do_markdown'));
            remove_filter('the_excerpt',array($value['function'][0],'do_markdown'));
        }
    }
}
add_action('plugins_loaded','remove_do_markdown');

function wp_syntax_change_priorities(){
    if (class_exists( 'WP_Syntax' ) ) {
        remove_filter( 'the_content', array( 'WP_Syntax', 'beforeFilter' ), 0 );
        remove_filter( 'the_excerpt', array( 'WP_Syntax', 'beforeFilter' ), 0 );
        remove_filter( 'comment_text', array( 'WP_Syntax', 'beforeFilter' ), 0 );
        add_filter( 'the_content', array( 'WP_Syntax', 'beforeFilter' ), 1 );
        add_filter( 'the_excerpt', array( 'WP_Syntax', 'beforeFilter' ), 1 );
        add_filter( 'comment_text', array( 'WP_Syntax', 'beforeFilter' ), 1 );
    }
}
add_action('plugins_loaded','wp_syntax_change_priorities');

function do_typewriter_parsedown($content){
    $options = get_option('typewriter_parsedown_options', array('geshi_formatting'=>0));
    if($options['geshi_formatting']){
        $parse = new GeshiParsedown();
    }else{
        $parse = new Parsedown();
    }
    return $parse->text($content);
}
add_filter('the_content','do_typewriter_parsedown',0);
add_filter('the_excerpt','do_typewriter_parsedown',0);

//Add setting to toggle GeSHi formatting
add_action('admin_init', 'typewriter_parsedown_init');
function typewriter_parsedown_init(){
    register_setting(
        'writing',                 // settings page
        'typewriter_parsedown_options',          // option name
        'typewriter_parsedown_validate_options'  // validation callback
    );
    
    add_settings_field(
        'typewriter_parsedown_use_geshi',      // id
        'Output GeSHi format for Markdown code blocks',              // setting title
        'typewriter_parsedown_setting_input',    // display callback
        'writing',                 // settings page
        'default'                  // settings section
    );

}

// Display and fill the form field
function typewriter_parsedown_setting_input() {
    // get option 'boss_email' value from the database
    $options = get_option( 'typewriter_parsedown_options' );
    $value = $options['geshi_formatting'];
    
    // echo the field
?>
    <input type="checkbox" value="1" name="typewriter_parsedown_options[geshi_formatting]" <?php checked('1', $options['geshi_formatting']); ?>" />
<?php
}

// Validate user input
function typewriter_parsedown_validate_options( $input ) {
    $input['geshi_formatting'] = ( $input['geshi_formatting']==1 ? 1 : 0 );
    
    return $input;
}

