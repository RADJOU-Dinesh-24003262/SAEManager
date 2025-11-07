(function () {
    function escapeHtml(s) {
        return String(s || '').replace(/[&<>"']/g, function (m) {
            return {'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'}[m];
        });
    }

    document.addEventListener('click', function (e) {
        var a = e.target.closest('a');
        if (!a) return;
        var href = a.getAttribute('href') || '';
        var match = href.match(/^\/sae\/(\d+)$/);
        if (!match) return;
        e.preventDefault();
        var id = match[1];

        fetch('/sae/' + id, {
            method: 'GET',
            headers: { 'Accept': 'application/json' },
            credentials: 'same-origin'
        }).then(function (res) {
            if (!res.ok) throw new Error('Network response was not ok');
            return res.json();
        }).then(function (data) {
            var container = document.getElementById('sae-content');
            if (!container) return;
            var comps = '';
            if (Array.isArray(data.competences) && data.competences.length) {
                comps = '<h4>Compétences</h4><ul>' + data.competences.map(function (c) {
                    return '<li>' + escapeHtml(c) + '</li>';
                }).join('') + '</ul>';
            }
            var renduLink = data.rendu ? '<p><a href="' + escapeHtml(data.rendu) + '" target="_blank">Lien rendu</a></p>' : '';
            container.innerHTML = '<article class="sae-detail">' +
                '<h3>' + escapeHtml(data.subject_name || 'Sujet') + '</h3>' +
                '<p><strong>Début :</strong> ' + escapeHtml(data.begin_date || '') + '</p>' +
                '<p><strong>Fin :</strong> ' + escapeHtml(data.end_date || '') + '</p>' +
                '<p>' + escapeHtml(data.description || '') + '</p>' +
                renduLink +
                comps +
                '</article>';
            // Optionnel : mettre à jour l'historique du navigateur
            history.replaceState(null, '', '/sae/' + id);
        }).catch(function (err) {
            console.error('Erreur lors du chargement de la SAE :', err);
        });
    });
})();