var ScormCloud = window.ScormCloud || {};

(ScormCloud.Post = function() {
	return {};
}());

ScormCloud.Post.displayMessage = function(key,msg){
    jQuery('.scormCloudInvitation[key="'+ key +'"] .inviteMessage').text(msg).show().delay(5000).fadeOut('slow');
}

ScormCloud.Post.makeAnonRegLaunch = function(key){
    var $j = jQuery.noConflict();
    
	$j('input[key="' + key + '"][name="launch"]').attr('disabled', 'disabled');

    var fname = $j.trim($j('input[key="' + key + '"][name="scormcloudfname"]').val());
    var lname = $j.trim($j('input[key="' + key + '"][name="scormcloudlname"]').val());
    var email = $j.trim($j('input[key="' + key + '"][name="scormcloudemail"]').val());
    
    if (fname.length == 0 || lname.length == 0 || email.length == 0){
        ScormCloud.Post.displayMessage(key,"Please make sure you have entered a first and last name and an email.");
        return;
    }
    
    
    var inviteId = key;
    var postUrl = $j('input[key="' + key + '"][name="launch"]').attr('url');
    
    
    $j.ajax({
    type: "POST",
    url: postUrl,
    data: 	"action=addAnonRegGetLaunchUrl" + 
            "&fname=" + fname +
            "&lname=" + lname +
            "&email=" + encodeURIComponent(email) +
            "&inviteid=" + inviteId +
            "&returnurl=" + window.location,
    success: function(data){
        //alert(data);
        window.location = data;
    }
    });
    
    
    
    
}

ScormCloud.Post.makeUserRegLaunch = function(key){
    var $j = jQuery.noConflict();
    
	$j('input[key="' + key + '"][name="launch"]').attr('disabled', 'disabled');

    var inviteId = key;
    var postUrl = $j('input[key="' + key + '"][name="launch"]').attr('url');
    
    $j.ajax({
    type: "POST",
    url: postUrl,
    data: 	"action=addUserRegGetLaunchUrl" + 
            "&inviteid=" + inviteId +
            "&returnurl=" + window.location,
    success: function(data){
        //alert(data);
        window.location = data;
    }
    });
    
    
}



ScormCloud.Post.getLaunchURL = function(key, regId){
    var $j = jQuery.noConflict();
    
    var postUrl = $j('input[key="' + key + '"][name="launch"]').attr('disabled', 'disabled').attr('url');
    
    $j.ajax({
    type: "POST",
    url: postUrl,
    data: 	"action=getLaunchUrl" + 
            "&regid=" + regId +
            "&returnurl=" + window.location,
    success: function(data){
        //alert(data);
        window.location = data;
    }
    });
    
    
}