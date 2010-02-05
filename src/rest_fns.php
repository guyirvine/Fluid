<?php

if ( isset( $_SERVER["PATH_INFO"] ) ) {
    if ( $_SERVER["PATH_INFO"] == "/" ) {
        $path_list = array();
    } else {
        $path_info = $_SERVER["PATH_INFO"];
        $path_list = explode( '/', $path_info );
        if ( empty( $path_list[0] ) ) {
            array_shift( $path_list );
        }
        if ( empty( $path_list[count( $path_list )-1] ) ) {
            array_pop( $path_list );
        }
    }
}
