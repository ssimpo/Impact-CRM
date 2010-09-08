dojo.provide("impact.scroller.scroller");

//Generic requires
dojo.require("dijit._Widget");
dojo.require("dijit._Templated");

//More requires, needed for this widget
dojo.require("dojo.fx");

//Localisation
dojo.requireLocalization("dijit", "loading");

dojo.declare (
	"impact.scroller.scroller",
	[dijit._Widget,dijit._Templated],
	{
		loadingMessage: '<span class="dijitContentPaneLoading">${loadingState}</span>',
		errorMessage: '<span class="dijitContentPaneError">${errorState}</span>',
		templateString: '<div dojoAttachPoint="container"></div>',
		src:'',
		period:0,
		duration:0,
		slidewidth:0,
		slideheight:0,
		showslides:0,
		width:0,
		height:0,
		gap:5,
		scroller:'',
		_slides:[],
		_current_scroll:{},
		_current_side_no:0,
		_slide_html:[],
		
		postMixInProperties: function() {
			this.period = parseInt(this.period);
			this.duration = parseInt(this.duration);
			
			this.slideheight = parseInt(this.slideheight);
			this.slidewidth = parseInt(this.slidewidth);
			this.showslides = parseInt(this.showslides);
			this.gap = parseInt(this.gap);
			this.width = ((this.slidewidth*this.showslides)+(this.gap*this.showslides));
			this.height = this.slideheight;
			
			var messages = dojo.i18n.getLocalization("dijit", "loading", this.lang);
			this.loadingMessage = dojo.string.substitute(this.loadingMessage, messages);
			this.errorMessage = dojo.string.substitute(this.errorMessage, messages);
			
			this.inherited(arguments);
		},
		buildRendering: function() {
			this.inherited(arguments);
			this.container.setAttribute('class','dojoxrcbcscroller');
			
			dojo.style(this.container,{
				'height':this.height+'px',
				'width':this.width+'px',
				'position':'relative',
				'overflow':'hidden',
				'innerHTML':this.loadingMessage
			});
			
			dojo.xhrGet({
				"url": 'scripts/impact/scroller/controller.php',
				content:{scroller:this.scroller},
				handleAs: "json",
				preventCache: true,
				load: dojo.hitch(this,this._feedLoaded)
			});
		},
		_feedLoaded: function(data) {
			dojo.forEach(data.items,function(item,index) {
				
				var div = dojo.create('div',{'class':'dojoxrcbcscrollerslide'},this.domNode,'last');
				
				dojo.style(div,{
					'position':'absolute',
					'width':this.slidewidth+'px',
					'height':this.slideheight+'px',
					'left':((this.slidewidth*index)+((index+1)*5))+'px',
					'top':'0px'
				});
				dojo.place(unescape(item),div,'last');
				
				this._slides[index] = div;
				this._current_side_no = index;
				
			},this);
			
			this._current_scroll = setInterval(dojo.hitch(this, "_slide"),this.period);
		},
		_slide: function () {
			clearInterval(this._current_scroll);
			
			dojo.style(this._slides[this._current_side_no],{
				left:'-'+this.slidewidth+'px'
			});
			this._current_side_no--;
			if (this._current_side_no < 0) { this._current_side_no = (this._slides.length-1); }

			var animation = new Array();
			dojo.forEach(this._slides,function(slide,index) {
				animation[index] = dojo.animateProperty({
					node: slide,
					duration: this.duration,
					properties: {
						left: {
							start: dojo.coords(slide).l,
							end: dojo.coords(slide).w+dojo.coords(slide).l
						}
					}
				});
					

			},this);
			
			ani = dojo.fx.combine(animation);
			dojo.connect(ani,"onEnd",this,function () {
				this._current_scroll = setInterval(dojo.hitch(this, "_slide"),this.period);
			});
			ani.play();
		}
		
		
	}
);