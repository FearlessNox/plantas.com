document.addEventListener('DOMContentLoaded', function() {
    // Inicializar formulários
    initForms();
    
    // Inicializar botões de adicionar
    initAddButtons();

    // Adicionar eventos aos botões de excluir nas tabelas existentes
    initDeleteButtons();
});

// Função para gerenciar a navegação entre seções
function initNavigation() {
    const navLinks = document.querySelectorAll('nav a');
    const sections = document.querySelectorAll('section');
    
    navLinks.forEach(link => {
        link.addEventListener('click', function(e) {
            e.preventDefault();
            
            // Remover classe ativa de todos os links e seções
            navLinks.forEach(l => l.classList.remove('active'));
            sections.forEach(s => s.classList.remove('section-active'));
            
            // Adicionar classe ativa ao link clicado
            this.classList.add('active');
            
            // Mostrar a seção correspondente
            const targetId = this.getAttribute('href').substring(1);
            document.getElementById(targetId).classList.add('section-active');
        });
    });
}

// Inicializar formulários
function initForms() {
    const plantaFormCard = document.getElementById('plantaFormCard');
    const usuarioFormCard = document.getElementById('usuarioFormCard');
    const cuidadoFormCard = document.getElementById('cuidadoFormCard');
    
    // Botões de fechar formulários
    const closePlantaForm = document.getElementById('closePlantaForm');
    const closeUsuarioForm = document.getElementById('closeUsuarioForm');
    const closeCuidadoForm = document.getElementById('closeCuidadoForm');
    
    if (closePlantaForm && plantaFormCard) {
        closePlantaForm.addEventListener('click', function() {
            plantaFormCard.style.display = 'none';
        });
    }
    
    if (closeUsuarioForm && usuarioFormCard) {
        closeUsuarioForm.addEventListener('click', function() {
            usuarioFormCard.style.display = 'none';
        });
    }
    
    if (closeCuidadoForm && cuidadoFormCard) {
        closeCuidadoForm.addEventListener('click', function() {
            cuidadoFormCard.style.display = 'none';
        });
    }
}

// Inicializar botões de adicionar
function initAddButtons() {
    const btnAddPlanta = document.getElementById('btnAddPlanta');
    const btnAddUsuario = document.getElementById('btnAddUsuario');
    const btnAddCuidado = document.getElementById('btnAddCuidado');
    
    if (btnAddPlanta) {
        btnAddPlanta.addEventListener('click', function() {
            const formCard = document.getElementById('plantaFormCard');
            formCard.style.display = 'block';
        });
    }
    
    if (btnAddUsuario) {
        btnAddUsuario.addEventListener('click', function() {
            const formCard = document.getElementById('usuarioFormCard');
            formCard.style.display = 'block';
        });
    }
    
    if (btnAddCuidado) {
        btnAddCuidado.addEventListener('click', function() {
            const formCard = document.getElementById('cuidadoFormCard');
            formCard.style.display = 'block';
        });
    }
}

// Inicializar botões de excluir nas tabelas
function initDeleteButtons() {
    const deleteButtons = document.querySelectorAll('.btn-delete');
    
    deleteButtons.forEach(button => {
        button.addEventListener('click', async function() {
            const id = this.dataset.id;
            if (!id) return;

            // Identificar qual tabela está sendo usada
            const table = this.closest('table');
            let apiEndpoint = '';
            let itemTipo = '';

            if (table.id === 'plantasTable') {
                apiEndpoint = '/plantas.com/api/plantas.php';
                itemTipo = 'planta';
            } else if (table.id === 'usuariosTable') {
                apiEndpoint = '/plantas.com/api/usuarios.php';
                itemTipo = 'usuário';
            } else if (table.id === 'cuidadosTable') {
                apiEndpoint = '/plantas.com/api/cuidados.php';
                itemTipo = 'cuidado';
            } else {
                console.error('Tabela não identificada');
                return;
            }

            const confirmDelete = confirm(`Tem certeza que deseja excluir este ${itemTipo}? Esta ação não pode ser desfeita.`);
            
            if (confirmDelete) {
                try {
                    const response = await fetch(`${apiEndpoint}?id=${id}`, {
                        method: 'DELETE'
                    });

                    const data = await response.json();

                    if (data.success) {
                        // Remover a linha da tabela
                        const row = this.closest('tr');
                        row.remove();

                        // Se não houver mais registros, mostrar mensagem
                        const tbody = table.querySelector('tbody');
                        if (tbody.children.length === 0) {
                            const colSpan = table.querySelector('thead tr').children.length;
                            tbody.innerHTML = `
                                <tr>
                                    <td colspan="${colSpan}" class="empty-table">Nenhum registro encontrado</td>
                                </tr>`;
                        }

                        showNotification(data.message || `${itemTipo.charAt(0).toUpperCase() + itemTipo.slice(1)} excluído(a) com sucesso`, 'success');
                    } else {
                        // Se houver erro por causa de registros relacionados, mostrar mensagem mais amigável
                        if (data.message && data.message.includes('existem cuidados registrados')) {
                            if (itemTipo === 'planta') {
                                showNotification('Não é possível excluir esta planta pois existem cuidados registrados para ela. Exclua primeiro os cuidados relacionados.', 'warning');
                            } else if (itemTipo === 'usuário') {
                                showNotification('Não é possível excluir este usuário pois existem cuidados registrados para ele. Exclua primeiro os cuidados relacionados.', 'warning');
                            } else {
                                showNotification(data.message, 'warning');
                            }
                        } else {
                            showNotification(data.message || `Erro ao excluir ${itemTipo}`, 'error');
                        }
                    }
                } catch (error) {
                    console.error('Erro:', error);
                    showNotification(`Erro ao excluir ${itemTipo}`, 'error');
                }
            }
        });
    });
}

// Função para mostrar notificações
function showNotification(message, type = 'success') {
    // Remover notificações anteriores
    const existingNotifications = document.querySelectorAll('.notification');
    existingNotifications.forEach(notification => {
        notification.remove();
    });

    const notification = document.createElement('div');
    notification.className = `notification ${type}`;
    notification.innerHTML = `
        <div class="notification-content">
            <span class="notification-message">${message}</span>
            <button class="notification-close">&times;</button>
        </div>
    `;
    
    document.body.appendChild(notification);
    
    // Adicionar evento para fechar notificação
    const closeBtn = notification.querySelector('.notification-close');
    const closeNotification = () => {
        notification.classList.add('closing');
        setTimeout(() => {
            notification.remove();
        }, 300); // Tempo da animação
    };

    closeBtn.addEventListener('click', closeNotification);
    
    // Auto-remover após 5 segundos
    setTimeout(closeNotification, 5000);
}

// Atualizar dropdown de usuários
function updateUsuariosDropdown() {
    const select = document.getElementById('usuario_id');
    select.innerHTML = '<option value="">Selecione um usuário</option>';
    
    const usuarios = getUsuarios();
    usuarios.forEach(usuario => {
        const option = document.createElement('option');
        option.value = usuario.id;
        option.textContent = usuario.nome;
        select.appendChild(option);
    });
}

// Atualizar dropdown de plantas
function updatePlantasDropdown() {
    const select = document.getElementById('planta_id');
    select.innerHTML = '<option value="">Selecione uma planta</option>';
    
    const plantas = getPlantas();
    plantas.forEach(planta => {
        const option = document.createElement('option');
        option.value = planta.id;
        option.textContent = planta.nome_popular;
        select.appendChild(option);
    });
}

// Funções auxiliares
function formatTipoPlanta(tipo) {
    const tipos = {
        'interior': 'Interior',
        'exterior': 'Exterior',
        'suculenta': 'Suculenta',
        'frutifera': 'Frutífera',
        'hortalica': 'Hortaliça'
    };
    return tipos[tipo] || tipo;
}

function formatNivelLuz(nivel) {
    const niveis = {
        'baixa': 'Baixa',
        'media': 'Média',
        'alta': 'Alta'
    };
    return niveis[nivel] || nivel;
}

function formatExperiencia(nivel) {
    const niveis = {
        'iniciante': 'Iniciante',
        'intermediario': 'Intermediário',
        'avancado': 'Avançado'
    };
    return niveis[nivel] || nivel;
}

function formatTipoCuidado(tipo) {
    const tipos = {
        'rega': 'Rega',
        'poda': 'Poda',
        'adubacao': 'Adubação',
        'transplante': 'Transplante',
        'controle_pragas': 'Controle de Pragas'
    };
    return tipos[tipo] || tipo;
}

function formatDate(dateString) {
    if (!dateString) return '-';
    const date = new Date(dateString);
    return date.toLocaleDateString('pt-BR');
}

// Funções de acesso aos dados
function getPlantas() {
    const plantasJSON = localStorage.getItem('plantas');
    return plantasJSON ? JSON.parse(plantasJSON) : [];
}

function getUsuarios() {
    const usuariosJSON = localStorage.getItem('usuarios');
    return usuariosJSON ? JSON.parse(usuariosJSON) : [];
}

function getCuidados() {
    const cuidadosJSON = localStorage.getItem('cuidados');
    return cuidadosJSON ? JSON.parse(cuidadosJSON) : [];
}

function getPlantaById(id) {
    const plantas = getPlantas();
    return plantas.find(p => p.id == id);
}

function getUsuarioById(id) {
    const usuarios = getUsuarios();
    return usuarios.find(u => u.id == id);
}

function savePlantas() {
    const plantasTable = document.getElementById('plantasTable');
    const rows = plantasTable.querySelectorAll('tbody tr:not(.empty-table)');
    const plantas = [];
    
    rows.forEach(row => {
        const cells = row.querySelectorAll('td');
        if (cells.length >= 6) {
            plantas.push({
                id: cells[0].textContent,
                nome_cientifico: cells[1].textContent,
                nome_popular: cells[2].textContent,
                tipo_planta: cells[3].textContent.toLowerCase(),
                nivel_luz: cells[4].textContent.toLowerCase(),
                frequencia_rega: cells[5].textContent
            });
        }
    });
    
    localStorage.setItem('plantas', JSON.stringify(plantas));
}

function saveUsuarios() {
    const usuariosTable = document.getElementById('usuariosTable');
    const rows = usuariosTable.querySelectorAll('tbody tr:not(.empty-table)');
    const usuarios = [];
    
    rows.forEach(row => {
        const cells = row.querySelectorAll('td');
        if (cells.length >= 5) {
            usuarios.push({
                id: cells[0].textContent,
                nome: cells[1].textContent,
                email: cells[2].textContent,
                telefone: cells[3].textContent === 'Não informado' ? '' : cells[3].textContent,
                nivel_experiencia: cells[4].textContent.toLowerCase()
            });
        }
    });
    
    localStorage.setItem('usuarios', JSON.stringify(usuarios));
}

function saveCuidados() {
    const cuidadosTable = document.getElementById('cuidadosTable');
    const rows = cuidadosTable.querySelectorAll('tbody tr:not(.empty-table)');
    const cuidados = [];
    
    rows.forEach(row => {
        const cells = row.querySelectorAll('td');
        if (cells.length >= 6) {
            cuidados.push({
                id: cells[0].textContent,
                usuario_nome: cells[1].textContent,
                planta_nome: cells[2].textContent,
                tipo_cuidado: cells[3].textContent.toLowerCase(),
                data_cuidado: cells[4].textContent,
                proxima_data: cells[5].textContent
            });
        }
    });
    
    localStorage.setItem('cuidados', JSON.stringify(cuidados));
}

// Carregar dados de exemplo
function loadMockData() {
    // Verificar se já existem dados
    if (localStorage.getItem('dados_carregados') === 'true') {
        loadExistingData();
        return;
    }
    
    // Dados de exemplo - Plantas
    const plantasExemplo = [
        {
            id: 1001,
            nome_cientifico: 'Monstera deliciosa',
            nome_popular: 'Costela de Adão',
            tipo_planta: 'interior',
            nivel_luz: 'media',
            frequencia_rega: 7
        },
        {
            id: 1002,
            nome_cientifico: 'Sansevieria trifasciata',
            nome_popular: 'Espada de São Jorge',
            tipo_planta: 'interior',
            nivel_luz: 'baixa',
            frequencia_rega: 14
        },
        {
            id: 1003,
            nome_cientifico: 'Lavandula angustifolia',
            nome_popular: 'Lavanda',
            tipo_planta: 'exterior',
            nivel_luz: 'alta',
            frequencia_rega: 3
        }
    ];
    
    // Dados de exemplo - Usuários
    const usuariosExemplo = [
        {
            id: 2001,
            nome: 'Maria Silva',
            email: 'maria@exemplo.com',
            telefone: '(11) 98765-4321',
            nivel_experiencia: 'avancado'
        },
        {
            id: 2002,
            nome: 'João Pereira',
            email: 'joao@exemplo.com',
            telefone: '(21) 98765-4321',
            nivel_experiencia: 'iniciante'
        }
    ];
    
    // Dados de exemplo - Cuidados
    const cuidadosExemplo = [
        {
            id: 3001,
            usuario_id: 2001,
            planta_id: 1001,
            usuario_nome: 'Maria Silva',
            planta_nome: 'Costela de Adão',
            tipo_cuidado: 'rega',
            data_cuidado: '2025-04-15',
            proxima_data: '2025-04-22',
            frequencia: 7,
            observacoes: 'Regar somente quando o solo estiver seco.'
        },
        {
            id: 3002,
            usuario_id: 2002,
            planta_id: 1002,
            usuario_nome: 'João Pereira',
            planta_nome: 'Espada de São Jorge',
            tipo_cuidado: 'adubacao',
            data_cuidado: '2025-04-10',
            proxima_data: '2025-05-10',
            frequencia: 30,
            observacoes: 'Usar adubo orgânico diluído.'
        }
    ];
    
    // Salvar dados no localStorage
    localStorage.setItem('plantas', JSON.stringify(plantasExemplo));
    localStorage.setItem('usuarios', JSON.stringify(usuariosExemplo));
    localStorage.setItem('cuidados', JSON.stringify(cuidadosExemplo));
    localStorage.setItem('dados_carregados', 'true');
    
    // Carregar nas tabelas
    loadExistingData();
}

// Carregar dados existentes nas tabelas
function loadExistingData() {
    // Carregar plantas
    const plantas = getPlantas();
    plantas.forEach(planta => {
        addPlantaToTable(planta);
    });
    
    // Carregar usuários
    const usuarios = getUsuarios();
    usuarios.forEach(usuario => {
        addUsuarioToTable(usuario);
    });
    
    // Carregar cuidados
    const cuidados = getCuidados();
    cuidados.forEach(cuidado => {
        addCuidadoToTable(cuidado);
    });
    
    // Atualizar dropdowns
    updateUsuariosDropdown();
    updatePlantasDropdown();
}

// Adicionar planta à tabela
function addPlantaToTable(planta) {
    const table = document.getElementById('plantasTable');
    const tbody = table.querySelector('tbody');
    
    // Limpar mensagem de tabela vazia se for o primeiro item
    if (tbody.querySelector('.empty-table')) {
        tbody.innerHTML = '';
    }
    
    // Criar nova linha
    const tr = document.createElement('tr');
    tr.setAttribute('data-id', planta.id);
    tr.innerHTML = `
        <td>${planta.id}</td>
        <td>${planta.nome_cientifico}</td>
        <td>${planta.nome_popular}</td>
        <td>${formatTipoPlanta(planta.tipo_planta)}</td>
        <td>${formatNivelLuz(planta.nivel_luz)}</td>
        <td>${planta.frequencia_rega}</td>
        <td>
            <div class="action-buttons">
                <button class="btn-action btn-view" title="Visualizar"><i class="fas fa-eye"></i></button>
                <button class="btn-action btn-delete" title="Excluir"><i class="fas fa-trash"></i></button>
            </div>
        </td>
    `;
    
    // Adicionar eventos aos botões
    addActionButtonEvents(tr);
    
    // Adicionar linha à tabela
    tbody.appendChild(tr);
    
    // Salvar em localStorage (simulação de persistência)
    savePlantas();
}

// Adicionar usuário à tabela
function addUsuarioToTable(usuario) {
    const table = document.getElementById('usuariosTable');
    const tbody = table.querySelector('tbody');
    
    // Limpar mensagem de tabela vazia se for o primeiro item
    if (tbody.querySelector('.empty-table')) {
        tbody.innerHTML = '';
    }
    
    // Criar nova linha
    const tr = document.createElement('tr');
    tr.setAttribute('data-id', usuario.id);
    tr.innerHTML = `
        <td>${usuario.id}</td>
        <td>${usuario.nome}</td>
        <td>${usuario.email}</td>
        <td>${usuario.telefone || 'Não informado'}</td>
        <td>${formatExperiencia(usuario.nivel_experiencia)}</td>
        <td>
            <div class="action-buttons">
                <button class="btn-action btn-view" title="Visualizar"><i class="fas fa-eye"></i></button>
                <button class="btn-action btn-delete" title="Excluir"><i class="fas fa-trash"></i></button>
            </div>
        </td>
    `;
    
    // Adicionar eventos aos botões
    addActionButtonEvents(tr);
    
    // Adicionar linha à tabela
    tbody.appendChild(tr);
    
    // Salvar em localStorage (simulação de persistência)
    saveUsuarios();
}

// Adicionar cuidado à tabela
function addCuidadoToTable(cuidado) {
    const table = document.getElementById('cuidadosTable');
    const tbody = table.querySelector('tbody');
    
    // Limpar mensagem de tabela vazia se for o primeiro item
    if (tbody.querySelector('.empty-table')) {
        tbody.innerHTML = '';
    }
    
    // Criar nova linha
    const tr = document.createElement('tr');
    tr.setAttribute('data-id', cuidado.id);
    tr.innerHTML = `
        <td>${cuidado.id}</td>
        <td>${cuidado.usuario_nome}</td>
        <td>${cuidado.planta_nome}</td>
        <td>${formatTipoCuidado(cuidado.tipo_cuidado)}</td>
        <td>${formatDate(cuidado.data_cuidado)}</td>
        <td>${formatDate(cuidado.proxima_data)}</td>
        <td>
            <div class="action-buttons">
                <button class="btn-action btn-view" title="Visualizar"><i class="fas fa-eye"></i></button>
                <button class="btn-action btn-delete" title="Excluir"><i class="fas fa-trash"></i></button>
            </div>
        </td>
    `;
    
    // Adicionar eventos aos botões
    addActionButtonEvents(tr);
    
    // Adicionar linha à tabela
    tbody.appendChild(tr);
    
    // Salvar em localStorage (simulação de persistência)
    saveCuidados();
}

// Adicionar eventos aos botões de ação
function addActionButtonEvents(tr) {
    const viewBtn = tr.querySelector('.btn-view');
    const deleteBtn = tr.querySelector('.btn-delete');
    
    viewBtn.addEventListener('click', function() {
        const id = tr.getAttribute('data-id');
        // Implementar visualização detalhada
        showNotification('Visualização detalhada será implementada em breve!', 'success');
    });
    
    deleteBtn.addEventListener('click', function() {
        const id = tr.getAttribute('data-id');
        if (confirm('Tem certeza que deseja excluir este item?')) {
            tr.remove();
            showNotification('Item excluído com sucesso!', 'success');
            // Atualizar armazenamento
            savePlantas();
            saveUsuarios();
            saveCuidados();
        }
    });
}