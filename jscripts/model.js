//$(document).ready(DefineModels);
function DM_Model(result, formName, isJson) {
    if (typeof (isJson) == "undefined") {
        isJson = false;
    }
    var self = this;
    self.formName = formName;
    self.lastResult = ko.observable();
    self.errorMessage = ko.observable();
    self.successMessage = ko.observable();

    self.AddSpecialMemberModel = function (name, item) {
        for (j = 0; j < self.MyModels.length; j++) {
            var jname = self.MyModels[j]['name'];
            var type = self.MyModels[j]['type'];

            if (type == "fieldValues") {
                if (DM_IsSet(jname) && self.MyModels[j]["forModelname"] == name &&
                    DM_IsSet(self.MyModels[j]['isConnected']) && self.MyModels[j]['isConnected']
                    && DM_IsSet(self.MyModels[j]['forModelname'])
                    && DM_IsSet(self.MyModels[j]['ParentFieldName'])
                ) {
                    self[jname + "_ParentFieldName"] = self.MyModels[j]["ParentFieldName"];
                    self[jname + "_forModelname"] = self.MyModels[j]["forModelname"];
                    varsCompute = ko.computed(function () {
                        modelName = this;
                        if (!DM_IsSet(item[modelName])) {
                            item[modelName] = ko.observableArray([]);
                        }
                        if (DM_IsSet(item[self[modelName + "_ParentFieldName"]]())) {
                            item[self[modelName + "_ParentFieldName"]]();
                            myArray = ko.utils.arrayFilter(self[modelName](), function (r) {
                                return item[self[modelName + "_ParentFieldName"]]() == r[self[modelName + "_ParentFieldName"]]();
                            });
                            item[modelName](myArray);
                        }
                    }, jname);

                }

            }
        }
    }


    self.CreateSpecialArray = function (name, specialname, data, validators, addvalidators, defaultFilters, formulas)
    {
        if (self[name + specialname] == null || typeof (self[name + specialname]) == 'undefined') {
            self[name + specialname] = ko.observableArray();
            if (specialname == "_filterRow") {
                var specailMember = new DM_SpecialFilter(data[0]);
                var specailMemberReserve = new DM_SpecialFilter(data[0]);
                if (defaultFilters != null) {
                    DM_Edit(specailMember, defaultFilters);
                    DM_Edit(specailMemberReserve, defaultFilters);
                }
            }
            else {
                var specailMember = new DM_Special(data[0]);
                var specailMemberReserve = new DM_Special(data[0]);
            }
            if (addvalidators) {
                DM_AddValidators(specailMember, validators);
                DM_AddFormulas(specailMember, formulas);
                DM_AddValidators(specailMemberReserve, validators);
                DM_AddFormulas(specailMemberReserve, formulas);
            }
            self.AddSpecialMemberModel(name, specailMember);
            self[name + specialname].push(specailMember);
            self[name + specialname + 'Validators'] = validators;
            self[name + specialname + 'Formulas'] = formulas;
            self.AddSpecialMemberModel(name, specailMemberReserve);
            self[name + specialname + 'Reserve'] = specailMemberReserve;
        }
        else {
            if (self[name + "_isFiltered"] == true) {
                //self.filterMyData(name); 
                filterow = self[name + '_filterRow']()[0];
                self.filterMyDataHelper(name, filterow, "");
            }
            else {
                self[name + specialname]([]);
                if (specialname == "_filterRow") {
                    var specailMember = new DM_SpecialFilter(data[0]);
                    var specailMemberReserve = new DM_SpecialFilter(data[0]);
                    if (defaultFilters != null) {
                        DM_Edit(specailMember, defaultFilters);
                        DM_Edit(specailMemberReserve, defaultFilters);
                    }
                }
                else {
                    var specailMember = new DM_Special(data[0]);
                    var specailMemberReserve = new DM_SpecialFilter(data[0]);
                }
                if (addvalidators) {
                    DM_AddValidators(specailMember, validators);
                    DM_AddFormulas(specailMember, formulas);
                    DM_AddValidators(specailMemberReserve, validators);
                    DM_AddFormulas(specailMemberReserve, formulas);
                }
                self.AddSpecialMemberModel(name, specailMember);
                self[name + specialname].push(specailMember);
                self.AddSpecialMemberModel(name, specailMemberReserve);
                self[name + specialname + 'Reserve'] = specailMemberReserve;
            }
        }
    }

    self.isValid = function (model) {
        if (typeof (self[model]) != "undefined") {
            var valid = true;
            ko.utils.arrayForEach(self[model](), function (item) {
                if (typeof (item['isValid']) != 'undefined' && item['isValid']() == false) {
                    valid = false;
                    item['showErrors']();
                }
            });
            return valid;
        }
    }

    self.filterByPage = function (modelname) {
        count = 0;
        pagenum = self[modelname + 'pagenum']();
        pagesize = self[modelname + 'pagesize'];
        if (self[modelname + '_empty_computed'] != null && self[modelname + '_empty_computed']() == true) {
            ko.utils.arrayForEach(self[modelname](), function (item) {
                if (item["isfiltermatch"]() == true) {
                    count++;
                    if (count > (pagenum - 1) * pagesize && count <= pagenum * pagesize) {
                        item['isOnPage'](true);
                    }
                    else {
                        item['isOnPage'](false);
                    }
                }
                else {
                    item['isOnPage'](false);
                }

            });
        }
        else {
            ko.utils.arrayForEach(self[modelname](), function (item) {

                item['isOnPage'](false);


            });

        }
        if (DM_IsSet(self[modelname + 'trigger'])) {
            self[modelname + 'trigger'].notifySubscribers();
        }
    }


    self.changePage = function (modelname, pagenum) {
        if (typeof (self[modelname]) != "undefined" && typeof (self[modelname + 'pagenum']) != "undefined") {
            self[modelname + 'pagenum'](pagenum);
            self.filterByPage(modelname);
        }
    }



    self.initiateArray = function (name, mappedItems) {
        for (j = 0; j < self.MyModels.length; j++) {
            var jname = self.MyModels[j]['name'];
            var type = self.MyModels[j]['type'];

            if (type == "fieldValues") {
                if (DM_IsSet(jname) && self.MyModels[j]["forModelname"] == name && DM_IsSet(self.MyModels[j]['isConnected']) && self.MyModels[j]['isConnected'] && DM_IsSet(self.MyModels[j]['forModelname'])
                    && DM_IsSet(self.MyModels[j]['ParentFieldName'])
                ) {

                    self[jname + "_ParentFieldName"] = self.MyModels[j]["ParentFieldName"];
                    self[jname + "_forModelname"] = self.MyModels[j]["forModelname"];

                    ko.utils.arrayForEach(mappedItems, function (item) {
                        self.AddSpecialMemberModel(name, item);
                    });
                }

            }
        }

        if (self[name] == null || typeof (self[name]) == 'undefined') {
            self[name] = ko.observableArray([]);
        }
        self[name](mappedItems);


        /*
         if(self[name]!=null)
         {
         self[name].valueWillMutate();
         self[name].removeAll();
         }
         else
         {
         self[name] = ko.observableArray([]);      
         }
         self[name].push.apply(self[name], mappedItems);
         self[name].valueHasMutated();
         */
    }

    self.initiateArrayFast = function (name, mappedItems) {
        for (j = 0; j < self.MyModels.length; j++) {
            var jname = self.MyModels[j]['name'];
            var type = self.MyModels[j]['type'];

            if (type == "fieldValues") {
                if (DM_IsSet(jname) && self.MyModels[j]["forModelname"] == name && DM_IsSet(self.MyModels[j]['isConnected']) && self.MyModels[j]['isConnected'] && DM_IsSet(self.MyModels[j]['forModelname'])
                    && DM_IsSet(self.MyModels[j]['ParentFieldName'])
                ) {

                    self[jname + "_ParentFieldName"] = self.MyModels[j]["ParentFieldName"];
                    self[jname + "_forModelname"] = self.MyModels[j]["forModelname"];

                    ko.utils.arrayForEach(mappedItems, function (item) {
                        self.AddSpecialMemberModel(name, item);
                    });
                }

            }
        }


        if (self[name] != null) {
            self[name].valueWillMutate();
            self[name].removeAll();
        }
        else {
            self[name] = ko.observableArray([]);
        }
        self[name].push.apply(self[name], mappedItems);
        self[name].valueHasMutated();

    }


    self.filterMyData = function (modelname) {
        self.mainFilter=modelname;
        if (self[modelname] != null && typeof (self[modelname] != "undefined")
            && self[modelname + '_filterRow'] != null && typeof (self[modelname + '_filterRow'] != "undefined")) {
            if (DM_IsSet(self[modelname + '_DefaultQuery']) && self[modelname + '_DefaultQuery'] == true) {
                filterow = self[modelname + '_filterRow']()[0];
                var senddata = {
                    'action': 'DMDynamicRequest',
                    'formaction': 'GetDataById',
                    'myurl': window.location.href,
                    'formParameters': { 'formName': modelname, 'formId': self.formName },


                    'filter': { 'name': modelname, 'data': ko.toJS(filterow) }

                };
                var funccall = function (resultOrigin) {
                    var n = resultOrigin.indexOf('{"error":');
                    var result = resultOrigin.substring(n, resultOrigin.length);
                    var results = JSON.parse(result);

                    if (typeof (results['error']) != 'undefined' && results['error'] != null) {
                        self[modelname + '_errorMessage'](results['error']);
                        $("body").css("cursor", "default");
                    }
                    else {
                        if (
                            typeof (results['updatedData']) != 'undefined' && results['updatedData'] != null) {
                            //  self[modelname+'_successMessage'](results['result']);
                            self.ReadData(results['updatedData']);

                            self[modelname + '_filterRow']([]);
                            self.AddSpecialMemberModel(modelname, filterow);
                            self[modelname + '_filterRow'].push(filterow);

                            self[modelname + "_isFilteredDefault"] = true;
                            self.filterMyDataHelper(modelname, filterow, "_newOne");
                            $("body").css("cursor", "default");
                        }
                    }
                };

                self[modelname + '_successMessage']();
                self[modelname + '_errorMessage']();
                $("body").css("cursor", "progress");
                applyToServer(funccall, funccall, senddata);
            }
            else {

                filterow = self[modelname + '_filterRow']()[0];
                self.filterMyDataHelper(modelname, filterow, "");
            }

        }
    }

    self.RestoreFilters = function () {
        for (i = 0; i < self.MyModels.length; i++) {
            thisModel = self.MyModels[i];
            modelname = self.MyModels[i]['name'];
            thistype = self.MyModels[i]['type'];
            if (DM_IsSet(self[modelname + '_connected']) && thistype == "group" && DM_IsSet(self[modelname + '_filterRow'])) {
                ko.utils.arrayForEach(self[modelname + '_connected'](), function (submodel) {
                    if (DM_IsSet(submodel['modelname'])) {
                        submodelName = submodel['modelname']();
                        type = "_" + submodel['type']();
                        if (DM_IsSet(submodelName) && DM_IsSet(self[submodelName]) && DM_IsSet(self[submodelName + type])) {
                            filterow = self[modelname + "_filterRow"]()[0];
                            filterowCopy = self[submodelName + type]()[0];
                            self.copySameParameters(filterow, filterowCopy, submodel);


                        }
                    }
                });
            }
        }
    }

    self.filterMyDataHelper = function (modelname, filterow, subtype) {
        if (DM_IsSet(self[modelname + '_connected'])) {
            ko.utils.arrayForEach(self[modelname + '_connected'](), function (submodel) {
                if (DM_IsSet(submodel['modelname'])) {
                    submodelName = submodel['modelname']();
                    type = "_" + submodel['type']();
                    if (type == "_group") {
                        type = "_filterRow";
                    }
                    if (DM_IsSet(submodelName) && DM_IsSet(self[submodelName]) && DM_IsSet(self[submodelName + type])) {
                        filterowCopy = self[submodelName + type]()[0];

                        self.copySameParameters(filterow, filterowCopy, submodel);
                        // self[submodelName+type+"Reserve"]=DM_Copy(filterowCopy,false);    
                        if (type == "_filterRow" && subtype == "") {
                            self[submodelName + "filterRowCopy"] = filterowCopy;
                            self.filterRow(submodelName, filterowCopy);
                        }
                        else {
                            self[submodelName + "_isFilteredDefault"] = true;
                            if (subtype == type || subtype == "") {
                                self[submodelName + type]([]);
                                self.AddSpecialMemberModel(submodelName, filterowCopy);
                                self[submodelName + type].push(filterowCopy);


                            }
                        }
                    }


                }
            });
        }
        if (subtype == "") {
            self.filterRow(modelname, filterow);
        }
    }

    self.copySameParameters = function (filterow, filterowCopy, connected) {
        parentField = connected['parentField']();
        childField = connected['childField']();

        if (DM_IsSet(filterow[parentField]) && typeof (filterowCopy[childField]) != "undefined") {
            filterowCopy[childField](filterow[parentField]());
            filterowCopy[childField].notifySubscribers();
        }

    }

    self.filterRow = function (modelname, filterow) {
        ko.utils.arrayForEach(self[modelname](), function (item) {
            item["isfiltermatch"](true);
            var props = Object.getOwnPropertyNames(item);
            for (var i = 0; i < props.length; i++) {
                propname = props[i];
                if (typeof (propname) != "undefined" && typeof (filterow[propname]) != "undefined"
                    && typeof (filterow[propname]()) != "undefined" && filterow[propname]() != null && propname.search("_model") == -1) {
                    if (String(item[propname]()).search(String(filterow[propname]())) == -1) {
                        item["isfiltermatch"](false);
                    }
                }
            }
        });

        self[modelname + "_isFiltered"] = true;
        self.changePage(modelname, 1);


    }

    self.filterByParameter = function (modelname, parameter, value) {
        if (self[modelname] != null && typeof (self[modelname] != "undefined")) {

            ko.utils.arrayForEach(self[modelname](), function (item) {
                item["isfiltermatch"](true);

                propname = parameter;
                if (typeof (propname) != "undefined"
                    && typeof (item[propname]()) != "undefined" && item[propname]() != null && propname.search("_model") == -1) {
                    if (String(item[propname]()).search(String(value)) == -1) {
                        item["isfiltermatch"](false);
                    }
                }

            });
            self[modelname + 'pagetrigger'].notifySubscribers();
            self.changePage(modelname, 1);
        }
    }



    //initiate
    self.ReadData = function (models) {
        /* models.sort(function(a,b){
         if (a['type']=="group"  && b['type']=="fieldValues") return false;
         if (a['type']=="fieldValues"  && b['type']=="group") return true;
         return true;
         })  ;
         */
        if (!DM_IsSet(self.MyModels)) {
            self.MyModels = models;
        }
        else {

            //renew only new models
            for (var i = 0; i < models.length; i++) {
                var name = models[i]['name'];
                var type = models[i]['type'];
                for (var j = 0; j < self.MyModels.length; j++) {
                    var jname = self.MyModels[j]['name'];
                    var jtype = self.MyModels[j]['type'];
                    if (name == jname && type == jtype) {
                        self.MyModels[j] = models[i];
                    }
                }

            }

        }
        for (var i = 0; i < models.length; i++) {
            var data = models[i]['data'];
            var name = models[i]['name'];
            var type = models[i]['type'];

            if (type == "fieldValues") {

                var mappedItems = $.map(data, function (item) {
                    var myItem = new DM_Value(item);
                    return myItem;
                });
                /* 
                 var mappedItems = ko.utils.arrayMap(data, function(item) {
                 var myItem = new DM_Value(item) ;
                 return myItem;
                 });
                 */
                self.initiateArray(name, mappedItems)

            }
        }

        for (var i = 0; i < models.length; i++) {
            var data = models[i]['data'];
            var name = models[i]['name'];
            var type = models[i]['type'];
            var pagesize = models[i]['pagesize'];
            var pagenum = models[i]['pagenum'];

            var validators = models[i]['validators'];
            var formulas = models[i]['formulas'];
            var connected = models[i]['connected'];
            var applyFilters = models[i]['applyFilters'];
            var defaultQuery = models[i]['applyDefaultQuery'];
            var isempty = models[i]['empty'];



            if (DM_IsSet(name) && !DM_IsSet(self[name + '_errorMessage'])) {
                self[name + '_errorMessage'] = ko.observable();
                self[name + '_errorMessage_computed'] = ko.computed(function () { self[this + '_errorMessage'](); return self[this + '_errorMessage'](); }, name);
            }
            if (DM_IsSet(name) && !DM_IsSet(self[name + '_successMessage'])) {
                self[name + '_successMessage'] = ko.observable();
                self[name + '_successMessage_computed'] = ko.computed(function () { self[this + '_successMessage'](); return self[this + '_successMessage'](); }, name);
            }
            switch (type) {

                case "group":
                    if (DM_IsSet(defaultQuery)) {
                        self[name + '_DefaultQuery'] = defaultQuery;
                    }
                    self[name + 'trigger'] = ko.observable();
                    self[name + 'pagetrigger'] = ko.observable();

                    var mappedItems = $.map(data, function (item) {
                        var myItem = new DM_Item(item);
                        DM_AddValidators(myItem, validators);
                        DM_AddFormulas(myItem, formulas);
                        self.AddSpecialMemberModel(name, myItem);
                        return myItem;
                    });
                    /*
                     var mappedItems =  ko.utils.arrayMap(data, function(item) {
                     var myItem = new DM_Item(item) ;
                     DM_AddValidators(myItem,validators);
                     DM_AddFormulas(myItem,formulas);
                     self.AddSpecialMemberModel(name,myItem);
                     return myItem;
                     });
                     */

                    self.initiateArrayFast(name, mappedItems);

                    if (DM_IsSet(connected)) {
                        /*
                         var connectedModels = ko.utils.arrayMap(connected, function(item) {
                         var model = new DM_Connected(item);
                         return model;
                         });
                         */
                        var connectedModels = $.map(connected, function (item) {
                            var model = new DM_Connected(item);
                            return model;
                        });

                        if (connectedModels.length > 0) {
                            self.initiateArray(name + '_connected', connectedModels);
                        }
                    }

                    self[name + 'pagesize'] = pagesize;
                    if (self[name + 'filteredlength'] == null || typeof (self[name + 'filteredlength']) == "undefined") {
                        self[name + 'filteredlength'] = ko.computed(function () {
                            modelName = this;
                            self[modelName + 'trigger']();
                            count = 0;
                            ko.utils.arrayForEach(self[modelName](), function (item) {
                                if (item["isfiltermatch"]() == true)
                                    count++;
                            });
                            self[modelName + 'pagetrigger'].notifySubscribers();
                            return count;
                        }, name);
                    }
                    if (self[name + 'mypages'] == null || typeof (self[name + 'mypages']) == "undefined") {
                        self[name + 'mypages'] = ko.computed(function () {
                            modelName = this;
                            self[modelName + 'pagetrigger']();
                            pagesize = self[modelName + 'pagesize'];

                            var pagesnum = Math.floor(self[modelName + 'filteredlength']() / pagesize);
                            if (self[modelName + 'filteredlength']() > pagesnum * pagesize) {
                                pagesnum++;
                            }
                            var pages = ko.observableArray([]);
                            for (var i = 1; i <= pagesnum; i++) {
                                pages.push(new DM_Page(i));
                            }
                            return pages;


                        }, name);
                    }
                    if (!DM_IsSet(self[name + '_empty'])) {
                        self[name + '_empty'] = ko.observable(isempty);
                    }
                    else {
                        self[name + '_empty'](isempty);
                    }
                    self[name + '_empty_computed'] = ko.computed(function () { if (self[this + '_empty']()) return false; else return true; }, name);
                    if (!DM_IsSet(self[name + 'pagenum'])) {
                        self[name + 'pagenum'] = ko.observable(pagenum);
                    }
                    else {
                        self[name + 'pagenum'](pagenum);
                    }

                    self[name + 'pagenumComputed'] = ko.computed(function () {
                        modelName = this;
                        self[modelName + 'trigger'].notifySubscribers();
                        return self[modelName + 'pagenum']();
                    }, name);
                    self.filterByPage(name);


                    defaultFilters = models[i]['defaultFilters'];
                    if (DM_IsSet(applyFilters) && applyFilters == true && DM_IsSet(defaultFilters)) {

                        self[name + "_toFilter"] = true;
                        self.CreateSpecialArray(name, "_filterRow", data, validators, false, defaultFilters, formulas);

                    }
                    else {
                        self.CreateSpecialArray(name, "_filterRow", data, validators, false, null, formulas);
                    }

                    self.CreateSpecialArray(name, "_new", data, validators, true, null, formulas);


                    break;
                case "search":
                    self.CreateSpecialArray(name, "_search", data, validators, false, null, formulas);
                    break;
                case "newOne":
                    self.CreateSpecialArray(name, "_newOne", data, validators, true, null, formulas);
                    // self.CreateSpecialArray(name,"_newOne",data,validators,true,null,formulas);

                    break;
            }
        }
        self.RestoreFilters();
        for (var i = 0; i < models.length; i++) {

            var name = models[i]['name'];
            var type = models[i]['type'];

            //go Over connected model to group one, and connect parnet filter to the model.
            if (type == "group" && DM_IsSet(self[name + "_connected"]) && DM_IsSet(self[name + "_DefaultQuery"]) && self[name + "_DefaultQuery"] == true) {
                for (var s = 0; s < self[name + "_connected"]().length; s++) {
                    var connectedModel = self[name + "_connected"]()[0];
                    submodelName = connectedModel['modelname']();
                    if (DM_IsSet(self[name + "_filterRow"]) && self[name + "_filterRow"]().length > 0) {
                        self[submodelName + "_parentFilter"] = self[name + "_filterRow"]()[0];
                        self[submodelName + "_sendParentFilter"] = true;
                        self[submodelName + "_parentFilterName"] = name;
                    }
                }

            }




            if (self[name + "_toFilter"] == true) {
                if (DM_IsSet(self[name + "_DefaultQuery"]) && self[name + "_DefaultQuery"] == true) {
                    self.filterMyDataHelper(name, self[name + "_filterRow"]()[0], "_newOne");
                }
                else {
                    self.filterMyDataHelper(name, self[name + "_filterRow"]()[0], "");
                }
            }
        }


    }

    //initiation!
    if (!isJson) {
        var models = JSON.parse(result);
    }
    else {
        var models = result;
    }
    self.ReadData(models);




    self.addToForm = function (data) {
        if (DM_IsSet(data["field_29"]) && data["field_29"]() != "") {
            MyText = "<p><strong>[@label_id_" + data["field_29"]() + "@]</strong><br/>" + "[@field_id_" + data["field_29"]() + "@]<p>";
            tinyMCE.activeEditor.execCommand('mceInsertContent', false, MyText);
        }
    }

    self.addToFormShortcode = function (data, fieldCode, shortcodePattern) {
        if (DM_IsSet(data[fieldCode]) && data[fieldCode]() != "") {
            MyText = shortcodePattern.replace("[$ShortCode$]", data[fieldCode]());

            tinyMCE.activeEditor.execCommand('mceInsertContent', false, MyText);
        }
    }

    self.addToTextAreaShortcode = function (data, fieldCode, textAreaCode, shortcodePattern) {
        if (DM_IsSet(data[fieldCode]) && data[fieldCode]() != "" && DM_IsSet(data[textAreaCode])) {
            MyText = shortcodePattern.replace("[$ShortCode$]", data[fieldCode]());
            oldCode = data[textAreaCode]();
            NewCode = oldCode + MyText;
            data[textAreaCode](NewCode);
        }
    }

    self.replaceTextShortcode = function (data, fieldCode, textAreaCode, shortcodePattern) {
        if (DM_IsSet(data[fieldCode]) && data[fieldCode]() != "" && DM_IsSet(data[textAreaCode])) {
            MyText = shortcodePattern.replace("[$ShortCode$]", data[fieldCode]());

            data[textAreaCode](MyText);
        }
    }


    self.searchData = function (modelname) {

        var senddata = {
            'action': 'DMDynamicRequest',
            'formaction': 'Search',
            'formParameters': { 'formName': modelname, 'formId': self.formName },
            'myurl': window.location.href,
            'model': { 'name': modelname, 'data': ko.toJS(self[modelname + '_search']) }

        };
        var funccall = function (resultOrigin) {
            var n = resultOrigin.indexOf('{"error":');
            var result = resultOrigin.substring(n, resultOrigin.length);
            var results = JSON.parse(result);
            if (typeof (results['error']) != 'undefined' && results['error'] != null) {
                self[modelname + '_errorMessage'](results['error']);
                $("body").css("cursor", "default");
            }
            else {
                if (typeof (results['result']) != 'undefined' && results['result'] != null &&
                    typeof (results['updatedData']) != 'undefined' && results['updatedData'] != null) {
                    self[modelname + '_successMessage'](results['result']);

                    self.ReadData(results['updatedData']);
                    $("body").css("cursor", "default");
                }
            }
        };
        self[modelname + '_successMessage']();
        self[modelname + '_errorMessage']();
        $("body").css("cursor", "progress");
        applyToServer(funccall, funccall, senddata);

    }

    self.addData = function (modelname) {
        var filterow = null;
        var filterName = null;
        var modelSendname = null;
        var filterData = null;
        if ((DM_IsSet(self[modelname + '_DefaultQuery']) && self[modelname + '_DefaultQuery'] == true) || (DM_IsSet(self[modelname + "_sendParentFilter"]) && self[modelname + "_sendParentFilter"] == true)) {



            if (DM_IsSet(self[modelname + "_sendParentFilter"]) && self[modelname + "_sendParentFilter"] == true) {
                filterow = self[modelname + '_parentFilter'];
                filterName = self[modelname + '_parentFilterName'];
            }
            else {

                filterow = self[modelname + '_filterRow']()[0];
                filterName = modelname;
            }
            filterData = ko.toJS(filterow);
        }
        if (self[modelname + '_newOne'] != null && typeof (self[modelname + '_newOne'] != "undefined") && self.isValid(modelname + '_newOne')) {
            modelSendname = modelname;
        }
        else {

        }

        var senddata = {
            'action': 'DMDynamicRequest',
            'formaction': 'AddNew',
            'myurl': window.location.href,
            'formParameters': { 'formName': modelname, 'formId': self.formName },
            'returnFormParameters': { 'formId': self.formName },
            'currUrl': window.location.href,
            'filter': { 'name': filterName, 'data': filterData },
            'model': { 'name': modelSendname, 'data': ko.toJS(self[modelname + '_newOne']) }

        };


        var funccall = function (resultOrigin) {
            var n = resultOrigin.indexOf('{"error":');
            var result = resultOrigin.substring(n, resultOrigin.length);
            var results = JSON.parse(result);
            if (typeof (results['error']) != 'undefined' && results['error'] != null) {
                self[modelname + '_errorMessage'](results['error']);
                $("body").css("cursor", "default");
            }
            else {
                if (typeof (results['result']) != 'undefined' && results['result'] != null &&
                    typeof (results['updatedData']) != 'undefined' && results['updatedData'] != null) {
                    self[modelname + '_successMessage'](results['result']);

                    self.ReadData(results['updatedData']);
                    $("body").css("cursor", "default");
                }
            }
        };
        self[modelname + '_successMessage']();
        self[modelname + '_errorMessage']();
        $("body").css("cursor", "progress");
        applyToServer(funccall, funccall, senddata);

    }

    self.CreateDefaultForm = function (modelnamefield, data, field, colorField) {
        var answer = confirm("Are you sure you want to recode the design?");
        if (answer) {
            if (DM_IsSet(data) && DM_IsSet(modelnamefield) && DM_IsSet(data[modelnamefield]) && DM_IsSet(field) && DM_IsSet(data[field]) && DM_IsSet(colorField) && DM_IsSet(data[colorField])) {
                var senddata = {
                    'action': 'DMDynamicRequest',
                    'formaction': 'CreateDefaultForm',
                    'formParameters': { 'formId': self.formName },
                    'entityId': data[modelnamefield],
                    'color': data[colorField]
                };

                var funccall = function (resultOrigin) {
                    var n = resultOrigin.indexOf('{"error":');
                    var result = resultOrigin.substring(n, resultOrigin.length);
                    var results = JSON.parse(result);
                    if (typeof (results['error']) != 'undefined' && results['error'] != null) {
                        alert(results['error']);
                    }
                    else {

                        data[field](results['result']);
                    }
                };
                applyToServer(funccall, funccall, senddata);
            }
        }
    }

    self.remove = function (modelname, data) {
        //TODO: add validation!
        if (typeof (self[modelname]) != "undefined") {
            self[modelname].destroy(data);
            self[modelname + 'pagetrigger'].notifySubscribers();
        }

    }



    self.showMe = function (data) {
        data["ShowExtend"](true);
    }

    self.hideMe = function (data) {
        data["ShowExtend"](false);
    }

    self.saveItemData = function (modelname, data) {
        if (self[modelname] != null && typeof (self[modelname] != "undefined")) {

            if (self[modelname + "_isFilteredDefault"] == true) {

                filterow = self[modelname + '_filterRow']()[0];
                var senddata = {
                    'action': 'DMDynamicRequest',
                    'formaction': 'SaveData',
                    'myurl': window.location.href,
                    'formParameters': { 'formName': modelname, 'formId': self.formName },
                    'returnFormParameters': { 'formId': self.formName },
                   /* 'filter': { 'name': modelname, 'data': ko.toJS(filterow) },*/
                    'model': { 'name': modelname, 'data': ko.toJS(data) }


                };



            } else {
                var senddata = {
                    'action': 'DMDynamicRequest',
                    'formaction': 'SaveData',
                    'myurl': window.location.href,
                    'formParameters': { 'formName': modelname, 'formId': self.formName },
                    'returnFormParameters': { 'formId': self.formName },
                    'model': { 'name': modelname, 'data': ko.toJS(data) }


                };
            }


            var funccall = function (resultOrigin) {
                var n = resultOrigin.indexOf('{"error":');
                var result = resultOrigin.substring(n, resultOrigin.length);
                var results = JSON.parse(result);
                if (typeof (results['error']) != 'undefined' && results['error'] != null) {
                    self[modelname + '_errorMessage'](results['error']);
                    $("body").css("cursor", "default");
                }
                else {
                    if (typeof (results['result']) != 'undefined' && results['result'] != null &&
                        typeof (results['updatedData']) != 'undefined' && results['updatedData'] != null) {
                        self[modelname + '_successMessage'](results['result']);

                        if (self[modelname + "_isFilteredDefault"] == true)
                        {
                            if(typeof(self[modelname + "filterRowCopy"])!="undefined" ) {
                                self.ReadData(results['updatedData']);
                                filterowCopy=self[submodelName + "filterRowCopy"] ;
                                self.filterRow(modelname, filterowCopy);
                            }
                            else {
                                if (typeof(self.mainFilter) != "undefined") {
                                    self.filterMyData(self.mainFilter);
                                }
                                else {
                                    filterow = self[modelname + '_filterRow']()[0];
                                    self.filterRow(modelname, filterow);
                                }
                            }

                        }
                        else {
                            self.ReadData(results['updatedData']);
                        }

                        $("body").css("cursor", "default");
                    }
                }
            };
            self[modelname + '_successMessage']();
            self[modelname + '_errorMessage']();
            $("body").css("cursor", "progress");
            applyToServer(funccall, funccall, senddata);
        }
    }

    self.deleteItemData = function (modelname, data) {



        var answer = confirm("Are you sure you want to delete this row?");
        if (answer) {


            var filterow = null;
            var filterName = null;
            var modelSendname = null;
            var filterData = null;
            if ((DM_IsSet(self[modelname + '_DefaultQuery']) && self[modelname + '_DefaultQuery'] == true) || (DM_IsSet(self[modelname + "_sendParentFilter"]) && self[modelname + "_sendParentFilter"] == true)) {
                if (DM_IsSet(self[modelname + "_sendParentFilter"]) && self[modelname + "_sendParentFilter"] == true) {
                    filterow = self[modelname + '_parentFilter'];
                    filterName = self[modelname + '_parentFilterName'];
                }
                else {

                    filterow = self[modelname + '_filterRow']()[0];
                    filterName = modelname;
                }
                filterData = ko.toJS(filterow);
            }
            if (self[modelname + '_newOne'] == null && typeof (self[modelname + '_newOne'] != "undefined") && self.isValid(modelname + '_newOne')) {
            }
            else {
                modelSendname = modelname;
            }

            var senddata = {
                'action': 'DMDynamicRequest',
                'formaction': 'DeleteData',
                'myurl': window.location.href,
                'formParameters': { 'formName': modelname, 'formId': self.formName },
                'returnFormParameters': { 'formId': self.formName },
                'currUrl': window.location.href,
                'filter': { 'name': filterName, 'data': filterData },
                'model': { 'name': modelSendname, 'data': ko.toJS(data) }

            };





            var funccall = function (resultOrigin) {
                var n = resultOrigin.indexOf('{"error":');
                var result = resultOrigin.substring(n, resultOrigin.length);
                var results = JSON.parse(result);
                if (typeof (results['error']) != 'undefined' && results['error'] != null) {
                    self[modelname + '_errorMessage'](results['error']);
                    $("body").css("cursor", "default");

                }
                else {
                    if (typeof (results['result']) != 'undefined' && results['result'] != null &&
                        typeof (results['updatedData']) != 'undefined' && results['updatedData'] != null) {
                        self[modelname + '_successMessage'](results['result']);

                        self.ReadData(results['updatedData']);
                        $("body").css("cursor", "default");
                    }
                }
            };
            self[modelname + '_successMessage']();
            self[modelname + '_errorMessage']();
            $("body").css("cursor", "progress");
            applyToServer(funccall, funccall, senddata);
        }

    }



    self.addNewRow = function (modelname) {
        if (self[modelname] != null && typeof (self[modelname] != "undefined")
            && self[modelname + '_new'] != null && typeof (self[modelname + '_new']) != "undefined" && self.isValid(modelname + '_new')) {
            var specialname = "_new";
            var addingRow = self[modelname + '_new']()[0];
            var newRow = new DM_ItemFromItem(addingRow);
            newRow['newRow'](true);
            DM_AddValidators(newRow, self[modelname + specialname + 'Validators']);
            DM_AddFormulas(newRow, self[modelname + specialname + 'Formulas']);
            self[modelname].push(newRow);

            //empty new row
            self[modelname + '_new']([]);
            var specialMember = new DM_Copy(self[modelname + '_newReserve'], false);

            DM_AddValidators(specialMember, self[modelname + specialname + 'Validators']);
            DM_AddFormulas(specialMember, self[modelname + specialname + 'Formulas']);
            self.AddSpecialMemberModel(modelname, specialMember);
            self[modelname + '_new'].push(specialMember);
            self[modelname + 'pagetrigger'].notifySubscribers();
        }
    }

    self.reset = function (modelname, specialname) {
        if (self[modelname] != null && typeof (self[modelname] != "undefined")
            && self[modelname + specialname] != null && typeof (self[modelname + specialname]) != "undefined") {


            var specialMember = new DM_Copy(self[modelname + specialname + 'Reserve'], false);
            if ((specialname == "_new" || specialname == "_newOne") && (self[modelname + '_isFiltered'] || self[modelname + '_isFilteredDefault'])) {
                filterRow = self[modelname + '_filterRow']()[0];
                DM_CopyFromFilter(specialMember, filterRow);
            }

            DM_AddValidators(specialMember, self[modelname + specialname + 'Validators']);
            DM_AddFormulas(specialMember, self[modelname + specialname + 'Formulas']);
            newArray = ko.observableArray();

            //specialMember["ShowExtend"](true); 
            // specialMember["ShowExtend"].notifySubscribers();


            self[modelname + specialname]([]);
            self.AddSpecialMemberModel(modelname, specialMember);
            self[modelname + specialname].push(specialMember);
            self.showMe(self[modelname + specialname]()[0]);


        }
    }

    self.GetData = function () {
        var senddata = {
            'action': 'DMDynamicRequest',
            'formaction': 'GetDataById',
            'myurl': window.location.href,
            'formParameters': {
                'formId': self.formName
            }

        };
        var funccall = function (result) {
            if (result.search("error") > -1) {
                alert(result);
            }
            else {
                var models = JSON.parse(result);
                self.ReadData(models);
            }
        };
        applyToServer(funccall, funccall, senddata);
    }

    self.sortSpecial = function (modelname, field) {
        if (self[modelname] != null && typeof (self[modelname] != "undefined")) {
            if (self[modelname + "_" + field + 'sorted'] != 'Asc') {
                self[modelname].sort(function (a, b) {
                    return a[field]() > b[field]() ? 1 : -1;
                });
                self[modelname + "_" + field + 'sorted'] = 'Asc';
                self[modelname + "_lastfilter"] = field;
            }
            else {
                self[modelname].sort(function (a, b) {
                    return a[field]() > b[field]() ? -1 : 1;
                });
                self[modelname + "_" + field + 'sorted'] = 'Desc';
                self[modelname + "_lastfilter"] = field;
            }
            self[modelname + 'pagetrigger'].notifySubscribers();
        }

    }

    self.saveData = function (modelName) {
        if (typeof (self[modelName]) != 'undefined' && self[modelName] != null) {
            var filterow = null;
            var filterName = null;
            var modelSendname = null;
            var filterData = null;
            if ((DM_IsSet(self[modelname + '_DefaultQuery']) && self[modelname + '_DefaultQuery'] == true) || (DM_IsSet(self[modelname + "_sendParentFilter"]) && self[modelname + "_sendParentFilter"] == true)) {
                if (DM_IsSet(self[modelname + "_sendParentFilter"]) && self[modelname + "_sendParentFilter"] == true) {
                    filterow = self[modelname + '_parentFilter'];
                    filterName = self[modelname + '_parentFilterName'];
                }
                else {

                    filterow = self[modelname + '_filterRow']()[0];
                    filterName = modelname;
                }
                filterData = ko.toJS(filterow);
            }
            if (self[modelname + '_newOne'] == null && typeof (self[modelname + '_newOne'] != "undefined") && self.isValid(modelname + '_newOne')) {
            }
            else {
                modelSendname = modelname;
            }
//,'filter': { 'name': filterName, 'data': filterData }
            var senddata = {
                'action': 'DMDynamicRequest',
                'formaction': 'SaveChanges',
                'formParameters': { 'formName': modelname, 'formId': self.formName },
                'returnFormParameters': { 'formId': self.formName },
                'currUrl': window.location.href,
                'myurl': window.location.href,
                'model': { 'name': modelSendname, 'data': ko.toJS(data) }


            };
            if (self.isValid(modelName)) {

                var funccall = function (resultOrigin) {
                    var n = resultOrigin.indexOf('{"error":');
                    var result = resultOrigin.substring(n, resultOrigin.length);
                    var results = JSON.parse(result);
                    if (typeof (results['error']) != 'undefined' && results['error'] != null) {
                        self[modelName + '_errorMessage'](results['error']);
                        $("body").css("cursor", "default");

                    }
                    else {
                        if (typeof (results['result']) != 'undefined' && results['result'] != null &&
                            typeof (results['updatedData']) != 'undefined' && results['updatedData'] != null) {
                            self[modelName + '_successMessage'](results['result']);
                            self.ReadData(results['updatedData']);
                            $("body").css("cursor", "default");

                        }
                    }
                };
                self[modelName + '_successMessage']();
                self[modelName + '_errorMessage']();
                $("body").css("cursor", "progress");
                applyToServer(funccall, funccall, senddata);
            }
        }
        else
            alert('no such model name!' + modelName);
    }

    self.Preview = function (modelName, divName) {
        //send ModelName with preview Action
        //with result Load html to someDiv
        //Create New Model
        //Load Model to this Div
        //on close div remove model and div content
    }
}

function DM_getUrlParameter(sParam) {
    var sPageURL = window.location.search.substring(1);
    var sURLVariables = sPageURL.split('&');
    for (var i = 0; i < sURLVariables.length; i++) {
        var sParameterName = sURLVariables[i].split('=');
        if (sParameterName[0] == sParam) {
            return sParameterName[1];
        }
    }
}


function DefineModels() {
    //TODO: Change to something dynamic

    $("[id*='form_id_'] ").each(function () {
        formName = this.id;
        formNameArr = formName.split("_");
        formId = formNameArr[2];
        formparams = DM_getUrlParameter("form_" + formId + "_params");

        var senddata = {
            'action': 'DMDynamicRequest',
            'formaction': 'GetDataById',
            'myurl': window.location.href,
            'formParameters': {
                'formId': formId,
                'formparams': formparams
            }

        };
        var funccall = function (result) {
            if (result.search("error") > -1) {
                alert(result);
            }
            else {
                var myelement = document.getElementById(id);

                ko.applyBindings(new DM_Model(result, formName), this);
            }
        }
        applyToServer(funccall, funccall, senddata);
    });

}

function applyToServer(successcall, failcall, data) {
    var myurl = DM_getUrl();
    $.ajax({
        url: myurl,
        type: 'POST',
        data: data,
        success: successcall,
        fail: failcall
    });
}



function DM_ShowHideOnMouse(obj, Myvar) {
    if (obj != null && typeof (obj) != 'undefined') {
        var divName = $(obj).attr("id") + "_help";
        if (divName != null && typeof (divName) != "undefined") {
            helpdiv = document.getElementById(divName);
            if (helpdiv != null && typeof (helpdiv) != "undefined") {
                if (Myvar) {

                    helpdiv.style.visibility = "visible";
                    helpdiv.style.height = "inherit";
                    helpdiv.style.position = "absolute";

                }
                else {
                    helpdiv.style.visibility = "hidden";
                    helpdiv.style.height = "0";
                    helpdiv.style.position = "absolute";
                }
            }
        }
    }
}