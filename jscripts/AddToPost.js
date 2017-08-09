function DM_AddFormToPost(dropdownElement)
{
      formSelect = document.getElementById(dropdownElement);
     if(formSelect!=null && typeof(formSelect)!="undefined")
       {
           formId=formSelect.value;
           Mytext ="[@form_id_"+formId+"@]";
           tinyMCE.activeEditor.execCommand('mceInsertContent', false, Mytext);
       } 
}