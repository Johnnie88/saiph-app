/**
 * Tab-based client-side navigation.
 *
 * Usage:
 *  - The container should contain links to tabs, with the `tab-link` class. They should use anchor links to the tab content elements.
 *  - On the page, tab content elements should have the `tab-content` class, and an ID that matches the `href` of the tab link.
 *
 * The `current` class will be added to the active tab link and tab content element. If the current #anchor doesn't match a tab, the first tab will be active.
 *
 *  @example
 *  ```
 *  <div class="tabs">
 *      <a href="#tab-1" class="tab-link">Tab 1</a>
 *      <a href="#tab-2" class="tab-link">Tab 2</a>
 *  </div>
 *  <div id="tab-1" class="tab-content">Tab 1 content</div>
 *  <div id="tab-2" class="tab-content">Tab 2 content</div>
 *
 *  setupTabNavigation( '.tabs' );
 *  ```
 *
 * @param {string} selector Container element for tab links.
 */
export function setupTabNavigation( selector ) {
	const tabLinks = Array.from(
		document.querySelector( selector ).querySelectorAll( '.tab-link' )
	);

	const tabs = tabLinks.map( ( tabLink ) => ( {
		link: tabLink,
		content: document.querySelector( tabLink.getAttribute( 'href' ) ),
	} ) );

	function setTab( event ) {
		const hash = location.hash.replace( /^#/, '' );

		const active =
			tabs.find( ( { content } ) => content.id === hash ) ?? tabs[ 0 ];

		tabs.forEach( ( tab ) => {
			tab.content.classList.toggle( 'current', tab === active );
			tab.link.classList.toggle( 'current', tab === active );
		} );

		event?.preventDefault();
	}

	setTab();

	// on location change
	window.addEventListener( 'hashchange', setTab );
}
