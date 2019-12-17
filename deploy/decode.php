<?php

function decode_data($data)
{

	return(unserialize( base64_decode( $data)));
}

function  encode_data($data)
{
	return(base64_encode( serialize( $data ) ));
}
