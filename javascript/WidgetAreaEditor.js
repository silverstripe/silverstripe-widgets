(function($) {
	$.entwine('ss', function($) {
        $('.WidgetAreaEditor').entwine({
            onmatch: function() {
                this.name = $(this).attr('name');
                /*this.rewriteWidgetAreaAttributes();
                UsedWidget.applyToChildren(document.getElementById('usedWidgets-'+this.name), 'div.Widget');

                var availableWidgets = document.getElementById('availableWidgets-'+this.name).childNodes;
                
                for(var i = 0; i < availableWidgets.length; i++) {
                    var widget = availableWidgets[i];
                    // Don't run on comments, whitespace, etc
                    if (widget.nodeType == 1) {
                        // Gotta change their ID's because otherwise we get clashes between two tabs
                        widget.id = widget.id + '-'+this.name;
                    }
                }*/
            
            
                // Create dummy sortable to prevent javascript errors
                $(this).find('.availableWidgets').sortable({
                    opacity: 0.6,
                    handle: '.handle'
                });
                
                // Used widgets are sortable
                $(this).find('.usedWidgets').sortable({
                    opacity: 0.6,
                    handle: '.handle',
                    //update: this.updateWidgets,
                    connectWith: $(this).find('.availableWidgets')
                });
                
                // Figure out maxid, this is used when creating new widgets
                this.maxid = 0;
                
                var usedWidgets = $(this).find('.usedWidgets .Widget');
                for(var i = 0; i < usedWidgets.length; i++) {
                    var widget = usedWidgets.get(i);
                    if(widget.id) {
                        widgetid = widget.id.match(/Widget\[([0-9]+)\]/i);
                        if(widgetid && parseInt(widgetid[1]) > this.maxid) {
                            this.maxid = parseInt(widgetid[1]);
                        }
                    }
                }
                
                
                // Ensure correct sort values are written when page is saved
                // TODO Adjust to new event listeners
                //jQuery('.cms-edit-form').bind('ajaxsubmit', this.beforeSave.bind(this));*/
            }/*,
            
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
            }*/
            
            
        
            /*$('div.availableWidgets .Widget h3').entwine({
                onclick: function(event) {
                    parts = this.parentNode.id.split('-');
                    var widgetArea = parts.pop();
                    var className = parts.pop();
                    document.getElementById('WidgetAreaEditor-'+widgetArea).addWidget(className, widgetArea);
                }
            });
            
            $('div.usedWidgets .TreeDropdownField').entwine({
                getName: function() {
                    return 'Widget_TDF_Endpoint';
                }
            });
            
            /*$('div.usedWidgets div.Widget').entwine({
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
            });*/
        });
    })
})(jQuery);