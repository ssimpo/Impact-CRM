dojo.provide("impact.checkboxArray");

dojo.require("dijit.form.CheckBox");

dojo.declare(
	"impact.checkboxArray",
	[dijit._Widget,dijit._Templated],
	{
		templateString:'<table summary="Table to display checkboxes in an array" dojoAttachPoint="table" class="dojoxTCCcheckboxArray"></table>',
		
		cols:0,
		name:'',
		values:[],
		labels:[],
		selected:[],
		
		postMixInProperties: function () {
			this.inherited(arguments);
		},
		startup: function () {
			this.inherited(arguments);
			
			var tr = '';
			dojo.forEach (this.values, function (item,index) {
				if ((index % this.cols) == 0) {tr = dojo.create('tr',{},this.table,'last');}
				var id = this._rndID(this.name+'_'+index.toString());
				var td =  dojo.create('td',{innerHTML:'&nbsp;'},tr,'last');
				var checkBox = new dijit.form.CheckBox({
					'name':this.name,'value':item,'id':id
				});
				
				if (dojo.indexOf(this.selected,item) != -1) { checkBox.attr('checked',true); }
				
				dojo.place(checkBox.domNode,td,'first');
				var label = dojo.create('label',{'innerHTML':this.labels[index],'for':id},td,'last');
			},this);
			var blanks = (this.values.length % this.cols);
			
			for (var i = 0; i <= blanks; i++) {//blank cells when no. checkboxs and rows*cols is not equal
				dojo.create('td',{innerHTML:'&nbsp;'},tr,'last');
			}
			
		},
		_rndID: function (id) {
			id += '_';
			for (var i = 1; i < 10; i++) {id += String.fromCharCode(this._rndInt(65,90));}
			return id;
		},
		_rndInt: function (min, max) {
			return Math.floor(Math.random() * (max - min + 1)) + min;
		}  
	}
);