<?php
$api_url = 'http://localhost:5000/api/system_statuses';
$system_statuses = [];
$error_message = '';

try {
    $response = @file_get_contents($api_url);
    if ($response === false) {
        $error_message = "Échec de la connexion à l'API Flask. Vérifiez si le serveur Flask est en cours d'exécution.";
        error_log('Échec de la récupération des statuts systèmes depuis ' . $api_url);
    } else {
        $data = json_decode($response, true);
        if ($data === null) {
            $error_message = "Réponse JSON invalide de l'API.";
            error_log('Erreur de décodage JSON depuis ' . $api_url . ': ' . json_last_error_msg());
        } elseif (!isset($data['status']) || $data['status'] !== 'success' || !isset($data['systems'])) {
            $error_message = isset($data['message']) ? $data['message'] : "Réponse API invalide (manque 'status' ou 'systems').";
            error_log('Erreur API: ' . $error_message);
        } else {
            $system_statuses = $data['systems'];
        }
    }
} catch (Exception $e) {
    $error_message = "Erreur: " . $e->getMessage();
    error_log('Erreur lors de la récupération des statuts: ' . $e->getMessage());
}
?>

<aside class="sidebar w-full lg:w-1/4 bg-white rounded-xl shadow-md p-6 mt-20">
    <h2 class="tech-font text-xl font-bold mb-4 text-blue-800 flex items-center">
        <i class="fas fa-robot mr-2"></i> Assistant IA
    </h2>
    <div class="space-y-4">
        <div class="dashboard-card bg-gradient-to-r from-blue-50 to-blue-100 p-4 rounded-lg border border-blue-200 cursor-pointer">
            <h3 class="font-semibold text-blue-800 flex items-center">
                <i class="fas fa-brain mr-2"></i> Base de connaissances
            </h3>
            <p class="text-sm text-gray-600 mt-1">Accéder aux documents et politiques de l'entreprise</p>
        </div>
        <div class="dashboard-card bg-gradient-to-r from-green-50 to-green-100 p-4 rounded-lg border border-green-200 cursor-pointer">
            <h3 class="font-semibold text-green-800 flex items-center">
                <i class="fas fa-calendar-alt mr-2"></i> Planning
            </h3>
            <p class="text-sm text-gray-600 mt-1">Gérer les réunions et rendez-vous</p>
        </div>
        <div class="dashboard-card bg-gradient-to-r from-purple-50 to-purple-100 p-4 rounded-lg border border-purple-200 cursor-pointer">
            <h3 class="font-semibold text-purple-800 flex items-center">
                <i class="fas fa-chart-line mr-2"></i> Analytics
            </h3>
            <p class="text-sm text-gray-600 mt-1">Insights sur la performance de l'entreprise</p>
        </div>
        <div class="dashboard-card bg-gradient-to-r from-yellow-50 to-yellow-100 p-4 rounded-lg border border-yellow-200 cursor-pointer">
            <h3 class="font-semibold text-yellow-800 flex items-center">
                <i class="fas fa-plug mr-2"></i> Intégrations
            </h3>
            <p class="text-sm text-gray-600 mt-1">Connecter avec d'autres outils</p>
        </div>
    </div>
    <div class="mt-8">
        <h3 class="tech-font text-lg font-semibold mb-3 text-blue-800">Statuts des systèmes</h3>
        <div class="space-y-3">
            <?php if (!empty($system_statuses)): ?>
                <?php foreach ($system_statuses as $system): ?>
                    <?php
                    $statusClass = $system['status'] === 'online' ? 'status-online' : ($system['status'] === 'degraded' ? 'status-degraded' : 'status-offline');
                    ?>
                    <div class="flex items-center justify-between p-2 bg-gray-50 rounded">
                        <div class="flex items-center">
                            <span class="system-status-indicator <?php echo htmlspecialchars($statusClass); ?>"></span>
                            <span><?php echo htmlspecialchars($system['system_name']); ?></span>
                        </div>
                        <span class="text-gray-600"><?php echo htmlspecialchars($system['status']); ?></span>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <p class="text-red-400"><?php echo htmlspecialchars($error_message ?: 'Erreur lors du chargement des statuts.'); ?></p>
            <?php endif; ?>
        </div>
    </div>
</aside>