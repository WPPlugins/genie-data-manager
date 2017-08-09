function DM_Connected(dataItem) {
    var props = Object.getOwnPropertyNames(dataItem);
    var Item = this;
    Item['modelname'] = ko.observable(dataItem['modelname']);
    Item['parentField'] = ko.observable(dataItem['parentField']);
    Item['childField'] = ko.observable(dataItem['childField']);
    Item['type'] = ko.observable(dataItem['type']);
}
function isNumber(n) {
    return !isNaN(parseFloat(n)) && isFinite(n);
}

function DM_AddFormulas(datarow, formulas) {
    if (DM_IsSet(formulas)) {
        var props = Object.getOwnPropertyNames(datarow);
        for (var i = 0; i < props.length; i++) {
            var propertyName = props[i];
            for (var j = 0; j < formulas.length; j++) {
                if (formulas[j]['current'] == propertyName) {
                    var formulaD = formulas[j];
                    datarow[propertyName + '_formula'] = formulaD;
                    datarow[propertyName + '_computed'] = ko.computed({
                        read: function () {
                            thisPropertyName = this;
                            formulaDetails = datarow[thisPropertyName + '_formula'];
                            fexpression = formulaDetails['formula'];
                            expressionChanged = false;
                            hasOldValues = false;
                            expressionNulled = false;
                            evaluate = false;
                            isallnums = true;
                            for (var t = 0; t < formulaDetails['fields'].length; t++) {
                                fieldName = formulaDetails['fields'][t];
                                if (DM_IsSet(datarow[fieldName])) {
                                    fieldValue = datarow[fieldName]();
                                    if (fexpression.indexOf("[@" + fieldName + "@]") != -1 && !$.isNumeric(fieldValue)) {
                                        isallnums = false;
                                    }
                                    fexpression = fexpression.replace("[@" + fieldName + "@]", fieldValue);

                                    if (typeof (datarow[fieldName + "_OldValue"]) == "undefined") {
                                        evaluate = true;
                                    }
                                    if (DM_IsSet(datarow[fieldName + "_OldValue"])) {
                                        hasOldValues = true;
                                        if (datarow[fieldName + "_OldValue"]() != datarow[fieldName]() && !(datarow[fieldName]() == "" && datarow[fieldName + "_OldValue"]() == "0")) {
                                            expressionChanged = true;
                                        }
                                        if (datarow[fieldName]() == "" && datarow[fieldName + "_OldValue"]() == "0") {
                                            expressionNulled = true;
                                        }
                                    }
                                }
                            }
                            value = fexpression;
                            if (DM_IsSet(formulaDetails["isMatematic"]) && formulaDetails["isMatematic"] == "1" && isallnums) {
                                try {
                                    value = eval(fexpression);
                                }
                                catch (e) {

                                }
                            }
                            if (!hasOldValues || expressionChanged || evaluate) {
                                datarow[thisPropertyName](value);
                                datarow[thisPropertyName].notifySubscribers();
                                return value;
                            }
                            else {
                                if (expressionNulled && DM_IsSet(datarow[thisPropertyName + "_OldValue"]) && datarow[thisPropertyName]() != datarow[thisPropertyName + "_OldValue"]()) {
                                    datarow[thisPropertyName](datarow[thisPropertyName + "_OldValue"]());
                                }
                                return datarow[thisPropertyName]();
                            }
                        }, write: function (value) { datarow[this](value); }
                    }, propertyName);


                }
            }

        }
    }
}

function DM_AddValidators(datarow, validators) {

    for (var i = 0; i < validators.length; i++) {

        var propertyname = validators[i]['name'];
        var validatortype = validators[i]['validator'];
        var Mymessage = validators[i]['message'];
        if (typeof (datarow[propertyname]) != "undefined") {
            switch (validatortype) {
                case 'required':
                    datarow[propertyname].extend({
                        required: {
                            params: true,
                            message: Mymessage
                        }

                    });
                    break;
                case 'date':
                    datarow[propertyname].extend({
                        date: {
                            params: true,
                            message: Mymessage
                        }
                    });
                    break;
                case 'number':
                    datarow[propertyname].extend({
                        number: {
                            params: true,
                            message: Mymessage
                        }
                    });
                    break;
                case 'email':
                    datarow[propertyname].extend({
                        email: {
                            params: true,
                            message: Mymessage
                        }
                    });
                    break;
            }
        }
    }
    datarow['errors'] = ko.validation.group(datarow);
    datarow['isValid'] = ko.computed(function () {
        if (this.errors().length == 0) {
            return true;
        }
        else {
            return false;
        }
    }, datarow);

    datarow['showErrors'] = function () {
        datarow.errors.showAllMessages();
    }
}

function DM_Page(num) {
    Item = this;
    Item['num'] = ko.observable(num);
    Item['currNum'] = ko.computed(
        function () {
            return Item['num']();
        });
}

function DM_Connection(datarow) {
    var props = Object.getOwnPropertyNames(datarow);
    var Item = this;
    for (var i = 0; i < props.length; i++) {
        var propertyName = props[i];
        if (typeof (propertyName) == "undefined")
            continue;
        Item[propertyName] = ko.observable();
    }
}/**
 * Created by lubchik on 5/29/2017.
 */
