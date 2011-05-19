

function scormcloud_deleteCourse(courseId,posturl){
        
        if (confirm('Are you sure you wish to delete this course?')){
        
            jQuery.ajax({
            type: "POST",
            url: posturl,
            data: 	"action=deletecourse" + 
                    "&courseid=" + courseId,
            success: function(html){
                jQuery('#CourseListTable tr[key=' + courseId + ']').hide();
            }
            });
        }
        
        
    
}

function scormcloud_LaunchCourseReport(courseId,posturl){
        
        
        jQuery.ajax({
        type: "POST",
        url: posturl,
        data: 	"action=getCourseReportUrl" + 
                "&courseid=" + courseId,
        success: function(url){
            window.open(url);
        }
        });
        
     
}

function scormcloud_LaunchCoursePreview(courseId,posturl,returnUrl){
        
        
        jQuery.ajax({
        type: "POST",
        url: posturl,
        data: 	"action=getPreviewUrl" + 
                "&courseid=" + courseId +
                "&returnurl=" + returnUrl,
        success: function(url){
            window.location = url;
        }
        });
        
     
}

jQuery(document).ready(function(){
    
    jQuery('span.localizeRecentDate').each(function(){
        
        var d = new Date(jQuery(this).attr('utcdate'));
        d.setTime(d.getTime() - (d.getTimezoneOffset() * 60000));
        var now = new Date();
        
        jQuery(this).text(((now.getDate() == d.getDate()) ? "Today " : "Yesterday ") + "at " + d.toLocaleTimeString());
    });
    
    jQuery('span.localizeDate').each(function(){
		var d = new Date(jQuery(this).attr('utcdate'));
		d.setTime(d.getTime() - (d.getTimezoneOffset() * 60000));
		var now = new Date();
		
		var fmt = jQuery(this).attr('format');
		if(fmt != null){
			jQuery(this).text(dateFormat(d, fmt));
		} else {
			jQuery(this).text(d.toLocaleDateString() + " " + d.toLocaleTimeString());
		}
	});
    
    
    jQuery('.scormcloud-admin-page .reportageWrapper h3.hndle').click(function(){
        jQuery(this).parent().toggleClass('closed');
    });
    
});