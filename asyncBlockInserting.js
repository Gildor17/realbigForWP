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

// "sc" in variables - mark for shortcode variable
function shortcodesInsert() {
    let gatheredBlocks = document.querySelectorAll('.percentPointerClass.scMark');
    let scBlockId = -1;
    let scAdId = -1;
    let blockStatus = '';
    let gatheredBlockChild;
    let okStates = ['done','refresh-wait','no-block','fetched'];
    let scContainer;
    let scRepeatFuncLaunch = false;

    if (gatheredBlocks&&gatheredBlocks.length > 0) {
        for (let i = 0; i < gatheredBlocks.length; i++) {
            gatheredBlockChild = gatheredBlocks[i].children[0];
            if (!gatheredBlockChild) {
                continue;
            }
            scAdId = -3;
            blockStatus = null;
            scContainer = null;

            scAdId = gatheredBlockChild.getAttribute('data-aid');
            blockStatus = gatheredBlockChild.getAttribute('data-state');

            if (scAdId > 0) {
                if (blockStatus&&okStates.includes(blockStatus)) {
                    scContainer = gatheredBlocks[i].parentElement.querySelector('.shortcodes[data-id="'+scAdId+'"]');
                    if (scContainer) {
                        if (blockStatus=='no-block') {
                            gatheredBlockChild.innerHTML = '';
                        } else {
                            gatheredBlockChild.innerHTML = scContainer.innerHTML;
                        }
                        scContainer.remove();
                    }
                    gatheredBlocks[i].classList.remove('scMark');
                } else {
                    scRepeatFuncLaunch = true;
                }
            } else if (scAdId == -3||scAdId === null) {
                scRepeatFuncLaunch = true;
            }
        }

        if (scRepeatFuncLaunch) {
            // console.log('shortcodes-alert');
            setTimeout(function () {
                shortcodesInsert();
            }, 100);
        }
    } else {
        endedSc = true;
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
                // console.log('cache-alert:'+cuc_cou);
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
        // if (searchType == 'marked') {
        //     blocksInContainer = document.querySelector(blType + containerString + blockStrJs);
        //     if (blocksInContainer) {
        //         currentBlock = blocksInContainer;
        //         currentBlockId = currentBlock.querySelector('.content_rb').getAttribute('data-id');
        //         currentContainer = null;
        //         for (j = 0; j < usedBlockSettingArray.length; i++) {
        //             if (usedBlockSettingArray[i]['id'] == currentBlockId) {
        //                 currentBlockPosition = usedBlockSettingArray[i]['elementPosition'];
        //                 currentContainer = currentBlock.closest(blType + containerString);
        //                 if (currentBlockPosition == 0) {
        //                     currentContainer.parentNode.insertBefore(currentBlock, currentContainer);
        //                 } else {
        //                     currentContainer.parentNode.insertBefore(currentBlock, currentContainer.nextSibling);
        //                 }
        //                 break;
        //             }
        //         }
        //     }
        // } else if (searchType == 'non-marked') {
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
            blocksRepositionUse(excIdClass[i], '', markingString);
        }
    }

}

function asyncBlocksInsertingFunction(blockSettingArray, contentLength) {
    try {
        var content_pointer = document.querySelector("#content_pointer_id");
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
                elementToAdd = document.querySelector('.percentPointerClass.coveredAd[data-id="'+blockSettingArray[i]['id']+'"]');
                if (!elementToAdd) {
                    continue;
                }

                if (blockSettingArray[i]["minHeaders"] > 0) {
                    if (blockSettingArray[i]["minHeaders"] > termorarity_parent_with_content_length) {
                        continue;
                    }
                }
                if (blockSettingArray[i]["maxHeaders"] > 0) {
                    if (blockSettingArray[i]["maxHeaders"] < termorarity_parent_with_content_length) {
                        continue;
                    }
                }
                if (blockSettingArray[i]["minSymbols"] > contentLength) {
                    continue;
                }
                if (blockSettingArray[i]["maxSymbols"] > 0) {
                    if (blockSettingArray[i]["maxSymbols"] < contentLength) {
                        continue;
                    }
                }

                if (blockSettingArray[i]["setting_type"] == 1) {
                    function placingToH1(usedElement, elementTagToFind) {
                        currentElement = usedElement.querySelectorAll(elementTagToFind);
                        if (currentElement.length < 1) {
                            if (usedElement.parentElement) {
                                placingToH1(usedElement.parentElement, elementTagToFind);
                            }
                        }
                    }

                    if (blockSettingArray[i]["element"].toLowerCase()=='h1') {
                        placingToH1(parent_with_content, blockSettingArray[i]["element"]);
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
                                    currentElement = blocksRepositionUse(excIdClass[i2], currentElement, 'marked');
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
                                    currentElement = blocksRepositionUse(excIdClass[i2], currentElement, 'marked');
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
                        elementToAdd.classList.remove('coveredAd');

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
                        if (excIdClass&&excIdClass.length > 0) {
                            for (let i2 = 0; i2 < excIdClass.length; i2++) {
                                currentElement = blocksRepositionUse(excIdClass[i2], currentElement, 'marked');
                            }
                        }
                        if (currentElement != undefined && currentElement != null) {
                            if (pCount > 1) {
                                currentElement.parentNode.insertBefore(elementToAdd, currentElement);
                                elementToAdd.classList.remove('coveredAd');
                            } else {
                                currentElement.parentNode.insertBefore(elementToAdd, currentElement.nextSibling);
                                elementToAdd.classList.remove('coveredAd');
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

        // here
        // percentSeparator(lordOfElements);
        // end of here

        if (containerFor6th.length > 0) {
            percentInserter(lordOfElements, containerFor6th);
        }
        if (containerFor7th.length > 0) {
            symbolInserter(lordOfElements, containerFor7th);
        }
        let stopper = 0;

        window.addEventListener('load', function () {
            if (repeat = true) {
                // console.log('async-blocks-alert');
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
        // console.log('insertings-alert');
        setTimeout(function () {
            asyncInsertingsInsertingFunction(insertingsArray);
        }, 100)
    }
}

// function launchInsertingsFunctionLaunch() {
//     if (typeof insertingsFunctionLaunch !== 'undefined' && typeof insertingsFunctionLaunch === 'function') {
//         console.log("Insertings function found;");
//         insertingsFunctionLaunch();
//     } else {
//         console.log("Insertings function not found;");
//         setTimeout(function () {
//             launchInsertingsFunctionLaunch();
//         }, 100)
//     }
// }

function insertingsFunctionLaunch() {
    if (window.jsInsertingsLaunch !== undefined&&jsInsertingsLaunch == 25) {
        asyncInsertingsInsertingFunction(insertingsArray);
    } else {
        // console.log('insertings-launch-alert');
        setTimeout(function () {
            insertingsFunctionLaunch();
        }, 100)
    }
}
// insertingsFunctionLaunch();

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

function percentSeparator(lordOfElements) {
    var lcSeparator = lordOfElements.children;
    var lcLordOfElementsResult = 0;
    var lcLordOfElementsTextResult = "";
    var lcTextLength;
    var lcLengthPercent = 0;
    var lcTextNeedyLength = 0;
    var lcCurrentChildrenLength = 0;
    var lcPreviousChildrenLength = 0;
    var lcSeparatorResult = [];
    var lcSeparatorResultCounter = 0;
    var lcLastICounterValue = 0;
    var lcPossibleTagsArray = ["P", "H1", "H2", "H3", "H4", "H5", "H6", "DIV", "OL", "UL", "LI", "BLOCKQUOTE", "INDEX", "TABLE", "ARTICLE"];
    var lcPossibleTagsInCheck = ["DIV", "INDEX"];
    var lcDeniedClasses = ["percentPointerClass","content_rb"];
    var lcDeniedId = ["toc_container"];

    if (!document.getElementById("lcMarkedSpan")) {
        // lcLengthPercent = [10,25,43,60,82,97];
        lcTextLength = 0;
        for (let i = 0; i < lordOfElements.children.length; i++) {
            if (lordOfElements.children[i].tagName!="SCRIPT"&&!lordOfElements.children[i].classList.contains("percentPointerClass")) {
                lcTextLength = lcTextLength + lordOfElements.children[i].innerText.length;
            }
        }

        let lcnumberToUse = 0;
        for (let j = 0; j < 101; j++) {
            // lcTextNeedyLength = Math.round(lcTextLength * (lcLengthPercent[j]/100));
            lcTextNeedyLength = Math.round(lcTextLength * (j/100));
            // for (let i = 0; i < Math.round(lordOfElements.children.length/2); i++) {

            for (let i = lcLastICounterValue; i < lordOfElements.children.length; i++) {
                if (lordOfElements.children[i].tagName!="SCRIPT"&&!lordOfElements.children[i].classList.contains("percentPointerClass")) {
                    if (lcCurrentChildrenLength >= lcTextNeedyLength) {
                        let elementToAdd = document.createElement("div");
                        elementToAdd.classList.add("percentPointerClass");
                        // elementToAdd.innerHTML = "<div style='border: 1px solid grey; font-size: 20px; height: 25px; width: auto; background-color: #2aabd2'>"+lcLengthPercent[j]+"</div>";
                        elementToAdd.innerHTML = "<div style='border: 1px solid grey; font-size: 20px; height: 25px; width: auto; background-color: #2aabd2; clear:both;'>"+j+"</div>";
                        if (i > 0) {
                            lcnumberToUse = i - 1;
                        } else {
                            lcnumberToUse = i;
                        }
                        if (lcPreviousChildrenLength==0||((lcCurrentChildrenLength - Math.round(lcPreviousChildrenLength/2)) >= lcTextNeedyLength)) {
                            lordOfElements.children[lcnumberToUse].parentNode.insertBefore(elementToAdd, lordOfElements.children[i]);
                        } else {
                            lordOfElements.children[lcnumberToUse].parentNode.insertBefore(elementToAdd, lordOfElements.children[i].nextSibling);
                        }
                        lcLastICounterValue = i;
                        break;
                    }
                    lcLordOfElementsTextResult = lcLordOfElementsTextResult + " " + lordOfElements.children[i].innerText;
                    lcLordOfElementsResult = lcLordOfElementsResult + lordOfElements.children[i].innerText.length;
                    lcPreviousChildrenLength = lordOfElements.children[i].innerText.length;
                    lcCurrentChildrenLength = lcLordOfElementsResult;
                }
            }
        }
        var spanMarker = document.createElement("span");
        spanMarker.setAttribute("id", "lcMarkedSpan");
        lordOfElements.prepend(spanMarker);
    }

    for (let i = 0; i < lcSeparator.length; i++) {
        if (["P","UL","OL"].includes(lcSeparator[i].tagName)) {
            lcSeparatorResult[lcSeparatorResultCounter] = lcSeparator[i];
            lcSeparatorResultCounter++;
        } else if (lcSeparator[i].tagName=="BLOCKQUOTE"&&lcSeparator[i].children.length==1&&lcSeparator[i].children[0].tagName=="P") {
            lcSeparatorResult[lcSeparatorResultCounter] = lcSeparator[i];
            lcSeparatorResultCounter++;
        }
    }
}

// function multifilesTest() {
//
// }

function symbolInserter(lordOfElements, containerFor7th) {
    try {
        var separator = lordOfElements.children;
        var lordOfElementsResult = 0;
        var lordOfElementsTextResult = "";
        var textLength;
        let tlArray = [];
        let tlArrayCou = 0;
        var currentChildrenLength = 0;
        var possibleTagsArray = ["P", "H1", "H2", "H3", "H4", "H5", "H6", "DIV", "OL", "UL", "BLOCKQUOTE", "INDEX", "ARTICLE"];
        let possibleTagsInCheck = ["DIV", "INDEX"];
        let numberToUse = 0;
        let previousBreak = 0;
        let cycle_1_val;
        let cycle_2_val;
        let needleLength;
        let currentSumLength;
        let elementToAdd;
        let elementToBind;

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

                // elementToAdd = document.createElement("div");
                // elementToAdd.classList.add("percentPointerClass");
                // elementToAdd.innerHTML = containerFor7th[i]["text"];
                // elementToAdd.style.margin = '5px 0px';
                // elementToAdd.style.display = 'block';

                elementToAdd = document.querySelector('.percentPointerClass[data-id="'+containerFor7th[i]['id']+'"]');
                if (!elementToAdd) {
                    continue;
                }

                if (containerFor7th[i]['elementPlace'] < 0) {
                    for (let j = tlArray.length-1; j > -1; j--) {
                        currentSumLength = currentSumLength + tlArray[j]['length'];
                        if (needleLength < currentSumLength) {
                            elementToBind = tlArray[j]['element'];
                            if (excIdClass&&excIdClass.length > 0) {
                                for (let i2 = 0; i2 < excIdClass.length; i2++) {
                                    elementToBind = blocksRepositionUse(excIdClass[i2], elementToBind, 'marked');
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
                            elementToBind = blocksRepositionUse(excIdClass[i2], elementToBind, 'marked');
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
                                    elementToBind = blocksRepositionUse(excIdClass[i2], elementToBind, 'marked');
                                }
                            }
                            elementToBind.parentNode.insertBefore(elementToAdd, tlArray[j]['element'].nextSibling);
                            elementToAdd.classList.remove('coveredAd');
                            break;
                        } else {
                            if (j == tlArray.length-1) {
                                elementToBind = tlArray[j]['element'];
                                if (excIdClass&&excIdClass.length > 0) {
                                    for (let i2 = 0; i2 < excIdClass.length; i2++) {
                                        elementToBind = blocksRepositionUse(excIdClass[i2], elementToBind, 'marked');
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
                // if (lordOfElements.children[i].tagName!="SCRIPT"&&!lordOfElements.children[i].classList.contains("percentPointerClass")) {
                // if (wof_wof==1) {
                    // if (possibleTagsArray.includes(lordOfElements.children[i].tagName)&&!lordOfElements.children[i].classList.contains("percentPointerClass")&&lordOfElements.children[i].id!="toc_container") {
                    //     if (possibleTagsInCheck.includes(lordOfElements.children[i].tagName)) {
                    //         if (lordOfElements.children[i].children.length > 1) {
                    //             for (let j = 0; j < lordOfElements.children[i].children.length; j++) {
                    //                 if (possibleTagsArray.includes(lordOfElements.children[i].children[j].tagName)&&!lordOfElements.children[i].children[j].classList.contains("percentPointerClass")&&lordOfElements.children[i].children[j].id!="toc_container") {
                    //                     textLength = textLength + lordOfElements.children[i].children[j].innerText.length;
                    //                 }
                    //             }
                    //         }
                    //     } else {
                    //         textLength = textLength + lordOfElements.children[i].innerText.length;
                    //     }
                    // }
                // } else {
                    returnedTextLength = textLengthMeter(i,lordOfElements.children[i], 1);
                    textLength = textLength + returnedTextLength;
                // }
            }

            function insertByPercents(i, j, usedElement, insLevel) {
                let elementDeniedClasses = false;
                let elementDeniedIds = false;
                if (usedElement[i].tagName == 'TABLE') {
                    penyok_stoparik = 1;
                }
                if (possibleTagsArray.includes(usedElement[i].tagName)) {
                    for (let cou = 0; cou < deniedClasses.length; cou++) {
                        if (usedElement[i].classList.contains(deniedClasses[cou])) {
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
                        if (possibleTagsInCheck.includes(usedElement[i].tagName)) {
                            if (usedElement[i].children.length > 0) {
                                if (!arrCouLast[insLevel+1]||arrCouLast[insLevel+1] < 0) {
                                    arrCouLast[insLevel+1] = 0;
                                }
                                for (let j1 = arrCouLast[insLevel+1]; j1 < usedElement[i].children.length; j1++) {
                                    insertByPercents(j1, j, usedElement[i].children, insLevel+1);
                                    if (toNextElement==1) {
                                        arrCouLast[insLevel] = i;
                                        return false;
                                    }
                                    if (j1 == (usedElement[i].children.length - 1)) {
                                        arrCouLast[insLevel+1] = -1;
                                    }
                                }
                            }
                        } else {
                            if (currentChildrenLength >= textNeedyLength) {
                                elementToAdd = document.querySelector('.percentPointerClass[data-id="'+containerFor6th[j]['id']+'"]');

                                if (i > 0) {
                                    numberToUse = i - 1;
                                } else {
                                    numberToUse = i;
                                }
                                if (previousChildrenLength==0||((currentChildrenLength - Math.round(previousChildrenLength/2)) >= textNeedyLength)) {
                                    if (usedElement[numberToUse].parentElement.tagName.toLowerCase() == "blockquote") {
                                        usedElement[numberToUse].parentElement.parentNode.insertBefore(elementToAdd, usedElement[i]);
                                    } else {
                                        usedElement[numberToUse].parentNode.insertBefore(elementToAdd, usedElement[i]);
                                    }
                                } else {
                                    if (usedElement[numberToUse].parentElement.tagName.toLowerCase() == "blockquote") {
                                        usedElement[numberToUse].parentElement.parentNode.insertBefore(elementToAdd, usedElement[i].nextSibling);
                                    } else {
                                        usedElement[numberToUse].parentNode.insertBefore(elementToAdd, usedElement[i].nextSibling);
                                    }
                                }
                                elementToAdd.classList.remove('coveredAd');
                                arrCouLast[insLevel] = i;
                                toNextElement = 1;
                                return false;
                            }
                            lordOfElementsTextResult = lordOfElementsTextResult + " " + usedElement[i].innerText;
                            lordOfElementsResult = lordOfElementsResult + usedElement[i].innerText.length;
                            previousChildrenLength = usedElement[i].innerText.length;
                            currentChildrenLength = lordOfElementsResult;
                            return false;
                        }
                    }
                }
            }

            function insertByPercentsNew(j) {
                let perfectPlace = document.querySelectorAll('.textLengthMarker');
                let localMiddleValue = 0;

                if (perfectPlace.length > 0) {
                    for (let i = 0; i < perfectPlace.length; i++) {
                        if (perfectPlace[i].getAttribute('data-id') > textNeedyLength) {
                            if (i > 0) {
                                localMiddleValue = perfectPlace[i].getAttribute('data-id') - perfectPlace[i-1].getAttribute('data-id');
                                localMiddleValue = perfectPlace[i].getAttribute('data-id') - Math.round(localMiddleValue/2);
                            } else {
                                localMiddleValue = Math.round(perfectPlace[i].getAttribute('data-id')/2);
                            }

                            elementToAdd = document.querySelector('.percentPointerClass[data-id="'+containerFor6th[j]['id']+'"]');
                            elementToBind = perfectPlace[i];
                            if (excIdClass&&excIdClass.length > 0) {
                                for (let i2 = 0; i2 < excIdClass.length; i2++) {
                                    elementToBind = blocksRepositionUse(excIdClass[i2], elementToBind, 'marked');
                                }
                            }

                            if (textNeedyLength < localMiddleValue) {
                                elementToBind.parentNode.insertBefore(elementToAdd, elementToBind.previousSibling);
                            } else {
                                elementToBind.parentNode.insertBefore(elementToAdd, elementToBind);
                            }
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
                // for (let i = arrCouLast[insLevel]; i < lordOfElements.children.length; i++) {
                //     if (hof_hof==1) {
                //         if (possibleTagsArray.includes(lordOfElements.children[i].tagName)&&!lordOfElements.children[i].classList.contains("percentPointerClass")&&lordOfElements.children[i].id!="toc_container") {
                //             if (possibleTagsInCheck.includes(lordOfElements.children[i].tagName)) {
                //                 if (lordOfElements.children[i].children.length > 0) {
                //                     for (let j1 = lastJ1CounterValue; j1 < lordOfElements.children[i].children.length; j1++) {
                //                         if (possibleTagsArray.includes(lordOfElements.children[i].children[j1].tagName)&&!lordOfElements.children[i].children[j1].classList.contains("percentPointerClass")&&lordOfElements.children[i].children[j1].id!="toc_container") {
                //                             if (currentChildrenLength >= textNeedyLength) {
                //                                 // elementToAdd = document.createElement("div");
                //                                 // elementToAdd.classList.add("percentPointerClass");
                //                                 // elementToAdd.innerHTML = containerFor6th[j]["text"];
                //                                 // elementToAdd.style.margin = '5px 0px';
                //                                 // elementToAdd.style.display = 'block';
                //
                //                                 elementToAdd = document.querySelector('.percentPointerClass[data-id="'+containerFor6th[j]['id']+'"]');
                //
                //                                 if (j1 > 0) {
                //                                     numberToUse = j1 - 1;
                //                                 } else {
                //                                     numberToUse = j;
                //                                 }
                //                                 if (previousChildrenLength==0||((currentChildrenLength - Math.round(previousChildrenLength/2)) >= textNeedyLength)) {
                //                                     if (lordOfElements.children[i].children[numberToUse].parentElement.tagName.toLowerCase() == "blockquote") {
                //                                         lordOfElements.children[i].children[numberToUse].parentElement.parentNode.insertBefore(elementToAdd, lordOfElements.children[i].children[j1]);
                //                                     } else {
                //                                         lordOfElements.children[i].children[numberToUse].parentNode.insertBefore(elementToAdd, lordOfElements.children[i].children[j1]);
                //                                     }
                //                                 } else {
                //                                     if (lordOfElements.children[i].children[numberToUse].parentElement.tagName.toLowerCase() == "blockquote") {
                //                                         lordOfElements.children[i].children[numberToUse].parentElement.parentNode.insertBefore(elementToAdd, lordOfElements.children[i].children[j1].nextSibling);
                //                                     } else {
                //                                         lordOfElements.children[i].children[numberToUse].parentNode.insertBefore(elementToAdd, lordOfElements.children[i].children[j1].nextSibling);
                //                                     }
                //                                 }
                //                                 elementToAdd.classList.remove('coveredAd');
                //                                 lastICounterValue = i;
                //                                 lastJ1CounterValue = j1;
                //                                 previousBreak = 1;
                //                                 break;
                //                             }
                //                             lordOfElementsTextResult = lordOfElementsTextResult + " " + lordOfElements.children[i].children[j1].innerText;
                //                             lordOfElementsResult = lordOfElementsResult + lordOfElements.children[i].children[j1].innerText.length;
                //                             previousChildrenLength = lordOfElements.children[i].children[j1].innerText.length;
                //                             currentChildrenLength = lordOfElementsResult;
                //                         }
                //                     }
                //                     if (previousBreak==1) {
                //                         break;
                //                     }
                //                 }
                //             } else {
                //                 if (currentChildrenLength >= textNeedyLength) {
                //                     // elementToAdd = document.createElement("div");
                //                     // elementToAdd.classList.add("percentPointerClass");
                //                     // elementToAdd.innerHTML = containerFor6th[j]["text"];
                //
                //                     elementToAdd = document.querySelector('.percentPointerClass[data-id="'+containerFor6th[j]['id']+'"]');
                //
                //                     if (i > 0) {
                //                         numberToUse = i - 1;
                //                     } else {
                //                         numberToUse = i;
                //                     }
                //                     if (previousChildrenLength==0||((currentChildrenLength - Math.round(previousChildrenLength/2)) >= textNeedyLength)) {
                //                         if (lordOfElements.children[numberToUse].parentElement.tagName.toLowerCase() == "blockquote") {
                //                             lordOfElements.children[numberToUse].parentElement.parentNode.insertBefore(elementToAdd, lordOfElements.children[i]);
                //                         } else {
                //                             lordOfElements.children[numberToUse].parentNode.insertBefore(elementToAdd, lordOfElements.children[i]);
                //                         }
                //                     } else {
                //                         if (lordOfElements.children[numberToUse].parentElement.tagName.toLowerCase() == "blockquote") {
                //                             lordOfElements.children[numberToUse].parentElement.parentNode.insertBefore(elementToAdd, lordOfElements.children[i].nextSibling);
                //                         } else {
                //                             lordOfElements.children[numberToUse].parentNode.insertBefore(elementToAdd, lordOfElements.children[i].nextSibling);
                //                         }
                //                     }
                //                     elementToAdd.classList.remove('coveredAd');
                //                     lastICounterValue = i;
                //                     break;
                //                 }
                //                 lordOfElementsTextResult = lordOfElementsTextResult + " " + lordOfElements.children[i].innerText;
                //                 lordOfElementsResult = lordOfElementsResult + lordOfElements.children[i].innerText.length;
                //                 previousChildrenLength = lordOfElements.children[i].innerText.length;
                //                 currentChildrenLength = lordOfElementsResult;
                //             }
                //         }
                //     } else {
                //         // insertByPercents(i, j, lordOfElements.children, insLevel);
                //         // if (toNextElement==1) {
                //         //     break;
                //         // }
                //         // if (i == (lordOfElements.children.length - 1)) {
                //         //     arrCouLast[insLevel] = 0;
                //         // }
                //     }
                // }
            }
            clearTlMarks();
            var spanMarker = document.createElement("span");
            spanMarker.setAttribute("id", "markedSpan");
            lordOfElements.prepend(spanMarker);
        }
    } catch (e) {

    }
}