function testFuncInTestFile(blockSettingArray, contentLength) {

    try {
        var content_pointer = document.getElementById("content_pointer_id");
        var parent_with_content = content_pointer.parentElement;

        var newElement = document.createElement("div");
        var elementToAdd;

        var counter = 0;
        var currentElement;
        var backElement = 0;
        var sumResult = 0;

        for(var i = 0; i < blockSettingArray.length; i++)
        {
            try {
                if (blockSettingArray[i]["minSymbols"] > contentLength)
                {
                    continue;
                }
                if (blockSettingArray[i]["setting_type"]==1)
                {
                    currentElement = parent_with_content.getElementsByTagName(blockSettingArray[i]["element"]);
                    if (currentElement.length < 1) {
                        currentElement = parent_with_content.parentElement.getElementsByTagName(blockSettingArray[i]["element"]);
                    }
                    if (blockSettingArray[i]["elementPlace"] < 0) {
                        sumResult = currentElement.length + blockSettingArray[i]["elementPlace"];
                        if (sumResult >= 0&&sumResult < currentElement.length) {
                            currentElement = currentElement[sumResult];
                        }
                    } else {
                        sumResult = blockSettingArray[i]["elementPlace"]-1;
                        if (sumResult < currentElement.length) {
                            currentElement = currentElement[sumResult];
                        }
                    }
                    if (currentElement != undefined||currentElement != null)
                    {
                        if (blockSettingArray[i]["elementPosition"]==0)
                        {
                            elementToAdd = document.createElement("div");
                            elementToAdd.innerHTML = blockSettingArray[i]["text"];
                            newElement = elementToAdd.firstChild;
                            currentElement.parentNode.insertBefore(newElement, currentElement);
                        }
                        else
                        {
                            elementToAdd = document.createElement("div");
                            elementToAdd.innerHTML = blockSettingArray[i]["text"];
                            newElement = elementToAdd.firstChild;
                            currentElement.parentNode.insertBefore(newElement, currentElement.nextSibling);
                        }
                    }
                }
                else if (blockSettingArray[i]["setting_type"]==3)
                {
                    var elementType = blockSettingArray[i]["directElement"].charAt(0);
                    var elementName = blockSettingArray[i]["directElement"].substring(1);
                    if (elementType==='#')
                    {
                        currentElement = parent_with_content.querySelector(elementType+elementName);
                    }
                    else if (elementType==='.')
                    {
                        currentElement = parent_with_content.getElementsByClassName(elementName);
                        if (currentElement.length > 0)
                        {
                            for (var i1 = 0; i1 < currentElement.length; i1++)
                            {
                                if (!blockSettingArray[i]["element"]||currentElement[i1].tagName.toLowerCase() == blockSettingArray[i]["element"].toLowerCase())
                                {
                                    currentElement = currentElement[i1];
                                    // break;
                                }
                            }
                        }
                    }
                    if (currentElement != undefined||currentElement != null) {
                        if (blockSettingArray[i]["elementPosition"] == 0) {
                            elementToAdd = document.createElement("div");
                            elementToAdd.innerHTML = blockSettingArray[i]["text"];
                            newElement = elementToAdd.firstChild;
                            currentElement.parentNode.insertBefore(newElement, currentElement.nextSibling);
                        }
                        else {
                            elementToAdd = document.createElement("div");
                            elementToAdd.innerHTML = blockSettingArray[i]["text"];
                            newElement = elementToAdd.firstChild;
                            currentElement.parentNode.insertBefore(newElement, currentElement);
                        }
                    }
                }
                else if (blockSettingArray[i]["setting_type"]==4)
                {
                    elementToAdd = document.createElement("div");
                    elementToAdd.innerHTML = blockSettingArray[i]["text"];
                    newElement = elementToAdd.firstChild;
                    // parent_with_content.parentNode.insertBefore(newElement, parent_with_content.nextSibling);
                    parent_with_content.append(newElement);
                }
            } catch (e) { }
        }
    } catch (e) { }
}