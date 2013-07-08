<div class="$ClassName Widget" id="$Name">
	<h3 class="handle">$CMSTitle</h3>
	<div class="widgetDescription">
		<p>$Description</p>
	</div>
	<% if $CMSEditor %>
	<div class="widgetFields">
		$CMSEditor
	</div>
	<% end_if %>
	<input type="hidden" name="$Name[Type]" value="$ClassName" />
	<input type="hidden" name="$Name[Sort]" value="$Sort" />
	<p class="deleteWidget"><span class="widgetDelete ss-ui-button"><% _t('WidgetEditor_ss.DELETE', 'Delete') %></span></p>
</div>