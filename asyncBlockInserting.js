//var jsInputerLaunch = 0;

function asyncBlocksInsertingFunction(blockSettingArray, contentLength) {
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
        let posCurrentElement;

        function getFromConstructions(currentElement) {
            if (currentElement.parentElement.tagName.toLowerCase() == "blockquote") {
                currentElement = currentElement.parentElement;
            } else if (["tr","td","th","thead","tbody","table"].includes(currentElement.parentElement.tagName.toLowerCase())) {
                currentElement = currentElement.parentElement;
                while (["tr", "td", "th", "thead", "tbody", "table"].includes(currentElement.parentElement.tagName.toLowerCase())) {
                    currentElement = currentElement.parentElement;
                }
            }
            return currentElement;
        }
        
        function initTargetToInsert(blockSettingArray) {
            let posCurrentElement;
            if (blockSettingArray[i]["elementPosition"] == 0) {
                posCurrentElement = currentElement;
                currentElement.style.marginTop = '0px';
            } else {
                posCurrentElement = currentElement.nextSibling;
                currentElement.style.marginBottom = '0px';
            }

            return posCurrentElement;
        }

        for (var i = 0; i < blockSettingArray.length; i++) {
            currentElementChecker = false;
            try {
                elementToAdd = document.createElement("div");
                elementToAdd.classList.add("percentPointerClass");
                elementToAdd.style.margin = '5px 0px';
                elementToAdd.innerHTML = blockSettingArray[i]["text"];
                elementToAdd.style.display = 'block';
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
                            currentElement = getFromConstructions(currentElement);
                            if (currentElement) {
                                currentElementChecker = true;
                            }
                        }
                    } else {
                        sumResult = blockSettingArray[i]["elementPlace"] - 1;
                        if (sumResult < currentElement.length) {
                            currentElement = currentElement[sumResult];
                            currentElement = getFromConstructions(currentElement);
                            if (currentElement) {
                                currentElementChecker = true;
                            }
                        }
                    }
                    if (currentElement != undefined && currentElement != null && currentElementChecker) {
                        posCurrentElement = initTargetToInsert(blockSettingArray);
                        // if (blockSettingArray[i]["elementPosition"] == 0) {
                        //     currentElement.parentNode.insertBefore(elementToAdd, currentElement);
                        // } else {
                        //     currentElement.parentNode.insertBefore(elementToAdd, currentElement.nextSibling);
                        // }
                        currentElement.parentNode.insertBefore(elementToAdd, posCurrentElement);

                        blockSettingArray.splice(i, 1);
                        poolbackI = 1;
                        i--;
                    } else {
                        repeat = true;
                    }
                } else if (blockSettingArray[i]["setting_type"] == 3) {
                    let elementTypeSymbol = '';
                    let elementSpaceSymbol = '';
                    let elementName = '';
                    let elementType = '';
                    let elementTag  = '';
                    let directElement = blockSettingArray[i]["directElement"].trim();

                    if (directElement.search('#') > -1||(!blockSettingArray['element']||(
                        blockSettingArray['element']&&directElement.search('.') > 0))) {
                        currentElement = document.querySelector(directElement);
                    }
                    if (!currentElement) {
                        elementTypeSymbol = directElement.search('#');
                        if (elementTypeSymbol < 0) {
                            elementTypeSymbol = directElement.search('.');
                            elementType = 'class';
                            elementName = directElement.replace('\s', '.');
                            if (elementTypeSymbol < 0) {
                                elementName = '.' + elementName;
                            }
                            if (blockSettingArray['element']) {
                                elementName = blockSettingArray['element']+elementName;
                            }
                            currentElement = document.querySelector(elementName);
                            if (currentElement) {
                                currentElementChecker = true;
                            }
                        } else {
                            elementType = 'id';
                            elementName = directElement.subString(elementTypeSymbol);
                            elementSpaceSymbol = elementName.search('\s');
                            elementName = elementName.substring(0, elementSpaceSymbol - 1);
                            currentElement = document.querySelector(elementName);
                            if (currentElement) {
                                currentElementChecker = true;
                            }
                        }
                    } else {
                        currentElementChecker = true;
                    }

                    if (currentElement != undefined && currentElement != null && currentElementChecker) {
                        posCurrentElement = initTargetToInsert(blockSettingArray);
                        // if (blockSettingArray[i]["elementPosition"] == 0) {
                        //     currentElement.parentNode.insertBefore(elementToAdd, currentElement);
                        // } else {
                        //     currentElement.parentNode.insertBefore(elementToAdd, currentElement.nextSibling);
                        // }
                        currentElement.parentNode.insertBefore(elementToAdd, posCurrentElement);

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
                    let currentElement = document.getElementById("content_pointer_id").parentElement;
                    if (currentElement.getElementsByTagName("p").length > 0) {
                        let elementNumber = Math.round(currentElement.getElementsByTagName("p").length/2);
                        currentElement = currentElement.getElementsByTagName("p")[elementNumber];
                        currentElement = getFromConstructions(currentElement);
                        if (currentElement != undefined && currentElement != null) {
                            currentElement.parentNode.insertBefore(elementToAdd, currentElement.nextSibling);
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
        // if () {
        asyncBlocksInsertingFunction(blockSettingArray, contentLength);
        // }
    } else {
        setTimeout(function () {
            asyncFunctionLauncher();
        }, 50)
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
        var lastJ1CounterValue = 0;
        var possibleTagsArray = ["P", "H1", "H2", "H3", "H4", "H5", "H6", "DIV", "OL", "UL", "BLOCKQUOTE", "INDEX"];
        let possibleTagsInCheck = ["DIV", "INDEX"];

        if (!document.getElementById("markedSpan")) {
            textLength = 0;
            for (let i = 0; i < lordOfElements.children.length; i++) {
                // if (lordOfElements.children[i].tagName!="SCRIPT"&&!lordOfElements.children[i].classList.contains("percentPointerClass")) {
                if (possibleTagsArray.includes(lordOfElements.children[i].tagName)&&!lordOfElements.children[i].classList.contains("percentPointerClass")&&lordOfElements.children[i].id!="toc_container") {
                    if (possibleTagsInCheck.includes(lordOfElements.children[i].tagName)) {
                        if (lordOfElements.children[i].children.length > 1) {
                            for (let j = 0; j < lordOfElements.children[i].children.length; j++) {
                                if (possibleTagsArray.includes(lordOfElements.children[i].children[j].tagName)&&!lordOfElements.children[i].children[j].classList.contains("percentPointerClass")&&lordOfElements.children[i].children[j].id!="toc_container") {
                                    textLength = textLength + lordOfElements.children[i].children[j].innerText.length;
                                }
                            }
                        }
                    } else {
                        textLength = textLength + lordOfElements.children[i].innerText.length;
                    }
                }
            }

            let numberToUse = 0;
            let previousBreak = 0;
            for (let j = 0; j < containerFor6th.length; j++) {
                previousBreak = 0;
                textNeedyLength = Math.round(textLength * (containerFor6th[j]["elementPlace"]/100));
                for (let i = lastICounterValue; i < lordOfElements.children.length; i++) {
                    if (possibleTagsArray.includes(lordOfElements.children[i].tagName)&&!lordOfElements.children[i].classList.contains("percentPointerClass")&&lordOfElements.children[i].id!="toc_container") {
                        if (possibleTagsInCheck.includes(lordOfElements.children[i].tagName)) {
                            if (lordOfElements.children[i].children.length > 0) {
                                for (let j1 = lastJ1CounterValue; j1 < lordOfElements.children[i].children.length; j1++) {
                                    if (possibleTagsArray.includes(lordOfElements.children[i].children[j1].tagName)&&!lordOfElements.children[i].children[j1].classList.contains("percentPointerClass")&&lordOfElements.children[i].children[j1].id!="toc_container") {
                                        if (currentChildrenLength >= textNeedyLength) {
                                            let elementToAdd = document.createElement("div");
                                            elementToAdd.classList.add("percentPointerClass");
                                            elementToAdd.innerHTML = containerFor6th[j]["text"];
                                            if (j1 > 0) {
                                                numberToUse = j1 - 1;
                                            } else {
                                                numberToUse = j;
                                            }
                                            if (previousChildrenLength==0||((currentChildrenLength - Math.round(previousChildrenLength/2)) >= textNeedyLength)) {

                                                if (lordOfElements.children[i].children[numberToUse].parentElement.tagName.toLowerCase() == "blockquote") {
                                                    lordOfElements.children[i].children[numberToUse].parentElement.parentNode.insertBefore(elementToAdd, lordOfElements.children[i].children[j1]);
                                                } else {
                                                    lordOfElements.children[i].children[numberToUse].parentNode.insertBefore(elementToAdd, lordOfElements.children[i].children[j1]);
                                                }
                                            } else {
                                                if (lordOfElements.children[i].children[numberToUse].parentElement.tagName.toLowerCase() == "blockquote") {
                                                    lordOfElements.children[i].children[numberToUse].parentElement.parentNode.insertBefore(elementToAdd, lordOfElements.children[i].children[j1].nextSibling);
                                                } else {
                                                    lordOfElements.children[i].children[numberToUse].parentNode.insertBefore(elementToAdd, lordOfElements.children[i].children[j1].nextSibling);
                                                }
                                            }
                                            lastICounterValue = i;
                                            lastJ1CounterValue = j1;
                                            previousBreak = 1;
                                            break;
                                        }
                                        lordOfElementsTextResult = lordOfElementsTextResult + " " + lordOfElements.children[i].children[j1].innerText;
                                        lordOfElementsResult = lordOfElementsResult + lordOfElements.children[i].children[j1].innerText.length;
                                        previousChildrenLength = lordOfElements.children[i].children[j1].innerText.length;
                                        currentChildrenLength = lordOfElementsResult;
                                    }
                                }
                                if (previousBreak==1) {
                                    break;
                                }
                            }
                        } else {
                            if (currentChildrenLength >= textNeedyLength) {
                                let elementToAdd = document.createElement("div");
                                elementToAdd.classList.add("percentPointerClass");
                                elementToAdd.innerHTML = containerFor6th[j]["text"];
                                if (i > 0) {
                                    numberToUse = i - 1;
                                } else {
                                    numberToUse = i;
                                }
                                if (previousChildrenLength==0||((currentChildrenLength - Math.round(previousChildrenLength/2)) >= textNeedyLength)) {
                                    if (lordOfElements.children[numberToUse].parentElement.tagName.toLowerCase() == "blockquote") {
                                        lordOfElements.children[numberToUse].parentElement.parentNode.insertBefore(elementToAdd, lordOfElements.children[i]);
                                    } else {
                                        lordOfElements.children[numberToUse].parentNode.insertBefore(elementToAdd, lordOfElements.children[i]);
                                    }
                                } else {
                                    if (lordOfElements.children[numberToUse].parentElement.tagName.toLowerCase() == "blockquote") {
                                        lordOfElements.children[numberToUse].parentElement.parentNode.insertBefore(elementToAdd, lordOfElements.children[i].nextSibling);
                                    } else {
                                        lordOfElements.children[numberToUse].parentNode.insertBefore(elementToAdd, lordOfElements.children[i].nextSibling);
                                    }
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
            }
            var spanMarker = document.createElement("span");
            spanMarker.setAttribute("id", "markedSpan");
            lordOfElements.prepend(spanMarker);
        }
    } catch (e) {

    }
}