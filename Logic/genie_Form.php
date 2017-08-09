<?php
include_once dirname(dirname(__FILE__)) . '/Logic/genie_GeneralUsage.php';
include_once dirname(dirname(__FILE__)) . '/Logic/genie_DataBase.php';
include_once dirname(dirname(__FILE__)) . '/Logic/genie_CMSSpecials.php';
include_once dirname(dirname(__FILE__)) . '/Logic/genie_DynamicCreation.php';
include_once dirname(dirname(__FILE__)) . '/Logic/genie_MailChimp.php';
include_once dirname(dirname(__FILE__)) . '/Logic/genie_Filters.php';
include_once dirname(dirname(__FILE__)) . '/Logic/genie_ModelHelper.php';
include_once dirname(dirname(__FILE__)) . '/Logic/genie_Template.php';
include_once dirname(dirname(__FILE__)) . '/Logic/genie_FormHelper.php';
//genie_CMSSpecials
  class genie_Form
  {

      private $developing = GENIE_DEVELOPMENT_MODE;
   
      public function CheckPermission($formId, $actionType)
      {
          //TODO: Add permission check for action
          //validate version of code
          return true;
      }
       
      public function Manage($Request)
      {
        if(isset($Request['formaction']) && isset($Request['formParameters'])
            && (isset($Request['formParameters']['formName']) || isset($Request['formParameters']['formId'])))
        {
             $results= "";
             $returnData = array();
             $nomodel=false;
             $error=null; 
             if($this->CheckPermission($Request['formParameters']['formId'],$Request['formaction']))
             {
                switch($Request['formaction'])
                {
                    case 'SaveChanges':
                    if(isset($Request['model']) )
                        {
                            $results= $this->SaveChanges($Request['model'],$Request['formParameters']);
                        }
                        break;
                     case 'GetDataById':
                          $filter = null;
                          if(isset($Request['filter']))
                          {
                                    $filter = $Request['filter'];
                          }
                          
                          $returnDataArray=$this->GetDataByIdNew($Request['formParameters'],$Request['myurl'],false,$filter);  
                         $results = $returnDataArray['results'];
                         $returnData = $returnDataArray['resultsData'];
                        break;
                    case 'Search':
                     if(isset($Request['model']) )
                        {
                            $returnDataArray= $this->Search($Request['model'],$Request['formParameters']);
                            $results = $returnDataArray['results'];
                            $returnData = $returnDataArray['resultsData'];
                        }
                        break;
                     case 'AddNew':
                     if(isset($Request['model']) )
                        {
                            $results= $this->Add($Request['model'],$Request['formParameters'],$Request['currUrl']);
                        }
                        break;
                     case 'SaveData':
                        if(isset($Request['model']) )
                        {
                            $results= $this->Save($Request['model'],$Request['formParameters']);
                        }
                        break;
                     case 'DeleteData':
                        if(isset($Request['model']) )
                        {
                            $results= $this->Delete($Request['model'],$Request['formParameters']);
                        }
                        break;
                     case 'CreateDefaultForm':
                              if(isset($Request['entityId'])   )
                                {
                                     $entityId = $Request['entityId'];
                                     $color="";
                                     if(isset($Request['color']) && trim($Request['color'])!="")
                                     {
                                         $color = " ".$Request['color'];
                                     }
                                     $results=$this->CreateDefaultForm($entityId,$color);
                                }
                                $nomodel=true;
                     break;
                }
                if(!$nomodel)
                {
                    if(isset($Request['returnFormParameters']))
                    {
                        $modelName=null;
                        $filter=null;
                       if(isset($Request['model']) && isset($Request['model']['name']) && $Request['formaction']!="AddNew"  && $Request['formaction']!="DeleteData")
                        {
                             $modelName = $Request['model']['name'];
                             
                        }
                          if(isset($Request['filter']) && isset($Request['filter']['name']) )
                          {
                              if( ( $Request['formaction']!="AddNew" && $Request['formaction']!="DeleteData") ||  ($Request['filter']['name']!=$Request['model']['name']))
                              {      
                                $filter = $Request['filter'];
                              }
                          }
                        $returnDataArray=  $this->GetDataByIdNew($Request['returnFormParameters'],$Request['myurl'],false,$filter,$modelName);
                        $returnData = $returnDataArray['resultsData'];
                    }
                    if($this->ReturnDefault($Request,$returnData))
                    {
                         $modelName=null;
                        $filter=null;
                        
                        if(isset($Request['model']) && isset($Request['model']['name']) && $Request['formaction']!="AddNew"  && $Request['formaction']!="DeleteData")
                        {
                             $modelName = $Request['model']['name'];
                             
                        }
                          if(isset($Request['filter']) && isset($Request['filter']['name']) )
                          {
                              if( ( $Request['formaction']!="AddNew" && $Request['formaction']!="DeleteData") || ($Request['filter']['name']!=$Request['model']['name']))
                              {      
                                $filter = $Request['filter'];
                              }
                          }
                         $returnDataArray= $this->GetDataByIdNew($Request['formParameters'],$Request['myurl'],false,$filter,$modelName);
                        $returnData = $returnDataArray['resultsData'];
                    }
                
                    
                    if($this->checkPosition($results,"error"))
                    {
                        
                        while($this->checkPosition($results,"error"))
                        {
                            $results = str_replace("error","",$results);
                        }
                        $error = $results;
                    }
                }
                return json_encode(array('error'=>$error,'result'=>$results,'updatedData'=>$returnData));
            }     
        }
      }
      
      public function CreateDefaultForm($entityId, $color="")
      {
          
          $DM_DataBase = new genie_DataBase();
          
          $tableDetails = $DM_DataBase->GetTableDetailsByEntity($entityId);
          $tableName=$tableDetails["tableName"];
          $Definitions = $DM_DataBase->getDefinitions($tableName);
          $countInRow=0;
          if($DM_DataBase->CheckIfNotEmpty($Definitions))
          { 
              if($this->checkPosition($color,"none"))
              {
                  $pattern="<div class='form'>";
                  $pattern.="<legend>".$tableDetails['name']."</legend>";
                   foreach($Definitions as $Definition)
                                  {
                                       $extra="";
                                      if($Definition["fieldName"]=="id")
                                      {
                                           continue;
                                      }
                                      $fieldcode="[@field_id_".$Definition["id"].$extra."@]";  
                                      $labelcode="[@label_id_".$Definition["id"]."@]";  
                                      if($countInRow==3)
                                      {
                                           $pattern.="<br/>";
                                          $countInRow=0;
                                      }
                                      
                                      $pattern.="".$labelcode."";
                                      $pattern.=$fieldcode;
                                      $countInRow++;
                                  }
                  $pattern.="</div>";
              }               
              else
              {
                  if($this->checkPosition($color,"DM_"))
                  {
                      $pattern = "<div class='".$color."'>";
                          $pattern .= "<div class='DMbody'>";
                              $pattern.="<div class='DMform'>";
                                  $pattern.="<div class='DMfieldset'>";
                                  $pattern.="<h1>".$tableDetails['name']."</h1>";
                                  foreach($Definitions as $Definition)
                                  {
                                       $extra="";
                                      if($Definition["fieldName"]=="id")
                                      {
                                           continue;
                                      }
                                      $fieldcode="[@field_id_".$Definition["id"].$extra."@]";  
                                      $labelcode="[@label_id_".$Definition["id"]."@]";  
                                      if($countInRow==3)
                                      {
                                           $pattern.="<div class='DMfieldset'>";
                                           $pattern.="</div>";
                                          $countInRow=0;
                                      }
                                      
                                      $pattern.="<div class='DM_label'>".$labelcode."</div>";
                                      $pattern.=$fieldcode;
                                      $countInRow++;
                                  }
                                  $pattern.="</div>";
                                  
                                   $pattern.="[@formButton@]";
                              $pattern.="</div>";
                          $pattern.="</div>";
                      $pattern.="</div>";
                  }
                  else
                  {
                      $countInRow=0;
                      $style="";
                      
                      $pattern=$style.'
                      <div class="DM_AdminPanel">
            <div class="DM_main_page"><div class="DM_popup_box_container'.$color.'">';
                      $pattern.='<div class="DM_box_title'.$color.'"><label>'.$tableDetails['name'].'</label></div>';
                      $pattern .= '<div class="DM_box_body'.$color.'">[@errorMessage@][@successMessage@]';
                      foreach($Definitions as $Definition)
                      {
                          
                          $extra="";
                          if($Definition["fieldName"]=="id")
                          {
                               continue;
                          }
                          $fieldcode="[@field_id_".$Definition["id"].$extra."@]";  
                          $labelcode="[@label_id_".$Definition["id"]."@]";  
                          if($countInRow==3)
                          {
                              $pattern.="<br/>";
                              $countInRow=0;
                          }
                          if($Definition["input_type"]=="textarea" || $Definition["input_type"]=="htmleditor")
                          {
                                $pattern.='<div class="DM_custom_input_box'.$color.' DM_allwidth">';
                                $pattern.='<p>'.$labelcode.'</p>';
                                $pattern.=$fieldcode;
                                $pattern.='</div>';
                                $pattern.="<br/>";
                                $countInRow=0;
                          }
                          else
                          {
                              $pattern.='<div class="DM_custom_input_box'.$color.'">';
                              if($Definition["input_type"]=="checkbox" || $Definition["type"]=="boolean")
                              {
                                   $pattern.='<div class="DM_input-control DM_checkbox'.$color.'" data-role="input-control">';
                                    $pattern.=$fieldcode; 
                                    $pattern.="</div>";   
                              }
                              else
                              {
                                  
                                   $pattern.="<p>".$labelcode."</p>".$fieldcode."";
                              }
                              $pattern.="</div>";
                              $countInRow++;    
                          }
                      }
                      $pattern.="<br/><p class='DM_UserPanel".$color."' style='text-align:center;'>[@formButton@]</p>";
                      $pattern.="</div>";
                      $pattern.="</div>";
                      $pattern.="</div>";
                      $pattern.="</div>";
                  }
              }
          }
          return $pattern;
      }

      public function ReturnDefault($Request,$returnData)
      {
          $result=false;
           if(count($returnData)<1 && !isset($Request['returnFormParameters']) && $Request['formaction']!="Search")
           {
               $result=true;
           }
           return $result;
      }
      
      public function Save($ModelParameters,$formParameters)
      {
           $DM_DataBase= new genie_DataBase();
           $results="";
           $model = $ModelParameters['data'];
           $DM_CMSSpecials=new genie_CMSSpecials();
           $setParameters = $DM_DataBase->createParamsFromRow($model,true);
           $rowId=$DM_DataBase->GetId($setParameters);
           $tableDetails = $DM_DataBase->GetTableDetails($formParameters['formName']); 
           if(count($tableDetails)>=1)
           {
               $changedArr =$DM_DataBase->checkIfChanged($model,$tableDetails["id"]);
               $changed=$changedArr['result'];
               $errorMessage = $changedArr['errorMessage'];
               if($changed)
               {
                    
                    $whereParameters =$DM_DataBase->createParamsFromRow(array('id'=>$rowId));                     
                    $setParameters = $DM_DataBase->createParamsFromRow($model,true);   
                   $tableName = $tableDetails['tableName'];
                   if($DM_DataBase->getPermission($tableName,"Change"))
                   {
                        $results  = $this->SaveHelper($model,$tableName,$tableDetails,$setParameters,$results); 
                   }
                   else
                   {
                      $errorMessage=$DM_CMSSpecials->Translate("You do not have permission to save changes",$tableDetails["id"]);
                   }
                    
          
               }
               if($errorMessage!="" && $errorMessage!="not changed")
               {
                  $results.=$rowId.":".$errorMessage; 
               }
          }
           return $results;
      }
      
      private function SaveHelper($model,$tableName,$tableDetails,$setParameters,$results)
      {
           $res="success";
           $DM_DataBase = new genie_DataBase();
           $rowId = $DM_DataBase->GetId($setParameters);
            $DM_CMSSpecials = new genie_CMSSpecials();
         if($DM_DataBase->getPermission($tableName,"Change"))
         {
                    $entityId=$DM_DataBase->getEntityId($tableName);  
                    $changedArr =$DM_DataBase->checkIfChanged($model,$entityId);
                   $changed=$changedArr['result'];
                   $errorMessage = $changedArr['errorMessage'];
                   if($changed)
                   {
                    /*   switch($tableName)
                       {   
                           case "DMSysEntities":
                                $genie_Table=new genie_Table();
                                $res = $genie_Table->UpdateTable($model);
                                break;
                           case  "DMSysDefinitions":
                                $genie_Table=new genie_Table();
                                $res = $genie_Table->UpdateField($model);
                                break;
                           case "DMSysForms":
                                //save table connections
                                break; 
                       }
                      */ 
                       
                            
                           $whereParameters = $DM_DataBase->createParamsFromRow(array("id"=>$rowId),false);
                           
                            $resArr= $DM_DataBase->UpdateTable($tableName,$setParameters,$whereParameters);
                            $res=$resArr['res'];
                           $errorMessage=$resArr['error'];      
                        if($results!="")
                            $results.="<br/>";
                            
                       if($res=="success")
                       {
                            $results .= $rowId.": ". $DM_CMSSpecials->Translate($tableDetails["successUpdatetxt"],$tableDetails['id']);
                            
                            
                       }
                       else
                       {
                           
                            $error="error ". $rowId.":".$DM_CMSSpecials->Translate($tableDetails["failUpdatetxt"],$tableDetails['id']);
                            if(!($this->checkPosition($errorMessage,"queryProblems:")))
                            {
                                $error.=" : ".$errorMessage;
                            }
                            $results .= $error;
                            
                           
                       }
                   }
             
           }
           else
           {
              $errorMessage=$DM_CMSSpecials->Translate("You do not have permission to save changes",$tableDetails["id"]);
           }
            if($errorMessage!="" && $errorMessage!="not changed")
           {
              $results.="not changed: ". $rowId.":".$errorMessage; 
           }
           return $results;
           
      }
      
       public function Add($Models,$formParameters,$currUrl)
      {
          if(count($Models['data'])>=1)
          {
              $DM_DataBase= new genie_DataBase();
             $results="";
             $model = $Models['data'][0];     
              
            $setParameters = $DM_DataBase->createParamsFromRow($model,true);
            
            
             $tableDetails = $DM_DataBase->GetTableDetails($formParameters['formName']);
             if(count($tableDetails)>=1)
             {
                $tableName = $tableDetails['tableName'];
               
                 $results = $this->AddHelper($model,$tableName,$tableDetails,$setParameters,$results,$currUrl);
                 
                 
                return $results;
             }
          }
      }
      
      private function AddHelper($model,$tableName,$tableDetails,$setParameters,$results,$currUrl='')
      {
          $DM_CMSSpecials = new genie_CMSSpecials();
           $DM_DataBase = new genie_DataBase();
         if($DM_DataBase->getPermission($tableName,"Add"))
         {
               $res="success";
               $resInsert=-1;
               switch($tableName)
               {
                   case "DMSysEntities":
                        $DM_Table=new genie_Table();
                        $res = $DM_Table->AddTable($model);
                        break;
				/*TODO:Split definitions*/
                   case  "DMSysDefinitions":
                        $DM_Table=new genie_Table();
                        $res = $DM_Table->AddField($model,true);
                        break;
                    
               }
               
               if($res=="success")
               { 
                   $setParameters=$this->ResetIsSystem($tableName,$setParameters);
                   
                   $errorMessage="";
                    $resArray=$DM_DataBase->Insert($tableName,$setParameters,false);
                    $resInsert = $resArray['res'];
                    $errorMessage= $resArray['error'];   
                    if($resInsert>-1)
                    {     
                          $this->NewEmailsProcedure($model,$tableName,$DM_DataBase,$DM_CMSSpecials,$currUrl);
                          $results=$this->FirstDefinitionsInsert($tableName,$resInsert,$DM_CMSSpecials,$DM_DataBase,$results);
                          $results .= $resInsert.": ". $DM_CMSSpecials->Translate( $tableDetails["successInserttxt"],$tableDetails['id']);
                    }
                    else
                    {
                         if($tableName=="DMSysEntities")
                           {
                                $DMTable = new genie_Table();
                                $DM_Table->DeleteTable($model);
                           }
                        $error="error ".$DM_CMSSpecials->Translate($tableDetails["failInserttxt"],$tableDetails['id']);
                        if(strrpos("queryProblems:",$errorMessage)===FALSE)
                        {
                            $error.=": ".$errorMessage.$resInsert;
                       }
                        $results .= $error;
                    }
               }
               else
               {
                    $res = str_replace("error","",$res);
                    $res = str_replace(": ","",$res);
                     $results .= " error ". $DM_CMSSpecials->Translate($res,$DM_DataBase->getEntityId("DMSysEntities"));  
               }
                if(trim($results)!="")
                    $results.="<br/>";
               //  echo $errorMessage;
         }
         else
         {
             $results.=$DM_CMSSpecials->Translate("You do not have permission to add new item",$DM_DataBase->getEntityId("DMSysEntities"));
         }
            return $results;
      }
    
    private function FirstDefinitionsInsert($tableName,$resInsert,$DM_CMSSpecials,$DM_DataBase,$results)
    {
        if($tableName=="DMSysEntities")
       {
            $DM_Table = new genie_Table();
            $ress = $DM_Table->CreateFirstDefinitions($resInsert);
            if($this->checkPosition($ress,"error"))
            {
                $ress = str_replace("error","",$ress);
                $ress = str_replace(": ","",$ress);
               $results .= $res.": error ". $DM_CMSSpecials->Translate($ress,$DM_DataBase->getEntityId("DMSysEntities"));
            }
            
       }
       return $results;
    }
    
    private function ResetIsSystem($tableName,$setParameters)
    {
            
          /* if($tableName!="DMSysEntities")
           {     
               $genie_DataBase = new genie_DataBase();
               $tableDetails = $genie_DataBase->GetTableDetailsByTableName($tableName);
              $isSystem = ($tableDetails["isSystem"]=="1"?true:false);
                    
                        foreach($setParameters as &$param)
                        {
                            if($param["fieldName"]=="isSystem")
                            {
                                $param["value"]=$isSystem."";
                            }
                        }
           }*/
          return $setParameters;
    }
     
     private function NewEmailsProcedure($model,$tableName,$DM_DataBase,$DM_CMSSpecials,$currUrl)
     {
            $DM_DataBase= new genie_DataBase();
            $tableDetails = $DM_DataBase->GetTableDetailsByTableName($tableName);
             $definitions = $DM_DataBase->createDefinitionsFromRow($tableName,$model,true);
           $email="";
            if(isset($tableDetails['email']) && trim($tableDetails['email'])!="")
            {
                $email = $tableDetails['email'];
            }
          $secondEmail="";
          
          if(isset($tableDetails['emailField'] ) && trim($tableDetails['emailField'])!="" && isset($model["field_".$tableDetails['emailField']] ) && trim($model["field_".$tableDetails['emailField']])!="")
          {
            $secondEmail = $model["field_".$tableDetails['emailField']];
          }
         if(trim($email)!="" || (isset($secondEmail) && trim($secondEmail)!=""))
          {
                
                $emailText=$tableDetails['emailText'];
                $title = $tableDetails['emailTitle'];
                $Defitinions = $DM_DataBase->getDefinitions($tableName);
                $res = $this->ReplaceDefsInText($title,$emailText,$Defitinions);
                $emailText=$res['emailText'];
                $title=$res['title'];
                if(trim($currUrl)!="")
                {
                    $emailText.=$DM_CMSSpecials->Translate("Generated on:",$DM_DataBase->getEntityId("DMSysEntities")).$currUrl;
                }
                if( (isset($email) && trim($email)!=""))
                {
                    $emails = explode (";",$email);
                    foreach($emails as $emailCurr)
                    {
                        $DM_CMSSpecials->sendMail($emailCurr,$emailText,$title);
                    }
                }
                
                if( (isset($secondEmail) && trim($secondEmail)!=""))
                {
                    $emailCurr = $secondEmail;
                    
                    $DM_CMSSpecials->sendMail($emailCurr,$emailText,$title);
                   
                    $AddToMailChimp = $tableDetails["AddToMailChimp"];
                    $MailChimpAPI=$tableDetails["MailChimpAPI"];
                    $MailChimpListID = $tableDetails["MailChimpListID"];
                     if($this->ValIsNotEmpty($AddToMailChimp) && $AddToMailChimp=="1" && $this->ValIsNotEmpty($MailChimpAPI) && $this->ValIsNotEmpty($MailChimpListID))
                    {
                        $DM_MailChimp=new genie_MailChimp();
                        $DM_MailChimp->AddMail($emailCurr,$MailChimpAPI,$MailChimpListID,$currUrl);
                    }
                    
                }
                   
          }
     }
     
     private function ReplaceDefsInText($title,$emailText,$Definitions)
     {
         $DM_DataBase = new genie_DataBase();
         if($DM_DataBase->CheckIfNotEmpty($Definitions))
         {
                  foreach($Definitions as $def)
                {
                    $tag = "[@field_id_".$def["id"]."@]";
                     if($this->checkPosition($title,$tag) || $this->checkPosition($emailText,$tag))
                    {
                        if(isset($model["field_".$def["id"]]))
                        {
                            $value = $model["field_".$def["id"]];
                        }
                        else
                        {
                            $value=$def["defaultValue"];
                        }
                        $emailText=str_replace($tag,$value,$emailText);
                        $title=str_replace($tag,$value,$title);
                    }
                }
         }
       return array('title'=>$title,'emailText'=>$emailText);                         
     }

     public function ValIsNotEmpty($val)
     {
         if(isset($val) && trim($val)!="")
         {
             return true;
         }
         return false;
     }
      
     public function Delete($Models,$formParameters)
     {
         $result="";
           $DM_DataBase= new genie_DataBase();
            $tableDetails = $DM_DataBase->GetTableDetails($formParameters['formName']);
             if(count($tableDetails)>=1)
             {
                $tableName = $tableDetails['tableName'] ;
                $model = $Models['data'];  
                $result=$this->DeleteHelper($model,$tableName,$tableDetails,$result);   
               
             }
          return $result;
     }
     
     private function DeleteHelper($model, $tableName,$tableDetails,$result)
     {
          $DM_CMSSpecials = new genie_CMSSpecials();
          $DM_DataBase = new genie_DataBase();
         if($DM_DataBase->getPermission($tableName,"Change"))
         {
              
              $rowId=$DM_DataBase->getIdFromRow($model,true);
                $deleteParams =$DM_DataBase->createParamsFromRow( array("id"=>$rowId ),false);
                
              $res="success";
               switch($tableName)
               {
                   case "DMSysEntities":
                        $DM_Table=new genie_Table();
                        $res = $DM_Table->DeleteTable($model);
                        break;
                   case  "DMSysDefinitions":
					/*TODO:Split definitions*/
                        $DM_Table=new genie_Table();
                        $res = $DM_Table->DeleteField($model);
                        break;
                    
               }
               if($res=="success")
               { 
                      
                    $res=$DM_DataBase->Delete($tableName,$deleteParams);
               }
               if($result!="")
                    $result.="<br/>";
                if($res=="success")
                {
                    $result .= $rowId.": ". $DM_CMSSpecials->Translate($tableDetails['successDeleteTxt'],$tableDetails["id"]);
                }
                else
                {
                     $result .= "error ".$rowId.": ".$DM_CMSSpecials->Translate($tableDetails['failDeleteTxt'],$tableDetails['id']);
                }
         }
         else
         {
               $result.="error ".$rowId.": ".$DM_CMSSpecials->Translate("You do not have permission to add new item",$DM_DataBase->getEntityId("DMSysEntities"));  
         }  
                return $result;
     }
   
      public function Search($Models,$formParameters)
      {
          $resultData =array(); 
           $DM_DataBase= new genie_DataBase();
            $tableDetails = $DM_DataBase->GetTableDetails($formParameters['formName']);
            $definitions = $DM_DataBase->getDefinitions($tableDetails['tableName']);
             $validators = $this->GetValidators($definitions); 
             $count = 0;
        $model = $Models['data'][0];     
          $whereParams = $DM_DataBase->createParamsFromRow($model,true);
          $Results = $DM_DataBase->Select($tableDetails['tableName'],$whereParams);
          if(count($Results)>0)
          {
              $pagesize = $tableDetails['groupnumtxt'];
              if($pagesize==null || is_numeric($pagesize)==false)
              {
                  $pagesize=1;
              }
              array_push($resultData,array('name'=>$formParameters['formName'],'type'=>'group',"data"=>$Results,'validators'=>$validators,
           'pagesize'=>$tableDetails['groupnumtxt'],'pagenum'=>1));
            $count++;
          }
      
          $DM_CMSSpecials=new genie_CMSSpecials();
          if($count>0)
          {
                return array('results'=>$DM_CMSSpecials->Translate("Ok",$DM_DataBase->getEntityId("DMSysEntities")),'resultsData'=>$resultData);
          }
          else
          {
                return array('results'=>$tableDetails['norowstxt'],'resultsData'=>$resultData);
          }
      }
        
      public function SaveChanges($Models,$formParameters)
      {
          $result="";
           $DM_DataBase= new genie_DataBase();
            $tableName = $DM_DataBase->GetTableName($formParameters[0]['formName']);
            $tables = $DM_DataBase->Select("DMSysEntities",$DM_DataBase->createParamsFromRow(array("tableName"=>$tableName)),false);
            
            if($DM_DataBase->CheckIfNotEmpty($tables))
            {
                $tableDetails=$tables[0];
            
                $data = $Models['data'];
                foreach($data as $row)
                {
                    $setDetails = $DM_DataBase->createParamsFromRow($data,true);
                    $isDestroy = $DM_DataBase->getFieldFromRow($row,"_destroy");
                    if($isDestroy=="true" || $isDestroy==true)
                    {
                        $result=$this->DeleteHelper($row,$tableName,$tableDetails,$result);
                    }
                    else
                    {
                        $id =  $DM_DataBase->getIdFromRow($row,true);
                        $updateParameters = $DM_DataBase->createParamsFromRow($row,true);
                        
                        if($id>0)
                        {
                            
                             $changedArr =$DM_DataBase->checkIfChanged($row,$tableDetails["id"]);
                               $changed=$changedArr['result'];
                               $errorMessage = $changedArr['errorMessage'];
                               if($changed)
                            {
                                $result = $this->SaveHelper($row,$tableName,$tableDetails,$updateParameters,$result);
                                 
                            }
                        }
                        else
                        {
                           $result=$this->AddHelper($row,$tableName,$tableDetails,$updateParameters,$result); 
                        }
                    }
                }
            }
          return $result; 
      }
 

       public function CreateMetaBox()
      {
            $DM_DataBase = new genie_DataBase();
            $DM_CMSSpecials = new genie_CMSSpecials();
            $result="";
             $whereArray=array();
            if(!$this->developing)
            {
                $whereArray = $DM_DataBase->createParamsFromRow(array("isSystem"=>0)) ;
            }
            
            
            $forms = $DM_DataBase->Select("DMSysForms",$whereArray,false);
            
            $inputName ="DMFormIdForms";
            $inputId = "DMFormIdForms";
            if($DM_DataBase->CheckIfNotEmpty($forms))
            {
                $result="<label for='".$inputId."'>".$DM_CMSSpecials->translate( 'Display Name',$DM_DataBase->getEntityId("DMSysForms") ) ."</label> <select name='".$inputName."' id='".$inputId."'>";        
                foreach($forms as $form)
                {
                   $result.=" <option  value=".$form['id'].">".$form['displayName']."</option>"; 
                }
                 $result.=" </select>";     
             
                 $result.= "<a href='#' onclick='DM_AddFormToPost(\"".$inputId."\")'>".$DM_CMSSpecials->translate( 'Add To Post',$DM_DataBase->getEntityId("DMSysForms") )."</a>";
            } 
            
             return $result;   
            
      }
      
      //TODO: check that : Apply filter and query is not working
      public function GetDataByIdNew($formParameters,$url,$encoded=true,$filter=null,$modelName=null)
      {
          if(isset($formParameters['formId']))
          {
              $type = 'group';
              if(isset($formParameters['type']))
              {
                  $type = $formParameters['type'];
              }
              $models=array();
              
              $models = $this->GetFilteredDataById($formParameters,$url,$filter,$modelName);
              if($encoded)
              {
                return json_encode($models);
              }
              else
               return array('results'=>'ok','resultsData'=>$models);
          }
      }
      
      public function GetDefinitions($formDetails)
      {
           $DM_DataBase= new genie_DataBase();
           $tableParams =$DM_DataBase->createParamsFromRow(array('id'=>$formDetails['id']));
             $tableDetails = $DM_DataBase->Select('DMSysEntities',$tableParams);
              $definitions =   $DM_DataBase->getDefinitions($tableDetails[0]['tableName']);
              return $definitions;
      }
      
       //use in get Models and search
       public function GetFilteredDataById($formParameters,$url,$filter=null,$modelName=null)
      {
         
           if(isset($formParameters['formId']))
          {
              $id =  $formParameters['formId'];
              $modelsArray = $this->GetFilterDataByIdHelper($id,$url,$filter,$modelName);
               $models=$modelsArray['models'];
               $loaded=$modelsArray['loaded'];
          }
          return $models;
      }
      
      private function GetFilterDataByIdHelper($id,$url,$filter=null,$modelName=null)
      {
           $models = array();
          $loaded = array();
          $DM_DataBase = new genie_DataBase();
          $connectedEntities=array();
          $connections=array();
          $forms=array();
          $DM_CMS= new genie_CMSSpecials();
          $cachedName = "Forms_id_".$id;
          $thisForms=$DM_CMS->getDataFromCache($cachedName);
          if($thisForms==null)
          {
           $whereParameters = $DM_DataBase->createDefinitionsFromRow("DMSysForms",array("id"=>$id));
              $thisForms = $DM_DataBase->Select("DMSysForms",$whereParameters,false); 
              $DM_CMS->saveDataToCache($cachedName,$thisForms);
          }
              if($DM_DataBase->CheckIfNotEmpty($thisForms))
              {
                  $formParamsRow = $thisForms[0];
                     if($formParamsRow['type']=="scheme")
                     {
                         $cachedName = "Form_list_id_".$id;
                          $formList=$DM_CMS->getDataFromCache($cachedName);
                          if($formList==null)
                          {
                             $formList = $this->CreateFormsList($formParamsRow['formHtml']);
                              $DM_CMS->saveDataToCache($cachedName,$formList);
                          }
                         $forms = $formList['included'];
                         $connections = $formList['connections'];
                         $connectedEntities = $formList['connectedEntities'];
                         foreach($forms as $form)
                         {
                             $modelsArray = $this->getFilteredDataByFormDetails($form,$filter,$models,$connections,$connectedEntities,$forms,$loaded,$url,$modelName);
                             $models=$modelsArray['models'];
                             $loaded=$modelsArray['loaded'];
                         }
                     }
                     else
                     { 
                          $modelsArray =  $this->getFilteredDataByFormDetails($formParamsRow,$filter,$models,$connections,$connectedEntities,$forms,$loaded,$url,$modelName);           
                          $models=$modelsArray['models'];
                          $loaded=$modelsArray['loaded'];
                     }
              }
              return $modelsArray; 
      }
      
      private function GetConnectedToFormDetails($id)
      {
          $connected=null;
           $DM_DataBase = new genie_DataBase();
           $whereParameters = $DM_DataBase->createDefinitionsFromRow("DMSysForms",array("id"=>$id));
               $connectedForms = $DM_DataBase->Select("DMSysForms",$whereParameters,false);
               if($DM_DataBase->CheckIfNotEmpty($connectedForms))
               {
                  $connected = array('parentForm'=>$connectedForms[0],'childForm'=>$form); 
               }
           return $connected;
      }
      
      
      private function GetConnected($form,$formList,$connectedEntities)
      {
          $connected=array();
          $DM_DataBase = new genie_DataBase();
          if($DM_DataBase->CheckIfNotEmpty($connectedEntities))
          {
            foreach($connectedEntities as $entity)
            {
                if($entity['parentEntity']==$form['entity_id'])
                {
                   foreach($formList as $subForm)
                   {
                       if($entity['childEntity']==$subForm['entity_id'] && ($subForm['type']=="newOne" || $subForm['type']=="filterRow"))
                       {
                           $add=true;
                           foreach($connected as $connection)
                           {
                               if(($connection['modelname']=='entity_id_'.$subForm['entity_id'] && $connection['parentField']=="field_".$entity['parentField'] && $connection['childField']=="field_".$entity['childField'] && $connection['type']==$subForm['type']))
                                {
                                  $add=false;  
                                }
                           }
                           if($add)
                           {
                               array_push($connected,array('modelname'=>'entity_id_'.$subForm['entity_id'],'parentField'=>"field_".$entity['parentField'],'childField'=>"field_".$entity['childField'],'type'=>$subForm['type'])); 
                               array_push($connected,array('modelname'=>'entity_id_'.$subForm['entity_id'],'parentField'=>"field_".$entity['parentField'],'childField'=>"field_".$entity['childField'],'type'=>"filterRow")); 
                           }
                       }
                   } 
                }
            }
          }
            return $connected;
      }
      
      private function GetDefaultFilterOrQuery($connectedParent,$DM_DataBase,$defParameter)
      {
          $parentFilter=array();
            //go over parent definitions and create a new filter
            $parentEntityId= $connectedParent['parentForm']['entity_id'];
            $parentTableName = $DM_DataBase->GetTableNameByEntity($parentEntityId);
            $parentDefinitions =   $DM_DataBase->getDefinitions($parentTableName);
            foreach($parentDefinitions as $definition)
            {
                 if(isset($definition[$defParameter]) && trim($definition[$defParameter])!="")
                 {
                     if($definition[$defParameter]!="[@lastValue@]")
                     {
                        $parentFilter["field_".$definition["id"]] = $definition[$defParameter];
                     }
                     else
                     {
                         $lastValue = $DM_DataBase->getLastValue($parentTableName,$definition["fieldName"]);
                        $parentFilter["field_".$definition["id"]] = $lastValue; 
                     }
                     
                 }
            }
            return $parentFilter;
      }
      


      /*TODO: 1. make it more readable
            2.  make it work faster
            3.  create filter and new with correct default values (from filter!)
            */
      public function getFilteredDataByFormDetails($form,$filter,$models,$connections,$connectedEntities,$formList,$loaded,$url,$modelName=null)
      {
          $filterNotEmpty=false;
          //<editor-fold desc="Check if filter empty">
          $DM_FormHelper = new genie_FormHelper();
          $filterNotEmpty = $DM_FormHelper->CheckFilterEmpty($filter);
          //</editor-fold>
          //<editor-fold desc="Initiate vars">
            $DM_CMSSpecials = new genie_CMSSpecials();
          $DM_ModelHelper = new genie_ModelHelper();
            $defWhereParameters=array();
           $formType = $form['type'];
           $formEntity = $form['entity_id'];
           $defaultFilters=false;
           $defaultQuery=false;
           $applyFilters=false;
            $parentEntity= "";
            $parentField ="0";
            $childField = "0";
            $parentTableName = "";
          //</editor-fold>

          //<editor-fold desc="Check if current entity already in $loaded: $toLoad,$groupLoaded ">
            $loading=$DM_ModelHelper->checkLoaded($loaded,$formType,$formEntity,$modelName);
            $toLoad = $loading['toLoad'];
            $groupLoaded=$loading['groupLoaded'];
            //</editor-fold>

            if($toLoad)
            {
                $DM_DataBase = new genie_DataBase();
                //<editor-fold desc="Load Definitions">
                $entityId= $form['entity_id'];
                  $cachedName = "Definitions_id_".$entityId;

                  $Definitions=$DM_CMSSpecials->getDataFromCache($cachedName);
                  if($Definitions==null)
                  {
                        $whereParams = $DM_DataBase->createParamsFromRow(array("entity_id"=>$entityId),false);
				/*TODO:Split definitions*/
                        $Definitions = $DM_DataBase->Select("DMSysDefinitions",$whereParams,false);
                        $DM_CMSSpecials->saveDataToCache($cachedName,$Definitions);
                  }
                  //</editor-fold>

                //<editor-fold desc="Initiate Definition class, validators, formulas">
                $DM_Definition = new genie_Definition();
                $validators = array(); 
                $formulas=array();
                //</editor-fold>

                //<editor-fold desc="Load Table Details">
                $cachedName = "table_details_".$form["entity_id"];
                  $tableDetails=$DM_CMSSpecials->getDataFromCache($cachedName);
                  if($tableDetails==null)
                  {    
                    $tableDetails = $DM_DataBase->GetTableDetails("entity_id_".$form["entity_id"]);
                    $DM_CMSSpecials->saveDataToCache($cachedName,$tableDetails);
                  }
                  //</editor-fold>

                //<editor-fold desc="Initiate tablename, empty parent entity and form">
                $tableName = $tableDetails["tableName"];
                $parentEntity=null;
                $parentForm=null;
                //</editor-fold>

                $connected=$this->GetConnected($form,$formList,$connectedEntities);

                 if(!$groupLoaded)
                 {
                        if($connectedEntities!=null)
                        {
                            //<editor-fold desc="Check if connected and initiate parent">
                            foreach($connectedEntities as $entity)
                            {
                                if($entity["childEntity"]==$formEntity)
                                {
                                    $parentEntity= $entity['parentEntity'];
                                    $parentField = $entity['parentField'];
                                    $childField = $entity['childField'];
                                    $parentTableName = $DM_DataBase->GetTableNameByEntity($parentEntity);
                                }
                            }
                            //</editor-fold>
                            //<editor-fold desc="Check if parent entity is a filter and initiate parent form">
                            if($parentEntity!=null && $formList!=null)
                            {
                               foreach($formList as $formSub)
                               {
                                   if($formSub['entity_id']==$parentEntity && $formSub['type']=='filterRow')
                                   {
                                       $parentForm = $formSub;
                                   }
                               } 
                            }
                            //</editor-fold>
                        }
                 }

               $thisRow=array();   
                 foreach($Definitions as $definition)
                {
                    //<editor-fold desc="Load default values to $thisRow by $definition ">
                        $thisFieldDefValue= $definition["defaultValue"];
                        /*
                        if($definition["type"]=="boolean" && $definition["value"]!="1" && $definition["value"]!=1 && $definition["value"]!=true)
                        {
                            $thisFieldDefValue=false;
                        }
                          */

                       $thisFieldDefValue= $DM_CMSSpecials->GetDefaultFromValue($thisFieldDefValue,$definition["id"],$url);
                       
                       $thisRow['field_'.$definition['id']] = $thisFieldDefValue;
                    //<editor-fold desc=" if fom type is new one and has filter load filter values">
                        if($formType=="newOne" && $filter!=null && $filter['name']=='entity_id_'.$formEntity)
                        {
                             foreach($filter['data'] as $key=>$value)
                             {
                                 if("field_".$definition["id"]==$key && isset($value) && trim($value)!="")
                                 {
                                      $thisRow['field_'.$definition['id']] = $value; 
                                 }
                             }
                        }
                  //</editor-fold>


                    //<editor-fold desc=" if fom type is new one and has parent load parent values according to connection">
                        if(isset($parentEntity) && $formType=="newOne" && $filter!=null && $filter['name']=='entity_id_'.$parentEntity)
                        {
                             foreach($filter['data'] as $key=>$value)
                             {
                                 if("field_".$definition["id"]==$childField && $key==$parentField && isset($value) && trim($value)!="")
                                 {
                                      $thisRow['field_'.$definition['id']] = $value; 
                                 }
                             }
                        }
                        //</editor-fold>
                       //</editor-fold>

                    //<editor-fold desc="Load $validators and $formulas by $definition">
                       $validators = $DM_Definition->AddValidators($definition,$validators,$tableDetails,$form);
                       $formulas= $DM_Definition->AddFieldFormulas($Definitions,$definition,$formulas);
                    //</editor-fold>

                    //<editor-fold desc="Load $models by $definition">
                        $defValue=null;
                        $resArray=$this->DefineModelsByDefinition($definition,$DM_DataBase,$models,'entity_id_'.$entityId);
                         $definition=$resArray['definition'];
                         $models=$resArray['models'];
                         //</editor-fold>

                    //<editor-fold desc="load $defWhereParameters by getLastValue or $definition['defaultFilterValue']">
                        if( ($form['applyFilter'] && $filter==null))
                        { 
                           if($definition["defaultFilterValue"]=="[@lastValue@]")
                           {
                               $defValue = $DM_DataBase->getLastValue($tableName,$definition["fieldName"]);
                               
                           }
                           else
                           {
                               $defValue =  $definition["defaultFilterValue"] ;
                           }
                            array_push($defWhereParameters,array("field_".$definition["id"]=>$defValue));    
                               
                        }
                        //</editor-fold>
                }

                //<editor-fold desc="initiate $thisRow by $filterRow: current or as a parent filter">
                if($filter!=null && $filterNotEmpty)
                 {
                     $filterRow=$filter['data'];
                      if(isset($childField) && isset($parentField) && $filter['name']=='entity_id_'.$parentEntity)
                      {
                          if(isset($thisRow["field_".$childField])&& isset($filterRow["field_".$parentField]))
                          {
                            $thisRow["field_".$childField]=$filterRow["field_".$parentField];
                          }
                      }

                      if($filter['name']=='entity_id_'.$formEntity)
                      {
                          $filterRow=$filter['data'];   
                          foreach($filterRow as $key=>$value)
                          {
                                foreach($thisRow as $thisKey=>$thisValue)
                                {
                                    if($key==$thisKey && trim($value)!="")
                                    {
                                        $thisRow[$key]=$value;
                                    }
                                } 
                          }
                          $defaultFilters = $filter['data']; 
                          $applyFilters=true;
                          $defaultQuery=true;
                      }
                 }
                 //</editor-fold>
        
                 if(!$groupLoaded)
                 {
                        $applyFilters=false;
                        $defaultQuery=false;

                     //<editor-fold desc="if this form is not group, get form id by formList and entity id: $form_id ">
                        $form_id= $form["id"];
                        if($form['type']!="group")
                        {
                            foreach ($formList as $formI)
                            {
                                if($form["entity_id"]==$formI["entity_id"] && $formI["type"]=="group")
                                {
                                    $form_id=$formI["id"];
                                }
                            }
                        }
                        //</editor-fold>


                     //<editor-fold desc=" get $defWhereParameters by $filter">
                       if($filter!=null && $filterNotEmpty)
                       { 
                           if(!isset($childField) || !isset($parentField))
                           {
                               $childField="0";$parentField="0";
                           }
                            $res=$DM_ModelHelper->AddDefQueryByFilter($filter,$formEntity,$childField,$parentField,$parentEntity,$defWhereParameters,$defaultFilters,$applyFilters,$defaultQuery);
                            $defaultFilters = $res['defaultFilters']; 
                            $applyFilters=$res['applyFilters']; 
                            $defaultQuery=$res['defaultQuery']; 
                            $defWhereParameters=$res['defWhereParameters']; 
                       }
                        else
                        {
                             if($form['applyDefaultQuery'] || ($parentForm!=null && $parentForm['applyDefaultQuery']))
                             {
                                 
                                 if(!isset($childField) || !isset($parentField))
                                   {
                                       $childField="0";$parentField="0";
                                   }
                                   $res=$DM_ModelHelper->StartDefaultFilter($DM_DataBase,$form,$parentForm,$parentField,$parentTableName,$childField,$defWhereParameters,$applyFilters,$defaultQuery,$defaultFilters);
                                    $defaultFilters = $res['defaultFilters']; 
                                    $applyFilters=$res['applyFilters']; 
                                    $defaultQuery=$res['defaultQuery']; 
                                    $defWhereParameters=$res['defWhereParameters']; 
                             }
                        }
               //</editor-fold>

                     //TODO: add variable that defines if filter overwrites the default or adding another
                     //<editor-fold desc="Get $DefaultFields  by DMSysDefaultQueryValues of this $form_id">
                     $cachedName = "defaultFields_form_id_".$form_id;
                     $DefaultFields = $DM_CMSSpecials->getDataFromCache($cachedName);
                     if($DefaultFields==null)
                     {
                         $QuerywhereParams = $DM_DataBase->createParamsFromRow( array("form_id"=>$form_id),false);
                         $DefaultFields = $DM_DataBase->Select("DMSysDefaultQueryValues",$QuerywhereParams,false);
                         $DM_CMSSpecials->saveDataToCache($cachedName,$DefaultFields);
                     }
                     //</editor-fold>

                     //<editor-fold desc="push $DefaultFields into $defWhereParameters">
                     if($DM_DataBase->CheckIfNotEmpty($DefaultFields))
                     {
                         foreach($DefaultFields as $field)
                         {
                             $value = $field['value'];
                             $value = $DM_CMSSpecials->GetDefaultFromValue($value,$field["definition_id"],$url);
                             if(trim($value)!="" && trim($value)!="[@queryParam@]")
                             {
                                 array_push($defWhereParameters,array("field_".$field["definition_id"]=>$value));
                             }
                         }
                     }
                     //</editor-fold>

                     //<editor-fold desc="Remove system forms in case is not developer vesion">
                        if(!$this->developing)
                        {
                            $isSystemDef = $DM_DataBase->GetDefinitionByName($tableName,$Definitions,"isSystem");
                            
                            array_push($defWhereParameters,array("field_".$isSystemDef["id"]=>0)); 
                        }
                // </editor-fold>

                     //<editor-fold desc="If user has read permission apply select with defwhereparams,main and secondary order">
                     $whereParams = $DM_DataBase->createParamsFromRows($defWhereParameters,true);
                        if($DM_DataBase->getPermission($tableName,"Read"))
                          {
                              if($DM_DataBase->getPermission($tableName,"Read"))
                              {
                                  $orderParams=array();
                                 if(trim($form["MainOrder"])!="")
                                 {
                                     array_push($orderParams,array("fieldName"=>$form["MainOrder"],"type"=>$form["MainOrderType"]));
                                 } 
                                 if(trim($form["SecondaryOrder"])!="")
                                 {
                                     array_push($orderParams,array("fieldName"=>$form["SecondaryOrder"],"type"=>$form["SecondaryOrderType"]));
                                 }
                                $results = $DM_DataBase->Select($tableName,$whereParams,true,false,$orderParams);
                              }
                          }
                          else
                          {
                              $results=array();
                          }
                        //</editor-fold>

                     //<editor-fold desc="Add current structure to models:(name,type,data,validators,formulas, pagesize...">
                        $type = $form['type'];
                        if($DM_DataBase->CheckIfNotEmpty($results))
                        {
                            if($filter==null)
                            {
                                $groupModel = array('name'=>'entity_id_'.$entityId,'type'=>"group",'data'=>$results,'validators'=>$validators,'formulas'=>$formulas, 'pagesize'=>$tableDetails["groupnumtxt"],'pagenum'=>1,'connected'=>$connected,"applyFilters"=>$applyFilters,'origin'=>$type,'applyDefaultQuery'=>$defaultQuery,'defaultFilters'=>$defaultFilters,'empty'=>false);
                            }
                            else
                            {
                                $groupModel = array('name'=>'entity_id_'.$entityId,'type'=>"group",'data'=>$results,'validators'=>$validators,'formulas'=>$formulas, 'pagesize'=>$tableDetails["groupnumtxt"],'pagenum'=>1,'connected'=>$connected,"applyFilters"=>$applyFilters,'origin'=>$type,'applyDefaultQuery'=>$defaultQuery,'defaultFilters'=>$defaultFilters,'empty'=>false);  
                            }
                        }
                        else
                        {  
                            $groupModel = array('name'=>'entity_id_'.$entityId,'type'=>"group",'data'=>array($thisRow),'validators'=>$validators,'formulas'=>$formulas, 'pagesize'=>$tableDetails["groupnumtxt"],'pagenum'=>1,'connected'=>$connected,"applyFilters"=>$applyFilters,'origin'=>$type,'applyDefaultQuery'=>$defaultQuery,'defaultFilters'=>$defaultFilters,'empty'=>true);
                        }
                       array_push($models,$groupModel);
                        //</editor-fold>

                        array_push($loaded,array('type'=>'group','entity_id'=>$entityId));
                 }

                //<editor-fold desc="if not group - add to loaded $thisRow">
                if($formType!="group" && $formType!=null)
                {
                    $groupModel = array('name'=>'entity_id_'.$entityId,'type'=>$formType,'data'=>array($thisRow),'validators'=>$validators,'formulas'=>$formulas, 'pagesize'=>$tableDetails["groupnumtxt"],'pagenum'=>1,'connected'=>$connected,"applyFilters"=>$applyFilters,'origin'=>$formType,'applyDefaultQuery'=>$defaultQuery,'defaultFilters'=>$defaultFilters);
                    array_push($models,$groupModel); 
                    array_push($loaded,array('type'=>$formType,'entity_id'=>$entityId));       
                }
                //</editor-fold>
           }
           return array('models'=>$models,'loaded'=>$loaded);
      }
     
    
     
      
      private function CreateFormsList($formHtml)
      {   
          $includedForms = array();
          $connections = array();
          $connectedEntities=array();
          $DM_DataBase = new genie_DataBase();
           $forms = $DM_DataBase->Select("DMSysForms",array(),false);    
            if($DM_DataBase->CheckIfNotEmpty($forms))
              {
                  //initiate arrays
                  foreach ($forms as $form)
                  {
                      $tag ="[@form_id_".$form['id']."@]";
                       if($this->checkPosition($formHtml,$tag))
                       {
                           array_push($includedForms,$form);
                           if(isset($form["connectedFormId"])  && trim($form["connectedFormId"])!="")
                           {
                              foreach ($forms as $formParameters)
                              {
                                  if($formParameters["id"]==$form["connectedFormId"])
                                  {
                                      array_push($connections,array('parentEntity_id'=>$formParameters['entity_id'],'childEntity_id'=>$form['entity_id'],'parentForm'=>$formParameters,'childForm'=>$form));
                                      array_push($connectedEntities,array('parentEntity'=>$formParameters["entity_id"],'childEntity'=>$form['entity_id'],'parentField'=>$form["parentField"],'childField'=>$form['childField']));
                                  }
                              }
                           }
                       }
                  }
              }
              foreach($includedForms as &$form)
              {
                 foreach($includedForms as $formHelper)
                 {
                     if($form['entity_id']==$formHelper['entity_id'])
                     {
                        if($formHelper['applyFilter']==1) $form['applyFilter']=true; 
                        if($formHelper['applyDefaultQuery']==1) $form['applyDefaultQuery']=true; 
                        if(isset($formHelper['connectedFormId']) && trim($formHelper['connectedFormId'])!="") $form['connectedFormId'] = $formHelper['connectedFormId'];
                        if(isset($formHelper['parentField']) && trim($formHelper['parentField'])!="") $form['parentField'] = $formHelper['parentField'];
                        if(isset($formHelper['childField']) && trim($formHelper['childField'])!="") $form['childField'] = $formHelper['childField'];
                     }
                 }
              }
              return array('included'=>$includedForms,'connections'=>$connections,'connectedEntities'=>$connectedEntities);                      
      }
       
     
     
      
    
          
        public function DefineModelsByDefinition($definition,$DM_DataBase,$models,$modelName)
        {
            $DM_CMSSpecials = new genie_CMSSpecials();
            $cachedName = "group_Model_".$definition["id"]."_model_".$modelName;
            $groupModel=$DM_CMSSpecials->getDataFromCache($cachedName);
            if($groupModel==null)
            {
                if($definition['input_type']=="dropdownlist" || $definition['input_type']=="radio")
                {
                      if(isset($definition['valuesType']) && $definition['valuesType']=="valuesTable" && isset($definition['valuesTable']) && isset($definition['valuesValueField']) && isset($definition['valuesShowField']))
                        {
                            $whereArray=array();
                            if(!$this->developing)
                            {
                                $TableDetails = $DM_DataBase->GetTableDetailsByTableName($definition['valuesTable']);
                                if($TableDetails["isSystem"])
                                {
                                    $whereArray = $DM_DataBase->createParamsFromRow(array("isSystem"=>0)) ;
                                }
                            }
                            $valueList = $DM_DataBase->Select($definition['valuesTable'],$whereArray,false);
                           
                           if( $definition['IsConnected']==1 && isset($definition['ParentTableSource']) && trim($definition['ParentTableSource'])!=""
                           && isset($definition['ThisFeatureConnector']) && trim($definition['ThisFeatureConnector'])!=""
                           && isset($definition['ParentDataSourceConnector']) && trim($definition['ParentDataSourceConnector'])!=""
                           && isset($definition['ParentFieldName']) && trim($definition['ParentFieldName'])!=""
                           && isset($definition['ParentFieldId']) && trim($definition['ParentFieldId'])!=""
                           )
                           {
                               $defmodel = array();
                               $TableDetails = $DM_DataBase->GetTableDetailsByTableName($definition['ParentTableSource']);
                               $whereArray=array();    
                               if(!$this->developing)
                                {
                                    if($TableDetails["isSystem"])
                                    {
                                        $whereArray = $DM_DataBase->createParamsFromRow(array("isSystem"=>0)) ;
                                    }
                                }
                               $ParentValueList = $DM_DataBase->Select($definition['ParentTableSource'],$whereArray,false);   
                               $defmodel = array();
                               foreach($ParentValueList as $parentRow)
                               {
                                   $foound=false;
                                   if($DM_DataBase->CheckIfNotEmpty($defmodel))
                                    {
                                        $parentFieldName='field_'.$definition['ParentFieldId'];
                                       $parentFieldValue=$parentRow[$definition['ParentFieldName']];
                                       foreach($defmodel as $dval)
                                       {
                                            if($dval["extraValue"]==$parentFieldName  && $dval[$parentFieldName]==$parentFieldValue )
                                            {
                                                $foound=true;
                                            }
                                       }
                                    }
                                   if(!$foound)
                                   {
								/*TODO:Split definitions*/
                                        array_push($defmodel,array('hasExtra'=>true,'extraValue'=>'field_'.$definition['ParentFieldId'], 'field_'.$definition['ParentFieldId']=>$parentRow[$definition['ParentFieldName']],  'fieldValue'=> '','fieldText'=>$DM_CMSSpecials->Translate("Not Selected",$DM_DataBase->getEntityId("DMSysDefinitions")), 'id'=>-1,'parentField'=>-1,'valuesParentFieldId'=>-1));    
                                   }
                                   foreach($valueList as $valueRow)
                                   {
                                           if($parentRow[$definition["ParentDataSourceConnector"]]==$valueRow[$definition["ThisFeatureConnector"]])
                                           {
                                               $parentValue =$parentRow[$definition['ParentFieldName']];
                                               $thisValue=   $valueRow[$definition['valuesValueField']];
                                               $thisName=  $valueRow[$definition['valuesShowField']];
                                                $parentField='field_'.$definition['ParentFieldId'];
                                                $foound=false;
                                                
                                                if($DM_DataBase->CheckIfNotEmpty($defmodel))
                                                {
                                                       foreach($defmodel as $dval)
                                                       {
                                                            if($dval[$parentField]==$parentValue && $dval["fieldValue"]==$thisValue &&  $dval["fieldText"]==$thisName)
                                                            {
                                                                $foound=true;
                                                            }
                                                       }
                                                }
                                               if(!$foound)
                                               {
                                                 array_push($defmodel,array('hasExtra'=>true,'extraValue'=>'field_'.$definition['ParentFieldId'], 'field_'.$definition['ParentFieldId']=>$parentRow[$definition['ParentFieldName']],  'fieldValue'=> $valueRow[$definition['valuesValueField']],'fieldText'=>$valueRow[$definition['valuesShowField']], 'id'=>$valueRow["id"]));    
                                               }
                                           }
                                   }
                               }
                                $groupModel = array('name'=>"field_".$definition["id"]."_model",'forModelname'=>$modelName,'ParentFieldName'=>'field_'.$definition['ParentFieldId'],'type'=>'fieldValues','data'=>$defmodel,'isConnected'=>true,'connectedTo'=>'field_'.$definition['ParentFieldId']);
                                    array_push($models,$groupModel);
                                    $DM_CMSSpecials->saveDataToCache($cachedName,$groupModel);
                               
                           }
                           else
                           {
                                    $defmodel = array();
						/*TODO:Split definitions*/
                                    array_push($defmodel,array('fieldValue'=> '','fieldText'=>$DM_CMSSpecials->Translate("Not Selected",$DM_DataBase->getEntityId("DMSysDefinitions")), 'id'=>-1,'parentField'=>-1,'valuesParentFieldId'=>-1));
                                    foreach($valueList as $valueRow)
                                    {
                                        if(isset($valueRow[$definition['valuesValueField']]))
                                        {   
                                               $thisValue=    $valueRow[$definition['valuesValueField']];
                                               $thisName=  $valueRow[$definition['valuesShowField']];
                                                $foound=false;
                                                
                                                if($DM_DataBase->CheckIfNotEmpty($defmodel))
                                                {
                                                       foreach($defmodel as $dval)
                                                       {
                                                            if( $dval["fieldValue"]==$thisValue &&  $dval["fieldText"]==$thisName)
                                                            {
                                                                $foound=true;
                                                            }
                                                       }
                                                }
                                               if(!$foound)
                                               {
                                                    array_push($defmodel,array('fieldValue'=> $valueRow[$definition['valuesValueField']],'fieldText'=>$valueRow[$definition['valuesShowField']], 'id'=>$valueRow["id"]));
                                               }
                                        }
                                    }
                                    $groupModel = array('name'=>"field_".$definition["id"]."_model",'type'=>'fieldValues','data'=>$defmodel);
                                    array_push($models,$groupModel);
                                    $DM_CMSSpecials->saveDataToCache($cachedName,$groupModel);
                           }
                        }
                        else
                        {
                            $defmodel = array();
					/*TODO:Split definitions*/
                            array_push($defmodel,array('fieldValue'=> '','fieldText'=>$DM_CMSSpecials->Translate("Not Selected",$DM_DataBase->getEntityId("DMSysDefinitions")), 'id'=>-1,'parentField'=>-1,'valuesParentFieldId'=>-1));
                            $values = explode (",",$definition['valuesList']);
                            foreach($values as $value )
                            {
                                $show=trim($value);
                                $store=trim($value);
                                if($this->checkPosition($value,"="))
                                {
                                    $valueArr=explode  ("=",$value);
                                    if(count($valueArr)>1)
                                    {
                                        $show=trim($valueArr[0]);
                                        $store=trim($valueArr[1]);
                                    }
                                }
                                array_push($defmodel, array('fieldValue'=>$store,'fieldText'=>$show,"id"=>0,'parentField'=>-1,'valuesParentFieldId'=>-1));
                            }
                            $groupModel = array('name'=>"field_".$definition["id"]."_model",'type'=>'fieldValues','data'=>$defmodel);
                            array_push($models,$groupModel);
                            $DM_CMSSpecials->saveDataToCache($cachedName,$groupModel); 
                        }  
                 }
            }
            else
            {
               array_push($models,$groupModel);   
            }
             return array('definition'=>$definition,'models'=>$models);
        }
      
        public function getFormHtmlByContent($pattern,$entityId=0)
      {
          $DM_GeneralUsage= new genie_GeneralUsage();
          $pattern = $DM_GeneralUsage->translateAll($pattern,$entityId);
             $DM_DataBase = new genie_DataBase();
              
             $existingForms =  $DM_DataBase->Select("DMSysForms",array(),false);
             foreach($existingForms as $form)
             {
                 if(isset($form["entity_id"]))
                 {
                     $tag ="[@form_id_".$form['id']."@]";
                       if($this->checkPosition($pattern,$tag))
                       {
                           $resultHtml = $this->getFormHtmlById($form['id']);
                           $pattern=str_replace($tag,$resultHtml,$pattern);   
                       }
                       
                      
                 }
             }
             $dir = plugin_dir_url(dirname(__FILE__));
             $dirImages=$dir."images"; 
             $pattern=$this->ReplaceTagAll("[@Images@]",$dirImages,$pattern); 
             return $pattern;
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
      
      public function getFormHtmlById($id, $sub=false,$AddGroup=true)
      {
          $pattern="";
             $DM_DataBase = new genie_DataBase();
             $row=array("id"=>&$id);
             $whereParameters = $DM_DataBase->createDefinitionsFromRow("DMSysForms",$row);
             $formParameters = $DM_DataBase->Select("DMSysForms",$whereParameters,false);
             //TODO: Add read model
             if($DM_DataBase->CheckIfNotEmpty($formParameters))
             {
                 $formParamsRow = $formParameters[0];
                 if($formParamsRow['type']=="scheme")
                 {
                     $pattern = $formParamsRow['formHtml'];
                     if($formParamsRow['multilanguge'])
                     {
                         $DM_GeneralUsage = new genie_GeneralUsage();
                        $pattern = $DM_GeneralUsage->translateAll($pattern,$formParamsRow["entity_id"]);
                        
                     }
                     $existingForms =  $DM_DataBase->Select("DMSysForms",array(),false);
                     $resArr = $this->markGroupFormsToReturn($existingForms,$pattern);  
                     $groupsToAdd=$resArr['groupsToAdd'];
                       $existingForms= $resArr['existingForms'];
                       $pattern= $resArr['pattern'];          
                     foreach($existingForms as $form)
                     {
                         $AddGroup = true;
                         if(isset($groupsToAdd[$form['formName']]))
                         {
                             $AddGroup = $groupsToAdd[$form['formName']];
                         }
                         
                         $tag ="[@form_id_".$form['id']."@]";
                           if($this->checkPosition($pattern,$tag))
                           {
                               $resultHtml = $this->getFormHtmlById($form['id'],true,$AddGroup);
                               $pattern=str_replace($tag,$resultHtml,$pattern);   
                           }
                           
                        
                     }
                 }
                 else
                 {
                    $pattern = $this->getFormHtmlHelper($formParamsRow,true,$AddGroup);   
                 }
             }
             $formSub ="";
             if($sub)
             {
                 $formSub="_sub";
             }
             $DM_Template = new genie_Template();
             $result =$DM_Template->getFormDiv($id,$formSub,$pattern);
            
             return $result;
      }
        
      
     
      
      
      
      
        public function GetHtml($formIdentifier) 
      {
          
          $DM_DataBase = new genie_DataBase();
          $forms = $DM_DataBase->Select("DMSysForms",array(),false,false);
          if($DM_DataBase->CheckIfNotEmpty($forms))
          {
              foreach($forms as $form)
              {
                 $tag ="[@form_id_".$form['id']."@]";
                       if($this->checkPosition($formIdentifier,$tag))
                       {
                           $resultHtml = $this->getFormHtmlById($form['id']);
                           return $resultHtml;
                       }
              }
                       
          }
         return $this->GetHtmlFromFile($formIdentifier);

      }
      
    
           
      public function GetHtmlFromFile($formIdentifier)
      {     $DM_DataBase=new genie_DataBase();
           $dir =  dirname(dirname(__FILE__)) ;
         if(file_exists($dir."/files/form_".$formIdentifier.".html"))
         {
                 $DM_GeneralUsage = new genie_GeneralUsage();
               $html = $DM_GeneralUsage->getFileContent($dir."/files/form_".$formIdentifier.".html");
                $strroot ='$root';
                 
                 $pos = strrpos($html,"[@root@]");
                 while($pos)
                 { 
                    $html = str_replace("[@root@]",$strroot,$html);
                     $pos = strrpos($html,"[@root@]");
                 }
                  
                 $html = $this->getFormHtmlByContent($html,$DM_DataBase->getEntityId("DMSysEntities"));
                 $DM_GeneralUsage= new genie_GeneralUsage();
                 $html = $DM_GeneralUsage->translateAll($html,$DM_DataBase->getEntityId("DMSysEntities"));
                 $html = $this->ReplaceHelpTags($html,$formIdentifier);
                return $html;
         }
         else
         {
             $DM_CMSSpecials = new genie_CMSSpecials();
             return $DM_CMSSpecials->Translate("No Such form",$DM_DataBase->getEntityId("DMSysEntities"));
         }     
      }
  
      private function ReplaceHelpTags($html,$formIdentifier)
      {
          $posoptions = array();
          array_push($posoptions,array('tag'=>"[@DM_GetTables@]",'formName'=>"Wizards"));
          array_push($posoptions,array('tag'=>"[@DM_GetSystemTables@]",'formName'=>"Wizards"));
          array_push($posoptions,array('tag'=>"[@DM_GetEntitiesForTemplate@]",'formName'=>"TemplateManagement"));
          foreach($posoptions as $posOption)
          {
                  $tag =$posOption["tag"];
                 $formName=$posOption["formName"];
                 if($this->checkPosition($html,$tag) && $formName==$formIdentifier)
                 {
                      $DM_Filters=new genie_Filters();
                      $tagReplace = $DM_Filters->getCodeTranslate($tag,array());
                      $html = str_replace($tag,$tagReplace,$html) ;
                 }
          }
         return $html;
      }
   
      public function getFormHtmlHelper($formDetails,$sub=false,$AddGroup=true)
      {
          $DM_GeneralUsage = new genie_GeneralUsage();
           $DM_DataBase = new genie_DataBase();
           $DM_CMSSpecials = new genie_CMSSpecials();
           
           $titles="";  
           $groupButtonSave="";                                                                    
           $strroot ='$root';
           $strparent ='$parent';
           $dir = plugin_dir_url(dirname(__FILE__));
           $dirImages=$dir."images";
           $type=   $formDetails["type"];
           $formName   = $formDetails['formName'];
           $tableDetails = $DM_DataBase->GetTableDetails($formName);
           $entityId = $tableDetails['id'];
           $formDefinitions = $DM_DataBase->getDefinitions($tableDetails['tableName']);
           $fieldIdId =$DM_DataBase->getIdField($entityId);
           $formHtml = $formDetails['formHtml']; 
           $exerptHtml = $formDetails['exerptHtml'] ;
           $exerptTitle = $formDetails['exerptHtml'] ;
           $IndexDesign=$formDetails['IndexDesign'] ;
           $isExtended=  $formDetails['isExtended'];
           $multilanguage=  $formDetails['multilanguge'];
           $mappingType = $DM_GeneralUsage->GetTypeMapping($type);
           $modelName =  "entity_id_".$entityId.$mappingType['formSufix'];
           $MyPages="";
           $isOnPage="";
           
           $resArr = $this->ReplaceDefinitions($formDefinitions,$formHtml,$exerptHtml,$exerptTitle,$isExtended,$modelName,$strroot,$multilanguage,$formDetails);
            $formDefinitions=$resArr['formDefinitions'];
            $formHtml=$resArr['formHtml'];
            $exerptHtml=$resArr['exerptHtml'];  
            $exerptTitle=$resArr['exerptTitle']; 
           $resArr= $this->AddMainButtonAndPages($mappingType,$type,$entityId,$formHtml,$MyPages,$formDetails);
           $MyPages=$resArr['MyPages']; 
           $formHtml=$resArr['formHtml']; 
           $resArr=$this->GroupSpecialize($type,$isOnPage,$entityId,$DM_CMSSpecials,$formDetails,$groupButtonSave);
            $groupButtonSave=$resArr['groupButtonSave'];
            $isOnPage=$resArr['isOnPage'];
            
           if ($isExtended)
           {
               $titles =   $exerptTitle;
               $resArr=$this->AddButtonsToGroupHtml($exerptHtml,$titles,$entityId,$formHtml,$formDetails);  
                $formHtml=$resArr['formHtml'];
                $exerptHtml=$resArr['exerptHtml'];                  
                $titles=$resArr['titles'];                  
           }
           if($formDetails["showType"]=="showAsButton")
           {
                $formHtml=$this->ShortToShowAsButton($formHtml,$strroot,$formDetails,$dirImages,$multilanguage);
                
           }  
           $IndexDesign="";
           if($formDetails["AddIndexCode"]==1 || $formDetails["AddIndexCode"]=="1" || $formDetails["AddIndexCode"])
           {
                $IndexDesign = $this->CreateIndex($formDetails);
           }                        
          $resArr=$this->DesignForm($formHtml,$isOnPage,$mappingType,$entityId,$groupButtonSave,$MyPages,$modelName,$titles,$fieldIdId,$formDetails,$IndexDesign);
          $formHtml=$resArr['formHtml'];
          $groupButtonSave=$resArr['groupButtonSave'];                  
                                  
          $formHtml=$this->AddGroupView($type,$AddGroup,$formName,$formHtml,$sub);
          
          if($multilanguage)
          {
            $formHtml=$DM_GeneralUsage->translateAll($formHtml,$entityId);
          }
          
          return $formHtml;              
      }
      
    
    private function CreateIndex($formDetails)
    {
        $index="";
        $DM_CMSSpecials=new genie_CMSSpecials();
        $DM_Database = new genie_DataBase();
         $defQueryTable = $DM_CMSSpecials->GetTableName("DMSysDefaultQueryValues"); 
         $Query = " select * from ".$defQueryTable." where value like '%[@queryParam@]%' and form_id=".$formDetails["id"];
         $DefQueryRows = $DM_CMSSpecials->getResults($Query);
         if($DM_Database->CheckIfNotEmpty($DefQueryRows))
         {
             
             foreach($DefQueryRows as $defq)
             {
                $tableDetails= $DM_Database->GetTableDetailsByEntity($formDetails["entity_id"]);
                $tableName=$DM_CMSSpecials->GetTableName( $tableDetails["tableName"]);
                $definitionDetails=$DM_Database->getDefinitionById($defq["definition_id"]);
                if($definitionDetails!=null)
                {
                    $fieldName=$definitionDetails["fieldName"];
                    $indexQuery="select ".$fieldName." from ".$tableName." group by ".$fieldName;
                    $PossibleValues = $DM_CMSSpecials->getResults($indexQuery);
                    if($DM_Database->CheckIfNotEmpty($PossibleValues))
                    {
                        
                        foreach($PossibleValues as $posValue)
                        {
                            $pattern = $formDetails["IndexDesign"];
                            $parameter  =  str_replace("[@queryParam@]","",$defq["value"]);  
                              $tagUrl =  "[@IndexParameterUrl@]";
                              $replacementUrl = "?field_id_".$defq["definition_id"]."=".$posValue[$fieldName];
                              $pattern =  str_replace($tagUrl,$replacementUrl,$pattern);  
                              
                               $tagName =  "[@IndexParameterName@]";
                              $replacementName = $posValue[$fieldName];
                              $pattern =  str_replace($tagName,$replacementName,$pattern);
                              
                              
                                $tagLink =  "[@IndexParameterLink@]";
                              $replacementLink = "<a href='".$replacementUrl."'>".$replacementName."</a>";
                              $pattern =  str_replace($tagLink,$replacementLink,$pattern);  
                              
                              $index.=$pattern;
                        }
                    }
                }
                
             }
             
           if(trim($index)!="")
           {
               $index="<div class='dm_index'>".$index."</div>";
           }  
         }
         return $index;
    }
    
    
    
   /*** HELP Functions fro HTML HELPER ***/ 

       private function GroupSpecialize($type,$isOnPage,$entityId,$DM_CMSSpecials,$formDetails,$groupButtonSave)
       {
           
           if($type=="group")
          {
              $isOnPage=" data-bind='visible:isOnPage()'" ;
              if($formDetails["showSaveAllButton"] && !$formDetails["readOnly"])
              {
                  $DM_Template = new genie_Template();
                $groupButtonSave =$DM_Template->groupButtonSave($entityId,$formDetails,$DM_CMSSpecials);
              }
          }
          $resArr=array();
          $resArr['groupButtonSave']=$groupButtonSave;
            $resArr['isOnPage']=$isOnPage;
            return $resArr;
       }  
      
      private function markGroupFormsToReturn($existingForms,$pattern)
      {
           $groupsToAdd = array();

                     
                     foreach($existingForms as &$form)
                     {
                          $tag ="[@form_id_".$form['id']."@]"; 
                           $tag2 = "[@form_entity_id_".$form["entity_id"]."_".$form['type']."@]";
                         $type = $form['type'];
                          if(($this->checkPosition($pattern,$tag) || $this->checkPosition($pattern,$tag2) ) && ($type=="filterRow" || $type=="new"|| $type=="search")    )
                          {
                              $AddToGroup = true;
                              
                               foreach($existingForms as &$form2)
                               {
                                    $tag ="[@form_id_".$form2['id']."@]"; 
                                     $tag2 = "[@form_entity_id_".$form2["entity_id"]."_".$form2['type']."@]";
                                   if($form2['type']=='group' && ($form2["entity_id"]==$form["entity_id"]) && ($this->checkPosition($pattern,$tag) || $this->checkPosition($pattern,$tag2) ))
                                   {
                                      
                                       $AddToGroup = false; 
                                       if($form["applyDefaultQuery"]==true)
                                       {
                                           $form2['applyDefaultQuery']=true;
                                       }  
                                   }
                                   
                                   
                               }
                              $groupsToAdd["entity_id_".$form["entity_id"]] =$AddToGroup;  
                          }
                     }    
                     return array('groupsToAdd'=> $groupsToAdd,'existingForms'=>$existingForms,'pattern'=>$pattern);
      }
      
      
     
     
      private function ReplaceDefinitions($formDefinitions,$formHtml,$exerptHtml,$exerptTitle,$isExtended,$modelName,$strroot,$multilanguage,$formDetails)
      {
          $DM_Definition =  new genie_Definition();
           foreach($formDefinitions as &$definition)
           {
              
              if($formDetails["readOnly"])
              {
                  $value=$DM_Definition->GetFieldNameValueReadOnly($definition);
              }
              else
              { 
                    $value=$DM_Definition->GetFieldNameValueForFormHtml($definition,$multilanguage);
              }
               $label = $DM_Definition->GetFieldNameLabelForFormHtml($definition,$multilanguage);
               $fieldValue =   $DM_Definition->GetFormattedFieldValue($definition);
               $fieldCode =   $DM_Definition->GetFormattedFieldCode($definition);
               
               $formHtml = str_replace("[@field_id_".$definition["id"]."@]",$value,$formHtml);
               $formHtml = str_replace("[@label_id_".$definition["id"]."@]",$label,$formHtml);
               
                $formHtml = str_replace("[@field_id_".$definition["id"]."_readOnly@]",$fieldValue,$formHtml);
                $formHtml = str_replace("[@field_id_".$definition["id"]."_code@]",$fieldCode,$formHtml);
               
               if  ($isExtended)
               {
                   $valueReadOnly=$DM_Definition->GetFieldNameValueReadOnly($definition);
                   $labelReadOnlyText=$DM_Definition->GetFieldNameLabelReadOnly($definition,$multilanguage);
                    $DM_Template=new genie_Template();
                   $labelReadOnly = $DM_Template->labelReadOnly($strroot,$modelName,$definition,$labelReadOnlyText);
                 //   $labelReadOnly = $genie_Definition->GetFieldNameLabelReadOnly($definition);
                   $exerptHtml = str_replace("[@field_id_".$definition["id"]."@]",$valueReadOnly,$exerptHtml);
                   $exerptTitle = str_replace("[@field_id_".$definition["id"]."@]",$labelReadOnly,$exerptTitle);
               }
           }
           $resArr=array();
            $resArr['formDefinitions']=$formDefinitions;
            $resArr['formHtml']=$formHtml;
            $resArr['exerptHtml']=$exerptHtml;
            $resArr['exerptTitle']=$exerptTitle;
            return $resArr;
      }
      
      private function AddButtonsToGroupHtml($exerptHtml,$titles,$entityId,$formHtml,$formDetails)
      {       
          $DM_CMSSpecials = new genie_CMSSpecials();
           $dir = plugin_dir_url(dirname(__FILE__));
               $dirImages=$dir."images";
                 $strroot ='$root';    
                 $DM_Template = new genie_Template();
                 if($formDetails['readOnly']=="1")
                 {
                     $editImage=$DM_Template->editImageShowMore($strroot,$DM_CMSSpecials,$formDetails,$dirImages);;
                     $deleteImage="";
                     $addToPageImage="";
                     
                     
                 }
                 else
                 {
                       
                          $deleteImage = $DM_Template->deleteImage($strroot,$entityId,$dirImages,$DM_CMSSpecials,$formDetails);
                          $addToPageImage =$DM_Template->addToPageImage($strroot,$DM_CMSSpecials,$formDetails,$dirImages);
                          $editImage=$DM_Template->editImageEdit($strroot,$DM_CMSSpecials,$formDetails,$dirImages);
                 }
                 
                 
              
              if($this->checkPosition($exerptHtml,"[@formButton@]"))
              {
                  $exerptHtml = str_replace("[@formButton@]",$editImage,$exerptHtml);  
                  $exerptHtml = str_replace("[@deleteButton@]",$deleteImage,$exerptHtml);
                   $exerptHtml = str_replace("[@addToPageButton@]",$addToPageImage,$exerptHtml);
                  $titles = str_replace("[@formButton@]",'',$titles);  
                  $titles = str_replace("[@deleteButton@]",'',$titles);      
                  $titles = str_replace("[@addToPageButton@]",'',$titles);  
              }
              else
              {
                  $exerptHtml.=$editImage;
              }
              
              
              $formHtml =  $DM_Template->formHtml($exerptHtml,$strroot,$dirImages,$formHtml);
                   
                  $resArr=array();
               $resArr['formHtml']=$formHtml;
                $resArr['exerptHtml']=$exerptHtml;
                $resArr['titles']=$titles;
                return $resArr;
      }
      
      
      private function AddGroupView($type,$AddGroup,$formName,$formHtml,$sub)
      {
          $DM_DataBase = new genie_DataBase();
          if(($type=="filterRow" || $type=="new"|| $type=="search") && $AddGroup)
         {
             $whereArray =$DM_DataBase->createParamsFromRow( array("formName"=>$formName,"type"=>"group"));
               $formDetails = $DM_DataBase->Select("DMSysForms",$whereArray,false);
               if(count($formDetails)>=1 && isset($formDetails[0]['formHtml']))
               {
                   $groupHtml = $this->getFormHtmlHelper($formDetails[0],$sub);
                    $formHtml.=$groupHtml;    
               }
            
            
         }     
         return $formHtml;
      }
      
       public function ReplaceTagAll($tag,$replacement,$pattern)
      {
          while($this->checkPosition($pattern,$tag))
          {
              $pattern =  str_replace($tag,$replacement,$pattern);
          }
          return $pattern;
      }
      
      private function AddMainButtonAndPages($mappingType,$type,$entityId,$formHtml,$MyPages,$formDetails)
      {
             $strroot ='$root';
          $button="";
          $deleteButton="";
          $DM_CMSSpecials = new genie_CMSSpecials();
          $DM_Template = new genie_Template();
           if($formDetails["readOnly"]=='1')
               {
                   $button="";
                   $deleteButton="";
               }
               else
               {   
                    $button = $DM_Template->actionButton($mappingType,$entityId,$DM_CMSSpecials,$formDetails);
                    
               }
           if($type=="group")
           {
               if($formDetails["readOnly"]=='1')
               {
                   $button="";
               }
               else
               {     
                   $deleteButton = $DM_Template->groupDeleteButton($mappingType,$entityId,$DM_CMSSpecials,$formDetails);
                    $button = $DM_Template->groupActionButton($mappingType,$entityId,$DM_CMSSpecials,$formDetails);
               }
                //TODO Add special design to Pages
                $MyPages=$DM_Template->Pages($entityId,$strroot,$DM_CMSSpecials,$formDetails) ;
           }
           
           $tag  = "[@formButton@]";
           if(!$this->checkPosition($formHtml,$tag))
           {
               $formHtml.="[@formButton@]";
           }
          
          
           $formHtml = str_replace("[@formButton@]",$button,$formHtml);  
           $formHtml = str_replace("[@deleteButton@]",$deleteButton,$formHtml); 
           //formSufix
           $resetButton ="";
           if($mappingType['formSufix']=="_new" || $mappingType['formSufix']=="_newOne")
           {
             
                $resetButton = $DM_Template->resetButton($strroot,$entityId,$mappingType,$DM_CMSSpecials,$formDetails);
                 $formHtml = str_replace("[@formResetButton@]",$resetButton,$formHtml);  
           }
           $resArr=array();
           $resArr['MyPages']=$MyPages;
           $resArr['formHtml']=$formHtml;
           return $resArr;
      }
      
      private function ShortToShowAsButton($formHtml,$strroot,$formDetails,$dirImages,$multilanguage=false)
      {
          $DM_CMSSpecials = new genie_CMSSpecials();
          $DM_Template = new genie_Template();
            $shortcutText=$formDetails["shortcutText"];
            if($multilanguage)
            {
               $shortcutText = $DM_CMSSpecials->Translate($shortcutText,$formDetails["entity_id"]) ;
            }
           $formHtml = $DM_Template->showAsButtonFormHtml($strroot,$dirImages,$formHtml,$shortcutText);
           return $formHtml; 
      }
      
      private function DesignForm($formHtml,$isOnPage,$mappingType,$entityId,$groupButtonSave,$MyPages,$modelName,$titles,$fieldIdId,$formDetails,$IndexDesign="")
      {
          $strroot ='$root';
           $strparent ='$parent';
            $DM_Temlate= new genie_Template();
              $titles =  $this->ReplaceTagAll("[@fieldIdId@]","\"0\"",$titles); 
               $visibleGroup=$DM_Temlate->visibleGroup($formDetails,$modelName);
             
          
           $formHtml =  $DM_Temlate->formFinalDesign($entityId,$mappingType,$titles,$visibleGroup,$isOnPage,$formHtml,$MyPages,$groupButtonSave,$modelName,$formDetails,$IndexDesign);
           
           
            
            
           if($this->checkPosition($formHtml,"[@errorMessage@]") )
           {
                $isRoot="[@root@].";    
                $errorDiv =$DM_Temlate->errorDiv($isRoot,$entityId,$entityId);
                $formHtml=$this->ReplaceTagAll("[@errorMessage@]",$errorDiv,$formHtml);
                $errorDiv="";
           }
           else
           {
               $isRoot="";     
               $errorDiv =$DM_Temlate->errorDiv($isRoot,$entityId,$entityId);
           }
              
           if($this->checkPosition($formHtml,"[@successMessage@]") )
           {
                $isRoot="[@root@].";    
                  $successDiv = $DM_Temlate->successDiv($isRoot,$entityId);
                $formHtml=$this->ReplaceTagAll("[@successMessage@]",$successDiv,$formHtml);
                 $successDiv = "";
           }
           else
           {
               $isRoot="";    
               $successDiv = $DM_Temlate->successDiv($isRoot,$entityId);
           }   
           
           $formHtml =  $this->ReplaceTagAll("[@ErrorDiv@]",$errorDiv,$formHtml); 
           $formHtml =  $this->ReplaceTagAll("[@SuccessDiv@]",$successDiv,$formHtml); 
          
           $formHtml =  $this->ReplaceTagAll("[@root@]",$strroot,$formHtml);
          $formHtml =  $this->ReplaceTagAll("[@parent@]",$strparent,$formHtml);
          $dir = plugin_dir_url(dirname(__FILE__));
               $dirImages=$dir."images"; 
          $formHtml =  $this->ReplaceTagAll("[@Images@]",$dirImages,$formHtml); 
          
          
           $formHtml =  $this->ReplaceTagAll("[@fieldIdId@]","field_".$fieldIdId."()",$formHtml); 
            
            $resArr=array();
            $resArr['formHtml']=$formHtml;
          $resArr['groupButtonSave']=$groupButtonSave;
          return $resArr;
          
      }  

      /*** END OF HELP Functions fro HTML HELPER ***/  
  }