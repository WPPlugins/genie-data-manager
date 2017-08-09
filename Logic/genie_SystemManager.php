<?php
include_once dirname(dirname(__FILE__)) . '/Logic/genie_DynamicCreation.php';
include_once dirname(dirname(__FILE__)) . '/Logic/genie_CMSSpecials.php';
include_once dirname(dirname(__FILE__)) . '/Logic/genie_GeneralUsage.php';
include_once dirname(dirname(__FILE__)) . '/Logic/genie_DataBase.php';
  class genie_SystemManager
  {
      public function Manage($Request)
      {
          
          $results =array("error"=> "No permission or no form action");
          $DM_DataBase=new genie_DataBase();
          if($DM_DataBase->getSpecialPermission() &&  is_admin() && isset($Request['formaction']))
          {
                 $results=array("error"=> "No known action");
                 switch($Request['formaction'])
                {
                    case "LoadTables":
                        $DM_DynamicCreation = new genie_DynamicCreation();
                        $results = $DM_DynamicCreation->CreateTablesFromFile("defaults.txt");
                    break;
                    case "RunQuery":
                         $results = $this->RunQuery($Request);
                        break;
                    case "SaveQueryToFile":
                        $results = $this->SaveQuery($Request);
                        break; 
                     case "GetDefaultDefinitions":
                        $results =  $this->GetDefinitions($Request);
                        break; 
                     case "GetFormTestHtml":
                       $results =  $this->GetFormTestHtml($Request);
                       return $results;
                        break; 
                       case "GetFormTestModels":
                         $results =  $this->GetFormTestModels($Request);
                       
                        break; 
                       case "SaveSystemState":
                            $results=$this->SaveSystemState($Request);
                       break;
                       case "LoadSystemState":
                            $results=$this->LoadSystemState($Request);
                       break;
                       
                        case "SaveTemplate":
                            $results=$this->SaveTemplate($Request);
                       break;
                        case "LoadTemplate":
                            $results=$this->LoadTemplate($Request);
                       break;
                       
                        
                }
          }
          return json_encode($results);
      }
     
     
      public function SaveSystemState($Request)
      {
         
         $filePath="SystemState/systemState" ;
         if(isset($Request['fileNameTxt']))
         {
                  $filePath="SystemState/". $Request['fileNameTxt']."";
         }
         $DM_DataBase= new genie_DataBase();
       
          return $DM_DataBase->SaveDatabaseSystemState($filePath);
      }
      
       public function LoadTemplate($Request)
      {
          if(isset($Request['templateFileText']))
         {
             $templateFileText= $Request['templateFileText'];
             $upload_dir = wp_upload_dir();
             $uploadDirURL =  $upload_dir['url'];
             $uploadDirPath=$upload_dir["path"];
             $templateFileText=str_replace($uploadDirURL,$uploadDirPath,$templateFileText);
            
             if(file_exists($templateFileText)) {
                 
                 $fileName=str_replace($uploadDirPath."/","",$templateFileText);
                 $file = "Templates/".$fileName;
                 $fileDestination =  dirname(dirname(__FILE__))."/files/".$file ;
                 $res= copy (  $templateFileText , $fileDestination );
                 if($res)
                 {
                   $DMGeneralUsage = new genie_GeneralUsage();
                   $DMGeneralUsage->unzip($fileDestination,$fileName);
                   
                   $DMDataBase = new genie_DataBase();
                   $fileNameToLoad=str_replace(".zip","",$file);
                   $result = $DMDataBase->LoadSystemState($fileNameToLoad,false,true); 
                   if($result=="finished loading")
                   {
                       $DMCMS = new genie_CMSSpecials();
                       $entityId = $DMDataBase->getEntityId("DMSysEntities");
                       $result=$DMCMS->Translate("Template loaded visit",$entityId)." <a href='admin.php?page=DM_DesignForm'>".$DMCMS->Translate("Portalic Management",$entityId)."</a>";
                       unlink($fileDestination);
                       return $result;
                   }
                 }
             }
         }
      }
      
       public function SaveTemplate($Request)
      {
         
         $filePath="Templates/";
         if(isset($Request['templateName']) && isset($Request['entities']))
         {
             $filePath.= $Request['templateName']."";
            $DM_DataBase= new genie_DataBase();
            if($DM_DataBase->CheckIfNotEmpty($Request['entities']))
            {
                $paramsRow=array();
            foreach($Request['entities'] as $entity)
            {
                $params = $DM_DataBase->createParamsFromRow(array('id'=>$entity),false);
                if($DM_DataBase->CheckIfNotEmpty($params))
                {
                    foreach($params as $param)
                    {
                        array_push($paramsRow,$param);
                    }
                }
            }
            $result= $DM_DataBase->SaveDatabaseState($filePath,$paramsRow,true,true);
            $fileNames=$result["fileNames"];
            $DMGeneRalUsage=new genie_GeneralUsage();
            $filePath=$Request['templateName'].".zip";
            $DMGeneRalUsage->saveArray(array(array('templateName'=>$Request['templateName'])),$filePath."templateName",",",true);
            array_push($fileNames,$filePath."templateName");
            $DMGeneRalUsage->create_zip($fileNames,$filePath,true,true);
            
            $upload_dir = wp_upload_dir();
            $destination =  $upload_dir['url']."/".$filePath;
            //$filePath = plugin_dir_url(dirname(dirname(__FILE__))."files/".$filePath);
            foreach($fileNames as $file)
            {
                $file =  dirname(dirname(__FILE__))."/files/".$file ;
                if(file_exists($file)) {
                    unlink($file);
                }
            }
            return array("result"=>$result["result"],"zip"=>$destination);
            }
         }
      }
      
       public function LoadSystemState($Request)
      {
         
         $filePath="SystemState/systemState" ;
         if(isset($Request['fileNameTxt']))
         {
                  $filePath="SystemState/". $Request['fileNameTxt']."";
         }
         $DM_DataBase= new genie_DataBase();
         
          return $DM_DataBase->LoadSystemState($filePath,false);   
      }
      
       public function GetFormTestHtml($Request)
      {
          $html=array('error'=>'no table name');
          if(isset($Request['parameters']))
          {
             $DM_DynamicCreation=new genie_DynamicCreation();
             $html = $DM_DynamicCreation->CreateFormByTable($Request['parameters']['tableName'],$Request['parameters']['formType']);
          }
          return $html;
             
      }
      
        public function GetFormTestModels($Request)
      {
          $models=array('error'=>'no table name');
          if(isset($Request['parameters']))
          {
             $DM_DynamicCreation=new genie_DynamicCreation();
             $models = $DM_DynamicCreation->CreateModelByTable($Request['parameters']['tableName'],$Request['parameters']['formType']);
          }
          return $models;
             
      }
  
  
      public function GetDefinitions($Request)
      {
          $definitions=array('error'=>'no table name');
          if(isset($Request['tableNameText']))
          {
             $DM_DynamicCreation=new genie_DynamicCreation();
             $definitions = $DM_DynamicCreation->CreateFieldsListFromTable($Request['tableNameText'],true);
          }
          return $definitions;
             
      }
      
      public function SaveQuery($Request)
      {
            $myarray = $this->RunQuery($Request);
                $DM_GeneralUsage = new genie_GeneralUsage();
                $resultTemp = $DM_GeneralUsage->saveArray($myarray['resultsRow'],$Request['fileNameTxt'],"&");
               $pos = strrpos($resultTemp,"error");
               if($pos)
               {
                   $results= array ("error"=>$resultTemp);
               }
                $results=array("resultOk"=> $resultTemp); 
                return $results;
      }
  
      public function RunQuery($Request)
      {
           $results=array("error"=> "No Query");
            if(isset($Request["Query"]))
            {
                switch($Request["QueryType"])
                {
                    case "Results":
                        $DM_CMSSpecials = new genie_CMSSpecials();
                        $results=array("resultsRow"=> $DM_CMSSpecials->getResults($Request["Query"]));
                        
                        break;
                    default:
                       $DM_CMSSpecials = new genie_CMSSpecials();
                        $resultTemp = $DM_CMSSpecials->execute($Request["Query"]);
                        if(!$resultTemp)
                        {
                                $results=array("error"=> "Execution Error");   
                        }
                        else
                        {
                             $results=array("resultOk"=> "Query executed");   
                        }
                        break;
                        
                }
            }
            return $results;
      }
  }