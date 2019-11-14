//var jsInputerLaunch = 0;
// function rerunShortcodes() {
//     let xhttp = new XMLHttpRequest();
//     let sendData = 'action=rerunShortcodes&type=scRerun';
//     xhttp.onreadystatechange = function(redata) {
//         if (this.readyState == 4 && this.status == 200) {
//             if (redata) {
//                 // decodedData = JSON.parse(redata);
//             }
//
//             console.log('cache succeed');
//             // document.getElementById("demo").innerHTML = this.responseText;
//         }
//     };
//     // xhttp.open("POST", ajaxurl, true);
//     xhttp.open("POST", adg_object.ajax_url, true);
//     xhttp.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
//     xhttp.send(sendData);
// }
//
// if (document.readyState === "complete" || (document.readyState !== "loading" && !document.documentElement.doScroll)) {
//     rerunShortcodes();
// } else {
//     document.addEventListener("DOMContentLoaded", rerunShortcodes, false);
// }

if (typeof endedSc === 'undefined') {
    var endedSc = false;
}
if (typeof endedCc === 'undefined') {
    var endedCc = false;
}
if (typeof usedAdBlocksArray==='undefined') {
    var usedAdBlocksArray = [];
}
if (typeof usedBlockSettingArrayIds==='undefined') {
    var usedBlockSettingArrayIds = [];
}

// "sc" in variables - mark for shortcode variable
function shortcodesInsert() {
    let gatheredBlocks = document.querySelectorAll('.percentPointerClass.scMark');
    let scBlockId = -1;
    let scAdId = -1;
    let blockStatus = '';
    let gatheredBlockChild;
    let okStates = ['done','refresh-wait','no-block','fetched'];
    let scContainer;
    let oneSuccess;
    let oneFail;
    let sci;
    let scRepeatFuncLaunch = false;
    let i1 = 0;

    if (typeof scArray !== 'undefined') {
        if (scArray&&scArray.length > 0&&gatheredBlocks&&gatheredBlocks.length > 0) {
            for (let i = 0; i < gatheredBlocks.length; i++) {
                gatheredBlockChild = gatheredBlocks[i].children[0];
                if (!gatheredBlockChild) {
                    continue;
                }
                scAdId = -3;
                blockStatus = null;
                scContainer = null;

                scAdId = gatheredBlockChild.getAttribute('data-aid');
                scBlockId = gatheredBlockChild.getAttribute('data-id');
                blockStatus = gatheredBlockChild.getAttribute('data-state');

                // if (scBlockId&&scArray[scBlockId]) {
                if (scBlockId&&scAdId > 0) {
                    sci = -1;
                    for (i1 = 0; i1 < scArray.length; i1++) {
                        if (scBlockId == scArray[i1]['blockId']&&scAdId == scArray[i1]['adId']) {
                            sci = i1;
                        }
                    }

                    if (sci > -1) {
                        if (blockStatus&&okStates.includes(blockStatus)) {
                            // if (scArray[scBlockId][scAdId]) {
                                if (blockStatus=='no-block') {
                                    gatheredBlockChild.innerHTML = '';
                                } else {
                                    jQuery(gatheredBlockChild).html(scArray[sci]['text']);
                                }
                                for (i1 = 0; i1 < scArray.length; i1++) {
                                    if (scBlockId == scArray[i1]['blockId']) {
                                        scArray.splice(i1, 1);
                                    }
                                }
                                gatheredBlocks[i].classList.remove('scMark');
                            // }
                        }
                    }
                }
            }
        } else if (!scArray||(scArray&&scArray.length < 1)) {
            endedSc = true;
        }
    } else {
        endedSc = true;
    }

    if (!endedSc) {
        setTimeout(function () {
            shortcodesInsert();
        }, 200);
    }

}

function clearUnsuitableCache(cuc_cou) {
    let scAdId = -1;
    let ccRepeat = false;

    let gatheredBlocks = document.querySelectorAll('.percentPointerClass .content_rb');

    if (gatheredBlocks&&gatheredBlocks.length > 0) {
        for (let i = 0; i < gatheredBlocks.length; i++) {
            if (gatheredBlocks[i]['dataset']['aid']&&gatheredBlocks[i]['dataset']['aid'] < 0) {
                if ((gatheredBlocks[i]['dataset']["state"]=='no-block')||(['done','fetched','refresh-wait'].includes(gatheredBlocks[i]['dataset']["state"]))) {
                    gatheredBlocks[i]['innerHTML'] = '';
                } else {
                    ccRepeat = true;
                }
            } else if (!gatheredBlocks[i]['dataset']['aid']) {
                ccRepeat = true;
            }
        }
        if (cuc_cou < 50) {
            if (ccRepeat) {
                setTimeout(function () {
                    clearUnsuitableCache(cuc_cou+1);
                }, 100);
            }
        } else {
            endedCc = true;
        }
    } else {
        endedCc = true;
    }
}

function blocksRepositionUse(containerString, blType, searchType) {
    let blocksInContainer;
    let currentBlock;
    let currentBlockId;
    let currentBlockPosition;
    let currentContainer;
    let i = 0;
    let j = 0;
    let blockStrJs = ' .percentPointerClass.marked';
    let blockStrPhp = ' .percentPointerClass:not(.marked)';
    let blockStr = ' .percentPointerClass';

    if (searchType) {
        if (searchType == 'marked') {
            blocksInContainer = blType.closest(containerString);

            if (blocksInContainer) {
                return blocksInContainer;
            } else {
                return blType;
            }
        } else if (searchType == 'non-marked') {
            blocksInContainer = document.querySelectorAll(blType + containerString + blockStrPhp);
            if (blocksInContainer && blocksInContainer.length > 0 && usedBlockSettingArray && usedBlockSettingArray.length > 0) {
                for (i = 0; i < blocksInContainer.length; i++) {
                    currentBlock = blocksInContainer[i];
                    currentBlockId = currentBlock.querySelector('.content_rb').getAttribute('data-id');
                    currentContainer = null;
                    for (j = 0; j < usedBlockSettingArray.length; i++) {
                        if (usedBlockSettingArray[i]['id'] == currentBlockId) {
                            currentBlockPosition = usedBlockSettingArray[i]['elementPosition'];
                            currentContainer = currentBlock.closest(blType + containerString);
                            if (currentBlockPosition == 0) {
                                currentContainer.parentNode.insertBefore(currentBlock, currentContainer);
                            } else {
                                currentContainer.parentNode.insertBefore(currentBlock, currentContainer.nextSibling);
                            }
                            break;
                        }
                    }
                }
            }
        }
    }
    return false;
}

function blocksReposition() {
    let containersArray = [];
    containersArray[0] = [];
    containersArray[0]['type'] = [''];
    containersArray[0]['list'] = ['table', 'blockquote'];
    containersArray[1] = [];
    containersArray[1]['type'] = ['#'];
    containersArray[1]['list'] = ['toc_container'];
    containersArray[2] = [];
    containersArray[2]['type'] = ['.'];
    containersArray[2]['list'] = ['percentPointerClass','content_rb'];

    let markingString = 'non-marked';
    let penyok_stoparik = 0;
    let i = 0;
    let j = 0;

    for (i = 0; i < containersArray.length; i++) {
        penyok_stoparik = 1;
        for (j = 0; j < containersArray[i]['list'].length; j++) {
            blocksRepositionUse(containersArray[i]['list'][j], containersArray[i]['type'], markingString);
        }
    }

    if (excIdClass&&excIdClass.length > 0) {
        for (i = 0; i < excIdClass.length; i++) {
            if (excIdClass[i].length > 0) {
                blocksRepositionUse(excIdClass[i], '', markingString);
            }
        }
    }
}

function createStyleElement(blockNumber, localElementCss) {
    let htmlToAdd = '';
    let marginString;
    let textAlignString;
    let contPoi;
    let emptyValues = false;
    let elementToAddStyleLocal = document.querySelector('#blocksAlignStyle');
    if (!elementToAddStyleLocal) {
        contPoi = document.querySelector('#content_pointer_id');
        if (!contPoi) {
            return false;
        }

        elementToAddStyleLocal = document.createElement('style');
        elementToAddStyleLocal.setAttribute('id', 'blocksAlignStyle');
        contPoi.parentNode.insertBefore(elementToAddStyleLocal, contPoi);
    }

    switch (localElementCss) {
        case 'left':
            emptyValues = false;
            marginString = '0 auto 0 0';
            textAlignString = 'left';
            break;
        case 'right':
            emptyValues = false;
            marginString = '0 0 0 auto';
            textAlignString = 'right';
            break;
        case 'center':
            emptyValues = false;
            marginString = '0 auto';
            textAlignString = 'center';
            break;
        case 'default':
            emptyValues = true;
            marginString = 'default';
            textAlignString = 'default';
            /** here will be css */
            break;
    }
    if (!emptyValues) {
        // htmlToAdd = '#content_rb_'+blockNumber+' > * {\n' +
        //     '    margin: '+marginString+';\n' +
        //     '    text-align: '+textAlignString+';\n' +
        //     '}\n';
        htmlToAdd = '#content_rb_'+blockNumber+' > * {\n' +
            '    margin: '+marginString+';\n' +
            '}\n';
    }

    elementToAddStyleLocal.innerHTML += htmlToAdd;

    // return true;
    return textAlignString;
}

function asyncBlocksInsertingFunction(blockSettingArray, contentLength) {
    try {
        var content_pointer = document.querySelector("#content_pointer_id"); //orig
        var parent_with_content = content_pointer.parentElement;
        var lordOfElements = parent_with_content;
        parent_with_content = parent_with_content.parentElement;

        var newElement = document.createElement("div");
        var elementToAdd;
        var elementToAddStyle;
        var poolbackI = 0;

        var counter = 0;
        var currentElement;
        var repeatableCurrentElement;
        var repeatableSuccess;
        var reCou;
        var curFirstPlace;
        var curElementCount;
        var curElementStep;
        var backElement = 0;
        var sumResult = 0;
        var repeat = false;
        var currentElementChecker = false;
        let containerFor6th = [];
        let containerFor7th = [];
        let posCurrentElement;
        var block_number;

        function getFromConstructions(currentElement) {
            if (currentElement.parentElement.tagName.toLowerCase() == "blockquote") {
                currentElement = currentElement.parentElement;
            } else if (["tr","td","th","thead","tbody","table"].includes(currentElement.parentElement.tagName.toLowerCase())) {
                currentElement = currentElement.parentElement;
                while (["tr","td","th","thead","tbody","table"].includes(currentElement.parentElement.tagName.toLowerCase())) {
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
            currentElement.style.clear = 'both';

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

        function placingToH1(usedElement, elementTagToFind) {
            currentElement = usedElement.querySelectorAll(elementTagToFind);
            if (currentElement.length < 1) {
                if (usedElement.parentElement) {
                    placingToH1(usedElement.parentElement, elementTagToFind);
                }
            }
        }

        var termorarity_parent_with_content = parent_with_content;
        var termorarity_parent_with_content_length = 0;
        var headersList = ['h1', 'h2', 'h3', 'h4', 'h5', 'h6'];
        for (var hc1 = 0; hc1 < headersList.length; hc1++) {
            termorarity_parent_with_content_length += termorarity_parent_with_content.getElementsByTagName(headersList[hc1]).length;
        }

        for (var i = 0; i < blockSettingArray.length; i++) {
            currentElement = null;
            currentElementChecker = false;
            try {
                if (!blockSettingArray[i]["text"]||(blockSettingArray[i]["text"]&&blockSettingArray[i]["text"].length < 1)) {
                    blockSettingArray.splice(i, 1);
                    poolbackI = 1;
                    i--;
                    continue;
                }

                // elementToAdd = document.querySelector('.percentPointerClass.coveredAd[data-id="'+blockSettingArray[i]['id']+'"]');

                block_number = 0;

                elementToAdd = document.createElement("div");
                elementToAdd.classList.add("percentPointerClass");
                elementToAdd.classList.add("marked");
                if (blockSettingArray[i]["sc"]==1) {
                    elementToAdd.classList.add("scMark");
                }
                elementToAdd.innerHTML = blockSettingArray[i]["text"];
                block_number = elementToAdd.children[0].attributes['data-id'].value;

                elementToAddStyle = createStyleElement(block_number, blockSettingArray[i]["elementCss"]);

                if (elementToAddStyle&&elementToAddStyle!='default') {
                    elementToAdd.style.textAlign = elementToAddStyle;
                }

                if (blockDuplicate == 'no') {
                    if (usedBlockSettingArrayIds.length > 0) {
                        for (let i1 = 0; i1 < usedBlockSettingArrayIds.length; i1++) {
                            if (block_number==usedBlockSettingArrayIds[i1]) {
                                blockSettingArray.splice(i, 1);
                                poolbackI = 1;
                                i--;
                                continue;
                            }
                        }
                    }
                }

                if (blockSettingArray[i]["minHeaders"] > 0) {
                    if (blockSettingArray[i]["minHeaders"] > termorarity_parent_with_content_length) {
                        continue;
                    }
                }
                if (blockSettingArray[i]["maxHeaders"] > 0) {
                    if (blockSettingArray[i]["maxHeaders"] < termorarity_parent_with_content_length) {
                        blockSettingArray.splice(i, 1);
                        poolbackI = 1;
                        i--;
                        continue;
                    }
                }
                if (blockSettingArray[i]["minSymbols"] > contentLength) {
                    continue;
                }
                if (blockSettingArray[i]["maxSymbols"] > 0) {
                    if (blockSettingArray[i]["maxSymbols"] < contentLength) {
                        blockSettingArray.splice(i, 1);
                        poolbackI = 1;
                        i--;
                        continue;
                    }
                }

                if (blockSettingArray[i]["setting_type"] == 1) {
                    if (blockSettingArray[i]["element"].toLowerCase()=='h1') {
                        placingToH1(parent_with_content, blockSettingArray[i]["element"]);
                    } else if (blockSettingArray[i]["element"].toLowerCase()=='h2-4') {
                        currentElement = parent_with_content.querySelectorAll('h2,h3,h4');
                        if (currentElement.length < 1) {
                            currentElement = parent_with_content.parentElement.querySelectorAll('h2,h3,h4');
                        }
                    } else {
                        currentElement = parent_with_content.querySelectorAll(blockSettingArray[i]["element"]);
                        if (currentElement.length < 1) {
                            currentElement = parent_with_content.parentElement.querySelectorAll(blockSettingArray[i]["element"]);
                        }
                    }

                    if (blockSettingArray[i]["elementPlace"] < 0) {
                        sumResult = currentElement.length + blockSettingArray[i]["elementPlace"];
                        if (sumResult >= 0 && sumResult < currentElement.length) {
                            currentElement = currentElement[sumResult];
                            currentElement = getFromConstructions(currentElement);
                            if (excIdClass&&excIdClass.length > 0) {
                                for (let i2 = 0; i2 < excIdClass.length; i2++) {
                                    if (excIdClass[i2].length > 0) {
                                        currentElement = blocksRepositionUse(excIdClass[i2], currentElement, 'marked');
                                    } 
                                }
                            }
                            if (currentElement) {
                                currentElementChecker = true;
                            }
                        }
                    } else {
                        sumResult = blockSettingArray[i]["elementPlace"] - 1;
                        if (sumResult < currentElement.length) {
                            currentElement = currentElement[sumResult];
                            currentElement = getFromConstructions(currentElement);
                            if (excIdClass&&excIdClass.length > 0) {
                                for (let i2 = 0; i2 < excIdClass.length; i2++) {
                                    if (excIdClass[i2].length > 0) {
                                        currentElement = blocksRepositionUse(excIdClass[i2], currentElement, 'marked');
                                    }
                                }
                            }
                            if (currentElement) {
                                currentElementChecker = true;
                            }
                        }
                    }

                    if (currentElement != undefined && currentElement != null && currentElementChecker) {
                        posCurrentElement = initTargetToInsert(blockSettingArray);
                        currentElement.parentNode.insertBefore(elementToAdd, posCurrentElement);
                        elementToAdd.classList.remove('coveredAd');
                        // usedAdBlocksArray.push(checkIfBlockUsed);
                        usedBlockSettingArrayIds.push(block_number);
                        blockSettingArray.splice(i, 1);
                        poolbackI = 1;
                        i--;
                    } else {
                        repeat = true;
                    }
                } else if (blockSettingArray[i]["setting_type"] == 2) {
                    if (blockDuplicate == 'no') {
                        blockSettingArray[i]["elementCount"] = 1;
                    }
                    repeatableCurrentElement = [];
                    reCou = 0;
                    curFirstPlace = blockSettingArray[i]["firstPlace"];
                    curElementCount = blockSettingArray[i]["elementCount"];
                    curElementStep = blockSettingArray[i]["elementStep"];
                    repeatableSuccess = false;

                    elementToAddStyle = createStyleElement(block_number, blockSettingArray[i]["elementCss"]);

                    if (blockSettingArray[i]["element"].toLowerCase()=='h1') {
                        placingToH1(parent_with_content, blockSettingArray[i]["element"]);
                    } else if (blockSettingArray[i]["element"].toLowerCase()=='h2-4') {
                        repeatableCurrentElement = parent_with_content.querySelectorAll('h2,h3,h4');
                        if (repeatableCurrentElement.length < 1) {
                            repeatableCurrentElement = parent_with_content.parentElement.querySelectorAll('h2,h3,h4');
                        }
                    } else {
                        repeatableCurrentElement = parent_with_content.querySelectorAll(blockSettingArray[i]["element"]);
                        if (repeatableCurrentElement.length < 1) {
                            repeatableCurrentElement = parent_with_content.parentElement.querySelectorAll(blockSettingArray[i]["element"]);
                        }
                    }

                    for (let i1 = 0; i1 < blockSettingArray[i]["elementCount"]; i1++) {
                        currentElementChecker = false;
                        let repElementToAdd = document.createElement("div");
                        repElementToAdd.classList.add("percentPointerClass");
                        repElementToAdd.classList.add("marked");
                        if (blockSettingArray[i]["sc"]==1) {
                            repElementToAdd.classList.add("scMark");
                        }
                        repElementToAdd.innerHTML = blockSettingArray[i]["text"];

                        if (elementToAddStyle&&elementToAddStyle!='default') {
                            repElementToAdd.style.textAlign = elementToAddStyle;
                        }

                        sumResult = Math.round(parseInt(blockSettingArray[i]["firstPlace"]) + (i1*parseInt(blockSettingArray[i]["elementStep"])) - 1);
                        if (sumResult < repeatableCurrentElement.length) {
                            currentElement = repeatableCurrentElement[sumResult];
                            currentElement = getFromConstructions(currentElement);
                            if (excIdClass&&excIdClass.length > 0) {
                                for (let i2 = 0; i2 < excIdClass.length; i2++) {
                                    if (excIdClass[i2].length > 0) {
                                        currentElement = blocksRepositionUse(excIdClass[i2], currentElement, 'marked');
                                    }
                                }
                            }
                            if (currentElement) {
                                currentElementChecker = true;
                            }
                        }

                        if (currentElement != undefined && currentElement != null && currentElementChecker) {
                            posCurrentElement = initTargetToInsert(blockSettingArray);
                            currentElement.parentNode.insertBefore(repElementToAdd, posCurrentElement);
                            repElementToAdd.classList.remove('coveredAd');
                            // usedAdBlocksArray.push(checkIfBlockUsed);
                            curFirstPlace = sumResult + parseInt(blockSettingArray[i]["elementStep"]) + 1;
                            curElementCount--;
                            repeatableSuccess = true;
                        } else {
                            repeatableSuccess = false;
                            break;
                        }
                    }
                    if (repeatableSuccess==true) {
                        usedBlockSettingArrayIds.push(block_number);
                        blockSettingArray.splice(i, 1);
                        poolbackI = 1;
                        i--;
                    } else {
                        if (!blockSettingArray[i]["unsuccess"]) {
                            blockSettingArray[i]["unsuccess"] = 1;
                        } else {
                            blockSettingArray[i]["unsuccess"] = Math.round(blockSettingArray[i]["unsuccess"] + 1);
                        }
                        if (blockSettingArray[i]["unsuccess"] > 10) {
                            usedBlockSettingArrayIds.push(block_number);
                            blockSettingArray.splice(i, 1);
                            poolbackI = 1;
                            i--;
                        } else {
                            blockSettingArray[i]["firstPlace"] = curFirstPlace;
                            blockSettingArray[i]["elementCount"] = curElementCount;
                            blockSettingArray[i]["elementStep"] = curElementStep;
                            repeat = true;
                        }
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
                            if (elementTypeSymbol < 0) {
                                elementName = '.' + elementName;
                            } else {
                                if (blockSettingArray[i]['element']) {
                                    if (blockSettingArray[i]['element']=='h2-4') {
                                        elementName = 'h2'+elementName+',h3'+elementName+',h4'+elementName;
                                    } else {
                                        elementName = blockSettingArray[i]['element']+elementName;
                                    }
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
                        elementToAdd.classList.remove('coveredAd');
                        // usedAdBlocksArray.push(checkIfBlockUsed);
                        usedBlockSettingArrayIds.push(block_number);
                        blockSettingArray.splice(i, 1);
                        poolbackI = 1;
                        i--;
                    } else {
                        repeat = true;
                    }
                } else if (blockSettingArray[i]["setting_type"] == 4) {
                    parent_with_content.append(elementToAdd);
                    // usedAdBlocksArray.push(checkIfBlockUsed);
                    usedBlockSettingArrayIds.push(block_number);
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
                        if (excIdClass&&excIdClass.length > 0) {
                            for (let i2 = 0; i2 < excIdClass.length; i2++) {
                                if (excIdClass[i2].length > 0) {
                                    currentElement = blocksRepositionUse(excIdClass[i2], currentElement, 'marked');
                                }
                            }
                        }
                        if (currentElement != undefined && currentElement != null) {
                            if (pCount > 1) {
                                currentElement.parentNode.insertBefore(elementToAdd, currentElement);
                            } else {
                                currentElement.parentNode.insertBefore(elementToAdd, currentElement.nextSibling);
                            }
                            elementToAdd.classList.remove('coveredAd');
                            // usedAdBlocksArray.push(checkIfBlockUsed);
                            usedBlockSettingArrayIds.push(block_number);
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
                                    // usedAdBlocksArray.push(checkIfBlockUsed);
                                    usedBlockSettingArrayIds.push(block_number);
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
                                // usedAdBlocksArray.push(checkIfBlockUsed);
                                usedBlockSettingArrayIds.push(block_number);
                                blockSettingArray.splice(i, 1);
                                poolbackI = 1;
                                i--;
                                break;
                            }
                        }
                    } else {
                        containerFor6th.push(blockSettingArray[i]);
                        // usedAdBlocksArray.push(checkIfBlockUsed);
                        usedBlockSettingArrayIds.push(block_number);
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
                                    // usedAdBlocksArray.push(checkIfBlockUsed);
                                    usedBlockSettingArrayIds.push(block_number);
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
                                // usedAdBlocksArray.push(checkIfBlockUsed);
                                usedBlockSettingArrayIds.push(block_number);
                                blockSettingArray.splice(i, 1);
                                poolbackI = 1;
                                i--;
                                break;
                            }
                        }
                    } else {
                        containerFor7th.push(blockSettingArray[i]);
                        // usedAdBlocksArray.push(checkIfBlockUsed);
                        usedBlockSettingArrayIds.push(block_number);
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
        shortcodesInsert();
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
        if (!endedSc) {
            shortcodesInsert();
        }
        if (!endedCc) {
            // clearUnsuitableCache(0);
        }
        blocksReposition();
        // cachePlacing();
    } else {
        // console.log('wait-async-blocks-launch-alert');
        setTimeout(function () {
            asyncFunctionLauncher();
        }, 50);
    }
}
// asyncFunctionLauncher();

function old_asyncInsertingsInsertingFunction(insertingsArray) {
    let currentElementForInserting = 0;
    let positionElement = 0;
    let position = 0;
    let insertToAdd = 0;
    let repeatSearch = 0;
    if (insertingsArray&&insertingsArray.length > 0) {
        for (let i = 0; i < insertingsArray.length; i++) {
            if (!insertingsArray[i]['used']||(insertingsArray[i]['used']&&insertingsArray[i]['used']==0)) {
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
            if (!insertingsArray[i]['used']||(insertingsArray[i]['used']&&insertingsArray[i]['used']==0)) {
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
        }, 100)
    }
}

function insertingsFunctionLaunch() {
    if (window.jsInsertingsLaunch !== undefined&&jsInsertingsLaunch == 25) {
        asyncInsertingsInsertingFunction(insertingsArray);
    } else {
        setTimeout(function () {
            insertingsFunctionLaunch();
        }, 100)
    }
}

function setLongCache() {
    let xhttp = new XMLHttpRequest();
    let sendData = 'action=setLongCache&type=longCatching';
    xhttp.onreadystatechange = function(redata) {
        if (this.readyState == 4 && this.status == 200) {
            console.log('long cache deployed');
        }
    };
    xhttp.open("POST", adg_object_ad.ajax_url, true);
    xhttp.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
    xhttp.send(sendData);
}

function cachePlacing(alert_type, errorInfo=null) {
    let adBlocks = document.querySelectorAll('.percentPointerClass .content_rb');
    let curAdBlock;
    let okStates = ['done','refresh-wait','no-block','fetched'];
    // let adId = -1;
    let blockStatus = null;
    let blockId;

    if (adBlocks&&adBlocks.length > 0) {
        for (let i = 0; i < adBlocks.length; i++) {
            blockStatus = null;
            blockStatus = adBlocks[i]['dataset']['state'];

            if (!blockStatus) {
                blockId = adBlocks[i]['dataset']['id'];
                if (cachedBlocksArray&&cachedBlocksArray[blockId]) {
                    // adBlocks[i].innerHTML = cachedBlocksArray[blockId];
                    jQuery(adBlocks[i]).html(cachedBlocksArray[blockId]);
                }
            }
        }
    }

    if (alert_type&&alert_type=='high') {
        setLongCache();
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
        // var possibleTagsArray = ["P", "H1", "H2", "H3", "H4", "H5", "H6", "DIV", "OL", "UL", "LI", "BLOCKQUOTE", "INDEX", "TABLE", "ARTICLE"];
        var possibleTagsArray = ["P", "H1", "H2", "H3", "H4", "H5", "H6", "DIV", "BLOCKQUOTE", "INDEX", "ARTICLE"];
        let possibleTagsInCheck = ["DIV", "INDEX"];
        let numberToUse = 0;
        let previousBreak = 0;
        let cycle_1_val;
        let cycle_2_val;
        let needleLength;
        let currentSumLength;
        let elementToAdd;
        let elementToBind;
        let elementToAddStyle;
        let block_number;

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
                elementToAdd.classList.add("marked");
                if (containerFor7th[i]["sc"]==1) {
                    elementToAdd.classList.add("scMark");
                }
                elementToAdd.innerHTML = containerFor7th[i]["text"];
                block_number = elementToAdd.children[0].attributes['data-id'].value;
                if (!elementToAdd) {
                    continue;
                }

                elementToAddStyle = createStyleElement(block_number, containerFor7th[i]["elementCss"]);

                if (elementToAddStyle&&elementToAddStyle!='default') {
                    elementToAdd.style.textAlign = elementToAddStyle;
                }


                if (containerFor7th[i]['elementPlace'] < 0) {
                    for (let j = tlArray.length-1; j > -1; j--) {
                        currentSumLength = currentSumLength + tlArray[j]['length'];
                        if (needleLength < currentSumLength) {
                            elementToBind = tlArray[j]['element'];
                            if (excIdClass&&excIdClass.length > 0) {
                                for (let i2 = 0; i2 < excIdClass.length; i2++) {
                                    if (excIdClass[i2].length > 0) {
                                        elementToBind = blocksRepositionUse(excIdClass[i2], elementToBind, 'marked');
                                    }
                                }
                            }
                            elementToBind.parentNode.insertBefore(elementToAdd, elementToBind);
                            elementToAdd.classList.remove('coveredAd');
                            break;
                        } else {
                            if (j == 0) {
                                tlArray[j]['element'].parentNode.insertBefore(elementToAdd, tlArray[tlArray.length-1]['element'].nextSibling);
                                elementToAdd.classList.remove('coveredAd');
                                break;
                            }
                        }
                    }
                } else if (containerFor7th[i]['elementPlace'] == 0) {
                    elementToBind = tlArray[0]['element'];
                    if (excIdClass&&excIdClass.length > 0) {
                        for (let i2 = 0; i2 < excIdClass.length; i2++) {
                            if (excIdClass[i2].length > 0) {
                                elementToBind = blocksRepositionUse(excIdClass[i2], elementToBind, 'marked');
                            }
                        }
                    }
                    elementToBind.parentNode.insertBefore(elementToAdd, elementToBind);
                    elementToAdd.classList.remove('coveredAd');
                } else {
                    for (let j = 0; j < tlArray.length; j++) {
                        currentSumLength = currentSumLength + tlArray[j]['length'];
                        if (needleLength < currentSumLength) {
                            elementToBind = tlArray[j]['element'];
                            if (excIdClass&&excIdClass.length > 0) {
                                for (let i2 = 0; i2 < excIdClass.length; i2++) {
                                    if (excIdClass[i2].length > 0) {
                                        elementToBind = blocksRepositionUse(excIdClass[i2], elementToBind, 'marked');
                                    }
                                }
                            }
                            // elementToBind.parentNode.insertBefore(elementToAdd, tlArray[j]['element'].nextSibling);
                            elementToBind.parentNode.insertBefore(elementToAdd, elementToBind.nextSibling);
                            elementToAdd.classList.remove('coveredAd');
                            break;
                        } else {
                            if (j == tlArray.length-1) {
                                elementToBind = tlArray[j]['element'];
                                if (excIdClass&&excIdClass.length > 0) {
                                    for (let i2 = 0; i2 < excIdClass.length; i2++) {
                                        if (excIdClass[i2].length > 0) {
                                            elementToBind = blocksRepositionUse(excIdClass[i2], elementToBind, 'marked');
                                        }
                                    }
                                }
                                elementToBind.parentNode.insertBefore(elementToAdd, elementToBind.nextSibling);
                                elementToAdd.classList.remove('coveredAd');
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
        var arrCou = [];
        var arrCouLast = [];
        var possibleTagsArray = ["P", "H1", "H2", "H3", "H4", "H5", "H6", "DIV", "OL", "UL", "LI", "BLOCKQUOTE", "INDEX", "TABLE", "ARTICLE"];
        var possibleTagsInCheck = ["DIV", "INDEX"];
        var deniedClasses = ["percentPointerClass","content_rb","textLengthMarker"];
        var deniedId = ["toc_container"];
        var numberToUse = 0;
        var previousBreak = 0;
        var toNextElement = 0;
        let elementToAdd;
        let wof_wof = 2;
        let hof_hof = 2;
        var penyok_stoparik = 0;
        var gatheredTextLength = 0;
        var lceCou = 0;
        var lastLceCou = 0;
        var elementToBind;
        let elementToAddStyle;
        let block_number;
        // var checkIfBlockUsed = 0;

        function textLengthMeter(i, usedElement, deepLvl) {
            let localtextLength = 0;
            if (deepLvl==1) {
                penyok_stoparik = 1;
            }
            if (usedElement.tagName == 'TABLE') {
                penyok_stoparik = 1;
            }
            let elementDeniedClasses = false;
            let elementDeniedIds = false;
            let deeperLocalTextLength = 0;
            if (possibleTagsArray.includes(usedElement.tagName)) {
                for (let cou = 0; cou < deniedClasses.length; cou++) {
                    if (usedElement.classList.contains(deniedClasses[cou])) {
                        elementDeniedClasses = true;
                    }
                }
                if (!elementDeniedClasses) {
                    for (let cou = 0; cou < deniedId.length; cou++) {
                        if (usedElement.id == deniedId[cou]) {
                            elementDeniedIds = true;
                        }
                    }
                }
                if (!elementDeniedClasses&&!elementDeniedIds) {
                    if (possibleTagsInCheck.includes(usedElement.tagName)&&usedElement.children.length > 1) {
                        for (let j = 0; j < usedElement.children.length; j++) {
                            deeperLocalTextLength = textLengthMeter(j,usedElement.children[j], deepLvl+1);
                            // localtextLength = localtextLength + textLengthMeter(j,usedElement.children[j], deepLvl+1);
                            localtextLength = localtextLength + deeperLocalTextLength;
                            gatheredTextLength = gatheredTextLength + deeperLocalTextLength;
                        }
                    } else {
                        localtextLength = localtextLength + usedElement.innerText.length;
                        gatheredTextLength = gatheredTextLength + usedElement.innerText.length;
                    }
                    let lcElementToAdd = document.createElement("div");
                    lcElementToAdd.classList.add("textLengthMarker");
                    lcElementToAdd.classList.add("hidden");
                    lcElementToAdd.setAttribute('data-id', gatheredTextLength);
                    lcElementToAdd.setAttribute('data-number', lceCou);
                    lcElementToAdd.style.margin = '0';
                    lcElementToAdd.style.height = '0';
                    lcElementToAdd.style.width = '0';
                    lceCou++;

                    usedElement.parentNode.insertBefore(lcElementToAdd, usedElement.nextSibling);
                    // elementToAdd.style.display = 'block';
                }
            }
            return localtextLength;
        }

        if (!document.getElementById("markedSpan")) {
            textLength = 0;
            for (let i = 0; i < lordOfElements.children.length; i++) {
                let returnedTextLength = 0;
                returnedTextLength = textLengthMeter(i,lordOfElements.children[i], 1);
                textLength = textLength + returnedTextLength;
            }

            function insertByPercentsNew(j) {
                let perfectPlace = document.querySelectorAll('.textLengthMarker');
                let localMiddleValue = 0;

                if (perfectPlace.length > 0) {
                    for (let i = 0; i < perfectPlace.length; i++) {
                        if (perfectPlace[i].getAttribute('data-id') > textNeedyLength) {
                            // elementToAdd = document.querySelector('.percentPointerClass[data-id="'+containerFor6th[j]['id']+'"]');
                            elementToAdd = document.createElement("div");
                            elementToAdd.classList.add("percentPointerClass");
                            elementToAdd.classList.add("marked");
                            if (containerFor6th[j]["sc"]==1) {
                                elementToAdd.classList.add("scMark");
                            }
                            elementToAdd.innerHTML = containerFor6th[j]["text"];
                            block_number = elementToAdd.children[0].attributes['data-id'].value;

                            elementToAddStyle = createStyleElement(block_number, containerFor6th[j]["elementCss"]);
                            if (elementToAddStyle&&elementToAddStyle!='default') {
                                elementToAdd.style.textAlign = elementToAddStyle;
                            }

                            if (!elementToAdd) {
                                return false;
                            }

                            if (i > 0) {
                                localMiddleValue = perfectPlace[i].getAttribute('data-id') - perfectPlace[i-1].getAttribute('data-id');
                                localMiddleValue = perfectPlace[i].getAttribute('data-id') - Math.round(localMiddleValue/2);
                            } else {
                                localMiddleValue = Math.round(perfectPlace[i].getAttribute('data-id')/2);
                            }

                            elementToBind = perfectPlace[i];
                            if (excIdClass&&excIdClass.length > 0) {
                                for (let i2 = 0; i2 < excIdClass.length; i2++) {
                                    if (excIdClass[i2].length > 0) {
                                        elementToBind = blocksRepositionUse(excIdClass[i2], elementToBind, 'marked');
                                    }
                                }
                            }

                            if (textNeedyLength < localMiddleValue) {
                                elementToBind.parentNode.insertBefore(elementToAdd, elementToBind.previousSibling);
                            } else {
                                elementToBind.parentNode.insertBefore(elementToAdd, elementToBind);
                            }
                            // usedAdBlocksArray.push(checkIfBlockUsed);
                            elementToAdd.classList.remove('coveredAd');
                            return false;
                        }
                        if (i > 1) {
                            perfectPlace[i-2].remove();
                        }
                    }
                    return false;
                }
            }

            function clearTlMarks() {
                let marksForDeleting = document.querySelectorAll('.textLengthMarker');

                if (marksForDeleting.length > 0) {
                    for (let i = 0; i < marksForDeleting.length; i++) {
                        marksForDeleting[i].remove();
                    }
                }
            }

            let insLevel = 1;
            arrCouLast[insLevel] = 0;
            for (let j = 0; j < containerFor6th.length; j++) {
                previousBreak = 0;
                toNextElement = 0;
                // textNeedyLength = Math.round(textLength * (containerFor6th[j]["elementPlace"]/100));
                textNeedyLength = Math.round(gatheredTextLength * (containerFor6th[j]["elementPlace"]/100));
                insertByPercentsNew(j);
            }
            clearTlMarks();
            var spanMarker = document.createElement("span");
            spanMarker.setAttribute("id", "markedSpan");
            lordOfElements.prepend(spanMarker);
        }
    } catch (e) {}
}