/**
***************************************************************************************************
 * @Author		Michal Krugly
 * 
 * Copyright (c) 2013 by Michal Krugly (mailto: mickrugly[at]gmail.com)
 * 
 * All rights reserved.
 * Redistribution and use in source and binary forms, with or without modification, are permitted provided that the following conditions are met:
 *
 *   - Redistributions of source code must retain the above copyright notice, this list of conditions and the following disclaimer.
 *   - Redistributions in binary form must reproduce the above copyright notice, this list of conditions and the following disclaimer in the documentation and/or other materials provided with the distribution.
 *   - Neither the name of the Michal Krugly nor the names of its contributors may be used to endorse or promote products derived from this software without specific prior written permission.

 * DISCLAIMER:
 * THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS "AS IS" AND ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT LIMITED TO, THE IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS FOR A PARTICULAR PURPOSE ARE DISCLAIMED. IN NO EVENT SHALL THE COPYRIGHT OWNER OR CONTRIBUTORS BE LIABLE FOR ANY DIRECT, INDIRECT, INCIDENTAL, SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES (INCLUDING, BUT NOT LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES; LOSS OF USE, DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER CAUSED AND ON ANY THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT LIABILITY, OR TORT (INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN ANY WAY OUT OF THE USE OF THIS SOFTWARE, EVEN IF ADVISED OF THE POSSIBILITY OF SUCH DAMAGE. 
 
**************************************************************************************************
**/
// infinite scroll stuff
var infiniteScrollSema = false;
// set min-height of the main content based on window size
$(function () {
	$(window).resize(function() {
		//$('#main-content').css({'min-height':($(window).height()-160+'px')});
		$('#main-content').css({'min-height':($(window).height()-parseInt($('#main-content').css("margin-top"))-parseInt($('#main-content').css("margin-bottom"))-$('#header').height()-$('#top-nav').height()-$('#page-footer').height()-10+'px')});
	});
	$(window).trigger('resize');
});

// tooltip handling
$(function () {
    $(document).tooltip({
        items: '.tooltip-dynamic',
        show: {delay: 200},
        position: { my: "left top", at: "right center", collision: "flipfit"},
		open: function (event, ui) {
			ui.tooltip.css("max-width", "500px");
			ui.tooltip.css("max-height", "600px");
			ui.tooltip.css("overflow", "auto");
		},
        close: function (event, ui) {
            ui.tooltip.hover(
            function () {
                $(this).stop(true).fadeTo(20, 1);
            },

            function () {
                $(this).fadeOut(400, function () {
                    $(this).remove();
                })
            });
        },
        content: function(callback) {
			// save current title attribute
			var actionUrl = $(this).data("tooltip-action");
			var actionData = {};
			if (! actionUrl) {
				actionUrl = 'libs/ajax/tooltipcontent.php';
				actionData['id'] = this.id;
			}
			
			var currentTitle=$(this).prop('title');
			$.ajax({
				// the URL for the request
				//url: 'libs/ajax/tooltipcontent.php',
				// the data to send (will be converted to a query string)
				//data: {
				//	id: this.id
				//},
				url: actionUrl,
				data: actionData,
				// whether this is a POST or GET request
				type: "GET",
				// the type of data we expect back
				dataType : "html",
				// code to run if the request succeeds;
				// the response is passed to the function
				success: function( data ) {
					if (!data || data.length === 0) {
						callback(currentTitle);
					} else {
						callback(data);
					}
				}
			});
		}
    });
});

function iResize() {
    document.getElementById('widget-m-content-').style.height = 
    document.getElementById('widget-m-content-').contentWindow.document.body.offsetHeight + 'px';
}

// general
function initialise(){
    // infinite scroll stuff (ToDo. extend it for other pages)
    $(window).scroll(function()
    {
      // fire the scroll update 200 untis before page end
	  if($(window).scrollTop() + 200 + $(window).height() >= $(document).height() && infiniteScrollSema === false)
      {
        var scTop = $(window).scrollTop();
        if (scTop != 0) {       
          $(".json-link").each(function() {
            if ($(this).data('container') == 'buildcomparetbody' && $(this).data('sort-count') > 0) {
              //if (parseInt($(this).attr('data-sort-iter'),10) < $(this).data('sort-count')) {
                requestBuildCompareTBody($(this));
              //}
            }    
          });
          $(".ajax-table-paging").each(function() {
            var pageCount = parseInt($(this).data('page-count'),10);
            var page = parseInt($(this).attr('data-page'),10);
            var containerName = $(this).data("container");
            if (pageCount > 1 && containerName && page < pageCount) {
                scrollTablePage($(this));
            }    
          });		  
		  
        }
      }
    });
    // json-link initial filling of the table;
    $(".json-link").each(function() {
       if($(this).data('container') == 'buildcomparetbody') {
          if( parseInt($(this).attr('data-sort-iter'),10) == 0 && $(this).data('sort-count') > 0) {
             requestBuildCompareTBody($(this));
          } 
        }    
    });
	// handle paging for infinite scroll tables
	$(".ajax-table-paging").each(function() {
            var pageCount = parseInt($(this).data('page-count'),10);
            var page = parseInt($(this).attr('data-page'),10);
            var containerName = $(this).data('container');
            if (pageCount > 1 && containerName && page == 0) {
               scrollTablePage($(this));
            }
	});	

    	// makes the skewed headers height adjusted based on the content length
	$(".table-blue-ext").find('.rotate-45').each(function() { $(this).css('height', Math.ceil($(this).find('span').first().outerWidth() * Math.cos(45*Math.PI/180)));});
    
	// makes the header sticking to the top of the viewport, cacheHeaderHeight set for performance reasons
	$(".table-blue-ext").stickyTableHeaders({cacheHeaderHeight: true});
	
	// progress bar for rate
	$(".rate-progress-bar").progressbar({
	  value: false  
	});
	$(".rate-progress-bar").each(function() {
		$(this).progressbar('value',$(this).data('val'));
	});
	
	// dashboard tabs
	var activeTab = ($("#dashboard-tabs").attr('data-active')) ? $("#dashboard-tabs").data("active") : 0;
	$("#dashboard-tabs").tabs({
		beforeLoad: function(event, ui) {
			// if the target panel is empty, return true
			return ui.panel.html() == "";
		},
		beforeActivate: function(event, ui) {
			$(this).data("active", ui.newTab.index()); // update active tab variable
			// update browser window address
			var curUrl = window.location.href;
			window.history.pushState({'url':curUrl},"", queryString.setParam("tab",ui.newTab.index(),curUrl));	
			return true;
		},
		active: activeTab
	});

	$('.scrollable-table').scrollabletable({
		maxHeight: 400,
		widthInPx: true
	});
	
	// widgets
	$( ".dashboard-column" ).sortable({
		connectWith: ".dashboard-column",
		handle: ".widgie-header",
		cancel: ".widgie-toggle",
		placeholder: "widgie-placeholder"
	});
	$( ".widgie" )
		.addClass( "ui-widget ui-widget-content ui-helper-clearfix" )
		.find( ".widgie-header" )
		.filter(function(index) {
			return $( "span", this ).length === 0;
		})
		.addClass( "ui-widget-header" )
		.prepend( "<span class='ui-icon ui-icon-minusthick widgie-toggle'></span>");
	$( ".widgie-toggle" ).unbind('click').on('click', function(e) {
		var icon = $( this );
		icon.toggleClass( "ui-icon-minusthick ui-icon-plusthick" );
		icon.closest( ".widgie" ).find( ".widgie-content" ).toggle();
	});
	$('.widgie-iframe').on('load', function () {
		var inx = $(this).attr('id');
		$('#'+inx+'-pre').hide();
    });
    
    // sort table by column (localy)
    $('.table-sorter').unbind('click').on('click', function(e) {
		// get column index
		var colInx = $(this).parent().children().index($(this));
		// toggle direction
		var dir = 1;
        if($(this).data("dir") == 1) 
        {
			dir = -1;
		}
		$(this).parent().find('.table-sorter').data("dir", 0);
        $(this).data("dir", dir);
        // get table
        var table = $(this).closest('table');
		var sortedTable = nlitemeUtils.sortTable(table, colInx, dir);
		var test;
	});
	
	// autocomplete handling 
	$(".autocompletable").autocomplete({
        source: function(request, response) {
            $.ajax({
                url: "libs/ajax/autocomplete.php",
                dataType: "json",
                data: {
                    term : request.term,
                    field : this.element.attr('name')
                },
                success: function(data) {
                    response(data);
                },
                error: function(jqXHR, textStatus, errorThrown)
                { 	
                }
            });
        },
        select: function( event, ui ) {
            var val =  $(this).val();
            val = val.split(',');
            val.pop();
            $.map(val, $.trim);
            if(val.length > 0)
            {
                val.join(',');
                val = val + ','
            }
            val = val + ui.item.value;
            $(this).val(val);
            return false;
        },
        min_length: 1,
        delay: 30
    });
    
    // chosen handling (static lists)
    $('.chosen-select').chosen({
		width: "100%",
		placeholder_text_multiple: " ",
		placeholder_text_single: " ",
		allow_single_deselect: true,
		disable_search_threshold: 10,
		search_contains: true,
	});
    
    // toggler button
    // TBD for some reason sometimes does not work reliably. Find out why
	$('.toggler').unbind('click').click(function() {
		var divToToggle = $(this).data("toggleable");
		$(divToToggle).toggle();
	});

	// toggle checkboxes
	$('#select_all').click(function() {
		var c = this.checked;
		$(':checkbox').prop('checked',c);
	});
	
	// if delete for mutliple selection is clicked check if checkboxes are checked ;-)
	//$('.button-delete-multiple').click(function(event) {
	//	if ($(this).closest('form').find("input:checkbox:checked").length == 0) {
	//		alert('Please first make a selection from the list');
	//		event.preventDefault();
    //    }
    //});
	
	// jquery based button
	$('.button-jq').button();
    
	// handling form control buttons on click modify enclosing form action with proper action ;-)
	$('.submit-button-action').unbind('click').click(function(event) {
		var currentForm = $(this).closest('form');
		
		// if data-name contains action related string modify enclosing form action with proper action ;-)
		// and submit
		var nameFound = $(this).data("name").match( /com\.nliteme\./ );
		if(nameFound != null && nameFound.length) {
			if($(this).hasClass('button-delete-multiple') && currentForm.find("input:checkbox:checked").length == 0) {
				alert('Please first make a selection from the list');
				return;
			} else {
				// set proper action
				currentForm.attr('action', queryString.setParam('action', $(this).data("name"), currentForm.attr('action')));
				// submit form
				currentForm.submit();
			}
		} 
		// if data-name is "emptyform" clear the current form inputs
		else if($(this).data("name") == "emptyform") {
			currentForm.find('input:text, input:password, input:file, select, textarea').val('');
			currentForm.find('.chosen-select').trigger('chosen:updated'); // update chosen
			currentForm.find('input:radio, input:checkbox').removeAttr('checked').removeAttr('selected');
			currentForm.find('#form-id-label').text('');
			// hide related div
			currentForm.find('#related').toggle();
			// remove id param from URL
			currentForm.attr('action', queryString.unsetParam(currentForm.data("id"),currentForm.attr('action')));
			var curUrl = window.location.href;
			window.history.pushState({'url':curUrl},"", queryString.unsetParam(currentForm.data("id"),curUrl));
			
		}
		// if data-name is "copyform" clear id in label and all .form-clearoncopy-field class fields only
		else if($(this).data("name") == "copyform") {
			currentForm.find('input:text.form-clearoncopy-field, input:password.form-clearoncopy-field, input:file.form-clearoncopy-field, select.form-clearoncopy-field, textarea.form-clearoncopy-field').val('');
			currentForm.find('.chosen-select').trigger('chosen:updated'); // update chosen
			currentForm.find('input:radio.form-clearoncopy-field, input:checkbox.form-clearoncopy-field').removeAttr('checked').removeAttr('selected');
			currentForm.find('#form-id-label').text('');
			// hide related div
			currentForm.find('#related').toggle();
			// remove id param from URL
			currentForm.attr('action', queryString.unsetParam(currentForm.data("id"),currentForm.attr('action')));
			var curUrl = window.location.href;
			window.history.pushState({'url':curUrl},"", queryString.unsetParam(currentForm.data("id"),curUrl));			
		}
    });
    
    // handle form reset button
    $(".reset-form").unbind('click').click(function(e) {
		e.preventDefault();
		var currentForm = $(this).closest('form');
		currentForm.trigger("reset");
		// remove selected from all options and set default selection if configured
		currentForm.find('option').removeAttr('selected').prop('selected', function() {
				return ($(this).data("default") == undefined ? false : true);
		});
		currentForm.find('input:text, input:password, input:file, textarea').val('');
		currentForm.find('.chosen-select').trigger('chosen:updated');
    });
    
    // handle sumbit form event
	$(".ajax-form").submit(function(e)
	{
		var isFormValid = true;
		// check if required fields are set (chosen-select class has to be set in filter, because the underlying field is not visible)
		$(this).find('input, textarea, select').filter('[required]:visible, .field-required', this).each(function() {
			if(this.value == '') {
				isFormValid = false;
				return false;
			} else {
				return true;
			}
		});
		
		// if form is valid continue with submit
		if(isFormValid) {
			//var formInputs = $(this).serializeArray();
			// submit only non-empty elements
			var formInputs = $(this).find('input, textarea, select').filter(function(index){
					return (this.value != "");
				}).serializeArray();
            
			var formAction = $(this).attr("action");
			var formMethod = $(this).attr("method");
			// show preloader
			$(document).find("#main-content-pre").show();
			$.ajax(
			{
				url : formAction,
				type: formMethod,
				data : formInputs,
				success:function(data, textStatus, jqXHR)
				{
					//data: returned data from server
					$(document).find('#main-content').empty();
					$(document).find('#main-content').html(data);
					// if form type is GET update browser url 
					if(this.type === 'GET')
					{
						var curUrl = window.location.href;
						var mainUrl = this.url.replace(/(action\=com\.nliteme\.)(?!Main)/, '$1Main');
						window.history.pushState({'url':curUrl},"", mainUrl);
					}
				},
				error: function(jqXHR, textStatus, errorThrown)
				{  
				},
				complete: function()
				{
					// hide preloader
					$(document).find("#main-content-pre").hide();					
				}
			});
		}
		
		// if form or it's parent is toggleable toggle it
		$(this).closest('.toggleable').toggle();
		
		e.preventDefault(); //STOP default action
		e.unbind(); //unbind. to stop multiple form submit.
	});
	
	// handle ajax links
	$('.ajax-link').on('click', function(e) {
		$.ajax(
		{
			url : $(this).prop('href'),
			type: 'POST',
			success:function(data, textStatus, jqXHR)
			{
				//data: returned data from server
				$(document).find('#main-content').html(data);
				// update browser url
				var curUrl = window.location.href;
				var mainUrl = this.url.replace(/(action\=com\.nliteme\.)(?!Main)/, '$1Main');
				window.history.pushState({'url':curUrl},"", mainUrl);
			},
			error: function(jqXHR, textStatus, errorThrown)
			{  
			}
		});
		e.preventDefault();
		e.unbind();
		return false; // stop the browser following the link
	});

	// datetimepicker handling
    $(".hasdatetimepicker").each(function() {
		$(this).datetimepicker({
			howWeek: true,
			showOtherMonths: true,
			selectOtherMonths: true,
			dateFormat: "yy-mm-dd",
			
			onSelect: function (selectedDateTime){
				var re = /(.*?)_(FROM|TO)/i;
				var found = this.id.match( re );
				if(found && found.length == 3)
				{
					if(found[2] == "FROM") {
						var mybro=found[1]+"_TO";
						$('#'+mybro).datetimepicker('option', 'minDate', $(this).datetimepicker('getDate') );
					} else {
						var mybro=found[1]+"_FROM";
						$('#'+mybro).datetimepicker('option', 'maxDate', $(this).datetimepicker('getDate') );
					}
				}			
			}
		});
    });
	
	// make use of jquery ui css classes for icons outside ui widgets
	$('.ui-icon').hover(
        function () {
            $(this).parent().addClass('ui-state-hover');
        },
        function () {
            $(this).parent().removeClass('ui-state-hover');
        }
    );
    
    // semistatic tooltips
    $('.tooltip-semistatic').tooltip({
        items: '.tooltip-semistatic',
        show: {delay: 200},
        position: { my: "left top", at: "right center", collision: "flipfit"},
		open: function (event, ui) {
			ui.tooltip.css("max-width", "500px");
			ui.tooltip.css("max-height", "600px");
			ui.tooltip.css("overflow", "auto");
		},
        close: function (event, ui) {
            ui.tooltip.hover(
            function () {
                $(this).stop(true).fadeTo(20, 1);
            },

            function () {
                $(this).fadeOut(400, function () {
                    $(this).remove();
                })
            });
        },
        content: function(callback) {
			callback($(this).find('.tooltip-holder').html());
		}
    });
    
    // resize description textarea height to fit the content
    $('.aes-description').on( 'change keyup paste cut', function (){
		$(this).height(this.scrollHeight);
	}).change();
	
	//fade out the .fade-me-out selections
	$('.fade-me-out').delay(3000).fadeOut(1000);
};

$(document).ready(function() {
    // initilize the rest
    initialise();
});
$(document).ajaxComplete(function () {		
    initialise();
});

// handle history.popstate without jquery
// currently only reload the page (to be checked how it works in other browsers)
//window.onpopstate = function(e){
//	window.location.reload();

    /*
    if(e.state){
        window.location.href = e.state.url;
    }
    */ 
//};
// use this instead of the above  window.onpopstate = function(e){..
// make sure that you always define a state (i.e. whenever pushState is done)
// this prevent from looped reloading on page load with some browsers e.g. Safari (because on page load state is null)
// TDB. use history.js to handle window history
// info from http://stackoverflow.com/questions/15896434/window-onpopstate-on-page-load
window.addEventListener('popstate', function(event) {
    if (event.state) {
        window.location.reload();
    }
}, false);

/* 
 * nliteme build compare json based related functions
 */
function scrollTablePage(input) {
  var containerInput = input.data('json-input');
  var containerName = input.data('container');
  var pageCount = parseInt(input.data('page-count'),10);
  var page = parseInt(input.attr('data-page'),10) + 1; // next page
  var sessScrollid = input.data('rand');
  input.attr('data-page',page); // update data-page attribute with the incremented page
  var link = input.data('ajax-table-link');
  if (page < pageCount) {
	// update link with a next page
    link = queryString.setParam('page', page, link);
	infiniteScrollSema = true;
	$('#'+containerName+'-pre').show();
	$.ajax(
	{
		url : link,
		type: 'GET',
		context: sessScrollid,
		success:function(data, textStatus, jqXHR)
		{
			// console.log(this + ", " + $(".ajax-table-paging").first().data('rand'));
			infiniteScrollSema = false;
			if ($.isEmptyObject(data)) {
				scrollTablePage(input);
			} else if (this == $(".ajax-table-paging").first().data('rand')) {                              
				$(data).appendTo("#"+containerName);
				if ($(data).length > 0 && $('#message-container-messages').is(":visible"))
				{
					$('#message-container-messages').fadeOut(1000);
				}
			}
		},
		error: function(jqXHR, textStatus, errorThrown)
		{
			infiniteScrollSema = false;
		},
		complete: function()
		{
			infiniteScrollSema = false;
			$('#'+containerName+'-pre').hide();
			// check if height of this element is smaller than it's container
			// and call the function again
			if ($(window).height() >= $(document).height()) {
				scrollTablePage(input);
			}
		}
	});
  }
};

/* 
 * nliteme build compare json based related functions
 */
function requestBuildCompareTBody(input) {
  var containerInput = input.data('json-input');
  var containerName = input.data('container');
  var sortMain = input.data('sort-main');
  var sortIter = parseInt(input.attr('data-sort-iter'),10);
  var sortStep = input.data('sort-step');
  var link = input.data('json-link');
  if (sortIter < input.data('sort-count')) {
	var output = {};
	// first cleanup the js tmpl, if this is a compare
	// (otherwise search form submit does not update the cached template and a new page load is needed)
	if (sortIter == 0) {
		tmpl.cache["tmpl-"+containerName] = null;
	}
	// now prepare the input for ajax request
	$.each(containerInput, function(key, value) {
		if (key == 'compareby') {
			output['compareby'] = containerInput['compareby'];
		} else if (key == 'sortings') {
			$.each(value, function(key, value) {         
			    if (key == sortMain) {
			    	arr = [];
			    	var limit = sortIter+sortStep > Object.keys(value['index']).length ? Object.keys(value['index']).length : sortIter+sortStep;
			    	var start = sortIter;
			    	for(var i=start; i<limit ;i++)
			    	{
						arr.push(value['index'][i]);
			    	}
			    	output[key] = arr;
			    	input.attr('data-sort-iter',limit);
			    } else {
				    output[key] = Object.keys(value['map']);
			    }
			});
		} else if (key == 'builds') {
			output['buildid'] = Object.keys(value['buildid']['map']);
		}
	});
	infiniteScrollSema = true;
	$('#'+containerName+'-pre').show();
	$.ajax(
	{
		url : link,//+'&'+$.param(output),
		type: 'POST',
		dataType:'json',
		data: output,
		success:function(data, textStatus, jqXHR)
		{
			infiniteScrollSema = false;
			if ($.isEmptyObject(data)) {
				requestBuildCompareTBody(input);
			} else {
				var output = processBuildCompareTBody(data,input);
				$(output).appendTo("#"+containerName);
			}
		},
		error: function(jqXHR, textStatus, errorThrown)
		{
			infiniteScrollSema = false;
		},
		complete: function()
		{
			infiniteScrollSema = false;
			$('#'+containerName+'-pre').hide();
			// check if height of this element is smaller than it's container
			// and call the function again
			if ($(window).height() >= $('#'+containerName).height()) {
				requestBuildCompareTBody(input);
			}
		}
	});
  }
};
    
function processBuildCompareTBody(data, input)
{
   var output = "";
   var containerName = input.data('container');
   var containerInput = input.data('json-input');
   var recordCount = 0;
   $.each(data, function(k,datarow){
        var tmpldata = {};
        $.each(containerInput, function(key, sortColumn) {
            if (key == 'sortings') {
              $.each(sortColumn, function(key, value) {
                var inx = datarow[key]['index'];
                var mapped_value = value['map'][inx];
                var details = datarow[key];
                details['value'] = mapped_value;
                tmpldata[key] = details;
              });
            } else if (key == 'builds') {
              var builds = {};
              $.each(sortColumn['buildid']['index'], function(k,v) {
                  builds['b'+v] = datarow[key][v];
                  tmpldata['b'+v] = datarow[key][v];
              });
              //tmpldata[key] = builds;
            }
        });
        recordCount = recordCount + 1;
        output = output+tmpl("tmpl-"+containerName, tmpldata);
   });
   // update label, if exists, with a number of accumulated records
   var labelContainer = $('#'+containerName+'-counter');
   if (labelContainer) {
        recordCount = recordCount + parseInt(labelContainer.attr('data-count'),10);
        labelContainer.attr('data-count',recordCount);
        labelContainer.text(recordCount);
   } 
   return output;
};

/* 
 * nliteme extentions
 */

// sorting function
(function () {
	'use strict';
	var nlitemeUtils = {};
	nlitemeUtils.sortTable = function(table, colInx, direction) {
		var rows = table.children('tbody').children('tr');
		rows.sort(function(a, b) {
			var A = $(a).children('td').eq(colInx).text().toUpperCase().replace(/\%\n?/,'');
			var B = $(b).children('td').eq(colInx).text().toUpperCase().replace(/\%\n?/,'');
			// for numbers
			if($.isNumeric(A) && $.isNumeric(B)) {
				return (direction*(A-B));
			}
			//for strings
			if(A > B) {
				return direction;
			} 
			if(A < B) {
				return (-1*direction);
			} 
			return 0;
		});
		$.each(rows, function(index, row) {
			table.children('tbody').append(row);
		});
		return table;
	};
	
	if (typeof module !== 'undefined' && module.exports) {
		module.exports = nlitemeUtils;
	} else {
		window.nlitemeUtils = nlitemeUtils;
	}
})();

/*
 * scrollabletable widget
 * adds scroll bar to the body
 */ 
(function($) {
$.widget( "ui.scrollabletable", {

	options: {
		height: 'auto',
		maxHeight: 300,
		widthInPx: true,
	},

	_create: function(){
		var $self = $(this.element);

		// first get current cells width and set them explicitly for each th and td (so that they are the same)
		this._setCellsWidth($self);
		// then style thead and tbody so that scroll bar is present
		this._setScrollbarStyle($self);
		// final styling e.g. right padding adjustment for thead etc.
		var padding = $self.outerWidth() - $self.find('>tbody tr').eq(0).outerWidth();
		$self.find('>thead').css('padding-right', padding + 'px');
		$self.find('>tbody').scrollTop(0);
	},

	_setScrollbarStyle: function($container){
		// style thead
		var theadStyle = {'width':"auto"
			,'display':"block"
			,'padding':'0 20px 0 0'
			,'margin' : '0'
		};
		$container.find('>thead').css(theadStyle);
		
		// style tbody
		var tbodyStyle = {'width':"auto"
			,'display' : "block"
			,'padding' : '0'
			,'margin' : '0'
			,'overflow-y' : "auto"
			,'overflow-x' : "hidden"
			,'height' : (isFinite(this.options.height)) ? this.options.height + "px"	: this.options.height
			,'max-height' : (isFinite(this.options.maxHeight)) ? this.options.maxHeight + "px" : this.options.maxHeight		
		};
		$container.find('>tbody').css(tbodyStyle);
		 
	},

	_setCellsWidth: function($container){
		var widthAggregated = 0;
		var $headCells = $container.find('>thead th, >thead td');
		var $bodyCells = $container.find('>tbody tr').eq(0).children('td');
		for(var i = 0; i < $headCells.size(); i++){
			if(this.options.widthInPx === true) {
				$headCells.eq(i).css('width', $headCells.eq(i).width() + "px");
				$bodyCells.eq(i).css('width', $headCells.eq(i).width() + "px");
			} else {
				var width = Math.floor($headCells.eq(i).width()/$container.width()*100);
				if(i === $headCells.size() - 1)
				{
					width = 100 - widthAggregated;
					if(width < 0) {width = 0;}
				}
				$headCells.eq(i).css('width', width + "%");
				$bodyCells.eq(i).css('width', width + "%");
				widthAggregated += width;
			}
		}
	}
});
})(jQuery);


/*!
	query-string
	Parse and stringify URL query strings
	https://github.com/sindresorhus/query-string
	by Sindre Sorhus
	MIT License
	
	NOTE. Some modification and correction by Michal Krugly
*/
(function () {
	'use strict';
	var queryString = {};

	queryString.parse = function (str) {
		if (typeof str !== 'string') {
			return {};
		}

		str = str.trim().replace(/^\?/, '');

		if (!str) {
			return {};
		}

		return str.trim().split('&').reduce(function (ret, param) {
			let parts = param.replace(/\+/g, ' ').split('=');
			// missing `=` should be `null`:
			// http://w3.org/TR/2012/WD-url-20120524/#collect-url-parameters
			// nliteme correction: parts[0] also needs to be decoded, since it will be encoded later
			let key = decodeURIComponent(parts[0])
			let value = parts[1] === undefined ? null : decodeURIComponent(parts[1]);
			// check if key is an non-indexed bracket type variable e.g. v[]
			let result = /(\[\])$/.exec(key);
			key = key.replace(/\[\]$/, '');

			if (!result) {
			    ret[key] = value;
			} else {
			    if (ret[key] === undefined) {
			        ret[key] = [];
			    }
			    ret[key] = [].concat(ret[key], value);
			}
			return ret;
		}, {});
	};

	queryString.stringify = function (obj) {
		return obj ? Object.keys(obj).map(function (key) {
			if (Array.isArray(obj[key])) {
			    let k = encodeURIComponent(key + '[]')
			    return obj[key].map(function (value) {
			        let v = value === null ? '': '=' + encodeURIComponent(value);
			        return k + v;
			    }).join('&')
			} else {
			    return encodeURIComponent(key) + '=' + encodeURIComponent(obj[key]);
			}
		}).join('&') : '';
	};
	
	// nliteme addons
	queryString.setParam = function (paramName, paramValue, queryUrl) {
		var urlSplit = queryUrl.split('?');
		var queryArr = queryString.parse(urlSplit[1]);
		queryArr[paramName] = paramValue;
		return urlSplit[0]+'?'+queryString.stringify(queryArr);
	};
	
	queryString.unsetParam = function (paramName, queryUrl) {
		var urlSplit = queryUrl.split('?');
		var queryArr = queryString.parse(urlSplit[1]);
		delete queryArr[paramName];
		//queryArr[paramName] = '';
		return urlSplit[0]+'?'+queryString.stringify(queryArr);		
	};
	

	if (typeof module !== 'undefined' && module.exports) {
		module.exports = queryString;
	} else {
		window.queryString = queryString;
	}
})();
/*!
 * END query-string
 */ 

