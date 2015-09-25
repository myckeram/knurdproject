/*
please see wp-commentpreview.php for more information
*/

var edButtons = new Array();
var edLinks = new Array();
var edOpenTags = new Array();

var pre_url = "";
var sub_url = "";
var comment_text = "";

function edButton(id, display, tagStart, tagEnd, access, tip, open) {
	this.id = id;				// used to name the toolbar button
	this.display = display;		// label on button
	this.tagStart = tagStart; 	// open tag
	this.tagEnd = tagEnd;		// close tag
	this.access = access;		// access key
	this.tip = tip;				// title
	this.open = open;			// set to -1 if tag does not need to be closed
}

edButtons[edButtons.length] = new edButton('ed_strong',		'b',		'<strong>',			'</strong>',		'b',	'Bold Text'		);
edButtons[edButtons.length] = new edButton('ed_em',			'i',		'<em>',				'</em>',			'i',	'Italic Text'	);
edButtons[edButtons.length] = new edButton('ed_strike',		'strike',	'<strike>',			'</strike>',		's',	'Strike Through');
edButtons[edButtons.length] = new edButton('ed_link',		'link',		'',					'</a>',				'a',	'Hyperlink'	); // special
edButtons[edButtons.length] = new edButton('ed_abbr',		'abbr',		'',					'</abbr>',			'r',	'Abbreviation'	); // special
edButtons[edButtons.length] = new edButton('ed_acronym',	'acronym',	'',					'</acronym>',		'y',	'Acronym'		); // special
edButtons[edButtons.length] = new edButton('ed_block',		'b-quote',	'<blockquote>',		'</blockquote>',	'q',	'Quoted Text'	);
edButtons[edButtons.length] = new edButton('ed_pre',		'code',		'<code>',			'</code>',			'c',	'Program Code'	);

function edShowButton(button, i) {
	if (button.id == 'ed_abbr') {
		return '<input type="button" id="' + button.id + '" accesskey="' + button.access + '" title="' + button.tip + 
		'" class="ed_button" onclick="edInsertAbbrv(edCanvas, ' + i + ');" value="' + button.display + '" />';
	}
	else if (button.id == 'ed_acronym') {
		return '<input type="button" id="' + button.id + '" accesskey="' + button.access + '" title="' + button.tip + 
		'" class="ed_button" onclick="edInsertAcronym(edCanvas, ' + i + ');" value="' + button.display + '" />';
	}
	else if (button.id == 'ed_block') {
		return '<input type="button" id="' + button.id + '" accesskey="' + button.access + '" title="' + button.tip + 
		'" class="ed_button" onclick="edInsertQuote(edCanvas, ' + i + ');" value="' + button.display + '" />';
	}
	else if (button.id == 'ed_link') {
		return '<input type="button" id="' + button.id + '" accesskey="' + button.access + '" title="' + button.tip + 
		'" class="ed_button" onclick="edInsertLink(edCanvas, ' + i + ');" value="' + button.display + '" />';
	}
	else {
		return '<input type="button" id="' + button.id + '" accesskey="' + button.access + '" title="' + button.tip + 
		'" class="ed_button" onclick="edInsertTag(edCanvas, ' + i + ');" value="' + button.display + '"  />';
	}
}

function edToolbar() {
	var htmltext = '<div id="ed_toolbar">Quicktags: ';
	for (i = 0; i < edButtons.length; i++) {
		htmltext = htmltext + edShowButton(edButtons[i], i);
	}
	htmltext = htmltext + '<input type="button" id="ed_close" class="ed_button" onclick="edCloseAllTags();" title="Close all open tags" value="Close Tags" />';
	htmltext = htmltext + '</div>';
	
	document.getElementById("quicktags").innerHTML = htmltext;
}


function dosubmit(mode, url) {
	if(comment_validate()) {
		document.getElementById("commentform").setAttribute("action", url);
		if(mode == 0) {
			if(! (document.getElementById("author") == null || document.forms['commentform'].cauthor == null)) {
				document.getElementById("actualauthor").value = document.getElementById("author").value;
				// hack for ie6
				if(document.getElementById("actualauthor").value == 'undefined') {
					document.forms['commentform'].actualauthor.value = document.forms['commentform'].cauthor.value;
				}
			}
		}
		return true;
	}
	else {
		return false;
	}
}

// modification code

function edAddTag(button) {
	if (edButtons[button].tagEnd != '') {
		edOpenTags[edOpenTags.length] = button;
		document.getElementById(edButtons[button].id).value = '/' + document.getElementById(edButtons[button].id).value;
	}
}

function edRemoveTag(button) {
	for (i = 0; i < edOpenTags.length; i++) {
		if (edOpenTags[i] == button) {
			edOpenTags.splice(i, 1);
			document.getElementById(edButtons[button].id).value = document.getElementById(edButtons[button].id).value.replace('/', '');
		}
	}
}

function edCheckOpenTags(button) {
	var tag = 0;
	for (i = 0; i < edOpenTags.length; i++) {
		if (edOpenTags[i] == button) {
			tag++;
		}
	}
	if (tag > 0) {
		return true; // tag found
	}
	else {
		return false; // tag not found
	}
}	

function edCloseAllTags() {
	var count = edOpenTags.length;
	for (o = 0; o < count; o++) {
		edInsertTag(edCanvas, edOpenTags[edOpenTags.length - 1]);
	}
}

// insertion code

function edInsertTag(myField, i) {
	//IE support
	if (document.selection) {
		myField.focus();
	    sel = document.selection.createRange();
		if (sel.text.length > 0) {
			sel.text = edButtons[i].tagStart + sel.text + edButtons[i].tagEnd;
		}
		else {
			if (!edCheckOpenTags(i) || edButtons[i].tagEnd == '') {
				sel.text = edButtons[i].tagStart;
				edAddTag(i);
			}
			else {
				sel.text = edButtons[i].tagEnd;
				edRemoveTag(i);
			}
		}
		myField.focus();
	}
	//MOZILLA/NETSCAPE support
	else if (myField.selectionStart || myField.selectionStart == '0') {
		var startPos = myField.selectionStart;
		var endPos = myField.selectionEnd;
		var cursorPos = endPos;
		var scrollTop = myField.scrollTop;

		if (startPos != endPos) {
			myField.value = myField.value.substring(0, startPos)
			              + edButtons[i].tagStart
			              + myField.value.substring(startPos, endPos) 
			              + edButtons[i].tagEnd
			              + myField.value.substring(endPos, myField.value.length);
			cursorPos += edButtons[i].tagStart.length + edButtons[i].tagEnd.length;
		}
		else {
			if (!edCheckOpenTags(i) || edButtons[i].tagEnd == '') {
				myField.value = myField.value.substring(0, startPos) 
				              + edButtons[i].tagStart
				              + myField.value.substring(endPos, myField.value.length);
				edAddTag(i);
				cursorPos = startPos + edButtons[i].tagStart.length;
			}
			else {
				myField.value = myField.value.substring(0, startPos) 
				              + edButtons[i].tagEnd
				              + myField.value.substring(endPos, myField.value.length);
				edRemoveTag(i);
				cursorPos = startPos + edButtons[i].tagEnd.length;
			}
		}
		myField.focus();
		myField.selectionStart = cursorPos;
		myField.selectionEnd = cursorPos;
		myField.scrollTop = scrollTop;
	}
	else {
		if (!edCheckOpenTags(i) || edButtons[i].tagEnd == '') {
			myField.value += edButtons[i].tagStart;
			edAddTag(i);
		}
		else {
			myField.value += edButtons[i].tagEnd;
			edRemoveTag(i);
		}
		myField.focus();
	}
}

function edInsertContent(myField, myValue) {
	//IE support
	if (document.selection) {
		myField.focus();
		sel = document.selection.createRange();
		sel.text = myValue;
		myField.focus();
	}
	//MOZILLA/NETSCAPE support
	else if (myField.selectionStart || myField.selectionStart == '0') {
		var startPos = myField.selectionStart;
		var endPos = myField.selectionEnd;
		myField.value = myField.value.substring(0, startPos)
		              + myValue 
                      + myField.value.substring(endPos, myField.value.length);
		myField.focus();
		myField.selectionStart = startPos + myValue.length;
		myField.selectionEnd = startPos + myValue.length;
	} else {
		myField.value += myValue;
		myField.focus();
	}
}

// custom action button methods

function edInsertLink(myField, i, defaultValue) {
	if (!defaultValue) {
		defaultValue = 'http://';
	}
	if (!edCheckOpenTags(i)) {
		var URL = prompt('Enter the URL' ,defaultValue);
		if (URL) {
			edButtons[i].tagStart = '<a href="' + URL + '">';
			edInsertTag(myField, i);
		}
	}
	else {
		edInsertTag(myField, i);
	}
}

function edInsertAbbrv(myField, i, defaultValue) {
	if (!edCheckOpenTags(i)) {
		var title = prompt('Enter description of the abbreviation (leave blank if not applicable)', '');
		if (title == '') {
			edButtons[i].tagStart = '<abbr>';
			edInsertTag(myField, i);
		}
		else if (title != null) {
			edButtons[i].tagStart = '<abbr title="' + title + '">';
			edInsertTag(myField, i);
		}
	}
	else {
		edInsertTag(myField, i);
	}
}

function edInsertAcronym(myField, i, defaultValue) {
	if (!edCheckOpenTags(i)) {
		var title = prompt('Enter description of the acronym (leave blank if not applicable)', '');
		if (title == '') {
			edButtons[i].tagStart = '<acronym>';
			edInsertTag(myField, i);
		}
		else if (title != null) {
			edButtons[i].tagStart = '<acronym title="' + title + '">';
			edInsertTag(myField, i);
		}
	}
	else {
		edInsertTag(myField, i);
	}
}

function edInsertQuote(myField, i, defaultValue) {
	if (!edCheckOpenTags(i)) {
		var cite = prompt('Enter the site your quoting from (leave blank if not applicable)', '');
		if (cite == '') {
			edButtons[i].tagStart = '<blockquote>';
			edInsertTag(myField, i);
		}
		else if (cite != null) {
			edButtons[i].tagStart = '<blockquote cite="' + cite + '">';
			edInsertTag(myField, i);
		}
	}
	else {
		edInsertTag(myField, i);
	}
}

function comment_validate() {
	var all_valid = true;
	if(document.getElementById('email') != null) {
		if(document.getElementById('author').value == '') {
			document.getElementById('author').className = "errorfield";
			all_valid = false;
		}
		else {
			document.getElementById('author').className = "default";
		}
		
		var regexp = new RegExp('^[\\w-_\.]+@[\\w-_]+[\.][\\w-_\.]+$');
						
		if(document.getElementById('email').value == '' || ! document.getElementById('email').value.match(regexp)) {
			document.getElementById('email').className = "errorfield";
			all_valid = false;
		}
		else {
			document.getElementById('email').className = "default";
		}
	}
	
	if(document.getElementById('comment').value == '') {
		document.getElementById('comment').className = "errorfield";
		all_valid = false;
	}
	else {
		document.getElementById('comment').className = "default";
	}
	
	return all_valid;
}