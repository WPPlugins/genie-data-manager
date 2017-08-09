$( document ).ready( 
    function()
    {
       $("#templatefile").pekeUpload(
       {
           url: DM_getAdminUrl(),
                action:'DMUploadFile', 
                allowedExtensions:'zip',
                onFileSuccess : function(response_data) 
                {
                    var templateFileText = document.getElementById("templateFileTxt");
                    len = response_data.length;
                    if(response_data[len-1]=="0")
                    {
                        response_data=response_data.substring(0,len-1);
                    }
                   templateFileText.value = response_data;
                      
                             
                    },
                onFileError : function (response_data)
                {             
                         alert(response_data);
                }
       }); 
    }
)


         
function CreateTemplate()
{
  var checkedEntities = 
    $("input:checkbox[name=entityName]:checked")
        .map(function() {
            return $(this).val();
        }).get();
     var templateNameText = document.getElementById("templateNameTxt").value;   
    var senddata = {
                           'action':'DMSystemDynamicRequest',
                           'formaction':'SaveTemplate',
                           'entities':   checkedEntities,
                           'templateName':templateNameText
                           
                           };                      
    var funccall = function(result)
    {
        var results=JSON.parse(result);
       if(results.result=="finished")
       {
            document.getElementById("QueryResult").innerHTML = "<a href='"+results.zip+"' target='_blank'>"+results.zip+"</a>"; 
           
       }
       
    }
    applyToServer(funccall,funccall,senddata);
}

function LoadTemplate()
{
  
   
     var templateFileText = document.getElementById("templateFileTxt").value;   
    var senddata = {
                           'action':'DMSystemDynamicRequest',
                           'formaction':'LoadTemplate',
                           'templateFileText':templateFileText
                           
                           };                      
    var funccall = function(result)
    {
        var results=JSON.parse(result);
      
      document.getElementById("QueryResult2").innerHTML = results; 
           
       
       
    }
    applyToServer(funccall,funccall,senddata);
}

function DM_getAdminUrl()
         {
             if(document.URL.indexOf("wp-admin") != -1) {
                 return "admin-ajax.php";
             }
             else
             {
                 if(document.URL.indexOf("index.php") != -1)
                 {
                       mylength = document.URL.indexOf("index.php");
                       url = document.URL.substring(0,mylength-1);
                       return url+"/wp-admin/admin-ajax.php";
                 }
                 else
                 {
                      if(document.URL.indexOf("?") != -1)
                     {
                       mylength = document.URL.indexOf("?");
                       url = document.URL.substring(0,mylength-1);
                       return url+"/wp-admin/admin-ajax.php";
                       
                     }
                     else
                     {
                       return document.URL+"/wp-admin/admin-ajax.php"                     
                     }
                    
                 }
             }
         }