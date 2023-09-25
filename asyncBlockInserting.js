if (typeof endedSc==='undefined') {var endedSc = false;}
if (typeof endedCc==='undefined') {var endedCc = false;}
if (typeof usedAdBlocksArray==='undefined') {var usedAdBlocksArray = [];}
if (typeof usedBlockSettingArrayIds==='undefined') {var usedBlockSettingArrayIds = [];}
if (typeof sameElementAfterWidth==='undefined') {var sameElementAfterWidth = false;}
if (typeof sameElementAfterExcClassId==='undefined') {var sameElementAfterExcClassId = false;}
if (typeof sameElementAfterFromConstruction==='undefined') {var sameElementAfterFromConstruction = false;}
if (typeof rb_tempElement_check==='undefined') {var rb_tempElement_check = false;}
if (typeof rb_tempElement==='undefined') {var rb_tempElement = null;}
if (typeof window.jsInputerLaunch==='undefined') {window.jsInputerLaunch = -1;}

function launchUpdateRbDisplays() {
    if ((typeof updateRbDisplays !== 'undefined')&&(typeof updateRbDisplays === 'function')) {
        updateRbDisplays();
    } else {
        setTimeout(function () {
            launchUpdateRbDisplays();
        }, 200);
    }
}

/* "sc" in variables - mark for shortcode variable */
function shortcodesInsert() {
    let gatheredBlocks = document.querySelectorAll('.percentPointerClass.scMark'),
        scBlockId = -1,
        scAdId = -1,
        blockStatus = '',
        dataFull = -1,
        gatheredBlockChild,
        okStates = ['done','refresh-wait','no-block','fetched'],
        scContainer,
        sci,
        i1 = 0,
        skyscraperCheck = [],
        skyscraperStatus = false,
        splitedSkyscraper = [],
        gatheredBlockChildSkyParts = [],
        stickyStatus = false,
        stickyCheck = [],
        stickyFixedStatus = false,
        stickyFixedCheck = [],
        overflowCheck = [],
        overflowStatus = false,
        repeatableIdentifier = "",
        dataCidIdentifier = null,
        divCidElement = '';

    if (typeof scArray !== 'undefined') {
        if (scArray&&scArray.length > 0&&gatheredBlocks&&gatheredBlocks.length > 0&&typeof window.rulvW5gntb !== 'undefined') {
            dataCidIdentifier = window.rulvW5gntb;
            for (let i = 0; i < gatheredBlocks.length; i++) {
                gatheredBlockChild = gatheredBlocks[i].children[0];
                if (!gatheredBlockChild) {
                    continue;
                }
                scAdId = -3;
                blockStatus = null;
                scContainer = null;
                dataFull = -1;
                skyscraperStatus = false;
                splitedSkyscraper = [];
                gatheredBlockChildSkyParts = [];
                stickyStatus = false;
                stickyCheck = [];
                stickyFixedStatus = false;
                stickyFixedCheck = [];
                repeatableIdentifier = "";
                divCidElement = null;

                scAdId = gatheredBlockChild.getAttribute('data-aid');
                scBlockId = gatheredBlockChild.getAttribute('data-id');
                blockStatus = gatheredBlockChild.getAttribute('data-state');
                dataFull = gatheredBlockChild.getAttribute('data-full');

                if (scBlockId&&scAdId > 0) {
                    sci = -1;
                    for (i1 = 0; i1 < scArray.length; i1++) {
                        if (scBlockId == scArray[i1]['blockId']&&scAdId == scArray[i1]['adId']) {
                            sci = i1;
                        }
                    }

                    if (sci > -1) {
                        if (blockStatus&&okStates.includes(blockStatus)) {
                            if (blockStatus=='no-block') {
                                gatheredBlockChild.innerHTML = '';
                            } else if ((blockStatus=='fetched'&&dataFull==1)||!['no-block','fetched'].includes(blockStatus)) {
                                for (let cl1 = 0; cl1 < gatheredBlocks[i].classList.length; cl1++) {
                                    if (gatheredBlocks[i].classList[cl1].includes("repeatable-mark")) {
                                        repeatableIdentifier = gatheredBlocks[i].classList[cl1];
                                    }
                                }

                                if (repeatableIdentifier) {
                                    divCidElement = document.querySelectorAll(".percentPointerClass.scMark."+repeatableIdentifier+' div[data-cid="'+dataCidIdentifier+'"]');
                                } else {
                                    divCidElement = gatheredBlockChild.querySelectorAll('div[data-cid="'+dataCidIdentifier+'"]');
                                }

                                var text = scArray[sci]['text'];
                                if (divCidElement&&divCidElement.length > 0) {
                                    for (let i2 = 0; i2 < divCidElement.length; i2++) {
                                        jQuery(divCidElement[i2]).html(text);
                                    }
                                } else {
                                    jQuery(gatheredBlockChild).html(text);
                                }
                                launchUpdateRbDisplays();
                            }
                            if (blockStatus!='fetched'||(blockStatus=='fetched'&&dataFull==1)) {
                                for (i1 = 0; i1 < scArray.length; i1++) {
                                    if (scBlockId == scArray[i1]['blockId']) {
                                        scArray.splice(i1, 1);
                                        i1--;
                                    }
                                }
                                gatheredBlocks[i].classList.remove('scMark');
                            }
                        }
                    }
                } else if (scBlockId&&scAdId < 1&&['no-block','fetched'].includes(blockStatus)) {
                    for (i1 = 0; i1 < scArray.length; i1++) {
                        if (scBlockId == scArray[i1]['blockId']) {
                            scArray.splice(i1, 1);
                            i1--;
                        }
                    }
                    gatheredBlocks[i].classList.remove('scMark');
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

    let gatheredBlocks = document.querySelectorAll('.percentPointerClass .' + block_classes.join(', .percentPointerClass .'));

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

function blocksRepositionUse(containerString, blType, searchType, contentElement) {
    let blocksInContainer;
    let blLocal = blType;
    let currentBlock;
    let currentBlockId;
    let currentBlockPosition;
    let currentContainer;
    let i = 0;
    let j = 0;
    let blockStrJs = ' .percentPointerClass.marked';
    let blockStrPhp = ' .percentPointerClass:not(.marked)';
    let blockStr = ' .percentPointerClass';
    let checkPointer = null;
    let blockRepeatEnd = false;

    if (searchType) {
        if (searchType == 'marked') {
            while (!blockRepeatEnd) {
                blLocal = blLocal.parentElement;
                if (blLocal) {
                    checkPointer = blLocal.querySelector("#content_pointer_id");
                    if (!checkPointer) {
                        blocksInContainer = jQuery(blLocal).parent(containerString);
                        if (blocksInContainer && blocksInContainer.length > 0) {
                            /* checkPointer = blocksInContainer.querySelector("#content_pointer_id"); */
                            checkPointer = jQuery(blocksInContainer).find("#content_pointer_id");
                            if (checkPointer && checkPointer.length > 0) {
                                blocksInContainer = null;
                            }
                            blockRepeatEnd = true;
                        }
                    } else {
                        blockRepeatEnd = true
                    }
                } else {
                    blockRepeatEnd = true
                }
            }
            /* blocksInContainer = jQuery(blType).parent(containerString); */
            if (blocksInContainer&&blocksInContainer.length > 0) {
                /* blocksInContainer.parentNode.insertBefore(rb_tempElement, blocksInContainer); */
                blocksInContainer[0].parentNode.insertBefore(rb_tempElement, blocksInContainer[0]);

                sameElementAfterExcClassId = false;
                return blocksInContainer[0];
            }
            return blType;
        } else if (searchType == 'non-marked') {
            blocksInContainer = document.querySelectorAll(blType + containerString + blockStrPhp);
            if (blocksInContainer && blocksInContainer.length > 0 && usedBlockSettingArray && usedBlockSettingArray.length > 0) {
                for (i = 0; i < blocksInContainer.length; i++) {
                    currentBlock = blocksInContainer[i];
                    currentBlockId = currentBlock.querySelector('.' + block_classes.join(', .')).getAttribute('data-id');
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
        htmlToAdd = '.percentPointerClass  > *[data-id="'+blockNumber+'"] {\n' +
            '    margin: '+marginString+';\n' +
            '}\n';
    }

    elementToAddStyleLocal.innerHTML += htmlToAdd;
    return textAlignString;
}

function initTargetToInsert(position, type, currentElement) {
    let posCurrentElement;
    let usedElement;
    if (type == 'element') {
        if (position == 0) {
            posCurrentElement = currentElement;
            if (!(typeof obligatoryMargin!=='undefined'&&obligatoryMargin===1)) {
                currentElement.classList.add('rfwp_removedMarginTop');
            }
        } else {
            posCurrentElement = currentElement.nextSibling;
            if (!(typeof obligatoryMargin!=='undefined'&&obligatoryMargin===1)) {
                currentElement.classList.add('rfwp_removedMarginBottom');
            }
        }
        currentElement.style.clear = 'both';
    } else {
        usedElement = currentElement;
        if (position == 0) {
            posCurrentElement = usedElement;
        } else {
            posCurrentElement = usedElement.nextSibling;
        }
    }
    return posCurrentElement;
}

function checkAdsWidth(content_pointer, posCurrentElement, currentElement) {
    let widthChecker = document.querySelector('#widthChecker');
    let widthCheckerStyle = null;
    let content_pointerStyle = getComputedStyle(content_pointer);
    /* let getPositionForTempElement = null;
    let testImgDetected = false;
    let testImg;
    let testImageCompWidth;
    let testImgCou = 0
    let figureChilds;
    let figureComWidth;
    let fcCou = 0; */
    let content = content_pointer.parentElement;

    if (!widthChecker) {
        widthChecker = document.createElement("div");
        widthChecker.setAttribute('id','widthChecker');
        widthChecker.style.display = 'flex';
    }

    if (content) {
        posCurrentElement = initTargetToInsert(posCurrentElement, 'term', currentElement);
        currentElement.parentNode.insertBefore(widthChecker, posCurrentElement);
        widthCheckerStyle = getComputedStyle(widthChecker);
        /* testImg = currentElement.previousSibling;
        if (testImg) {
            while (!testImgDetected&&testImgCou<4) {
                if (testImg&&testImg.nodeName.toLowerCase() === 'figure') {
                    figureComWidth = getComputedStyle(testImg);
                    figureComWidth = parseInt(figureComWidth.width);
                    figureChilds = testImg.childNodes;
                    if (figureChilds&&figureChilds.length > 0) {
                        while (!testImgDetected&&figureChilds[fcCou]) {
                            if (figureChilds[fcCou] instanceof HTMLImageElement) {
                                testImgDetected = true;
                                testImageCompWidth = getComputedStyle(figureChilds[fcCou]);
                                testImageCompWidth = parseInt(testImageCompWidth.width);
                                console.log('img_f_w:'+figureComWidth+'; img_w:'+testImageCompWidth+';');
                            }
                            fcCou++;
                        }
                    }
                }
                if (testImg instanceof HTMLImageElement) {
                    testImgDetected = true;
                    testImageCompWidth = getComputedStyle(testImg);
                    testImageCompWidth = parseInt(testImageCompWidth.width);
                    console.log('img_w:'+testImageCompWidth+';');
                }
                if (!testImg.previousSibling) {
                    break;
                }
                testImg = testImg.previousSibling;
                testImgCou++;
            }
        }
        console.log('cp_w:'+parseInt(content_pointerStyle.width)+'; wc_w:'+parseInt(widthCheckerStyle.width)+';'); */
        if (parseInt(widthCheckerStyle.width) >= (parseInt(content_pointerStyle.width) - 50)) {
            return true;
        }
    }
    currentElement.parentNode.insertBefore(rb_tempElement, currentElement.nextSibling);
    rb_tempElement_check = true;
    return false;
}

/* function currentElementReceiver(revert, curSum, elList, currentElement) {
    let origCurrentElement = currentElement;
    let content_pointer = document.querySelector("#content_pointer_id");
    let sameElementAfterWidth = false;
    let testCou = 0;
    while (elList[curSum]&&sameElementAfterWidth==false&&testCou < 5) {
        currentElement = elList[curSum];
        try {
            sameElementAfterWidth=true;
            sameElementAfterWidth = checkAdsWidth(content_pointer, 0, currentElement);
        } catch (ex) {
            console.log(ex.message);
        }
        revert? curSum--: curSum++;
        testCou++;
    }
    return currentElement?currentElement:origCurrentElement;
} */

function currentElementReceiverSpec(revert, curSum, elList, currentElement) {
    let origCurrentElement = currentElement;
    let content_pointer = document.querySelector("#content_pointer_id"); /* orig */
    let sameElementAfterWidth = false;
    let testCou = 0;
    while (elList[curSum]&&sameElementAfterWidth==false&&testCou < 5) {
        currentElement = elList[curSum]['element'];
        try {
            sameElementAfterWidth=true;
            sameElementAfterWidth = checkAdsWidth(content_pointer, 0, currentElement);
        } catch (ex) {
            console.log(ex.message);
        }
        revert? curSum--: curSum++;
        testCou++;
    }
    return currentElement?currentElement:origCurrentElement;
}

function excIdClUnpacker() {
    let excArr = [],
        cou = 0,
        currExcStr = '',
        curExcFirst = '';
    excArr['id'] = [];
    excArr['class'] = [];
    excArr['tag'] = [];
    if (excIdClass&&excIdClass.length > 0) {
        while (excIdClass[cou]) {
            currExcStr = excIdClass[cou];
            if (currExcStr.length > 0) {
                curExcFirst = currExcStr.substring(0,1);
                switch (curExcFirst) {
                    case '#':
                        if (currExcStr.length > 1) {
                            currExcStr = currExcStr.substring(1);
                            excArr['id'].push(currExcStr);
                        }
                        break;
                    case '.':
                        if (currExcStr.length > 1) {
                            currExcStr = currExcStr.substring(1);
                            excArr['class'].push(currExcStr);
                        }
                        break;
                    default:
                        excArr['tag'].push(currExcStr);
                        break;
                }
                cou++;
            }
        }
    }
    return excArr;
}

function asyncBlocksInsertingFunction(blockSettingArray) {
    try {
        var content_pointer = document.querySelector("#content_pointer_id"); /* orig */
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
        var curSumResult = 0;
        var repeat = false;
        var currentElementChecker = false;
        let containerFor6th = [];
        let containerFor7th = [];
        var posCurrentElement;
        var block_number;
        let contentLength = content_pointer.getAttribute('data-content-length');
        let rejectedBlocks = content_pointer.getAttribute('data-rejected-blocks');
        if (rejectedBlocks&&rejectedBlocks.length > 0) {
            rejectedBlocks = rejectedBlocks.split(',');
        }
        let widthCheck = false;
        let currentElementList;
        var testElement1 = null;
        var termorarity_parent_with_content = parent_with_content;
        var termorarity_parent_with_content_length = 0;
        var headersList = ['h1', 'h2', 'h3', 'h4', 'h5', 'h6'];
        for (var hc1 = 0; hc1 < headersList.length; hc1++) {
            termorarity_parent_with_content_length += termorarity_parent_with_content.getElementsByTagName(headersList[hc1]).length;
        }

        let detailedElementList;
        let ExcStrCou = 1;
        let detailedQueryString;
        let usedElement;
        let tagList = [];
        let localSumResult;
        let binderName;

        var removeClearing;
        var repeatableBlockIdentifier = 0;

        var i;

        if (contentLength < 1) {
            contentLength = parent_with_content.innerText.length
        }

        rb_tempElement = document.querySelector('#rb_tempElement');
        if (!rb_tempElement) {
            rb_tempElement = document.createElement('span');
            rb_tempElement.setAttribute('id', 'rb_tempElement');
        }

        function getFromConstructions(currentElement) {
            if (currentElement.parentElement.tagName.toLowerCase() == "blockquote") {
                currentElement = currentElement.parentElement;
                /* initTargetToInsert(blockSettingArray, 'element', currentElement); */
                currentElement.parentNode.insertBefore(rb_tempElement, currentElement);
                rb_tempElement_check = true;
                sameElementAfterFromConstruction=false;
            } else if (["tr","td","th","thead","tbody","table"].includes(currentElement.parentElement.tagName.toLowerCase())) {
                currentElement = currentElement.parentElement;
                while (["tr","td","th","thead","tbody","table"].includes(currentElement.parentElement.tagName.toLowerCase())) {
                    currentElement = currentElement.parentElement;
                }
                currentElement.parentNode.insertBefore(rb_tempElement, currentElement);
                rb_tempElement_check = true;
                sameElementAfterFromConstruction=false;
            }
            return currentElement;
        }

        function directClassElementDetecting(blockSettingArray, directElement) {
            let findQuery = 0;
            let directClassElementResult = [];

            currentElement = document.querySelectorAll(directElement);
            if (currentElement.length > 0) {
                if (blockSettingArray[i]['elementPlace'] > 1) {
                    if (currentElement.length >= blockSettingArray[i]['elementPlace']) {
                        currentElement = currentElement[blockSettingArray[i]['elementPlace']-1];
                    } else if (currentElement.length < blockSettingArray[i]['elementPlace']) {
                        currentElement = currentElement[currentElement.length - 1];
                    } else {
                        findQuery = 1;
                    }
                } else if (blockSettingArray[i]['elementPlace'] < 0) {
                    if ((currentElement.length + blockSettingArray[i]['elementPlace'] + 1) > 0) {
                        currentElement = currentElement[currentElement.length + blockSettingArray[i]['elementPlace']];
                    } else {
                        findQuery = 1;
                    }
                } else {
                    findQuery = 1;
                }
            } else {
                findQuery = 1;
            }

            directClassElementResult['findQuery'] = findQuery;
            directClassElementResult['currentElement'] = currentElement;

            return directClassElementResult;
        }

        function placingToH1(usedElement, elementTagToFind) {
            let uselessLet;
            currentElement = usedElement.querySelectorAll(elementTagToFind);

            if (currentElement.length < 1) {
                if (usedElement.parentElement) {
                    uselessLet = placingToH1(usedElement.parentElement, elementTagToFind);
                }
            }
            return currentElement;
        }

        function elementsCleaning(excArr, elList, pwcLocal, gatherString) {
            let markedClass = 'rb_m_inc';
            let markedClassBad = 'rb_m_exc';
            let cou = 0;
            let cou1 = 0;
            let finalArr = [];
            let finalArrClear = [];
            let checkNearest;
            let outOfRangeCheck;
            let gatherRejected;
            let allower;

            try {
                while (elList[cou]) {
                    allower = true;
                    if (!elList[cou].classList.contains(markedClassBad)) {
                        if (excArr&&excArr.length > 0) {
                            cou1 = 0;
                            while (excArr[cou1]) {
                                checkNearest = elList[cou].parentElement.closest(excArr[cou1]);
                                if (checkNearest) {
                                    checkNearest.classList.add('currClosest');
                                    outOfRangeCheck = pwcLocal.querySelector('.currClosest');
                                    if (outOfRangeCheck) {
                                        allower = false;
                                        checkNearest.classList.add(markedClass);
                                        gatherRejected = checkNearest.querySelectorAll(gatherString);
                                        if (gatherRejected.length > 0) {
                                            for (let i1 = 0; i1 < gatherRejected.length; i1++) {
                                                gatherRejected[i1].classList.add(markedClassBad);
                                            }
                                        }
                                    }
                                    checkNearest.classList.remove('currClosest');
                                }
                                cou1++;
                            }
                        }
                        if (allower===true) {
                            elList[cou].classList.add(markedClass);
                            /* finalArr.push(elList[cou]); */
                        }
                    }
                    cou++;
                }
                finalArr = pwcLocal.querySelectorAll('.'+markedClass+':not('+markedClassBad+')');
                finalArrClear = pwcLocal.querySelectorAll('.'+markedClass+',.'+markedClassBad);
                if (finalArrClear&&finalArrClear.length > 0) {
                    for (let i1 = 0; i1 < finalArrClear.length; i1++) {
                        finalArrClear[i1].classList.remove(markedClass,markedClassBad);
                    }
                }
            } catch (er) {
                console.log(er.message);
            }
            return finalArr;
        }

        function cureentElementsGather(usedElement, loopLimit = 2, localPwc = parent_with_content) {
            let curElementSearchRepeater = true;
            let curElementSearchCounter = 0;
            let currentElementLoc = null;
            let ExcludedStringBegin = '';
            let ExcludedString = '';
            let ExcludedStringEnd = '';
            let tagListString = '';
            let tagListStringExc = '';
            let cou = 0;
            /* let excArr = excIdClUnpacker(); */
            let tagListCou = 0;

            if (usedElement=='h1') {
                currentElementLoc = placingToH1(localPwc, usedElement);
            } else {
                if (usedElement=='h2-4') {tagList = ['h2','h3','h3'];}
                else                     {tagList = [usedElement];   }
                while (tagList[tagListCou]) {
                    tagListString += ((cou++>0)?',':'')+tagList[tagListCou];
                    tagListStringExc += ':not('+tagList[tagListCou]+')';
                    tagListCou++;
                }

                ExcludedString = '';
                if (excIdClass&&excIdClass.length > 0) {
                    for (let i2 = 0; i2 < excIdClass.length; i2++) {
                        if (excIdClass[i2].length > 0) {
                            ExcludedString += (i2>0?',':'')+excIdClass[i2]+tagListStringExc;
                        }
                    }
                }
                detailedQueryString += tagListString+','+ExcludedString;

                /* console.log(detailedQueryString); */
                while (curElementSearchRepeater&&curElementSearchCounter < loopLimit) {
                    try {
                        currentElementLoc = localPwc.querySelectorAll(tagListString);
                    } catch (e1) {console.log(e1.message);}
                    if (!currentElementLoc) {
                        if (localPwc.parentElement) {
                            localPwc = localPwc.parentElement;
                        } else {
                            break;
                        }
                    } else {
                        currentElementLoc = elementsCleaning(excIdClass, currentElementLoc, localPwc, detailedQueryString);
                        curElementSearchRepeater = false;
                    }
                    curElementSearchCounter++;
                }
            }
            return currentElementLoc;
        }

        function currentElementReceiver(revert, localCurEl = currentElement) {
            let origCurEl = localCurEl;
            curSumResult = sumResult;
            detailedElementList = localCurEl;
            sameElementAfterWidth = false;
            let testCou = 0;
            while (detailedElementList[curSumResult]&&sameElementAfterWidth==false&&testCou < 8) {
                localCurEl = detailedElementList[curSumResult];
                try {
                    sameElementAfterWidth=true;
                    sameElementAfterWidth = checkAdsWidth(content_pointer, blockSettingArray[i]["elementPosition"], localCurEl);
                } catch (ex) {
                    console.log(ex.message);
                }
                revert? curSumResult--: curSumResult++;
                testCou++;
            }
            if (localCurEl) {
                currentElementChecker = true;
            }
            return localCurEl?localCurEl:origCurEl;
        }
        
        function endingActions(block_number) {
            usedBlockSettingArrayIds.push(block_number);
            blockSettingArray.splice(i--, 1);
            poolbackI = 1;
        }

        for (i = 0; i < blockSettingArray.length; i++) {
            currentElement = null;
            currentElementChecker = false;
            sameElementAfterWidth = false;
            sameElementAfterExcClassId = false;
            sameElementAfterFromConstruction = false;
            tagListCou = 0;
            poolbackI = 0;
            detailedQueryString = '';
            binderName = elementBinderNameGenerator();

            try {
                if (!blockSettingArray[i]["text"]
                    ||(blockSettingArray[i]["text"]&&blockSettingArray[i]["text"].length < 1)
                    ||(rejectedBlocks&&rejectedBlocks.includes(blockSettingArray[i]["id"]))
                    ||((blockSettingArray[i]["maxHeaders"] > 0)&&(blockSettingArray[i]["maxHeaders"] < parseInt(termorarity_parent_with_content_length)))
                    ||((blockSettingArray[i]["maxSymbols"] > 0)&&(blockSettingArray[i]["maxSymbols"] < parseInt(contentLength)))
                    ||(content_pointer.classList.contains("hard-content")&&blockSettingArray[i]["setting_type"]!=3)
                ) {
                    blockSettingArray.splice(i--, 1);
                    poolbackI = 1;
                    continue;
                }

                block_number = 0;

                elementToAdd = document.createElement("div");
                elementToAdd.classList.add("percentPointerClass");
                elementToAdd.classList.add("marked");
                if (blockSettingArray[i]["sc"]==1) {
                    elementToAdd.classList.add("scMark");
                }
                elementToAdd.innerHTML = blockSettingArray[i]["text"];
                elementToAdd.dataset.rbinder = binderName;
                block_number = elementToAdd.children[0].attributes['data-id'].value;

                if (blockDuplicate == 'no') {
                    if (usedBlockSettingArrayIds.length > 0) {
                        for (let i1 = 0; i1 < usedBlockSettingArrayIds.length; i1++) {
                            if (block_number==usedBlockSettingArrayIds[i1]) {
                                blockSettingArray.splice(i--, 1);
                                poolbackI = 1;
                                break;
                            }
                        }
                        if (poolbackI == 1) {
                            continue;
                        }
                    }
                }

                elementToAddStyle = createStyleElement(block_number, blockSettingArray[i]["elementCss"]);

                if (elementToAddStyle&&elementToAddStyle!='default') {
                    elementToAdd.style.textAlign = elementToAddStyle;
                }

                if ((blockSettingArray[i]["minHeaders"] > 0)&&(blockSettingArray[i]["minHeaders"] > termorarity_parent_with_content_length)) {continue;}
                if (blockSettingArray[i]["minSymbols"] > contentLength) {continue;}

                if (blockSettingArray[i]["setting_type"] == 1) {
                    currentElement = cureentElementsGather(blockSettingArray[i]["element"].toLowerCase());
                    if (currentElement) {
                        if (blockSettingArray[i]["elementPlace"] < 0) {
                            sumResult = currentElement.length + blockSettingArray[i]["elementPlace"];
                            if (sumResult >= 0 && sumResult < currentElement.length) {
                                currentElement = currentElementReceiver(true);
                            }
                        } else {
                            sumResult = blockSettingArray[i]["elementPlace"] - 1;
                            if (sumResult < currentElement.length) {
                                currentElement = currentElementReceiver(false);
                            }
                        }
                    }
                    if (currentElement != undefined && currentElement != null && currentElementChecker) {
                        posCurrentElement = initTargetToInsert(blockSettingArray[i]["elementPosition"], 'element', currentElement);
                        currentElement.parentNode.insertBefore(elementToAdd, posCurrentElement);
                        currentElement.classList.add('rbinder-'+binderName);
                        elementToAdd.classList.remove('coveredAd');
                        usedBlockSettingArrayIds.push(block_number);
                        blockSettingArray.splice(i--, 1);
                        poolbackI = 1;
                        rb_tempElement_check = false;
                    } else {
                        repeat = true;
                    }
                }
                else if (blockSettingArray[i]["setting_type"] == 2) {
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

                    repeatableCurrentElement = cureentElementsGather(blockSettingArray[i]["element"].toLowerCase());
                    if (repeatableCurrentElement) {
                        for (let i1 = 0; i1 < blockSettingArray[i]["elementCount"]; i1++) {
                            currentElementChecker = false;
                            let repElementToAdd = document.createElement("div");
                            repElementToAdd.classList.add("percentPointerClass");
                            repElementToAdd.classList.add("marked");
                            if (blockSettingArray[i]["sc"]==1) {
                                repElementToAdd.classList.add("scMark");
                            }
                            repElementToAdd.classList.add("repeatable-mark-"+repeatableBlockIdentifier);
                            repElementToAdd.innerHTML = blockSettingArray[i]["text"];

                            if (elementToAddStyle&&elementToAddStyle!='default') {
                                repElementToAdd.style.textAlign = elementToAddStyle;
                            }

                            sumResult = Math.round(parseInt(blockSettingArray[i]["firstPlace"]) + (i1*parseInt(blockSettingArray[i]["elementStep"])) - 1);
                            if (sumResult < repeatableCurrentElement.length) {
                                currentElement = currentElementReceiver(false, repeatableCurrentElement);
                            }

                            if (currentElement != undefined && currentElement != null && currentElementChecker) {
                                posCurrentElement = initTargetToInsert(blockSettingArray[i]["elementPosition"], 'element', currentElement);
                                currentElement.parentNode.insertBefore(repElementToAdd, posCurrentElement);
                                currentElement.classList.add('rbinder-'+binderName);
                                repElementToAdd.classList.remove('coveredAd');
                                curFirstPlace = sumResult + parseInt(blockSettingArray[i]["elementStep"]) + 1;
                                curElementCount--;
                                repeatableSuccess = true;
                            } else {
                                repeatableSuccess = false;
                                break;
                            }
                        }
                    }
                    if (repeatableSuccess==true) {
                        usedBlockSettingArrayIds.push(block_number);
                        blockSettingArray.splice(i--, 1);
                        poolbackI = 1;
                        repeatableBlockIdentifier++;
                    } else {
                        if (!blockSettingArray[i]["unsuccess"]) {
                            blockSettingArray[i]["unsuccess"] = 1;
                        } else {
                            blockSettingArray[i]["unsuccess"] = Math.round(blockSettingArray[i]["unsuccess"] + 1);
                        }
                        if (blockSettingArray[i]["unsuccess"] > 10) {
                            usedBlockSettingArrayIds.push(block_number);
                            blockSettingArray.splice(i--, 1);
                            poolbackI = 1;
                        } else {
                            blockSettingArray[i]["firstPlace"] = curFirstPlace;
                            blockSettingArray[i]["elementCount"] = curElementCount;
                            blockSettingArray[i]["elementStep"] = curElementStep;
                            repeat = true;
                        }
                    }
                }
                else if (blockSettingArray[i]["setting_type"] == 3) {
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
                    } else if ((directElement.search('#') < 0)&&(directElement.search('.') > -1)) {
                        directClassResult = directClassElementDetecting(blockSettingArray, directElement);
                        findQuery = directClassResult['findQuery'];
                        currentElement = directClassResult['currentElement'];
                    }
                    if (findQuery == 1) {
                        currentElement = document.querySelector(directElement);
                    }
                    if (currentElement) {
                        currentElementChecker = true;
                    }

                    if (currentElement != undefined && currentElement != null && currentElementChecker) {
                        posCurrentElement = initTargetToInsert(blockSettingArray[i]["elementPosition"], 'element', currentElement);
                        currentElement.parentNode.insertBefore(elementToAdd, posCurrentElement);
                        elementToAdd.classList.remove('coveredAd');
                        currentElement.classList.add('rbinder-'+binderName);
                        usedBlockSettingArrayIds.push(block_number);
                        blockSettingArray.splice(i--, 1);
                        poolbackI = 1;
                    } else {
                        repeat = true;
                    }
                }
                else if (blockSettingArray[i]["setting_type"] == 4) {
                    document.querySelector("#content_pointer_id").parentElement.append(elementToAdd);
                    usedBlockSettingArrayIds.push(block_number);
                    blockSettingArray.splice(i--, 1);
                    poolbackI = 1;
                }
                else if (blockSettingArray[i]["setting_type"] == 5) {
                    let currentElementList = cureentElementsGather('p', 1, content_pointer.parentElement);
                    if (currentElementList&&currentElementList.length > 0) {
                        let pCount = currentElementList.length;
                        let elementNumber = Math.round(pCount/2);
                        if (pCount > 1) {
                            currentElement = currentElementList[elementNumber+1];
                        }
                        if (currentElement != undefined && currentElement != null) {
                            if (pCount > 1) {
                                currentElement.parentNode.insertBefore(elementToAdd, currentElement);
                            } else {
                                currentElement.parentNode.insertBefore(elementToAdd, currentElement.nextSibling);
                            }
                            elementToAdd.classList.remove('coveredAd');
                            currentElement.classList.add('rbinder-'+binderName);
                            usedBlockSettingArrayIds.push(block_number);
                            blockSettingArray.splice(i--, 1);
                            poolbackI = 1;
                        } else {
                            repeat = true;
                        }
                    } else {
                        repeat = true;
                    }
                }
                else if (blockSettingArray[i]["setting_type"] == 6) {
                    if (containerFor6th.length > 0) {
                        for (let j = 0; j < containerFor6th.length; j++) {
                            if (containerFor6th[j]["elementPlace"]>blockSettingArray[i]["elementPlace"]) {
                                /* continue; */
                                if (j == containerFor6th.length-1) {
                                    containerFor6th.push(blockSettingArray[i]);
                                    /* usedAdBlocksArray.push(checkIfBlockUsed); */
                                    usedBlockSettingArrayIds.push(block_number);
                                    blockSettingArray.splice(i--, 1);
                                    poolbackI = 1;
                                    break;
                                }
                            } else {
                                for (let k = containerFor6th.length-1; k > j-1; k--) {
                                    containerFor6th[k + 1] = containerFor6th[k];
                                }
                                containerFor6th[j] = blockSettingArray[i];
                                /* usedAdBlocksArray.push(checkIfBlockUsed); */
                                usedBlockSettingArrayIds.push(block_number);
                                blockSettingArray.splice(i--, 1);
                                poolbackI = 1;
                                break;
                            }
                        }
                    } else {
                        containerFor6th.push(blockSettingArray[i]);
                        usedBlockSettingArrayIds.push(block_number);
                        blockSettingArray.splice(i--, 1);
                        poolbackI = 1;
                    }
                /* vidpravutu v vidstiinuk dlya 6ho tipa */
                }
                else if (blockSettingArray[i]["setting_type"] == 7) {
                    if (containerFor7th.length > 0) {
                        for (let j = 0; j < containerFor7th.length; j++) {
                            if (containerFor7th[j]["elementPlace"]>blockSettingArray[i]["elementPlace"]) {
                                /* continue; */
                                if (j == containerFor7th.length-1) {
                                    containerFor7th.push(blockSettingArray[i]);
                                    usedBlockSettingArrayIds.push(block_number);
                                    blockSettingArray.splice(i--, 1);
                                    poolbackI = 1;
                                    break;
                                }
                            } else {
                                for (let k = containerFor7th.length-1; k > j-1; k--) {
                                    containerFor7th[k + 1] = containerFor7th[k];
                                }
                                containerFor7th[j] = blockSettingArray[i];
                                usedBlockSettingArrayIds.push(block_number);
                                blockSettingArray.splice(i--, 1);
                                poolbackI = 1;
                                break;
                            }
                        }
                    } else {
                        containerFor7th.push(blockSettingArray[i]);
                        usedBlockSettingArrayIds.push(block_number);
                        blockSettingArray.splice(i--, 1);
                        poolbackI = 1;
                    }
                /* vidpravutu v vidstiinuk dlya 7ho tipa */
                }
            } catch (e) {
                console.log(e.message);
            }
        }

        var array = textLengthGatherer(lordOfElements),
            tlArray = array.array,
            length = array.length;

        if (containerFor6th.length > 0) {
            percentInserter(lordOfElements, containerFor6th, tlArray, length);
        }
        if (containerFor7th.length > 0) {
            symbolInserter(lordOfElements, containerFor7th, tlArray);
        }
        shortcodesInsert();
        let stopper = 0;

        window.addEventListener('load', function () {
            if (repeat = true) {
                setTimeout(function () {
                    /* asyncBlocksInsertingFunction(blockSettingArray, contentLength) */
                    asyncBlocksInsertingFunction(blockSettingArray);
                }, 100);
            }
        });
    } catch (e) {
        console.log(e.message);
    }
}

function possibleTagsInCheckConfirmer(possibleTagsArray, possibleTagsInCheck) {
    if (possibleTagsArray.includes("LI")) {
        if (possibleTagsArray.includes("UL")) {
            possibleTagsInCheck.push("UL");
        }
        if (possibleTagsArray.includes("OL")) {
            possibleTagsInCheck.push("OL");
        }
    }

    return false;
}

function textLengthGatherer(lordOfElementsLoc) {
    var possibleTagsArray;
    if (typeof tagsListForTextLength!=="undefined") {
        possibleTagsArray = tagsListForTextLength;
    } else {
        possibleTagsArray = ["P", "H1", "H2", "H3", "H4", "H5", "H6", "DIV", "BLOCKQUOTE", "INDEX", "ARTICLE", "SECTION"];
    }
    let possibleTagsInCheck = ["DIV", "INDEX", "SECTION"];

    possibleTagsInCheckConfirmer(possibleTagsArray, possibleTagsInCheck);
    let excArr = excIdClUnpacker(),
        textLength = 0,
        tlArray = [];

    function textLengthGathererRec(lordOfElementsLoc) {
        let allowed;
        let cou1;
        let classesArray;
        let countSuccess = 0;
        try {
            for (let i = 0; i < lordOfElementsLoc.children.length; i++) {
                if (possibleTagsArray.includes(lordOfElementsLoc.children[i].tagName)
                    &&!lordOfElementsLoc.children[i].classList.contains("percentPointerClass")
                    &&lordOfElementsLoc.children[i].id!="toc_container"
                ) {
                    if (possibleTagsInCheck.includes(lordOfElementsLoc.children[i].tagName)
                        &&(lordOfElementsLoc.children[i].children.length > 0)
                    ) {
                        allowed = true;
                        if (lordOfElementsLoc.children[i].id&&excArr['id'].length > 0) {
                            cou1 = 0;
                            while (excArr['id'][cou1]) {
                                if (lordOfElementsLoc.children[i].id.toLowerCase()==excArr['id'][cou1].toLowerCase()) {
                                    allowed = false;
                                    break;
                                }
                                cou1++;
                            }
                        }

                        if (lordOfElementsLoc.children[i].classList.length > 0&&excArr['class'].length > 0) {
                            cou1 = 0;
                            while (excArr['class'][cou1]) {
                                classesArray = excArr['class'][cou1].split('.');
                                if (classesArray.every(className => lordOfElementsLoc.children[i].classList.contains(className))) {
                                    allowed = false;
                                    break;
                                }
                                cou1++;
                            }
                        }

                        if (excArr['tag'].length > 0) {
                            cou1 = 0;
                            while (excArr['tag'][cou1]) {
                                if (lordOfElementsLoc.children[i].tagName.toLowerCase()==excArr['tag'][cou1].toLowerCase()) {
                                    allowed = false;
                                    break;
                                }
                                cou1++;
                            }
                        }

                        if (allowed) {
                            if (textLengthGathererRec(lordOfElementsLoc.children[i], excArr, possibleTagsArray, possibleTagsInCheck)) {
                                countSuccess++;
                                continue;
                            }
                        }
                    }
                    textLength = textLength + lordOfElementsLoc.children[i].innerText.length;
                    tlArray.push({
                        tag: lordOfElementsLoc.children[i].tagName,
                        length: lordOfElementsLoc.children[i].innerText.length,
                        lengthSum: textLength,
                        element: lordOfElementsLoc.children[i]
                    });
                    countSuccess++;
                }
            }
        } catch (er) {
            console.log(er.message);
        }
        return countSuccess > 0;
    }

    textLengthGathererRec(lordOfElementsLoc);

    return {array: tlArray, length: textLength};
}

window.asyncFunctionLauncher = function() {
    if (window.jsInputerLaunch !== undefined
        &&[15, 10].includes(window.jsInputerLaunch)
        &&(typeof asyncBlocksInsertingFunction !== 'undefined' )
        &&(typeof asyncBlocksInsertingFunction === 'function')
        &&typeof endedSc!=='undefined'&&
        typeof endedCc!=='undefined'&&
        typeof usedAdBlocksArray!=='undefined'&&
        typeof usedBlockSettingArrayIds!=='undefined'&&
        typeof sameElementAfterWidth!=='undefined'&&
        typeof sameElementAfterExcClassId!=='undefined'&&
        typeof sameElementAfterFromConstruction!=='undefined'&&
        typeof rb_tempElement_check!=='undefined'&&
        typeof rb_tempElement!=='undefined'&&
        typeof window.jsInputerLaunch!=='undefined') {
        /* asyncBlocksInsertingFunction(blockSettingArray, contentLength); */
        asyncBlocksInsertingFunction(blockSettingArray);
        if (!endedSc) {
            shortcodesInsert();
        }
        if (!endedCc) {
            /* clearUnsuitableCache(0); */
        }
        /* blocksReposition();
        cachePlacing();
        symbolMarkersPlaced(); */
    } else {
        setTimeout(function () {
            asyncFunctionLauncher();
        }, 50);
    }
};
/* asyncFunctionLauncher(); */

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
    xhttp.open("POST", rb_ajaxurl, true);
    xhttp.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
    xhttp.send(sendData);
}

function cachePlacing(alert_type, errorInfo=null) {
    let adBlocks = document.querySelectorAll('.percentPointerClass .' + block_classes.join(', .percentPointerClass .'));
    let curAdBlock;
    let okStates = ['done','refresh-wait','no-block','fetched'];
    /* let adId = -1; */
    let blockAid = null;
    let blockId;

    if (typeof cachedBlocksArray !== 'undefined'&&cachedBlocksArray&&cachedBlocksArray.length > 0&&adBlocks&&adBlocks.length > 0) {
        for (let i = 0; i < adBlocks.length; i++) {
            blockAid = adBlocks[i]['dataset']['aid'];

            if (!blockAid) {
                blockId = adBlocks[i]['dataset']['id'];
                if (cachedBlocksArray[blockId]) {
                    jQuery(adBlocks[i]).html(cachedBlocksArray[blockId]);
                }
            }
        }
    }

    if (alert_type&&alert_type=='high') {
        setLongCache();
    }
}

function symbolInserter(lordOfElements, containerFor7th, tlArray) {
    try {
        var currentChildrenLength = 0;
        let previousBreak = 0;
        let needleLength;
        let currentSumLength;
        let elementToAdd;
        let elementToBind;
        let elementToAddStyle;
        let block_number;
        let binderName;

        if (!document.getElementById("markedSpan1")) {
            for (let i = 0; i < containerFor7th.length; i++) {
                previousBreak = 0;
                currentChildrenLength = 0;
                currentSumLength = 0;
                needleLength = Math.abs(containerFor7th[i]['elementPlace']);
                binderName = elementBinderNameGenerator();

                elementToAdd = document.createElement("div");
                elementToAdd.classList.add("percentPointerClass");
                elementToAdd.classList.add("marked");
                if (containerFor7th[i]["sc"]==1) {
                    elementToAdd.classList.add("scMark");
                }
                elementToAdd.dataset.rbinder = binderName;
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
                            elementToBind = currentElementReceiverSpec(true, j, tlArray, elementToBind);
                            elementToBind.parentNode.insertBefore(elementToAdd, elementToBind);
                            elementToBind.classList.add('rbinder-'+binderName);
                            elementToAdd.classList.remove('coveredAd');
                            break;
                        }
                    }
                } else if (containerFor7th[i]['elementPlace'] == 0) {
                    elementToBind = tlArray[0]['element'];
                    elementToBind.parentNode.insertBefore(elementToAdd, elementToBind);
                    elementToBind.classList.add('rbinder-'+binderName);
                    elementToAdd.classList.remove('coveredAd');
                } else {
                    for (let j = 0; j < tlArray.length; j++) {
                        currentSumLength = currentSumLength + tlArray[j]['length'];
                        if (needleLength < currentSumLength) {
                            elementToBind = tlArray[j]['element'];
                            elementToBind = currentElementReceiverSpec(false, j, tlArray, elementToBind);
                            elementToBind.parentNode.insertBefore(elementToAdd, elementToBind.nextSibling);
                            elementToBind.classList.add('rbinder-'+binderName);
                            elementToAdd.classList.remove('coveredAd');
                            break;
                        }
                    }
                }
            }

            var spanMarker = document.createElement("span");
            spanMarker.setAttribute("id", "markedSpan1");
            lordOfElements.prepend(spanMarker);
        }
    } catch (e) {
        console.log(e);
    }
}

function percentInserter(lordOfElements, containerFor6th, tlArray, textLength) {
    try {
        var textNeedyLength = 0;
        let elementToAdd;
        var elementToBind;
        let elementToAddStyle;
        let block_number;
        var binderName;
        /* var checkIfBlockUsed = 0; */

        function insertByPercents(textLength) {
            let localMiddleValue = 0;

            for (let j = 0; j < containerFor6th.length; j++) {
                textNeedyLength = Math.round(textLength * (containerFor6th[j]["elementPlace"]/100));
                for (let i = 0; i < tlArray.length; i++) {
                    if (tlArray[i]['lengthSum'] >= textNeedyLength) {
                        binderName = elementBinderNameGenerator();

                        elementToAdd = document.createElement("div");
                        elementToAdd.classList.add("percentPointerClass");
                        elementToAdd.classList.add("marked");
                        if (containerFor6th[j]["sc"]==1) {
                            elementToAdd.classList.add("scMark");
                        }
                        elementToAdd.dataset.rbinder = binderName;
                        elementToAdd.innerHTML = containerFor6th[j]["text"];
                        if (!elementToAdd) {
                            break;
                        }
                        block_number = elementToAdd.children[0].attributes['data-id'].value;
                        elementToAddStyle = createStyleElement(block_number, containerFor6th[j]["elementCss"]);
                        if (elementToAddStyle&&elementToAddStyle!='default') {
                            elementToAdd.style.textAlign = elementToAddStyle;
                        }

                        localMiddleValue = tlArray[i]['lengthSum'] - Math.round(tlArray[i]['length']/2);
                        elementToBind = tlArray[i]['element'];
                        currentElementReceiverSpec(false, i, tlArray, elementToBind);
                        if (textNeedyLength < localMiddleValue) {
                            elementToBind.parentNode.insertBefore(elementToAdd, elementToBind);
                        } else {
                            elementToBind.parentNode.insertBefore(elementToAdd, elementToBind.nextSibling);
                        }
                        elementToBind.classList.add('rbinder-'+binderName);
                        elementToAdd.classList.remove('coveredAd');
                        break;
                    }
                }
            }
            return false;
        }

        function clearTlMarks() {
            let marksForDeleting = document.querySelectorAll('.textLengthMarker');

            if (marksForDeleting.length > 0) {
                for (let i = 0; i < marksForDeleting.length; i++) {
                    marksForDeleting[i].remove();
                }
            }
        }

        if (!document.getElementById("markedSpan")) {
            insertByPercents(textLength);
            clearTlMarks();
            var spanMarker = document.createElement("span");
            spanMarker.setAttribute("id", "markedSpan");
            lordOfElements.prepend(spanMarker);
        }
    } catch (e) {
        console.log(e.message);
    }
}

function saveContentBlock(contentContainer) {
    try {
        if (!gather_content) {
            console.log('content gather save function entered');
            let xhttp = new XMLHttpRequest();
            let sendData = 'action=RFWP_saveContentContainer&type=gatherContentBlock&data='+contentContainer;
            xhttp.onreadystatechange = function(redata) {
                if (this.readyState == 4 && this.status == 200) {
                    console.log('content gather succeed');
                } else {
                    console.log('content gather gone wrong');
                }
            };
            xhttp.open("POST", rb_ajaxurl, true);
            xhttp.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
            xhttp.send(sendData);
        }
    } catch (er) {
        console.log('content gather error: '+er+';');
    }
}

window.gatherContentBlock = function() {
    let cPointer = null,
        cPointerParent = null,
        cPointerParentString = null,
        classWords = ['content','entry','post','wrap','description','taxonomy'],
        classChoosed = false;

    cPointer =  document.querySelector('#content_pointer_id');
    if (cPointer) {
        if (window.jsInputerLaunch!==15) {
            return false;
        }
        cPointerParent = cPointer.parentElement;
        if (cPointerParent) {
            if (cPointerParent.id) {
                cPointerParentString = '#'+cPointerParent.id;
            } else {
                if (cPointerParent.classList.length > 0) {
                    cPointerParentString = '.'+cPointerParent.classList[0];
                    for (let j = 0; j < classWords.length; j++) {
                        for (let i = 0; i < cPointerParent.classList.length; i++) {
                            if (cPointerParent.classList[i].includes(classWords[j])) {
                                cPointerParentString = '.'+cPointerParent.classList[i];
                                classChoosed = true;
                                break;
                            }
                        }
                        if (classChoosed===true) {
                            break;
                        }
                    }
                }
            }
            if (cPointerParentString) {
                console.log('content gather content block detected');
                /* cPointerParentString = JSON.stringify(cPointerParentString); */
                saveContentBlock(cPointerParentString);
            }
        }
    } else {
        console.log('content gather delayed');
        setTimeout(function () {
            gatherContentBlock();
        }, 500);
    }
};

window.removeMarginClass = function(blockObject) {
    if (blockObject && typeof window.jsInputerLaunch !== 'undefined' && [15, 10].includes(window.jsInputerLaunch)) {
        let binderName,
            neededElement,
            currentDirection,
            seekerIterationCount,
            currentSubling;

        binderName = blockObject.dataset.rbinder;
        if (binderName) {
            seekerIterationCount = 0;
            currentDirection = 'before';
            do {
                seekerIterationCount++;
                currentSubling = blockObject.nextElementSibling;
                if (currentSubling&&currentSubling.classList.contains('rbinder-'+binderName)) {
                    neededElement = currentSubling;
                }
            } while (currentSubling&&!neededElement&&seekerIterationCount < 5);

            if (!neededElement) {
                seekerIterationCount = 0;
                currentDirection = 'after';
                do {
                    seekerIterationCount++;
                    currentSubling = blockObject.previousElementSibling;
                    if (currentSubling&&currentSubling.classList.contains('rbinder-'+binderName)) {
                        neededElement = currentSubling;
                    }
                } while (currentSubling&&!neededElement&&seekerIterationCount < 5);
            }
            if (neededElement) {
                if (currentDirection === 'before') {
                    neededElement.classList.remove('rfwp_removedMarginTop');
                } else {
                    neededElement.classList.remove('rfwp_removedMarginBottom');
                }
            }
        }
    }

    return false;
};

function elementBinderNameGenerator() {
    let binderName = '',
        checkedElements,
        passed = false;

    while (passed===false) {
        binderName = Math.floor(Math.random()*100000);
        checkedElements = document.querySelectorAll('[data-rbinder="'+binderName+'"]');
        if (checkedElements.length < 1) {
            passed = true;
        }
    }

    return binderName;
}