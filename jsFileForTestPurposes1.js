var jsInputerLaunch = 0;

function testFuncInTestFile(blockSettingArray, contentLength) {
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

        for (var i = 0; i < blockSettingArray.length; i++) {
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
                        }
                    } else {
                        sumResult = blockSettingArray[i]["elementPlace"] - 1;
                        if (sumResult < currentElement.length) {
                            currentElement = currentElement[sumResult];
                        }
                    }
                    if (currentElement != undefined && currentElement != null) {
                        if (blockSettingArray[i]["elementPosition"] == 0) {
                            elementToAdd = document.createElement("div");
                            elementToAdd.innerHTML = blockSettingArray[i]["text"];
                            newElement = elementToAdd.firstChild;
                            currentElement.parentNode.insertBefore(newElement, currentElement);
                            blockSettingArray.splice(i, 1);
                        }
                        else {
                            elementToAdd = document.createElement("div");
                            elementToAdd.innerHTML = blockSettingArray[i]["text"];
                            newElement = elementToAdd.firstChild;
                            currentElement.parentNode.insertBefore(newElement, currentElement.nextSibling);
                            blockSettingArray.splice(i, 1);
                        }
                    }
                }
                else if (blockSettingArray[i]["setting_type"] == 3) {
                    var elementType = blockSettingArray[i]["directElement"].charAt(0);
                    var elementName = blockSettingArray[i]["directElement"].substring(1);
                    if (elementType == '#') {
                        currentElement = document.querySelector(elementType + elementName);
                    }
                    else if (elementType == '.') {
                        /* currentElement = parent_with_content.getElementsByClassName(elementName);   //orig */
                        currentElement = document.getElementsByClassName(elementName);
                        if (currentElement.length > 0) {
                            for (var i1 = 0; i1 < currentElement.length; i1++) {
                                if (!blockSettingArray[i]["element"] || currentElement[i1].tagName.toLowerCase() == blockSettingArray[i]["element"].toLowerCase()) {
                                    currentElement = currentElement[i1];
                                    break;
                                }
                            }
                        }
                    }
                    if (currentElement != undefined && currentElement != null) {
                        if (blockSettingArray[i]["elementPosition"] == 0) {
                            elementToAdd = document.createElement("div");
                            elementToAdd.innerHTML = blockSettingArray[i]["text"];
                            newElement = elementToAdd.firstChild;
                            currentElement.parentNode.insertBefore(newElement, currentElement);
                            blockSettingArray.splice(i, 1);
                        }
                        else {
                            elementToAdd = document.createElement("div");
                            elementToAdd.innerHTML = blockSettingArray[i]["text"];
                            newElement = elementToAdd.firstChild;
                            currentElement.parentNode.insertBefore(newElement, currentElement.nextSibling);
                            blockSettingArray.splice(i, 1);
                        }
                    } else {
                        repeat = true;
                    }
                }
                else if (blockSettingArray[i]["setting_type"] == 4) {
                    elementToAdd = document.createElement("div");
                    elementToAdd.innerHTML = blockSettingArray[i]["text"];
                    newElement = elementToAdd.firstChild;
                    // parent_with_content.parentNode.insertBefore(newElement, parent_with_content.nextSibling);
                    parent_with_content.append(newElement);
                    blockSettingArray.splice(i, 1);
                }
            } catch (e) {
            }
        }
        window.addEventListener('load', function () {
            if (repeat = true) {
                setTimeout(function () {
                    testFuncInTestFile(blockSettingArray, contentLength)
                }, 100);
            }
        });
    } catch (e) {
    }
}

testFuncLauncher();
function testFuncLauncher() {
    if (jsInputerLaunch == 15) {
        testFuncInTestFile(blockSettingArray, contentLength);
    } else {
        setTimeout(function () {
            testFuncLauncher();
        }, 20)
    }
}

