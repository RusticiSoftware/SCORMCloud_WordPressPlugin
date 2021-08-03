
(ScormCloud.Widget = function() {
	return {};
}());



ScormCloud.Widget.getLaunchURL = function(regId,widgetType){
	if (jQuery('a[key="' + regId + '"]').attr('clicked') != 'true'){
    	var postUrl = jQuery('a[key="' + regId + '"]').attr('clicked','true').attr('url');
    
	    jQuery.ajax({
	    type: "POST",
	    url: postUrl,
	    data: 	"action=getLaunchUrl" +
	            "&widgetname=" + widgetType + 
	            "&regid=" + regId +
	            "&returnurl=" + window.location,
	    success: function(data){
	        //alert(data);
	        window.location = data;
	    }
	    });
    
	}
    
}

ScormCloud.Widget.getAnonCatalogLaunchURL = function(courseId){
    var $j = jQuery.noConflict();
    var fname = $j.trim($j('.courselistDiv .anonlaunchdiv input[key="' + courseId + '"][name="scormcloudfname"]').val());
    var lname = $j.trim($j('.courselistDiv .anonlaunchdiv input[key="' + courseId + '"][name="scormcloudlname"]').val());
    var email = $j.trim($j('.courselistDiv .anonlaunchdiv input[key="' + courseId + '"][name="scormcloudemail"]').val());
    
    if (fname.length == 0 || lname.length == 0 || email.length == 0){
        ScormCloud.Widget.displayCatalogMessage(courseId,"Please make sure you have entered a first and last name and an email.");
        return;
    }
    var postUrl = jQuery('.courselistDiv .anonlaunchdiv input.catalogLaunchBtn[key="' + courseId + '"]').attr('disabled', 'disabled').attr('url');
    var title = jQuery('.courselistDiv .anonlaunchdiv input.catalogLaunchBtn[key="' + courseId + '"]').attr('coursetitle');
    
    jQuery.ajax({
    type: "POST",
    url: postUrl,
    data: 	"action=addAnonCatalogRegGetLaunchUrl" +
            "&fname=" + fname +
            "&lname=" + lname +
            "&email=" + encodeURIComponent(email) +
            "&courseid=" + courseId +
            "&coursetitle=" + title +
            "&returnurl=" + window.location,
    success: function(data){
        //alert(data);
        window.location = data;
    }
    });
    
    
}

ScormCloud.Widget.getCatalogLaunchURL = function(courseId){
	if (jQuery('a[key="' + courseId + '"]').attr('clicked') != 'true'){

    	var postUrl = jQuery('a[key="' + courseId + '"]').attr('clicked','true').attr('url');
	    var title = jQuery('a[key="' + courseId + '"]').attr('coursetitle');
    
	    jQuery.ajax({
	    type: "POST",
	    url: postUrl,
	    data: 	"action=addCatalogRegGetLaunchUrl" + 
	            "&courseid=" + courseId +
	            "&coursetitle=" + title +
	            "&returnurl=" + window.location,
	    success: function(data){
	        //alert(data);
	        window.location = data;
	    }
	    });
	}
    
    
}

ScormCloud.Widget.displayCatalogMessage = function(key,msg){
    jQuery('.courselistDiv .anonlaunchdiv[key="'+ key +'"] .launchMessage').text(msg).show().delay(5000).fadeOut('slow');
}

var $j = jQuery.noConflict();

$j(document).ready(function(){

    $j(".courselistDiv .anonLaunch").toggle(function(){
        $j(".courselistDiv .anonlaunchdiv[key='" + $j(this).attr('key') +"']").fadeIn('fast');
    },function(){
        $j(".courselistDiv .anonlaunchdiv[key='" + $j(this).attr('key') +"']").fadeOut('fast');
    });
    

    $j('.courselistDiv .toggleButton').toggle(function(){
        $j($j(this).attr('toggleobject')).fadeIn();
        $j(this).text($j(this).attr('onText'));
    },function(){
        $j($j(this).attr('toggleobject')).fadeOut();
        $j(this).text($j(this).attr('offText'));
    });
});