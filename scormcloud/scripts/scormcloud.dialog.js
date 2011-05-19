
var ScormCloud = window.ScormCloud || {};

(ScormCloud.Dialog = function() {
	
	return {
	
		embed : function() {
			
			if (typeof this.embedTrainingUrl !== 'string' || typeof tb_show !== 'function') {
				return;
			}
			
			//alert(this.embedTrainingUrl);
			tb_show('Insert SCORM Cloud Training', this.embedTrainingUrl + ((this.embedTrainingUrl.match(/\?/)) ? "&" : "?") , false);
		}
		
	};
	
}());




ScormCloud.Dialog.insertTag = function(str) {
		
		var tag = str;
		var win = window.parent || window;
				
		if ( typeof win.tinyMCE !== 'undefined' && ( win.ed = win.tinyMCE.activeEditor ) && !win.ed.isHidden() ) {
			win.ed.focus();
			if (win.tinymce.isIE)
				win.ed.selection.moveToBookmark(win.tinymce.EditorManager.activeEditor.windowManager.bookmark);

			win.ed.execCommand('mceInsertContent', false, tag);
		} else {
			win.edInsertContent(win.edCanvas, tag);
		}
		
		// Close Lightbox
		win.tb_remove();
		
	};