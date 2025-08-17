export class TabManager {
    constructor() {
        this.currentTab = 'notes';
        this.tabButtons = document.querySelectorAll('[data-tab]');
        this.tabContents = document.querySelectorAll('.tab-content');
        
        this.init();
    }
    
    init() {
        if (this.tabButtons.length === 0) {
            console.error('❌ Botones de tab no encontrados');
            return;
        }
        
        this.setupEvents();
        this.showTab('notes'); // Mostrar tab de notas por defecto
        console.log('📑 TabManager inicializado');
    }
    
    setupEvents() {
        this.tabButtons.forEach(button => {
            button.addEventListener('click', (e) => {
                const tabName = e.target.dataset.tab;
                this.showTab(tabName);
            });
        });
    }
    
    showTab(tabName) {
        // Ocultar todos los tabs
        this.tabContents.forEach(content => {
            content.classList.add('hidden');
        });
        
        // Remover estado activo de todos los botones
        this.tabButtons.forEach(button => {
            button.classList.remove('border-blue-500', 'text-slate-100');
            button.classList.add('border-transparent', 'text-slate-300');
        });
        
        // Mostrar tab seleccionado
        const selectedContent = document.getElementById(`tab-${tabName}`);
        const selectedButton = document.querySelector(`[data-tab="${tabName}"]`);
        
        if (selectedContent) {
            selectedContent.classList.remove('hidden');
        }
        
        if (selectedButton) {
            selectedButton.classList.remove('border-transparent', 'text-slate-300');
            selectedButton.classList.add('border-blue-500', 'text-slate-100');
        }
        
        this.currentTab = tabName;
        
        // Disparar evento personalizado para notificar cambio de tab
        window.dispatchEvent(new CustomEvent('tabChanged', { 
            detail: { tab: tabName } 
        }));
    }
    
    getCurrentTab() {
        return this.currentTab;
    }
    
    isTabVisible(tabName) {
        return this.currentTab === tabName;
    }
}
