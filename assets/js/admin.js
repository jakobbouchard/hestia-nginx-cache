window.addEventListener("DOMContentLoaded", (_event) => {
	document
		.querySelector("#wp-admin-bar-hestia-nginx-cache-manual-purge .ab-item")
		.addEventListener("click", async (event) => {
			event.preventDefault();
			const container = document.querySelector("#hestia-nginx-cache-admin-notices");
			const oldNotice = container.querySelector(".notice");
			const notice = document.createElement("div");
			notice.classList.add("notice");
			notice.appendChild(document.createElement("p"));

			try {
				const res = await fetch(ajaxurl, {
					method: "POST",
					headers: { "Content-Type": "application/x-www-form-urlencoded" },
					body: new URLSearchParams({
						action: "hestia_nginx_cache_manual_purge",
						wp_nonce: document.querySelector("#hestia-nginx-cache-purge-wp-nonce").textContent,
					}),
				});
				if (!res.ok) {
					notice.classList.add("notice-error");
					notice.querySelector("p").textContent = "An error occured while purging the cache.";
				} else {
					const { success, data } = await res.clone().json();
					notice.classList.add(success ? "notice-success" : "notice-error");
					notice.querySelector("p").textContent = data.message;
				}
			} catch (error) {
				console.error(error);
				notice.classList.add("notice-error");
				notice.querySelector("p").textContent = "An error occured while purging the cache.";
			}

			if (oldNotice) {
				oldNotice.remove();
			}
			container.append(notice);
		});
});
