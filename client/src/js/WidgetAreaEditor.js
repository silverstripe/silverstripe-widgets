/* global jQuery, tinyMCE, ss */
(function ($) {
  // eslint-disable-next-line no-shadow
  $.entwine('ss', ($) => {
    $('.WidgetAreaEditor').entwine({
      onmatch() {
        const parentName = $(this).attr('name');
        this.rewriteWidgetAreaAttributes();

        const availableWidgets = $(`#availableWidgets-${parentName}`).children().each(function () {
          // Don't run on comments, whitespace, etc
          if ($(this)[0].nodeType === 1) {
            // Gotta change their ID's because otherwise we get clashes between two tabs
            $(this)[0].id = `${$(this)[0].id}-${parentName}`;
          }
        });

        const parentRef = $(this);


        // Used widgets are sortable
        $(this).find('.usedWidgets').sortable({
          opacity: 0.6,
          handle: '.handle',
          update(e, ui) {
            parentRef.updateWidgets(e, ui);
          },
          placeholder: 'ui-state-highlight',
          forcePlaceholderSize: true,
          start(e, ui) {
            const htmleditors = $(ui.item).closest('.Widget').find('textarea.htmleditor');
            $.each(htmleditors, (k, i) => {
              tinyMCE.execCommand('mceRemoveControl', false, $(i).attr('id'));
            });
          },
          stop(e, ui) {
            const htmleditors = $(ui.item).closest('.Widget').find('textarea.htmleditor');
            $.each(htmleditors, (k, i) => {
              tinyMCE.execCommand('mceAddControl', true, $(i).attr('id'));
            });
          }
        });

        // Ensure correct sort values are written when page is saved
        // TODO Adjust to new event listeners
        $('.cms-container').bind('submitform', (e) => { parentRef.beforeSave(e); });
      },

      rewriteWidgetAreaAttributes() {
        const name = $(this).attr('name');

        const monkeyWith = function (widgets, widgetName) {
          if (!widgets) {
            return;
          }

          widgets.each(function () {
            const widget = $(this)[0];
            if (!widget.rewritten && (widget.id || widget.name)) {
              if (widget.id && widget.id.indexOf('Widget[') === 0) {
                widget.id = widget.id.replace(/Widget\[/, `Widget[${widgetName}][`);
              }
              if (widget.name && widget.name.indexOf('Widget[') === 0) {
                widget.name = widget.name.replace(/Widget\[/, `Widget[${widgetName}][`);
              }
              widget.rewritten = 'yes';
            }
          });
        };

        monkeyWith($(`#WidgetAreaEditor-${name} .Widget`), name);
        monkeyWith($(`#WidgetAreaEditor-${name} .Widget *`), name);
      },

      beforeSave() {
        // Ensure correct sort values are written when page is saved
        const usedWidgets = $(`#usedWidgets-${$(this).attr('name')}`);
        if (usedWidgets) {
          this.sortWidgets();

          const children = usedWidgets.children();

          children.each(function () {
            if ($(this).beforeSave) {
              $(this).beforeSave();
            }
          });
        }
      },

      addWidget(className, holder) {
        if ($(`#WidgetAreaEditor-${holder}`).attr('maxwidgets')) {
          const maxCount = $(`#WidgetAreaEditor-${holder}`).attr('maxwidgets');
          const count = $(`#usedWidgets-${holder} .Widget`).length;
          if (count + 1 > maxCount) {
            alert(ss.i18n._t('WidgetAreaEditor.TOOMANY'));
            return;
          }
        }

        const parentRef = $(this);
          const locale = $(this).closest('form').find('input[name=Locale]').val();

        $.ajax({
          url: `WidgetController/EditableSegment/${className}`,
          success(response) { parentRef.insertWidgetEditor(response); },
          data: {
            locale,
          },
        });
      },

      updateWidgets(e, ui) {
        // Gotta get the name of the current dohickey based off the ID
        const name = $(this).attr('id').split('-').pop();

        let i = 0;
        const usedWidgets = $(`#usedWidgets-${name}`).children().each(function () {
          const widget = $(this)[0];
          if (widget.id) {
            $(this).find(`input[name=${widget.id.replace(/\]/g, '\\]').replace(/\[/g, '\\[')}\\[Sort\\]]`).val(i);
            i += 1;
          }
        });
      },

      insertWidgetEditor(response) {
        const newID = $(response).find('.formid').val();
        const widgetContent = response.replace(/Widget\[0\]/gi, `Widget[${newID}]`);
        $(`#usedWidgets-${$(this).attr('name')}`).append(widgetContent);

        this.rewriteWidgetAreaAttributes();
      },

      sortWidgets() {
        // Order the sort by the order the widgets are in the list
        $(`#usedWidgets-${$(this).attr('name')}`).children().each(function (i) {
            const div = $(this)[0];

            if (div.nodeName !== '#comment') {
              const fields = div.getElementsByTagName('input');
              let field;
              // eslint-disable-next-line no-cond-assign
              for (let j = 0; field = fields.item(j); j++) {
                if (field.name === `${div.id}[Sort]`) {
                  field.value = i;
                }
              }
            }
          });
      },

      deleteWidget(widgetToRemove) {
        // Remove a widget from the used widgets column
        widgetToRemove.remove();
      }
    });

    $('div.availableWidgets .Widget h3').entwine({
      onclick(event) {
        const parts = $(this).parent().attr('id').split('-');
        const widgetArea = parts.pop();
        const className = parts.pop();
        $(`#WidgetAreaEditor-${widgetArea}`).addWidget(className, widgetArea);
      }
    });

    $('div.usedWidgets div.Widget').entwine({
      onmatch() {
        // Call deleteWidget when delete button is pushed
        $(this).find('span.widgetDelete').click(function () {
          $(this).closest('.WidgetAreaEditor').deleteWidget($(this).parent().parent());
        });
      }
    });
  });
}(jQuery));
