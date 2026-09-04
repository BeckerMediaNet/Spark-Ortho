import beerslider from 'beerslider';

const BeerSlider = beerslider.BeerSlider || beerslider.default || beerslider;

document.addEventListener('DOMContentLoaded', () => {
	initTransitionSliders();
});

if (typeof wp !== 'undefined' && wp?.data?.subscribe) {
	wp.data.subscribe(() => {
		setTimeout(() => {
			initTransitionSliders();
		}, 150);
	});
}

function initTransitionSliders() {
	const sliders = document.querySelectorAll(
		'.block--transition-slider .beer-slider:not(.beer-ready)'
	);

	sliders.forEach((slider) => {
		const start = slider.getAttribute('data-start') || '50';
		new BeerSlider(slider, { start });

		const observer = new MutationObserver(() => {
			if (slider.classList.contains('beer-ready')) {
				observer.disconnect();
				setupSlider(slider);
			}
		});
		observer.observe(slider, { attributes: true, attributeFilter: ['class'] });
	});
}

function setupSlider(slider) {
	const range  = slider.querySelector('.beer-range');
	const handle = slider.querySelector('.beer-handle');
	const reveal = slider.querySelector('.beer-reveal');
	if (!range || !handle || !reveal) return;

	const beforeLabel   = reveal.getAttribute('data-beer-label') || 'Before';
	const afterLabel    = slider.getAttribute('data-beer-label') || 'After';
	const headingFont   = getComputedStyle(document.documentElement).getPropertyValue('--wp--preset--font-family--heading').trim() || 'sans-serif';
	const beforeLabelPx = measureLabelWidth(beforeLabel, headingFont);
	const afterLabelPx  = measureLabelWidth(afterLabel, headingFont);

	range.setAttribute('aria-label', `Compare ${beforeLabel} and ${afterLabel} images`);
	range.setAttribute('aria-valuetext', getValueText(parseFloat(range.value), beforeLabel, afterLabel));

	function updateLabels(value) {
		const containerWidth = slider.offsetWidth;
		if (!containerWidth) return;
		const handleHalf   = handle.offsetWidth / 2;
		const handleCenter = (value / 100) * containerWidth;
		const insetPx = parseFloat(getComputedStyle(document.documentElement).fontSize) * 1.25;
		const gap     = 8;
		const beforeEdge = insetPx + beforeLabelPx + gap;
		const afterEdge  = containerWidth - insetPx - afterLabelPx - gap;
		slider.classList.toggle('hide-before-label', (handleCenter - handleHalf) < beforeEdge);
		slider.classList.toggle('hide-after-label',  (handleCenter + handleHalf) > afterEdge);
		range.setAttribute('aria-valuetext', getValueText(value, beforeLabel, afterLabel));
	}

	if (window.matchMedia('(pointer: coarse)').matches) {
		// Touch devices: iOS Safari does not reliably allow programmatic range.value
		// modification during touch events, so we bypass the range input entirely
		// and drive the reveal + handle directly from pointer coordinates.
		range.style.pointerEvents = 'none';
		slider.style.touchAction  = 'pan-y';

		let active = false;

		const moveToPointer = (e) => {
			const rect = slider.getBoundingClientRect();
			const cw   = rect.width;
			if (!cw) return;
			const edgePct = (handle.offsetWidth / 2 / cw) * 100;
			let pct = ((e.clientX - rect.left) / cw) * 100;
			pct = Math.max(edgePct, Math.min(100 - edgePct, pct));
			reveal.style.width = pct + '%';
			handle.style.left  = pct + '%';
			range.value = pct;
			range.setAttribute('aria-valuenow', pct);
			updateLabels(pct);
		};

		slider.addEventListener('pointerdown', (e) => {
			active = true;
			try { slider.setPointerCapture(e.pointerId); } catch (_) {}
			moveToPointer(e);
		});

		slider.addEventListener('pointermove', (e) => {
			if (active) moveToPointer(e);
		});

		slider.addEventListener('pointerup',     () => { active = false; });
		slider.addEventListener('pointercancel', () => { active = false; });
	} else {
		const clampEdge = (e) => {
			const containerWidth = slider.offsetWidth;
			if (!containerWidth) return;
			const edgePercent = (handle.offsetWidth / 2 / containerWidth) * 100;
			const val = parseFloat(e.target.value);
			if (val < edgePercent)       e.target.value = edgePercent;
			if (val > 100 - edgePercent) e.target.value = 100 - edgePercent;
		};
		range.addEventListener('input',  clampEdge, { capture: true });
		range.addEventListener('change', clampEdge, { capture: true });
		range.addEventListener('input', () => { updateLabels(parseFloat(range.value)); });
	}

	updateLabels(parseFloat(range.value));
}

function measureLabelWidth(text, fontFamily) {
	const el = document.createElement('span');
	el.setAttribute('aria-hidden', 'true');
	Object.assign(el.style, {
		position:      'absolute',
		visibility:    'hidden',
		pointerEvents: 'none',
		fontSize:      '0.8rem',
		fontWeight:    '600',
		letterSpacing: '0.04em',
		textTransform: 'uppercase',
		padding:       '0.35rem 0.75rem',
		fontFamily,
		whiteSpace:    'nowrap',
	});
	el.textContent = text;
	document.body.appendChild(el);
	const width = el.offsetWidth;
	document.body.removeChild(el);
	return width;
}

function getValueText(value, beforeLabel, afterLabel) {
	if (value <= 10) return `Showing mostly ${afterLabel}`;
	if (value >= 90) return `Showing mostly ${beforeLabel}`;
	return `${Math.round(value)}% ${beforeLabel}, ${Math.round(100 - value)}% ${afterLabel}`;
}
