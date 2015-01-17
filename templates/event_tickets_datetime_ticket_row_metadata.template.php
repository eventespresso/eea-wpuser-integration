<div style="clear:both"></div>
<h5 class="tickets-heading"><?php _e('Ticket Meta', 'event_espresso'); ?></h5><?php echo $ticket_meta_help_link; ?>
<div class="inside">
    <ul id="dynamicMetaInput">
        <li>
            <label for="emeta[]">Key: </label>
            <input type="text" id="TKT_WPU_meta_key_1" name="TKT_WPU_meta_key[]" value="" size="20">
            <label for="emetad[]">Value:  </label>
            <input type="text" id="TKT_WPU_meta_value_1" name="TKT_WPU_meta_value[]" value="" size="20">
            <img alt="Remove Meta" src="http://desktop/copyOfSidneyAtEetestingDotInfo/EE3-master/wp-content/plugins/event-espresso/images/icons/remove.gif" onclick="this.parentNode.parentNode.removeChild(this.parentNode);" title="Remove this meta box" class="remove-item">
        </li>
    </ul>

    <p><input type="button" onclick="addMetaInput('dynamicMetaInput');" value="Add A Meta Box" class="button"></p>

    <script type="text/javascript">
		//Dynamic form fields
		function addMetaInput(divName) {
			jQuery("#" + divName).append("<li><label>Key: </label><input size='20' type='text' value='' name='TKT_WPU_meta_key[]' ><label> Value: </label><input size='20' type='text' value='' name='TKT_WPU_meta_value[]' > <img class=\"remove-item\" title=\"Remove this meta box\" onclick=\"this.parentNode.parentNode.removeChild(this.parentNode);\" src=\"http://desktop/copyOfSidneyAtEetestingDotInfo/EE3-master/wp-content/plugins/event-espresso/images/icons/remove.gif\" alt=\"Remove Meta\" /></li>");
			counter++;
		}
    </script>
</div>