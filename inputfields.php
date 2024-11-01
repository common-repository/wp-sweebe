<?php

class InputFields {

    public static function text($args) {
        echo '<input type="text" id="' . $args['id'] . '" name="wp_sweebe_options[' . $args['option'] . ']' . '" value="' . $args['value'] . '" />';
    }

}