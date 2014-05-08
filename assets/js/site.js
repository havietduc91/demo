//Custom js files for site
/** Author: Chungth ; email: huychungtran@gmail.com**/
$(function(){ // document ready
	// tinycme
	tinymceConfigs = 
	{
        mode : "specific_textareas",
        editor_selector : "isEditor",
        plugins : "media",
        setup : function(ed) {
        	if($(ed.getElement('class')).hasClass('full'))
    		{
        		//ed.settings.plugins += ",table";
        		//ed.settings.theme_advanced_buttons1 += ",mce_media,table";
        		// Create an alternate "insert media" button
        		ed.addButton ('mce_media', {
        		    'title' : 'Embed a video',
        		    'image' : CL.ASSETS_CDN + '/common/images/youtube.gif',
        		    'onclick' : function () {
        		        var url = prompt('Please enter the embed code for your video:','');
        		        var code, regexRes;
        		        if(url == null)
        		        	return;
        		        regexRes = url.match("[\\?&]v=([^&#]*)");
        		        code = (regexRes === null) ? "" : regexRes[1];
        		        if (code === "") { return; }
        		        
        		        flash_mov = "http://www.youtube.com/v/" + code + "?version=3&f=videos";
        		        embedHTML = '';
        		        embedHTML += '<object>';
    					embedHTML += '<param name="movie" value="'+flash_mov+'"></param>';
    					embedHTML += '<param name="allowFullScreen" value="true"></param>';
    					embedHTML += '<param name="allowscriptaccess" value="always"></param>';	
    					embedHTML += '<param name="movie" value="' + flash_mov + '" />';
    					embedHTML += '<param name="wmode" value="transparent" />';
    					embedHTML += '<embed src="' + flash_mov + '" type="application/x-shockwave-flash" wmode="transparent" type="application/x-shockwave-flash" width="530" height="315" allowscriptaccess="always" allowfullscreen="true"></embed></object>';
    					
        		        //ed.execCommand('mceInsertContent', false, '<img src="http://img.youtube.com/vi/' + code + '/0.jpg" class="mceItem" alt="' + code + '"/>');
        		        ed.execCommand('mceInsertContent', false, embedHTML);
        		    }
        		});
        		
    		}
        },
        formats : {
        	underline : {inline : 'u', exact : true},
        },
        style_formats: [
            {title: 'Heading 1',block: 'h1', exact: true},
            {title: 'Heading 2',block: 'h2', exact: true},
            {title: 'Heading 3',block: 'h3', exact: true},
            {title: 'Heading 4',block: 'h4', exact: true},
            {title: 'Heading 5',block: 'h5', exact: true},
            {title: 'Heading 6',block: 'h6', exact: true},
        ],
        font_size_style_values : "xx-small,x-small,small,medium,large,x-large,xx-large",
        content_css : CL.ASSETS_CDN + "/edx/css/tinymce.css", 
        imagemanager_contextmenu: true,
        theme_advanced_toolbar_align : "left",
        theme_advanced_resizing : true,
        theme_advanced_buttons1 : "bold,italic,underline,bullist,numlist,|,styleselect,|,forecolor,backcolor,|,link,unlink,|,undo,redo,|",
        theme_advanced_buttons2 : "",
        theme_advanced_buttons3 : "",
        theme : 'advanced',
        width : "100%",
        height: "200",
        entity_encoding : "raw",
        force_br_newlines : false,
    	force_p_newlines : false,
    	forced_root_block : '',
        relative_urls : false,
		file_browser_callback : MadFileBrowser,
		extended_valid_elements: "iframe[src|title|width|height|allowfullscreen|frameborder]"
	};	
	tinyMCE.init(tinymceConfigs);
	
	function MadFileBrowser(field_name, url, type, win) {
		  tinyMCE.activeEditor.windowManager.open({
		      file : "/mfm.php?field=" + field_name + "&url=" + url + "",
			  //file: "/file/master/file-manager?field=" + field_name + "&url=" + url + "",
		      title : 'File Manager',
		      width : "640",
		      height : "450",
		      resizable : "no",
		      inline : "yes",
		      close_previous : "no"
		  }, {
		      window : win,
		      input : field_name
		  });
		  return false;
	}

});
/** End * */
/*
<!-- Place this tag after the last +1 button tag. -->
*/
/*
window.___gcfg = {lang: 'vi'};

(function() {
	var po = document.createElement('script'); po.type = 'text/javascript'; po.async = true;
	po.src = 'https://apis.google.com/js/plusone.js';
	var s = document.getElementsByTagName('script')[0]; s.parentNode.insertBefore(po, s);
})();

(function(d, s, id) {
  var js, fjs = d.getElementsByTagName(s)[0];
  if (d.getElementById(id)) return;
  js = d.createElement(s); js.id = id;
  js.src = "//connect.facebook.net/en_US/all.js#xfbml=1&appId=216059938523158";
  fjs.parentNode.insertBefore(js, fjs);
}(document, 'script', 'facebook-jssdk'));
*/
