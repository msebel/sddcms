var EFFECT_DURATION = 1000;

// Zeigt einen FAQ an oder blendet ihn aus
function ToggleFaqView(nFaqID) {
	var oFaq = document.getElementById('faq' + nFaqID);
	var oImg = document.getElementById('img' + nFaqID);
	// Toggeln des Divs
	if (oFaq.style.display == 'none') {
		Effect.Appear(oFaq, { duration: EFFECT_DURATION/1000 });
		oImg.className = 'faqUnexpandIcon';
	} else {
		Effect.Fade(oFaq, { duration: EFFECT_DURATION/1000 });
		oImg.className = 'faqExpandIcon';
	}
}

// Gibt auf einem Div den Hand Zeiger aus
function mpHand(oDiv) {
	oDiv.style.cursor = 'pointer';
}

// Gibt wieder den Default Zeigen aus
function mpDefault(oDiv) {
	oDiv.style.cursor = 'default';
}