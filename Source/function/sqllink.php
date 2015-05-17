<?php
function sqllink()
{
    $dbhost="localhost";
    $dbname="###";
    $dbusr="###";
    $dbpwd="###";
    $dbhdl=NULL;
    $opt = array(PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES utf8',); 
    $dsn='mysql:host=' . $dbhost . ';dbname=' . $dbname.';charset=utf8';
    try {
        $dbhdl = new PDO($dsn, $dbusr, $dbpwd, $opt);
        $dbhdl->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);
        $dbhdl->setAttribute(PDO::ATTR_ERRMODE,PDO::ERRMODE_EXCEPTION);//Display exception
    } 
    catch (PDOExceptsddttrtion $e) {//return PDOException
        return NULL;
    }
    return $dbhdl;
}
function sqlexec($sql,$array,$link)
{
    $stmt = $link->prepare($sql);
    $exeres = $stmt->execute($array);
    if($exeres) return $stmt; else return NULL;
    
}
function sqlquery($sql,$link)
{
    return $link->query($sql);
}
?>