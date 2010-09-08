dojo.provide("impact.ACLDialog");

dojo.require("dijit.form.Button");
dojo.require("dojox.TCC.checkboxArray");
dojo.require("dijit.Dialog");

dojo.declare(
	"impact.ACLDialog",
	[dijit._Widget,dijit._Templated,dijit.form.Button],
	{
		editors:[],
		readers:[],
		roles:[],
		_dialog:{},
		
		postMixInProperties: function () {
			this.inherited(arguments);
		},
		startup: function () {
			this.inherited(arguments);
			
			this._dialog = new dijit.Dialog({
				title: "Access Control List",
				content: "",
				style: "width: 350px"
			});
			
			var content = this._dialog.containerNode;
			var readers = new dojox.TCC.checkboxArray({
				'cols':3,'name':'readers','values':this.roles,'labels':this.roles,'selected':this.readers
			});
			readers.startup();
			var editors = new dojox.TCC.checkboxArray({
				'cols':3,'name':'editors','values':this.roles,'labels':this.roles,'selected':this.editors
			});
			editors.startup();
			
			dojo.create('h2',{innerHTML:'Readers:'},content,'last');
			dojo.place(readers.domNode,content,'last');
			dojo.create('br',{},content,'last');
			dojo.create('h2',{innerHTML:'Editors:'},content,'last');
			dojo.place(editors.domNode,content,'last');
			dojo.create('br',{},content,'last');
			
			var saveBtn = new dijit.form.Button({'label':'save'});
			dojo.connect(saveBtn.domNode,'onclick',this,function () {
				this._dialog.hide();
			});
			dojo.place(saveBtn.domNode,content,'last');
		},
		onClick: function() {
			this._dialog.show();
		}
	}
);