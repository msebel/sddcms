// Sortiert die Felder absteigend
function updateSort() {
	var els = document.getElementsByName("sort[]");
	for (i = 0;i < els.length;i++) {
		els[i].value = (i + 1);
	}
}