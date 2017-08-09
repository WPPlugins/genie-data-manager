function DM_IsSet(variable) {
    if (variable != null && typeof (variable) != "undefined") {
        return true;
    }
    return false;
}

function DM_Item(datarow) {
    var props = Object.getOwnPropertyNames(datarow);
    var Item = this;
    for (var i = 0; i < props.length; i++) {
        var propertyName = props[i];

        if (typeof (propertyName) == "undefined")
            continue;
        Item[propertyName] = ko.observable(datarow[props[i]]);
        Item[propertyName + "_computed"] = ko.computed(function () {
            propName = this;
            return Item[propName]();

        }, propertyName);
        Item[propertyName + "_OldValue"] = ko.observable(datarow[props[i]]);
    }
    Item["isvisible"] = ko.observable(true);
    Item["isfiltermatch"] = ko.observable(true);
    Item["newRow"] = ko.observable(false);
    Item["isOnPage"] = ko.observable(false);
    Item["ShowExtend"] = ko.observable(false);
    Item["ShowExtend_computed"] = ko.computed(function () { return Item["ShowExtend"](); });

}

function DM_Value(value) {
    var Item = this;
    Item["value"] = ko.observable(value["fieldValue"]);
    Item["fieldValue"] = ko.observable(value["fieldValue"]);
    Item["fieldText"] = ko.observable(value["fieldText"]);
    Item["id"] = ko.observable(value["id"]);
    if (DM_IsSet(value["hasExtra"]) && value["hasExtra"] && DM_IsSet(value["extraValue"])) {
        Item[value["extraValue"]] = ko.observable(value[value["extraValue"]]);
    }
    Item["isfiltermatch"] = ko.observable(true);
    Item["ShowExtend"] = ko.observable(false);
    Item["ShowExtend_computed"] = ko.computed(function () { return Item["ShowExtend"](); });
    //valuesParentFieldId
}

function DM_ItemFromItem(dmSpecialItem) {
    var props = Object.getOwnPropertyNames(dmSpecialItem);
    var Item = this;
    for (var i = 0; i < props.length; i++) {
        var propertyName = props[i];

        if (typeof (propertyName) == "undefined")
            continue;
        Item[propertyName] = ko.observable(dmSpecialItem[props[i]]());
        Item[propertyName + "_OldValue"] = ko.observable(dmSpecialItem[props[i]]());
    }
    Item["isvisible"] = ko.observable(true);
    Item["isfiltermatch"] = ko.observable(true);
    Item["newRow"] = ko.observable(false);
    Item["isOnPage"] = ko.observable(false);
    Item["ShowExtend"] = ko.observable(false);
    Item["ShowExtend_computed"] = ko.computed(function () { return Item["ShowExtend"](); });
}

function DM_SpecialFilter(datarow) {
    var props = Object.getOwnPropertyNames(datarow);
    var Item = this;
    for (var i = 0; i < props.length; i++) {
        var propertyName = props[i];

        if (typeof (propertyName) == "undefined")
            continue;

        Item[propertyName] = ko.observable();
        Item[propertyName + "_computed"] = ko.computed(function () {
            propName = this;
            return Item[this]();

        }, propertyName);

    }
    Item["ShowExtend"] = ko.observable(false);
    Item["ShowExtend_computed"] = ko.computed(function () { return Item["ShowExtend"](); });
}

function DM_Special(datarow) {
    var props = Object.getOwnPropertyNames(datarow);
    var Item = this;

    for (var i = 0; i < props.length; i++) {
        var propertyName = props[i];

        if (typeof (propertyName) == "undefined")
            continue;

        Item[propertyName] = ko.observable(datarow[propertyName]);
        Item[propertyName + "_computed"] = ko.computed(function () {
            propName = this;
            return Item[this]();

        }, propertyName);
    }
    Item["ShowExtend"] = ko.observable(false);
    Item["ShowExtend_computed"] = ko.computed(function () { return Item["ShowExtend"](); });
}

function DM_Copy(dataItem, ShowExtend) {
    var props = Object.getOwnPropertyNames(dataItem);

    var Item = this;

    for (var i = 0; i < props.length; i++) {
        var propertyName = props[i];

        if (typeof (propertyName) == "undefined")
            continue;

        try {
            Item[propertyName] = ko.observable(dataItem[propertyName]());
        }
        catch (e) {
        }
    }
    if (typeof (Item["ShowExtend"]) != "undefined") {
        Item["ShowExtend_computed"] = ko.computed(function () { return Item["ShowExtend"](); });
    }
    if (typeof (ShowExtend) != "undefined" && ShowExtend != null) {
        Item["ShowExtend"](ShowExtend);
        Item["ShowExtend"].notifySubscribers();
    }
}

function DM_CopyFromFilter(dataNew, dataFilter) {
    var props = Object.getOwnPropertyNames(dataFilter);
    var Item = dataNew;
    for (var i = 0; i < props.length; i++) {
        var propertyName = props[i];

        if (typeof (propertyName) == "undefined")
            continue;
        if (DM_IsSet(dataFilter[propertyName]) && dataFilter[propertyName]() != "" && DM_IsSet(Item[propertyName])) {
            try {
                if (propertyName.indexOf("_computed") == -1 && propertyName.indexOf("Model") == -1) {
                    var value = dataFilter[propertyName]();
                    if (value != "" && value != null) {
                        if (DM_IsSet(Item[propertyName]) && Item[propertyName] != null) {
                            Item[propertyName](value);
                        }
                        else {
                            Item[propertyName] = ko.observable(value);
                        }
                    }
                }
            }
            catch (e) {
            }
        }

    }
}

function DM_Edit(Item, dataItem) {
    var props = Object.getOwnPropertyNames(dataItem);

    for (var i = 0; i < props.length; i++) {
        var propertyName = props[i];

        if (typeof (propertyName) == "undefined" || typeof (Item[propertyName]) == "undefined")
            continue;
        Item[propertyName] = ko.observable(dataItem[propertyName]);
    }
}