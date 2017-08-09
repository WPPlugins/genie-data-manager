<?php
  include_once dirname(dirname(__FILE__)) . '/Logic/genie_GeneralUsage.php';
include_once dirname(dirname(__FILE__)) . '/Logic/genie_CMSSpecials.php';
include_once dirname(dirname(__FILE__)) . '/Logic/genie_DataBase.php';
include_once dirname(dirname(__FILE__)) . '/Logic/genie_Definition.php';
include_once dirname(dirname(__FILE__)) . '/Logic/genie_Table.php';
include_once dirname(dirname(__FILE__)) . '/Logic/genie_Form.php';

class genie_ModelHelper
{
    
    public function checkLoaded($loaded,$formType,$formEntity,$modelName)
    {
         $toLoad=true;  
           $groupLoaded = false;    
           foreach($loaded as $load)
          {
                 $toLoad = $this->checkLoadedInternal($load,$formType,$formEntity,$toLoad);
                  $groupLoaded=$this->checkGroupLoaded($load,$formEntity,$groupLoaded);
          }
          
          if($modelName!=null && $modelName!="entity_id_".$formEntity) 
          {
                 $toLoad=false;  
          }
          return array('toLoad'=>$toLoad,'groupLoaded'=>$groupLoaded);
          
    }
    
    
    private function checkLoadedInternal($load,$formType,$formEntity,$toLoad)
    {
        
        if($load['type']==$formType && $load['entity_id']==$formEntity)
              {
                  $toLoad=false;
              }
              
        return $toLoad;
    }
    
    private function checkGroupLoaded($load,$formEntity,$groupLoaded)
    {
        if($load['type']=='group' && $load['entity_id']==$formEntity)
              {
                  $groupLoaded=true;
              }
        return $groupLoaded;
    }
    
    
    public function AddDefQueryByFilter($filter,$formEntity,$childField,$parentField,$parentEntity,$defWhereParameters,$defaultFilters,$applyFilters,$defaultQuery)
    {
        
        $DM_GeneralUsage = new genie_GeneralUsage();
            if($filter['name']=='entity_id_'.$parentEntity)
            {
                $filterRow=$filter['data'];
                if($childField!="0" && $parentField!="0" && isset($filterRow['field_'.$parentField]))
                {
                    array_push($defWhereParameters,array("field_".$childField=>$filterRow['field_'.$parentField])); 
                }
               
            }
            if($filter['name']=='entity_id_'.$formEntity)
            {
                $filterRow=$filter['data'];   
                foreach($filterRow as $key=>$value)
                {
                    if(!$DM_GeneralUsage->checkPosition($key,"_OldValue") && !$DM_GeneralUsage->checkPosition($key,"_model") && trim($value)!="")
                    {
                        array_push($defWhereParameters,array($key=>$value)); 
                    }
                }
                 $defaultFilters = $filter['data']; 
                $applyFilters=true;
                $defaultQuery=true;
            }
     
        return array('defaultFilters'=>$defaultFilters,'applyFilters'=>$applyFilters,'defaultQuery'=>$defaultQuery,'defWhereParameters'=>$defWhereParameters);
    }
    
    
    public function StartDefaultFilter($DM_DataBase,$form,$parentForm,$parentField,$parentTableName,$childField,$defWhereParameters,$applyFilters,$defaultQuery,$defaultFilters)
    {
        $DM_DataBase= new genie_DataBase();
        $DM_CMS = new genie_CMSSpecials();
        if($parentForm!=null && ($parentForm['applyDefaultQuery'] || $parentForm['applyDefaultQuery']==1))
         {
             
             $cachedName="default_filters_entity_".$parentForm["entity_id"];
             
              $posibleResults=$DM_CMS->getDataFromCache($cachedName);
              if($posibleResults==null)
              {
              
                  $defaultFilters = $DM_DataBase->getDefaultFilters("entity_id_".$parentForm["entity_id"],"defaultQueryValue");
                  $defaultFiltersParams = $DM_DataBase->createParamsFromRow($defaultFilters,true);
                  $posibleResults = $DM_DataBase->Select($parentTableName,$defaultFiltersParams,true);
                  $DM_CMS->saveDataToCache($cachedName,$posibleResults);
              }
              foreach($posibleResults as $result)
              {
                  if($childField!="0" && $parentField!="0" && isset($result['field_'.$parentField]) && trim($result['field_'.$parentField])!="")
                  {
                        array_push($defWhereParameters,array("field_".$childField=>$result['field_'.$parentField])); 
                       
                  }
              }
         }
         if($form['applyDefaultQuery'] || $form['applyDefaultQuery']==1)
         {
             $cachedName="defaultQueryValues_".$form["entity_id"];
             $defaultFilters=$DM_CMS->getDataFromCache($cachedName);
             if($defaultFilters==null)
             {
                    $defaultFilters = $DM_DataBase->getDefaultFilters("entity_id_".$form["entity_id"],"defaultQueryValue");
                    $DM_CMS->saveDataToCache($cachedName,$defaultFilters);
             }
             foreach($defaultFilters as $key=>$value)
             {
                array_push($defWhereParameters,array($key=>$value)); 
             }
             $applyFilters=true;
             $defaultQuery=true;
         }
         if(!$defaultQuery && ($form['applyFilter']==1 || $form['applyFilter']))
         {
              $cachedName="defaultFilterValues_".$form["entity_id"];
             $defaultFilters=$DM_CMS->getDataFromCache($cachedName);
             if($defaultFilters==null)
             {
                    $defaultFilters = $DM_DataBase->getDefaultFilters("entity_id_".$form["entity_id"],"defaultFilterValue");
                    $DM_CMS->saveDataToCache($cachedName,$defaultFilters);
             }
             $applyFilters=true;
             
         }
         return array('defaultFilters'=>$defaultFilters,'applyFilters'=>$applyFilters,'defaultQuery'=>$defaultQuery,'defWhereParameters'=>$defWhereParameters);
         
    }
    
    
    
    
}