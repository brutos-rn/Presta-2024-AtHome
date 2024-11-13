import { getTokenFromUrl } from './utils.js';
import { displayAssociations } from './associations.js';

export function initAutocomplete(token) {
    const searchField = document.querySelector('.category-search-field');

    if (searchField) {
        searchField.addEventListener('input', function () {
            const query = searchField.value;

            if (query.length < 2) {
                removeAutocompleteResults();
                return;
            }

            fetchAutocompleteResults(query, token);
        });
    }
}

function removeAutocompleteResults() {
    const autocompleteResults = document.querySelector('.autocomplete-results');
    if (autocompleteResults) {
        autocompleteResults.remove();
    }
}

function fetchAutocompleteResults(query, token) {
    fetch(`/admin-dev/modules/artcategorysearch/search?query=${encodeURIComponent(query)}&_token=${token}`)
        .then(response => response.json())
        .then(data => {
            removeAutocompleteResults();
            displayAutocompleteResults(data, token);
        });
}

function displayAutocompleteResults(data, token) {
    const searchField = document.querySelector('.category-search-field');
    const autocompleteResults = document.createElement('div');
    autocompleteResults.classList.add('autocomplete-results');
    searchField.insertAdjacentElement('afterend', autocompleteResults);

    data.forEach(category => {
        const item = document.createElement('div');
        item.classList.add('autocomplete-item');
        item.textContent = category.name;
        item.dataset.categoryId = category.id_category;
        autocompleteResults.appendChild(item);

        item.addEventListener('click', function () {
            addAssociation(category.id_category, token);
            searchField.value = category.name;
            autocompleteResults.remove();
        });
    });
}

function addAssociation(idAssociatedCategory, token) {
    fetch(`/admin-dev/modules/artcategorysearch/add-association?id_associated_category=${idAssociatedCategory}&_token=${token}`, {
        method: 'POST'
    })
    .then(response => response.json())
    .then(result => {
        if (result.success) {
            displayAssociations(token);
        } else {
            console.error(result.error);
        }
    });
}
