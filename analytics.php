<?php include 'includes/header.php'; ?>
<?php include 'includes/sidebar.php'; ?>
<main class="flex-grow">
    <div class="container mx-auto px-4 py-8">
        <h2 class="tech-font text-3xl font-bold mb-6 text-blue-800 flex items-center">
            <i class="fas fa-chart-line mr-2"></i> Analytics
        </h2>
        <!-- Message pour erreurs ou informations -->
        <div id="analytics-message" class="mb-4 text-sm text-red-500 hidden"></div>
        <!-- Section pour les tickets -->
        <section class="mb-8">
            <div class="bg-white rounded-xl shadow-md p-6">
                <h3 class="tech-font text-xl font-semibold mb-4 text-blue-800">Statuts des Tickets</h3>
                <canvas id="tickets-chart" width="400" height="200"></canvas>
            </div>
        </section>
        <!-- Section pour les ventes (placeholder) -->
        <section class="mb-8">
            <div class="bg-white rounded-xl shadow-md p-6">
                <h3 class="tech-font text-xl font-semibold mb-4 text-blue-800">Statistiques des Ventes</h3>
                <p id="sales-message" class="text-gray-600">Données des ventes non disponibles (endpoint non implémenté).</p>
                <!-- <canvas id="sales-chart" width="400" height="200"></canvas> -->
            </div>
        </section>
    </div>
    <?php include 'includes/footer.php'; ?>
</main>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    // Gestion du menu mobile
    const mobileMenuButton = document.getElementById('mobileMenuButton');
    const sidebar = document.querySelector('.sidebar');
    let overlay = document.getElementById('sidebarOverlay');

    if (mobileMenuButton && sidebar) {
        if (!overlay) {
            overlay = document.createElement('div');
            overlay.id = 'sidebarOverlay';
            overlay.className = 'hidden lg:hidden';
            document.body.appendChild(overlay);
        }

        mobileMenuButton.addEventListener('click', () => {
            sidebar.classList.toggle('open');
            overlay.classList.toggle('hidden');
        });

        overlay.addEventListener('click', () => {
            sidebar.classList.remove('open');
            overlay.classList.add('hidden');
        });
    }

    // Charger les données des tickets
    function loadTicketsChart() {
        const messageDiv = document.getElementById('analytics-message');
        fetch('http://localhost:5000/api/tickets')
            .then(response => {
                if (!response.ok) {
                    throw new Error(`HTTP error! Status: ${response.status}`);
                }
                return response.json();
            })
            .then(data => {
                if (data.status === 'success') {
                    const ctx = document.getElementById('tickets-chart').getContext('2d');
                    new Chart(ctx, {
                        type: 'doughnut',
                        data: {
                            labels: ['Ouverts', 'En cours', 'Fermés'],
                            datasets: [{
                                label: 'Statuts des Tickets',
                                data: [
                                    data.data.open || 0,
                                    data.data.in_progress || 0,
                                    data.data.closed || 0
                                ],
                                backgroundColor: ['#36A2EB', '#FFCE56', '#4BC0C0'],
                                borderColor: ['#ffffff', '#ffffff', '#ffffff'],
                                borderWidth: 1
                            }]
                        },
                        options: {
                            responsive: true,
                            plugins: {
                                legend: { position: 'top' }
                            }
                        }
                    });
                    messageDiv.classList.add('hidden');
                } else {
                    console.error('Erreur lors du chargement des tickets:', data.message);
                    messageDiv.classList.remove('hidden');
                    messageDiv.textContent = `Erreur: ${data.message}`;
                }
            })
            .catch(error => {
                console.error('Erreur lors du chargement des tickets:', error);
                messageDiv.classList.remove('hidden');
                messageDiv.textContent = `Erreur: ${error.message}`;
            });
    }

    // Charger les données des ventes (placeholder)
    function loadSalesChart() {
        const messageDiv = document.getElementById('sales-message');
        messageDiv.textContent = 'Données des ventes non disponibles (endpoint non implémenté).';
        // TODO: Implémenter lorsque l'endpoint /api/sales est disponible
    }

    // Charger les graphiques au démarrage
    loadTicketsChart();
    loadSalesChart();
</script>
</body>
</html>