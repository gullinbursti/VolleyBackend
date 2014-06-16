<?php
function getProperties( $object )
{
    $properties = array_keys(get_object_vars( $object ));
    sort( $properties );
    return $properties;
}

?>
