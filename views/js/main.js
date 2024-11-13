import { initAutocomplete } from './modules/autocomplete.js';
import { initAssociations } from './modules/associations.js';
import { getTokenFromUrl } from './modules/utils.js';

document.addEventListener('DOMContentLoaded', function () {
    const token = getTokenFromUrl();

    initAutocomplete(token);
    initAssociations(token);
});
