var post_table,page_table,media_table,theme_table,plugin_table,user_table,custom_post_table, blox_table, tables_table, menu_table;

jQuery.fn.dataTableExt.oApi.fnGetFilteredNodes = function ( oSettings ){
    var anRows = [];
    for ( var i=0, iLen=oSettings.aiDisplay.length ; i<iLen ; i++ ){
            var nRow = oSettings.aoData[ oSettings.aiDisplay[i] ].nTr;
            anRows.push( nRow );
    }
    return anRows;
};

var oCache = {
	iCacheLower: -1
};

function fnSetKey( aoData, sKey, mValue ){
	for ( var i=0, iLen=aoData.length ; i<iLen ; i++ ){
		if ( aoData[i].name == sKey ){
			aoData[i].value = mValue;
		}
	}
}

function fnGetKey( aoData, sKey ){
	for ( var i=0, iLen=aoData.length ; i<iLen ; i++ ){
		if ( aoData[i].name == sKey ){
			return aoData[i].value;
		}
	}
	return null;
}

function fnDataTablesPipeline ( sSource, aoData, fnCallback ) {
	var iPipe = 5; /* Ajust the pipe size */
	
	var bNeedServer = false;
	var sEcho = fnGetKey(aoData, "sEcho");
	var iRequestStart = fnGetKey(aoData, "iDisplayStart");
	var iRequestLength = fnGetKey(aoData, "iDisplayLength");
	var iRequestEnd = iRequestStart + iRequestLength;
	oCache.iDisplayStart = iRequestStart;
	
	/* outside pipeline? */
	if ( oCache.iCacheLower < 0 || iRequestStart < oCache.iCacheLower || iRequestEnd > oCache.iCacheUpper ){
		bNeedServer = true;
	}
	
	/* sorting etc changed? */
	if ( oCache.lastRequest && !bNeedServer ){
		for( var i=0, iLen=aoData.length ; i<iLen ; i++ ){
			if ( aoData[i].name != "iDisplayStart" && aoData[i].name != "iDisplayLength" && aoData[i].name != "sEcho" ){
				if ( aoData[i].value != oCache.lastRequest[i].value ){
					bNeedServer = true;
					break;
				}
			}
		}
	}
	
	/* Store the request for checking next time around */
	oCache.lastRequest = aoData.slice();
	
	if ( bNeedServer )
	{
		if ( iRequestStart < oCache.iCacheLower )
		{
			iRequestStart = iRequestStart - (iRequestLength*(iPipe-1));
			if ( iRequestStart < 0 )
			{
				iRequestStart = 0;
			}
		}
		
		oCache.iCacheLower = iRequestStart;
		oCache.iCacheUpper = iRequestStart + (iRequestLength * iPipe);
		oCache.iDisplayLength = fnGetKey( aoData, "iDisplayLength" );
		fnSetKey( aoData, "iDisplayStart", iRequestStart );
		fnSetKey( aoData, "iDisplayLength", iRequestLength*iPipe );
		
		jQuery.getJSON( sSource, aoData, function (json) { 
			/* Callback processing */
			oCache.lastJson = jQuery.extend(true, {}, json);
			
			if ( oCache.iCacheLower != oCache.iDisplayStart ){
				json.aaData.splice( 0, oCache.iDisplayStart-oCache.iCacheLower );
			}
			json.aaData.splice( oCache.iDisplayLength, json.aaData.length );
			
			fnCallback(json)
		} );
	}
	else
	{
		json = jQuery.extend(true, {}, oCache.lastJson);
		json.sEcho = sEcho; /* Update the echo for each response */
		json.aaData.splice( 0, iRequestStart-oCache.iCacheLower );
		json.aaData.splice( iRequestLength, json.aaData.length );
		fnCallback(json);
		return;
	}
}

function remove_setting(obj){
	if(jQuery(obj).parent().find(".activate_setting").attr("value")=="Deactivate")	{
		jQuery(".activate_setting").attr("value","Activate");
		jQuery(".activate_setting").css("display","inline");
		jQuery("#activated_index").val("");
	}
	jQuery(obj).parent().remove();
	
	var actives=jQuery(".activate_setting");
	for(i=0;i<actives.length;i++)
		if(jQuery(actives[i]).attr("value")=="Deactivate") {jQuery("#activated_index").val(i); break;}
			
	var accords=jQuery(".s_as");
	
	if(accords.length==1)
		jQuery(accords).parent().find(".remove_setting").css("display","none");
}

function activate_setting(obj){
	if(jQuery(obj).attr("value")=="Activate") 	{
		jQuery(".activate_setting").css("display","none");
		jQuery(obj).attr("value","Deactivate");
		jQuery(obj).css("display","inline");
		var actives=jQuery(".activate_setting");
		for(i=0;i<actives.length;i++)
			if(actives[i]==obj) {jQuery("#activated_index").val(i); break;}
	}
	else	{
		jQuery(".activate_setting").css("display","inline");
		jQuery(obj).attr("value","Activate");
		jQuery("#activated_index").val("");
	}
}

function row_click(obj,type)
{
	var val=jQuery(obj).val();
	if(jQuery(obj).attr("checked")=="checked")	{
		jQuery("#selected_"+type).val(jQuery("#selected_"+type).val()+"["+val+"]");
		jQuery("#no_"+type).html(parseInt(jQuery("#no_"+type).html())+1);
	}
	else	{
		var preVal=jQuery("#selected_"+type).val();
		preVal=preVal.replace("["+val+"]", "");
		jQuery("#selected_"+type).val(preVal);
		jQuery("#no_"+type).html(parseInt(jQuery("#no_"+type).html())-1);
	}
	
	var total=jQuery("."+type+"_check").length;
	var selected=jQuery("."+type+"_check:checked").length;
	if(total==selected)
		jQuery("#checkall_"+type).attr("checked","checked");
	else
		jQuery("#checkall_"+type).removeAttr("checked");
}

jQuery(document).ready(function(){
	var post_check_all = false;
	post_table = jQuery('#post_table').dataTable({
		"bAutoWidth": false,
		"bProcessing": true,
		"bServerSide": true,
		"sPaginationType": "full_numbers",
		"sAjaxSource": ajaxurl+"?action=datatable_post",
		"sServerMethod": "POST",
		"aoColumnDefs": [
          { 
		  	'bSortable': false, 
			'aTargets': [ 0,2,3,4 ]
		  }
       	],
		"fnDrawCallback":function(oSettings) {
			var row_array=jQuery(this).find(".post_check");			
			for(i=0;i<row_array.length;i++)
			{
				var rowID=jQuery(row_array[i]).val();
				var selectedList=jQuery("#selected_post").val();
				if(selectedList.indexOf("["+rowID+"]")>=0)
					jQuery(row_array[i]).attr("checked","checked");
			}
			
			var total = jQuery('input', post_table.fnGetNodes()).length;
			var selected = jQuery('input:checked', post_table.fnGetNodes()).length;
			
			if(total==selected)
				jQuery("#checkall_post").attr("checked","checked");
			else
				jQuery("#checkall_post").removeAttr("checked");
		}
	});
	
	page_table = jQuery('#page_table').dataTable({
		"bAutoWidth": false,
		"bProcessing": true,
		"bServerSide": true,
		"sPaginationType": "full_numbers",
		"sAjaxSource": ajaxurl+"?action=datatable_page",
		"sServerMethod": "POST",
		"aoColumnDefs": [
          { 
		  	'bSortable': false, 
			'aTargets': [ 0,2,3 ]
		  }
       	],
		"fnDrawCallback":function(oSettings) {
			var row_array=jQuery(this).find(".page_check");			
			for(i=0;i<row_array.length;i++)
			{
				var rowID=jQuery(row_array[i]).val();
				var selectedList=jQuery("#selected_page").val();
				if(selectedList.indexOf("["+rowID+"]")>=0)
					jQuery(row_array[i]).attr("checked","checked");
			}
			
			var total = jQuery('input', page_table.fnGetNodes()).length;
			var selected = jQuery('input:checked', page_table.fnGetNodes()).length;
			
			if(total==selected)
				jQuery("#checkall_page").attr("checked","checked");
			else
				jQuery("#checkall_page").removeAttr("checked");
		}
	});
	
	media_table = jQuery('#media_table').dataTable({
		"bAutoWidth": false,
		"bProcessing": true,
		"bServerSide": true,
		"sPaginationType": "full_numbers",
		"sAjaxSource": ajaxurl+"?action=datatable_media",
		"sServerMethod": "POST",
		"aoColumnDefs": [
          { 
		  	'bSortable': false, 
			'aTargets': [ 0,1,3,4 ]
		  }
       	],
		"fnDrawCallback":function(oSettings) {
			var row_array=jQuery(this).find(".media_check");			
			for(i=0;i<row_array.length;i++)
			{
				var rowID=jQuery(row_array[i]).val();
				var selectedList=jQuery("#selected_media").val();
				if(selectedList.indexOf("["+rowID+"]")>=0)
					jQuery(row_array[i]).attr("checked","checked");
			}
			
			var total = jQuery('input', media_table.fnGetNodes()).length;
			var selected = jQuery('input:checked', media_table.fnGetNodes()).length;
			
			if(total==selected)
				jQuery("#checkall_media").attr("checked","checked");
			else
				jQuery("#checkall_media").removeAttr("checked");
		}
	});
	theme_table = jQuery('#theme_table').dataTable({
		"sPaginationType": "full_numbers",
		"bPaginate": true,
		"aoColumnDefs": [
          { 'bSortable': false, 'aTargets': [ 0 ] }
       	],
		"fnDrawCallback":function(oSettings) {
			var row_array=jQuery(this).find(".theme_check");			
			for(i=0;i<row_array.length;i++)
			{
				var rowID=jQuery(row_array[i]).val();
				var selectedList=jQuery("#selected_theme").val();
				if(selectedList.indexOf("["+rowID+"]")>=0)
					jQuery(row_array[i]).attr("checked","checked");
			}
			
			var total = jQuery('input', this.fnGetNodes()).length;
			var selected = jQuery('input:checked', this.fnGetNodes()).length;
			
			if(total==selected)
				jQuery("#checkall_theme").attr("checked","checked");
			else
				jQuery("#checkall_theme").removeAttr("checked");
		}
	});
	plugin_table = jQuery('#plugin_table').dataTable({
		"sPaginationType": "full_numbers",
		"bPaginate": true,
		"aoColumnDefs": [
          { 'bSortable': false, 'aTargets': [ 0 ] }
       	],
		"fnDrawCallback":function(oSettings) {
			var row_array=jQuery(this).find(".plugin_check");			
			for(i=0;i<row_array.length;i++)
			{
				var rowID=jQuery(row_array[i]).val();
				var selectedList=jQuery("#selected_plugin").val();
				if(selectedList.indexOf("["+rowID+"]")>=0)
					jQuery(row_array[i]).attr("checked","checked");
			}
			
			var total = jQuery('input', this.fnGetNodes()).length;
			var selected = jQuery('input:checked', this.fnGetNodes()).length;
			
			if(total==selected)
				jQuery("#checkall_plugin").attr("checked","checked");
			else
				jQuery("#checkall_plugin").removeAttr("checked");
		}
	});
	user_table = jQuery('#user_table').dataTable({
		"bAutoWidth": false,
		"bProcessing": true,
		"bServerSide": true,
		"sPaginationType": "full_numbers",
		"sAjaxSource": ajaxurl+"?action=datatable_user",
		"sServerMethod": "POST",
		"aoColumnDefs": [
          { 
		  	'bSortable': false, 
			'aTargets': [ 0,3,4 ]
		  }
       	],
		"fnDrawCallback":function(oSettings) {
			var row_array=jQuery(this).find(".user_check");			
			for(i=0;i<row_array.length;i++)
			{
				var rowID=jQuery(row_array[i]).val();
				var selectedList=jQuery("#selected_user").val();
				if(selectedList.indexOf("["+rowID+"]")>=0)
					jQuery(row_array[i]).attr("checked","checked");
			}
			
			var total = jQuery('input', user_table.fnGetNodes()).length;
			var selected = jQuery('input:checked', user_table.fnGetNodes()).length;
			
			if(total==selected)
				jQuery("#checkall_user").attr("checked","checked");
			else
				jQuery("#checkall_user").removeAttr("checked");
		}
	});
	
	custom_post_table = jQuery('#custom_post_table').dataTable({
		"bAutoWidth": false,
		"bProcessing": true,
		"bServerSide": true,
		"sPaginationType": "full_numbers",
		"sAjaxSource": ajaxurl+"?action=datatable_custom_post",
		"sServerMethod": "POST",
		"aoColumnDefs": [
          { 
		  	'bSortable': false, 
			'aTargets': [ 0,2,3,4,5 ]
		  }
       	],
		"fnDrawCallback":function(oSettings) {
			var row_array=jQuery(this).find(".custom_post_check");			
			for(i=0;i<row_array.length;i++)
			{
				var rowID=jQuery(row_array[i]).val();
				var selectedList=jQuery("#selected_custom_post").val();
				if(selectedList.indexOf("["+rowID+"]")>=0)
					jQuery(row_array[i]).attr("checked","checked");
			}
			
			var total = jQuery('input', custom_post_table.fnGetNodes()).length;
			var selected = jQuery('input:checked', custom_post_table.fnGetNodes()).length;
			
			if(total==selected)
				jQuery("#checkall_custom_post").attr("checked","checked");
			else
				jQuery("#checkall_custom_post").removeAttr("checked");
		}
	});

	menu_table = jQuery('#menu_table').dataTable({
		"bAutoWidth": false,
		"bProcessing": true,
		"bServerSide": true,
		"sPaginationType": "full_numbers",
		"sAjaxSource": ajaxurl+"?action=datatable_menu",
		"sServerMethod": "POST",
		"aoColumnDefs": [
          { 
		  	'bSortable': false, 
			'aTargets': [ 0,3]
		  }
       	],
		"fnDrawCallback":function(oSettings) {
			var row_array=jQuery(this).find(".menu_check");			
			for(i=0;i<row_array.length;i++)
			{
				var rowID=jQuery(row_array[i]).val();
				var selectedList=jQuery("#selected_menu").val();
				if(selectedList.indexOf("["+rowID+"]")>=0)
					jQuery(row_array[i]).attr("checked","checked");
			}
			
			var total = jQuery('input', menu_table.fnGetNodes()).length;
			var selected = jQuery('input:checked', menu_table.fnGetNodes()).length;
			
			if(total==selected)
				jQuery("#checkall_menu").attr("checked","checked");
			else
				jQuery("#checkall_menu").removeAttr("checked");
		}
	});
	
	jQuery("#checkall_post").click(function(){
		if (jQuery("#checkall_post").attr("checked") == "checked")
		{
			var total_no=jQuery('input', post_table.fnGetFilteredNodes()).length;
			var selected_no=jQuery('input:checked', post_table.fnGetFilteredNodes()).length;
			jQuery("#no_post").html(parseInt(jQuery("#no_post").html())+(total_no-selected_no));
			
			jQuery('input', post_table.fnGetFilteredNodes()).attr("checked","checked");
			
			var row_array=jQuery('input', post_table.fnGetFilteredNodes());
						
			for(i=0;i<row_array.length;i++)
			{
				var rowID=jQuery(row_array[i]).val();
				var selectedList=jQuery("#selected_post").val();
				if(selectedList.indexOf("["+rowID+"]")<0)
					jQuery("#selected_post").val(selectedList+"["+rowID+"]");
			}
		}
		else
		{
			var selected_no=jQuery('input:checked', post_table.fnGetFilteredNodes()).length;
			jQuery("#no_post").html(parseInt(jQuery("#no_post").html())-selected_no);
			
			jQuery('input', post_table.fnGetFilteredNodes()).removeAttr("checked");
			
			var row_array=jQuery('input', post_table.fnGetFilteredNodes());
						
			for(i=0;i<row_array.length;i++)
			{
				var rowID=jQuery(row_array[i]).val();
				var selectedList=jQuery("#selected_post").val();
				if(selectedList.indexOf("["+rowID+"]")>=0)
					jQuery("#selected_post").val(selectedList.replace("["+rowID+"]",""));
			}
		}
	})
	
	jQuery("#checkall_page").click(function(){
		if (jQuery("#checkall_page").attr("checked") == "checked")
		{
			var total_no=jQuery('input', page_table.fnGetFilteredNodes()).length;
			var selected_no=jQuery('input:checked', page_table.fnGetFilteredNodes()).length;
			jQuery("#no_page").html(parseInt(jQuery("#no_page").html())+(total_no-selected_no));
			
			jQuery('input', page_table.fnGetFilteredNodes()).attr("checked","checked");
			
			var row_array=jQuery('input', page_table.fnGetFilteredNodes());
						
			for(i=0;i<row_array.length;i++)
			{
				var rowID=jQuery(row_array[i]).val();
				var selectedList=jQuery("#selected_page").val();
				if(selectedList.indexOf("["+rowID+"]")<0)
					jQuery("#selected_page").val(selectedList+"["+rowID+"]");
			}
		}
		else
		{
			var selected_no=jQuery('input:checked', page_table.fnGetFilteredNodes()).length;
			jQuery("#no_page").html(parseInt(jQuery("#no_page").html())-selected_no);
			
			jQuery('input', page_table.fnGetFilteredNodes()).removeAttr("checked");
			
			var row_array=jQuery('input', page_table.fnGetFilteredNodes());
						
			for(i=0;i<row_array.length;i++)
			{
				var rowID=jQuery(row_array[i]).val();
				var selectedList=jQuery("#selected_page").val();
				if(selectedList.indexOf("["+rowID+"]")>=0)
					jQuery("#selected_page").val(selectedList.replace("["+rowID+"]",""));
			}
		}
	})
	
	jQuery("#checkall_link").click(function(){
		if (jQuery("#checkall_link").attr("checked") == "checked")
		{
			var total_no=jQuery('input', link_table.fnGetFilteredNodes()).length;
			var selected_no=jQuery('input:checked', link_table.fnGetFilteredNodes()).length;
			jQuery("#no_link").html(parseInt(jQuery("#no_link").html())+(total_no-selected_no));
			
			jQuery('input', link_table.fnGetFilteredNodes()).attr("checked","checked");
			
			var row_array=jQuery('input', link_table.fnGetFilteredNodes());
						
			for(i=0;i<row_array.length;i++)
			{
				var rowID=jQuery(row_array[i]).val();
				var selectedList=jQuery("#selected_link").val();
				if(selectedList.indexOf("["+rowID+"]")<0)
					jQuery("#selected_link").val(selectedList+"["+rowID+"]");
			}
		}
		else
		{
			var selected_no=jQuery('input:checked', link_table.fnGetFilteredNodes()).length;
			jQuery("#no_link").html(parseInt(jQuery("#no_link").html())-selected_no);
			
			jQuery('input', link_table.fnGetFilteredNodes()).removeAttr("checked");
			
			var row_array=jQuery('input', link_table.fnGetFilteredNodes());
						
			for(i=0;i<row_array.length;i++)
			{
				var rowID=jQuery(row_array[i]).val();
				var selectedList=jQuery("#selected_link").val();
				if(selectedList.indexOf("["+rowID+"]")>=0)
					jQuery("#selected_link").val(selectedList.replace("["+rowID+"]",""));
			}
		}
	})
	
	jQuery("#checkall_media").click(function(){
		if (jQuery("#checkall_media").attr("checked") == "checked")
		{
			var total_no=jQuery('input', media_table.fnGetFilteredNodes()).length;
			var selected_no=jQuery('input:checked', media_table.fnGetFilteredNodes()).length;
			jQuery("#no_media").html(parseInt(jQuery("#no_media").html())+(total_no-selected_no));
			
			jQuery('input', media_table.fnGetFilteredNodes()).attr("checked","checked");
			
			var row_array=jQuery('input', media_table.fnGetFilteredNodes());
						
			for(i=0;i<row_array.length;i++)
			{
				var rowID=jQuery(row_array[i]).val();
				var selectedList=jQuery("#selected_media").val();
				if(selectedList.indexOf("["+rowID+"]")<0)
					jQuery("#selected_media").val(selectedList+"["+rowID+"]");
			}
		}
		else
		{
			var selected_no=jQuery('input:checked', media_table.fnGetFilteredNodes()).length;
			jQuery("#no_media").html(parseInt(jQuery("#no_media").html())-selected_no);
			
			jQuery('input', media_table.fnGetFilteredNodes()).removeAttr("checked");
			
			var row_array=jQuery('input', media_table.fnGetFilteredNodes());
						
			for(i=0;i<row_array.length;i++)
			{
				var rowID=jQuery(row_array[i]).val();
				var selectedList=jQuery("#selected_media").val();
				if(selectedList.indexOf("["+rowID+"]")>=0)
					jQuery("#selected_media").val(selectedList.replace("["+rowID+"]",""));
			}
		}
	})
	
	jQuery("#checkall_theme").click(function(){
		if (jQuery("#checkall_theme").attr("checked") == "checked")
		{
			var total_no=jQuery('input', theme_table.fnGetFilteredNodes()).length;
			var selected_no=jQuery('input:checked', theme_table.fnGetFilteredNodes()).length;
			jQuery("#no_theme").html(parseInt(jQuery("#no_theme").html())+(total_no-selected_no));
			
			jQuery('input', theme_table.fnGetFilteredNodes()).attr("checked","checked");
			
			var row_array=jQuery('input', theme_table.fnGetFilteredNodes());
						
			for(i=0;i<row_array.length;i++)
			{
				var rowID=jQuery(row_array[i]).val();
				var selectedList=jQuery("#selected_theme").val();
				if(selectedList.indexOf("["+rowID+"]")<0)
					jQuery("#selected_theme").val(selectedList+"["+rowID+"]");
			}
		}
		else
		{
			var selected_no=jQuery('input:checked', theme_table.fnGetFilteredNodes()).length;
			jQuery("#no_theme").html(parseInt(jQuery("#no_theme").html())-selected_no);
			
			jQuery('input', theme_table.fnGetFilteredNodes()).removeAttr("checked");
			
			var row_array=jQuery('input', theme_table.fnGetFilteredNodes());
						
			for(i=0;i<row_array.length;i++)
			{
				var rowID=jQuery(row_array[i]).val();
				var selectedList=jQuery("#selected_theme").val();
				if(selectedList.indexOf("["+rowID+"]")>=0)
					jQuery("#selected_theme").val(selectedList.replace("["+rowID+"]",""));
			}
		}
	})
	
	jQuery("#checkall_plugin").click(function(){
		if (jQuery("#checkall_plugin").attr("checked") == "checked")
		{
			var total_no=jQuery('input', plugin_table.fnGetFilteredNodes()).length;
			var selected_no=jQuery('input:checked', plugin_table.fnGetFilteredNodes()).length;
			jQuery("#no_plugin").html(parseInt(jQuery("#no_plugin").html())+(total_no-selected_no));
			
			jQuery('input', plugin_table.fnGetFilteredNodes()).attr("checked","checked");
			
			var row_array=jQuery('input', plugin_table.fnGetFilteredNodes());
						
			for(i=0;i<row_array.length;i++)
			{
				var rowID=jQuery(row_array[i]).val();
				var selectedList=jQuery("#selected_plugin").val();
				if(selectedList.indexOf("["+rowID+"]")<0)
					jQuery("#selected_plugin").val(selectedList+"["+rowID+"]");
			}
		}
		else
		{
			var selected_no=jQuery('input:checked', plugin_table.fnGetFilteredNodes()).length;
			jQuery("#no_plugin").html(parseInt(jQuery("#no_plugin").html())-selected_no);
			
			jQuery('input', plugin_table.fnGetFilteredNodes()).removeAttr("checked");
			
			var row_array=jQuery('input', plugin_table.fnGetFilteredNodes());
						
			for(i=0;i<row_array.length;i++)
			{
				var rowID=jQuery(row_array[i]).val();
				var selectedList=jQuery("#selected_plugin").val();
				if(selectedList.indexOf("["+rowID+"]")>=0)
					jQuery("#selected_plugin").val(selectedList.replace("["+rowID+"]",""));
			}
		}
	})
	
	jQuery("#checkall_user").click(function(){
		if (jQuery("#checkall_user").attr("checked") == "checked")
		{
			var total_no=jQuery('input', user_table.fnGetFilteredNodes()).length;
			var selected_no=jQuery('input:checked', user_table.fnGetFilteredNodes()).length;
			jQuery("#no_user").html(parseInt(jQuery("#no_user").html())+(total_no-selected_no));
			
			jQuery('input', user_table.fnGetFilteredNodes()).attr("checked","checked");
			
			var row_array=jQuery('input', user_table.fnGetFilteredNodes());
						
			for(i=0;i<row_array.length;i++)
			{
				var rowID=jQuery(row_array[i]).val();
				var selectedList=jQuery("#selected_user").val();
				if(selectedList.indexOf("["+rowID+"]")<0)
					jQuery("#selected_user").val(selectedList+"["+rowID+"]");
			}
		}
		else
		{
			var selected_no=jQuery('input:checked', user_table.fnGetFilteredNodes()).length;
			jQuery("#no_user").html(parseInt(jQuery("#no_user").html())-selected_no);
			
			jQuery('input', user_table.fnGetFilteredNodes()).removeAttr("checked");
			
			var row_array=jQuery('input', user_table.fnGetFilteredNodes());
						
			for(i=0;i<row_array.length;i++)
			{
				var rowID=jQuery(row_array[i]).val();
				var selectedList=jQuery("#selected_user").val();
				if(selectedList.indexOf("["+rowID+"]")>=0)
					jQuery("#selected_user").val(selectedList.replace("["+rowID+"]",""));
			}
		}
	})
	
	jQuery("#checkall_custom_post").click(function(){
		if (jQuery("#checkall_custom_post").attr("checked") == "checked")
		{
			var total_no=jQuery('input', custom_post_table.fnGetFilteredNodes()).length;
			var selected_no=jQuery('input:checked', custom_post_table.fnGetFilteredNodes()).length;
			jQuery("#no_custom_post").html(parseInt(jQuery("#no_custom_post").html())+(total_no-selected_no));
			
			jQuery('input', custom_post_table.fnGetFilteredNodes()).attr("checked","checked");
			
			var row_array=jQuery('input', custom_post_table.fnGetFilteredNodes());
						
			for(i=0;i<row_array.length;i++)
			{
				var rowID=jQuery(row_array[i]).val();
				var selectedList=jQuery("#selected_custom_post").val();
				if(selectedList.indexOf("["+rowID+"]")<0)
					jQuery("#selected_custom_post").val(selectedList+"["+rowID+"]");
			}
		}
		else
		{
			var selected_no=jQuery('input:checked', custom_post_table.fnGetFilteredNodes()).length;
			jQuery("#no_custom_post").html(parseInt(jQuery("#no_custom_post").html())-selected_no);
			
			jQuery('input', custom_post_table.fnGetFilteredNodes()).removeAttr("checked");
			
			var row_array=jQuery('input', custom_post_table.fnGetFilteredNodes());
						
			for(i=0;i<row_array.length;i++)
			{
				var rowID=jQuery(row_array[i]).val();
				var selectedList=jQuery("#selected_custom_post").val();
				if(selectedList.indexOf("["+rowID+"]")>=0)
					jQuery("#selected_custom_post").val(selectedList.replace("["+rowID+"]",""));
			}
		}
	});
	jQuery("#checkall_blox").click(function(){
		if (jQuery("#checkall_blox").attr("checked") == "checked"){
			var total_no=jQuery('input', blox_table.fnGetFilteredNodes()).length;
			var selected_no=jQuery('input:checked', blox_table.fnGetFilteredNodes()).length;
			jQuery("#no_blox").html(parseInt(jQuery("#no_custom_post").html())+(total_no-selected_no));
			
			jQuery('input', blox_table.fnGetFilteredNodes()).attr("checked","checked");
			
			var row_array=jQuery('input', blox_table.fnGetFilteredNodes());
						
			for(i=0;i<row_array.length;i++)
			{
				var rowID=jQuery(row_array[i]).val();
				var selectedList=jQuery("#selected_blox").val();
				if(selectedList.indexOf("["+rowID+"]")<0)
					jQuery("#selected_blox").val(selectedList+"["+rowID+"]");
			}
		}
		else{
			var selected_no=jQuery('input:checked', blox_table.fnGetFilteredNodes()).length;
			jQuery("#no_blox").html(parseInt(jQuery("#no_blox").html())-selected_no);
			
			jQuery('input', blox_table.fnGetFilteredNodes()).removeAttr("checked");
			
			var row_array=jQuery('input', blox_table.fnGetFilteredNodes());
						
			for(i=0;i<row_array.length;i++)	{
				var rowID=jQuery(row_array[i]).val();
				var selectedList=jQuery("#selected_blox").val();
				if(selectedList.indexOf("["+rowID+"]")>=0)
					jQuery("#selected_blox").val(selectedList.replace("["+rowID+"]",""));
			}
		}
	});
	
	jQuery("#checkall_menu").click(function(){
		if (jQuery("#checkall_menu").attr("checked") == "checked"){
			var total_no=jQuery('input', menu_table.fnGetFilteredNodes()).length;
			var selected_no=jQuery('input:checked', menu_table.fnGetFilteredNodes()).length;
			jQuery("#no_menu").html(parseInt(jQuery("#no_menu").html())+(total_no-selected_no));
			
			jQuery('input', menu_table.fnGetFilteredNodes()).attr("checked","checked");
			
			var row_array=jQuery('input', menu_table.fnGetFilteredNodes());
						
			for(i=0;i<row_array.length;i++)
			{
				var rowID=jQuery(row_array[i]).val();
				var selectedList=jQuery("#selected_menu").val();
				if(selectedList.indexOf("["+rowID+"]")<0)
					jQuery("#selected_menu").val(selectedList+"["+rowID+"]");
			}
		}
		else{
			var selected_no=jQuery('input:checked', menu_table.fnGetFilteredNodes()).length;
			jQuery("#no_menu").html(parseInt(jQuery("#no_menu").html())-selected_no);
			
			jQuery('input', menu_table.fnGetFilteredNodes()).removeAttr("checked");
			
			var row_array=jQuery('input', menu_table.fnGetFilteredNodes());
						
			for(i=0;i<row_array.length;i++)	{
				var rowID=jQuery(row_array[i]).val();
				var selectedList=jQuery("#selected_menu").val();
				if(selectedList.indexOf("["+rowID+"]")>=0)
					jQuery("#selected_menu").val(selectedList.replace("["+rowID+"]",""));
			}
		}
	})
	
	jQuery("#pub").click(function(){

		var allVals = [];
		var allTypes = [];
		
		var ids=["#selected_post","#selected_page","#selected_media","#selected_theme","#selected_plugin","#selected_user","#selected_custom_post","#selected_tables","#selected_menu","#selected_blox"];
		var type=[0,0,2,3,4,5,0,6,20,10];
		
		for(i=0;i<10;i++){
			var value=jQuery(ids[i]).val().split("][").join(",").split("[").join("").split("]").join("");
			if(value!="")
			{
				var row_array=value.split(",");
				for(j=0;j<row_array.length;j++)
				{
					allVals.push(row_array[j]);
					allTypes.push(type[i]);
				}
			}
		}
		
		var total = allVals.length;
		var step = 100/total;
				
		if (allVals.length == 0){
			alert("You must select at least one post!");
		}else{
			jQuery("#progressbar").css("display","block");
			jQuery("#status").removeAttr("disabled");
			jQuery("#ajax_field").html("");
			jQuery('.push-progress').find('.progress-bar').css('width', '0').attr('aria-valuenow', 0).html('0%');
			jQuery('.percent_status').show();
			jQuery('.percent_status').html("0%");
			jQuery("#pub").attr("disabled","disabled");
			doCopy(0,0,allVals.length,allVals,allTypes,step);
		}
	});

	jQuery("#pub_all").click(function(){
	                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                      	var allVals = [];
		var allTypes = [];
		
		var ids=["#selected_post","#selected_page","#selected_link","#selected_media","#selected_theme","#selected_plugin","#selected_user","#selected_custom_post"];
		var result = confirm("Warning - you are about to push all site content");
		if ( result == true ) {
			jQuery.ajax({
				url: ajaxurl+"?action=push_all",
				type:'get',
				dataType : 'json',
				success:function(data)
				{
					if(data!= ""){
						allVals = data.value;
						allTypes = data.type;
						var total = allVals.length;
						var step = 100/total;
								
						if (allVals.length == 0){
							alert("Nothing to push!");
						}else{
							jQuery("#progressbar").css("display","block");
							jQuery("#status").removeAttr("disabled");
							jQuery("#ajax_field").html("");
							jQuery('.push-progress').find('.progress-bar').css('width', '0').attr('aria-valuenow', 0).html('0%');
							jQuery('.percent_status').show();
							jQuery('.percent_status').html("0%");
							jQuery("#pub").attr("disabled","disabled");
							jQuery(".push-btn").attr("disabled","disabled");
							doPostCopy(0,0,allVals.length,allVals,allTypes,step);
						}
					}
				}
			});
		}
	});

	jQuery("#pub_all_post").click(function(){
	                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                      	var allVals = [];
		var allTypes = [];
		
		var ids=["#selected_post","#selected_page","#selected_link","#selected_media","#selected_theme","#selected_plugin","#selected_user","#selected_custom_post"];
		var result = confirm("Warning - you are about to push all site content except post");
		if ( result == true ) {
			jQuery.ajax({
				url:AJAX.path+"/includes/ajax_push_all_post_fields.php",
				type:'get',
				dataType : 'json',
				success:function(data)
				{
					if(data!=""){
						allVals = data.value;
						allTypes = data.type;
						var total = allVals.length;
						var step = 100/total;
								
						if (allVals.length == 0){
							alert("Nothing to push!");
						}else{
							jQuery("#progressbar").css("display","block");
							jQuery("#status").removeAttr("disabled");
							jQuery("#ajax_field").html("");
							jQuery('.push-progress').find('.progress-bar').css('width', '0').attr('aria-valuenow', 0).html('0%');
							jQuery('.percent_status').show();
							jQuery('.percent_status').html("0%");
							jQuery("#pub").attr("disabled","disabled");
							jQuery(".push-btn").attr("disabled","disabled");
							doPostCopy(0,0,allVals.length,allVals,allTypes,step);
						}
					}
				}
			});
		}
	});

	jQuery("#pub_replace").click(function(){
	                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                      	var allVals = [];
		var allTypes = [];
		
		var result = confirm("Warning - you are about to copy this site.");
		if ( result == true ) {
			jQuery.ajax({
				url:ajaxurl+"?action=replace_content",
				type:'get',
				dataType : 'json',
				success:function(data)
				{
					if(data!=""){
						allVals = data.value;
						allTypes = data.type;
						var total = allVals.length;
						var step = 100/total;
								
						if (allVals.length == 0){
							alert("Nothing to push!");
						}else{
							jQuery("#progressbar").css("display","block");
							jQuery("#status").removeAttr("disabled");
							jQuery("#ajax_field").html("");
							jQuery('.push-progress').find('.progress-bar').css('width', '0').attr('aria-valuenow', 0).html('0%');
							jQuery('.percent_status').show();
							jQuery('.percent_status').html("0%");
							jQuery("#pub").attr("disabled","disabled");
							jQuery(".push-btn").attr("disabled","disabled");
							doPostCopy(0,0,allVals.length,allVals,allTypes,step);
						}
					}
				}
			});
		}
	});
	jQuery('#pub_database').click(function(){
		var allTypes = [];
		
		var result = confirm("Warning - you are about to copy this site.");
		if ( result == true ) {
			jQuery.ajax({
				url:ajaxurl+"?action=replace_content",
				type:'get',
				dataType : 'json',
				success:function(data)
				{
					if(data!=""){
						allVals = data.value;
						allTypes = data.type;
						var total = allVals.length;
						var step = 100/total;
								
						if (allVals.length == 0){
							alert("Nothing to push!");
						}else{
							jQuery("#progressbar").css("display","block");
							jQuery("#status").removeAttr("disabled");
							jQuery("#ajax_field").html("");
							jQuery('.push-progress').find('.progress-bar').css('width', '0').attr('aria-valuenow', 0).html('0%');
							jQuery('.percent_status').show();
							jQuery('.percent_status').html("0%");
							jQuery("#pub").attr("disabled","disabled");
							jQuery(".push-btn").attr("disabled","disabled");
							doPostCopy(0,0,allVals.length,allVals,allTypes,step);
						}
					}
				}
			});
		}
	});

	jQuery('#pub_delete').click(function(){
		var allTypes = [];
		
		var result = confirm("Warning - you are about to delete destination site.");
		if ( result == true ) {
			jQuery.ajax({
				url:ajaxurl+"?action=push_delete",
				type:'get',
				dataType : 'json',
				success:function(data)
				{
					if(data!=""){
						allVals = data.value;
						allTypes = data.type;
						var total = allVals.length;
						var step = 100/total;
								
						if (allVals.length == 0){
							alert("Nothing to push!");
						}else{
							jQuery("#progressbar").css("display","block");
							jQuery("#status").removeAttr("disabled");
							jQuery("#ajax_field").html("");
							jQuery('.push-progress').find('.progress-bar').css('width', '0').attr('aria-valuenow', 0).html('0%');
							jQuery('.percent_status').show();
							jQuery('.percent_status').html("0%");
							jQuery("#pub").attr("disabled","disabled");
							jQuery(".push-btn").attr("disabled","disabled");
							doPostCopy(0,0,allVals.length,allVals,allTypes,step);
						}
					}
				}
			});
		}
	});

	jQuery('#pub_image').click(function(){
		var result = confirm("Warning - you are about to copy entire uploads directory to destination.");
		if ( result == true ) {
			jQuery.ajax({
				url:ajaxurl+"?action=push_image",
				type:'get',
				dataType : 'json',
				success:function(data){
					if(data!=""){
						var total = data.total;
						var step = 100/total;

						if (data.length == 0){
							alert("Nothing to push!");
						}else{
							jQuery("#progressbar").css("display","block");
							jQuery("#status").removeAttr("disabled");
							jQuery("#ajax_field").html("");
							jQuery('.push-progress').find('.progress-bar').css('width', '0').attr('aria-valuenow', 0).html('0%');
							jQuery('.percent_status').show();
							jQuery('.percent_status').html("0%");
							jQuery("#pub").attr("disabled","disabled");
							jQuery(".push-btn").attr("disabled","disabled");
							doImageCopy(0,0,data.length, data);
						}
					}
				}
			});
		}
	});
	
	jQuery("#status").click(function(){
		jQuery( "#dialog" ).dialog({
			width: 650,
			height : 400,
			modal: true,
			buttons: [ { text: "Hide", click: function() { jQuery( this ).dialog( "close" ); } } ]
		});
	});

	jQuery("#sync").click(function(){
		var allTypes = [];

		jQuery.ajax({
			url:AJAX.path+"/includes/ajax_push_all_fields.php",
			type:'get',
			dataType : 'json',
			success:function(data)
			{
				if(data!=""){
					allVals = data.value;
					allTypes = data.type;
					var total = allVals.length;
					var step = 100/total;
							
					if (allVals.length == 0){
						alert("Nothing to push!");
					}else{
						jQuery("#progressbar").css("display","block");
						jQuery("#status").removeAttr("disabled");
						jQuery("#ajax_field").html("");
						jQuery('.push-progress').find('.progress-bar').css('width', '0').attr('aria-valuenow', 0).html('0%');
						jQuery('.percent_status').show();
						jQuery('.percent_status').html("0%");
						jQuery("#pub").attr("disabled","disabled");
						jQuery(".push-btn").attr("disabled","disabled");
						doPostSync(0,0,allVals.length,allVals,allTypes,step);
					}
				}
			}
		});
	});
	
	jQuery("#new_setting").click(function(){
		var setting_list=jQuery("#setting_wrapper").find(".s_as");
		var last_id=jQuery(setting_list[setting_list.length-1]).attr("id");
		
		var new_id="setting_accordion_"+(parseInt(last_id.split("_")[2])+1);
		var setting_content="<div style=\"border:1px solid #ddd; padding:15px; margin-bottom:40px; width:600px\"><input type=\"button\" class=\"remove_setting\" value=\"Remove\" onclick=\"remove_setting(this)\" /><input type=\"button\" class=\"activate_setting\" value=\"Activate\" onclick=\"activate_setting(this)\" />";
		
		setting_content+="<div id="+new_id+" class=\"s_as\">";
		setting_content+=jQuery(setting_list[0]).html();
		jQuery(setting_list[0]).parent().find(".remove_setting").css("display","inline");
		setting_content+="</div>";
		
		jQuery("#setting_wrapper").append(setting_content);
		jQuery("#"+new_id).accordion();
		
		jQuery(window).scrollTop(jQuery(document).height());
	});
});

function doImageCopy(progress, iterator, limit, hash) {
	if (iterator < limit){
		var plugin_path = jQuery('#plugin_path').val();
		var step = 100/limit;
		jQuery.ajax({
			url:AJAX.path +'/includes/xi_image_all.php?hash='+hash[iterator],
			type:'get',
			success:function(msg){
				var cont = jQuery("#ajax_field").find("tbody").html();
				if (msg!='')
					jQuery("#ajax_field").html(cont+ "<tr><td>" + msg + "</td></tr>");
				jQuery("#log-container").stop().animate({ scrollTop: jQuery("#ajax_field")[0].scrollHeight},800)
				iterator ++;
				progress = progress + step;
				jQuery('.push-progress').find('.progress-bar').css('width', progress + '%').attr('aria-valuenow', progress).html(parseInt(progress) + '%');
				jQuery('.percent_status').show();
				jQuery('.percent_status').html(parseInt(progress)+"%");
				doImageCopy(progress, iterator, limit, hash);
			}
		});
	}
	else{
		var cont = jQuery("#ajax_field").html();
		jQuery('.push-progress').find('.progress-bar').css('width', '100%').attr('aria-valuenow', 100).html('100%');
		jQuery("#ajax_field").html(cont+"<tr><td class=\"bg-primary\">Completed!</td><tr>");
		jQuery("#pub").removeAttr("disabled");
		jQuery(".push-btn").removeAttr("disabled");
		jQuery("#pub_all_post").removeAttr("disabled");
		jQuery("#copy_all").removeAttr("disabled");
		jQuery("#delete_all").removeAttr("disabled");
		jQuery("#spaceused1_percentText").html("Completed");
		jQuery(".checkall-btn").removeAttr("checked");
		jQuery(".no-selected").html(0);
		jQuery('.percent_status').html("100%!");
		jQuery(".post_check").removeAttr("checked");
		jQuery(".page_check").removeAttr("checked");
		jQuery(".media_check").removeAttr("checked");
		jQuery(".theme_check").removeAttr("checked");
		jQuery(".plugin_check").removeAttr("checked");
		jQuery(".user_check").removeAttr("checked");
		jQuery(".custom_post_check").removeAttr("checked");
		jQuery(".menu_check").removeAttr("checked");
		jQuery(".blox_check").removeAttr("checked");
		jQuery('.selected-hidden').val("");

		jQuery.ajax({
			type	:	"get",
			url		:	AJAX.path + "/includes/xi_publish_status.php?end=1", // check other users are using this plugin on same host
			success	:	function( val ) {}
		});
	}
}

function doPostCopy(progress, iterator, limit, allvals, alltypes, step) {
	if (iterator < limit){
		var plugin_path = jQuery('#plugin_path').val();
		jQuery.ajax({
			url:ajaxurl + '?action=push_copy&type='+alltypes[iterator]+'&value='+allvals[iterator],
			type:'get',
			success:function(msg){
				var cont = jQuery("#ajax_field").find("tbody").html();
				if (msg!='')
					jQuery("#ajax_field").html(cont+ "<tr><td>" + msg + "</td></tr>");
				jQuery("#log-container").stop().animate({ scrollTop: jQuery("#ajax_field")[0].scrollHeight},800)
				iterator ++;
				progress = progress + step;
				jQuery('.push-progress').find('.progress-bar').css('width', progress + '%').attr('aria-valuenow', progress).html(parseInt(progress) + '%');
				jQuery('.percent_status').show();
				jQuery('.percent_status').html(parseInt(progress)+"%");
				doCopy(progress,iterator,limit,allvals,alltypes,step);
			}
		});
	}
	else{
		var cont = jQuery("#ajax_field").html();
		jQuery('.push-progress').find('.progress-bar').css('width', '100%').attr('aria-valuenow', 100).html('100%');
		jQuery("#ajax_field").html(cont+"<tr><td class=\"bg-primary\">Completed!</td><tr>");
		jQuery("#pub").removeAttr("disabled");
		jQuery(".push-btn").removeAttr("disabled");
		jQuery("#pub_all_post").removeAttr("disabled");
		jQuery("#copy_all").removeAttr("disabled");
		jQuery("#delete_all").removeAttr("disabled");
		jQuery("#spaceused1_percentText").html("Completed");
		jQuery(".checkall-btn").removeAttr("checked");
		jQuery(".no-selected").html(0);
		jQuery('.percent_status').html("100%!");
		jQuery(".post_check").removeAttr("checked");
		jQuery(".page_check").removeAttr("checked");
		jQuery(".media_check").removeAttr("checked");
		jQuery(".theme_check").removeAttr("checked");
		jQuery(".plugin_check").removeAttr("checked");
		jQuery(".user_check").removeAttr("checked");
		jQuery(".custom_post_check").removeAttr("checked");
		jQuery(".menu_check").removeAttr("checked");
		jQuery(".blox_check").removeAttr("checked");
		jQuery('.selected-hidden').val("");

		jQuery.ajax({
			type	:	"get",
			url		:	AJAX.path + "/includes/xi_publish_status.php?end=1", // check other users are using this plugin on same host
			success	:	function( val ) {}
		});
	}
}

function doPostSync(progress, iterator, limit, allvals, alltypes, step) {
	if (iterator < limit){
		var plugin_path = jQuery('#plugin_path').val();
		jQuery.ajax({
			url: ajaxurl +'?action=push_sync&type='+alltypes[iterator]+'&value='+allvals[iterator],
			type:'get',
			success:function(msg){
				var cont = jQuery("#ajax_field").find("tbody").html();
				if (msg!='')
					jQuery("#ajax_field").html(cont+ "<tr><td>" + msg + "</td></tr>");
				jQuery("#log-container").stop().animate({ scrollTop: jQuery("#ajax_field")[0].scrollHeight},800)
				iterator ++;
				progress = progress + step;
				jQuery('.push-progress').find('.progress-bar').css('width', progress + '%').attr('aria-valuenow', progress).html(progress + '%');
				jQuery('.percent_status').show();
				jQuery('.percent_status').html(parseInt(progress)+"%");
				doSync(progress,iterator,limit,allvals,alltypes,step);
			}
		});
	}
	else{
		var cont = jQuery("#ajax_field").html();
		jQuery('.push-progress').find('.progress-bar').css('width', '100%').attr('aria-valuenow', 100).html('100%');
		jQuery("#ajax_field").html(cont+"<tr><td class=\"bg-primary\">Completed!</td><tr>");
		jQuery("#pub").removeAttr("disabled");
		jQuery(".push-btn").removeAttr("disabled");
		jQuery("#pub_all_post").removeAttr("disabled");
		jQuery("#copy_all").removeAttr("disabled");
		jQuery("#delete_all").removeAttr("disabled");
		jQuery("#spaceused1_percentText").html("Completed");
		jQuery(".checkall-btn").removeAttr("checked");
		jQuery(".no-selected").html(0);
		jQuery('.percent_status').html("100%!");
		jQuery(".post_check").removeAttr("checked");
		jQuery(".page_check").removeAttr("checked");
		jQuery(".media_check").removeAttr("checked");
		jQuery(".theme_check").removeAttr("checked");
		jQuery(".plugin_check").removeAttr("checked");
		jQuery(".user_check").removeAttr("checked");
		jQuery(".custom_post_check").removeAttr("checked");
		jQuery(".menu_check").removeAttr("checked");
		jQuery(".blox_check").removeAttr("checked");
		jQuery('.selected-hidden').val("");
	}
}

function doCopy(progress,iterator,limit,allvals,alltypes,step)
{
	if (iterator < limit){
		var plugin_path = jQuery('#plugin_path').val();
		jQuery.ajax({
			url:ajaxurl +'?action=push_copy&type='+alltypes[iterator]+'&value='+allvals[iterator],
			type:'get',
			success:function(msg){
				var cont = jQuery("#ajax_field").find("tbody").html();
				if (msg!='')
					jQuery("#ajax_field").html(cont+ "<tr><td>" + msg + "</td></tr>");
				jQuery("#log-container").stop().animate({ scrollTop: jQuery("#ajax_field")[0].scrollHeight},800)
				iterator ++;
				progress = progress + step;
				jQuery('.push-progress').find('.progress-bar').css('width', progress + '%').attr('aria-valuenow', progress).html(parseInt(progress) + '%');
				
				doCopy(progress,iterator,limit,allvals,alltypes,step);
			},
			error: function(error) {
				jQuery("#log-container").stop().animate({ scrollTop: jQuery("#ajax_field")[0].scrollHeight},800)
				iterator ++;
				progress = progress + step;
				jQuery('.push-progress').find('.progress-bar').css('width', progress + '%').attr('aria-valuenow', progress).html(parseInt(progress) + '%');
				
				doCopy(progress,iterator,limit,allvals,alltypes,step);
			}
		});
	}
	else
	{
		var cont = jQuery("#ajax_field").html();
		jQuery('.push-progress').find('.progress-bar').css('width', '100%').attr('aria-valuenow', 100).html('100%');
		jQuery("#ajax_field").html(cont+"<tr><td class=\"bg-primary\">Completed!</td><tr>");
		jQuery("#pub").removeAttr("disabled");
		jQuery(".push-btn").removeAttr("disabled");
		jQuery("#pub_all_post").removeAttr("disabled");
		jQuery("#copy_all").removeAttr("disabled");
		jQuery("#delete_all").removeAttr("disabled");
		jQuery("#spaceused1_percentText").html("Completed");
		jQuery(".checkall-btn").removeAttr("checked");
		jQuery(".no-selected").html(0);
		jQuery('.percent_status').html("100%!");
		jQuery(".post_check").removeAttr("checked");
		jQuery(".page_check").removeAttr("checked");
		jQuery(".media_check").removeAttr("checked");
		jQuery(".theme_check").removeAttr("checked");
		jQuery(".plugin_check").removeAttr("checked");
		jQuery(".user_check").removeAttr("checked");
		jQuery(".custom_post_check").removeAttr("checked");
		jQuery(".menu_check").removeAttr("checked");
		jQuery(".blox_check").removeAttr("checked");
		jQuery('.selected-hidden').val("");

	}
}

function doSync(progress,iterator,limit,allvals,alltypes,step)
{
	if (iterator < limit)
	{
		var plugin_path = jQuery('#plugin_path').val();
		jQuery.ajax({
			url:ajaxurl +'?action=push_sync&type='+alltypes[iterator]+'&value='+allvals[iterator],
			type:'get',
			success:function(msg)
			{
				var cont = jQuery("#ajax_field").find("tbody").html();
				if (msg!='')
					jQuery("#ajax_field").html(cont+ "<tr><td>" + msg + "</td></tr>");
				jQuery("#log-container").stop().animate({ scrollTop: jQuery("#ajax_field")[0].scrollHeight},800)
				iterator ++;
				progress = progress + step;
				jQuery('.push-progress').find('.progress-bar').css('width', progress + '%').attr('aria-valuenow', progress).html(parseInt(progress) + '%');
				jQuery('.percent_status').show();
				jQuery('.percent_status').html(parseInt(progress)+"%");
				doSync(progress,iterator,limit,allvals,alltypes,step);
			}
		});
	}
	else
	{
		var cont = jQuery("#ajax_field").html();
		jQuery('.push-progress').find('.progress-bar').css('width', '100%').attr('aria-valuenow', 100).html('100%');
		jQuery("#ajax_field").html(cont+"<tr><td class=\"bg-primary\">Completed!</td><tr>");
		jQuery("#pub").removeAttr("disabled");
		jQuery(".push-btn").removeAttr("disabled");
		jQuery("#pub_all_post").removeAttr("disabled");
		jQuery("#copy_all").removeAttr("disabled");
		jQuery("#delete_all").removeAttr("disabled");
		jQuery("#spaceused1_percentText").html("Completed");
		jQuery(".checkall-btn").removeAttr("checked");
		jQuery(".no-selected").html(0);
		jQuery('.percent_status').html("100%!");
		jQuery(".post_check").removeAttr("checked");
		jQuery(".page_check").removeAttr("checked");
		jQuery(".media_check").removeAttr("checked");
		jQuery(".theme_check").removeAttr("checked");
		jQuery(".plugin_check").removeAttr("checked");
		jQuery(".user_check").removeAttr("checked");
		jQuery(".custom_post_check").removeAttr("checked");
		jQuery(".menu_check").removeAttr("checked");
		jQuery(".blox_check").removeAttr("checked");
		jQuery('.selected-hidden').val("");
	}
}

function preview(src,width,height)
{
	if (width > 800){
		img_width = 800;
	}else{
		img_width = width;
	}
	
	if (height > 600){
		img_height = 600;
	}else{
		img_height = height;
	}
	jQuery( "#preview_image" ).dialog({
			width:img_width,
			height:img_height,
			modal: true
	});
	jQuery("#pre_img").attr("src",src);
	jQuery("#pre_img").attr("width",width);
	jQuery("#pre_img").attr("height",height);
}