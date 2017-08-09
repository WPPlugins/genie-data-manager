<?php
include_once dirname(dirname(__FILE__)) . '/Logic/genie_CMSSpecials.php';

  class genie_GeneralUsage
  {
      
      public function copyArray(&$myArray)
      {
         $array = array_merge(array(), $myArray);
         return $array;
      }
      
      
      public function GetTypeMapping($formType)
      {
                  $dir =  dirname(dirname(__FILE__)) ;  
                  $types = $this->getData($dir.'/files/formTypesMapping.txt');
                  
          foreach($types as &$type)
          {
              if($type['type']==$formType)
              {
                  return $type;
              }
          }
          return array();
      }
      
      public function saveArray($myarray,$fileName,$delimiter,$addDir=true)
      {
          $dir=$fileName;
          if($addDir)
          {
              $dir =  dirname(dirname(__FILE__))."/files/".$fileName ;  
          }

            $txtResult = "";
            $openLine="";
              for($ind=0;$ind<count($myarray);$ind++)
              {
                  
                  foreach($myarray[$ind] as $key=>$value)
                  {
                    if($ind==0)
                    {
                      $openLine.=$key.$delimiter;
                    }
                    $txtResult.=$value.$delimiter;
                  }
                  $txtResult.="\n";
              }
              
             $fh = fopen($dir, 'w');
             if(!file_exists($dir))
             {
                  return "error - can't create file";    
             }
             fwrite($fh, $openLine."\n");
             fwrite($fh, $txtResult."\n");
             fclose($fh);
             return ' results written to file : ' . $dir;         
      }
      
      public function saveArrayNew($myarray,$fileName,$delimiter,$addDir=true, $linedelimiter="\n")
      {
          //TODO:Change save as json!
          $dir=$fileName;
          if($addDir)
          {
              $dir =  dirname(dirname(__FILE__))."/files/".$fileName ;  
          }

            $txtResult = "";
            $openLine="";
              for($ind=0;$ind<count($myarray);$ind++)
              {
                  
                  foreach($myarray[$ind] as $key=>$value)
                  {
                    if($ind==0)
                    {
                      $openLine.=$key.$delimiter;
                    }
                    $txtResult.=$value.$delimiter;
                  }
                  $txtResult.=$linedelimiter;
              }
              
             $fh = fopen($dir, 'w');
             if(!file_exists($dir))
             {
                  return "error - can't create file";    
             }
             fwrite($fh, $openLine.$linedelimiter);
             fwrite($fh, $txtResult.$linedelimiter);
             fclose($fh);
             return ' results written to file : ' . $dir;         
      }

      public function saveArrayJson( $myarray,$fileName,$addDir=true )
      {
          //TODO:Change save as json!
          $dir=$fileName;
          if($addDir)
          {
              $dir =  dirname(dirname(__FILE__))."/files/".$fileName ;
          }

          $txtResult=json_encode($myarray);


          $fh = fopen($dir, 'w');
          if(!file_exists($dir))
          {
              return "error - can't create file";
          }

          fwrite($fh, $txtResult);
          fclose($fh);
          return ' results written to file : ' . $dir;
      }
      
      
      
      public function getDataNew($fileName,$delimiter='&',$addDir=false, $linedelimiter="\n")
      {   
          if($addDir)
          {
              $fileName =  dirname(dirname(__FILE__))."/files/".$fileName ;  
          }
          $rowCount = 0;
          $output = array();
          $mapping = array();
          if(file_exists($fileName)) 
          {
              if (($handle = file_get_contents($fileName)) !== FALSE) {
                  
                  $dataArray = preg_split ("/".$linedelimiter."/",$handle);
                  
                    foreach ( $dataArray as $dataRow) 
                    {
                        $data = preg_split ("/".$delimiter."/",$dataRow);  
                        
                        if($rowCount==0)
                        {
                            foreach($data as $key=>$value)
                            {
                                 if(trim($value)!="")
                                 {
                                    array_push($mapping,array( 'index'=>$key,'key'=>$value));
                                 }
                            }
                        }
                        else
                        {
                            $outputRow=array();
                            foreach($mapping as $mappingRow)
                            {
                                if(isset($data[$mappingRow["index"]]))
                                {
                                    $outputRow[$mappingRow["key"]] = $data[$mappingRow["index"]];
                                }
                                else
                                {
                                   $outputRow[$mappingRow["key"]] = ""; 
                                }
                            }
                            array_push($output,$outputRow);
                        }
                        
                       $rowCount++; 
                    }
                 
              }
          }
       return $output;             
      }

      public function getDataJson($fileName,$addDir=false)
      {
          if($addDir)
          {
              $fileName =  dirname(dirname(__FILE__))."/files/".$fileName ;
          }
          $rowCount = 0;
          $output = array();
          $mapping = array();
          if(file_exists($fileName))
          {
              if (($handle = file_get_contents($fileName)) !== FALSE) {
                  $output = json_decode($handle,true);
              }
          }
          return $output;
      }

      public function getData($fileName,$delimiter='&',$addDir=false)
      {   
          if($addDir)
          {
              $fileName =  dirname(dirname(__FILE__))."/files/".$fileName ;  
          }
          $rowCount = 0;
          $output = array();
          $mapping = array();
          if (($handle = fopen($fileName, "r")) !== FALSE) {
                while (($data = fgetcsv($handle, 1000,$delimiter)) !== FALSE) 
                {
                    if($rowCount==0)
                    {
                        foreach($data as $key=>$value)
                        {
                             
                            array_push($mapping,array( 'index'=>$key,'key'=>$value));
                        }
                    }
                    else
                    {
                        $outputRow=array();
                        foreach($mapping as $mappingRow)
                        {
                            $outputRow[$mappingRow["key"]] = $data[$mappingRow["index"]];
                        }
                        array_push($output,$outputRow);
                    }
                    
                   $rowCount++; 
                }
           fclose($handle);   
          }
       return $output;       
          
      }
      

      
      public function getFileContent($fileName)
      {
          if(file_exists($fileName))
          {
            $file = file_get_contents($fileName);
            return $file;
          }
          return "sorry file not found:".$fileName;
          
      }
     
        public function checkPosition($pattern, $tag)
      {
          $pos =  strrpos($pattern,$tag);
           if($pos===false)
              {
                return false;
              }
          return true;
            
      }
       
      public function setFileContent($fileName,$content)
      {
          
           if(file_exists($fileName))
          {
              file_put_contents($fileName, $content);  
            
                return "ok";
          }
          return "sorry file not found:".$fileName;
      }
      
      function generateRandomString($length = 10) 
      {
            $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
            $randomString = '';
            for ($i = 0; $i < $length; $i++) {
                $randomString .= $characters[rand(0, strlen($characters) - 1)];
            }
            return $randomString;
      }
      
      function translateAll($content,$entityId=0)
      {
          $DM_CMSSpecials = new genie_CMSSpecials();
          $translatedValues=array();
          $partsToTranslate = preg_split ("/\[@endtranslate@\]/",$content);
          foreach($partsToTranslate as $part)
          {
              if(!(strrpos($part,"[@translate@]")===false))
              {
                  $part .="[@endtranslate@]";
                  //TODO:Check it!
                  $found=preg_match ("/\[@translate@\](.*)\[@endtranslate@\]$/",$part,$translatedValues);
                  if($found>0)
                  {
                       $toTranslate = $translatedValues[1];
                       if(trim($toTranslate)!="")
                       {
                            $value = $DM_CMSSpecials->Translate($toTranslate,$entityId);
                            $content= str_replace($translatedValues[0],$value,$content);      
                       }
                  }
              }
          }
          
          return $content;
      }
      
       public function mysql_escape_mimic($inp) 
      { 
        if(is_array($inp)) 
            return array_map(__METHOD__, $inp); 

        if(!empty($inp) && is_string($inp)) { 
            return str_replace(array('\\', "\0", "\n", "\r", "'", '"', "\x1a"), array('\\\\', '\\0', '\\n', '\\r', "\\'", '\\"', '\\Z'), $inp); 
        } 

        return $inp; 
      } 
      
      function unzip($destination,$fileName)
      {
          //system('unzip '.$destination); 
          $zip = new ZipArchive;
          $res = $zip->open($destination);
          
          if ($res === TRUE) {
              $path = str_replace($fileName,"",$destination);
            $zip->extractTo($path);
          }
          $zip->close();
          
      }
      
      function createPath($path) {
    if (is_dir($path)) return true;
    $prev_path = substr($path, 0, strrpos($path, '/', -2) + 1 );
    $return = $this->createPath($prev_path);
    return ($return && is_writable($prev_path)) ? mkdir($path) : false;
}

      /* creates a compressed zip file */
    function create_zip($files = array(),$destination = '',$overwrite = false,$addDir=true)
    {
         if($addDir)
          {
              $upload_dir = wp_upload_dir();
              $uploadDir=$upload_dir["path"];
              
              $res = $this->createPath($uploadDir);
              if(!$res)
                return "failed";
              $destination =  $uploadDir."/".$destination ;  
          }
        //if the zip file already exists and overwrite is false, return false
        if(file_exists($destination) && !$overwrite) { return false; }
        //vars
        $valid_files = array();
        //if files were passed in...
        if(is_array($files)) {
            //cycle through each file
            foreach($files as $file) {
                 if($addDir)
                  {
                       $fileName=str_replace("Templates/","",$file);
                      $file =  dirname(dirname(__FILE__))."/files/".$file ;
                       
                       //$res= copy (  $file , $uploadDir."/".$fileName );
                       //if($res)
                       //{
                        //   $file= $uploadDir."/".$fileName;
                       //}
                  }
                //make sure the file exists
                if(file_exists($file)) {
                    array_push($valid_files,array('file'=> $file,'localName'=>$fileName));
                }
            }
        }
        //if we have good files...
        if(count($valid_files)) {
            //create the archive
            $zip = new ZipArchive();
            if($zip->open($destination,$overwrite ? ZIPARCHIVE::OVERWRITE : ZIPARCHIVE::CREATE) !== true) {
                return false;
            }
            //add the files
            foreach($valid_files as $file) {
                $zip->addFile($file['file'],$file['localName']);
            }
            //debug
            //echo 'The zip archive contains ',$zip->numFiles,' files with a status of ',$zip->status;
            
            //close the zip -- done!
            $zip->close();
            
            //check to make sure the file exists
            return file_exists($destination)?$destination:false;
        }
        else
        {
            return false;
        }
    }
  }