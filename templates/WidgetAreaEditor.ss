<div class="WidgetAreaEditor field" id="WidgetAreaEditor-$Name" name="$Name"<% if MaxWidgets %> maxwidgets="$MaxWidgets"<% end_if %>>
	<input type="hidden" id="$Name" name="$IdxField" value="$Value" />
	<div class="availableWidgetsHolder">
		<h2><% _t('WidgetAreaEditor_ss.AVAILABLE', 'Available Widgets') %></h2>
		<p class="message"><% _t('WidgetAreaEditor_ss.AVAILWIDGETS', 'Click a widget title below to use it on this page.') %></p>
		<div class="availableWidgets" id="availableWidgets-$Name">
			<% if AvailableWidgets %>
				<% loop AvailableWidgets %>
					$DescriptionSegment
				<% end_loop %>
			<% else %>
				<div class="NoWidgets" id="NoWidgets-$Name">
					<p><% _t('WidgetAreaEditor_ss.NOAVAIL', 'There are currently no widgets available.') %></p>
				</div>
			<% end_if %>
		</div>
	</div>
	<div class="usedWidgetsHolder">
		<h2><% _t('WidgetAreaEditor_ss.INUSE', 'Widgets currently used') %></h2>
		<p class="message"><% _t('WidgetAreaEditor_ss.TOSORT', 'To sort currently used widgets on this page, drag them up and down.') %></p>
		
		<div class="usedWidgets" id="usedWidgets-$Name">
			<% if UsedWidgets %>
				<% loop UsedWidgets %>
					$EditableSegment
				<% end_loop %>
			<% else %>
				<div class="NoWidgets" id="NoWidgets-$Name"></div>
			<% end_if %>
		</div>
	</div>
</div>