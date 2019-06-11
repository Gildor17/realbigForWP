var nReadyBlock = false;
var fetchedCounter = 0;

function sendReadyBlocksNew(blocks) {
    let decodedData;
    let xhttp = new XMLHttpRequest();
    let sendData = 'action=saveAdBlocks&type=blocksGethering&data='+blocks;
    xhttp.onreadystatechange = function(redata) {
        if (this.readyState == 4 && this.status == 200) {
            if (redata) {
                // decodedData = JSON.parse(redata);
            }

            console.log('cache succeed');
            // document.getElementById("demo").innerHTML = this.responseText;
        }
    };
    // xhttp.open("POST", ajaxurl, true);
    xhttp.open("POST", adg_object.ajax_url, true);
    xhttp.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
    xhttp.send(sendData);
}

function gatherReadyBlocksOrig() {
    let dottaCounter = 0;
    let blocks = '';
    let gatheredBlocks = document.getElementsByClassName('content_rb');
    let checker = 0;
    let adContent = '';
    let curState = '';

    if (gatheredBlocks.length > 0) {
        // blocks += '{"data":[';
        blocks += '{data:[';
        for (let i = 0; i < gatheredBlocks.length; i++) {
            curState = gatheredBlocks[i]['dataset']["state"].toLowerCase();
            checker = 0;
            if (curState&&(gatheredBlocks[i]['innerHTML'].length > 0||curState=='no-block')) {
                if (gatheredBlocks[i]['innerHTML'].length > 0) {
                    checker = 1;
                    adContent = gatheredBlocks[i]['innerHTML'].replace(/\"/g, "\'");
                } else if (curState=='no-block') {
                    checker = 1;
                    adContent = '';
                }
                if (checker==1) {
                    if (dottaCounter > 0) {
                        blocks += ',';
                    }
                    // blocks += '{"id":"'+gatheredBlocks[i]['dataset']['id']+'","code":"'+adContent+'"}';
                    blocks += '{id:'+gatheredBlocks[i]['dataset']['id']+',code:"'+adContent+'"}';
                    dottaCounter++;
                }
            }
        }
        blocks += ']}';

        let newBlocks = JSON.stringify(blocks);

        sendReadyBlocksNew(blocks);
    }
}

function gatherReadyBlocksAfterOrig() {
    // let blocks = '';
    let blocks = {};
    let counter1 = 0;
    let gatheredBlocks = document.getElementsByClassName('content_rb');
    let checker = 0;
    let adContent = '';
    let curState = '';
    let thisData = [];
    let sumData = [];
    let newBlocks = '';
    let thisDataString = '';

    if (gatheredBlocks.length > 0) {
        blocks.data = {};

        for (let i = 0; i < gatheredBlocks.length; i++) {
            curState = gatheredBlocks[i]['dataset']["state"].toLowerCase();
            checker = 0;
            if (curState&&(gatheredBlocks[i]['innerHTML'].length > 0||curState=='no-block')) {
                if (gatheredBlocks[i]['innerHTML'].length > 0) {
                    checker = 1;
                    adContent = gatheredBlocks[i]['innerHTML'];
                    // adContent = decodeURIComponent(adContent);
                    // adContent = adContent.replace(/&amp;/g, 'a_m_p');
                    // adContent = adContent.replace(/&quot;/g, '"');
                    // adContent = adContent.replace(/\"/g, "\'");
                    adContent = adContent.replace(/\&/g, "rb_amp_rb");
                    adContent = adContent.replace(/\'/g, "rb_quot_rb");
                    adContent = adContent.replace(/\"/g, "rb_double_quot_rb");
                    adContent = adContent.replace(/\?/g, "rb_question_mark_rb");
                    adContent = adContent.replace(/\</g, "rb_open_angle_rb");
                    adContent = adContent.replace(/\>/g, "rb_close_angle_rb");
                    adContent = adContent.replace(/\;/g, "rb_semicolon_rb");
                    adContent = adContent.replace(/script/g, "scr_ipt");
                } else if (curState=='no-block') {
                    checker = 1;
                    adContent = '';
                }
                if (checker==1) {
                    blocks.data[counter1] = {id:gatheredBlocks[i]['dataset']['id'],code:adContent};
                    counter1++;
                }
            }
        }

        blocks = JSON.stringify(blocks);

        sendReadyBlocksNew(blocks);
    }
}

function gatherReadyBlocks() {
    // let blocks = '';
    let blocks = {};
    let counter1 = 0;
    let gatheredBlocks = document.getElementsByClassName('content_rb');
    let checker = 0;
    let adContent = '';
    let curState = '';
    let thisData = [];
    let sumData = [];
    let newBlocks = '';
    let thisDataString = '';

    if (gatheredBlocks.length > 0) {
        blocks.data = {};

        for (let i = 0; i < gatheredBlocks.length; i++) {
            curState = gatheredBlocks[i]['dataset']["state"].toLowerCase();
            checker = 0;
            if (curState&&(gatheredBlocks[i]['innerHTML'].length > 0||curState=='no-block')) {
                if (gatheredBlocks[i]['innerHTML'].length > 0) {
                    checker = 1;
                } else if (curState=='no-block') {
                    checker = 1;
                }
                if (checker==1) {
                    blocks.data[counter1] = {id:gatheredBlocks[i]['dataset']['id'],code:gatheredBlocks[i]['dataset']['aid']};
                    counter1++;
                }
            }
        }

        blocks = JSON.stringify(blocks);
        sendReadyBlocksNew(blocks);
    }
}

function timeBeforeGathering() {
    let gatheredBlocks = document.getElementsByClassName('content_rb');
    let okStates = ['done','refresh-wait','no-block','fetched'];
    let curState = '';

    for (let i = 0; i < gatheredBlocks.length; i++) {
        if (!gatheredBlocks[i]['dataset']["state"]) {
            nReadyBlock = true;
            break;
        } else {
            curState = gatheredBlocks[i]['dataset']["state"].toLowerCase();
            if (!okStates.includes(curState)) {
                nReadyBlock = true;
                break;
            } else if (curState=='fetched'&&fetchedCounter < 3) {
                fetchedCounter++;
                nReadyBlock = true;
                break;
            }
        }
    }
    if (nReadyBlock == true) {
        nReadyBlock = false;
        setTimeout(timeBeforeGathering,2000);
    } else {
        gatherReadyBlocks();
    }
}

if (document.readyState === "complete" || (document.readyState !== "loading" && !document.documentElement.doScroll)) {
    timeBeforeGathering();
} else {
    document.addEventListener("DOMContentLoaded", timeBeforeGathering, false);
}