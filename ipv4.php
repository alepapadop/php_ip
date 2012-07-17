<?php

/*
 * @author Alexis Papadopoulos
 * MIT Licence
 */



#************** FOR EXTERNAL USE ****************************

//gets an ipv4 subnet and mask address in dotted format and returns an array
//containing the network and broadcast address and also the network and
//host bits.
function get_range_from_subnet($ipv4_subnet, $ipv4_mask)
{
    
    $subnet_parts=  explode('.', $ipv4_subnet);
    $mask_parts=  explode('.', $ipv4_mask);
    
    $subnet_in_binary_no_dots=  convert_dec_to_8bit_binary($subnet_parts[0])
                                    .convert_dec_to_8bit_binary($subnet_parts[1])
                                    .convert_dec_to_8bit_binary($subnet_parts[2])
                                    .convert_dec_to_8bit_binary($subnet_parts[3]);
    
    $mask_in_binary_no_dots=  convert_dec_to_8bit_binary($mask_parts[0])
                                    .convert_dec_to_8bit_binary($mask_parts[1])
                                    .convert_dec_to_8bit_binary($mask_parts[2])
                                    .convert_dec_to_8bit_binary($mask_parts[3]);
    
    $network_host_bits=  get_number_of_network_host_bits($mask_in_binary_no_dots);
    
    $network_ipv4_address=get_ipv4_network_address_from_binary_ipv4_and_mask($subnet_in_binary_no_dots,$mask_in_binary_no_dots);
    
    $broadcast_ipv4_address=  get_ipv4_broadcast_address_from_ipv4_and_mask($subnet_in_binary_no_dots,$mask_in_binary_no_dots);
    
    return    array('network_bits'=>$network_host_bits['network_bits'],
                    'host_bits'=>$network_host_bits['host_bits'],
                    'network_address'=>$network_ipv4_address,
                    'broadcast_address'=>$broadcast_ipv4_address);
}

//gets an ipv4 subnet and mask address in dotted format and returns an array
//containing all the usable host addresses
function get_ipv4_host_addresses_from_ipv4_network_broadcast_address($ipv4_network_address,$ipv4_broadcast_address)
{
    $ipv4_host_addresses=array();
    $network_address_long=  ip2long($ipv4_network_address)+1;
    $broadcast_address_long=  ip2long($ipv4_broadcast_address)-1;
    
    $host_addresses_long=range($network_address_long,$broadcast_address_long);
    
    foreach($host_addresses_long as $hosts)
    {
        $ipv4_host_addresses[]=  long2ip($hosts);
    }
    
    return $ipv4_host_addresses;
}

//gets an ipv4 address in dotted format and returns true if the format
//is acceptable
function validate_ipv4_address($ipv4)
{
    $valid = false;
    $pattern = '/^([0-9]{1,3})\.([0-9]{1,3})\.([0-9]{1,3})\.([0-9]{1,3})$/';
    if (preg_match($pattern, $ipv4, $parts)) {
        if (
                $parts[1] > 0 &&
                $parts[1] <= 255 &&
                $parts[2] >= 0 &&
                $parts[2] <= 255 &&
                $parts[3] >= 0 &&
                $parts[3] <= 255 &&
                $parts[4] >= 0 &&
                $parts[4] <= 255
        ) {
            $valid=true;
        }
    }
    return $valid;
}


function validate_subnet_mask($ipv4_mask)
{
    $valid=false;
    
    $mask_parts=  explode('.', $ipv4_mask);
    
    $mask_in_binary_no_dots=  convert_dec_to_8bit_binary($mask_parts[0])
                                    .convert_dec_to_8bit_binary($mask_parts[1])
                                    .convert_dec_to_8bit_binary($mask_parts[2])
                                    .convert_dec_to_8bit_binary($mask_parts[3]);
        
    $pattern='/([1]{1,}[0]{0,})(.*)/';
    
    if(preg_match($pattern, $mask_in_binary_no_dots ,$parts))
    {
        if($parts[2]=='')
            $valid=true;
    }
    return $valid;
}

#************** FOR INTERNAL USE ****************************


//gets an decimal as input and returns an binary represantation in 8 bit format
function convert_dec_to_8bit_binary($dec)
{
    $binary=  decbin($dec);
    
    $octet=str_pad($binary, 8, "0", STR_PAD_LEFT);
    
    return $octet;
}

//gets the subnet mask in binary format without dots and returns an array with
//with the host and network bits
function get_number_of_network_host_bits($binary_mask)
{
    
    $pos=strpos($binary_mask, '0');
    if($pos!==false)
    {
        $network_bits=$pos;
        $host_bits=32-$pos;
    }else{
        $network_bits=32;
        $host_bits=0;
    }
    
    return array('network_bits'=>$network_bits,'host_bits'=>$host_bits);
    
}

//gets as input an ipv4 address and subnet mask in binary format without dots and
//returns the ipv4 network address in dotted format
function get_ipv4_network_address_from_binary_ipv4_and_mask($binary_ipv4,$binary_mask)
{
    $network_address_binary=$binary_ipv4 & $binary_mask;
    
    $pattern='/([0,1]{8})([0,1]{8})([0,1]{8})([0,1]{8})/';
    
    preg_match($pattern, $network_address_binary, $octets);
    
    $network_address=  bindec($octets[1]).'.'.bindec($octets[2]).'.'.bindec($octets[3]).'.'.bindec($octets[4]);
    
    return $network_address;
}


//gets as input an ipv4 address and subnet mask in binary format without dots and
//returns the ipv4 broadcast address in dotted format
function get_ipv4_broadcast_address_from_ipv4_and_mask($binary_ipv4,$binary_mask)
{
    $network_host_bits=  get_number_of_network_host_bits($binary_mask);
    
    $network_address_binary=$binary_ipv4 & $binary_mask;
    
    $network_part_binary=  substr($network_address_binary, 0, $network_host_bits['network_bits']);
    
    $broadcast_address_binary=str_pad($network_part_binary, 32, "1", STR_PAD_RIGHT);
    
    $pattern='/([0,1]{8})([0,1]{8})([0,1]{8})([0,1]{8})/';
    
    preg_match($pattern, $broadcast_address_binary, $octets);
    
    $broadcast_address=  bindec($octets[1]).'.'.bindec($octets[2]).'.'.bindec($octets[3]).'.'.bindec($octets[4]);
    
    return $broadcast_address;
}

?>
