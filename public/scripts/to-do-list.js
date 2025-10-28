// listener of the DOMContentLoaded event to initialize the to-do list
document.addEventListener('DOMContentLoaded', function() {
    // Initialize the to-do list section
    initializeTodoList();
    
    // Attach event listeners to existing buttons (if any)
    attachExistingButtons();
});

function initializeTodoList() {
    const todoListSection = document.getElementById('to-do-list');
    if (!todoListSection) return;

    // Create and append the form to add new tasks
    const addTaskForm = createAddTaskForm();
    todoListSection.appendChild(addTaskForm);

    // Add an event listener for the form
    addTaskForm.addEventListener('submit', function(e) {
        e.preventDefault();
        addNewTask();
    });
}

function createAddTaskForm() {
    const form = document.createElement('div');
    form.className = 'add-task-form';
    form.innerHTML = `
        <h3>Ajouter une nouvelle tâche</h3>
        <form id="new-task-form">
            <input type="text" id="task-description" placeholder="Description de la tâche" required>
            <button type="submit">Ajouter la tâche</button>
        </form>
    `;
    return form;
}

function addNewTask() {
    const taskDescriptionInput = document.getElementById('task-description');
    const description = taskDescriptionInput.value.trim();
    
    if (description === '') {
        alert('Veuillez entrer une description pour la tâche');
        return;
    }

    const todoList = document.querySelector('#to-do-list ul');
    if (!todoList) return;

    // Create the new task
    const newTask = createTaskElement(description);
    todoList.appendChild(newTask);

    // Reset the input field
    taskDescriptionInput.value = '';
}

function createTaskElement(description) {
    const taskId = 'task-' + Date.now(); // Unique ID for the task
    
    const taskItem = document.createElement('li');
    taskItem.id = taskId;
    taskItem.innerHTML = `
        <span class="task-description">${description}</span>
        <button onclick="markTaskAsCompleted('${taskId}')">Marquer comme terminée</button>
        <button onclick="deleteTask('${taskId}')" class="delete-btn">Supprimer</button>
    `;
    
    return taskItem;
}

function markTaskAsCompleted(taskId) {
    const taskElement = document.getElementById(taskId);
    if (taskElement) {
        taskElement.classList.toggle('completed');
        const button = taskElement.querySelector('button');
        if (taskElement.classList.contains('completed')) {
            button.textContent = 'Marquer comme non terminée';
            taskElement.style.opacity = '0.6';
        } else {
            button.textContent = 'Marquer comme terminée';
            taskElement.style.opacity = '1';
        }
    }
}

function deleteTask(taskId) {
    const taskElement = document.getElementById(taskId);
    if (taskElement && confirm('Êtes-vous sûr de vouloir supprimer cette tâche ?')) {
        taskElement.remove();
    }
}

function attachExistingButtons() {
    // Attach event listeners to existing buttons in the HTML
    const existingButtons = document.querySelectorAll('#to-do-list button');
    existingButtons.forEach(button => {
        if (button.textContent.includes('Marquer comme terminée')) {
            const taskItem = button.closest('li');
            if (taskItem && !taskItem.id) {
                taskItem.id = 'task-' + Date.now();
            }
            button.onclick = function() {
                markTaskAsCompleted(taskItem.id);
            };
        }
    });
}

// Function to add a task programmatically (useful for other scripts)
function addTask(description) {
    const todoList = document.querySelector('#to-do-list ul');
    if (!todoList) return null;

    const newTask = createTaskElement(description);
    todoList.appendChild(newTask);
    return newTask.id; // Return the ID of the created task
}

// Function to retrieve all tasks
function getAllTasks() {
    const tasks = [];
    const taskElements = document.querySelectorAll('#to-do-list li');
    
    taskElements.forEach(taskElement => {
        const description = taskElement.querySelector('.task-description').textContent;
        const isCompleted = taskElement.classList.contains('completed');
        tasks.push({
            id: taskElement.id,
            description: description,
            completed: isCompleted
        });
    });
    
    return tasks;
}