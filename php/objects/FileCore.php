<?php

namespace Host;

class FileCore
{
    public static function getAllFolderPathsAt($parentFolder)
    {
        //  INIT
        $folderArray    = array();
        $parentFolder   = rtrim($parentFolder, '/\\');

        if(is_dir($parentFolder))
        {
            //  GET directory
            $d = dir($parentFolder);
            
            //  FOR each entry
            while (false !== ($entry = $d->read())) 
            {
                //  IF entry is valid block folder
                if($entry != '.'
                && $entry != '..'
                && strpos($entry, '.') !== 0
                && is_dir($parentFolder.'/'.$entry))
                {
                    //  BUILD path
                    $folderPath     = $parentFolder.'/'.$entry.'/';

                    //  STORE path
                    $folderArray[]  = $folderPath;

                    //  GET sub folder paths
                    $subFolderArray = self::getAllFolderPathsAt($folderPath);

                    //  STORE sub folder paths
                    $folderArray    = array_merge($folderArray, $subFolderArray);
                }
            }
            
            //  CLOSE directory
            $d->close();
        }

        return $folderArray;
    }

    public static function getFolderPathsAt($parentFolder)
    {
        //  INIT
        $folderArray    = array();
        $parentFolder   = rtrim($parentFolder, '/\\');

        if(is_dir($parentFolder))
        {
            //  GET directory
            $d = dir($parentFolder);
            
            //  FOR each entry
            while (false !== ($entry = $d->read())) 
            {
                //  IF entry is valid block folder
                if($entry != '.'
                && $entry != '..'
                && is_dir($parentFolder.'/'.$entry))
                {
                    //  BUILD path
                    $folderPath     = $parentFolder.'/'.$entry.'/';

                    //  STORE path
                    $folderArray[]  = $folderPath;
                }
            }
            
            //  CLOSE directory
            $d->close();
        }

        return $folderArray;
    }

    public static function getFoldersAt($parentFolder)
    {
        //  INIT
        $folderArray    = array();
        $parentFolder   = rtrim($parentFolder, '/\\');

        if(is_dir($parentFolder))
        {
            //  GET directory
            $d = dir($parentFolder);
            
            //  FOR each entry
            while (false !== ($entry = $d->read())) 
            {
                //  IF entry is valid block folder
                if($entry != '.'
                && $entry != '..'
                && is_dir($parentFolder.'/'.$entry))
                {
                    //  STORE path
                    $folderArray[]  = $entry;
                }
            }
            
            //  CLOSE directory
            $d->close();
        }

        return $folderArray;
    }

    public static function getFilesInFolder($parentFolder)
    {
        //  INIT
        $fileArray    = array();

        //  GET directory
        $d = dir($parentFolder);
        
        //  FOR each entry
        while (false !== ($entry = $d->read())) 
        {    
            //  IF entry is valid block folder
            if($entry != '.'
            && $entry != '..'
            && is_file($parentFolder.$entry))
            {
                $fileArray[]    = $strParentFolder.$entry;
            }
        }
        
        //  CLOSE directory
        $d->close();

        return $fileArray;
    }

    public static function pathEndsWith($path, $endsWith)
    {
        return (substr($path, 0, -1*strlen($endsWith)) == $endsWith);
    }

    public static function ensurePath($targetPath)
    {
        //  path token array
        $targetPathClean    = str_replace('\\','/',$targetPath);
        $partArray          = explode('/', trim($targetPathClean,'/'));

        //  start current path
        $currentPath        = each($partArray)['value'];

        //  if starts with drive letter
        if(strpos($currentPath, ':') !== false)
        {
            $currentPath   .= '/'.each($partArray)['value'];
        }

        //  for each path part
        while($partData = each($partArray))
        {
            //  path to check
            $checkPath  = $currentPath.'/'.$partData['value'];

            //  if path doesn't exist
            if(!is_dir($checkPath))
            {
                //  create last folder
                mkdir($checkPath);
            }

            //  update current path
            $currentPath = $checkPath;
        }
    }

    public static function getNamespaceFromFile($file)
    {
        //  init
        $maxLinesToScan     = 100;
        $namespace          = false;

        if(file_exists($file))
        {
            //  read file to array
            $lineArray  = file($file);

            foreach($lineArray AS $line)
            {
                //  if line starts with 'namespace'
                if(strpos($line, 'namespace') === 0)
                {
                    //  get namespace
                    $namespace  = trim(str_replace(array('namespace',';'),'',$line));

                    //  end foreach loop
                    break;
                }
            }
        }

        return $namespace;
    }
}