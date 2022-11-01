window.addEventListener("DOMContentLoaded", (_evt) => {
	/**
	 * @param {Response|Error} resOrError
	 */
	async function showNotice(resOrError) {
		const container = document.querySelector("#hestia-nginx-cache-admin-notices");
		if (!container) {
			return;
		}
		const oldNotice = container.querySelector(".notice");
		const newNotice = document.createElement("div");
		const newNoticeBody = newNotice.appendChild(document.createElement("p"));

		if (!resOrError.ok) {
			console.error(resOrError);
			newNotice.classList.add("notice", "notice-error");
			newNoticeBody.textContent = "The Hestia Nginx Cache could not be purged!";
		} else {
			const { success, data } = await resOrError.clone().json();
			newNotice.classList.add("notice", success ? "notice-success" : "notice-error");
			newNoticeBody.textContent = data.message;
		}

		oldNotice?.remove();
		container.append(newNotice);
	}

	/**
	 * @param {Event} evt
	 */
	async function purge(evt) {
		evt.preventDefault();
		const nonce = document.querySelector("#hestia-nginx-cache-purge-wp-nonce");

		fetch(ajaxurl, {
			method: "POST",
			headers: { "Content-Type": "application/x-www-form-urlencoded" },
			body: new URLSearchParams({
				action: "hestia_nginx_cache_manual_purge",
				wp_nonce: nonce.textContent,
			}),
		})
			.then(showNotice)
			.catch(showNotice);
	}

	document
		.querySelector("#wp-admin-bar-hestia-nginx-cache-manual-purge .ab-item")
		?.addEventListener("click", purge);
	document
		.querySelector(".settings_page_hestia-nginx-cache input#purge_cache")
		?.addEventListener("click", purge);
});
