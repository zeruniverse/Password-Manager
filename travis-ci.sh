#!/bin/bash
# check php syntax
if [ $# -lt 1 ];then
    echo 'Usage: ' $0  'directory';
    exit 1
fi
if [ ! -d $1 ];then
    echo $1  'not a directory,please check!';
    exit 1
fi
directory=$1
temp_file="myerrorfile"
ls -R $directory | awk  '
    BEGIN{
        FS="n"   
        folder="'$directory'"
        logname="'$temp_file'"
    }
    {
        if($0~/.php$/){
            system("php -l " folder "/" $0  "   >>  " logname  " 2>&1") 
        }
        if($0~/:$/){
            folder=substr($1,1,length($1)-1)
	print folder
        }
    }
'
if [ -e $temp_file ];then
    cat $temp_file | awk '
        BEGIN{
            error = 0
       }
        {
            if($0~/Parse/) {
                error++
                print $0
            }  
        }
        END{
            print "Total Error:" error
	    if(error>0) exit 1
            exit 0
        }
    '
else
    echo "php file not found."
    exit 1;
fi
