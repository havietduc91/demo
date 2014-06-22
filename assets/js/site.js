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
	
	var loaded = 0;
	var isloading = false;
	if (CL.page === 'site/index/index')
    {
	    //video list load more
	    $(window).scroll(function(e){
	        if  ($(window).scrollTop() == $(document).height() - $(window).height()){
	            if (loaded < 1 && !isloading)
	            {
	                //$('#nav-footer').remove();
	                var url = $('#next-page').attr('data-href-widget');
	                if(url != '#/widget'){
	                    $("#nav-footer").hide();
	                    $('#loading').show();
	                    isloading = true;
	                    $.ajax({
	                        url : url,
	                        data : {
	                            _cl_modal_ajax : 1
	                        },
	                        success : function(data){
	                            isloading = false;
	                            loaded ++;
	                            $("#nav-footer").remove();
	                            $(data.result.content).insertBefore("#loading");
	                            $('#loading').hide();
	                        }
	                    });	
	                }
	            }
	            else
	            {
	                // do noting
	            }
	        }
	    });
    }

});

$(document).ready(function() {
	$.ajaxSetup({ cache: true });
	$.getScript('//connect.facebook.net/en_UK/all.js', function(){
		FB.init({
			appId: CL.FB_APP_ID,
			//channelUrl: 'hoibi.net',
			  status: true,
	          cookie: true, 
	          xfbml: true,
	            
		});     
		var init_comment_cb = function()
		{
			FB.Event.subscribe('comment.create',
		            function (response) {
		                $.ajax({
		                    url : "/video/new-fb-comment",
		                    data : 
		                    {
		                        id  : CL.nid,
		                        url : window.location.href
		                    }
		                });
		            }
		         );
		        FB.Event.subscribe('comment.remove',
		            function (response) {
		               $.ajax();
		            }
	        );
		};
		
		var init_like_cb = function()
		{
			 FB.Event.subscribe('edge.create',
	            function (url) {
				 console.log(url);
		 			//from URL : http://fun.local/truyen-cuoi/440-123123-2.html
			     //TF : http://fun.local/quiz/757.html
		 			//TODO: check for fun & thaifun url
			        var re = new RegExp('(.*)\/([0-9]*)-([^\]*)\.html$');
		 			var tmp = url.match(re);
		 			console.log(tmp);
		 			if (tmp.length >= 2)
	 				{
		 				$.ajax({
		 					url : "/video/new-fb-like",
		 					data : 
		 					{
		 					    rt : 1, //voteup
		 						iid  : tmp[2], //9440
		 						url : url//http://hoibi.net/anh-vui/9440-1.htm
		 					},
		 					success:function(){
		 						count=$( "#total-vote-" + tmp[2] ).text();
		 						$( "#total-vote-" + tmp[2]).text(parseInt(count)+1);
		 					}
		 					
		 				});
	 				}
	            }
	        );
	        FB.Event.subscribe('edge.remove',
        		function (url) {
	 			//from URL : http://fun.local/truyen-cuoi/440-123123-2.html
	 			//TODO: check for fun & thaifun url
                var re = new RegExp('(.*)\/([0-9]*)-([^\]*)\.html$');
	 			var tmp = url.match(re);
	 			if (tmp.length >= 2)
 				{
	 				$.ajax({
	 					url : "/video/new-fb-like",
	 					data : 
	 					{
	 					    rt : 4,
	 						iid  : tmp[2],
	 						url : url
	 					},
	 					success:function(){
	 						count=$( "#total-vote-" + tmp[2] ).text();
	 						$( "#total-vote-" + tmp[2]).text(parseInt(count)-1);
	 					}
	 				});
 				}
            }
            );
		};
		
		/*
		var get_login_fb=function(){
			FB.getLoginStatus(function(response) {
				  if (response.status === 'connected') {
				    console.log(response.authResponse.accessToken);
				    setLocalStorageCookie('access_token',response.authResponse.accessToken);
//				    $.cookie("access_token", response.authResponse.accessToken);
				  }
				 else {
					 	FB.login(function(response) {
					 		if (response.authResponse) {
							     var access_token =   FB.getAuthResponse()['accessToken'];
//							     console.log('Access Token = '+ access_token);
							     FB.api('/me/feed', function(response) {
							     $.cookie("access_token", access_token);
							     });
							   } else {
							     console.log('User cancelled login or did not fully authorize.');
							   }
					 }, {scope: 'publish_stream,offline_access,email,user_events,create_event,user_location'});
				}
		    });
//			 location.reload();
		}
		*/
		
		if (CL.page == 'video/index/view')
		{
			init_comment_cb();
		}
		
		init_like_cb();
		//get_login_fb();
	});
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
