<?php
function getProperties( $object )
{
    if ( !is_null($object) ) {
        $properties = array_keys(get_object_vars( $object ));
        sort( $properties );
    } else {
        $properties = array();
    }

    return $properties;
}

?>
