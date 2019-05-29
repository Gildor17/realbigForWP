//var jsInputerLaunch = 0;

function asyncBlocksInsertingFunction(blockSettingArray, contentLength) {
    try {
        var content_pointer = document.getElementById("content_pointer_id");
        var parent_with_content = content_pointer.parentElement;
        var lordOfElements = parent_with_content;
        parent_with_content = parent_with_content.parentElement;

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
        let containerFor7th = [];
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

        function directClassElementDetecting(blockSettingArray, directElement) {
            let findQuery = 0;
            let directClassElementResult = [];

            if (blockSettingArray[i]['elementPlace'] > 1) {
                currentElement = document.querySelectorAll(directElement);
                if (currentElement.length > 0) {
                    if (currentElement.length > blockSettingArray[i]['elementPlace']) {
                        currentElement = currentElement[blockSettingArray[i]['elementPlace']-1];
                    } else if (currentElement.length < blockSettingArray[i]['elementPlace']) {
                        currentElement = currentElement[currentElement.length - 1];
                    } else {
                        findQuery = 1;
                    }
                }
            } else if (blockSettingArray[i]['elementPlace'] < 0) {
                currentElement = document.querySelectorAll(directElement);
                if (currentElement.length > 0) {
                    if ((currentElement.length + blockSettingArray[i]['elementPlace'] + 1) > 0) {
                        currentElement = currentElement[currentElement.length + blockSettingArray[i]['elementPlace']];
                    } else {
                        findQuery = 1;
                    }
                }
            } else {
                findQuery = 1;
            }
            directClassElementResult['findQuery'] = findQuery;
            directClassElementResult['currentElement'] = currentElement;

            return directClassElementResult;
        }

        for (var i = 0; i < blockSettingArray.length; i++) {
            currentElement = null;
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
                    let findQuery = 0;
                    let directClassResult = [];
                    let directElement = blockSettingArray[i]["directElement"].trim();

                    if (directElement.search('#') > -1) {
                        findQuery = 1;
                    } else if ((directElement.search('#') < 0)&&(!blockSettingArray[i]['element']||
                        (blockSettingArray[i]['element']&&directElement.indexOf('.') > 0))) {

                        directClassResult = directClassElementDetecting(blockSettingArray, directElement);
                        findQuery = directClassResult['findQuery'];
                        currentElement = directClassResult['currentElement'];
                    }
                    if (findQuery == 1) {
                        currentElement = document.querySelector(directElement);
                    }
                    if (!currentElement) {
                        findQuery = 0;
                        elementTypeSymbol = directElement.search('#');
                        if (elementTypeSymbol < 0) {
                            elementTypeSymbol = directElement.indexOf('.');
                            elementType = 'class';
                            elementName = directElement.replace(/\s/, '.');
                            if (elementTypeSymbol < 1) {
                                elementName = '.' + elementName;
                            } else {
                                if (blockSettingArray[i]['element']) {
                                    elementName = blockSettingArray[i]['element']+elementName;
                                }
                            }

                            directClassResult = directClassElementDetecting(blockSettingArray, elementName);
                            findQuery = directClassResult['findQuery'];
                            currentElement = directClassResult['currentElement'];

                            if (findQuery == 1) {
                                currentElement = document.querySelector(elementName);
                            }

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
                        let pCount = currentElement.getElementsByTagName("p").length;
                        let elementNumber = Math.round(pCount/2);
                        if (pCount > 1) {
                            currentElement = currentElement.getElementsByTagName("p")[elementNumber+1];
                        }
                        currentElement = getFromConstructions(currentElement);
                        if (currentElement != undefined && currentElement != null) {
                            if (pCount > 1) {
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
                } else if (blockSettingArray[i]["setting_type"] == 7) {
                    if (containerFor7th.length > 0) {
                        for (let j = 0; j < containerFor7th.length; j++) {
                            if (containerFor7th[j]["elementPlace"]<blockSettingArray[i]["elementPlace"]) {
                                // continue;
                                if (j == containerFor7th.length-1) {
                                    containerFor7th.push(blockSettingArray[i]);
                                    blockSettingArray.splice(i, 1);
                                    poolbackI = 1;
                                    i--;
                                    break;
                                }
                            } else {
                                for (let k = containerFor7th.length-1; k > j-1; k--) {
                                    containerFor7th[k + 1] = containerFor7th[k];
                                }
                                containerFor7th[j] = blockSettingArray[i];
                                blockSettingArray.splice(i, 1);
                                poolbackI = 1;
                                i--;
                                break;
                            }
                        }
                    } else {
                        containerFor7th.push(blockSettingArray[i]);
                        blockSettingArray.splice(i, 1);
                        poolbackI = 1;
                        i--;
                    }
                //    vidpravutu v vidstiinuk dlya 7ho tipa
                }
            } catch (e) { }
        }

        // percentSeparator(lordOfElements);

        if (containerFor6th.length > 0) {
            percentInserter(lordOfElements, containerFor6th);
        }
        if (containerFor7th.length > 0) {
            symbolInserter(lordOfElements, containerFor7th);
        }
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

function old_asyncInsertingsInsertingFunction(insertingsArray) {
    let currentElementForInserting = 0;
    let positionElement = 0;
    let position = 0;
    let insertToAdd = 0;
    let repeatSearch = 0;
    if (insertingsArray&&insertingsArray.length > 0) {
        for (let i = 0; i < insertingsArray.length; i++) {
            if (!insertingsArray[i]['used']||(insertingsArray[i]['used']&&inserinsertingsArray[i]['used']==0)) {
                positionElement = insertingsArray[i]['position_element'];
                position = insertingsArray[i]['position'];
                // insertToAdd = document.createElement('div');
                // insertToAdd = document.createElement("<div class='addedInserting'>"+insertingsArray[i]['content']+"</div>");
                // insertToAdd.classList.add('addedInserting');
                // insertToAdd.innerHTML = insertingsArray[i]['content'];
                // insertToAdd.innerHTML = insertToAdd.innerHTML.replace(/\\\'/,'\'',);
                insertToAdd = insertingsArray[i]['content'];

                currentElementForInserting = document.querySelector(positionElement);
                if (currentElementForInserting) {
                    if (position==0) {
                        // jQuery(currentElementForInserting).html(insertToAdd);
                        // currentElementForInserting.parentNode.insertBefore(insertToAdd, currentElementForInserting);
                        insertingsArray[i]['used'] = 1;
                    } else {
                        // jQuery(currentElementForInserting).html(insertToAdd);
                        // currentElementForInserting.parentNode.insertBefore(insertToAdd, currentElementForInserting.nextSibling);
                        insertingsArray[i]['used'] = 1;
                    }
                }
            }
        }
    }
    if (repeatSearch == 1) {
        setTimeout(function () {
            asyncInsertingsInsertingFunction(insertingsArray);
        }, 50)
    }
}

function asyncInsertingsInsertingFunction(insertingsArray) {
    let currentElementForInserting = 0;
    let currentElementToMove = 0;
    let positionElement = 0;
    let position = 0;
    let insertToAdd = 0;
    let postId = 0;
    let repeatSearch = 0;
    if (insertingsArray&&insertingsArray.length > 0) {
        for (let i = 0; i < insertingsArray.length; i++) {
            if (!insertingsArray[i]['used']||(insertingsArray[i]['used']&&inserinsertingsArray[i]['used']==0)) {
                positionElement = insertingsArray[i]['position_element'];
                position = insertingsArray[i]['position'];
                insertToAdd = insertingsArray[i]['content'];
                postId = insertingsArray[i]['postId'];

                currentElementForInserting = document.querySelector(positionElement);

                currentElementToMove = document.querySelector('.coveredInsertings[data-id="'+postId+'"]');
                if (currentElementForInserting) {
                    if (position==0) {
                        currentElementForInserting.parentNode.insertBefore(currentElementToMove, currentElementForInserting);
                        currentElementToMove.classList.remove('coveredInsertings');
                        insertingsArray[i]['used'] = 1;
                    } else {
                        currentElementForInserting.parentNode.insertBefore(currentElementToMove, currentElementForInserting.nextSibling);
                        currentElementToMove.classList.remove('coveredInsertings');
                        insertingsArray[i]['used'] = 1;
                    }
                } else {
                    repeatSearch = 1;
                }
            }
        }
    }
    if (repeatSearch == 1) {
        setTimeout(function () {
            asyncInsertingsInsertingFunction(insertingsArray);
        }, 50)
    }
}

function insertingsFunctionLaunch() {
    if (window.jsInsertingsLaunch !== undefined&&jsInsertingsLaunch == 25) {
        asyncInsertingsInsertingFunction(insertingsArray);
    } else {
        setTimeout(function () {
            insertingsFunctionLaunch();
        }, 50)
    }
}
insertingsFunctionLaunch();

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

function symbolInserter(lordOfElements, containerFor7th) {
    try {
        var separator = lordOfElements.children;
        var lordOfElementsResult = 0;
        var lordOfElementsTextResult = "";
        var textLength;
        let tlArray = [];
        let tlArrayCou = 0;
        var currentChildrenLength = 0;
        var possibleTagsArray = ["P", "H1", "H2", "H3", "H4", "H5", "H6", "DIV", "OL", "UL", "BLOCKQUOTE", "INDEX"];
        let possibleTagsInCheck = ["DIV", "INDEX"];
        let numberToUse = 0;
        let previousBreak = 0;
        let cycle_1_val;
        let cycle_2_val;
        let needleLength;
        let currentSumLength;
        let elementToAdd;

        if (!document.getElementById("markedSpan1")) {
            textLength = 0;
            for (let i = 0; i < lordOfElements.children.length; i++) {
                // if (lordOfElements.children[i].tagName!="SCRIPT"&&!lordOfElements.children[i].classList.contains("percentPointerClass")) {
                if (possibleTagsArray.includes(lordOfElements.children[i].tagName)&&!lordOfElements.children[i].classList.contains("percentPointerClass")&&lordOfElements.children[i].id!="toc_container") {
                    if (possibleTagsInCheck.includes(lordOfElements.children[i].tagName)) {
                        if (lordOfElements.children[i].children.length > 1) {
                            for (let j = 0; j < lordOfElements.children[i].children.length; j++) {
                                if (possibleTagsArray.includes(lordOfElements.children[i].children[j].tagName)&&!lordOfElements.children[i].children[j].classList.contains("percentPointerClass")&&lordOfElements.children[i].children[j].id!="toc_container") {
                                    textLength = textLength + lordOfElements.children[i].children[j].innerText.length;
                                    tlArray[tlArrayCou] = [];
                                    tlArray[tlArrayCou]['tag'] = lordOfElements.children[i].children[j].tagName;
                                    tlArray[tlArrayCou]['length'] = lordOfElements.children[i].children[j].innerText.length;
                                    tlArray[tlArrayCou]['element'] = lordOfElements.children[i].children[j];
                                    tlArrayCou++;
                                }
                            }
                        }
                    } else {
                        textLength = textLength + lordOfElements.children[i].innerText.length;
                        tlArray[tlArrayCou] = [];
                        tlArray[tlArrayCou]['tag'] = lordOfElements.children[i].tagName;
                        tlArray[tlArrayCou]['length'] = lordOfElements.children[i].innerText.length;
                        tlArray[tlArrayCou]['element'] = lordOfElements.children[i];
                        tlArrayCou++;
                    }
                }
            }

            for (let i = 0; i < containerFor7th.length; i++) {
                previousBreak = 0;
                currentChildrenLength = 0;
                currentSumLength = 0;
                needleLength = Math.abs(containerFor7th[i]['elementPlace']);

                elementToAdd = document.createElement("div");
                elementToAdd.classList.add("percentPointerClass");
                elementToAdd.innerHTML = containerFor7th[i]["text"];

                if (containerFor7th[i]['elementPlace'] < 0) {
                    for (let j = tlArray.length-1; j > -1; j--) {
                        currentSumLength = currentSumLength + tlArray[j]['length'];
                        if (needleLength < currentSumLength) {
                            tlArray[j]['element'].parentNode.insertBefore(elementToAdd, tlArray[j]['element']);
                            break;
                        } else {
                            if (j == 0) {
                                tlArray[j]['element'].parentNode.insertBefore(elementToAdd, tlArray[tlArray.length-1]['element'].nextSibling);
                                break;
                            }
                        }
                    }
                } else if (containerFor7th[i]['elementPlace'] == 0) {
                    tlArray[0]['element'].parentNode.insertBefore(elementToAdd, tlArray[0]['element']);
                } else {
                    for (let j = 0; j < tlArray.length; j++) {
                        currentSumLength = currentSumLength + tlArray[j]['length'];
                        if (needleLength < currentSumLength) {
                            tlArray[j]['element'].parentNode.insertBefore(elementToAdd, tlArray[j]['element'].nextSibling);
                            break;
                        } else {
                            if (j == tlArray.length-1) {
                                tlArray[j]['element'].parentNode.insertBefore(elementToAdd, tlArray[j]['element'].nextSibling);
                                break;
                            }
                        }
                    }
                }
            }

            //~~~~~~~~~~~~~~~~~~~~~

            var spanMarker = document.createElement("span");
            spanMarker.setAttribute("id", "markedSpan1");
            lordOfElements.prepend(spanMarker);
        }
    } catch (e) {
        console.log(e);
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