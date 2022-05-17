(function ($) {
	$(function () {
		$("#wp-admin-bar-hestia-nginx-cache-manual-purge a").click(function (e) {
			e.preventDefault();
			const container = $("#hestia-nginx-cache-admin-notices");
			$.post(
				ajaxurl,
				{
					action: "hestia_nginx_cache_manual_purge",
					wp_nonce: $.trim($("#hestia-nginx-cache-purge-wp-nonce").text()),
				},
				function (r) {
					let response;
					try {
						response = JSON.parse(r);
					} catch (error) {
						response = { success: false, message: error };
					}
					let noticeClass = "notice-success";
					if (!response.success) {
						noticeClass = "notice-error";
					}
					let notice = $(
						'<div class="notice ' +
							noticeClass +
							'"><p>' +
							response.message +
							"</p></div>"
					);
					container.append(notice);
					notice.on("click", function () {
						$(this).remove();
					});
					notice.delay(3000).fadeOut();
				}
			);
		});
	});
})(jQuery);
