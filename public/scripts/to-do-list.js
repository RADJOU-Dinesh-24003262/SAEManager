document.addEventListener('DOMContentLoaded', () => {
    const pendingList = document.getElementById('pending-list');
    const completedList = document.getElementById('completed-list');
    const addTaskBtn = document.getElementById('add-task-btn');
    const newTaskInput = document.getElementById('new-task-input');
    const newTaskPriority = document.getElementById('new-task-priority');
    const newTaskEndDate = document.getElementById('new-end-date');

    // Extract SAE ID from URL
    const saeId = window.location.pathname.split('/')[2];

    if (!saeId) {
        console.error("SAE ID not found in URL");
        return;
    }

    // Sort pending list on load
    sortList(pendingList);

    // Event delegation for both lists
    const handleTaskClick = async (e) => {
        const target = e.target;
        const listItem = target.closest('li');
        if (!listItem) return;

        const todoId = listItem.dataset.id;

        // 1. Handle Checkbox (Complete/Uncomplete)
        if (target.classList.contains('task-checkbox')) {
            const isChecked = target.checked;

            // Optimistic UI Update: Move task between lists
            if (isChecked) {
                completedList.appendChild(listItem);
                listItem.classList.add('completed');
                // Disable priority select if checked
                const select = listItem.querySelector('.priority-select');
                if (select) select.disabled = true;
            } else {
                pendingList.appendChild(listItem);
                listItem.classList.remove('completed');
                // Enable priority select if unchecked
                const select = listItem.querySelector('.priority-select');
                if (select) select.disabled = false;
                sortList(pendingList); // Re-sort pending list when moving back
            }

            checkEmptyLists();

            try {
                const response = await fetch(`/sae/${saeId}/to-do/update/${todoId}`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-Token': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    },
                    body: JSON.stringify({checked: isChecked})
                });

                // Check if response is JSON
                const contentType = response.headers.get("content-type");
                if (contentType && contentType.indexOf("application/json") !== -1) {
                    const data = await response.json();
                    if (!data.success) throw new Error(data.message);
                } else {
                    // If not JSON (e.g. PHP Error or HTML page), throw text
                    const text = await response.text();
                    throw new Error("Réponse serveur invalide: " + text.substring(0, 50) + "...");
                }

            } catch (error) {
                console.error('Error:', error);
                // Revert UI
                target.checked = !isChecked;
                if (!isChecked) {
                    completedList.appendChild(listItem);
                    listItem.classList.add('completed');
                } else {
                    pendingList.appendChild(listItem);
                    listItem.classList.remove('completed');
                    sortList(pendingList);
                }
                alert("Erreur: " + error.message);
            }
        }

        // 2. Handle Priority Change
        if (target.classList.contains('priority-select')) {
            const newPriority = parseInt(target.value);
            const oldPriority = parseInt(listItem.dataset.priority); // Track old priority

            // Optimistic Update
            listItem.dataset.priority = newPriority;
            listItem.classList.remove('priority-high', 'priority-medium', 'priority-low');
            const priorityClass = {1: 'priority-high', 2: 'priority-medium', 3: 'priority-low'}[newPriority];
            listItem.classList.add(priorityClass);

            sortList(pendingList); // Sort immediately

            try {
                const response = await fetch(`/sae/${saeId}/to-do/update/${todoId}`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-Token': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    },
                    body: JSON.stringify({priority: newPriority})
                });

                const contentType = response.headers.get("content-type");
                if (contentType && contentType.indexOf("application/json") !== -1) {
                    const data = await response.json();
                    if (!data.success) throw new Error(data.message);
                } else {
                    const text = await response.text();
                    throw new Error("Réponse serveur invalide: " + text.substring(0, 50));
                }

            } catch (error) {
                console.error('Error:', error);
                alert("Erreur de mise à jour priorité: " + error.message);
                // Revert UI
                target.value = oldPriority;
                listItem.dataset.priority = oldPriority;
                // ... re-apply classes and sort ...
                sortList(pendingList);
            }
        }

        // 3. Handle Delete
        if (target.classList.contains('btn-delete')) {
            if (!confirm("Voulez-vous vraiment supprimer cette tâche ?")) return;

            try {
                const response = await fetch(`/sae/${saeId}/to-do/delete/${todoId}`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-Token': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    }
                });
                const data = await response.json();
                if (data.success) {
                    listItem.remove();
                    checkEmptyLists();
                } else {
                    throw new Error(data.message);
                }
            } catch (error) {
                console.error('Error:', error);
                alert("Erreur lors de la suppression : " + error.message);
            }
        }

        if (target.classList.contains('task-end-date')) {
            const newDate = target.value;
            const oldDate = listItem.dataset.end_date;

            listItem.dataset.end_date = newDate;

            try {
                const response = await fetch(`/sae/${saeId}/to-do/update/${todoId}`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-Token': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    },
                    body: JSON.stringify({end_date: newDate})
                });

                const data = await response.json();
                if (!data.success) throw new Error(data.message);
                listItem.dataset.end_date = newDate;

            } catch (error) {
                console.error('Error:', error);
                alert("Erreur lors de la mise à jour de date : " + error.message);
                // Revert UI
                target.value = oldDate;
                listItem.dataset.end_date = oldDate;
            }
        }
    }

    if (pendingList) pendingList.addEventListener('change', handleTaskClick);
    if (pendingList) pendingList.addEventListener('click', handleTaskClick);
    
    if (completedList) completedList.addEventListener('change', handleTaskClick);
    if (completedList) completedList.addEventListener('click', handleTaskClick);


    function checkEmptyLists() {
        if (pendingList && pendingList.children.length === 0) {
            pendingList.innerHTML = '<li class="empty-message">Aucune tâche en cours.</li>';
        } else {
            const msg = pendingList.querySelector('.empty-message');
            if(msg && pendingList.children.length > 1) msg.remove();
        }

        if (completedList && completedList.children.length === 0) {
            completedList.innerHTML = '<li class="empty-message">Aucune tâche terminée.</li>';
        } else {
            const msg = completedList.querySelector('.empty-message');
            if(msg && completedList.children.length > 1) msg.remove();
        }
    }

    function sortList(list) {
        if(!list) return;
        const items = Array.from(list.children).filter(li => !li.classList.contains('empty-message'));
        if(items.length < 2) return;

        items.sort((a, b) => {
            const pA = parseInt(a.dataset.priority) || 2;
            const pB = parseInt(b.dataset.priority) || 2;
            return pA - pB; // Ascending: 1 (High) -> 2 (Medium) -> 3 (Low)
        });

        items.forEach(li => list.appendChild(li));
    }

    // Add Task Logic
    if (addTaskBtn && newTaskInput) {
        addTaskBtn.addEventListener('click', addNewTask);
        newTaskInput.addEventListener('keypress', (e) => {
            if (e.key === 'Enter') addNewTask();
        });
    }

    async function addNewTask() {
        const description = newTaskInput.value.trim();
        const priority = newTaskPriority ? parseInt(newTaskPriority.value) : 2;
        const endDate = newTaskEndDate.value;

        if (!description) return;

        const payload = { description: description, priority: priority, end_date : endDate };

        try {
            newTaskInput.disabled = true;
            addTaskBtn.disabled = true;

            const response = await fetch(`/sae/${saeId}/to-do/add`, {
                method: 'POST',
                headers: { 
                    'Content-Type': 'application/json',
                    'X-CSRF-Token': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                },
                body: JSON.stringify(payload)
            });

            const data = await response.json();

            if (data.success) {
                // Remove empty message
                const msg = pendingList.querySelector('.empty-message');
                if (msg) msg.remove();

                const li = createListItem(data);
                pendingList.appendChild(li);
                sortList(pendingList); // Sort after adding
                
                newTaskInput.value = '';
                if(newTaskPriority) newTaskPriority.value = '2';
            } else {
                throw new Error(data.message);
            }
        } catch (error) {
            alert("Erreur : " + error.message);
        } finally {
            newTaskInput.disabled = false;
            addTaskBtn.disabled = false;
            newTaskInput.focus();
        }
    }

    function createListItem(data) {
        const li = document.createElement('li');
        const pClass = {1: 'priority-high', 2: 'priority-medium', 3: 'priority-low'}[data.priority];
        li.className = `task-item ${pClass}`;
        li.dataset.id = data.todo_id;
        li.dataset.priority = data.priority;
        li.dataset.end_date = data.end_date;
        
        li.innerHTML = `
            <div class="task-content">
                <label>
                    <input type="checkbox" class="task-checkbox">
                    <span class="task-text">${escapeHtml(data.description)}</span>
                </label>
            </div>
            <div class="task-actions">
                <select class="priority-select">
                    <option value="1" ${data.priority == 1 ? 'selected' : ''}>Haute</option>
                    <option value="2" ${data.priority == 2 ? 'selected' : ''}>Moyenne</option>
                    <option value="3" ${data.priority == 3 ? 'selected' : ''}>Basse</option>
                </select>
                <input type="date" class="new-end-date" value="${escapeHtml(data.end_date)}">
                <button class="btn-delete" title="Supprimer">&times;</button>
            </div>
        `;
        return li;
    }

    function escapeHtml(text) {
        return text.replace(/[&<>"']/g, function(m) { 
            return {'&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#039;'}[m]; 
        });
    }
});
