<?php
/*
Created by GreenGene

VERSION: 1.0.1 ALPHA
*/

$action = $_GET[ "action" ];

include 'aes.php';
include 'password_secure.php';

function verifyPassword( $username, $password )
{
    if( file_exists( "USER_DATA/" . $username ) )
    {
        $file = fopen( "USER_DATA/" . $username . "/account.json", "r" );
        $data = json_decode( fread( $file, filesize( "USER_DATA/" . $username . "/account.json" ) ) );
        fclose( $file );

        if( password_verify( $password, $data->{ "password" } ) )
        {
            return true;
        }
        else
        {
           return false;
        }
    }
}

if( $action == "makeAccount" )
{
    $password = $_GET[ "password" ];
    $username = $_GET[ "username" ];

    $accounts = scandir( "USER_DATA" );
    $accountConflicts = false;
    foreach( $accounts as $key => $value )
    {
        if( $value == $username )
        {
            $accountConflicts = true;
        }
    }

    if( !$accountConflicts )
    {
        mkdir( "USER_DATA/" . $username );

        $userfile = fopen( "USER_DATA/" . $username . "/account.json", "w" );
        $queue = array( "username" => $username, "maxStorageLimit" => 100, "password" => password_hash( $password, PASSWORD_DEFAULT ) );
        fwrite( $userfile, json_encode( $queue ) );
        fclose( $userfile );

        mkdir( "USER_DATA/" . $username . "/FILES" );

        echo( json_encode( array( "error" => false, "value" => "done" ) ) );
    }
    else
    {
       echo( json_encode( array( "error" => true, "value" => "account_conflicts" ) ) ); 
    }
}
else if( $action == "uploadFile" )
{
    $username = $_GET[ "username" ];
    $password = $_GET[ "password" ];
    $code = $_GET[ "code" ];
    $fileName = $_GET[ "filename" ];

    $accounts = scandir( "USER_DATA" );
    $exists = false;
    foreach( $accounts as $key => $value )
    {
        if( $value == $username )
        {
            $exists = true;
        }
    }

    if( $exists )
    {
        $conflict = false;
        $files = scandir( "USER_DATA/" . $username . "/FILES" );
        foreach( $files as $key => $value )
        {
            if( $value == $fileName )
            {
                $conflict = true;
            }
        }

        if( !$conflict )
        {
            if( verifyPassword( $username, $password ) )
            {
                $aes = new AES( $code, $password, 256 );

                $enc = $aes->encrypt();

                $file = fopen( "USER_DATA/" . $username . "/FILES" . "/" . $fileName, "w" );
                fwrite( $file, $enc );
                fclose( $file );

                echo( json_encode( array( "error" => false, "value" => "done" ) ) );
            }
            else
            {
                echo( json_encode( array( "error" => true, "value" => "incorrect" ) ) );
            }
        }
        else
        {
            echo( json_encode( array( "error" => true, "value" => "file_conflicts" ) ) ); 
        }
    }
}
else if( $action == "verifyPassword" )
{
    $username = $_GET[ "username" ];
    $password = $_GET[ "password" ];

    if( verifyPassword( $username, $password) )
    {
        echo( json_encode( array( "error" => false, "value" => "correct" ) ) );
    }
    else
    {
        echo( json_encode( array( "error" => true, "value" => "incorrect" ) ) );
    }
}
else if( $action == "deleteFile" )
{
    $username = $_GET[ "username" ];
    $password = $_GET[ "password" ];
    $fileName = $_GET[ "filename" ];

    if( file_exists( "USER_DATA/" . $username ) )
    {
        if( verifyPassword( $username, $password ) )
        {
            if( file_exists( "USER_DATA/" . $username . "/FILES" . "/" . $fileName ) )
            {
                unlink( "USER_DATA/" . $username . "/FILES" . "/" . $fileName );

                echo( json_encode( array( "error" => false, "value" => "done" ) ) );
            }
            else
            {
                echo( json_encode( array( "error" => true, "value" => "file_not_exists" ) ) ); 
            }
        }
        else
        {
            echo( json_encode( array( "error" => true, "value" => "incorrect" ) ) );
        }
    }
    else
    {
        echo( json_encode( array( "error" => true, "value" => "account_not_exists" ) ) );
    }
}
else if( $action == "getFileContents" )
{
    $username = $_GET[ "username" ];
    $password = $_GET[ "password" ];
    $fileName = $_GET[ "filename" ];

    if( verifyPassword( $username, $password ) )
    {
        if( file_exists( "USER_DATA/" . $username . "/FILES" . "/" . $fileName ) )
        {
            $file = fopen( "USER_DATA/" . $username . "/FILES" . "/" . $fileName, "r" );
            $data = fread( $file, filesize( "USER_DATA/" . $username . "/FILES" . "/" . $fileName ) );
            fclose( $file );

            $aes = new AES($data, $password, 256);

            $dec = $aes -> decrypt( );

            echo( $dec );
        }
        else
        {
            echo( "HOUSECLOUD_FILE_OPR_ERR" );
        }
    }
    else
    {
        echo( "HOUSECLOUD_BAD_PASSCODE" );
    }
}
else
{
    echo( json_encode( array( "error" => true, "value" => "inval_opr" ) ) );
}
?> 