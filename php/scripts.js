function boxOpen(myid) {
	document.getElementById(myid).setAttribute("style", "display:inline;", 0);
	document.getElementById(myid).style.display = "inline";
}

function boxClose(myid) {
	document.getElementById(myid).setAttribute("style", "display:none;", 0);
	document.getElementById(myid).style.display = "none";
}

function boxWrite(myid,mymessage) {
	document.getElementById(myid).innerHTML = mymessage;
}
