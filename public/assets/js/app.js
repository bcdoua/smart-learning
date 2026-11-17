/* ============================================================
   SmartLearn Academy — App JS (SB Admin 2 style)
   ============================================================ */

$(document).ready(function () {

    /* ---- Sidebar toggle ---- */
    $('#sidebarToggle, #sidebarToggleTop').on('click', function () {
        $('body').toggleClass('sidebar-toggled');
        $('.sidebar').toggleClass('toggled');
        if ($('.sidebar').hasClass('toggled')) {
            $('.sidebar .collapse').collapse('hide');
        }
    });

    /* ---- Collapse sidebar on small screens ---- */
    $(window).resize(function () {
        if ($(window).width() < 768) {
            $('.sidebar .collapse').collapse('hide');
        }
        if ($(window).width() < 480 && !$('.sidebar').hasClass('toggled')) {
            $('body').addClass('sidebar-toggled');
            $('.sidebar').addClass('toggled');
            $('.sidebar .collapse').collapse('hide');
        }
    });

    /* ---- Scroll to top button ---- */
    $(window).scroll(function () {
        if ($(this).scrollTop() > 100) {
            $('.scroll-to-top').fadeIn();
        } else {
            $('.scroll-to-top').fadeOut();
        }
    });

    $('a.scroll-to-top').on('click', function (e) {
        e.preventDefault();
        $('html, body').animate({ scrollTop: 0 }, 600);
    });

    /* ---- DataTables ---- */
    if ($.fn.DataTable) {
        $('.datatable').DataTable({
            pageLength: 10,
            responsive: true,
            language: {
                url: 'https://cdn.datatables.net/plug-ins/1.13.8/i18n/fr-FR.json'
            }
        });
    }

    /* ---- Admin dashboard chart ---- */
    var chartCanvas = document.getElementById('mainChart');
    if (chartCanvas) {
        var rawValues = {};
        try {
            rawValues = JSON.parse(chartCanvas.dataset.values || '{}');
        } catch (e) {}

        var labels = Object.keys(rawValues).map(function (k) {
            var map = { students: 'Étudiants', teachers: 'Enseignants', courses: 'Cours', absences: 'Absences', exams: 'Examens' };
            return map[k] || k;
        });
        var data = Object.values(rawValues);

        new Chart(chartCanvas, {
            type: 'bar',
            data: {
                labels: labels,
                datasets: [{
                    label: 'Statistiques',
                    data: data,
                    backgroundColor: [
                        'rgba(78,115,223,0.8)',
                        'rgba(28,200,138,0.8)',
                        'rgba(54,185,204,0.8)',
                        'rgba(246,194,62,0.8)',
                        'rgba(231,74,59,0.8)'
                    ],
                    borderColor: [
                        'rgba(78,115,223,1)',
                        'rgba(28,200,138,1)',
                        'rgba(54,185,204,1)',
                        'rgba(246,194,62,1)',
                        'rgba(231,74,59,1)'
                    ],
                    borderWidth: 1,
                    borderRadius: 4
                }]
            },
            options: {
                plugins: { legend: { display: false } },
                scales: {
                    y: { beginAtZero: true, grid: { color: 'rgba(0,0,0,0.05)' } },
                    x: { grid: { display: false } }
                }
            }
        });
    }

    /* ---- AI Chatbot form ---- */
    var aiForm = document.getElementById('aiForm');
    if (aiForm) {
        aiForm.addEventListener('submit', async function (e) {
            e.preventDefault();
            var feed = document.getElementById('aiFeed');
            var textarea = aiForm.querySelector('textarea[name="question"]');
            var question = textarea.value.trim();
            if (!question) return;

            /* Show user bubble */
            feed.insertAdjacentHTML('beforeend',
                '<div class="bubble me"><strong>Moi</strong><p>' + escapeHtml(question) + '</p></div>'
            );
            textarea.value = '';

            /* Loading indicator */
            var loadingId = 'loading-' + Date.now();
            feed.insertAdjacentHTML('beforeend',
                '<div class="bubble ai" id="' + loadingId + '"><strong>SmartLearn IA</strong><p><i class="fas fa-spinner fa-spin"></i> En cours...</p></div>'
            );
            feed.scrollTop = feed.scrollHeight;

            try {
                var formData = new FormData(aiForm);
                formData.set('question', question);
                var response = await fetch(aiForm.dataset.url, {
                    method: 'POST',
                    body: formData,
                    headers: { 'X-Requested-With': 'XMLHttpRequest' }
                });
                var json = await response.json();
                var answer = json.answer || json.error || 'Erreur inconnue.';
                var loadingEl = document.getElementById(loadingId);
                if (loadingEl) {
                    loadingEl.querySelector('p').innerHTML = escapeHtml(answer).replace(/\n/g, '<br>');
                }
            } catch (err) {
                var loadingEl = document.getElementById(loadingId);
                if (loadingEl) {
                    loadingEl.querySelector('p').textContent = 'Erreur de connexion.';
                }
            }
            feed.scrollTop = feed.scrollHeight;
        });
    }

    /* ---- Auto-scroll chat feed ---- */
    var chatFeed = document.getElementById('chatFeed');
    if (chatFeed) {
        chatFeed.scrollTop = chatFeed.scrollHeight;
    }
    var aiFeed = document.getElementById('aiFeed');
    if (aiFeed) {
        aiFeed.scrollTop = aiFeed.scrollHeight;
    }

});

/* ---- HTML escape helper ---- */
function escapeHtml(value) {
    return String(value).replace(/[&<>"']/g, function (m) {
        return { '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#039;' }[m];
    });
}
