import './styles/app.scss';
import 'bootstrap';
import Choices from 'choices.js';

// Initialisation de Choices.js pour les sélecteurs multiples
document.addEventListener('DOMContentLoaded', function() {
    const selectElements = document.querySelectorAll('select[data-choices]');
    
    selectElements.forEach(select => {
        new Choices(select, {
            removeItemButton: true,
            placeholder: true,
            searchEnabled: true,
            searchPlaceholderValue: 'Rechercher...',
            shouldSort: false,
        });
    });
});
