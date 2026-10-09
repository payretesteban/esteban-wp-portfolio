/**
 * Contact form modal + Cal.com booking modal. Vanilla JS, no dependencies.
 * Config comes from wp_localize_script as window.estebanModals.
 */
( function () {
	'use strict';

	const cfg = window.estebanModals || {};
	const dialog = document.getElementById( 'ep-contact' );
	if ( ! dialog ) {
		return;
	}
	const form = dialog.querySelector( '.ep-form' );
	const statusEl = form.querySelector( '.ep-form__status' );
	const submitBtn = form.querySelector( '.ep-form__submit' );
	const tabs = Array.from( dialog.querySelectorAll( '[role="tab"]' ) );
	let lastTrigger = null;

	/* ---------- Contact modal ---------- */

	function openContact( trigger ) {
		lastTrigger = trigger || document.activeElement;
		if ( typeof dialog.showModal === 'function' ) {
			dialog.showModal();
		} else {
			dialog.setAttribute( 'open', '' );
		}
		document.documentElement.classList.add( 'ep-modal-open' );
		const first = form.querySelector( 'input:not([type="hidden"]):not([tabindex="-1"])' );
		if ( first ) {
			first.focus();
		}
	}

	function closeContact() {
		if ( dialog.open ) {
			dialog.close();
		}
	}

	dialog.addEventListener( 'close', function () {
		document.documentElement.classList.remove( 'ep-modal-open' );
		if ( window.location.hash === '#contact-form' ) {
			history.replaceState( null, '', window.location.pathname + window.location.search );
		}
		if ( lastTrigger && typeof lastTrigger.focus === 'function' ) {
			lastTrigger.focus();
		}
	} );

	// Click on the backdrop (outside the panel) closes.
	dialog.addEventListener( 'click', function ( e ) {
		if ( e.target === dialog ) {
			closeContact();
		}
	} );
	dialog.querySelectorAll( '[data-ep-close]' ).forEach( function ( btn ) {
		btn.addEventListener( 'click', closeContact );
	} );

	// Any link to #contact-form or a Button block with .ep-contact-trigger opens the modal.
	document.addEventListener( 'click', function ( e ) {
		const link = e.target.closest( 'a[href$="#contact-form"], .ep-contact-trigger a, .ep-contact-trigger button' );
		if ( link ) {
			e.preventDefault();
			openContact( link );
		}
	} );
	if ( window.location.hash === '#contact-form' ) {
		openContact();
	}

	/* ---------- Tabs: consulting vs full-time ---------- */

	function selectTab( tab ) {
		tabs.forEach( function ( t ) {
			const selected = t === tab;
			const panel = document.getElementById( t.getAttribute( 'aria-controls' ) );
			t.setAttribute( 'aria-selected', String( selected ) );
			t.tabIndex = selected ? 0 : -1;
			panel.hidden = ! selected;
			panel.disabled = ! selected; // Disabled fieldsets are not submitted.
		} );
		form.elements.inquiry.value = tab.dataset.inquiry;
	}
	tabs.forEach( function ( tab, i ) {
		tab.addEventListener( 'click', function () {
			selectTab( tab );
		} );
		tab.addEventListener( 'keydown', function ( e ) {
			if ( e.key !== 'ArrowRight' && e.key !== 'ArrowLeft' ) {
				return;
			}
			const next = tabs[ ( i + ( e.key === 'ArrowRight' ? 1 : -1 ) + tabs.length ) % tabs.length ];
			next.focus();
			selectTab( next );
		} );
	} );

	/* ---------- Validation + submit ---------- */

	function setFieldError( name, message ) {
		const input = form.elements[ name ];
		const err = document.getElementById( 'ep-f-' + name + '-error' );
		if ( ! input || ! err ) {
			return;
		}
		input.setAttribute( 'aria-invalid', message ? 'true' : 'false' );
		if ( message ) {
			input.setAttribute( 'aria-describedby', err.id );
		} else {
			input.removeAttribute( 'aria-describedby' );
		}
		err.textContent = message || '';
		err.hidden = ! message;
	}

	function validate() {
		const errors = {};
		const name = form.elements.name.value.trim();
		const email = form.elements.email.value.trim();
		const message = form.elements.message.value.trim();
		if ( ! name ) {
			errors.name = 'Please enter your name.';
		}
		if ( ! /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test( email ) ) {
			errors.email = 'Please enter a valid email address.';
		}
		if ( message.length < 10 ) {
			errors.message = 'Please write a message (at least 10 characters).';
		}
		[ 'name', 'email', 'message' ].forEach( function ( f ) {
			setFieldError( f, errors[ f ] );
		} );
		return errors;
	}

	function setStatus( text, type ) {
		statusEl.textContent = text || '';
		statusEl.dataset.type = type || '';
	}

	form.addEventListener( 'submit', async function ( e ) {
		e.preventDefault();
		const errors = validate();
		const firstError = Object.keys( errors )[ 0 ];
		if ( firstError ) {
			form.elements[ firstError ].focus();
			return;
		}

		const payload = {};
		new FormData( form ).forEach( function ( value, key ) {
			payload[ key ] = typeof value === 'string' ? value.trim() : value;
		} );

		submitBtn.disabled = true;
		setStatus( cfg.i18n.sending, 'pending' );

		try {
			const res = await fetch( cfg.endpoint, {
				method: 'POST',
				headers: { 'Content-Type': 'application/json' },
				body: JSON.stringify( payload ),
			} );
			const json = await res.json().catch( function () {
				return {};
			} );
			if ( ! res.ok ) {
				const fields = json && json.data && json.data.fields;
				if ( fields ) {
					Object.keys( fields ).forEach( function ( f ) {
						setFieldError( f, fields[ f ] );
					} );
				}
				throw new Error( ( json && json.message ) || cfg.i18n.error );
			}
			form.reset();
			selectTab( tabs[ 0 ] );
			form.classList.add( 'is-sent' );
			setStatus( cfg.i18n.success, 'success' );
		} catch ( err ) {
			setStatus( err.message || cfg.i18n.error, 'error' );
		} finally {
			submitBtn.disabled = false;
		}
	} );

	/* ---------- Cal.com booking modal (official embed) ---------- */

	function isDarkBackground() {
		const m = getComputedStyle( document.body ).backgroundColor.match( /\d+(\.\d+)?/g );
		if ( ! m ) {
			return true;
		}
		const lum = ( 0.2126 * m[ 0 ] + 0.7152 * m[ 1 ] + 0.0722 * m[ 2 ] ) / 255;
		return lum < 0.5;
	}

	const calLink = cfg.calLink;
	const calSelector = '.ep-cal-trigger a, [data-ep-book]';
	if ( calLink && document.querySelector( calSelector ) ) {
		const ns = 'ep-booking';
		const embedSrc = 'https://app.cal.com/embed/embed.js';
		let embedFailed = false;

		// Cal.com embed loader (from Cal's docs), loads embed.js on demand.
		/* eslint-disable */
		( function ( C, A, L ) { let p = function ( a, ar ) { a.q.push( ar ); }; let d = C.document; C.Cal = C.Cal || function () { let cal = C.Cal; let ar = arguments; if ( ! cal.loaded ) { cal.ns = {}; cal.q = cal.q || []; d.head.appendChild( d.createElement( 'script' ) ).src = A; cal.loaded = true; } if ( ar[ 0 ] === L ) { const api = function () { p( api, arguments ); }; const namespace = ar[ 1 ]; api.q = api.q || []; if ( typeof namespace === 'string' ) { cal.ns[ namespace ] = cal.ns[ namespace ] || api; p( cal.ns[ namespace ], ar ); p( cal, [ 'initNamespace', namespace ] ); } else p( cal, ar ); return; } p( cal, ar ); }; } )( window, embedSrc, 'init' );
		/* eslint-enable */

		window.Cal( 'init', ns, { origin: 'https://cal.com' } );
		window.Cal.ns[ ns ]( 'ui', {
			theme: isDarkBackground() ? 'dark' : 'light',
			hideEventTypeDetails: false,
			layout: 'month_view',
			cssVarsPerTheme: {
				dark: { 'cal-brand': '#38bdf8' },
				light: { 'cal-brand': '#0284c7' },
			},
		} );

		// If the embed script can't load (blocked, offline), fall back to the Cal page in a new tab.
		const embedScript = document.querySelector( 'script[src="' + embedSrc + '"]' );
		if ( embedScript ) {
			embedScript.addEventListener( 'error', function () {
				embedFailed = true;
			} );
		}

		// We open the modal ourselves (instead of data-cal-link) so we can stop the
		// link's default navigation — otherwise the browser also follows the href to cal.com.
		document.addEventListener( 'click', function ( e ) {
			const trigger = e.target.closest( calSelector );
			if ( ! trigger ) {
				return;
			}
			e.preventDefault();
			closeContact();
			if ( embedFailed ) {
				window.open( 'https://cal.com/' + calLink, '_blank', 'noopener' );
				return;
			}
			window.Cal.ns[ ns ]( 'modal', {
				calLink: calLink,
				config: { layout: 'month_view', theme: isDarkBackground() ? 'dark' : 'light' },
			} );
		} );
	}
} )();
