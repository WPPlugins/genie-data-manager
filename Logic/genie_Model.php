<?php
include_once dirname(dirname(__FILE__)) . '/Logic/genie_GeneralUsage.php';
include_once dirname(dirname(__FILE__)) . '/Logic/genie_DataBase.php';
include_once dirname(dirname(__FILE__)) . '/Logic/genie_CMSSpecials.php';
include_once dirname(dirname(__FILE__)) . '/Logic/genie_DynamicCreation.php';
include_once dirname(dirname(__FILE__)) . '/Logic/genie_MailChimp.php';
include_once dirname(dirname(__FILE__)) . '/Logic/genie_Filters.php';
include_once dirname(dirname(__FILE__)) . '/Logic/genie_ModelHelper.php';
include_once dirname(dirname(__FILE__)) . '/Logic/genie_Template.php';
//genie_CMSSpecials

  class genie_Model
  {
        public function getModelByFormId($formId, $filter, $modelName)
        {
           $filterNotEmpty = $this->FilterNotEmpty($filter); 
            
            $resultModels = array();
            $entitiesModels = array();
            $results = $this->getFormsDetails($formId);
             if($results!=null)
             {
                 $models = $results['models'];
                 $formsArr=$results['formsArr'];
                 $dependencies = $results['dependencies'];
                 $splittedModels = $this->SplitModels($models);
                  foreach($splittedModels as $entityId=>$splitModels)
                  {
                      if($modelName!=null && "entity_id_".$entityId!=$modelName)
                      {
                            continue;
                      }
                      else
                      {
                          $prepared=array();
                          foreach($splitModels as $spModel)
                          {
                              if($spModel['type']!="scheme" && !isset($prepared[$spModel['type']]))
                              {
                                  if($filterNotEmpty && ($filter['name']=="entity_id_".$spModel["entity_id"] || ($spModel['connected'] && $filter['name']=="entity_id_".$spModel["dependency"]['parentEntity'])))
                                  {
                                       
                                  }
                                  else
                                  {
                                      //create general filter
                                  }
                                  
                                  //add filter for this and parent model  where apply always = true 
                                  
                                  $prepared[$spModel['type']]=true;
                              }
                          }
                     
                      }
                  }
                 
             }
        }
        
        private function FilterNotEmpty($filter)
        {
            $filterNotEmpty=false;
          if($filter!=null)
          {
              $filterRow=$filter['data'];
              foreach($filter as $key=>$value)
              {
                  if(is_string($value) && trim($value)!="")
                  {
                      $filterNotEmpty=true;
                  }
              }
          }
          return $filterNotEmpty;
        }
        private function getFormsDetails($formId)
        {
            $results=null;
            $formsArr = array();
            $DM_GeneralUsage=new genie_GeneralUsage();
            $DM_DataBase = new genie_DataBase();
            $forms =    $DM_DataBase->Select("DMSysForms",array(),false,false);
			/*TODO:Split definitions*/
            $allDefinitions = $DM_DataBase->Select("DMSysDefinitions",array(),false,false);
            $allSubDependencies =  $DM_DataBase->Select("DMSysDefaultQueryValues",array(),false,false);
            $SelectedForm = null;
            foreach($forms as $form)
            {
                if($form["id"]==$formId)
                {
                   $SelectedForm=$form; 
                }
            }
            
            $models = array();
            $dependencies = array();
            
            $splittedDefinitions = $this->SplitDefinitions($allDefinitions);
		$splittedDefaults = $this->SplitDefaults($allSubDependencies);
            if($SelectedForm!=null)
            {
                //for scheme
                if($SelectedForm["type"]=="scheme")
                {
                    $formHtml = $SelectedForm["formHtml"];
                }
                else
                {
                    $formHtml ="[@form_id_".$SelectedForm["id"]."@]";
                }
               foreach($forms as $form)
               {
                    $tag = "[@form_id_".$form["id"]."@]";   
                    if($DM_GeneralUsage->checkPosition($formHtml,$tag))
                    {
                        $groupForm = null;
                        $groupFormExtra=null;
                        if($form['type']!="group" && $form['type']!="scheme")
                        {
                            $found=false;
                            foreach($forms as $formG)
                            {
                                $tag2 = "[@form_id_".$formG["id"]."@]";
                                if($formG['entity_id']==$form['entity_id'] && $formG['type']=="group" && $DM_GeneralUsage->checkPosition($formHtml,$tag2))
                                {
                                    $groupForm=$formG;
                                    $found=true;
                                } 
                                if($formG['entity_id']==$form['entity_id'] && $formG['type']=="group")
                                {
                                    $groupFormExtra=$formG;
                                } 
                            }
                            if($found==false)
                            {
                                $groupForm =  $groupFormExtra;
                            }
                        }
                        $defsToInsert=array();
                        if(isset($splittedDefinitions[$form["entity_id"]]))
                        {
                            $defsToInsert =   $splittedDefinitions[$form["entity_id"]];
                        }
                        
                        $defaultsToInsert=array();
                        if(isset($splittedDefaults[$form["id"]]))
                        {
                            $defaultsToInsert =   $splittedDefaults[$form["id"]];
                        }
                        
                        array_push($formsArr,$form);
                        if($form["connectedFormId"]>0)
                        {
                            foreach($forms as $form2)
                            {
                                if($form2["id"]==$form["connectedFormId"])
                                {
                                    $defsParentToInsert=array();
                                    if(isset($splittedDefinitions[$form2["entity_id"]]))
                                    {
                                        $defsParentToInsert =   $splittedDefinitions[$form2["entity_id"]];
                                    }
                                    $defaultsParentToInsert=array();
                                    if(isset($splittedDefaults[$form2["id"]]))
                                    {
                                        $defaultsParentToInsert =   $splittedDefaults[$form2["id"]];
                                    }
                                    $dependency = array('parentEntity'=>$form["entity_id"],'parentField'=>$form['parentField'],"parentForm"=>$form,'childEntity'=>$form2["entity_id"],'childField'=>$form['childField'],'childForm'=>$form2,'definitionsParent'=>$defsParentToInsert,'defaultsParent'=>$defaultsParentToInsert);
                                    array_push($dependencies, $dependency);
                                    array_push($models,array('entity_id'=>$form["entity_id"],'type'=>$form['type'],'definitions'=>$defsToInsert,'defaults'=>$defaultsToInsert,'form'=>$form,'groupForm'=>$groupForm, 'connected'=>true,'dependency'=>$dependency));
                                }
                            }
                        }
                        else
                        {
                            array_push($models,array('modelName'=>"entity_id_".$form["entity_id"],'type'=>$form['type'],'definitions'=>$defsToInsert,'defaults'=>$defaultsToInsert,'form'=>$form,'groupForm'=>$groupForm,'connected'=>false,'dependency'=>null));
                        }
                    }
               }
               $results = array('models'=>$models,'formsArr'=>$formsArr,'dependencies'=>$dependencies); 
            }
            
            return $results;
            
        }
        
        
        private function SplitModels($models)
        {
            $results = array();
            foreach($models as $model)
            {
                if(isset($model["entity_id"]))
                {
                    if(!isset($results[$model["entity_id"]]))
                    {
                        $currDefs = array();
                    }
                    else
                    {
                        $currDefs = $results[$model["entity_id"]]; 
                    }
                    array_push($currDefs,$model);
                    $results[$model["entity_id"]] = $currDefs;
                }
            }
            return $results;
        }
        

        
        private function SplitDefinitions($definitions)
        {
            $results = array();
            foreach($definitions as $definition)
            {
                if(isset($definition["entity_id"]))
                {
                    if(!isset($results[$definition["entity_id"]]))
                    {
                        $currDefs = array();
                    }
                    else
                    {
                        $currDefs = $results[$definition["entity_id"]]; 
                    }
                    array_push($currDefs,$definition);
                    $results[$definition["entity_id"]] = $currDefs;
                }
            }
            return $results;
        }
        
          private function SplitDefaults($definitions)
        {
            $results = array();
            foreach($definitions as $definition)
            {
                if(isset($definition["form_id"]))
                {
                    if(!isset($results[$definition["form_id"]]))
                    {
                        $currDefs = array();
                    }
                    else
                    {
                        $currDefs = $results[$definition["form_id"]]; 
                    }
                    array_push($currDefs,$definition);
                    $results[$definition["form_id"]] = $currDefs;
                }
            }
            return $results;
        }
  }
