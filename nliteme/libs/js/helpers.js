
/* some helper javascript funtions
 * 
 * 
 */
 
function showHideElem(showHideDiv, switchTextDiv) {
	var divToShow = document.getElementById(showHideDiv);
	var textDivToChange = document.getElementById(switchTextDiv);
	if(divToShow.style.display == "block") {
    		divToShow.style.display = "none";
    	if(textDivToChange)
    	{
			textDivToChange.innerHTML = "SEARCH";
		}
  	}
	else {
		divToShow.style.display = "block";
		if(textDivToChange)
    	{
			textDivToChange.innerHTML = "HIDE SEARCH";
		}
	}
} 

function showHideTableElem(showHideDiv, switchTextDiv) {
	var divToShow = document.getElementById(showHideDiv);
	var textDivToChange = document.getElementById(switchTextDiv);
	if(divToShow.style.visibility == "visible") {
    		divToShow.style.visibility = "collapse";
    	if(textDivToChange)
    	{
			textDivToChange.innerHTML = "SEARCH";
		}
  	}
	else {
		divToShow.style.visibility = "visible";
		if(textDivToChange)
    	{
			textDivToChange.innerHTML = "HIDE SEARCH";
		}
	}
} 

