<?php
// Include necessary files and libraries
include('includes/header.php');
?>

        <!-- Main Content -->
        <main class="flex-grow container mx-auto px-4 py-6 flex flex-col lg:flex-row gap-6">
            <!-- Left Sidebar -->
            <?php
            include('includes/sidebar.php');
            ?>

            <!-- Chat Interface -->
            <div class="flex-grow bg-white rounded-xl shadow-md overflow-hidden flex flex-col">
                <!-- Chat Header -->
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
                
                <!-- Chat Messages -->
                <div class="chat-container overflow-y-auto p-4 space-y-3 flex-grow">
                    <!-- Welcome Message -->
                    <div class="flex">
                        <div class="message bot-message p-4">
                            <div class="flex items-start space-x-2">
                                <div class="w-8 h-8 rounded-full bg-blue-100 flex items-center justify-center">
                                    <i class="fas fa-robot text-blue-600"></i>
                                </div>
                                <div>
                                    <p class="font-bold text-blue-600">NexusAI</p>
                                    <p class="mt-1">Hello! I'm your NexusAI Enterprise Assistant. How can I help you today?</p>
                                    <div class="mt-2 flex flex-wrap gap-2">
                                        <button class="text-xs bg-blue-100 hover:bg-blue-200 text-blue-800 py-1 px-2 rounded-full transition">
                                            Schedule meeting
                                        </button>
                                        <button class="text-xs bg-blue-100 hover:bg-blue-200 text-blue-800 py-1 px-2 rounded-full transition">
                                            Generate report
                                        </button>
                                        <button class="text-xs bg-blue-100 hover:bg-blue-200 text-blue-800 py-1 px-2 rounded-full transition">
                                            CRM query
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- User Message -->
                    <div class="flex justify-end">
                        <div class="message user-message p-4">
                            <div class="flex items-end space-x-2">
                                <div>
                                    <p>Can you show me the Q2 sales figures and compare them to Q1?</p>
                                    <p class="text-xs opacity-80 mt-1 text-right">10:24 AM</p>
                                </div>
                                <div class="w-8 h-8 rounded-full bg-blue-200 flex items-center justify-center">
                                    <i class="fas fa-user text-blue-800"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Bot Response -->
                    <div class="flex">
                        <div class="message bot-message p-4">
                            <div class="flex items-start space-x-2">
                                <div class="w-8 h-8 rounded-full bg-blue-100 flex items-center justify-center">
                                    <i class="fas fa-robot text-blue-600"></i>
                                </div>
                                <div>
                                    <p class="font-bold text-blue-600">NexusAI</p>
                                    <p class="mt-1">Certainly! I've retrieved the sales data from our CRM system via webhook integration.</p>
                                    
                                    <div class="mt-3 bg-gray-50 p-3 rounded-lg border border-gray-200">
                                        <div class="flex justify-between items-center mb-2">
                                            <h4 class="font-semibold text-gray-800">Quarterly Sales Comparison</h4>
                                            <span class="text-xs bg-blue-100 text-blue-800 py-1 px-2 rounded-full">Live Data</span>
                                        </div>
                                        
                                        <div class="grid grid-cols-2 gap-4 text-center">
                                            <div>
                                                <p class="text-sm text-gray-600">Q1 Sales</p>
                                                <p class="text-xl font-bold text-blue-600">$1,250,000</p>
                                            </div>
                                            <div>
                                                <p class="text-sm text-gray-600">Q2 Sales</p>
                                                <p class="text-xl font-bold text-green-600">$1,780,000</p>
                                            </div>
                                        </div>
                                        
                                        <div class="mt-3 pt-3 border-t border-gray-200">
                                            <p class="text-sm">
                                                <span class="font-semibold">42.4% increase</span> from Q1 to Q2. 
                                                The top performing product was <span class="font-semibold">Nexus X200</span> with $520,000 in sales.
                                            </p>
                                        </div>
                                    </div>
                                    
                                    <p class="mt-2">Would you like me to prepare a detailed report or schedule a meeting with the sales team?</p>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Typing Indicator -->
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
                
                <!-- Input Area -->
                <div class="border-t border-gray-200 p-4 bg-gray-50">
                    <div class="flex items-center space-x-2">
                        <button class="w-10 h-10 rounded-full bg-white border border-gray-300 flex items-center justify-center text-gray-600 hover:bg-gray-100 transition">
                            <i class="fas fa-paperclip"></i>
                        </button>
                        <div class="flex-grow relative">
                            <input 
                                type="text" 
                                id="messageInput" 
                                placeholder="Type your message..." 
                                class="w-full py-3 px-4 border border-gray-300 rounded-full focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                            >
                            <button class="absolute right-2 top-1/2 transform -translate-y-1/2 w-8 h-8 rounded-full bg-blue-600 text-white flex items-center justify-center hover:bg-blue-700 transition">
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
                            <span>Webhook connected to CRM</span>
                        </div>
                        <div>
                            <button class="text-blue-600 hover:text-blue-800">AI Suggestions</button>
                            <span class="mx-1">•</span>
                            <button class="text-blue-600 hover:text-blue-800">Templates</button>
                        </div>
                    </div>
                </div>
            </div>
        </main>
    <?php
        // Include footer
    include('includes/footer.php');
    ?>
       
    </div>

    <script>
        // Simple chatbot functionality
        document.addEventListener('DOMContentLoaded', function() {
            const messageInput = document.getElementById('messageInput');
            const typingIndicator = document.getElementById('typingIndicator');
            const chatContainer = document.querySelector('.chat-container');
            
            // Function to add a new message to the chat
            function addMessage(content, isUser = false) {
                const messageDiv = document.createElement('div');
                messageDiv.className = `flex ${isUser ? 'justify-end' : 'justify-start'}`;
                
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
                                <p class="${isUser ? '' : 'mt-1'}">${content}</p>
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
            
            // Function to simulate bot response
            function respondToUser(message) {
                // Show typing indicator
                typingIndicator.style.display = 'flex';
                chatContainer.scrollTop = chatContainer.scrollHeight;
                
                // Hide typing indicator after delay and show response
                setTimeout(() => {
                    typingIndicator.style.display = 'none';
                    
                    // Simple response logic - in a real app this would call an API
                    let response;
                    if (message.toLowerCase().includes('sales') || message.toLowerCase().includes('figures')) {
                        response = "I can provide sales data. Would you like to see:<br><br>" +
                                   "<button class='text-sm bg-blue-100 hover:bg-blue-200 text-blue-800 py-1 px-3 rounded-full mr-2 transition'>Quarterly Report</button>" +
                                   "<button class='text-sm bg-blue-100 hover:bg-blue-200 text-blue-800 py-1 px-3 rounded-full mr-2 transition'>Product Breakdown</button>" +
                                   "<button class='text-sm bg-blue-100 hover:bg-blue-200 text-blue-800 py-1 px-3 rounded-full transition'>Regional Analysis</button>";
                    } else if (message.toLowerCase().includes('meeting') || message.toLowerCase().includes('schedule')) {
                        response = "I can help schedule a meeting. Please specify:<br><br>" +
                                   "<button class='text-sm bg-blue-100 hover:bg-blue-200 text-blue-800 py-1 px-3 rounded-full mr-2 transition'>With the team</button>" +
                                   "<button class='text-sm bg-blue-100 hover:bg-blue-200 text-blue-800 py-1 px-3 rounded-full transition'>With a client</button>";
                    } else {
                        response = "I've processed your request via our enterprise systems. How else can I assist you today?";
                    }
                    
                    addMessage(response);
                }, 1500);
            }
            
            // Handle message submission
            messageInput.addEventListener('keypress', function(e) {
                if (e.key === 'Enter' && messageInput.value.trim() !== '') {
                    const message = messageInput.value.trim();
                    addMessage(message, true);
                    messageInput.value = '';
                    respondToUser(message);
                }
            });
            
            // Webhook simulation - in a real app this would connect to actual webhooks
            function simulateWebhookIntegration() {
                console.log("Simulating webhook connection to enterprise systems...");
                // This is where you would implement actual webhook connections
            }
            
            simulateWebhookIntegration();
        });
    </script>
</body>
</html>