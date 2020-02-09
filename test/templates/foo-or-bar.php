<?php

echo '<pre>';
var_dump( get_field('some_plugin_template') );
echo '</pre>';

get_template_part( get_field('some_plugin_template') );
