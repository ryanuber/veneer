<?php
set_include_path(
    dirname(__FILE__).
    DIRECTORY_SEPARATOR.
    'veneer'.
    PATH_SEPARATOR.
    get_include_path()
);
require 'veneer/veneer.php';
?>
