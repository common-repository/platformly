<?php

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

if(isset($access)){
    $user = ply_check_access(1);
}else{
    $user = ply_check_access();
}
