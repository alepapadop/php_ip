<?php

/*
 * @author Alexis Papadopoulos
 * MIT Licence
 */

#************** FOR EXTERNAL USE ****************************

//gets an ipv6 in hex format and returns true if the format is acceptable
function validate_ipv6_address($ipv6)
{
    $flag=false;
    
    //uncompressed form
    if (strpos($ipv6, '::') === false )
    {
        
        $pattern='/^([a-f0-9]{1,4}\:){7}([a-f0-9]{1,4})$/i';  
        if(preg_match($pattern, $ipv6))
                $flag=true;
        
    }elseif(substr_count($ipv6, '::')==1){
        
        $pattern='/^([a-f0-9]{1,4}::?){1,}([a-f0-9]{1,4})$/i';  
        if(preg_match($pattern, $ipv6))
                $flag=true;
        
    }

    return $flag; 
    
}

#************** FOR INTERNAL USE ****************************

?>
