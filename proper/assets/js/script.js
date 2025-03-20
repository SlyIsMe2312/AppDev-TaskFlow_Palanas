document.addEventListener("DOMContentLoaded", () => {
    initThemeToggle();
    initTaskManagement();
    initForms();
    restoreScroll();
});



const savedTheme = localStorage.getItem("theme") || "light";

/* ========================
   🌓 THEME TOGGLE
   ======================== */
function initThemeToggle() {
    const themeSelect = document.getElementById("themeSelect");
    const toggleThemeBtns = document.querySelectorAll("#toggleThemeBtn");
    const themeIcons = document.querySelectorAll("#themeIcon");
    const body = document.documentElement;

    const savedTheme = localStorage.getItem("theme") || "light";
    applyTheme(savedTheme);

    function applyTheme(theme) {
        body.setAttribute("data-bs-theme", theme);
        localStorage.setItem("theme", theme);
        if (themeSelect) themeSelect.value = theme;
        themeIcons.forEach(icon => (icon.textContent = theme === "dark" ? "☀️" : "🌙"));
    }

    themeSelect?.addEventListener("change", () => applyTheme(themeSelect.value));
    toggleThemeBtns.forEach(btn => btn.addEventListener("click", () => {
        applyTheme(body.getAttribute("data-bs-theme") === "light" ? "dark" : "light");
    }));
}

/* ========================
   📌 GENERIC FORM HANDLER
   ======================== */
   function initForms() {
    document.getElementById("taskForm")?.addEventListener("submit", handleFormSubmit);
    document.getElementById("categoryForm")?.addEventListener("submit", handleFormSubmit);
    document.getElementById("subtaskForm")?.addEventListener("submit", handleFormSubmit);
}

async function handleFormSubmit(event) {
    event.preventDefault();
    const form = event.target;
    const submitButton = form.querySelector("button[type='submit']");
    const spinner = submitButton.querySelector(".spinner-border");

    // Store original button text
    const originalText = submitButton.innerHTML;
    submitButton.style.width = submitButton.offsetWidth + "px"; // Prevent shrinking
    submitButton.innerHTML = `<span class="spinner-border spinner-border-sm"></span>`;
    submitButton.disabled = true;

    // Get input values
    let taskTitle = form.querySelector('input[name="taskTitle"]')?.value || "";
    let parentTaskTitle = form.querySelector('input[name="parentTaskTitle"]')?.value || "";
    let subtaskTitle = form.querySelector('input[name="subtaskTitle"]')?.value || "";
    let parentTaskId = form.querySelector('input[name="parentTaskId"]')?.value || "";
    let aiGenerate = form.querySelector('input[name="ai_generate"]')?.value === "1";


    console.log("Local AI Generate:", aiGenerate);
    console.log("Form AI Generate Value:", form.querySelector('input[name="ai_generate"]')?.value);

    try {
        let formData = new URLSearchParams();
        let aiData = null;

        // **CASE 1: TASK FORM (Creating a Parent Task)**
        if (form.id === "taskForm") {
            formData.append("taskTitle", taskTitle);

            console.log("🔹 Data sent to API (Task):", Object.fromEntries(formData));

            // Send request to api.php
            const aiResponse = await fetch("../dashboard/components/api.php", {
                method: "POST",
                body: formData
            });

            const aiResponseText = await aiResponse.text();
            console.log("Raw AI Response:", aiResponseText);

            aiData = JSON.parse(aiResponseText);
            console.log("AI Response for Task:", aiData);

            if (!aiData.success) throw new Error(aiData.message || "AI task creation failed.");

            const addData = await addTaskToDatabase(
                aiData.taskTitle,
                aiData.difficulty_numeric,
                Array.isArray(aiData.tags) ? aiData.tags.join(", ") : ""
            );

            console.log("Task Data:", addData);

            if (addData && addData.success) {
                form.reset();
                closeModal(form);
                addTaskToUI(addData.task); // ✅ Works because task exists
            } else {
                const errorMessage = addData && addData.message ? addData.message : "Unknown error occurred";
                throw new Error("Error adding task: " + errorMessage);
            }
        }

        // **CASE 2: SUBTASK FORM (AI ON)**
        else if (form.id === "subtaskForm" && aiGenerate) {
            formData.append("taskTitle", parentTaskTitle);
            formData.append("numSubtasks", subtaskTitle);
            formData.append("aiGenerate", "true");
            formData.append("parentTaskId", parentTaskId);

            console.log("🔹 Data sent to API (Subtask AI ON):", Object.fromEntries(formData));

            const aiResponse = await fetch("../dashboard/components/api.php", {
                method: "POST",
                body: formData
            });

            const aiResponseText = await aiResponse.text();
            console.log("Raw AI Response:", aiResponseText);

            aiData = JSON.parse(aiResponseText);
            console.log("AI Response for Subtasks (AI ON):", aiData);

            if (!aiData.success || !Array.isArray(aiData.subtasks)) throw new Error("AI subtask generation failed.");

            for (const subtask of aiData.subtasks) {
                const addData = await addSubtaskToDatabase(
                    parentTaskId,
                    subtask.title,
                    subtask.difficulty_numeric,
                    Array.isArray(subtask.tags) ? subtask.tags.join(", ") : ""
                );

                console.log("Subtask Data:", addData);

                if (!addData.success) throw new Error("Error adding subtask: " + addData.message);
            }

            form.reset();
            closeModal(form);
            refreshTaskList();
        }

        // **CASE 3: SUBTASK FORM (AI OFF)**
        else if (form.id === "subtaskForm" && !aiGenerate) {
            if (!subtaskTitle) throw new Error("Subtask title is required.");

            formData.append("subtaskTitle", subtaskTitle);
            formData.append("parentTaskId", parentTaskId);
            formData.append("aiGenerate", "false");

            console.log("🔹 Data sent to API (Subtask AI OFF):", Object.fromEntries(formData));

            const aiResponse = await fetch("../dashboard/components/api.php", {
                method: "POST",
                body: formData
            });

            const aiResponseText = await aiResponse.text();
            console.log("Raw AI Response:", aiResponseText);

            aiData = JSON.parse(aiResponseText);
            console.log("AI Response for Subtask (AI OFF):", aiData);

            if (!aiData.success || !Array.isArray(aiData.subtasks)) throw new Error("AI difficulty & tagging failed.");

            const addData = await addSubtaskToDatabase(
                parentTaskId,
                aiData.subtasks[0].title,
                aiData.subtasks[0].difficulty_numeric,
                Array.isArray(aiData.subtasks[0].tags) ? aiData.subtasks[0].tags.join(", ") : ""
            );

            console.log("Subtask Data:", addData);

            if (addData && addData.success) {
                form.reset();
                closeModal(form);
                addTaskToUI(addData.subtask); // ✅ Works because task exists
            } else {
                const errorMessage = addData && addData.message ? addData.message : "Unknown error occurred";
                throw new Error("Error adding task: " + errorMessage);
            }
        }

        // **CASE 4: CATEGORY FORM**
        else if (form.id === "categoryForm") {
            let categoryName = form.querySelector('input[name="categoryName"]')?.value || "";

            if (!categoryName) throw new Error("Category title is required.");

            formData.append("categoryName", categoryName);

            console.log("🔹 Data sent to API (Category):", Object.fromEntries(formData));

            const categoryResponse = await fetch("../dashboard/components/add.php", {
                method: "POST",
                body: formData
            });

            const categoryResponseText = await categoryResponse.text();
            console.log("Raw Category Response:", categoryResponseText);

            let categoryData = JSON.parse(categoryResponseText);
            console.log("Category Response:", categoryData);

            if (categoryData.success) {
                form.reset();
                closeModal(form);
                refreshTaskList();
            } else {
                form.reset();
                closeModal(form);
                throw new Error("Error adding category: " + categoryData.message);
            }
        }

    } catch (error) {
        console.error("Form submission error:", error);
        alert(error.message);
    } finally {
        submitButton.innerHTML = originalText;
        spinner.classList.add("d-none");
        submitButton.disabled = false;
    }
}

/**
 * Close modal after form submission.
 */
function closeModal(form) {
    const modal = form.closest(".modal");
    const modalInstance = modal ? bootstrap.Modal.getInstance(modal) : null;
    modalInstance?.hide();
}

/**
 * Sends a new task to the database.
 */
async function addTaskToDatabase(title, difficulty, tags) {
    const formData = new FormData();
    formData.append("taskTitle", title);
    formData.append("difficulty_numeric", difficulty);
    formData.append("tags", JSON.stringify(tags)); // Send tags as JSON

    console.log("Tags before sending:", tags); // Debugging line

    const response = await fetch("../dashboard/components/add.php", {
        method: "POST",
        body: formData
    });

    const rawText = await response.text();
    console.log("Raw Response from PHP:", rawText); 

    try {
        return JSON.parse(rawText);
    } catch (error) {
        console.error("Error parsing JSON:", error);
        return { success: false, message: "Invalid JSON response" };
    }
}


/**
 * Sends a new subtask to the database.
 */
async function addSubtaskToDatabase(parentTaskId, title, difficulty, tags) {
    const formData = new FormData();
    formData.append("parentTaskId", parentTaskId);
    formData.append("subtaskTitle", title);
    formData.append("difficulty_numeric", difficulty);
    formData.append("tags", JSON.stringify(tags)); // Ensure correct JSON format

    const response = await fetch("../dashboard/components/add.php", {
        method: "POST",
        body: formData
    });

    const rawText = await response.text();
    console.log("Raw Response from PHP:", rawText);

    try {
        return JSON.parse(rawText);
    } catch (error) {
        console.error("Error parsing JSON:", error);
        return { success: false, message: "Invalid JSON response" };
    }
}







/* ========================
   ✅ ADD TASK AND CATEGORY TO UI
   ======================== */
   function createTaskElement(task) {
        // Create list item
        const taskElement = document.createElement("li");
        taskElement.className = "list-group-item task d-flex flex-column";
        taskElement.draggable = true;
        taskElement.dataset.taskId = task.id;
        taskElement.style.marginBottom = "5px";

        // ✅ Task Content (Checkbox + Title)
        taskElement.innerHTML = `
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <input type="checkbox" class="task-checkbox" data-task-id="${task.id}" ${task.status === "completed" ? "checked" : ""}>
                    <strong>${sanitize(task.title)}</strong>
                </div>
                <div class="d-flex flex-wrap">
                    ${task.tags ? task.tags.map(tag => `<span class="badge bg-info text-dark me-2">${sanitize(tag)}</span>`).join("") : ""}
                </div>
            </div>
            ${task.subtasks && task.subtasks.length > 0 ? generateSubtasksHTML(task.subtasks) : ""}
        `;

        return taskElement;
    }

// ✅ Add Task to UI
function addTaskToUI(task) {
    document.getElementById("task-list")?.prepend(createTaskElement(task));
    refreshTaskList();
}

// ✅ Add Category to UI
function addCategoryToUI(category) {
    const categoryList = document.getElementById("category-list");
    if (!categoryList) return;

    const categoryElement = document.createElement("li");
    categoryElement.className = "list-group-item d-flex justify-content-between align-items-center";
    categoryElement.dataset.categoryId = category.id;
    categoryElement.innerHTML = `
        <span>${category.name}</span>
        <span class="badge bg-primary">${category.task_count} tasks</span>
    `;

    categoryList.prepend(categoryElement);
    refreshTaskList();
}

// ✅ Helper Functions
function sanitize(text) {
    const div = document.createElement("div");
    div.textContent = text;
    return div.innerHTML;
}

function generateSubtasksHTML(subtasks) {
    return `
        <ul class="list-group mt-2">
            ${subtasks.map(subtask => `
                <li class="list-group-item d-flex align-items-center">
                    <input type="checkbox" class="subtask-checkbox me-2" data-subtask-id="${subtask.id}" ${subtask.status === "completed" ? "checked" : ""}>
                    ${sanitize(subtask.title)}
                </li>
            `).join("")}
        </ul>
    `;
}




/* ========================
   ✅ TASK MANAGEMENT (Drag & Drop + Status Update)
   ======================== */
function initTaskManagement() {
    const taskList = document.getElementById("task-list");
    if (!taskList) return;

    let draggedTask = null;

    // ✅ Task Checkbox Handling (Event Delegation)
    taskList.addEventListener("change", event => {
        if (event.target.matches(".task-checkbox, .subtask-checkbox")) {
            const taskId = event.target.dataset.taskId || event.target.dataset.subtaskId;
            if (taskId) updateTaskStatus(taskId, event.target.checked ? "completed" : "pending");
        }
    });

    // ✅ Drag & Drop Handling
    taskList.addEventListener("dragstart", event => {
        if (event.target.classList.contains("task")) {
            draggedTask = event.target;
            draggedTask.classList.add("dragging");
        }
    });

    taskList.addEventListener("dragend", () => {
        if (draggedTask) {
            draggedTask.classList.remove("dragging");
            draggedTask = null;
            saveTaskOrder();
        }
    });

    taskList.addEventListener("dragover", event => {
        event.preventDefault();
        const afterElement = getDragAfterElement(taskList, event.clientY);
        const draggingTask = document.querySelector(".dragging");
        afterElement ? taskList.insertBefore(draggingTask, afterElement) : taskList.appendChild(draggingTask);
    });

    // ✅ Open Add Subtask Modal & Set Parent Task ID
    taskList.addEventListener("click", event => {
        if (event.target.classList.contains("add-subtask-btn")) {
            const parentTaskId = event.target.dataset.taskId;
            document.getElementById("parentTaskId").value = parentTaskId;
            document.getElementById("parentTaskTitle").value = event.target.closest(".task").querySelector("strong").textContent;

            const subtaskModalElement = document.getElementById("addSubTaskModal");
            const subtaskModal = new bootstrap.Modal(subtaskModalElement);
            subtaskModal.show();

            subtaskModalElement.addEventListener("shown.bs.modal", function () {
                console.log("Subtask modal opened. Initializing AI Toggle...");
                aiGenerateToggle();  // ✅ No need to pass form
            }, { once: true });
        }
    });


    // ✅ Delete Task/Subtask (Event Delegation)
    taskList.addEventListener("click", event => {
        const button = event.target.closest("button"); // Ensure we get the button, not the icon
        if (!button) return;

        if (button.classList.contains("delete-task-btn") || button.classList.contains("delete-subtask-btn")) {
            if (confirm("Are you sure you want to delete this task?")) {
                const taskId = button.dataset.taskId || button.dataset.subtaskId;
                if (!taskId) {
                    alert("Error: Task ID is missing!");
                    return;
                }

                console.log("Attempting to delete task with ID:", taskId);

                fetch('../dashboard/components/delete_task.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                    body: `taskId=${taskId}`
                })
                .then(response => response.text()) // Change this to .text() first for debugging
                .then(text => {
                    console.log("Raw response from server:", text);
                    try {
                        const data = JSON.parse(text);
                        console.log("Parsed JSON response:", data);
                        if (data.success) {
                            refreshTaskList();
                        } else {
                            alert("Error deleting task: " + (data.message || "Unknown error"));
                        }
                    } catch (error) {
                        console.error("Error parsing JSON:", error);
                        alert("Server response is not valid JSON. Check console for details.");
                    }
                })
                .catch(error => {
                    console.error("Fetch error:", error);
                    alert("Fetch request failed. Check console for details.");
                });
                
            }
        }
    });

    applyDifficultyColors();
}

// Function to Assign Dynamic Colors to Difficulty Labels
function applyDifficultyColors() {
    document.querySelectorAll(".difficulty-label").forEach(label => {
        const difficulty = parseFloat(label.getAttribute("data-difficulty"));
        if (!isNaN(difficulty)) {
            label.style.backgroundColor = getDifficultyColor(difficulty);
            label.style.color =  "#000";
            label.style.fontWeight = "bold";
            label.style.fontSize = "0.6rem";
            label.style.borderRadius = "5px";
            label.style.padding = "2px 10px";  
            label.style.alignItems = "center";
            
        }
    });
}

// Function to Compute Gradient Color (Green → Yellow → Red)
function getDifficultyColor(difficulty) {
    const isDarkMode = savedTheme === "dark";

    let red, green, blue;

    if (difficulty <= 4.0) {
        // Light Green to Light Yellow (0.1 → 4.0)
        let ratio = (difficulty - 0.1) / (4.0 - 0.1);
        red = Math.round(200 * ratio + 50);
        green = 230;
        blue = 150;
    } else if (difficulty <= 7.5) {
        // Light Yellow to Light Orange (4.1 → 7.5)
        let ratio = (difficulty - 4.1) / (7.5 - 4.1);
        red = 230;
        green = Math.round(200 * (1 - ratio) + 100);
        blue = 120;
    } else {
        // Light Orange to Light Red (7.6 → 10.0)
        let ratio = (difficulty - 7.6) / (10.0 - 7.6);
        red = 240;
        green = Math.round(150 * (1 - ratio) + 50);
        blue = 100;
    }

    // **Dark Mode Adjustment**: Reduce brightness & add gray
    if (isDarkMode) {
        red = Math.round(red * 0.8 + 30);   // Reduce intensity, add gray tint
        green = Math.round(green * 0.8 + 30);
        blue = Math.round(blue * 0.8 + 30);
    }

    return `rgb(${red}, ${green}, ${blue})`;
}






/* ========================
   ✅ UPDATE TASK STATUS (AJAX)
   ======================== */
async function updateTaskStatus(taskId, status) {
    try {
        const response = await fetch("../dashboard/components/update_status.php", {
            method: "POST",
            headers: { "Content-Type": "application/json" },
            body: JSON.stringify({ task_id: taskId, status })
        });

        const data = await response.json();
        console.log(data);
        refreshTaskList(); 
    } catch (error) {
        console.error("Error updating task:", error);
    }
}


function refreshTaskList() {
    const taskPane = document.querySelector(".task-view-pane");
    if (taskPane) {
        sessionStorage.setItem("scrollPosition", taskPane.scrollTop);
    }

    location.reload();
}

function restoreScroll() {
    const taskPane = document.querySelector(".task-view-pane");
    const savedScrollPosition = sessionStorage.getItem("scrollPosition");

    if (taskPane && savedScrollPosition !== null) {
        taskPane.scrollTop = parseInt(savedScrollPosition, 10);
        sessionStorage.removeItem("scrollPosition"); // Clear it after restoring
    }
}



/* ========================
   💾 SAVE TASK ORDER TO DATABASE
   ======================== */
async function saveTaskOrder() {
    const tasks = [...document.querySelectorAll("#task-list > .task")].map((task, index) => ({
        task_id: task.dataset.taskId,
        position: index + 1
    }));

    try {
        const response = await fetch("../dashboard/components/update-task-order.php", {
            method: "POST",
            headers: { "Content-Type": "application/json" },
            body: JSON.stringify(tasks)
        });

        const data = await response.json();
        console.log(data);
    } catch (error) {
        console.error("Error updating task order:", error);
    }
}

/* ========================
   🏗️ HELPER FUNCTION: Get Element After Dragged Item
   ======================== */
function getDragAfterElement(container, y) {
    return [...container.querySelectorAll(".task:not(.dragging)")].reduce((closest, child) => {
        const box = child.getBoundingClientRect();
        const offset = y - box.top - box.height / 2;
        return offset < 0 && offset > closest.offset ? { offset, element: child } : closest;
    }, { offset: Number.NEGATIVE_INFINITY }).element;
}

function aiGenerateToggle() {
    console.log("aiGenerateToggle() function is running!"); // Debugging line

    const aiToggle = document.getElementById("aiToggle");
    const subtaskLabel = document.getElementById("subtaskLabel");
    const subtaskTitle = document.getElementById("subtaskTitle");
    const aiGenerate = document.getElementById("aiGenerate");
    const submitButton = document.querySelector("#subtaskForm button[type='submit']");

    if (!aiToggle || !subtaskLabel || !subtaskTitle || !aiGenerate || !submitButton) return;

    aiToggle.addEventListener("change", function () {
        if (this.checked) {
            // AI mode: Change input to number of subtasks
            subtaskLabel.textContent = "How many subtasks to be generated?";
            subtaskTitle.type = "number";
            subtaskTitle.min = "1";
            subtaskTitle.value = "1";
            aiGenerate.value = "1"; 
            console.log(aiGenerate.value); // Debugging line
            submitButton.textContent = "Generate Subtasks";
        } else {
            // Manual mode: Change input back to text
            subtaskLabel.textContent = "Subtask Title";
            subtaskTitle.type = "text";
            subtaskTitle.value = "";
            aiGenerate.value = "0"; 
            console.log(aiGenerate.value); // Debugging line
            submitButton.textContent = "Add Subtask";
        }
    });
}