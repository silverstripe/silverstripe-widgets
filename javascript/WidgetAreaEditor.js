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
				$('.cms-container').bind('submitform', function(e) {parentRef.beforeSave(e)});
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
				
				var parentRef=$(this),
					locale = $(this).closest('form').find('input[name=Locale]').val();
				
				$.ajax({
					'url': 'WidgetController/EditableSegment/' + className, 
					'success' : function(response) {parentRef.insertWidgetEditor(response)},
					'data' : {
						'locale' :  locale ,
					},
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
				$('#usedWidgets-'+$(this).attr('name')).append(widgetContent);
				
				this.rewriteWidgetAreaAttributes();
			},
			
			sortWidgets: function() {
				// Order the sort by the order the widgets are in the list
				$('#usedWidgets-'+$(this).attr('name')).children().each(function(i) {
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
