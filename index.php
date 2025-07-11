<?php
// Inclure l'en-tête de la page
include('includes/header.php');
?>

        <?php
        // Inclure la barre latérale
        include('includes/sidebar.php');
        ?>

        <!-- Contenu principal de l'application -->
        <main class="flex-grow container mx-auto px-4 py-6 flex flex-col lg:flex-row gap-6 mt-20">
            <!-- Interface de Chat -->
            <div class="flex-grow bg-white rounded-xl shadow-md overflow-hidden flex flex-col">
                <!-- En-tête du Chat -->
                <div class="bg-gradient-to-r from-blue-600 to-blue-800 text-white p-4 flex items-center justify-between">
                    <div class="flex items-center space-x-3">
                        <div class="w-10 h-10 rounded-full bg-blue-500 flex items-center justify-center glow">
                            <i class="fas fa-robot"></i>
                        </div>
                        <div>
                            <h3 class="font-bold">NexusAI Assistant</h3>
                            <p class="text-xs opacity-80">Enterprise AI • Online</p>
                        </div>
                    </div>
                    <div class="flex space-x-2">
                        <button class="w-8 h-8 rounded-full bg-blue-700 hover:bg-blue-600 flex items-center justify-center transition">
                            <i class="fas fa-phone-alt text-xs"></i>
                        </button>
                        <button class="w-8 h-8 rounded-full bg-blue-700 hover:bg-blue-600 flex items-center justify-center transition">
                            <i class="fas fa-video text-xs"></i>
                        </button>
                        <button class="w-8 h-8 rounded-full bg-blue-700 hover:bg-blue-600 flex items-center justify-center transition">
                            <i class="fas fa-ellipsis-h text-xs"></i>
                        </button>
                    </div>
                </div>
                
                <!-- Messages du Chat -->
                <div class="chat-container overflow-y-auto p-4 space-y-3 flex-grow">
                    <!-- Message de bienvenue initial -->
                    <div class="flex">
                        <div class="message bot-message p-4">
                            <div class="flex items-start space-x-2">
                                <div class="w-8 h-8 rounded-full bg-blue-100 flex items-center justify-center">
                                    <i class="fas fa-robot text-blue-600"></i>
                                </div>
                                <div>
                                    <p class="font-bold text-blue-600">NexusAI</p>
                                    <p class="mt-1">Bonjour ! Je suis votre assistant d'entreprise NexusAI. Comment puis-je vous aider aujourd'hui ?</p>
                                    <div class="mt-2 flex flex-wrap gap-2">
                                        <button class="text-xs bg-blue-100 hover:bg-blue-200 text-blue-800 py-1 px-2 rounded-full transition">
                                            Planifier une réunion
                                        </button>
                                        <button class="text-xs bg-blue-100 hover:bg-blue-200 text-blue-800 py-1 px-2 rounded-full transition">
                                            Générer un rapport
                                        </button>
                                        <button class="text-xs bg-blue-100 hover:bg-blue-200 text-blue-800 py-1 px-2 rounded-full transition">
                                            Requête CRM
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Indicateur de saisie (Typing Indicator) -->
                    <div class="flex" id="typingIndicator" style="display: none;">
                        <div class="message bot-message p-4">
                            <div class="flex items-start space-x-2">
                                <div class="w-8 h-8 rounded-full bg-blue-100 flex items-center justify-center">
                                    <i class="fas fa-robot text-blue-600"></i>
                                </div>
                                <div>
                                    <p class="font-bold text-blue-600">NexusAI</p>
                                    <div class="typing-indicator mt-1">
                                        <span></span>
                                        <span></span>
                                        <span></span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Zone de saisie -->
                <div class="border-t border-gray-200 p-4 bg-gray-50">
                    <div class="flex items-center space-x-2">
                        <button class="w-10 h-10 rounded-full bg-white border border-gray-300 flex items-center justify-center text-gray-600 hover:bg-gray-100 transition">
                            <i class="fas fa-paperclip"></i>
                        </button>
                        <div class="flex-grow relative">
                            <input 
                                type="text" 
                                id="messageInput" 
                                placeholder="Tapez votre message..." 
                                class="w-full py-3 px-4 border border-gray-300 rounded-full focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                            >
                            <button id="sendMessageBtn" class="absolute right-2 top-1/2 transform -translate-y-1/2 w-8 h-8 rounded-full bg-blue-600 text-white flex items-center justify-center hover:bg-blue-700 transition">
                                <i class="fas fa-paper-plane text-sm"></i>
                            </button>
                        </div>
                        <button class="w-10 h-10 rounded-full bg-blue-600 text-white flex items-center justify-center hover:bg-blue-700 transition glow">
                            <i class="fas fa-microphone"></i>
                        </button>
                    </div>
                    
                    <div class="mt-3 flex justify-between items-center text-xs text-gray-500">
                        <div class="flex items-center">
                            <span class="webhook-status webhook-active"></span>
                            <span>Webhook connecté au CRM</span>
                        </div>
                        <div>
                            <button class="text-blue-600 hover:text-blue-800">Suggestions IA</button>
                            <span class="mx-1">•</span>
                            <button class="text-blue-600 hover:text-blue-800">Modèles</button>
                        </div>
                    </div>
                </div>
            </div>
        </main>
    </div> <!-- Ferme le conteneur principal de la mise en page -->

<?php
// Inclure le pied de page
include('includes/footer.php');
?>
        
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const messageInput = document.getElementById('messageInput');
        const typingIndicator = document.getElementById('typingIndicator');
        const chatContainer = document.querySelector('.chat-container');
        const sendMessageBtn = document.getElementById('sendMessageBtn'); 
        const mobileMenuButton = document.getElementById('mobileMenuButton');
        const sidebar = document.getElementById('sidebar');
        const sidebarOverlay = document.getElementById('sidebarOverlay');

        let chatHistory = [
            // NOUVEAU PROMPT SYSTÈME
            { role: "system", content: "Vous êtes un assistant d'entreprise IA utile et amical nommé NexusAI. Vous pouvez fournir des informations, générer des rapports, planifier des réunions et vous intégrer aux systèmes CRM/ERP. Avant d'effectuer toute action nécessitant des informations spécifiques (comme la création d'une tâche de projet, l'envoi d'un e-mail ou la planification d'une réunion), assurez-vous toujours d'avoir tous les détails nécessaires de l'utilisateur. Si des informations obligatoires sont manquantes, posez des questions de clarification à l'utilisateur pour les obtenir avant de tenter d'utiliser un outil. Soyez concis et professionnel." }
        ];

        function addMessage(content, isUser = false) {
            const messageDiv = document.createElement('div');
            messageDiv.className = `flex ${isUser ? 'justify-end' : 'justify-start'}`;

            const tempDiv = document.createElement('div');
            tempDiv.innerHTML = content;
            const parsedContent = tempDiv.innerHTML;

            const messageContent = `
                <div class="message ${isUser ? 'user-message' : 'bot-message'} p-4">
                    <div class="flex items-${isUser ? 'end' : 'start'} space-x-2">
                        ${!isUser ? `
                            <div class="w-8 h-8 rounded-full bg-blue-100 flex items-center justify-center">
                                <i class="fas fa-robot text-blue-600"></i>
                            </div>
                        ` : ''}
                        <div>
                            ${!isUser ? '<p class="font-bold text-blue-600">NexusAI</p>' : ''}
                            <p class="${isUser ? '' : 'mt-1'}">${parsedContent}</p>
                            <p class="text-xs opacity-80 mt-1 ${isUser ? 'text-right' : ''}">
                                ${new Date().toLocaleTimeString([], {hour: '2-digit', minute:'2-digit'})}
                            </p>
                        </div>
                        ${isUser ? `
                            <div class="w-8 h-8 rounded-full bg-blue-200 flex items-center justify-center">
                                <i class="fas fa-user text-blue-800"></i>
                            </div>
                        ` : ''}
                    </div>
                </div>
            `;

            messageDiv.innerHTML = messageContent;
            chatContainer.appendChild(messageDiv);
            chatContainer.scrollTop = chatContainer.scrollHeight;
        }

        async function respondToUser(message) {
            chatHistory.push({ role: "user", content: message });

            typingIndicator.style.display = 'flex';
            chatContainer.scrollTop = chatContainer.scrollHeight;

            try {
                const response = await fetch('http://localhost:5000/api/chat', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify({ messages: chatHistory })
                });

                if (!response.ok) {
                    const errorData = await response.json();
                    console.error('Réponse d\'erreur du serveur Flask:', errorData.reply || response.statusText);
                    throw new Error(`Erreur HTTP: ${response.status}`);
                }

                const data = await response.json();

                typingIndicator.style.display = 'none';

                if (data.reply) {
                    addMessage(data.reply);
                    chatHistory.push({ role: "assistant", content: data.reply });
                } else {
                    addMessage("Désolé, je n'ai pas pu obtenir une réponse de l'IA.", false);
                }

            } catch (error) {
                console.error('Erreur lors de la communication avec l\'IA (Flask):', error);
                typingIndicator.style.display = 'none';
                addMessage("Désolé, une erreur est survenue. Veuillez réessayer plus tard.", false);
            }
        }

        async function fetchSystemStatuses() {
            try {
                const response = await fetch('http://localhost:5000/api/system_statuses');
                if (!response.ok) {
                    throw new Error(`Erreur HTTP: ${response.status}`);
                }
                const data = await response.json();
                
                if (data.status === "success" && data.systems) {
                    data.systems.forEach(system => {
                        const systemIdMap = {
                            "CRM System": "crm",
                            "ERP System": "erp",
                            "HR Platform": "hr"
                        };
                        const htmlId = systemIdMap[system.system_name];
                        
                        if (htmlId) {
                            const statusElement = document.getElementById(`webhook-${htmlId}-status`);
                            
                            if (statusElement) {
                                statusElement.classList.remove('webhook-active', 'webhook-inactive');
                                
                                if (system.status === 'online') {
                                    statusElement.classList.add('webhook-active');
                                } else if (system.status === 'offline' || system.status === 'degraded') {
                                    statusElement.classList.add('webhook-inactive');
                                }
                            }
                        }
                    });
                } else {
                    console.error('Erreur lors de la récupération des statuts des systèmes:', data.message || 'Données invalides.');
                }
            } catch (error) {
                console.error('Erreur lors du chargement des statuts des systèmes:', error);
            }
        }

        if (sendMessageBtn) {
            sendMessageBtn.addEventListener('click', function() {
                const message = messageInput.value.trim();
                if (message !== '') {
                    addMessage(message, true);
                    messageInput.value = '';
                    respondToUser(message);
                }
            });
        } else {
            console.error("Erreur: Le bouton d'envoi de message (sendMessageBtn) n'a pas été trouvé dans le DOM.");
        }

        if (messageInput) {
            messageInput.addEventListener('keypress', function(e) {
                if (e.key === 'Enter' && messageInput.value.trim() !== '') {
                    if (sendMessageBtn) {
                        sendMessageBtn.click();
                    } else {
                        console.error("Erreur: Impossible de simuler le clic sur le bouton d'envoi car il n'a pas été trouvé.");
                    }
                }
            });
        } else {
            console.error("Erreur: Le champ de saisie de message (messageInput) n'a pas été trouvé dans le DOM.");
        }

        if (mobileMenuButton && sidebar) {
            if (!sidebarOverlay) {
                const overlay = document.createElement('div');
                overlay.id = 'sidebarOverlay';
                overlay.className = 'fixed inset-0 bg-black opacity-50 z-0 hidden lg:hidden';
                document.body.appendChild(overlay);
                sidebarOverlay = document.getElementById('sidebarOverlay');
            }

            mobileMenuButton.addEventListener('click', function() {
                sidebar.classList.toggle('-translate-x-full');
                sidebar.classList.toggle('open');
                sidebarOverlay.classList.toggle('hidden');
            });

            sidebarOverlay.addEventListener('click', function() {
                sidebar.classList.add('-translate-x-full');
                sidebar.classList.remove('open');
                sidebarOverlay.classList.add('hidden');
            });
        } else {
            console.warn("Certains éléments du menu mobile (bouton ou sidebar) sont manquants.");
        }

        console.log("Simulation de la connexion webhook aux systèmes d'entreprise...");
        
        fetchSystemStatuses(); 
    });
</script>
