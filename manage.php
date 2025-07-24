<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Short Links</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        body { font-family: 'Inter', sans-serif; }
        /* Custom styles for modal */
        .modal {
            display: none; /* Hidden by default */
            position: fixed; /* Stay in place */
            z-index: 1000; /* Sit on top */
            left: 0;
            top: 0;
            width: 100%; /* Full width */
            height: 100%; /* Full height */
            overflow: auto; /* Enable scroll if needed */
            background-color: rgba(0,0,0,0.4); /* Black w/ opacity */
            justify-content: center;
            align-items: center;
        }
        .modal-content {
            background-color: #fefefe;
            margin: auto;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            max-width: 500px;
            width: 90%;
            text-align: center;
        }
        .modal-buttons {
            display: flex;
            justify-content: center;
            gap: 10px;
            margin-top: 20px;
        }
    </style>
</head>
<body class="bg-gradient-to-br from-blue-500 to-purple-600 min-h-screen flex items-center justify-center p-4">

    <div id="loginPanel" class="bg-white p-8 rounded-xl shadow-2xl w-full max-w-md transform transition-all duration-300 hover:scale-105">
        <h1 class="text-3xl font-extrabold text-gray-800 mb-6 text-center">Admin Login</h1>
        <form id="loginForm" class="space-y-4">
            <div>
                <label for="password" class="block text-gray-700 text-sm font-semibold mb-2">Password:</label>
                <input type="password" id="password" name="password" placeholder="Enter management password" required
                       class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500 transition duration-200">
            </div>
            <button type="submit"
                    class="w-full bg-gradient-to-r from-green-500 to-teal-600 text-white font-bold py-3 px-4 rounded-lg shadow-md hover:from-green-600 hover:to-teal-700 transition duration-300 ease-in-out transform hover:-translate-y-1">
                Login
            </button>
        </form>
        <div id="loginMessageBox" class="mt-4 p-3 rounded-lg text-center hidden" role="alert"></div>
    </div>

    <div id="managePanel" class="bg-white p-8 rounded-xl shadow-2xl w-full max-w-4xl hidden">
        <div class="flex justify-between items-center mb-6">
            <h1 class="text-3xl font-extrabold text-gray-800">Manage Short Links</h1>
            <button id="logoutButton" class="bg-red-500 hover:bg-red-600 text-white font-bold py-2 px-4 rounded-lg shadow-md transition duration-300">
                Logout
            </button>
        </div>

        <div id="addLinkSection" class="mb-6 p-4 border border-gray-200 rounded-lg bg-gray-50">
            <h2 class="text-xl font-bold text-gray-800 mb-4">Add New Short Link</h2>
            <form id="addLinkForm" class="space-y-3">
                <div>
                    <label for="newLongUrl" class="block text-gray-700 text-sm font-semibold mb-1">Long URL:</label>
                    <input type="url" id="newLongUrl" placeholder="https://example.com/new/long/url" required
                           class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500">
                </div>
                <div>
                    <label for="newCustomCode" class="block text-gray-700 text-sm font-semibold mb-1">Custom Code (Optional):</label>
                    <input type="text" id="newCustomCode" placeholder="my-new-link"
                           class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500">
                    <p class="text-xs text-gray-500 mt-1">Leave empty for a random code.</p>
                </div>
                <button type="submit"
                        class="w-full bg-blue-500 hover:bg-blue-600 text-white font-bold py-2 px-4 rounded-lg shadow-md transition duration-300">
                    Add Link
                </button>
            </form>
        </div>

        <div id="manageMessageBox" class="mb-4 p-3 rounded-lg text-center hidden" role="alert"></div>

        <div class="overflow-x-auto">
            <table class="min-w-full bg-white border border-gray-200 rounded-lg shadow-sm">
                <thead>
                    <tr class="bg-gray-100 text-left text-gray-600 uppercase text-sm leading-normal">
                        <th class="py-3 px-6">Short Code</th>
                        <th class="py-3 px-6">Long URL</th>
                        <th class="py-3 px-6">Hits</th>
                        <th class="py-3 px-6">Created At</th>
                        <th class="py-3 px-6 text-center">Actions</th>
                    </tr>
                </thead>
                <tbody id="linksTableBody" class="text-gray-700 text-sm font-light">
                    <!-- Links will be loaded here by JavaScript -->
                </tbody>
            </table>
        </div>
        <div class="mt-6 text-center">
            <a href="index.php" class="text-blue-600 hover:underline text-sm">Go back to Shortener</a>
        </div>
    </div>

    <!-- Confirmation Modal -->
    <div id="confirmModal" class="modal">
        <div class="modal-content">
            <p id="modalMessage" class="text-lg font-semibold text-gray-800 mb-4"></p>
            <div class="modal-buttons">
                <button id="confirmYes" class="bg-green-500 hover:bg-green-600 text-white font-bold py-2 px-4 rounded-lg transition duration-300">Yes</button>
                <button id="confirmNo" class="bg-red-500 hover:bg-red-600 text-white font-bold py-2 px-4 rounded-lg transition duration-300">No</button>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const loginPanel = document.getElementById('loginPanel');
            const managePanel = document.getElementById('managePanel');
            const loginForm = document.getElementById('loginForm');
            const passwordInput = document.getElementById('password');
            const loginMessageBox = document.getElementById('loginMessageBox');
            const manageMessageBox = document.getElementById('manageMessageBox');
            const linksTableBody = document.getElementById('linksTableBody');
            const logoutButton = document.getElementById('logoutButton');
            const addLinkForm = document.getElementById('addLinkForm');
            const newLongUrlInput = document.getElementById('newLongUrl');
            const newCustomCodeInput = document.getElementById('newCustomCode');

            const confirmModal = document.getElementById('confirmModal');
            const modalMessage = document.getElementById('modalMessage');
            const confirmYes = document.getElementById('confirmYes');
            const confirmNo = document.getElementById('confirmNo');

            let currentActionCallback = null; // To store the callback for modal confirmation

            // Base URL for shortened links (fetch from PHP config if possible, or hardcode)
            // This should match the BASE_URL defined in config.php
            const BASE_URL = "http://yourdomain.com/"; // IMPORTANT: Update this to your actual domain!

            // Function to show/hide message boxes
            function showMessageBox(boxElement, message, classes) {
                boxElement.textContent = message;
                boxElement.className = `mb-4 p-3 rounded-lg text-center ${classes}`;
                boxElement.classList.remove('hidden');
                setTimeout(() => {
                    boxElement.classList.add('hidden');
                }, 5000); // Hide after 5 seconds
            }

            // --- Authentication Logic ---
            async function checkAuthentication() {
                // For simplicity, we'll assume if the session variable is set on the server,
                // the user is authenticated. A direct check here would require another API endpoint.
                // Instead, we'll try to list links. If it fails with "Authentication required",
                // we'll show the login panel.
                try {
                    const response = await fetch('api.php', {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json' },
                        body: JSON.stringify({ action: 'list' })
                    });
                    const data = await response.json();
                    if (data.success) {
                        loginPanel.classList.add('hidden');
                        managePanel.classList.remove('hidden');
                        loadLinks();
                    } else {
                        loginPanel.classList.remove('hidden');
                        managePanel.classList.add('hidden');
                        if (data.message === 'Authentication required.') {
                            showMessageBox(loginMessageBox, 'Please log in to manage links.', 'bg-yellow-100 text-yellow-700');
                        } else {
                            showMessageBox(loginMessageBox, `Error: ${data.message}`, 'bg-red-100 text-red-700');
                        }
                    }
                } catch (error) {
                    console.error('Error checking authentication:', error);
                    showMessageBox(loginMessageBox, 'Could not connect to the server. Please try again.', 'bg-red-100 text-red-700');
                    loginPanel.classList.remove('hidden');
                    managePanel.classList.add('hidden');
                }
            }

            loginForm.addEventListener('submit', async (e) => {
                e.preventDefault();
                const password = passwordInput.value;
                showMessageBox(loginMessageBox, '', 'hidden'); // Clear previous messages

                try {
                    const response = await fetch('api.php', {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json' },
                        body: JSON.stringify({ action: 'login', password: password })
                    });
                    const data = await response.json();
                    if (data.success) {
                        loginPanel.classList.add('hidden');
                        managePanel.classList.remove('hidden');
                        loadLinks();
                    } else {
                        showMessageBox(loginMessageBox, data.message, 'bg-red-100 text-red-700');
                    }
                } catch (error) {
                    console.error('Login error:', error);
                    showMessageBox(loginMessageBox, 'An unexpected error occurred during login.', 'bg-red-100 text-red-700');
                }
            });

            logoutButton.addEventListener('click', async () => {
                try {
                    const response = await fetch('api.php', {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json' },
                        body: JSON.stringify({ action: 'logout' })
                    });
                    const data = await response.json();
                    if (data.success) {
                        loginPanel.classList.remove('hidden');
                        managePanel.classList.add('hidden');
                        passwordInput.value = ''; // Clear password
                        showMessageBox(loginMessageBox, 'Logged out successfully.', 'bg-green-100 text-green-700');
                    } else {
                        showMessageBox(manageMessageBox, `Logout failed: ${data.message}`, 'bg-red-100 text-red-700');
                    }
                } catch (error) {
                    console.error('Logout error:', error);
                    showMessageBox(manageMessageBox, 'An unexpected error occurred during logout.', 'bg-red-100 text-red-700');
                }
            });

            // --- Link Management Logic ---
            async function loadLinks() {
                showMessageBox(manageMessageBox, '', 'hidden'); // Clear previous messages
                linksTableBody.innerHTML = '<tr><td colspan="5" class="py-4 text-center text-gray-500">Loading links...</td></tr>';
                try {
                    const response = await fetch('api.php', {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json' },
                        body: JSON.stringify({ action: 'list' })
                    });
                    const data = await response.json();

                    if (data.success) {
                        renderLinks(data.links);
                    } else {
                        showMessageBox(manageMessageBox, `Failed to load links: ${data.message}`, 'bg-red-100 text-red-700');
                        linksTableBody.innerHTML = '<tr><td colspan="5" class="py-4 text-center text-red-500">Error loading links.</td></tr>';
                    }
                } catch (error) {
                    console.error('Error loading links:', error);
                    showMessageBox(manageMessageBox, 'An unexpected error occurred while loading links.', 'bg-red-100 text-red-700');
                    linksTableBody.innerHTML = '<tr><td colspan="5" class="py-4 text-center text-red-500">Network error.</td></tr>';
                }
            }

            function renderLinks(links) {
                linksTableBody.innerHTML = '';
                if (links.length === 0) {
                    linksTableBody.innerHTML = '<tr><td colspan="5" class="py-4 text-center text-gray-500">No short links found.</td></tr>';
                    return;
                }

                links.forEach(link => {
                    const row = document.createElement('tr');
                    row.className = 'border-b border-gray-200 hover:bg-gray-50';
                    row.innerHTML = `
                        <td class="py-3 px-6 whitespace-nowrap">
                            <a href="${BASE_URL}${link.short_code}" target="_blank" class="text-blue-600 hover:underline font-medium">
                                ${link.short_code}
                            </a>
                        </td>
                        <td class="py-3 px-6 break-all">
                            <span id="longUrl-${link.id}">${link.long_url}</span>
                            <input type="url" id="editLongUrl-${link.id}" value="${link.long_url}" class="hidden w-full px-2 py-1 border rounded-md text-sm">
                        </td>
                        <td class="py-3 px-6">${link.hits}</td>
                        <td class="py-3 px-6">${new Date(link.created_at).toLocaleDateString()} ${new Date(link.created_at).toLocaleTimeString()}</td>
                        <td class="py-3 px-6 text-center whitespace-nowrap">
                            <button data-id="${link.id}" class="edit-btn bg-yellow-500 hover:bg-yellow-600 text-white font-bold py-1 px-3 rounded-md text-xs transition duration-300 mr-2">Edit</button>
                            <button data-id="${link.id}" class="save-btn bg-green-500 hover:bg-green-600 text-white font-bold py-1 px-3 rounded-md text-xs transition duration-300 hidden">Save</button>
                            <button data-id="${link.id}" class="cancel-btn bg-gray-400 hover:bg-gray-500 text-white font-bold py-1 px-3 rounded-md text-xs transition duration-300 hidden">Cancel</button>
                            <button data-id="${link.id}" class="delete-btn bg-red-500 hover:bg-red-600 text-white font-bold py-1 px-3 rounded-md text-xs transition duration-300">Delete</button>
                        </td>
                    `;
                    linksTableBody.appendChild(row);
                });
                addEventListenersToButtons();
            }

            function addEventListenersToButtons() {
                document.querySelectorAll('.edit-btn').forEach(button => {
                    button.onclick = (e) => toggleEditMode(e.target.dataset.id, true);
                });
                document.querySelectorAll('.save-btn').forEach(button => {
                    button.onclick = (e) => updateLink(e.target.dataset.id);
                });
                document.querySelectorAll('.cancel-btn').forEach(button => {
                    button.onclick = (e) => toggleEditMode(e.target.dataset.id, false);
                });
                document.querySelectorAll('.delete-btn').forEach(button => {
                    button.onclick = (e) => confirmAction('Are you sure you want to delete this link?', () => deleteLink(e.target.dataset.id));
                });
            }

            function toggleEditMode(id, enable) {
                const longUrlSpan = document.getElementById(`longUrl-${id}`);
                const editLongUrlInput = document.getElementById(`editLongUrl-${id}`);
                const editBtn = document.querySelector(`.edit-btn[data-id="${id}"]`);
                const saveBtn = document.querySelector(`.save-btn[data-id="${id}"]`);
                const cancelBtn = document.querySelector(`.cancel-btn[data-id="${id}"]`);
                const deleteBtn = document.querySelector(`.delete-btn[data-id="${id}"]`);

                if (enable) {
                    longUrlSpan.classList.add('hidden');
                    editLongUrlInput.classList.remove('hidden');
                    editBtn.classList.add('hidden');
                    saveBtn.classList.remove('hidden');
                    cancelBtn.classList.remove('hidden');
                    deleteBtn.classList.add('hidden'); // Hide delete during edit
                } else {
                    longUrlSpan.classList.remove('hidden');
                    editLongUrlInput.classList.add('hidden');
                    editBtn.classList.remove('hidden');
                    saveBtn.classList.add('hidden');
                    cancelBtn.classList.add('hidden');
                    deleteBtn.classList.remove('hidden'); // Show delete again
                    editLongUrlInput.value = longUrlSpan.textContent.trim(); // Reset input value
                }
            }

            async function updateLink(id) {
                const newLongUrl = document.getElementById(`editLongUrl-${id}`).value.trim();
                if (!newLongUrl || !isValidUrl(newLongUrl)) {
                    showMessageBox(manageMessageBox, 'Please enter a valid URL.', 'bg-red-100 text-red-700');
                    return;
                }

                try {
                    const response = await fetch('api.php', {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json' },
                        body: JSON.stringify({ action: 'update', id: id, long_url: newLongUrl })
                    });
                    const data = await response.json();
                    if (data.success) {
                        showMessageBox(manageMessageBox, 'Link updated successfully!', 'bg-green-100 text-green-700');
                        loadLinks(); // Reload links to update the table
                    } else {
                        showMessageBox(manageMessageBox, `Failed to update link: ${data.message}`, 'bg-red-100 text-red-700');
                    }
                } catch (error) {
                    console.error('Error updating link:', error);
                    showMessageBox(manageMessageBox, 'An unexpected error occurred while updating the link.', 'bg-red-100 text-red-700');
                }
            }

            async function deleteLink(id) {
                try {
                    const response = await fetch('api.php', {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json' },
                        body: JSON.stringify({ action: 'delete', id: id })
                    });
                    const data = await response.json();
                    if (data.success) {
                        showMessageBox(manageMessageBox, 'Link deleted successfully!', 'bg-green-100 text-green-700');
                        loadLinks(); // Reload links to update the table
                    } else {
                        showMessageBox(manageMessageBox, `Failed to delete link: ${data.message}`, 'bg-red-100 text-red-700');
                    }
                } catch (error) {
                    console.error('Error deleting link:', error);
                    showMessageBox(manageMessageBox, 'An unexpected error occurred while deleting the link.', 'bg-red-100 text-red-700');
                }
            }

            addLinkForm.addEventListener('submit', async (e) => {
                e.preventDefault();
                const longUrl = newLongUrlInput.value.trim();
                const customCode = newCustomCodeInput.value.trim();

                if (!longUrl || !isValidUrl(longUrl)) {
                    showMessageBox(manageMessageBox, 'Please enter a valid long URL.', 'bg-red-100 text-red-700');
                    return;
                }

                try {
                    const response = await fetch('api.php', {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json' },
                        body: JSON.stringify({
                            action: 'shorten',
                            long_url: longUrl,
                            custom_code: customCode
                        }),
                    });

                    const data = await response.json();

                    if (data.success) {
                        showMessageBox(manageMessageBox, `Link shortened: ${BASE_URL}${data.short_code}`, 'bg-green-100 text-green-700');
                        newLongUrlInput.value = '';
                        newCustomCodeInput.value = '';
                        loadLinks(); // Reload links to show the new one
                    } else {
                        showMessageBox(manageMessageBox, `Error adding link: ${data.message}`, 'bg-red-100 text-red-700');
                    }
                } catch (error) {
                    console.error('Error adding link:', error);
                    showMessageBox(manageMessageBox, 'An unexpected error occurred while adding the link.', 'bg-red-100 text-red-700');
                }
            });

            function isValidUrl(string) {
                try {
                    new URL(string);
                    return true;
                } catch (e) {
                    return false;
                }
            }

            // --- Custom Confirmation Modal ---
            function confirmAction(message, callback) {
                modalMessage.textContent = message;
                confirmModal.style.display = 'flex'; // Show modal
                currentActionCallback = callback;
            }

            confirmYes.onclick = () => {
                confirmModal.style.display = 'none';
                if (currentActionCallback) {
                    currentActionCallback();
                }
            };

            confirmNo.onclick = () => {
                confirmModal.style.display = 'none';
                currentActionCallback = null;
            };

            // Initial check for authentication when the page loads
            checkAuthentication();
        });
    </script>
</body>
</html>
