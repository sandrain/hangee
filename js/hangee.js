/**
 * hangee.js
 *
 */

function openPopup(url, name)
{
	options = "menubar=no,toolbar=no,directories=no,status=no,location=no,resizable=yes,scrollbars=yes,width=400,height=500";

	popup = window.open(url, name, options);

	popup.focus();
}

/*
function strpos(haystack, needle, offset)
{
	var i = (haystack+'').indexOf(needle, (offset || 0));
	return i == -1 ? false : i;
}
*/

