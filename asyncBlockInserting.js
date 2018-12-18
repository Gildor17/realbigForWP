//var jsInputerLaunch = 0;

function asyncBlocksInsertingFunction(blockSettingArray, contentLength) {
    // var currentPathFFile2 = path.dirname(__filename);

    try {

        var content_pointer = document.getElementById("content_pointer_id");
        var parent_with_content = content_pointer.parentElement;
        var lordOfElements = parent_with_content;
        parent_with_content = parent_with_content.parentElement;

        // percentSeparator(lordOfElements);

        var newElement = document.createElement("div");
        var elementToAdd;
        var poolbackI = 0;

        var counter = 0;
        var currentElement;
        var backElement = 0;
        var sumResult = 0;
        var repeat = false;
        var currentElementChecker = false;
        let containerFor6th = [];

        for (var i = 0; i < blockSettingArray.length; i++) {
            // if (poolbackI == 1) {
            //     i--;
            //     poolbackI = 0;
            // }
            currentElementChecker = false;
            try {
                elementToAdd = document.createElement("div");
                elementToAdd.classList.add("percentPointerClass");
                elementToAdd.innerHTML = blockSettingArray[i]["text"];
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
                        if (blockSettingArray[i]["elementPosition"] == 0) {
                            currentElement.parentNode.insertBefore(elementToAdd, currentElement);
                        } else {
                            currentElement.parentNode.insertBefore(elementToAdd, currentElement.nextSibling);
                        }
                        blockSettingArray.splice(i, 1);
                        poolbackI = 1;
                        i--;
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
                        if (blockSettingArray[i]["elementPosition"] == 0) {
                            currentElement.parentNode.insertBefore(elementToAdd, currentElement);
                        } else {
                            currentElement.parentNode.insertBefore(elementToAdd, currentElement.nextSibling);
                        }
                        blockSettingArray.splice(i, 1);
                        poolbackI = 1;
                        i--;
                    } else {
                        repeat = true;
                    }
                } else if (blockSettingArray[i]["setting_type"] == 4) {
                    parent_with_content.append(elementToAdd);
                    blockSettingArray.splice(i, 1);
                    poolbackI = 1;
                    i--;
                } else if (blockSettingArray[i]["setting_type"] == 5) {
                    let curElement = document.getElementById("content_pointer_id").parentElement;
                    if (curElement.getElementsByTagName("p").length > 0) {
                        let elementNumber = Math.round(curElement.getElementsByTagName("p").length/2);
                        curElement = curElement.getElementsByTagName("p")[elementNumber];
                        if (curElement != undefined && curElement != null) {
                            curElement.parentNode.insertBefore(elementToAdd, curElement.nextSibling);
                            blockSettingArray.splice(i, 1);
                            poolbackI = 1;
                            i--;
                        } else {
                            repeat = true;
                        }
                    } else {
                        repeat = true;
                    }
                } else if (blockSettingArray[i]["setting_type"] == 6) {
                    if (containerFor6th.length > 0) {
                        for (let j = 0; j < containerFor6th.length; j++) {
                            if (containerFor6th[j]["elementPlace"]<blockSettingArray[i]["elementPlace"]) {
                                // continue;
                                if (j == containerFor6th.length-1) {
                                    containerFor6th.push(blockSettingArray[i]);
                                    blockSettingArray.splice(i, 1);
                                    poolbackI = 1;
                                    i--;
                                    break;
                                }
                            } else {
                                for (let k = containerFor6th.length-1; k > j-1; k--) {
                                    containerFor6th[k + 1] = containerFor6th[k];
                                }
                                containerFor6th[j] = blockSettingArray[i];
                                blockSettingArray.splice(i, 1);
                                poolbackI = 1;
                                i--;
                                break;
                            }
                        }
                    } else {
                        containerFor6th.push(blockSettingArray[i]);
                        blockSettingArray.splice(i, 1);
                        poolbackI = 1;
                        i--;
                    }
                //    vidpravutu v vidstiinuk dlya 6ho tipa
                }
            } catch (e) { }
        }
        percentInserter(lordOfElements, containerFor6th);
        let stopper = 0;

        window.addEventListener('load', function () {
            if (repeat = true) {
                setTimeout(function () {
                    asyncBlocksInsertingFunction(blockSettingArray, contentLength)
                }, 100);
            }
        });
    } catch (e) {
        console.log(e.message);
    }
}

function asyncFunctionLauncher() {
    if (window.jsInputerLaunch !== undefined&&jsInputerLaunch == 15) {
        asyncBlocksInsertingFunction(blockSettingArray, contentLength);
    } else {
        setTimeout(function () {
            asyncFunctionLauncher()
        }, 20)
    }
}
asyncFunctionLauncher();

function percentSeparator(lordOfElements) {
    var separator = lordOfElements.children;
    var lordOfElementsResult = 0;
    var lordOfElementsTextResult = "";
    var textLength;
    var lengthPercent = 0;
    var textNeedyLength = 0;
    var currentChildrenLength = 0;
    var previousChildrenLength = 0;
    var separatorResult = [];
    var separatorResultCounter = 0;
    var lastICounterValue = 0;

    if (!document.getElementById("markedSpan")) {
        // lengthPercent = [10,25,43,60,82,97];
        textLength = 0;
        for (let i = 0; i < lordOfElements.children.length; i++) {
            if (lordOfElements.children[i].tagName!="SCRIPT"&&!lordOfElements.children[i].classList.contains("percentPointerClass")) {
                textLength = textLength + lordOfElements.children[i].innerText.length;
            }
        }

        let numberToUse = 0;
        for (let j = 0; j < 101; j++) {
            // textNeedyLength = Math.round(textLength * (lengthPercent[j]/100));
            textNeedyLength = Math.round(textLength * (j/100));
            // for (let i = 0; i < Math.round(lordOfElements.children.length/2); i++) {

            for (let i = lastICounterValue; i < lordOfElements.children.length; i++) {
                if (lordOfElements.children[i].tagName!="SCRIPT"&&!lordOfElements.children[i].classList.contains("percentPointerClass")) {
                    if (currentChildrenLength >= textNeedyLength) {
                        let elementToAdd = document.createElement("div");
                        elementToAdd.classList.add("percentPointerClass");
                        // elementToAdd.innerHTML = "<div style='border: 1px solid grey; font-size: 20px; height: 25px; width: auto; background-color: #2aabd2'>"+lengthPercent[j]+"</div>";
                        elementToAdd.innerHTML = "<div style='border: 1px solid grey; font-size: 20px; height: 25px; width: auto; background-color: #2aabd2'>"+j+"</div>";
                        if (i > 0) {
                            numberToUse = i - 1;
                        } else {
                            numberToUse = i;
                        }
                        if (previousChildrenLength==0||((currentChildrenLength - Math.round(previousChildrenLength/2)) >= textNeedyLength)) {
                            lordOfElements.children[numberToUse].parentNode.insertBefore(elementToAdd, lordOfElements.children[i]);
                        } else {
                            lordOfElements.children[numberToUse].parentNode.insertBefore(elementToAdd, lordOfElements.children[i].nextSibling);
                        }
                        lastICounterValue = i;
                        break;
                    }
                    lordOfElementsTextResult = lordOfElementsTextResult + " " + lordOfElements.children[i].innerText;
                    lordOfElementsResult = lordOfElementsResult + lordOfElements.children[i].innerText.length;
                    previousChildrenLength = lordOfElements.children[i].innerText.length;
                    currentChildrenLength = lordOfElementsResult;
                }
            }
        }
        var spanMarker = document.createElement("span");
        spanMarker.setAttribute("id", "markedSpan");
        lordOfElements.prepend(spanMarker);
    }


    for (let i = 0; i < separator.length; i++) {
        if (["P","UL","OL"].includes(separator[i].tagName)) {
            separatorResult[separatorResultCounter] = separator[i];
            separatorResultCounter++;
        } else if (separator[i].tagName=="BLOCKQUOTE"&&separator[i].children.length==1&&separator[i].children[0].tagName=="P") {
            separatorResult[separatorResultCounter] = separator[i];
            separatorResultCounter++;
        }
    }
}

function percentInserter(lordOfElements, containerFor6th) {
    try {
        var separator = lordOfElements.children;
        var lordOfElementsResult = 0;
        var lordOfElementsTextResult = "";
        var textLength;
        var lengthPercent = 0;
        var textNeedyLength = 0;
        var currentChildrenLength = 0;
        var previousChildrenLength = 0;
        var separatorResult = [];
        var separatorResultCounter = 0;
        var lastICounterValue = 0;

        if (!document.getElementById("markedSpan")) {
            // lengthPercent = [10,25,43,60,82,97];
            textLength = 0;
            for (let i = 0; i < lordOfElements.children.length; i++) {
                if (lordOfElements.children[i].tagName!="SCRIPT"&&!lordOfElements.children[i].classList.contains("percentPointerClass")) {
                    textLength = textLength + lordOfElements.children[i].innerText.length;
                }
            }

            let numberToUse = 0;
            for (let j = 0; j < containerFor6th.length; j++) {
                textNeedyLength = Math.round(textLength * (containerFor6th[j]["elementPlace"]/100));
                // textNeedyLength = Math.round(textLength * (j/100));
                // for (let i = 0; i < Math.round(lordOfElements.children.length/2); i++) {
                for (let i = lastICounterValue; i < lordOfElements.children.length; i++) {
                    if (lordOfElements.children[i].tagName!="SCRIPT"&&!lordOfElements.children[i].classList.contains("percentPointerClass")) {
                        if (currentChildrenLength >= textNeedyLength) {
                            let elementToAdd = document.createElement("div");
                            elementToAdd.classList.add("percentPointerClass");
                            // elementToAdd.innerHTML = "<div style='border: 1px solid grey; font-size: 20px; height: 25px; width: auto; background-color: #2aabd2'>"+lengthPercent[j]+"</div>";
                            // elementToAdd.innerHTML = "<div style='border: 1px solid grey; font-size: 20px; height: 25px; width: auto; background-color: #2aabd2'>"+j+"</div>";
                            elementToAdd.innerHTML = containerFor6th[j]["text"];
                            if (i > 0) {
                                numberToUse = i - 1;
                            } else {
                                numberToUse = i;
                            }
                            if (previousChildrenLength==0||((currentChildrenLength - Math.round(previousChildrenLength/2)) >= textNeedyLength)) {
                                lordOfElements.children[numberToUse].parentNode.insertBefore(elementToAdd, lordOfElements.children[i]);
                            } else {
                                lordOfElements.children[numberToUse].parentNode.insertBefore(elementToAdd, lordOfElements.children[i].nextSibling);
                            }
                            lastICounterValue = i;
                            break;
                        }
                        lordOfElementsTextResult = lordOfElementsTextResult + " " + lordOfElements.children[i].innerText;
                        lordOfElementsResult = lordOfElementsResult + lordOfElements.children[i].innerText.length;
                        previousChildrenLength = lordOfElements.children[i].innerText.length;
                        currentChildrenLength = lordOfElementsResult;
                    }
                }
            }
            var spanMarker = document.createElement("span");
            spanMarker.setAttribute("id", "markedSpan");
            lordOfElements.prepend(spanMarker);
        }
    } catch (e) {

    }
}