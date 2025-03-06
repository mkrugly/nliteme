
(function($) {
$.widget( "ui.scrollabletable", {

	options: {
		height: 'auto',
		maxHeight: 300,
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
});
})(jQuery);
