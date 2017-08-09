$( document ).ready( 
function()
{

    
   $("[id*='form_id_'] ").each( function ()
                        {
                            
                            
                            var id=$(this).attr("id");
                            if(id.indexOf("_sub")==-1)
                            {
                                 var formParams = id.split("_");
                                 if(formParams.length>=3)
                                 {
                                    
                                     var formId = formParams[2];
                                     
                                     var senddata = {
                                                                        'action':'DMDynamicRequest',
                                                   'formaction':'GetDataById',
                                                   'myurl':  window.location.href,
                                                   'formParameters':  { 
                                                       'formId': formId
                                                   }
                                                   
                                                   };                      
                                    
                                    var funccall = function(resultOrigin)
                                    {
                                           var n = resultOrigin.indexOf('{"error":');
                                           var result = resultOrigin.substring(n, resultOrigin.length);
                                         var results=JSON.parse(result); 
                                            if(typeof(results['error'])!='undefined' && results['error']!=null )
                                            {
                                               self.errorMessage(results['error']);
                                                $("body").css("cursor", "default");
                                            }  
                                            else
                                            {
                                                if(typeof(results['result'])!='undefined' && results['result']!=null && 
                                                typeof(results['updatedData'])!='undefined' && results['updatedData']!=null )
                                                {         
                                                    var myelement = document.getElementById(id);       
                                               
                                                    ko.applyBindings(new DM_Model(results['updatedData'],formId,true),myelement); 
                                                   $("body").css("cursor", "default");
                                                }
                                            }
                                            
                                       
                                    }
                                   $("body").css("cursor", "progress");
                                    applyToServer(funccall,funccall,senddata);
                                     
                                 }
                            }
                        });
       
              
            
                        
});


    
         
         