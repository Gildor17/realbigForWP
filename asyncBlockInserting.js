var jsInputerLaunch = 0;

function asyncBlocksInsertingFunction(blockSettingArray, contentLength) {
    // var currentPathFFile2 = path.dirname(__filename);

    try {
        var content_pointer = document.getElementById("content_pointer_id");
        var parent_with_content = content_pointer.parentElement;
        parent_with_content = parent_with_content.parentElement;

        var newElement = document.createElement("div");
        var elementToAdd;

        var counter = 0;
        var currentElement;
        var backElement = 0;
        var sumResult = 0;
        var repeat = false;
        var currentElementChecker = false;

        for (var i = 0; i < blockSettingArray.length; i++) {
            currentElementChecker = false;
            try {
                if (blockSettingArray[i]["minHeaders"] > 0) {
                    var termorarity_parent_with_content = parent_with_content;
                    var termorarity_parent_with_content_length = 0;
                    var headersList = ['h1', 'h2', 'h3', 'h4', 'h5', 'h6'];
                    for (var hc1 = 0; hc1 < headersList.length; hc1++) {
                        termorarity_parent_with_content_length += termorarity_parent_with_content.getElementsByTagName(headersList[hc1]).length;
                    }
                    if (blockSettingArray[i]["minHeaders"] > termorarity_parent_with_content_length) {
                        continue;
                    }
                }
                if (blockSettingArray[i]["minSymbols"] > contentLength) {
                    continue;
                }
                if (blockSettingArray[i]["setting_type"] == 1) {
                    currentElement = parent_with_content.getElementsByTagName(blockSettingArray[i]["element"]);
                    if (currentElement.length < 1) {
                        currentElement = parent_with_content.parentElement.getElementsByTagName(blockSettingArray[i]["element"]);
                    }
                    if (blockSettingArray[i]["elementPlace"] < 0) {
                        sumResult = currentElement.length + blockSettingArray[i]["elementPlace"];
                        if (sumResult >= 0 && sumResult < currentElement.length) {
                            currentElement = currentElement[sumResult];
                            currentElementChecker = true;
                        }
                    } else {
                        sumResult = blockSettingArray[i]["elementPlace"] - 1;
                        if (sumResult < currentElement.length) {
                            currentElement = currentElement[sumResult];
                            currentElementChecker = true;
                        }
                    }
                    if (currentElement != undefined && currentElement != null && currentElementChecker) {
                        elementToAdd = document.createElement("div");
                        elementToAdd.innerHTML = blockSettingArray[i]["text"];
                        newElement = elementToAdd.firstChild;
                        if (blockSettingArray[i]["elementPosition"] == 0) {
                            currentElement.parentNode.insertBefore(newElement, currentElement);
                        } else {
                            currentElement.parentNode.insertBefore(newElement, currentElement.nextSibling);
                        }
                        blockSettingArray.splice(i, 1);
                    } else {
                        repeat = true;
                    }
                } else if (blockSettingArray[i]["setting_type"] == 3) {
                    var elementType = blockSettingArray[i]["directElement"].charAt(0);
                    var elementName = blockSettingArray[i]["directElement"].substring(1);
                    if (elementType == '#') {
                        currentElement = document.querySelector(elementType + elementName);
                        currentElementChecker = true;
                    } else if (elementType == '.') {
                        /* currentElement = parent_with_content.getElementsByClassName(elementName);   //orig */
                        currentElement = document.getElementsByClassName(elementName);
                        if (currentElement.length > 0) {
                            for (var i1 = 0; i1 < currentElement.length; i1++) {
                                if (!blockSettingArray[i]["element"] || currentElement[i1].tagName.toLowerCase() == blockSettingArray[i]["element"].toLowerCase()) {
                                    currentElement = currentElement[i1];
                                    currentElementChecker = true;
                                    break;
                                }
                            }
                        }
                    }
                    if (currentElement != undefined && currentElement != null && currentElementChecker) {
                        elementToAdd = document.createElement("div");
                        elementToAdd.innerHTML = blockSettingArray[i]["text"];
                        newElement = elementToAdd.firstChild;
                        if (blockSettingArray[i]["elementPosition"] == 0) {
                            currentElement.parentNode.insertBefore(newElement, currentElement);
                        } else {
                            currentElement.parentNode.insertBefore(newElement, currentElement.nextSibling);
                        }
                        blockSettingArray.splice(i, 1);
                    } else {
                        repeat = true;
                    }
                } else if (blockSettingArray[i]["setting_type"] == 4) {
                    elementToAdd = document.createElement("div");
                    elementToAdd.innerHTML = blockSettingArray[i]["text"];
                    newElement = elementToAdd.firstChild;
                    // parent_with_content.parentNode.insertBefore(newElement, parent_with_content.nextSibling);
                    parent_with_content.append(newElement);
                    blockSettingArray.splice(i, 1);
                } else if (blockSettingArray[i]["setting_type"] == 5) {
                    elementToAdd = document.createElement("div");
                    elementToAdd.innerHTML = blockSettingArray[i]["text"];
                    newElement = elementToAdd.firstChild;
                    let curElement = document.getElementById("content_pointer_id").parentElement;
                    if (curElement.getElementsByTagName("p").length > 0) {
                        let elementNumber = Math.round(curElement.getElementsByTagName("p").length/2);
                        curElement = curElement.getElementsByTagName("p")[elementNumber];
                        if (curElement != undefined && curElement != null) {
                            curElement.parentNode.insertBefore(newElement, curElement.nextSibling);
                            blockSettingArray.splice(i, 1);
                        } else {
                            repeat = true;
                        }
                    } else {
                        repeat = true;
                    }
                }
            } catch (e) { }
        }
        window.addEventListener('load', function () {
            if (repeat = true) {
                setTimeout(function () {
                    asyncBlocksInsertingFunction(blockSettingArray, contentLength)
                }, 100);
            }
        });
    } catch (e) { }
}

asyncFunctionLauncher();
function asyncFunctionLauncher() {
    if (jsInputerLaunch == 15) {
        asyncBlocksInsertingFunction(blockSettingArray, contentLength);
    } else {
        setTimeout(function () {
            asyncFunctionLauncher();
        }, 20)
    }
}

