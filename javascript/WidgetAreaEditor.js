// Shortcut-function (until we update to Prototye v1.5)
if(typeof $$ != "Function") $$ = document.getElementsBySelector;

/**
 * File: WidgetAreaEditor.js
 */

/**
 * Class: WidgetAreaEditorClass
 */
WidgetAreaEditorClass = Class.create();
WidgetAreaEditorClass.prototype = {
	initialize: function() {
		this.name = this.getAttribute('name');
		this.rewriteWidgetAreaAttributes();
		UsedWidget.applyToChildren(document.getElementById('usedWidgets-'+this.name), 'div.Widget');

		var availableWidgets = document.getElementById('availableWidgets-'+this.name).childNodes;
		
		for(var i = 0; i < availableWidgets.length; i++) {
			var widget = availableWidgets[i];
			// Don't run on comments, whitespace, etc
			if (widget.nodeType == 1) {
				// Gotta change their ID's because otherwise we get clashes between two tabs
				widget.id = widget.id + '-'+this.name;
			}
		}
	
	
		// Create dummy sortable to prevent javascript errors
		Sortable.create('availableWidgets-'+this.name, {
			tag: 'li',
			handle: 'handle',
			containment: []
		});
		
		// Used widgets are sortable
		Sortable.create('usedWidgets-'+this.name, {
			tag: 'div',
			handle: 'handle',
			containment: ['availableWidgets-'+this.name, 'usedWidgets-'+this.name],
			onUpdate: this.updateWidgets
		});
		
		// Figure out maxid, this is used when creating new widgets
		this.maxid = 0;
		
		var usedWidgets = document.getElementById('usedWidgets-'+this.name).childNodes;
		for(var i = 0; i < usedWidgets.length; i++) {
			var widget = usedWidgets[i];
			if(widget.id) {
				widgetid = widget.id.match(/\Widget\[(.+?)\]\[([0-9]+)\]/i);
				if(widgetid && parseInt(widgetid[2]) > this.maxid) {
					this.maxid = parseInt(widgetid[2]);
				}
			}
		}

		// Ensure correct sort values are written when page is saved
		// TODO Adjust to new event listeners
		jQuery('.cms-edit-form').bind('ajaxsubmit', this.beforeSave.bind(this));
	},
	
	rewriteWidgetAreaAttributes: function() {
		this.name = this.getAttribute('name');

		var monkeyWith = function(widgets, name) {
			if (!widgets) {
				return;
			}
			for(var i = 0; i < widgets.length; i++) {
				widget = widgets[i];
				if (!widget.getAttribute('rewritten') && (widget.id || widget.name)) {
					if (widget.id && widget.id.indexOf('Widget[') === 0) {
						var newValue = widget.id.replace(/Widget\[/, 'Widget['+name+'][');
						//console.log('Renaming '+widget.tagName+' ID '+widget.id+' to '+newValue);
						widget.id = newValue;
					}
					if (widget.name && widget.name.indexOf('Widget[') === 0) {
						var newValue = widget.name.replace(/Widget\[/, 'Widget['+name+'][');
						//console.log('Renaming '+widget.tagName+' Name '+widget.name+' to '+newValue);
						widget.name = newValue;
					}
					widget.setAttribute('rewritten', 'yes');
				}
				else {
					//console.log('Skipping '+(widget.id ? widget.id : (widget.name ? widget.name : 'unknown '+widget.tagName)));
				}
			}
		}
		
		monkeyWith($$('#WidgetAreaEditor-'+this.name+' .Widget'), this.name);
		monkeyWith($$('#WidgetAreaEditor-'+this.name+' .Widget *'), this.name);
	},
	
	beforeSave: function() {
		// Ensure correct sort values are written when page is saved
		var usedWidgets = document.getElementById('usedWidgets-'+this.name);
		
		if(usedWidgets) {
			this.sortWidgets();
		
			var children = usedWidgets.childNodes;
		
			for( var i = 0; i < children.length; ++i ) {
				var child = children[i];
			
				if(child.beforeSave) {
					child.beforeSave();
				}
			}
		}
	},
	
	addWidget: function(className, holder) {
		
		if (document.getElementById('WidgetAreaEditor-'+holder).getAttribute('maxwidgets')) {
			var maxCount = document.getElementById('WidgetAreaEditor-'+holder).getAttribute('maxwidgets');
			var count = $$('#usedWidgets-'+holder+' .Widget').length;
			if (count+1 > maxCount) {
				alert(ss.i18n._t('WidgetAreaEditor.TOOMANY'));
				return;
			}
		}
		
		
		this.name = holder;
		jQuery.ajax({
			'url': 'Widget_Controller/EditableSegment/' + className, 
			'success' : document.getElementById('usedWidgets-'+holder).parentNode.parentNode.insertWidgetEditor.bind(this)
		});
	},

	updateWidgets: function() {
		var self = this;

		// Gotta get the name of the current dohickey based off the ID
		this.name = this.element.id.split('-').pop();

		// alert(this.name);
	
		// Gotta get the name of the current dohickey based off the ID
		this.name = this.element.id.split('-').pop();
		

		// This is called when an available widgets is dragged over to used widgets.
		// It inserts the editor form into the new used widget

		var usedWidgets = document.getElementById('usedWidgets-'+this.name).childNodes;
		for(var i = 0; i < usedWidgets.length; i++) {
			var widget = usedWidgets[i];
			if(widget.id && (widget.id.indexOf("Widget[") != 0) && (widget.id != 'NoWidgets-'+this.name)) {
				// Need to remove the -$Name part.
				var wIdArray = widget.id.split('-');
				wIdArray.pop();

				jQuery.ajax({
					'url': 'Widget_Controller/EditableSegment/' + wIdArray.join('-'),
					'success' : function() {
						document.getElementById('usedWidgets-'+self.name).parentNode.parentNode.insertWidgetEditor();
					}
				});
			}
		}
	},
	
	insertWidgetEditor: function(response) {
		// Remove placeholder text
		if(document.getElementById('NoWidgets-'+this.name)) {
			document.getElementById('usedWidgets-'+this.name).removeChild(document.getElementById('NoWidgets-'+this.name));
		}

		var usedWidgets = document.getElementById('usedWidgets-'+this.name).childNodes;
		
		// Give the widget a unique id
		widgetContent = response.replace(/Widget\[0\]/gi, "Widget[new-" + (++document.getElementById('usedWidgets-'+this.name).parentNode.parentNode.maxid) + "]");
		new Insertion.Top(document.getElementById('usedWidgets-'+this.name), widgetContent);
		
		document.getElementById('usedWidgets-'+this.name).parentNode.parentNode.rewriteWidgetAreaAttributes();
		UsedWidget.applyToChildren(document.getElementById('usedWidgets-'+this.name), 'div.Widget');
		
		// Repply some common form controls
		WidgetTreeDropdownField.applyTo('div.usedWidgets .TreeDropdownField');
		
		Sortable.create('usedWidgets-'+this.name, {
			tag: 'div',
			handle: 'handle',
			containment: ['availableWidgets-'+this.name, 'usedWidgets-'+this.name],
			onUpdate: document.getElementById('usedWidgets-'+this.name).parentNode.parentNode.updateWidgets
		});
	},
	
	sortWidgets: function() {
		// Order the sort by the order the widgets are in the list
		var usedWidgets = document.getElementById('usedWidgets-'+this.name);
		
		if(usedWidgets) {
			widgets = usedWidgets.childNodes;
			
			for(i = 0; i < widgets.length; i++) {
				var div = widgets[i];

				if(div.nodeName != '#comment') {
					var fields = div.getElementsByTagName('input');
					
					for(j = 0; field = fields.item(j); j++) {
						if(field.name == div.id + '[Sort]') {
							field.value = i;
						}
					}
				}
				
			}
		}
	},
	
	deleteWidget: function(widgetToRemove) {
		// Remove a widget from the used widgets column
		document.getElementById('usedWidgets-'+this.name).removeChild(widgetToRemove);
		// TODO ... re-create NoWidgets div?
	}
}

/**
 * Class: UsedWidget
 */
UsedWidget = Class.create();
UsedWidget.prototype = {
	initialize: function() {
		// Call deleteWidget when delete button is pushed
		this.deleteButton = this.findDescendant('span', 'widgetDelete');
		if(this.deleteButton)
			this.deleteButton.onclick = this.deleteWidget.bind(this);
	},
	
	// Taken from FieldEditor
	findDescendant: function(tag, clsName, element) {
		if(!element)
			element = this;
		
		var descendants = element.getElementsByTagName(tag);
		
		for(var i = 0; i < descendants.length; i++) {
			var el = descendants[i];
			
			if(tag.toUpperCase() == el.tagName && el.className.indexOf( clsName ) != -1)
				return el;
		}
		
		return null;
	},
	
	deleteWidget: function() {
		this.parentNode.parentNode.parentNode.deleteWidget(this);
	}
}

/**
 * Class: AvailableWidgetHeader
 */
AvailableWidgetHeader = Class.create();
AvailableWidgetHeader.prototype = {
	onclick: function(event) {
		parts = this.parentNode.id.split('-');
		var widgetArea = parts.pop();
		var className = parts.pop();
		document.getElementById('WidgetAreaEditor-'+widgetArea).addWidget(className, widgetArea);
	}
}
AvailableWidgetHeader.applyTo('div.availableWidgets .Widget h3');

/**
 * Class: WidgetTreeDropdownField
 */
WidgetTreeDropdownField = Class.extend('TreeDropdownField');
WidgetTreeDropdownField.prototype = {
	getName: function() {
		return 'Widget_TDF_Endpoint';
	}
}
WidgetTreeDropdownField.applyTo('div.usedWidgets .TreeDropdownField');
WidgetAreaEditorClass.applyTo('.WidgetAreaEditor');
=======
(function($) {
	$.entwine('ss', function($) {
		$('.WidgetAreaEditor').entwine({
			onmatch: function() {
				var parentName=$(this).attr('name');
				this.rewriteWidgetAreaAttributes();

				var availableWidgets=$('#availableWidgets-'+$(this).attr('name')).children().each(function() {
					// Don't run on comments, whitespace, etc
					if($(this)[0].nodeType==1) {
						// Gotta change their ID's because otherwise we get clashes between two tabs
						$(this)[0].id=$(this)[0].id+'-'+parentName;
					}
				});
			
				var parentRef=$(this);
				
				
				// Used widgets are sortable
				$(this).find('.usedWidgets').sortable({
					opacity: 0.6,
					handle: '.handle',
					update: function(e, ui) {parentRef.updateWidgets(e, ui)},
					placeholder: 'ui-state-highlight',
					forcePlaceholderSize: true
				});
				
				// Figure out maxid, this is used when creating new widgets
				$(this).data('maxid', 0);
				
				var usedWidgets = $(this).find('.usedWidgets .Widget');
				usedWidgets.each(function() {
					var widget = $(this)[0];
					if(widget.id) {
						widgetid = widget.id.match(/Widget\[(.+?)\]\[([0-9]+)\]/i);
						if(widgetid && parseInt(widgetid[2]) > parseInt(parentRef.data('maxid'))) {
							parentRef.data('maxid', parseInt(widgetid[2]));
						}
					}
				});
				
				
				// Ensure correct sort values are written when page is saved
				// TODO Adjust to new event listeners
				$('.cms-container').bind('submitform', this.beforeSave);
			},
			
			rewriteWidgetAreaAttributes: function() {
				var name = $(this).attr('name');

				var monkeyWith = function(widgets, name) {
					if (!widgets) {
						return;
					}
					
					widgets.each(function() {
						widget=$(this)[0];
						if (!widget.rewritten && (widget.id || widget.name)) {
							if (widget.id && widget.id.indexOf('Widget[') === 0) {
								var newValue = widget.id.replace(/Widget\[/, 'Widget['+name+'][');
								//console.log('Renaming '+widget.tagName+' ID '+widget.id+' to '+newValue);
								widget.id = newValue;
							}
							if (widget.name && widget.name.indexOf('Widget[') === 0) {
								var newValue = widget.name.replace(/Widget\[/, 'Widget['+name+'][');
								//console.log('Renaming '+widget.tagName+' Name '+widget.name+' to '+newValue);
								widget.name=newValue;
							}
							widget.rewritten='yes';
						}else {
							//console.log('Skipping '+(widget.id ? widget.id : (widget.name ? widget.name : 'unknown '+widget.tagName)));
						}
					});
				}
				
				monkeyWith($('#WidgetAreaEditor-'+name+' .Widget'), name);
				monkeyWith($('#WidgetAreaEditor-'+name+' .Widget *'), name);
			},
			
			beforeSave: function() {
				// Ensure correct sort values are written when page is saved
				var usedWidgets = $('#usedWidgets-'+$(this).attr('name'));
				if(usedWidgets) {
					this.sortWidgets();
				
					var children=usedWidgets.children();
				
					children.each(function() {
						if($(this).beforeSave) {
							$(this).beforeSave();
						}
					});
				}
			},
			
			addWidget: function(className, holder) {
				if($('#WidgetAreaEditor-'+holder).attr('maxwidgets')) {
					var maxCount = $('#WidgetAreaEditor-'+holder).attr('maxwidgets');
					var count = $('#usedWidgets-'+holder+' .Widget').length;
					if (count+1 > maxCount) {
						alert(ss.i18n._t('WidgetAreaEditor.TOOMANY'));
						return;
					}
				}
				
				var parentRef=$(this);
				$.ajax({
					'url': 'Widget_Controller/EditableSegment/' + className, 
					'success' : function(response) {parentRef.insertWidgetEditor(response)}
				});
			},
			
			updateWidgets: function(e, ui) {
				// Gotta get the name of the current dohickey based off the ID
				var name = $(this).attr('id').split('-').pop();
				
				var i=0;
				var usedWidgets = $('#usedWidgets-'+name).children().each(function() {
					var widget = $(this)[0];
					if(widget.id) {
						$(this).find('input[name='+widget.id.replace(/\]/g,'\\]').replace(/\[/g,'\\[')+'\\[Sort\\]]').val(i);
						i++;
					}
				});
			},
			
			insertWidgetEditor: function(response) {
				var usedWidgets = $('#usedWidgets-'+$(this).attr('name')).children();
				
				// Give the widget a unique id
				var newID=parseInt($(this).data('maxid'))+1;
				$(this).data('maxid', newID);
				
				var widgetContent = response.replace(/Widget\[0\]/gi, "Widget[new-" + (newID) + "]");
				$('#usedWidgets-'+$(this).attr('name')).prepend(widgetContent);
				
				this.rewriteWidgetAreaAttributes();
			},
			
			sortWidgets: function() {
				// Order the sort by the order the widgets are in the list
				$('#usedWidgets-'+$(this).attr('name')).children().each(function() {
						var div = $(this)[0];

						if(div.nodeName != '#comment') {
							var fields = div.getElementsByTagName('input');
							for(j = 0; field = fields.item(j); j++) {
								if(field.name == div.id + '[Sort]') {
									field.value = i;
								}
							}
						}
					});
			},
			
			deleteWidget: function(widgetToRemove) {
				// Remove a widget from the used widgets column
				widgetToRemove.remove();
			}
		});
		
		$('div.availableWidgets .Widget h3').entwine({
			onclick: function(event) {
				parts = $(this).parent().attr('id').split('-');
				var widgetArea = parts.pop();
				var className = parts.pop();
				$('#WidgetAreaEditor-'+widgetArea).addWidget(className, widgetArea);
			}
		});
		
		/**
		 * Disable chosen
		 */
		$('div.usedWidgets .field.dropdown select, div.usedWidgets .field select[multiple]').entwine({
			onmatch: function() {
				$(this).addClass('no-chzn');
				
				this._super();
			}
		});
		
		$('div.usedWidgets div.Widget').entwine({
			onmatch: function() {
				// Call deleteWidget when delete button is pushed
				$(this).find('span.widgetDelete').click(function() {
																$(this).closest('.WidgetAreaEditor').deleteWidget($(this).parent().parent());
															});
			}
		});
	})
})(jQuery);