<?php include 'includes/header.php'; ?>
<?php include 'includes/sidebar.php'; ?>
<main class="flex-grow">
    <div class="container mx-auto px-4 py-8">
        <h2 class="tech-font text-3xl font-bold mb-6 text-blue-800 flex items-center">
            <i class="fas fa-cog mr-2"></i> Paramètres
        </h2>
        <!-- Formulaire pour ajouter/modifier un utilisateur -->
        <section class="mb-8">
            <div class="bg-white rounded-xl shadow-md p-6">
                <h3 class="tech-font text-xl font-semibold mb-4 text-blue-800" id="form-title">Ajouter un utilisateur</h3>
                <form id="user-form" class="space-y-4">
                    <input type="hidden" id="user-id">
                    <div>
                        <label for="name" class="block text-sm font-medium text-gray-700">Nom</label>
                        <input type="text" id="name" placeholder="Nom" class="border p-2 rounded w-full" required>
                    </div>
                    <div>
                        <label for="email" class="block text-sm font-medium text-gray-700">Email</label>
                        <input type="email" id="email" placeholder="Email" class="border p-2 rounded w-full" required>
                    </div>
                    <button type="submit" class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700" id="form-button">Ajouter utilisateur</button>
                    <button type="button" class="bg-gray-600 text-white px-4 py-2 rounded hover:bg-gray-700 hidden" id="cancel-button">Annuler</button>
                </form>
                <div id="form-message" class="mt-2 text-sm"></div>
            </div>
        </section>
        <!-- Tableau pour afficher les utilisateurs -->
        <section class="mb-8">
            <div class="bg-white rounded-xl shadow-md p-6">
                <h3 class="tech-font text-xl font-semibold mb-4 text-blue-800">Liste des utilisateurs</h3>
                <table id="users-table" class="min-w-full bg-white">
                    <thead>
                        <tr>
                            <th class="py-2 px-4 border-b">Nom</th>
                            <th class="py-2 px-4 border-b">Email</th>
                            <th class="py-2 px-4 border-b">Date de création</th>
                            <th class="py-2 px-4 border-b">Actions</th>
                        </tr>
                    </thead>
                    <tbody id="users-table-body"></tbody>
                </table>
            </div>
        </section>
    </div>
    <?php include 'includes/footer.php'; ?>
</main>

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

    // Charger la liste des utilisateurs
    function loadUsers() {
        fetch('http://localhost:5000/api/users')
            .then(response => {
                if (!response.ok) {
                    throw new Error(`HTTP error! Status: ${response.status}`);
                }
                return response.json();
            })
            .then(data => {
                const formMessage = document.getElementById('form-message');
                if (data.status === 'success') {
                    const tableBody = document.getElementById('users-table-body');
                    tableBody.innerHTML = '';
                    data.data.forEach(user => {
                        const row = document.createElement('tr');
                        row.innerHTML = `
                            <td class="py-2 px-4 border-b">${user.name}</td>
                            <td class="py-2 px-4 border-b">${user.email}</td>
                            <td class="py-2 px-4 border-b">${new Date(user.created_at).toLocaleDateString()}</td>
                            <td class="py-2 px-4 border-b">
                                <button class="edit-button bg-yellow-500 text-white px-2 py-1 rounded hover:bg-yellow-600" data-id="${user.id}" data-name="${user.name}" data-email="${user.email}">Modifier</button>
                                <button class="delete-button bg-red-500 text-white px-2 py-1 rounded hover:bg-red-600" data-id="${user.id}">Supprimer</button>
                            </td>
                        `;
                        tableBody.appendChild(row);
                    });

                    // Ajouter des écouteurs pour les boutons Modifier et Supprimer
                    document.querySelectorAll('.edit-button').forEach(button => {
                        button.addEventListener('click', () => {
                            const id = button.getAttribute('data-id');
                            const name = button.getAttribute('data-name');
                            const email = button.getAttribute('data-email');
                            document.getElementById('user-id').value = id;
                            document.getElementById('name').value = name;
                            document.getElementById('email').value = email;
                            document.getElementById('form-title').textContent = 'Modifier un utilisateur';
                            document.getElementById('form-button').textContent = 'Modifier utilisateur';
                            document.getElementById('cancel-button').classList.remove('hidden');
                        });
                    });

                    document.querySelectorAll('.delete-button').forEach(button => {
                        button.addEventListener('click', () => {
                            const id = button.getAttribute('data-id');
                            if (confirm('Voulez-vous vraiment supprimer cet utilisateur ?')) {
                                fetch(`http://localhost:5000/api/users/${id}`, {
                                    method: 'DELETE'
                                })
                                    .then(response => {
                                        if (!response.ok) {
                                            throw new Error(`HTTP error! Status: ${response.status}`);
                                        }
                                        return response.json();
                                    })
                                    .then(data => {
                                        if (data.status === 'success') {
                                            formMessage.innerHTML = '<span class="text-green-500">Utilisateur supprimé avec succès !</span>';
                                            setTimeout(() => formMessage.innerHTML = '', 3000);
                                            loadUsers();
                                        } else {
                                            formMessage.innerHTML = `<span class="text-red-500">Erreur: ${data.message}</span>`;
                                            setTimeout(() => formMessage.innerHTML = '', 5000);
                                        }
                                    })
                                    .catch(error => {
                                        console.error('Erreur lors de la suppression:', error);
                                        formMessage.innerHTML = `<span class="text-red-500">Erreur: ${error.message}</span>`;
                                        setTimeout(() => formMessage.innerHTML = '', 5000);
                                    });
                            }
                        });
                    });
                } else {
                    console.error('Erreur lors du chargement des utilisateurs:', data.message);
                    formMessage.innerHTML = `<span class="text-red-500">Erreur: ${data.message}</span>`;
                    setTimeout(() => formMessage.innerHTML = '', 5000);
                }
            })
            .catch(error => {
                console.error('Erreur lors du chargement des utilisateurs:', error);
                formMessage.innerHTML = `<span class="text-red-500">Erreur: ${error.message}</span>`;
                setTimeout(() => formMessage.innerHTML = '', 5000);
            });
    }

    // Gérer la soumission du formulaire
    const userForm = document.getElementById('user-form');
    if (userForm) {
        userForm.addEventListener('submit', (e) => {
            e.preventDefault();
            const formMessage = document.getElementById('form-message');
            const id = document.getElementById('user-id').value;
            const name = document.getElementById('name').value;
            const email = document.getElementById('email').value;
            const method = id ? 'PUT' : 'POST';
            const url = id ? `http://localhost:5000/api/users/${id}` : 'http://localhost:5000/api/users';

            fetch(url, {
                method: method,
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ name, email })
            })
                .then(response => {
                    if (!response.ok) {
                        throw new Error(`HTTP error! Status: ${response.status}`);
                    }
                    return response.json();
                })
                .then(data => {
                    if (data.status === 'success') {
                        formMessage.innerHTML = `<span class="text-green-500">${id ? 'Utilisateur modifié' : 'Utilisateur ajouté'} avec succès !</span>`;
                        setTimeout(() => formMessage.innerHTML = '', 3000);
                        userForm.reset();
                        document.getElementById('user-id').value = '';
                        document.getElementById('form-title').textContent = 'Ajouter un utilisateur';
                        document.getElementById('form-button').textContent = 'Ajouter utilisateur';
                        document.getElementById('cancel-button').classList.add('hidden');
                        loadUsers();
                    } else {
                        console.error('Erreur lors de l\'opération:', data.message);
                        formMessage.innerHTML = `<span class="text-red-500">Erreur: ${data.message}</span>`;
                        setTimeout(() => formMessage.innerHTML = '', 5000);
                    }
                })
                .catch(error => {
                    console.error('Erreur lors de l\'opération:', error);
                    formMessage.innerHTML = `<span class="text-red-500">Erreur: ${error.message}</span>`;
                    setTimeout(() => formMessage.innerHTML = '', 5000);
                });
        });
    }

    // Gérer le bouton Annuler
    const cancelButton = document.getElementById('cancel-button');
    if (cancelButton) {
        cancelButton.addEventListener('click', () => {
            userForm.reset();
            document.getElementById('user-id').value = '';
            document.getElementById('form-title').textContent = 'Ajouter un utilisateur';
            document.getElementById('form-button').textContent = 'Ajouter utilisateur';
            cancelButton.classList.add('hidden');
            document.getElementById('form-message').innerHTML = '';
        });
    }

    // Charger les utilisateurs au démarrage
    loadUsers();
</script>
</body>
</html>