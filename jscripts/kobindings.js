ko.bindingHandlers.datepicker = {
    init: function(element, valueAccessor, allBindingsAccessor) {
        //initialize datepicker with some optional options
        var options = allBindingsAccessor().datepickerOptions || {};
        $(element).datepicker({dateFormat: 'yy-mm-dd'});
          
        //handle the field changing
        ko.utils.registerEventHandler(element, "change", function () {
            var observable = valueAccessor();
            var date = $(element).datepicker("getDate");
            day  = date.getDate(),  
            month = date.getMonth() + 1,              
            year =  date.getFullYear();
            var newDate=year+'-'+month+'-'+day;
            observable(newDate);
        });
        
        //handle disposal (if KO removes by the template binding)
        ko.utils.domNodeDisposal.addDisposeCallback(element, function() {
            $(element).datepicker("destroy");
        });
    
    },
    //update the control when the view model changes
    update: function(element, valueAccessor) {
        var value = ko.utils.unwrapObservable(valueAccessor()),
            current = $(element).datepicker("getDate");
        
        if (value - current !== 0) {
            $(element).datepicker("setDate", value);   
        }
    }
};


(function( wysiwyg ) {

    wysiwyg.extensions['mycustomextension'] = function( editor, args, allBindings, bindingContext ) {
        // your logic goes here
    };

})( ko.bindingHandlers['wysiwyg'] );



ko.bindingHandlers.pekeUpload = {
    init: function (element, valueAccessor) {
         var observable = valueAccessor();
         filestr=observable();
         if(filestr.length>0)
           {
            var newdiv = document.createElement("div");             
            newdiv.innerHTML ="<a href='"+ filestr+"' target='_blank'>view file</a>";  
           $(element).after(newdiv);
           $(element).toggle(); 
           }
       
    },
    update: function (element, valueAccessor, allBindingsAccessor, viewModel, bindingContext) {  
            $(element).pekeUpload({ 
                url: DF_getUrlPost(),
                action:'DMUploadFile',
                onFileSuccess : function(response_data) 
                {
                    len = response_data.length;
                    if(response_data[len-1]=="0")
                    {
                        response_data=response_data.substring(0,len-1);
                    }
                  var observable = valueAccessor();
                  observable(response_data);  
                      
                       
                         var newdiv = document.createElement("div");             
                         newdiv.innerHTML ="<a href='"+ response_data+"' target='_blank'>view file</a>";
                        //$(element).css("style","visibility:hidden;");
                        $(element).after(newdiv);
                       $(element).toggle(); 
                    },
                onFileError : function (response_data)
                {             
                         alert(response_data);
                }
    
        });
    }
     
}

ko.bindingHandlers.pekeUploadImage = {
    init: function (element, valueAccessor) {
       var observable = valueAccessor();
         filestr=observable();
           if(filestr.length>0)
           {
            var newdiv = document.createElement("img");             
            newdiv.src = filestr.trim();
           
           $(element).after(newdiv);
           $(element).toggle(); 
           }
    },
    update: function (element, valueAccessor, allBindingsAccessor, viewModel, bindingContext) {  
            $(element).pekeUpload({ 
                url: DF_getUrlPost(),
                action:'DMUploadImage',
                allowedExtensions:'jpeg|jpg|png|gif',
                onFileSuccess : function(response_data) 
                {
                    len = response_data.length;
                    if(response_data[len-1]=="0")
                    {
                        response_data=response_data.substring(0,len-1);
                    }
                  var observable = valueAccessor();
                  observable(response_data);  
                      
                             var newdiv = document.createElement("img");                       
                           newdiv.src = response_data;

                        //$(element).css("style","visibility:hidden;");
                        $(element).after(newdiv);
                       $(element).toggle(); 
                    },
                onFileError : function (response_data)
                {             
                         alert(response_data);
                }
    
        });
    }
    
}



ko.bindingHandlers.booleanValue = {
    init: function(element, valueAccessor, allBindingsAccessor) {
        var observable = valueAccessor(),
            interceptor = ko.computed({
                 init: function(newValue) {
                    if(newValue==1 || newValue=="1" || newValue=="true"|| newValue==true)
                    {
                        observable("1");
                    }
                    else
                    {
                          observable("0");
                    }
                } ,                  
                read: function() {
                    if(observable()=="1" || observable()=="true"|| observable()==true)
                    {
                        return true;
                    }
                    else
                    {
                        return false;
                    }
                },
                write: function(newValue) {
                      if(newValue=="1" || newValue=="true"|| newValue==true)
                    {
                              observable("1");
                    }
                    else
                    {
                          observable("0");
                    }
                }                   
            });

        ko.applyBindingsToNode(element, { checked: interceptor });
    }
};



 
  ko.bindingHandlers.radioButton = {
    init: function(element, valueAccessor, allBindingsAccessor) {
        var observable = valueAccessor(),
            interceptor = ko.computed({
                init: function(newValue) {
                    if(typeof(observable)!="undefined")
                      {
                       observable($(element).attr("value"));
                      }
                },                      
                read: function() {
                      if(typeof(observable)!="undefined")
                      {
                          if( $(element).attr("value")==observable())
                          { 
                              return $(element).attr("value");
                          }
                      }
                      return ko.observable();
                },
                write: function(newValue) {
                    if(typeof(observable)!="undefined")
                      {
                       observable($(element).attr("value"));
                      }
                }                   
            });

        ko.applyBindingsToNode(element, { checked: interceptor });
    }
};

ko.bindingHandlers['visibleInline'] = {
    'update': function (element, valueAccessor) {
        var value = ko.utils.unwrapObservable(valueAccessor());
        var isCurrentlyVisible = !(element.style.display == "none");
        if (value && !isCurrentlyVisible)
        {
            element.style.display = "block";
            element.style.position = "absolute";
            element.style.top = "-100px";
            element.style.right = "200px";
        }
        else if ((!value) && isCurrentlyVisible)
            element.style.display = "none";
    }
};

 function DF_getUrlPost()
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
                       return url+"wp-admin/admin-ajax.php";
                 }
                 else
                 {
                      if(document.URL.indexOf("?") != -1)
                     {
                       mylength = document.URL.indexOf("?");
                       url = document.URL.substring(0,mylength-1);
                       return url+"wp-admin/admin-ajax.php";
                       
                     }
                     else
                     {
                       return document.URL+"wp-admin/admin-ajax.php"                     
                     }
                    
                 }
             }
     }
