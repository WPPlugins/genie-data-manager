<?php


  class genie_Template
  {
      public function getFormDiv($id,$formSub,$pattern)
      {
          $result = "<div id='form_id_".$id."{$formSub}'>".$pattern."</div>"; 
          return $result;
      }
      
      public function groupButtonSave($entityId,$formDetails,$DM_CMSSpecials)
      {
          
          $groupButtonSave =' <input type="submit" data-bind="click: function() {saveData(\'entity_id_'.$entityId.'\') ;} " 
          value="'.$DM_CMSSpecials->Translate("Save All",$formDetails["entity_id"]).'">   ';     
          return $groupButtonSave;
      }
      
      public function  labelReadOnly($strroot,$modelName,$definition,$labelReadOnlyText)
      {
          $labelReadOnly = "<a href='#' data-bind='click: function() { ".$strroot.".sortSpecial(\"{$modelName}\",\"field_{$definition["id"]}\");}'>".$labelReadOnlyText."</a>";
          return $labelReadOnly;
      }
      
      public function   editImageShowMore($strroot,$DM_CMSSpecials,$formDetails,$dirImages)
      {
          $DM_CMSSpecials = new genie_CMSSpecials();
          $DM_DataBase = new genie_DataBase();
          $entityId=$DM_DataBase->getEntityId("DMSysEntities");
          $editImage=  $this->editImage($strroot,$DM_CMSSpecials,$formDetails,$dirImages,"Show More","readMore");
          
          // $editImage="<a href='#' data-bind=\"click: function(data) { {$strroot}.showMe(data);},visible:(!ShowExtend_computed())\"  
           //>".$genie_CMSSpecials->Translate("Show Details",$entityId)."</a>";
          
          return $editImage;
      }
       public function editImageEdit($strroot,$DM_CMSSpecials,$formDetails,$dirImages)
      {
         $editImage=  $this->editImage($strroot,$DM_CMSSpecials,$formDetails,$dirImages,"Edit","Edit");
          
          return $editImage;
      }
       
        public function editImage($strroot,$DM_CMSSpecials,$formDetails,$dirImages,$actionText,$imageName)
      {
          $editImage="<input type='image'  data-bind=\"click: function(data) { {$strroot}.showMe(data);},visible:(!ShowExtend_computed())\"  
          title='".$DM_CMSSpecials->Translate($actionText,$formDetails["entity_id"])."' src='{$dirImages}/{$imageName}.png'   />";
          return $editImage;
      }
       
      public function deleteImage($strroot,$entityId,$dirImages,$DM_CMSSpecials,$formDetails)
      {
          $deleteImage = "<input type='image'  data-bind=\"click: function(data) { {$strroot}.deleteItemData('entity_id_".$entityId."',data);}\"  
          title='".$DM_CMSSpecials->Translate('Delete',$formDetails["entity_id"])."' src='{$dirImages}/Delete.png'   />"; 
          return $deleteImage;
      }
      
       public function deleteButton($mappingType,$entityId,$DM_CMSSpecials,$formDetails)
      {
            $button = '<input class="DM_custom_button" type="submit" data-bind="click:  function(data) { [@root@].deleteItemData(\'entity_id_'.$entityId.'\',data);}"
           value="'.$DM_CMSSpecials->Translate('Delete',$formDetails["entity_id"]).'"> ';    
          return $button;
          
          
      }
      
      public function groupDeleteButton($mappingType,$entityId,$DM_CMSSpecials,$formDetails)
      {
          $button = '<input type="submit" data-bind="click:function(data) { [@root@].deleteItemData(\'entity_id_'.$entityId.'\',data);}" 
          value="'.$DM_CMSSpecials->Translate("Delete",$formDetails["entity_id"]).'"> ';    
          return $button;
      }
      
      public function addToPageImage($strroot,$DM_CMSSpecials,$formDetails,$dirImages)
      {
          $addToPageImage = "<input type='image'  data-bind=\"click: function(data) { {$strroot}.addToForm(data);}\"  
          title='".$DM_CMSSpecials->Translate('Add To Form',$formDetails["entity_id"])."' src='{$dirImages}/attach.png'   />"; 
          return $addToPageImage;
      }
      
    
      public function  formHtml($exerptHtml,$strroot,$dirImages,$formHtml)
      {
           $formHtml = $exerptHtml."               
                  <div  data-bind=\"visible:ShowExtend_computed()\" class=\"DM_Details\">
                    <img  data-bind=\"click: function(data) { {$strroot}.hideMe(data);},visible:ShowExtend_computed()\"  src=\"{$dirImages}/x.png\"/>
                   ".$formHtml."</div>";
                   return $formHtml;
      }
      
      public function actionButton($mappingType,$entityId,$DM_CMSSpecials,$formDetails)
      {
          $button = '<input class="DM_custom_button" type="submit" data-bind="click: function() { '.$mappingType["function"].'(\'entity_id_'.$entityId.'\');}"
           value="'.$DM_CMSSpecials->Translate($mappingType['Text'],$formDetails["entity_id"]).'"> ';    
          return $button;
      }
      
      public function groupActionButton($mappingType,$entityId,$DM_CMSSpecials,$formDetails)
      {
          $button = '<input type="submit" data-bind="click: function(data) { '.$mappingType["function"].'(\'entity_id_'.$entityId.'\',data);}" 
          value="'.$DM_CMSSpecials->Translate($mappingType['Text'],$formDetails["entity_id"]).'"> ';    
          return $button;
      }
      
      public function Pages($entityId,$strroot,$DM_CMSSpecials,$formDetails)
      {
        
          $style="";
          if(trim($formDetails["DisplayColor"])!="" && trim($formDetails["DisplayColor"])!="orange" )
          {
              $style=" ".$formDetails["DisplayColor"];
          }
          
          
                  $MyPages='
                   <div class="pagination'.$style.'" > 
                    <ul data-bind="template: { name: \'page-template-'.$entityId.'\', foreach: entity_id_'.$entityId.'mypages() }">
                    </ul>
                    </div>
                    
                    <script type="text/html" id="page-template-'.$entityId.'">
                       <li data-bind="css: currNum()=='.$strroot.'.entity_id_'.$entityId.'pagenum() ? \'selected\': \'\' "> 
                       <a href="#" data-bind="click: function() {'.$strroot.'.changePage(\'entity_id_'.$entityId.'\',currNum()) ;}">'.$DM_CMSSpecials->Translate("Page",$formDetails["entity_id"]).'
                <span data-bind="text:currNum"> </span> </a>
                      </li>
                    </script>
                
                ';
                
                return $MyPages;
      }
      
      public function  resetButton($strroot,$entityId,$mappingType,$DM_CMSSpecials,$formDetails)
      {
          $resetButton='<input type="submit" data-bind="click: function(data) { '.$strroot.'.reset(\'entity_id_'.$entityId.'\',\''.$mappingType['formSufix'].'\');}" 
          value="'.$DM_CMSSpecials->Translate("Reset",$formDetails["entity_id"]).'"> ';    
          return $resetButton;
          
      }
      
      public function showAsButtonFormHtml($strroot,$dirImages,$formHtml,$shortcutText)
      {
           $formHtml = "
               <input type='submit' href=\"#\" data-bind=\"click: function(data) { {$strroot}.showMe(data);},visible:(!ShowExtend_computed())\" value='{$shortcutText}'>
              <div  data-bind=\"visible:ShowExtend_computed()\">
              <img  data-bind=\"click: function(data) { {$strroot}.hideMe(data);},visible:ShowExtend_computed()\"  src=\"{$dirImages}/close.png\"/>
              {$formHtml}
              </div>
              ";
           return $formHtml;
      }
      
      public function   visibleGroup($formDetails,$modelName)
      {
          /*
           if($formDetails["type"]=="group")
              {
                $visibleGroup = " <div class='DmTable' style='display: none;' data-bind='foreach: ".$modelName." ,visible:".$modelName."_empty_computed'>";
              }
              else
              {
                  $visibleGroup = " <div class='DmTable' style='display: none;' data-bind='foreach: ".$modelName." ,visible:true'>";
              }
            */  
             
                $visibleGroup = ' <div class="DmTable"  data-bind="template:{ name:\'template-group-'.$modelName.'\',foreach:'.$modelName.'}"></div>';
             
              
              return $visibleGroup;              
      }
      
      public function  formFinalDesign($entityId,$mappingType,$titles,$visibleGroup,$isOnPage,$formHtml,$MyPages,$groupButtonSave,$modelName,$formDetails,$IndexDesign="")
      {
          
          //<script type="text/html" id="page-template-'.$entityId.'">   
            /*   $formHtml = " [@ErrorDiv@]      [@SuccessDiv@]   
          <div id='form_entity_".$entityId.$mappingType['formSufix']."'>     
              {$titles} 
                {$visibleGroup}
                <div ".$isOnPage.">".$formHtml."</div>
              </div>
          </div>".$MyPages.$groupButtonSave; 
          */ 
          $cssstyle="";
          if(trim($formDetails["cssStyle"]!=""))
          {
              $cssstyle = "<style>".$formDetails["cssStyle"]."</style>";
          }  
          if($formDetails["ShowTitles"]!="1"&&$formDetails["ShowTitles"]!=1 && $formDetails["ShowTitles"]!=true)
          {
              $titles="";
          }        
          $formHtml = $cssstyle.$IndexDesign." [@ErrorDiv@]      [@SuccessDiv@]   
            <div id='form_entity_".$entityId.$mappingType['formSufix']."'>     
              {$titles} 
                {$visibleGroup}
                <script type=\"text/html\" id=\"template-group-".$modelName."\">   
                <div ".$isOnPage.">".$formHtml."</div>
                </script>
          </div>".$MyPages.$groupButtonSave;
          return $formHtml;
      }
      
      public function errorDiv($isRoot,$entityId)
      {
          $errorDiv = "<div style='display:none;' data-bind='html:{$isRoot}entity_id_".$entityId."_errorMessage_computed(),
           visible:{$isRoot}entity_id_".$entityId."_errorMessage_computed()' class='errorDiv'></div>";
           return $errorDiv;
      }
      
      public function  successDiv($isRoot,$entityId)
      {
          $successDiv = "<div style='display:none;' data-bind='html:{$isRoot}entity_id_".$entityId."_successMessage_computed(), 
          visible:{$isRoot}entity_id_".$entityId."_successMessage_computed()' class='successDiv'></div> ";
          return $successDiv;
      }
      
      
      public function fieldMapping($getFieldType,$getFieldKey)
      {
          $fields = $this->createArray();
          
          
          $result= "[@mapValue@]";
          if($fields!=null && count($fields)>0)
          {
              foreach($fields as $fieldType=>$fieldDefs)
              {
                   if($fieldType==$getFieldType)
                   {
                       if($fieldDefs!=null && count($fieldDefs)>0)
                       {
                           foreach($fieldDefs as $fieldKey=>$fieldDef)
                           {
                               if($fieldKey==$getFieldKey)
                               {
                                   return $fieldDef;
                               }
                           }
                       }
                   }
              }
          }
          return $result;
      }
      
      public function createArray()
      {
         $fields=array();
         $field=array();
         $field['FieldNameValue']="  <input type='text' data-bind='value: [@field@]'  [@class@] [@style@]  />";
         $field['FieldNameLabel']="[@fieldName@]";
         $field['FieldNameValueReadOnly']='<label data-bind="text: [@field@]" [@class@] [@style@]></label>';
         $field['FieldNameLabelReadOnly']='<label  style="font-weight:bold;" width="200">[@fieldName@]</label>';
         $field['fieldReadOnly']="[@fieldValue@]";
         $field['fieldCode']="[@fieldCode@]";
         $fields['text']=$field;
         
         $field['FieldNameValue']="<textarea data-bind='value: [@field@]' [@class@] [@style@] ></textarea>";
         $field['FieldNameLabel']="[@fieldName@]";
         $field['FieldNameValueReadOnly']='<label data-bind="text: [@field@]" [@class@] [@style@]></label>';
         $field['FieldNameLabelReadOnly']='<label  style="font-weight:bold;" width="200">[@fieldName@]</label>';
         $field['fieldReadOnly']="[@fieldValue@]";
         $field['fieldCode']="[@fieldCode@]";
         $fields['textarea']=$field;
         
         
         $field['FieldNameValue']="<input  type='file' data-bind='pekeUpload:  [@field@] '  [@class@] [@style@] />";
         $field['FieldNameLabel']="[@fieldName@]";
         $field['FieldNameValueReadOnly']='<a data-bind="attr:{href:[@field@]}"><div [@class@] [@style@]>[@translate@]View File[@endtranslate@]</div></a>';
         $field['FieldNameLabelReadOnly']='<label  style="font-weight:bold;" width="200">[@fieldName@]</label>';
         $field['fieldReadOnly']="[@fieldValue@]";
         $field['fieldCode']="[@fieldCode@]";
         $fields['file']=$field;
         
         
         $field['FieldNameValue']="<textarea data-bind='wysiwyg: [@field@]'  [@class@] [@style@]></textarea>";
         $field['FieldNameLabel']="[@fieldName@]";
         $field['FieldNameValueReadOnly']='<div data-bind="html: [@field@]" [@class@] [@style@] ></div>';
         $field['FieldNameLabelReadOnly']='<label  style="font-weight:bold;" width="200">[@fieldName@]</label>';
         $field['fieldReadOnly']="[@fieldValue@]";
         $field['fieldCode']="[@fieldCode@]";
         $fields['htmleditor']=$field;
         
         $field['FieldNameValue']="<input  type='file' data-bind='pekeUploadImage:  [@field@] ' [@class@] [@style@] />";
         $field['FieldNameLabel']="[@fieldName@]";
         $field['FieldNameValueReadOnly']='<div [@class@] [@style@]><img data-bind="attr:{src:[@field@]}" /></div>';
         $field['FieldNameLabelReadOnly']='<label  style="font-weight:bold;" width="200">[@fieldName@]</label>';
         $field['fieldReadOnly']="[@fieldValue@]";
         $field['fieldCode']="[@fieldCode@]";
         $fields['image']=$field;
         
         $field['FieldNameValue']="<input data-bind='datepicker:  [@field@] '  [@class@] [@style@]/>";
         $field['FieldNameLabel']="[@fieldName@]";
         $field['FieldNameValueReadOnly']='<label data-bind="text: [@field@]" [@class@] [@style@]></label>';
         $field['FieldNameLabelReadOnly']='<label  style="font-weight:bold;" width="200">[@fieldName@]</label>';
         $field['fieldReadOnly']="[@fieldValue@]";
         $field['fieldCode']="[@fieldCode@]";
         $fields['date']=$field;
         
         $field['FieldNameValue']="<label><input type='checkbox' data-bind='booleanValue: [@field@]' /><span class='check'></span>[@fieldName@]</label>";
         $field['FieldNameLabel']="[@fieldName@]";
         $field['FieldNameValueReadOnly']='<label data-bind="text: [@field@]" [@class@] [@style@]></label>';
         $field['FieldNameLabelReadOnly']='<label  style="font-weight:bold;" width="200">[@fieldName@]</label>';
         $field['fieldReadOnly']="[@fieldValue@]";
         $field['fieldCode']="[@fieldCode@]";
         $fields['checkbox']=$field;
         
         $field['FieldNameValue']="<select data-bind='options: [@root@].[@fieldOptions@],optionsValue:\"fieldValue\", optionsText:\"fieldText\",  value: [@field@]' [@class@] [@style@]></select>";
         $field['FieldNameLabel']="[@fieldName@]";
         $field['FieldNameValueReadOnly']='<label data-bind="text: [@field@]" style="font-weight:normal;" width="200"></label>';
        
         $field['FieldNameLabelReadOnly']='<label  style="font-weight:bold;" width="200">[@fieldName@]</label>';
         $field['fieldReadOnly']="[@fieldValue@]";
         $field['fieldCode']="[@fieldCode@]";
         $fields['dropdownlist']=$field;
         
         $field['FieldNameValue']="<div data-bind='foreach:[@root@].[@fieldOptions@]'><label data-bind='text:fieldValue'></label><input type='radio' data-bind='value:fieldValue,radioButton:[@parent@].[@field@]'/ [@class@] [@style@]><br/></div>";
         $field['FieldNameLabel']="[@fieldName@]";
         $field['FieldNameValueReadOnly']='<label data-bind="text: [@field@]" style="font-weight:normal;" width="200"></label>';
         $field['FieldNameLabelReadOnly']='<label  style="font-weight:bold;" width="200">[@fieldName@]</label>';
         $field['fieldReadOnly']="[@fieldValue@]";
         $field['fieldCode']="[@fieldCode@]";
         $fields['radio']=$field;
         
         $field['FieldNameValue']="<input type='text' data-bind='value: [@field@]' [@class@] [@style@]  />";
         $field['FieldNameLabel']="[@fieldName@]";
         $field['FieldNameValueReadOnly']='<label data-bind="text: [@field@]" [@class@] [@style@]></label>';
         $field['FieldNameLabelReadOnly']='<label  style="font-weight:bold;" width="200">[@fieldName@]</label>';
         $field['fieldReadOnly']="[@fieldValue@]";
         $field['fieldCode']="[@fieldCode@]";
         $fields['email']=$field;
         
         $field['FieldNameValue']=" <input type='text' data-bind='value: [@field@]'  [@class@] [@style@]  />";
         $field['FieldNameLabel']="[@fieldName@]";
         $field['FieldNameValueReadOnly']='<a data-bind="attr:{href: [@field@]}" style="font-weight:normal;"><label data-bind="text: [@field@]" [@class@] [@style@]></label></a>';
         $field['FieldNameLabelReadOnly']='<label  style="font-weight:bold;" width="200">[@fieldName@]</label>';
         $field['fieldReadOnly']="[@fieldValue@]";
         $field['fieldCode']="[@fieldCode@]";
         $fields['link']=$field;
         
         /*template
         
          $field['FieldNameValue']="";
         $field['FieldNameLabel']="[@fieldName@]";
         $field['FieldNameValueReadOnly']='';
         $field['FieldNameLabelReadOnly']='';
         $field['fieldReadOnly']="[@fieldValue@]";
         $field['fieldCode']="[@fieldCode@]";
         $fields['']=$field;
         
         */
         return $fields;
         
      }
  }