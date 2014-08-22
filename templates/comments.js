<script>
	function loadComments(source, epid) {
	  "use strict";

		if ($('#comments_'+epid).length > 0){
			if ($('#comments_'+epid).is(':empty')) {
				$('#comments_'+epid).load('/comments/'+epid);
			} else {
				$('#comments_'+epid).empty();
			}
		} else {
			$('<div id="comments_'+epid+'"></div>').insertAfter(source);
			$('#comments_'+epid).load('/comments/'+epid);
		}
	}

	function processCommentSubmit(form, epid) {
		$.ajax({
				url: "/comments/comment_handler.php",
				type: "POST",
				data: $(form).serialize(),
				success: function(response) {
					if (response.return == "true") {
						$('#comments_'+epid).empty();
						$('#comments_'+epid).load('/comments/'+epid);
						alert("DANKE für dein Kommentar, es liegt nun zum Freigeben vor!");
					}
					$("#ron-commentform_errormessage").html(response.error);
				},
				error: function(xhr, textStatus, error){
					alert(response.error);
				}
			});

		return false;
	}

	function replyComment(epid, commentid) {
		$('#ron-cf-placeholder_'+epid).empty();
		$('#ron-cf-placeholder_'+epid).html('Antworten auf Kommentar');
//		$('#ron-commentform_'+epid).elements['replytoid'].val(commentid);
		document.forms['ron-commentform_'+epid].elements['replytoid'].value = commentid;
	}

</script>