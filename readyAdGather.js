var nReadyBlock = false;
var fetchedCounter = 0;

function sendReadyBlocksNew(blocks) {
    let xhttp = new XMLHttpRequest();
    let sendData = 'action=saveAdBlocks&type=blocksGethering&data='+blocks;
    xhttp.onreadystatechange = function(redata) {
        if (this.readyState == 4 && this.status == 200) {
            console.log('cache succeed');
        }
    };
    xhttp.open("POST", adg_object.ajax_url, true);
    xhttp.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
    xhttp.send(sendData);
}

function gatherReadyBlocks() {
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
            if (curState&&gatheredBlocks[i]['innerHTML'].length > 0&&gatheredBlocks[i]['dataset']['aid'] > 0&&curState!='no-block') {
                if (gatheredBlocks[i]['innerHTML'].length > 0) {
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