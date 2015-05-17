<?php
function encrypt($data, $key)
{
	$key	=	md5($key);
    $x		=	0;
	$char='';
	$str='';
	
    $len	=	strlen($data);
    $l		=	strlen($key);
    for ($i = 0; $i < $len; $i++)
    {
        if ($x == $l) 
        {
        	$x = 0;
        }
        $char .= $key{$x};
        $x++;
    }
    for ($i = 0; $i < $len; $i++)
    {
        $str .= chr(ord($data{$i}) + (ord($char{$i})) % 256);
    }
    return base64_encode($str);
}
function decrypt($data, $key)
{
	$key = md5($key);
    $x = 0;
	$char='';
	$str='';
    $data = base64_decode($data);
    $len = strlen($data);
    $l = strlen($key);
    for ($i = 0; $i < $len; $i++)
    {
        if ($x == $l) 
        {
        	$x = 0;
        }
        $char .= substr($key, $x, 1);
        $x++;
    }
    for ($i = 0; $i < $len; $i++)
    {
        if (ord(substr($data, $i, 1)) < ord(substr($char, $i, 1)))
        {
            $str .= chr((ord(substr($data, $i, 1)) + 256) - ord(substr($char, $i, 1)));
        }
        else
        {
            $str .= chr(ord(substr($data, $i, 1)) - ord(substr($char, $i, 1)));
        }
    }
    return $str;
}
function safe($str)
{
	$len=strlen($str);
	$re='';
	for($i=0;$i<$len;$i++){
	$c=substr($str,$i,1);
	if(ord('0')<=ord($c) && ord('9')>=ord($c))$re=$re.$c;
	if(ord('a')<=ord($c) && ord('z')>=ord($c))$re=$re.$c;
	if(ord('A')<=ord($c) && ord('Z')>=ord($c))$re=$re.$c;
	}
	return $re;
}?>