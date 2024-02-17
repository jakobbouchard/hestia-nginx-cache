window.addEventListener("DOMContentLoaded", (_evt) => {
	/** @type {number|null} */
	let timeout = null;

	/** @returns {HTMLDivElement} */
	function createContainer() {
		const container = document.createElement("div");
		container.id = "hestia-nginx-cache-admin-notices";
		document.querySelector("#wpadminbar").append(container);
		return container;
	}

	/**
	 * @param {string} message
	 * @param {boolean} [success=false]
	 */
	function showNotice(message, success = false) {
		let container = document.querySelector("#hestia-nginx-cache-admin-notices");
		if (!container) {
			container = createContainer();
		}

		let notice = container.querySelector(".notice");
		if (notice) {
			notice.classList.value = `notice ${success ? "notice-success" : "notice-error"}`;
			notice.querySelector("p").textContent = message;
		} else {
			notice = document.createElement("div");
			notice.classList.add("notice", success ? "notice-success" : "notice-error");
			notice.appendChild(document.createElement("p")).textContent = message;
			container.appendChild(notice);
		}

		if (timeout) {
			clearTimeout(timeout);
		}
		timeout = setTimeout(notice.remove, 5000);
	}

	/** @param {Event} evt */
	async function purge(evt) {
		evt.preventDefault();
		const nonce = document.querySelector("#hestia-nginx-cache-purge-wp-nonce");

		try {
			const result = await fetch(ajaxurl, {
				method: "POST",
				headers: { "Content-Type": "application/x-www-form-urlencoded" },
				body: new URLSearchParams({
					action: "hestia_nginx_cache_manual_purge",
					wp_nonce: nonce.textContent,
				}),
			});

			if (!result.ok) {
				console.error(result);
				showNotice(hestia_nginx_cache.could_not_purge);
			} else {
				const { success, data } = await result.clone().json();
				showNotice(data.message, success);
			}
		} catch (error) {
			console.error(error);
			showNotice(hestia_nginx_cache.could_not_purge);
		}
	}

	document
		.querySelector("#wp-admin-bar-hestia-nginx-cache-manual-purge .ab-item")
		?.addEventListener("click", purge);
	document
		.querySelector(".settings_page_hestia-nginx-cache input#purge_cache")
		?.addEventListener("click", purge);
});
