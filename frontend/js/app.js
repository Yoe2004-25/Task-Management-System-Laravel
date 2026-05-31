
const API_BASE_URL = 'http://127.0.0.1:8000/api';  // تأكد من تشغيل Laravel على port 8000
let authToken = localStorage.getItem('auth_token');
let currentUser = null;

// ========== دوال مساعدة ==========
function showToast(message, type = 'success') {
    const toast = document.getElementById('toast');
    if (!toast) return;
    toast.textContent = message;
    toast.className = `toast ${type}`;
    toast.style.display = 'block';
    setTimeout(() => {
        toast.style.display = 'none';
    }, 3000);
}

async function apiRequest(endpoint, method = 'GET', data = null) {
    const headers = {
        'Content-Type': 'application/json',
        'Accept': 'application/json'
    };
    
    if (authToken) {
        headers['Authorization'] = `Bearer ${authToken}`;
    }
    
    const config = {
        method: method,
        headers: headers
    };
    
    if (data && (method === 'POST' || method === 'PUT')) {
        config.body = JSON.stringify(data);
    }
    
    try {
        const response = await fetch(`${API_BASE_URL}${endpoint}`, config);
        const result = await response.json();
        
        if (!response.ok) {
            throw new Error(result.message || 'حدث خطأ في الطلب');
        }
        
        return result;
    } catch (error) {
        console.error('API Error:', error);
        showToast(error.message, 'error');
        throw error;
    }
}

function logout() {
    if (authToken) {
        apiRequest('/logout', 'POST').catch(() => {});
    }
    localStorage.removeItem('auth_token');
    localStorage.removeItem('user');
    window.location.href = 'index.html';
}

// ========== دوال المهام ==========
async function fetchTasks() {
    try {
        const statusFilter = document.getElementById('filterStatus')?.value;
        const priorityFilter = document.getElementById('filterPriority')?.value;
        
        let url = '/tasks?';
        if (statusFilter && statusFilter !== 'all') url += `status=${statusFilter}&`;
        if (priorityFilter && priorityFilter !== 'all') url += `priority=${priorityFilter}&`;
        
        const result = await apiRequest(url);
        const tasks = result.data || result;
        displayTasks(tasks);
        return tasks;
    } catch (error) {
        const tasksList = document.getElementById('tasksList');
        if (tasksList) tasksList.innerHTML = '<div class="loading">❌ فشل في تحميل المهام</div>';
        return [];
    }
}

async function fetchStatistics() {
    try {
        const stats = await apiRequest('/tasks-statistics');
        if (document.getElementById('statTotal')) {
            document.getElementById('statTotal').textContent = stats.total || 0;
            document.getElementById('statPending').textContent = stats.pending || 0;
            document.getElementById('statInProgress').textContent = stats.in_progress || 0;
            document.getElementById('statCompleted').textContent = stats.completed || 0;
            document.getElementById('statOverdue').textContent = stats.overdue || 0;
            
            const rate = stats.completion_rate || 0;
            document.getElementById('completionRate').textContent = `${rate}%`;
            const progressFill = document.getElementById('progressFill');
            if (progressFill) {
                progressFill.style.width = `${rate}%`;
                progressFill.textContent = `${rate}%`;
            }
        }
    } catch (error) {
        console.error('Failed to load statistics:', error);
    }
}

function displayTasks(tasks) {
    const tasksList = document.getElementById('tasksList');
    if (!tasksList) return;
    
    if (!tasks || tasks.length === 0) {
        tasksList.innerHTML = '<div class="loading">📭 لا توجد مهام لعرضها</div>';
        return;
    }
    
    tasksList.innerHTML = tasks.map(task => `
        <div class="task-card">
            <div class="task-header">
                <div>
                    <span class="task-title">${escapeHtml(task.title)}</span>
                    <span class="status-${task.status.replace('_', '-')}" style="padding:4px 12px;border-radius:20px;font-size:12px;margin-right:10px;">
                        ${getStatusText(task.status)}
                    </span>
                </div>
                <div class="task-actions">
                    <button class="edit-btn" onclick="openEditModal(${task.id})">✏️</button>
                    ${currentUser?.role === 'admin' ? `<button class="delete-btn" onclick="deleteTask(${task.id})">🗑️</button>` : ''}
                </div>
            </div>
            <div class="task-description" style="margin:10px 0;color:#666;">
                ${task.description ? escapeHtml(task.description.substring(0, 100)) : '📝 لا يوجد وصف'}
            </div>
            <div class="task-footer">
                <span class="priority-${task.priority}" style="padding:4px 12px;border-radius:20px;font-size:12px;">
                    ${getPriorityText(task.priority)}
                </span>
                <span>📅 ${task.due_date || 'غير محدد'}</span>
                <span>👤 ${task.user?.name || 'غير معروف'}</span>
            </div>
        </div>
    `).join('');
}

async function addTask(event) {
    event.preventDefault();
    
    const title = document.getElementById('taskTitle')?.value;
    if (!title) {
        showToast('يرجى إدخال عنوان المهمة', 'error');
        return;
    }
    
    const description = document.getElementById('taskDesc')?.value;
    const priority = document.getElementById('taskPriority')?.value;
    const due_date = document.getElementById('taskDueDate')?.value;
    
    try {
        await apiRequest('/tasks', 'POST', { title, description, priority, due_date: due_date || null });
        showToast('✅ تم إضافة المهمة بنجاح');
        document.getElementById('addTaskForm')?.reset();
        await fetchTasks();
        await fetchStatistics();
    } catch (error) {
        showToast('فشل في إضافة المهمة', 'error');
    }
}

async function openEditModal(taskId) {
    try {
        const result = await apiRequest(`/tasks/${taskId}`);
        const task = result.data || result;
        
        document.getElementById('editTaskId').value = task.id;
        document.getElementById('editTitle').value = task.title;
        document.getElementById('editDesc').value = task.description || '';
        document.getElementById('editStatus').value = task.status;
        document.getElementById('editPriority').value = task.priority;
        document.getElementById('editDueDate').value = task.due_date || '';
        
        document.getElementById('editModal').style.display = 'block';
    } catch (error) {
        showToast('فشل في تحميل بيانات المهمة', 'error');
    }
}

async function updateTask(event) {
    event.preventDefault();
    
    const taskId = document.getElementById('editTaskId').value;
    const data = {
        title: document.getElementById('editTitle').value,
        description: document.getElementById('editDesc').value,
        status: document.getElementById('editStatus').value,
        priority: document.getElementById('editPriority').value,
        due_date: document.getElementById('editDueDate').value || null
    };
    
    try {
        await apiRequest(`/tasks/${taskId}`, 'PUT', data);
        showToast('✅ تم تحديث المهمة بنجاح');
        closeModal();
        await fetchTasks();
        await fetchStatistics();
    } catch (error) {
        showToast('فشل في تحديث المهمة', 'error');
    }
}

async function deleteTask(taskId) {
    if (!confirm('هل أنت متأكد من حذف هذه المهمة؟')) return;
    
    try {
        await apiRequest(`/tasks/${taskId}`, 'DELETE');
        showToast('🗑️ تم حذف المهمة بنجاح');
        await fetchTasks();
        await fetchStatistics();
    } catch (error) {
        showToast('فشل في حذف المهمة', 'error');
    }
}

function closeModal() {
    document.getElementById('editModal').style.display = 'none';
}

// ========== دوال المصادقة ==========
async function login(event) {
    event.preventDefault();
    
    const email = document.getElementById('loginEmail')?.value;
    const password = document.getElementById('loginPassword')?.value;
    
    if (!email || !password) {
        showToast('يرجى إدخال البريد الإلكتروني وكلمة المرور', 'error');
        return;
    }
    
    try {
        const result = await apiRequest('/login', 'POST', { email, password });
        localStorage.setItem('auth_token', result.token);
        localStorage.setItem('user', JSON.stringify(result.user));
        showToast('🎉 تم تسجيل الدخول بنجاح');
        setTimeout(() => {
            window.location.href = 'dashboard.html';
        }, 1000);
    } catch (error) {
        showToast('فشل في تسجيل الدخول', 'error');
    }
}

async function register(event) {
    event.preventDefault();
    
    const name = document.getElementById('regName')?.value;
    const email = document.getElementById('regEmail')?.value;
    const password = document.getElementById('regPassword')?.value;
    const password_confirmation = document.getElementById('regPasswordConfirmation')?.value;
    
    if (password !== password_confirmation) {
        showToast('كلمة المرور غير متطابقة', 'error');
        return;
    }
    
    if (password.length < 8) {
        showToast('كلمة المرور يجب أن تكون 8 أحرف على الأقل', 'error');
        return;
    }
    
    try {
        const result = await apiRequest('/register', 'POST', { name, email, password, password_confirmation });
        localStorage.setItem('auth_token', result.token);
        localStorage.setItem('user', JSON.stringify(result.user));
        showToast('🎉 تم إنشاء الحساب بنجاح');
        setTimeout(() => {
            window.location.href = 'dashboard.html';
        }, 1000);
    } catch (error) {
        showToast('فشل في إنشاء الحساب', 'error');
    }
}

// ========== دوال مساعدة للعرض ==========
function getStatusText(status) {
    const map = { 'pending': '⏳ قيد الانتظار', 'in_progress': '⚙️ قيد التنفيذ', 'completed': '✅ مكتملة' };
    return map[status] || status;
}

function getPriorityText(priority) {
    const map = { 'low': '🟢 منخفضة', 'medium': '🟡 متوسطة', 'high': '🔴 عالية' };
    return map[priority] || priority;
}

function escapeHtml(text) {
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
}

// ========== تهيئة الصفحات ==========
function initAuthPage() {
    const loginCard = document.getElementById('loginCard');
    const registerCard = document.getElementById('registerCard');
    
    document.getElementById('showRegister')?.addEventListener('click', (e) => {
        e.preventDefault();
        if (loginCard) loginCard.style.display = 'none';
        if (registerCard) registerCard.style.display = 'block';
    });
    
    document.getElementById('showLogin')?.addEventListener('click', (e) => {
        e.preventDefault();
        if (registerCard) registerCard.style.display = 'none';
        if (loginCard) loginCard.style.display = 'block';
    });
    
    document.getElementById('loginForm')?.addEventListener('submit', login);
    document.getElementById('registerForm')?.addEventListener('submit', register);
    
    if (localStorage.getItem('auth_token')) {
        window.location.href = 'dashboard.html';
    }
}

function initDashboardPage() {
    authToken = localStorage.getItem('auth_token');
    const savedUser = localStorage.getItem('user');
    
    if (!authToken || !savedUser) {
        window.location.href = 'index.html';
        return;
    }
    
    currentUser = JSON.parse(savedUser);
    document.getElementById('userName').textContent = currentUser.name;
    const roleBadge = document.getElementById('userRole');
    if (currentUser.role === 'admin') {
        roleBadge.textContent = 'مدير';
        roleBadge.style.background = '#ef4444';
    } else {
        roleBadge.textContent = 'مستخدم';
    }
    
    document.getElementById('logoutBtn')?.addEventListener('click', logout);
    document.getElementById('addTaskForm')?.addEventListener('submit', addTask);
    document.getElementById('filterStatus')?.addEventListener('change', () => fetchTasks());
    document.getElementById('filterPriority')?.addEventListener('change', () => fetchTasks());
    document.querySelector('.close')?.addEventListener('click', closeModal);
    window.addEventListener('click', (e) => {
        if (e.target === document.getElementById('editModal')) closeModal();
    });
    document.getElementById('editTaskForm')?.addEventListener('submit', updateTask);
    
    fetchTasks();
    fetchStatistics();
    
    setInterval(() => {
        if (document.getElementById('tasksList')) {
            fetchTasks();
            fetchStatistics();
        }
    }, 30000);
}

// تشغيل الصفحة المناسبة
document.addEventListener('DOMContentLoaded', () => {
    if (window.location.pathname.includes('dashboard.html')) {
        initDashboardPage();
    } else {
        initAuthPage();
    }
});

window.openEditModal = openEditModal;
window.deleteTask = deleteTask;
window.closeModal = closeModal;